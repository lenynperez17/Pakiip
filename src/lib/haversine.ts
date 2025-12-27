/**
 * 🌍 FÓRMULA DE HAVERSINE
 *
 * Cálculo preciso de distancias entre dos puntos en la Tierra
 * usando coordenadas geográficas (latitud, longitud).
 *
 * La fórmula de Haversine calcula la distancia del gran círculo
 * entre dos puntos en una esfera a partir de sus longitudes y latitudes.
 *
 * @author Claude Code - Sistema Profesional
 * @version 1.0.0
 */

// Radio de la Tierra en kilómetros
const EARTH_RADIUS_KM = 6371;

/**
 * Convierte grados a radianes
 */
function toRadians(degrees: number): number {
  return degrees * (Math.PI / 180);
}

/**
 * 📏 Calcula la distancia entre dos coordenadas geográficas usando la fórmula de Haversine
 *
 * @param lat1 Latitud del primer punto en grados (-90 a 90)
 * @param lon1 Longitud del primer punto en grados (-180 a 180)
 * @param lat2 Latitud del segundo punto en grados (-90 a 90)
 * @param lon2 Longitud del segundo punto en grados (-180 a 180)
 * @returns Distancia en kilómetros
 *
 * @example
 * // Distancia entre Lima y Cusco
 * const distancia = calculateDistance(-12.0464, -77.0428, -13.5319, -71.9675);
 * console.log(`Distancia: ${distancia.toFixed(2)} km`); // ~571.47 km
 */
export function calculateDistance(
  lat1: number,
  lon1: number,
  lat2: number,
  lon2: number
): number {
  // Validar coordenadas
  if (
    lat1 < -90 || lat1 > 90 ||
    lat2 < -90 || lat2 > 90 ||
    lon1 < -180 || lon1 > 180 ||
    lon2 < -180 || lon2 > 180
  ) {
    console.error('❌ Coordenadas inválidas:', { lat1, lon1, lat2, lon2 });
    return Infinity;
  }

  // Diferencias en radianes
  const dLat = toRadians(lat2 - lat1);
  const dLon = toRadians(lon2 - lon1);

  // Convertir latitudes a radianes
  const lat1Rad = toRadians(lat1);
  const lat2Rad = toRadians(lat2);

  // Fórmula de Haversine
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1Rad) * Math.cos(lat2Rad) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  // Distancia en kilómetros
  const distance = EARTH_RADIUS_KM * c;

  return distance;
}

/**
 * 📏 Calcula si un punto está dentro de un radio de búsqueda
 *
 * @param centerLat Latitud del centro de búsqueda
 * @param centerLon Longitud del centro de búsqueda
 * @param pointLat Latitud del punto a verificar
 * @param pointLon Longitud del punto a verificar
 * @param radiusKm Radio de búsqueda en kilómetros
 * @returns true si el punto está dentro del radio, false si no
 *
 * @example
 * const estaEnRango = isWithinRadius(-12.0464, -77.0428, -12.1234, -77.0567, 10);
 * console.log(estaEnRango); // true si está a menos de 10km
 */
export function isWithinRadius(
  centerLat: number,
  centerLon: number,
  pointLat: number,
  pointLon: number,
  radiusKm: number
): boolean {
  const distance = calculateDistance(centerLat, centerLon, pointLat, pointLon);
  return distance <= radiusKm;
}

/**
 * 🎯 Encuentra el punto más cercano de una lista
 *
 * @param centerLat Latitud del centro de búsqueda
 * @param centerLon Longitud del centro de búsqueda
 * @param points Lista de puntos con coordenadas
 * @returns El punto más cercano y su distancia
 *
 * @example
 * const tiendas = [
 *   { id: '1', lat: -12.0464, lon: -77.0428 },
 *   { id: '2', lat: -12.1234, lon: -77.0567 },
 * ];
 * const masCercana = findNearest(-12.0500, -77.0400, tiendas);
 */
export function findNearest<T extends { lat: number; lon: number }>(
  centerLat: number,
  centerLon: number,
  points: T[]
): { point: T; distance: number } | null {
  if (points.length === 0) return null;

  let nearest: T = points[0];
  let minDistance = calculateDistance(centerLat, centerLon, points[0].lat, points[0].lon);

  for (let i = 1; i < points.length; i++) {
    const distance = calculateDistance(centerLat, centerLon, points[i].lat, points[i].lon);
    if (distance < minDistance) {
      minDistance = distance;
      nearest = points[i];
    }
  }

  return { point: nearest, distance: minDistance };
}

/**
 * 📍 Ordena puntos por distancia al centro
 *
 * @param centerLat Latitud del centro
 * @param centerLon Longitud del centro
 * @param points Lista de puntos
 * @returns Puntos ordenados por distancia (más cercano primero)
 *
 * @example
 * const tiendasOrdenadas = sortByDistance(-12.0464, -77.0428, tiendas);
 */
export function sortByDistance<T extends { lat: number; lon: number }>(
  centerLat: number,
  centerLon: number,
  points: T[]
): (T & { distance: number })[] {
  return points
    .map(point => ({
      ...point,
      distance: calculateDistance(centerLat, centerLon, point.lat, point.lon)
    }))
    .sort((a, b) => a.distance - b.distance);
}
