/**
 * Hook de geolocalización - SOLO GPS de alta precisión
 *
 * NO usa fallbacks de IP, WiFi, caché ni ubicaciones guardadas.
 * SIEMPRE obtiene ubicación GPS fresca del dispositivo.
 */

"use client";

import { useState, useEffect, useCallback, useRef } from 'react';
import { reverseGeocode } from '@/lib/google-geocoding';
import { calculateDistance } from '@/lib/haversine';
import { City } from '@/lib/placeholder-data';

// ==================== TIPOS ====================

export type LocationAccuracy = 'excellent' | 'good' | 'medium' | 'poor' | 'very-poor';

export interface UserLocation {
  latitude: number;
  longitude: number;
  accuracy: number;
  accuracyLevel: LocationAccuracy;
  timestamp: number;
  source: 'gps';
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
  // Tiempos reducidos para carga rápida
  GPS_TIMEOUT: 8000, // 8 segundos máximo

  // Precisión - solo para clasificar, NO para rechazar
  EXCELLENT_ACCURACY: 50, // < 50m = excelente
  GOOD_ACCURACY: 100, // < 100m = bueno
  MEDIUM_ACCURACY: 300, // < 300m = medio
};

// ==================== UTILIDADES ====================

function getAccuracyLevel(accuracy: number): LocationAccuracy {
  if (accuracy < CONFIG.EXCELLENT_ACCURACY) return 'excellent';
  if (accuracy < CONFIG.GOOD_ACCURACY) return 'good';
  if (accuracy < CONFIG.MEDIUM_ACCURACY) return 'medium';
  if (accuracy < 1000) return 'poor';
  return 'very-poor';
}

// ==================== HOOK PRINCIPAL ====================

export function useLocation(
  cities?: City[],
  options?: {
    autoRequest?: boolean;
  }
) {
  const {
    autoRequest = true,
  } = options || {};

  const [state, setState] = useState<LocationState>({
    location: null,
    isLoading: false,
    error: null,
    permissionStatus: 'unknown',
    hasAskedPermission: false,
  });

  const isRequestingRef = useRef(false);

  /**
   * Obtiene ubicación por GPS
   * ACEPTA CUALQUIER ubicación que el GPS devuelva - sin validación de precisión
   */
  const getGPSLocation = useCallback(async (): Promise<UserLocation> => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject({
          code: 'POSITION_UNAVAILABLE',
          message: 'Tu navegador no soporta geolocalización',
          canRetry: false,
        } as LocationError);
        return;
      }

      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const { latitude, longitude, accuracy } = position.coords;
          const accuracyLevel = getAccuracyLevel(accuracy);

          // ACEPTAR CUALQUIER UBICACIÓN que el GPS devuelva
          console.log(`[GPS] Ubicación obtenida: ${latitude}, ${longitude} (precisión: ${Math.round(accuracy)}m)`);

          // Obtener dirección con geocoding reverso
          let address: string | undefined;

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
          };

          resolve(location);
        },
        (error) => {
          let locationError: LocationError;

          switch (error.code) {
            case error.PERMISSION_DENIED:
              locationError = {
                code: 'PERMISSION_DENIED',
                message: 'Permisos de ubicación denegados. Por favor, habilita el acceso a tu ubicación.',
                canRetry: false,
              };
              break;
            case error.POSITION_UNAVAILABLE:
              locationError = {
                code: 'POSITION_UNAVAILABLE',
                message: 'GPS no disponible. Asegúrate de tener el GPS activado.',
                canRetry: true,
              };
              break;
            case error.TIMEOUT:
              locationError = {
                code: 'TIMEOUT',
                message: 'Tiempo de espera agotado. Intenta en un lugar con mejor señal GPS.',
                canRetry: true,
              };
              break;
            default:
              locationError = {
                code: 'UNKNOWN',
                message: error.message || 'Error desconocido al obtener ubicación',
                canRetry: true,
              };
          }

          reject(locationError);
        },
        {
          enableHighAccuracy: true, // GPS real, la más precisa posible
          timeout: CONFIG.GPS_TIMEOUT, // 60 segundos
          maximumAge: 0, // siempre ubicación ACTUAL, nunca cacheada
        }
      );
    });
  }, []);

  /**
   * Solicita ubicación GPS - SIEMPRE fresca, NUNCA caché
   */
  const requestLocation = useCallback(async (force: boolean = false) => {
    if (isRequestingRef.current && !force) {
      return;
    }

    isRequestingRef.current = true;
    setState(prev => ({ ...prev, isLoading: true, error: null }));

    try {
      // Verificar permisos
      if (navigator.permissions) {
        try {
          const permission = await navigator.permissions.query({ name: 'geolocation' as PermissionName });
          setState(prev => ({ ...prev, permissionStatus: permission.state }));

          if (permission.state === 'denied') {
            throw {
              code: 'PERMISSION_DENIED',
              message: 'Permisos de ubicación denegados. Habilita el GPS en la configuración de tu navegador.',
              canRetry: false,
            } as LocationError;
          }
        } catch (permError) {
          if ((permError as LocationError).code === 'PERMISSION_DENIED') {
            throw permError;
          }
        }
      }

      // Obtener ubicación GPS fresca
      const gpsLocation = await getGPSLocation();

      setState({
        location: gpsLocation,
        isLoading: false,
        error: null,
        permissionStatus: 'granted',
        hasAskedPermission: true,
      });
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
  }, [getGPSLocation]);

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
   * Refresca la ubicación (siempre GPS fresco)
   */
  const refreshLocation = useCallback(() => {
    return requestLocation(true);
  }, [requestLocation]);

  // Auto-request al montar
  useEffect(() => {
    if (autoRequest && !state.hasAskedPermission) {
      requestLocation();
    }
  }, [autoRequest, state.hasAskedPermission, requestLocation]);

  return {
    ...state,
    nearestCity: findNearestCity(),
    requestLocation,
    refreshLocation,
    clearCache: () => {}, // Ya no hay caché
    canAskPermission: true, // Siempre se puede pedir
  };
}
