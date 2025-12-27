/**
 * Utilidad para optimizar imágenes antes de subirlas al servidor
 * Reduce el tamaño de las imágenes convirtiéndolas a WebP y redimensionándolas
 */

import { uploadImageFromBase64 } from './upload';

/**
 * Configuración de optimización de imágenes
 */
export interface ImageOptimizationConfig {
  maxWidth?: number; // Ancho máximo en píxeles (default: 1920)
  maxHeight?: number; // Alto máximo en píxeles (default: 1080)
  quality?: number; // Calidad de compresión 0-1 (default: 0.8)
  format?: 'webp' | 'jpeg' | 'png'; // Formato de salida (default: 'webp')
}

/**
 * Carga una imagen desde Base64 o URL y retorna un elemento Image
 */
function loadImage(src: string): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = reject;
    img.src = src;
  });
}

/**
 * Optimiza una imagen redimensionándola y comprimiéndola
 * @param base64OrUrl String Base64 o URL de la imagen
 * @param config Configuración de optimización
 * @returns Imagen optimizada en formato Base64
 */
export async function optimizeImage(
  base64OrUrl: string,
  config: ImageOptimizationConfig = {}
): Promise<{ success: boolean; base64?: string; error?: string; originalSize?: number; optimizedSize?: number }> {
  try {
    const {
      maxWidth = 1920,
      maxHeight = 1080,
      quality = 0.8,
      format = 'webp'
    } = config;

    // Cargar la imagen
    const img = await loadImage(base64OrUrl);

    // Calcular nuevas dimensiones manteniendo aspect ratio
    let { width, height } = img;

    if (width > maxWidth || height > maxHeight) {
      const aspectRatio = width / height;

      if (width > height) {
        width = Math.min(width, maxWidth);
        height = width / aspectRatio;
      } else {
        height = Math.min(height, maxHeight);
        width = height * aspectRatio;
      }
    }

    // Crear canvas y redimensionar
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      throw new Error('No se pudo obtener contexto 2D del canvas');
    }

    // Dibujar imagen redimensionada con mejor calidad
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, 0, 0, width, height);

    // Convertir a Base64 en el formato deseado
    const mimeType = format === 'webp' ? 'image/webp' :
                     format === 'jpeg' ? 'image/jpeg' : 'image/png';

    const optimizedBase64 = canvas.toDataURL(mimeType, quality);

    // Calcular tamaños para estadísticas
    const originalSize = base64OrUrl.length;
    const optimizedSize = optimizedBase64.length;

    console.log(`🖼️ [IMAGE OPTIMIZER] Optimización completa:
      - Dimensiones originales: ${img.width}x${img.height}
      - Dimensiones finales: ${Math.round(width)}x${Math.round(height)}
      - Tamaño original: ${(originalSize / 1024).toFixed(2)} KB
      - Tamaño optimizado: ${(optimizedSize / 1024).toFixed(2)} KB
      - Reducción: ${((1 - optimizedSize / originalSize) * 100).toFixed(1)}%
      - Formato: ${format}
    `);

    return {
      success: true,
      base64: optimizedBase64,
      originalSize,
      optimizedSize
    };
  } catch (error: any) {
    console.error('Error al optimizar imagen:', error);
    return {
      success: false,
      error: error.message || 'Error al optimizar imagen'
    };
  }
}

/**
 * Optimiza y sube una imagen al servidor
 * @param base64OrUrl String Base64 o URL de la imagen
 * @param storagePath Ruta en Storage donde se guardará (ej: 'banners/promo-1.webp')
 * @param config Configuración de optimización
 * @returns URL pública de la imagen optimizada
 */
export async function optimizeAndUploadImage(
  base64OrUrl: string,
  storagePath: string,
  config: ImageOptimizationConfig = {}
): Promise<{ success: boolean; url?: string; error?: string; stats?: { originalSize: number; optimizedSize: number } }> {
  try {
    console.log(`📤 [IMAGE UPLOAD] Iniciando optimización y subida a: ${storagePath}`);

    // Paso 1: Optimizar imagen
    const optimizationResult = await optimizeImage(base64OrUrl, config);

    if (!optimizationResult.success || !optimizationResult.base64) {
      return {
        success: false,
        error: optimizationResult.error || 'Error al optimizar imagen'
      };
    }

    // Paso 2: Subir imagen optimizada al servidor
    const uploadResult = await uploadImageFromBase64(optimizationResult.base64, storagePath);

    if (!uploadResult.success) {
      return {
        success: false,
        error: uploadResult.error || 'Error al subir imagen'
      };
    }

    console.log(`✅ [IMAGE UPLOAD] Imagen subida exitosamente: ${uploadResult.url}`);

    return {
      success: true,
      url: uploadResult.url,
      stats: {
        originalSize: optimizationResult.originalSize || 0,
        optimizedSize: optimizationResult.optimizedSize || 0
      }
    };
  } catch (error: any) {
    console.error('Error en optimizeAndUploadImage:', error);
    return {
      success: false,
      error: error.message || 'Error al procesar y subir imagen'
    };
  }
}

/**
 * Optimiza múltiples imágenes en batch
 * Útil para migrar banners promocionales existentes
 */
export async function optimizeAndUploadBatch(
  images: Array<{ base64: string; path: string }>,
  config: ImageOptimizationConfig = {}
): Promise<Array<{ path: string; url?: string; success: boolean; error?: string }>> {
  console.log(`📦 [BATCH UPLOAD] Procesando ${images.length} imágenes...`);

  const results = await Promise.all(
    images.map(async ({ base64, path }) => {
      const result = await optimizeAndUploadImage(base64, path, config);
      return {
        path,
        url: result.url,
        success: result.success,
        error: result.error
      };
    })
  );

  const successful = results.filter(r => r.success).length;
  console.log(`✅ [BATCH UPLOAD] Completado: ${successful}/${images.length} exitosas`);

  return results;
}

/**
 * Genera un nombre de archivo único para Storage basado en timestamp
 * @param prefix Prefijo del archivo (ej: 'banner-promo')
 * @param extension Extensión del archivo (ej: 'webp')
 * @returns Nombre de archivo único
 */
export function generateUniqueFileName(prefix: string, extension: string = 'webp'): string {
  const timestamp = Date.now();
  const random = Math.random().toString(36).substring(2, 8);
  return `${prefix}-${timestamp}-${random}.${extension}`;
}

/**
 * Extrae el path de Storage desde una URL (compatibilidad con URLs legacy)
 * Útil para eliminar imágenes antiguas
 */
export function extractStoragePathFromUrl(url: string): string | null {
  try {
    const urlObj = new URL(url);
    const pathMatch = urlObj.pathname.match(/\/o\/(.+?)\?/);
    if (pathMatch && pathMatch[1]) {
      return decodeURIComponent(pathMatch[1]);
    }
    return null;
  } catch (error) {
    console.error('Error al extraer path de Storage:', error);
    return null;
  }
}
