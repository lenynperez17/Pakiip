// Utilidad para geocoding usando Google Maps API
// Funciones para obtener dirección desde coordenadas y buscar lugares

export interface GeocodeResult {
  formattedAddress: string;
  fullAddress: string;
  coordinates: {
    lat: number;
    lng: number;
  };
  // Propiedades adicionales extraídas de Google Maps
  locality?: string; // Ciudad
  district?: string; // Distrito
  city?: string; // Ciudad (alias)
  sublocality?: string; // Sublocalidad
  neighborhood?: string; // Vecindario/sector
}

/**
 * Obtiene la dirección a partir de coordenadas usando Google Maps Geocoder JavaScript API (moderna)
 * Sin problemas de CORS, sin warnings deprecados
 */
export async function reverseGeocode(
  lat: number,
  lng: number
): Promise<GeocodeResult | null> {
  // Verificar que Google Maps JavaScript API esté cargada
  if (typeof window === 'undefined' || !window.google?.maps) {
    return null;
  }

  try {
    // Usar Geocoder JavaScript API (moderna - sin CORS, sin warnings)
    const geocoder = new google.maps.Geocoder();
    const location = { lat, lng };

    // Llamada con Promise (versión moderna)
    const response = await geocoder.geocode({
      location: location,
      language: 'es',
      region: 'PE'
    });

    if (!response.results || response.results.length === 0) {
      return null;
    }

    const result = response.results[0];
    const addressComponents = result.address_components;

    // Extraer TODOS los componentes relevantes para dirección completa
    let streetNumber = '';
    let route = '';
    let neighborhood = '';
    let sublocality = '';
    let sublocalityLevel1 = '';
    let locality = '';
    let administrativeAreaLevel1 = ''; // Región
    let administrativeAreaLevel2 = ''; // Provincia
    let postalCode = '';
    let country = '';

    addressComponents.forEach((component: any) => {
      const types = component.types;

      if (types.includes('street_number')) {
        streetNumber = component.long_name;
      }
      if (types.includes('route')) {
        route = component.long_name;
      }
      if (types.includes('neighborhood')) {
        neighborhood = component.long_name;
      }
      if (types.includes('sublocality')) {
        sublocality = component.long_name;
      }
      if (types.includes('sublocality_level_1')) {
        sublocalityLevel1 = component.long_name;
      }
      if (types.includes('locality')) {
        locality = component.long_name;
      }
      if (types.includes('administrative_area_level_1')) {
        administrativeAreaLevel1 = component.long_name;
      }
      if (types.includes('administrative_area_level_2')) {
        administrativeAreaLevel2 = component.long_name;
      }
      if (types.includes('postal_code')) {
        postalCode = component.long_name;
      }
      if (types.includes('country')) {
        country = component.long_name;
      }
    });

    // Construir dirección formateada EXACTA Y COMPLETA (incluye código postal)
    let formattedAddress = '';

    // NIVEL 1: Calle completa con todos los detalles (MÁS COMPLETO)
    if (route) {
      // Calle + Número (el route ya incluye el tipo: "Avenida", "Jirón", "Calle")
      formattedAddress = route;
      if (streetNumber) {
        formattedAddress += ` ${streetNumber}`;
      }

      // Agregar distrito (priorizar sublocality > neighborhood)
      const distrito = sublocalityLevel1 || sublocality || neighborhood;
      if (distrito) {
        formattedAddress += `, ${distrito}`;
      }

      // Agregar ciudad
      if (locality && locality !== distrito) {
        formattedAddress += `, ${locality}`;
      }

      // IMPORTANTE: Agregar código postal si existe
      if (postalCode) {
        formattedAddress += ` ${postalCode}`;
      }

      // Agregar provincia si existe y es diferente de la ciudad
      if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
        formattedAddress += `, ${administrativeAreaLevel2}`;
      }
    }
    // NIVEL 2: Distrito + Ciudad + Postal (cuando no hay calle específica)
    else if (sublocalityLevel1 || sublocality || neighborhood) {
      const distrito = sublocalityLevel1 || sublocality || neighborhood;
      formattedAddress = distrito;

      if (locality && locality !== distrito) {
        formattedAddress += `, ${locality}`;
      }

      // IMPORTANTE: Agregar código postal
      if (postalCode) {
        formattedAddress += ` ${postalCode}`;
      }

      // Agregar provincia
      if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
        formattedAddress += `, ${administrativeAreaLevel2}`;
      }
    }
    // NIVEL 3: Ciudad + Postal + Provincia (cuando solo hay ciudad)
    else if (locality) {
      // Buscar en resultados adicionales si hay alguna calle cercana
      let nearbyStreet = '';

      for (let i = 1; i < Math.min(response.results.length, 10); i++) {
        const altResult = response.results[i];
        const altComponents = altResult.address_components;

        // Buscar si este resultado tiene route (calle)
        const altRoute = altComponents.find((c: any) => c.types.includes('route'));

        if (altRoute) {
          nearbyStreet = altRoute.long_name;
          break; // Usar la primera calle que encontremos
        }
      }

      // Construir dirección con calle cercana si se encontró
      if (nearbyStreet) {
        formattedAddress = `Cerca de ${nearbyStreet}, ${locality}`;
      } else {
        formattedAddress = locality;
      }

      // IMPORTANTE: Agregar código postal
      if (postalCode) {
        formattedAddress += ` ${postalCode}`;
      }

      if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
        formattedAddress += `, ${administrativeAreaLevel2}`;
      }
    }
    // NIVEL 4: Fallback - usar formatted_address completo PERO filtrar solo Plus Codes y país
    else {
      const addressParts = result.formatted_address.split(',').map(part => part.trim());

      // SOLO filtrar Plus Codes y país (NO filtrar código postal)
      const validParts = addressParts.filter(part => {
        const isPlusCode = /^[A-Z0-9]{4,8}\+[A-Z0-9]{2,3}$/.test(part);
        const isCountry = part === 'Perú' || part === 'Peru';
        return !isPlusCode && !isCountry;
      });

      // Tomar TODOS los elementos válidos para máxima completitud
      formattedAddress = validParts.join(', ') || locality || administrativeAreaLevel2 || 'Lima, Perú';
    }

    // Obtener coordenadas (pueden ser funciones en Google Maps API o valores directos)
    const finalLat = typeof result.geometry.location.lat === 'function'
      ? (result.geometry.location.lat as () => number)()
      : (result.geometry.location.lat as unknown as number);
    const finalLng = typeof result.geometry.location.lng === 'function'
      ? (result.geometry.location.lng as () => number)()
      : (result.geometry.location.lng as unknown as number);

    const finalResult: GeocodeResult = {
      formattedAddress,
      fullAddress: result.formatted_address,
      coordinates: { lat: finalLat, lng: finalLng },
    };

    return finalResult;
  } catch (error) {
    return null;
  }
}

/**
 * Función helper para transformar Place (nueva API) a GeocodeResult
 */
function transformPlaceToGeocodeResult(place: google.maps.places.Place): GeocodeResult {
  const addressComponents = place.addressComponents || [];

  // Extraer TODOS los componentes de dirección para máxima precisión
  let streetNumber = '';
  let route = '';
  let neighborhood = '';
  let sublocality = '';
  let sublocalityLevel1 = '';
  let locality = '';
  let administrativeAreaLevel1 = ''; // Región
  let administrativeAreaLevel2 = ''; // Provincia
  let postalCode = '';
  let country = '';

  addressComponents.forEach((component) => {
    const types = component.types;
    if (types.includes('street_number')) {
      streetNumber = component.longText || '';
    }
    if (types.includes('route')) {
      route = component.longText || '';
    }
    if (types.includes('neighborhood')) {
      neighborhood = component.longText || '';
    }
    if (types.includes('sublocality')) {
      sublocality = component.longText || '';
    }
    if (types.includes('sublocality_level_1')) {
      sublocalityLevel1 = component.longText || '';
    }
    if (types.includes('locality')) {
      locality = component.longText || '';
    }
    if (types.includes('administrative_area_level_1')) {
      administrativeAreaLevel1 = component.longText || '';
    }
    if (types.includes('administrative_area_level_2')) {
      administrativeAreaLevel2 = component.longText || '';
    }
    if (types.includes('postal_code')) {
      postalCode = component.longText || '';
    }
    if (types.includes('country')) {
      country = component.longText || '';
    }
  });

  // Construir dirección formateada EXACTA Y COMPLETA (incluye código postal)
  let formattedAddress = '';

  // NIVEL 1: Calle completa con todos los detalles (MÁS COMPLETO)
  if (route) {
    // Calle + Número (el route ya incluye el tipo: "Avenida", "Jirón", "Calle")
    formattedAddress = route;
    if (streetNumber) {
      formattedAddress += ` ${streetNumber}`;
    }

    // Agregar distrito (priorizar sublocality > neighborhood)
    const distrito = sublocalityLevel1 || sublocality || neighborhood;
    if (distrito) {
      formattedAddress += `, ${distrito}`;
    }

    // Agregar ciudad
    if (locality && locality !== distrito) {
      formattedAddress += `, ${locality}`;
    }

    // IMPORTANTE: Agregar código postal si existe
    if (postalCode) {
      formattedAddress += ` ${postalCode}`;
    }

    // Agregar provincia si existe y es diferente de la ciudad
    if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
      formattedAddress += `, ${administrativeAreaLevel2}`;
    }
  }
  // NIVEL 2: Distrito + Ciudad + Postal (cuando no hay calle específica)
  else if (sublocalityLevel1 || sublocality || neighborhood) {
    const distrito = sublocalityLevel1 || sublocality || neighborhood;
    formattedAddress = distrito;

    if (locality && locality !== distrito) {
      formattedAddress += `, ${locality}`;
    }

    // IMPORTANTE: Agregar código postal
    if (postalCode) {
      formattedAddress += ` ${postalCode}`;
    }

    // Agregar provincia
    if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
      formattedAddress += `, ${administrativeAreaLevel2}`;
    }
  }
  // NIVEL 3: Ciudad + Postal + Provincia (cuando solo hay ciudad)
  else if (locality) {
    formattedAddress = locality;

    // IMPORTANTE: Agregar código postal
    if (postalCode) {
      formattedAddress += ` ${postalCode}`;
    }

    if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
      formattedAddress += `, ${administrativeAreaLevel2}`;
    }
  }
  // NIVEL 4: Fallback - usar formatted_address completo PERO filtrar solo Plus Codes y país
  else {
    const addressParts = (place.formattedAddress || '').split(',').map(part => part.trim());

    // SOLO filtrar Plus Codes y país (NO filtrar código postal)
    const validParts = addressParts.filter(part => {
      const isPlusCode = /^[A-Z0-9]{4,8}\+[A-Z0-9]{2,3}$/.test(part);
      const isCountry = part === 'Perú' || part === 'Peru';
      return !isPlusCode && !isCountry;
    });

    // Tomar TODOS los elementos válidos para máxima completitud
    formattedAddress = validParts.join(', ') || locality || administrativeAreaLevel2 || 'Lima, Perú';
  }

  return {
    formattedAddress,
    fullAddress: place.formattedAddress || '',
    coordinates: {
      lat: place.location?.lat() || 0,
      lng: place.location?.lng() || 0
    }
  };
}

/**
 * Busca lugares/direcciones usando Google Maps Geocoder API
 * Usamos Geocoder porque Places API (legacy y nueva) está bloqueada para nuevos clientes desde marzo 2025
 */
export async function searchPlaces(query: string): Promise<GeocodeResult[]> {
  if (query.length < 3) {
    return [];
  }

  // Verificar que Google Maps JavaScript API esté cargada
  if (typeof window === 'undefined' || !window.google?.maps) {
    return [];
  }

  try {
    // Usar Geocoder API (funciona sin Places API)
    const geocoder = new google.maps.Geocoder();

    const response = await geocoder.geocode({
      address: query,
      componentRestrictions: { country: 'PE' },
      language: 'es',
      region: 'PE'
    });

    if (!response.results || response.results.length === 0) {
      return [];
    }

    // Transformar resultados de Geocoder a GeocodeResult
    const results = response.results.slice(0, 10).map((result) => {
      return transformGeocoderResultToGeocodeResult(result);
    });

    return results;
  } catch (error) {
    return [];
  }
}

/**
 * Transforma GeocoderResult a GeocodeResult
 */
function transformGeocoderResultToGeocodeResult(result: google.maps.GeocoderResult): GeocodeResult {
  const addressComponents = result.address_components || [];

  // Extraer componentes de dirección
  let streetNumber = '';
  let route = '';
  let neighborhood = '';
  let sublocality = '';
  let sublocalityLevel1 = '';
  let locality = '';
  let administrativeAreaLevel2 = '';
  let postalCode = '';

  addressComponents.forEach((component) => {
    const types = component.types;
    if (types.includes('street_number')) streetNumber = component.long_name;
    if (types.includes('route')) route = component.long_name;
    if (types.includes('neighborhood')) neighborhood = component.long_name;
    if (types.includes('sublocality')) sublocality = component.long_name;
    if (types.includes('sublocality_level_1')) sublocalityLevel1 = component.long_name;
    if (types.includes('locality')) locality = component.long_name;
    if (types.includes('administrative_area_level_2')) administrativeAreaLevel2 = component.long_name;
    if (types.includes('postal_code')) postalCode = component.long_name;
  });

  // Construir dirección formateada
  let formattedAddress = '';

  if (route) {
    formattedAddress = route;
    if (streetNumber) formattedAddress += ` ${streetNumber}`;
    const distrito = sublocalityLevel1 || sublocality || neighborhood;
    if (distrito) formattedAddress += `, ${distrito}`;
    if (locality && locality !== distrito) formattedAddress += `, ${locality}`;
    if (postalCode) formattedAddress += ` ${postalCode}`;
    if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
      formattedAddress += `, ${administrativeAreaLevel2}`;
    }
  } else if (sublocalityLevel1 || sublocality || neighborhood) {
    const distrito = sublocalityLevel1 || sublocality || neighborhood;
    formattedAddress = distrito;
    if (locality && locality !== distrito) formattedAddress += `, ${locality}`;
    if (postalCode) formattedAddress += ` ${postalCode}`;
    if (administrativeAreaLevel2 && administrativeAreaLevel2 !== locality) {
      formattedAddress += `, ${administrativeAreaLevel2}`;
    }
  } else {
    // Usar formatted_address filtrando Plus Codes y país
    const addressParts = result.formatted_address.split(',').map(part => part.trim());
    const validParts = addressParts.filter(part => {
      const isPlusCode = /^[A-Z0-9]{4,8}\+[A-Z0-9]{2,3}$/.test(part);
      const isCountry = part === 'Perú' || part === 'Peru';
      return !isPlusCode && !isCountry;
    });
    formattedAddress = validParts.join(', ') || locality || 'Lima, Perú';
  }

  // Obtener coordenadas
  const lat = typeof result.geometry.location.lat === 'function'
    ? result.geometry.location.lat()
    : (result.geometry.location.lat as unknown as number);
  const lng = typeof result.geometry.location.lng === 'function'
    ? result.geometry.location.lng()
    : (result.geometry.location.lng as unknown as number);

  return {
    formattedAddress,
    fullAddress: result.formatted_address,
    coordinates: { lat, lng },
    locality,
    district: sublocalityLevel1 || sublocality || neighborhood,
    city: locality,
  };
}
