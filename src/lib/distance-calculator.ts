/**
 * Calcula la distancia entre dos coordenadas usando la fórmula de Haversine
 * Retorna la distancia en kilómetros
 *
 * @param lat1 Latitud del punto 1
 * @param lng1 Longitud del punto 1
 * @param lat2 Latitud del punto 2
 * @param lng2 Longitud del punto 2
 * @returns Distancia en kilómetros
 */
export function calculateDistance(
  lat1: number,
  lng1: number,
  lat2: number,
  lng2: number
): number {
  // Radio de la Tierra en kilómetros
  const R = 6371;

  // Convertir grados a radianes
  const dLat = toRadians(lat2 - lat1);
  const dLng = toRadians(lng2 - lng1);

  // Fórmula de Haversine
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRadians(lat1)) *
      Math.cos(toRadians(lat2)) *
      Math.sin(dLng / 2) *
      Math.sin(dLng / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  // Distancia en kilómetros
  const distance = R * c;

  return distance;
}

/**
 * Convierte grados a radianes
 */
function toRadians(degrees: number): number {
  return degrees * (Math.PI / 180);
}

/**
 * Filtra vendors por distancia máxima desde la ubicación del usuario
 *
 * @param vendors Array de vendors
 * @param userLat Latitud del usuario
 * @param userLng Longitud del usuario
 * @param maxDistanceKm Distancia máxima en kilómetros (default: 20km)
 * @returns Array de vendors cercanos con su distancia calculada
 */
export function filterVendorsByDistance<T extends { coordinates: { lat: number; lng: number } }>(
  vendors: T[],
  userLat: number,
  userLng: number,
  maxDistanceKm: number = 20
): Array<T & { distance: number }> {
  return vendors
    .map(vendor => {
      const distance = calculateDistance(
        userLat,
        userLng,
        vendor.coordinates.lat,
        vendor.coordinates.lng
      );
      return { ...vendor, distance };
    })
    .filter(vendor => vendor.distance <= maxDistanceKm)
    .sort((a, b) => a.distance - b.distance); // Ordenar por distancia (más cercano primero)
}
