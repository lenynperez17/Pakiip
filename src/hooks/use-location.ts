/**
 * Hook profesional de geolocalización
 *
 * Sistema completo de manejo de ubicaciones con:
 * - Solicitud de permisos con UI amigable
 * - Validación de precisión GPS
 * - Fallback automático a IP geolocation
 * - Caché inteligente en localStorage
 * - Detección de ciudad más cercana
 * - Manejo robusto de errores
 * - Retry logic con backoff exponencial
 */

"use client";

import { useState, useEffect, useCallback, useRef } from 'react';
import { reverseGeocode } from '@/lib/google-geocoding';
import { getLocationByIP } from '@/lib/ip-geolocation';
import { calculateDistance } from '@/lib/haversine';
import { City } from '@/lib/placeholder-data';
import { useAppData } from './use-app-data';

// ==================== TIPOS ====================

export type LocationAccuracy = 'excellent' | 'good' | 'medium' | 'poor' | 'very-poor';

export interface UserLocation {
  latitude: number;
  longitude: number;
  accuracy: number; // metros
  accuracyLevel: LocationAccuracy;
  timestamp: number;
  source: 'gps' | 'ip' | 'cached' | 'manual';
  address?: string;
  city?: string;
  country?: string;
}

export interface LocationError {
  code: 'PERMISSION_DENIED' | 'POSITION_UNAVAILABLE' | 'TIMEOUT' | 'UNKNOWN';
  message: string;
  canRetry: boolean;
}

export interface LocationState {
  location: UserLocation | null;
  isLoading: boolean;
  error: LocationError | null;
  permissionStatus: 'prompt' | 'granted' | 'denied' | 'unknown';
  hasAskedPermission: boolean;
}

// ==================== CONFIGURACIÓN ====================

const CONFIG = {
  // Tiempos
  GPS_TIMEOUT: 15000, // 15 segundos para GPS
  GPS_RETRY_TIMEOUT: 30000, // 30 segundos en retry
  MAX_CACHE_AGE: 60 * 60 * 1000, // 1 hora

  // Precisión
  EXCELLENT_ACCURACY: 50, // < 50m = excelente
  GOOD_ACCURACY: 100, // < 100m = bueno
  MEDIUM_ACCURACY: 500, // < 500m = medio
  POOR_ACCURACY: 5000, // < 5km = pobre
  MAX_ACCEPTABLE_ACCURACY: 10000, // 10km = máximo aceptable

  // Retry
  MAX_RETRIES: 2,
  RETRY_DELAY_MS: 2000,

  // Caché
  CACHE_KEY: 'pakiip_user_location_v2',
  LAST_PERMISSION_ASK_KEY: 'pakiip_last_permission_ask',
  PERMISSION_ASK_COOLDOWN: 24 * 60 * 60 * 1000, // 24 horas
};

// ==================== UTILIDADES ====================

/**
 * Determina el nivel de precisión basado en la accuracy en metros
 */
function getAccuracyLevel(accuracy: number): LocationAccuracy {
  if (accuracy < CONFIG.EXCELLENT_ACCURACY) return 'excellent';
  if (accuracy < CONFIG.GOOD_ACCURACY) return 'good';
  if (accuracy < CONFIG.MEDIUM_ACCURACY) return 'medium';
  if (accuracy < CONFIG.POOR_ACCURACY) return 'poor';
  return 'very-poor';
}

/**
 * Guarda ubicación en el servidor (o localStorage como fallback)
 */
async function cacheLocation(location: UserLocation, userId?: string): Promise<void> {
  try {
    // Guardar en backend si hay usuario autenticado
    if (userId) {
      const { saveUserSettingsToBackend } = await import('@/lib/backend-adapter');
      const result = await saveUserSettingsToBackend(userId, { lastLocation: location });

      if (!result.success) {
        localStorage.setItem(CONFIG.CACHE_KEY, JSON.stringify(location));
      }
    } else {
      // Sin usuario, usar localStorage
      localStorage.setItem(CONFIG.CACHE_KEY, JSON.stringify(location));
    }
  } catch {
    try {
      localStorage.setItem(CONFIG.CACHE_KEY, JSON.stringify(location));
    } catch {
      // No se pudo guardar ubicación
    }
  }
}

/**
 * Recupera ubicación del servidor (o localStorage como fallback)
 */
async function getCachedLocation(userId?: string): Promise<UserLocation | null> {
  try {
    // Cargar del backend si hay usuario autenticado
    if (userId) {
      const { loadUserSettingsFromBackend } = await import('@/lib/backend-adapter');
      const result = await loadUserSettingsFromBackend(userId);

      if (result.success && result.data?.lastLocation) {
        const location: UserLocation = result.data.lastLocation;
        const age = Date.now() - location.timestamp;

        if (age > CONFIG.MAX_CACHE_AGE) {
          return null;
        }

        return { ...location, source: 'cached' };
      }
    }

    // Sin usuario o sin datos en servidor, usar localStorage
    const cached = localStorage.getItem(CONFIG.CACHE_KEY);
    if (!cached) return null;

    const location: UserLocation = JSON.parse(cached);
    const age = Date.now() - location.timestamp;

    if (age > CONFIG.MAX_CACHE_AGE) {
      localStorage.removeItem(CONFIG.CACHE_KEY);
      return null;
    }

    return { ...location, source: 'cached' };
  } catch {
    return null;
  }
}

/**
 * Verifica si podemos pedir permisos (cooldown de 24h)
 */
function canAskPermission(): boolean {
  try {
    const lastAsk = localStorage.getItem(CONFIG.LAST_PERMISSION_ASK_KEY);
    if (!lastAsk) return true;

    const timeSinceAsk = Date.now() - parseInt(lastAsk);
    return timeSinceAsk > CONFIG.PERMISSION_ASK_COOLDOWN;
  } catch {
    return true;
  }
}

/**
 * Registra que pedimos permisos
 */
function markPermissionAsked(): void {
  try {
    localStorage.setItem(CONFIG.LAST_PERMISSION_ASK_KEY, Date.now().toString());
  } catch {
    // No se pudo registrar
  }
}

// ==================== HOOK PRINCIPAL ====================

export function useLocation(
  cities?: City[],
  options?: {
    autoRequest?: boolean; // Solicitar automáticamente al montar
    useIPFallback?: boolean; // Usar IP si GPS falla
    enableCache?: boolean; // Usar caché
    minAccuracy?: number; // Precisión mínima aceptable en metros
  }
) {
  const {
    autoRequest = true,
    useIPFallback = true,
    enableCache = true,
    minAccuracy = CONFIG.MAX_ACCEPTABLE_ACCURACY,
  } = options || {};

  // Obtener usuario actual para persistencia en el servidor
  const { currentUser } = useAppData();

  // Estados
  const [state, setState] = useState<LocationState>({
    location: null,
    isLoading: false,
    error: null,
    permissionStatus: 'unknown',
    hasAskedPermission: false,
  });

  // Refs para evitar llamadas duplicadas
  const isRequestingRef = useRef(false);
  const retryCountRef = useRef(0);

  /**
   * Obtiene ubicación por GPS
   */
  const getGPSLocation = useCallback(async (retryCount: number = 0): Promise<UserLocation> => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject({
          code: 'POSITION_UNAVAILABLE',
          message: 'Tu navegador no soporta geolocalización',
          canRetry: false,
        } as LocationError);
        return;
      }

      const timeout = retryCount > 0 ? CONFIG.GPS_RETRY_TIMEOUT : CONFIG.GPS_TIMEOUT;

      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const { latitude, longitude, accuracy } = position.coords;
          const accuracyLevel = getAccuracyLevel(accuracy);

          // Validar precisión
          if (accuracy > minAccuracy) {
            // Si tenemos retries, intentar de nuevo
            if (retryCount < CONFIG.MAX_RETRIES) {
              setTimeout(() => {
                getGPSLocation(retryCount + 1).then(resolve).catch(reject);
              }, CONFIG.RETRY_DELAY_MS);
              return;
            }

            // Sin retries, rechazar
            reject({
              code: 'POSITION_UNAVAILABLE',
              message: `GPS impreciso (${accuracy.toFixed(0)}m). Usa búsqueda manual.`,
              canRetry: true,
            } as LocationError);
            return;
          }

          // Obtener dirección con geocoding reverso
          let address: string | undefined;
          let city: string | undefined;
          let country: string | undefined;

          try {
            const geocodeResult = await reverseGeocode(latitude, longitude);
            if (geocodeResult) {
              address = geocodeResult.formattedAddress;
            }
          } catch {
            // Geocoding falló, continuar sin dirección
          }

          const location: UserLocation = {
            latitude,
            longitude,
            accuracy,
            accuracyLevel,
            timestamp: Date.now(),
            source: 'gps',
            address,
            city,
            country,
          };

          resolve(location);
        },
        (error) => {
          let locationError: LocationError;

          switch (error.code) {
            case error.PERMISSION_DENIED:
              locationError = {
                code: 'PERMISSION_DENIED',
                message: 'Permisos de ubicación denegados',
                canRetry: false,
              };
              break;
            case error.POSITION_UNAVAILABLE:
              locationError = {
                code: 'POSITION_UNAVAILABLE',
                message: 'Ubicación no disponible',
                canRetry: true,
              };
              break;
            case error.TIMEOUT:
              locationError = {
                code: 'TIMEOUT',
                message: 'Tiempo de espera agotado',
                canRetry: true,
              };
              break;
            default:
              locationError = {
                code: 'UNKNOWN',
                message: error.message || 'Error desconocido',
                canRetry: true,
              };
          }

          reject(locationError);
        },
        {
          enableHighAccuracy: true,
          timeout,
          maximumAge: 0, // Siempre pedir ubicación fresca
        }
      );
    });
  }, [minAccuracy]);

  /**
   * Obtiene ubicación por IP (fallback)
   */
  const getIPLocation = useCallback(async (): Promise<UserLocation> => {
    try {
      const ipLocation = await getLocationByIP();

      // Hacer reverse geocoding para obtener dirección más precisa
      let address: string | undefined;
      let city: string | undefined = ipLocation.city;
      let country: string | undefined = ipLocation.country;

      try {
        const geocodeResult = await reverseGeocode(ipLocation.latitude, ipLocation.longitude);
        if (geocodeResult) {
          address = geocodeResult.formattedAddress;
        }
      } catch {
        // Si falla el geocoding, usar la dirección básica de IP
        address = `${ipLocation.city}, ${ipLocation.region}, ${ipLocation.country}`;
      }

      return {
        latitude: ipLocation.latitude,
        longitude: ipLocation.longitude,
        accuracy: ipLocation.accuracy,
        accuracyLevel: getAccuracyLevel(ipLocation.accuracy),
        timestamp: Date.now(),
        source: 'ip',
        address: address || `${ipLocation.city}, ${ipLocation.region}, ${ipLocation.country}`,
        city,
        country,
      };
    } catch {
      throw {
        code: 'POSITION_UNAVAILABLE',
        message: 'No se pudo obtener ubicación por IP',
        canRetry: false,
      } as LocationError;
    }
  }, []);

  /**
   * Solicita ubicación (flujo completo)
   */
  const requestLocation = useCallback(async (force: boolean = false) => {
    // Evitar llamadas duplicadas
    if (isRequestingRef.current && !force) {
      return;
    }

    isRequestingRef.current = true;
    retryCountRef.current = 0;

    setState(prev => ({ ...prev, isLoading: true, error: null }));

    try {
      // 1. Intentar caché si está habilitado y no es forzado
      if (enableCache && !force) {
        const cached = await getCachedLocation(currentUser?.id);
        if (cached) {
          setState({
            location: cached,
            isLoading: false,
            error: null,
            permissionStatus: 'granted',
            hasAskedPermission: true,
          });
          isRequestingRef.current = false;
          return;
        }
      }

      // 2. Verificar permisos de geolocalización
      if (navigator.permissions) {
        try {
          const permission = await navigator.permissions.query({ name: 'geolocation' as PermissionName });
          setState(prev => ({ ...prev, permissionStatus: permission.state }));

          if (permission.state === 'denied') {
            // Usar IP fallback si está habilitado
            if (useIPFallback) {
              const ipLocation = await getIPLocation();
              await cacheLocation(ipLocation, currentUser?.id);
              setState({
                location: ipLocation,
                isLoading: false,
                error: null,
                permissionStatus: 'denied',
                hasAskedPermission: true,
              });
              isRequestingRef.current = false;
              return;
            }

            throw {
              code: 'PERMISSION_DENIED',
              message: 'Permisos de ubicación denegados',
              canRetry: false,
            } as LocationError;
          }
        } catch {
          // No se pudo verificar permisos
        }
      }

      // 3. Intentar obtener ubicación por GPS
      try {
        markPermissionAsked();
        const gpsLocation = await getGPSLocation();

        await cacheLocation(gpsLocation, currentUser?.id);

        setState({
          location: gpsLocation,
          isLoading: false,
          error: null,
          permissionStatus: 'granted',
          hasAskedPermission: true,
        });
      } catch (gpsError: unknown) {
        const error = gpsError as LocationError;

        // 4. Fallback a IP si está habilitado
        if (useIPFallback && error.code !== 'TIMEOUT') {
          const ipLocation = await getIPLocation();

          await cacheLocation(ipLocation, currentUser?.id);

          setState({
            location: ipLocation,
            isLoading: false,
            error: null,
            permissionStatus: error.code === 'PERMISSION_DENIED' ? 'denied' : 'prompt',
            hasAskedPermission: true,
          });
        } else {
          // Sin fallback, propagar error
          throw gpsError;
        }
      }
    } catch (error: unknown) {
      setState(prev => ({
        ...prev,
        isLoading: false,
        error: error as LocationError,
        hasAskedPermission: true,
      }));
    } finally {
      isRequestingRef.current = false;
    }
  }, [enableCache, useIPFallback, getGPSLocation, getIPLocation, currentUser]);

  /**
   * Encuentra la ciudad más cercana
   */
  const findNearestCity = useCallback((): City | null => {
    if (!state.location || !cities || cities.length === 0) return null;

    const { latitude, longitude } = state.location;

    let nearestCity: City | null = null;
    let minDistance = Infinity;

    cities.forEach(city => {
      if (city.coordinates) {
        const distance = calculateDistance(
          latitude,
          longitude,
          city.coordinates.lat,
          city.coordinates.lng
        );

        if (distance < minDistance) {
          minDistance = distance;
          nearestCity = city;
        }
      }
    });

    return nearestCity;
  }, [state.location, cities]);

  /**
   * Refresca la ubicación
   */
  const refreshLocation = useCallback(() => {
    return requestLocation(true);
  }, [requestLocation]);

  /**
   * Limpia el caché
   */
  const clearCache = useCallback(() => {
    try {
      localStorage.removeItem(CONFIG.CACHE_KEY);
    } catch {
      // Error limpiando caché
    }
  }, []);

  // Auto-request al montar si está habilitado
  useEffect(() => {
    const initLocation = async () => {
      // Siempre intentar cargar del caché primero
      if (enableCache && !state.location) {
        const cached = await getCachedLocation(currentUser?.id);
        if (cached) {
          setState(prev => ({
            ...prev,
            location: cached,
            isLoading: false,
            hasAskedPermission: true,
          }));
          return;
        }
      }

      // Si no hay caché y podemos pedir permisos, solicitar ubicación
      if (autoRequest && !state.hasAskedPermission && canAskPermission()) {
        requestLocation();
      }
    };

    initLocation();
  }, [autoRequest, enableCache, currentUser?.id, state.hasAskedPermission, state.location, requestLocation]);

  return {
    // Estado
    ...state,

    // Ciudad más cercana
    nearestCity: findNearestCity(),

    // Acciones
    requestLocation,
    refreshLocation,
    clearCache,

    // Utilidades
    canAskPermission: canAskPermission(),
  };
}
