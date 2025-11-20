// Servicio especializado para manejar datos de la aplicación en Firestore
// OPTIMIZADO: Divide datos en colecciones separadas para evitar límite de 1MB por documento
import { saveDocument, getDocument, subscribeToDocument, getFirebaseFirestore } from './firebase';
import { AppData } from './placeholder-data';
import { optimizeAndUploadImage, generateUniqueFileName } from './image-optimizer';

// Nombres de colecciones en Firestore
const COLLECTIONS = {
  APP_DATA: 'appData', // Para metadata mínima (vacío o casi vacío)
  APP_SETTINGS: 'appSettings', // Para configuración de la aplicación (settings completos)
  VENDORS: 'vendors', // Colección para vendors (puede crecer mucho)
  VENDOR_IMAGES: 'vendorImages', // Colección separada para imágenes base64 de vendors (evita exceder 1MB)
  USERS: 'users', // Colección para usuarios
  USER_SETTINGS: 'userSettings', // Colección para configuraciones de usuario (rol activo, preferencias)
  DRIVERS: 'drivers', // Colección para conductores
  ORDERS: 'orders', // Colección para órdenes
  CATEGORIES: 'categories', // Colección para categorías
  CITIES: 'cities', // Colección para ciudades
  DELIVERY_ZONES: 'deliveryZones', // Colección para zonas de entrega
  ADMINS: 'admins', // Colección para administradores
  MESSAGES: 'messages', // Colección para mensajes
  FAVORS: 'favors', // Colección para favores
  PROMOTIONAL_BANNERS: 'promotionalBanners', // Colección para banners promocionales
  ANNOUNCEMENT_BANNERS: 'announcementBanners', // Colección para banners de anuncios
  BANK_ACCOUNTS: 'bankAccounts', // Colección para cuentas bancarias
  QR_PAYMENTS: 'qrPayments' // Colección para pagos QR
} as const;

// ID del documento principal que contiene solo metadata y configuración ligera
const MAIN_DOC_ID = 'main';

/**
 * Limpia datos para Firestore eliminando propiedades inválidas
 * (undefined, funciones, referencias circulares, tipos no soportados)
 * También convierte tipos especiales de Firestore a objetos planos
 */
function sanitizeForFirestore(obj: any, seen = new WeakSet()): any {
  // Manejar valores primitivos y null
  if (obj === null || obj === undefined) {
    return null;
  }

  // Manejar funciones
  if (typeof obj === 'function') {
    return null;
  }

  // Manejar tipos primitivos
  if (typeof obj !== 'object') {
    return obj;
  }

  // Detectar referencias circulares
  if (seen.has(obj)) {
    console.warn('Referencia circular detectada, omitiendo...');
    return null;
  }
  seen.add(obj);

  // Manejar Date (convertir a ISO string)
  if (obj instanceof Date) {
    return obj.toISOString();
  }

  // Manejar tipos especiales de Firestore (GeoPoint, Timestamp, DocumentReference)
  // Convertirlos a objetos planos simples
  if (obj.constructor && obj.constructor.name) {
    const constructorName = obj.constructor.name;

    // GeoPoint de Firestore
    if (constructorName === 'GeoPoint' && 'latitude' in obj && 'longitude' in obj) {
      return {
        lat: obj.latitude,
        lng: obj.longitude
      };
    }

    // Timestamp de Firestore
    if (constructorName === 'Timestamp' && 'toDate' in obj) {
      return obj.toDate().toISOString();
    }

    // DocumentReference de Firestore (convertir a string con path)
    if (constructorName === 'DocumentReference' && 'path' in obj) {
      return obj.path;
    }
  }

  // Manejar tipos especiales que Firestore no soporta
  if (obj instanceof Map || obj instanceof Set || obj instanceof WeakMap || obj instanceof WeakSet) {
    console.warn('Tipo no soportado detectado (Map/Set/WeakMap/WeakSet), omitiendo...');
    return null;
  }

  // Manejar arrays
  if (Array.isArray(obj)) {
    return obj
      .map(item => sanitizeForFirestore(item, seen))
      .filter(item => item !== null && item !== undefined);
  }

  // Manejar objetos planos
  const cleaned: any = {};

  try {
    for (const key in obj) {
      // Solo propiedades propias, no heredadas
      if (!obj.hasOwnProperty(key)) {
        continue;
      }

      const value = obj[key];

      // Omitir undefined, funciones y símbolos
      if (value === undefined || typeof value === 'function' || typeof value === 'symbol') {
        continue;
      }

      // Recursivamente limpiar objetos y arrays anidados
      const sanitized = sanitizeForFirestore(value, seen);

      // Solo agregar si no es null o undefined después de limpiar
      if (sanitized !== null && sanitized !== undefined) {
        cleaned[key] = sanitized;
      }
    }
  } catch (error) {
    console.error('Error al sanitizar objeto:', error);
    return null;
  }

  return cleaned;
}

/**
 * Convierte cualquier dato a formato JSON plano compatible con Firestore
 * Maneja objetos complejos convirtiéndolos a JSON puro
 */
function deepCloneForFirestore(obj: any): any {
  try {
    // Primero convertir a JSON y de vuelta para eliminar cualquier objeto no serializable
    const jsonString = JSON.stringify(obj, (key, value) => {
      // Eliminar funciones y undefined
      if (typeof value === 'function' || value === undefined) {
        return null;
      }
      // Convertir Date a ISO string
      if (value instanceof Date) {
        return value.toISOString();
      }
      return value;
    });

    return JSON.parse(jsonString);
  } catch (error) {
    console.error('Error al convertir a JSON:', error);
    return null;
  }
}

/**
 * Constantes para chunking
 */
const MAX_CHUNK_SIZE_BYTES = 800000; // 800KB - margen de seguridad bajo el límite de 1MB
const MAX_ITEMS_PER_CHUNK = 100; // Máximo de elementos por chunk para arrays grandes

/**
 * Divide un array en chunks seguros para Firestore
 * Garantiza que cada chunk no exceda MAX_CHUNK_SIZE_BYTES
 * NOTA: Si un item individual excede el límite, este debe ser tratado de forma especial
 */
function chunkArray<T>(array: T[], maxSizeBytes: number = MAX_CHUNK_SIZE_BYTES): T[][] {
  if (!Array.isArray(array) || array.length === 0) {
    return [array];
  }

  const chunks: T[][] = [];
  let currentChunk: T[] = [];
  let currentSize = 0;

  for (const item of array) {
    // Estimar tamaño del item en bytes
    const itemSize = JSON.stringify(item).length;

    // ⚠️ ADVERTENCIA: Si el item por sí solo excede el límite, se agrega solo
    // Esto causará error si no se maneja la separación de datos grandes (imágenes) ANTES
    if (itemSize > maxSizeBytes) {
      console.warn(`⚠️ [CHUNKING] Item individual excede ${(maxSizeBytes/1024).toFixed(0)}KB (${(itemSize/1024).toFixed(2)}KB). Esto puede fallar.`);
    }

    // Si agregar este item excede el límite, crear nuevo chunk
    if (currentSize + itemSize > maxSizeBytes && currentChunk.length > 0) {
      chunks.push(currentChunk);
      currentChunk = [item];
      currentSize = itemSize;
    } else {
      currentChunk.push(item);
      currentSize += itemSize;
    }
  }

  // Agregar el último chunk si tiene elementos
  if (currentChunk.length > 0) {
    chunks.push(currentChunk);
  }

  return chunks.length > 0 ? chunks : [[]];
}

/**
 * Separa las imágenes base64 de un vendor para almacenarlas independientemente
 * Evita que vendors con imágenes grandes excedan el límite de 1MB
 */
function extractVendorImages(vendor: any): { vendor: any; images: any } {
  const {logoUrl, bannerUrl, ...vendorWithoutImages} = vendor;

  return {
    vendor: vendorWithoutImages,
    images: {
      logoUrl: logoUrl || null,
      bannerUrl: bannerUrl || null
    }
  };
}

/**
 * Reconstruye un vendor combinando sus datos con sus imágenes
 */
function reconstructVendor(vendorData: any, imagesData: any): any {
  return {
    ...vendorData,
    logoUrl: imagesData?.logoUrl || '',
    bannerUrl: imagesData?.bannerUrl || ''
  };
}

/**
 * Guarda vendors separando sus imágenes en una colección aparte
 * Esto evita que vendors con imágenes base64 grandes excedan el límite de 1MB
 */
async function saveVendorsWithImages(
  vendors: any[]
): Promise<{ success: boolean; error?: string }> {
  try {
    console.log(`🏪 [VENDORS] Guardando ${vendors.length} vendors con imágenes separadas`);

    // Separar vendors e imágenes
    const vendorsWithoutImages: any[] = [];
    const vendorImagesMap: Record<string, any> = {};

    for (const vendor of vendors) {
      const { vendor: vendorData, images } = extractVendorImages(vendor);
      vendorsWithoutImages.push(vendorData);
      if (vendor.id) {
        vendorImagesMap[vendor.id] = images;
      }
    }

    // Guardar vendors sin imágenes usando chunking
    const vendorsResult = await saveArrayWithChunking(COLLECTIONS.VENDORS, vendorsWithoutImages);
    if (!vendorsResult.success) {
      return vendorsResult;
    }

    // Guardar imágenes de cada vendor por separado
    const imageResults = await Promise.all(
      Object.entries(vendorImagesMap).map(([vendorId, images]) =>
        saveDocument(COLLECTIONS.VENDOR_IMAGES, vendorId, {
          logoUrl: images.logoUrl || '',
          bannerUrl: images.bannerUrl || '',
          lastUpdated: new Date().toISOString()
        })
      )
    );

    const failedImage = imageResults.find(r => !r.success);
    if (failedImage) {
      return { success: false, error: `Error al guardar imágenes: ${failedImage.error}` };
    }

    console.log(`✅ [VENDORS] Guardados ${vendors.length} vendors y ${Object.keys(vendorImagesMap).length} sets de imágenes`);
    return { success: true };
  } catch (error: any) {
    console.error('Error al guardar vendors con imágenes:', error);
    return {
      success: false,
      error: error.message || 'Error al guardar vendors'
    };
  }
}

/**
 * Carga vendors reconstruyéndolos con sus imágenes desde colecciones separadas
 */
async function loadVendorsWithImages(fallback: any[]): Promise<any[]> {
  try {
    console.log(`🏪 [VENDORS] Cargando vendors con imágenes...`);

    // Cargar vendors sin imágenes
    const vendorsWithoutImages = await loadArrayFromChunks(COLLECTIONS.VENDORS, []);

    if (vendorsWithoutImages.length === 0) {
      console.log(`⚠️ [VENDORS] No se encontraron vendors, usando fallback`);
      return fallback;
    }

    // Cargar imágenes de cada vendor
    const vendorIds = vendorsWithoutImages.map((v: any) => v.id).filter(Boolean);

    const imagesPromises = vendorIds.map((id: string) =>
      getDocument(COLLECTIONS.VENDOR_IMAGES, id)
    );

    const imagesResults = await Promise.all(imagesPromises);

    // Reconstruir vendors con sus imágenes
    const reconstructedVendors = vendorsWithoutImages.map((vendor: any, index: number) => {
      const imagesResult = imagesResults[index];
      const images = imagesResult.success && imagesResult.data ? imagesResult.data : {};
      return reconstructVendor(vendor, images);
    });

    console.log(`✅ [VENDORS] ${reconstructedVendors.length} vendors reconstruidos con imágenes`);
    return reconstructedVendors;
  } catch (error) {
    console.error('Error al cargar vendors con imágenes:', error);
    return fallback;
  }
}

/**
 * Detecta si una URL es base64
 */
function isBase64Image(url: string): boolean {
  return url.startsWith('data:image/') || url.startsWith('data:application/');
}

/**
 * Procesa banners optimizando imágenes base64 y subiéndolas a Storage
 * @param banners Array de banners a procesar
 * @param folderPrefix Prefijo de carpeta en Storage (ej: 'promotional-banners')
 * @returns Banners con URLs de Storage en lugar de base64
 */
async function processBannersWithStorage(
  banners: any[],
  folderPrefix: string
): Promise<{ success: boolean; banners?: any[]; error?: string }> {
  try {
    console.log(`🎨 [BANNERS] Procesando ${banners.length} banners (${folderPrefix})...`);

    const processedBanners = await Promise.all(
      banners.map(async (banner, index) => {
        // Si la imagen no es base64, retornar el banner sin cambios
        if (!banner.imageUrl || !isBase64Image(banner.imageUrl)) {
          console.log(`  ℹ️ Banner ${banner.id}: ya tiene URL de Storage, omitiendo`);
          return banner;
        }

        // Optimizar y subir imagen a Storage
        console.log(`  🔄 Banner ${banner.id}: optimizando imagen base64...`);

        const fileName = generateUniqueFileName(`${folderPrefix}/${banner.id}`, 'webp');
        const uploadResult = await optimizeAndUploadImage(
          banner.imageUrl,
          fileName,
          {
            maxWidth: 1920,
            maxHeight: 1080,
            quality: 0.85, // Alta calidad para banners
            format: 'webp'
          }
        );

        if (!uploadResult.success) {
          console.error(`  ❌ Banner ${banner.id}: error al subir - ${uploadResult.error}`);
          // Retornar banner original en caso de error
          return banner;
        }

        console.log(`  ✅ Banner ${banner.id}: imagen optimizada y subida`);
        if (uploadResult.stats) {
          const reduction = ((1 - uploadResult.stats.optimizedSize / uploadResult.stats.originalSize) * 100).toFixed(1);
          console.log(`     Reducción: ${reduction}% (${(uploadResult.stats.originalSize / 1024).toFixed(0)}KB → ${(uploadResult.stats.optimizedSize / 1024).toFixed(0)}KB)`);
        }

        // Retornar banner con URL de Storage
        return {
          ...banner,
          imageUrl: uploadResult.url || banner.imageUrl
        };
      })
    );

    console.log(`✅ [BANNERS] ${processedBanners.length} banners procesados (${folderPrefix})`);

    return {
      success: true,
      banners: processedBanners
    };
  } catch (error: any) {
    console.error(`Error al procesar banners (${folderPrefix}):`, error);
    return {
      success: false,
      error: error.message || 'Error al procesar banners'
    };
  }
}

/**
 * Guarda un array en Firestore dividiéndolo en chunks si es necesario
 * @param collection Nombre de la colección
 * @param data Array de datos a guardar
 * @returns Resultado de la operación
 */
async function saveArrayWithChunking(
  collection: string,
  data: any[]
): Promise<{ success: boolean; error?: string }> {
  try {
    // Dividir en chunks
    const chunks = chunkArray(data);

    // Guardar metadata con número total de chunks
    const metaResult = await saveDocument(collection, 'meta', {
      totalChunks: chunks.length,
      totalItems: data.length,
      lastUpdated: new Date().toISOString()
    });

    if (!metaResult.success) {
      return metaResult;
    }

    // Guardar cada chunk
    const chunkResults = await Promise.all(
      chunks.map((chunk, index) =>
        saveDocument(collection, `chunk_${index}`, {
          data: JSON.stringify(chunk),
          chunkIndex: index,
          itemCount: chunk.length
        })
      )
    );

    // Verificar si algún chunk falló
    const failed = chunkResults.find(r => !r.success);
    if (failed) {
      return { success: false, error: failed.error };
    }

    return { success: true };
  } catch (error: any) {
    console.error(`Error al guardar ${collection} con chunking:`, error);
    return {
      success: false,
      error: error.message || 'Error al guardar con chunking'
    };
  }
}

/**
 * Carga un array desde Firestore reconstruyéndolo desde chunks
 * @param collection Nombre de la colección
 * @param fallback Valor por defecto si no hay datos
 * @returns Array reconstruido
 */
async function loadArrayFromChunks<T>(
  collection: string,
  fallback: T[]
): Promise<T[]> {
  try {
    // Cargar metadata
    const metaResult = await getDocument<{ totalChunks: number; totalItems: number }>(
      collection,
      'meta'
    );

    if (!metaResult.success || !metaResult.data) {
      return fallback;
    }

    const { totalChunks } = metaResult.data;

    // Cargar todos los chunks en paralelo
    const chunkPromises = Array.from({ length: totalChunks }, (_, index) =>
      getDocument<{ data: string; itemCount: number }>(collection, `chunk_${index}`)
    );

    const chunkResults = await Promise.all(chunkPromises);

    // Reconstruir array completo
    const reconstructedArray: T[] = [];

    for (let i = 0; i < chunkResults.length; i++) {
      const result = chunkResults[i];

      if (result.success && result.data?.data) {
        try {
          const chunkData = JSON.parse(result.data.data) as T[];
          reconstructedArray.push(...chunkData);
        } catch (error) {
          console.error(`Error al parsear chunk ${i} de ${collection}:`, error);
        }
      }
      // Chunk vacío es comportamiento normal, no mostrar warning
    }
    return reconstructedArray.length > 0 ? reconstructedArray : fallback;
  } catch (error) {
    console.error(`Error al cargar ${collection} desde chunks:`, error);
    return fallback;
  }
}

/**
 * Guarda todos los datos de la aplicación en Firestore usando colecciones separadas
 * @param data Datos completos de la aplicación
 */
export async function saveAppDataToFirestore(data: Omit<AppData, 'currentUser'>): Promise<{ success: boolean; error?: string }> {
  try {
    // Dividir datos en partes pequeñas para no exceder límite de 1MB
    // IMPORTANTE: Extraer TODAS las propiedades, dejando metadata vacío (solo para futuras propiedades pequeñas)
    const { vendors, users, drivers, orders, categories, cities, deliveryZones, admins, messages, favors, appSettings, ...metadata } = data;

    // Extraer los arrays grandes de appSettings
    const { promotionalBanners, announcementBanners, paymentMethods, ...coreSettings } = appSettings;
    const { bankAccounts, qrPayments, ...otherPaymentMethods } = paymentMethods;

    // Guardar appSettings SIN los arrays grandes (solo configuración básica)
    // NUEVA ESTRATEGIA: Guardar como JSON string en colección separada APP_SETTINGS
    const lightAppSettings = {
      ...coreSettings,
      paymentMethods: otherPaymentMethods
    };

    // Guardar lightAppSettings en su propia colección para evitar límite de 1MB en appData/main
    const settingsResult = await saveDocument(COLLECTIONS.APP_SETTINGS, 'data', {
      data: JSON.stringify(lightAppSettings)
    });

    if (!settingsResult.success) {
      console.error('Error al guardar appSettings:', settingsResult.error);
      return settingsResult;
    }

    // Guardar documento appData/main vacío (solo para mantener la colección, opcional)
    const metadataResult = await saveDocument(COLLECTIONS.APP_DATA, MAIN_DOC_ID, {
      initialized: true,
      lastUpdated: new Date().toISOString()
    });

    if (!metadataResult.success) {
      console.error('Error al guardar metadata:', metadataResult.error);
      return metadataResult;
    }

    // Guardar cada colección usando CHUNKING para evitar límite de tamaño
    // Las colecciones grandes se dividen automáticamente en fragmentos de <800KB
    // VENDORS usa función especializada que separa imágenes base64 en colección aparte
    console.log('🔄 Guardando colecciones con chunking...');

    // Procesar banners: optimizar imágenes base64 y subirlas a Storage
    console.log('🎨 Procesando banners con optimización de imágenes...');
    const promoResult = await processBannersWithStorage(promotionalBanners, 'promotional-banners');
    const announcementResult = await processBannersWithStorage(announcementBanners, 'announcement-banners');

    if (!promoResult.success || !announcementResult.success) {
      console.error('Error al procesar banners');
      return {
        success: false,
        error: promoResult.error || announcementResult.error || 'Error al procesar banners'
      };
    }

    const optimizedPromoBanners = promoResult.banners || promotionalBanners;
    const optimizedAnnouncementBanners = announcementResult.banners || announcementBanners;

    const results = await Promise.all([
      saveVendorsWithImages(vendors), // ✅ Función especializada para vendors con imágenes separadas
      saveArrayWithChunking(COLLECTIONS.USERS, users),
      saveArrayWithChunking(COLLECTIONS.DRIVERS, drivers),
      saveArrayWithChunking(COLLECTIONS.ORDERS, orders),
      saveArrayWithChunking(COLLECTIONS.CATEGORIES, categories),
      saveArrayWithChunking(COLLECTIONS.CITIES, cities),
      saveArrayWithChunking(COLLECTIONS.DELIVERY_ZONES, deliveryZones),
      saveArrayWithChunking(COLLECTIONS.ADMINS, admins),
      saveArrayWithChunking(COLLECTIONS.MESSAGES, messages),
      saveArrayWithChunking(COLLECTIONS.FAVORS, favors),
      // Guardar arrays de appSettings con imágenes ya optimizadas en Storage
      saveArrayWithChunking(COLLECTIONS.PROMOTIONAL_BANNERS, optimizedPromoBanners),
      saveArrayWithChunking(COLLECTIONS.ANNOUNCEMENT_BANNERS, optimizedAnnouncementBanners),
      saveArrayWithChunking(COLLECTIONS.BANK_ACCOUNTS, bankAccounts),
      saveArrayWithChunking(COLLECTIONS.QR_PAYMENTS, qrPayments)
    ]);

    // Verificar si alguna falló
    const failed = results.find(r => !r.success);
    if (failed) {
      console.error('Error al guardar colecciones:', failed.error);
      return { success: false, error: failed.error };
    }

    return { success: true };
  } catch (error: any) {
    console.error('Error al guardar datos en Firestore:', error);
    return {
      success: false,
      error: error.message || 'Error desconocido al guardar en Firestore'
    };
  }
}

/**
 * Carga todos los datos de la aplicación desde Firestore usando colecciones separadas
 * @param defaultData Datos por defecto a usar si no hay nada en Firestore
 */
export async function loadAppDataFromFirestore(
  defaultData: AppData
): Promise<{ success: boolean; data?: Omit<AppData, 'currentUser'>; error?: string }> {
  try {
    // Cargar metadata del documento principal
    const metadataResult = await getDocument<any>(COLLECTIONS.APP_DATA, MAIN_DOC_ID);

    if (!metadataResult.success) {
      // Si no hay datos, inicializar con defaults
      console.log('No se encontraron datos en Firestore, inicializando con datos por defecto...');
      const { currentUser, ...dataWithoutUser } = defaultData;
      await saveAppDataToFirestore(dataWithoutUser);
      return { success: true, data: dataWithoutUser };
    }

    // Cargar appSettings (que no usa chunking porque es pequeño)
    console.log('🔄 Cargando colecciones desde Firestore...');

    const settingsResult = await getDocument<{ data: string }>(COLLECTIONS.APP_SETTINGS, 'data');

    const parseJsonData = (result: { success: boolean; data?: { data: string } }, fallback: any) => {
      if (!result.success || !result.data?.data) return fallback;
      try {
        return JSON.parse(result.data.data);
      } catch (error) {
        console.error('Error al deserializar JSON:', error);
        return fallback;
      }
    };

    const loadedAppSettings = parseJsonData(settingsResult, {});

    // Cargar todas las colecciones usando CHUNKING en paralelo
    // VENDORS usa función especializada que reconstruye imágenes desde colección separada
    const [vendors, users, drivers, orders, categories, cities, deliveryZones, admins,
           messages, favors, promotionalBanners, announcementBanners, bankAccounts, qrPayments] = await Promise.all([
      loadVendorsWithImages(defaultData.vendors), // ✅ Función especializada para vendors con imágenes
      loadArrayFromChunks(COLLECTIONS.USERS, defaultData.users),
      loadArrayFromChunks(COLLECTIONS.DRIVERS, defaultData.drivers),
      loadArrayFromChunks(COLLECTIONS.ORDERS, defaultData.orders),
      loadArrayFromChunks(COLLECTIONS.CATEGORIES, defaultData.categories),
      loadArrayFromChunks(COLLECTIONS.CITIES, defaultData.cities),
      loadArrayFromChunks(COLLECTIONS.DELIVERY_ZONES, defaultData.deliveryZones),
      loadArrayFromChunks(COLLECTIONS.ADMINS, defaultData.admins),
      loadArrayFromChunks(COLLECTIONS.MESSAGES, defaultData.messages),
      loadArrayFromChunks(COLLECTIONS.FAVORS, defaultData.favors),
      // Arrays de appSettings
      loadArrayFromChunks(COLLECTIONS.PROMOTIONAL_BANNERS, defaultData.appSettings.promotionalBanners),
      loadArrayFromChunks(COLLECTIONS.ANNOUNCEMENT_BANNERS, defaultData.appSettings.announcementBanners),
      loadArrayFromChunks(COLLECTIONS.BANK_ACCOUNTS, defaultData.appSettings.paymentMethods.bankAccounts),
      loadArrayFromChunks(COLLECTIONS.QR_PAYMENTS, defaultData.appSettings.paymentMethods.qrPayments)
    ]);

    // Reconstruir appSettings completo
    const reconstructedAppSettings = {
      ...defaultData.appSettings,
      ...loadedAppSettings,
      promotionalBanners,
      announcementBanners,
      paymentMethods: {
        ...defaultData.appSettings.paymentMethods,
        ...(loadedAppSettings.paymentMethods || {}),
        bankAccounts,
        qrPayments
      }
    };

    // Reconstruir objeto completo
    const mergedData = {
      ...defaultData,
      appSettings: reconstructedAppSettings,
      vendors,
      users,
      drivers,
      orders,
      categories,
      cities,
      deliveryZones,
      admins,
      messages,
      favors
    };

    // Asegurar que currentUser no esté en los datos cargados
    if ('currentUser' in mergedData) {
      delete mergedData.currentUser;
    }

    return {
      success: true,
      data: mergedData as Omit<AppData, 'currentUser'>
    };
  } catch (error: any) {
    console.error('Error al cargar datos desde Firestore:', error);
    return {
      success: false,
      error: error.message || 'Error desconocido al cargar desde Firestore'
    };
  }
}

/**
 * Suscribirse a cambios en tiempo real de los datos de la aplicación
 * @param callback Función que se ejecuta cuando hay cambios
 */
export function subscribeToAppData(
  callback: (data: Omit<AppData, 'currentUser'>) => void
): () => void {
  // Por ahora solo suscribirse al documento principal de metadata
  // En el futuro se pueden agregar suscripciones a otras colecciones
  return subscribeToDocument(
    COLLECTIONS.APP_DATA,
    MAIN_DOC_ID,
    (data) => {
      if (data) {
        // Cuando cambie metadata, recargar todo
        // En producción optimizar para solo recargar lo necesario
        callback(data as any);
      }
    }
  );
}

/**
 * Verifica si Firestore está disponible e inicializado
 * @returns true si Firestore está listo para usar, false en caso contrario
 */
export function isFirestoreAvailable(): boolean {
  try {
    getFirebaseFirestore();
    return true;
  } catch (error) {
    return false;
  }
}

export { COLLECTIONS };
