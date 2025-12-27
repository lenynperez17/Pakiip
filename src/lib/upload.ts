/**
 * Utilidades de Upload de Imagenes
 * Usa la API local en lugar de Firebase Storage
 */

/**
 * Subir imagen desde File
 */
export async function uploadImage(
  file: File,
  path: string = "misc"
): Promise<{ success: boolean; url?: string; error?: string }> {
  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("path", path);

    const response = await fetch("/api/upload", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      return { success: true, url: result.url };
    } else {
      return { success: false, error: result.error };
    }
  } catch (error: any) {
    console.error("Error al subir imagen:", error);
    return { success: false, error: error.message };
  }
}

/**
 * Subir imagen desde base64
 */
export async function uploadImageFromBase64(
  base64: string,
  path: string = "misc"
): Promise<{ success: boolean; url?: string; error?: string }> {
  try {
    const response = await fetch("/api/upload", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ base64, path }),
    });

    const result = await response.json();

    if (result.success) {
      return { success: true, url: result.url };
    } else {
      return { success: false, error: result.error };
    }
  } catch (error: any) {
    console.error("Error al subir imagen base64:", error);
    return { success: false, error: error.message };
  }
}

/**
 * Subir imagen desde URL (descarga y sube)
 */
export async function uploadImageFromUrl(
  imageUrl: string,
  path: string = "misc"
): Promise<{ success: boolean; url?: string; error?: string }> {
  try {
    // Descargar la imagen
    const response = await fetch(imageUrl);
    const blob = await response.blob();

    // Convertir a File
    const file = new File([blob], "image.jpg", { type: blob.type });

    // Subir usando la funcion principal
    return uploadImage(file, path);
  } catch (error: any) {
    console.error("Error al subir imagen desde URL:", error);
    return { success: false, error: error.message };
  }
}

/**
 * @deprecated Funcion de compatibilidad - no hace nada
 * Mantener para evitar errores en imports existentes
 */
export function initializeFirebase(_config?: any): void {
  // No-op
}
