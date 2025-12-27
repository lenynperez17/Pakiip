/**
 * 🌐 SERVICIO DE GEOLOCALIZACIÓN POR IP
 *
 * Fallback para obtener ubicación aproximada cuando el GPS no está disponible
 * o el usuario denegó los permisos de geolocalización.
 *
 * Usa múltiples proveedores con fallback automático:
 * 1. ipapi.co (primario) - gratis, sin API key
 * 2. ip-api.com (secundario) - gratis, sin API key
 * 3. ipwhois.app (terciario) - gratis, sin API key
 *
 * @author Claude Code - Sistema Profesional
 * @version 1.0.0
 */

export interface IPLocationResult {
  latitude: number;
  longitude: number;
  accuracy: number; // Estimado en metros (típicamente 5-50km)
  city: string;
  region: string;
  country: string;
  countryCode: string;
  timezone: string;
  isp?: string;
}

/**
 * 🌐 Obtiene ubicación usando ipapi.co (proveedor primario)
 */
async function getLocationFromIPAPI(): Promise<IPLocationResult> {
  const response = await fetch('https://ipapi.co/json/', {
    headers: {
      'Accept': 'application/json',
    }
  });

  if (!response.ok) {
    throw new Error(`ipapi.co error: ${response.status}`);
  }

  const data = await response.json();

  // ipapi.co puede retornar error en formato JSON
  if (data.error) {
    throw new Error(`ipapi.co error: ${data.reason}`);
  }

  return {
    latitude: data.latitude,
    longitude: data.longitude,
    accuracy: 10000, // ~10km de precisión típica para IP
    city: data.city || 'Unknown',
    region: data.region || '',
    country: data.country_name || '',
    countryCode: data.country_code || '',
    timezone: data.timezone || '',
    isp: data.org || undefined,
  };
}

/**
 * 🌐 Obtiene ubicación usando ip-api.com (proveedor secundario)
 */
async function getLocationFromIPAPIcom(): Promise<IPLocationResult> {
  const response = await fetch('http://ip-api.com/json/?fields=status,message,country,countryCode,region,regionName,city,lat,lon,timezone,isp', {
    headers: {
      'Accept': 'application/json',
    }
  });

  if (!response.ok) {
    throw new Error(`ip-api.com error: ${response.status}`);
  }

  const data = await response.json();

  if (data.status === 'fail') {
    throw new Error(`ip-api.com error: ${data.message}`);
  }

  return {
    latitude: data.lat,
    longitude: data.lon,
    accuracy: 15000, // ~15km para este proveedor
    city: data.city || 'Unknown',
    region: data.regionName || '',
    country: data.country || '',
    countryCode: data.countryCode || '',
    timezone: data.timezone || '',
    isp: data.isp || undefined,
  };
}

/**
 * 🌐 Obtiene ubicación usando ipwhois.app (proveedor terciario)
 */
async function getLocationFromIPWhois(): Promise<IPLocationResult> {
  const response = await fetch('https://ipwhois.app/json/', {
    headers: {
      'Accept': 'application/json',
    }
  });

  if (!response.ok) {
    throw new Error(`ipwhois.app error: ${response.status}`);
  }

  const data = await response.json();

  if (!data.success) {
    throw new Error(`ipwhois.app error: ${data.message || 'Unknown error'}`);
  }

  return {
    latitude: parseFloat(data.latitude),
    longitude: parseFloat(data.longitude),
    accuracy: 20000, // ~20km para este proveedor
    city: data.city || 'Unknown',
    region: data.region || '',
    country: data.country || '',
    countryCode: data.country_code || '',
    timezone: data.timezone || '',
    isp: data.isp || undefined,
  };
}

/**
 * 🎯 Obtiene la ubicación del usuario usando su IP
 *
 * Intenta múltiples proveedores en secuencia hasta obtener un resultado válido.
 * Si todos fallan, lanza un error.
 *
 * @returns Información de ubicación basada en IP
 * @throws Error si todos los proveedores fallan
 *
 * @example
 * try {
 *   const location = await getLocationByIP();
 *   console.log(`Ciudad: ${location.city}, País: ${location.country}`);
 *   console.log(`Coords: ${location.latitude}, ${location.longitude}`);
 * } catch (error) {
 *   console.error('No se pudo obtener ubicación por IP:', error);
 * }
 */
export async function getLocationByIP(): Promise<IPLocationResult> {
  const providers = [
    { name: 'ipapi.co', fn: getLocationFromIPAPI },
    { name: 'ip-api.com', fn: getLocationFromIPAPIcom },
    { name: 'ipwhois.app', fn: getLocationFromIPWhois },
  ];

  const errors: { provider: string; error: any }[] = [];

  for (const provider of providers) {
    try {
      const result = await provider.fn();
      return result;
    } catch (error) {
      console.warn(`⚠️ ${provider.name} falló:`, error);
      errors.push({ provider: provider.name, error });
      // Continuar con el siguiente proveedor
    }
  }

  // Si todos fallaron, lanzar error con detalles
  console.error('❌ Todos los proveedores de IP geolocation fallaron:', errors);
  throw new Error(
    `No se pudo obtener ubicación por IP. Proveedores intentados: ${errors.map(e => e.provider).join(', ')}`
  );
}

/**
 * 🧪 Prueba todos los proveedores de IP geolocation
 *
 * Útil para debugging y verificar qué proveedores están funcionando.
 *
 * @returns Resultados de todos los proveedores
 */
export async function testAllProviders(): Promise<{
  ipapi: IPLocationResult | Error;
  ipapicom: IPLocationResult | Error;
  ipwhois: IPLocationResult | Error;
}> {
  const results = {
    ipapi: null as IPLocationResult | Error | null,
    ipapicom: null as IPLocationResult | Error | null,
    ipwhois: null as IPLocationResult | Error | null,
  };

  try {
    results.ipapi = await getLocationFromIPAPI();
  } catch (error) {
    results.ipapi = error as Error;
  }

  try {
    results.ipapicom = await getLocationFromIPAPIcom();
  } catch (error) {
    results.ipapicom = error as Error;
  }

  try {
    results.ipwhois = await getLocationFromIPWhois();
  } catch (error) {
    results.ipwhois = error as Error;
  }

  return results as any;
}
