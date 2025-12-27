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
  // LOG 1: Coordenadas de entrada
  console.log('📍 [GEOCODING] Iniciando reverse geocoding con coordenadas:', { lat, lng });

  // Verificar que Google Maps JavaScript API esté cargada
  if (typeof window === 'undefined' || !window.google?.maps) {
    console.error('❌ [GEOCODING] Google Maps JavaScript API no está cargada');
    return null;
  }

  try {
    // Usar Geocoder JavaScript API (moderna - sin CORS, sin warnings)
    const geocoder = new google.maps.Geocoder();
    const location = { lat, lng };

    console.log('🔍 [GEOCODING] Llamando a Google Geocoder API...');

    // Llamada con Promise (versión moderna)
    const response = await geocoder.geocode({
      location: location,
      language: 'es',
      region: 'PE'
    });

    // LOG 2: Respuesta completa de Google
    console.log('✅ [GEOCODING] Respuesta recibida de Google:', {
      totalResults: response.results?.length || 0,
      firstResult: response.results?.[0]
    });

    if (!response.results || response.results.length === 0) {
      console.error('❌ [GEOCODING] No se encontraron resultados para las coordenadas:', { lat, lng });
      return null;
    }

    const result = response.results[0];
    const addressComponents = result.address_components;

    // LOG 3: Dirección formateada de Google (antes de procesamiento)
    console.log('📝 [GEOCODING] Dirección formateada original de Google:', result.formatted_address);

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

    // LOG 4: Empezar a extraer componentes
    console.log('🔍 [GEOCODING] Extrayendo componentes de dirección...');

    addressComponents.forEach((component: any) => {
      const types = component.types;

      if (types.includes('street_number')) {
        streetNumber = component.long_name;
        console.log('  ✓ street_number:', streetNumber);
      }
      if (types.includes('route')) {
        route = component.long_name;
        console.log('  ✓ route:', route);
      }
      if (types.includes('neighborhood')) {
        neighborhood = component.long_name;
        console.log('  ✓ neighborhood:', neighborhood);
      }
      if (types.includes('sublocality')) {
        sublocality = component.long_name;
        console.log('  ✓ sublocality:', sublocality);
      }
      if (types.includes('sublocality_level_1')) {
        sublocalityLevel1 = component.long_name;
        console.log('  ✓ sublocality_level_1:', sublocalityLevel1);
      }
      if (types.includes('locality')) {
        locality = component.long_name;
        console.log('  ✓ locality:', locality);
      }
      if (types.includes('administrative_area_level_1')) {
        administrativeAreaLevel1 = component.long_name;
        console.log('  ✓ administrative_area_level_1:', administrativeAreaLevel1);
      }
      if (types.includes('administrative_area_level_2')) {
        administrativeAreaLevel2 = component.long_name;
        console.log('  ✓ administrative_area_level_2:', administrativeAreaLevel2);
      }
      if (types.includes('postal_code')) {
        postalCode = component.long_name;
        console.log('  ✓ postal_code:', postalCode);
      }
      if (types.includes('country')) {
        country = component.long_name;
        console.log('  ✓ country:', country);
      }
    });

    // LOG 5: Resumen de componentes extraídos
    console.log('📦 [GEOCODING] Resumen de componentes extraídos:', {
      streetNumber,
      route,
      neighborhood,
      sublocality,
      sublocalityLevel1,
      locality,
      administrativeAreaLevel1,
      administrativeAreaLevel2,
      postalCode,
      country
    });

    // Construir dirección formateada EXACTA Y COMPLETA (incluye código postal)
    let formattedAddress = '';

    console.log('🏗️ [GEOCODING] Iniciando construcción de dirección por niveles de prioridad...');

    // NIVEL 1: Calle completa con todos los detalles (MÁS COMPLETO)
    if (route) {
      console.log('✅ [GEOCODING] Usando NIVEL 1 (Calle completa con route)');
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
      console.log('✅ [GEOCODING] Usando NIVEL 2 (Distrito sin calle específica)');
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
      console.log('✅ [GEOCODING] Usando NIVEL 3 (Solo ciudad)');

      // 🔍 NUEVO: Buscar en resultados adicionales si hay alguna calle cercana
      let nearbyStreet = '';
      console.log('🔍 [GEOCODING] Buscando calles cercanas en otros resultados de Google...');

      for (let i = 1; i < Math.min(response.results.length, 10); i++) {
        const altResult = response.results[i];
        const altComponents = altResult.address_components;

        // Buscar si este resultado tiene route (calle)
        const altRoute = altComponents.find((c: any) => c.types.includes('route'));

        if (altRoute) {
          nearbyStreet = altRoute.long_name;
          console.log(`✅ [GEOCODING] Encontrada calle cercana en resultado #${i}: ${nearbyStreet}`);
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
      console.log('⚠️ [GEOCODING] Usando NIVEL 4 (Fallback con formatted_address)');
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

    // LOG 6: Dirección final construida
    // Obtener coordenadas (pueden ser funciones en Google Maps API o valores directos)
    const finalLat = typeof result.geometry.location.lat === 'function'
      ? (result.geometry.location.lat as () => number)()
      : (result.geometry.location.lat as unknown as number);
    const finalLng = typeof result.geometry.location.lng === 'function'
      ? (result.geometry.location.lng as () => number)()
      : (result.geometry.location.lng as unknown as number);

    console.log('🎯 [GEOCODING] Dirección final construida:', formattedAddress);
    console.log('📍 [GEOCODING] Coordenadas finales:', { lat: finalLat, lng: finalLng });

    const finalResult: GeocodeResult = {
      formattedAddress,
      fullAddress: result.formatted_address,
      coordinates: { lat: finalLat, lng: finalLng },
    };

    console.log('✅ [GEOCODING] Resultado completo:', finalResult);

    return finalResult;
  } catch (error) {
    console.error('❌ [GEOCODING ERROR] Error al hacer reverse geocoding:', error);
    console.error('❌ [GEOCODING ERROR] Stack trace:', error instanceof Error ? error.stack : 'No stack trace');
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
 * Busca lugares/direcciones usando Google Maps JavaScript API (AutocompleteSuggestion - API moderna)
 * Funciona desde el cliente sin problemas de CORS y sin warnings deprecados
 */
export async function searchPlaces(query: string): Promise<GeocodeResult[]> {
  console.log('🔍 [SEARCH] Iniciando búsqueda de lugares con query:', query);

  if (query.length < 3) {
    console.log('⚠️ [SEARCH] Query muy corto (< 3 caracteres), cancelando búsqueda');
    return [];
  }

  // Verificar que Google Maps JavaScript API esté cargada
  if (typeof window === 'undefined' || !window.google?.maps?.places) {
    console.error('❌ [SEARCH] Google Maps JavaScript API no está cargada');
    return [];
  }

  try {
    // PASO 1: Crear session token para la API (requerido para billing)
    const sessionToken = new google.maps.places.AutocompleteSessionToken();

    // PASO 2: Obtener sugerencias usando AutocompleteSuggestion (API MODERNA - sin warnings)
    const request = {
      input: query,
      region: 'pe', // Restringir resultados a Perú
      includedPrimaryTypes: ['geocode'],
      language: 'es',
      sessionToken: sessionToken
    };

    const { suggestions } = await google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request);

    if (!suggestions || suggestions.length === 0) {
      console.log(`⚠️ [SEARCH] No se encontraron resultados para "${query}"`);
      return [];
    }

    console.log(`✅ [SEARCH] Encontrados ${suggestions.length} resultados para "${query}"`);

    // PASO 3: Para cada sugerencia, obtener detalles completos usando Place API (MODERNA)
    const results = await Promise.all(
      suggestions.slice(0, 10).map(async (suggestion) => {
        try {
          // Verificar que placePrediction existe
          if (!suggestion.placePrediction?.placeId) {
            console.warn('⚠️ Sugerencia sin placeId:', suggestion);
            return null;
          }

          // Crear instancia de Place con el placeId
          const place = new google.maps.places.Place({
            id: suggestion.placePrediction.placeId
          });

          // Obtener campos necesarios
          await place.fetchFields({
            fields: ['displayName', 'formattedAddress', 'location', 'addressComponents']
          });

          // Transformar a GeocodeResult
          return transformPlaceToGeocodeResult(place);
        } catch (error) {
          console.error('❌ Error obteniendo detalles del lugar:', error);
          return null;
        }
      })
    );

    // Filtrar resultados nulos
    return results.filter((result): result is GeocodeResult => result !== null);
  } catch (error) {
    console.error('❌ Error al buscar lugares con Places API:', error);
    return [];
  }
}
