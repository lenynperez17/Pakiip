/**
 * Normaliza URLs para navegación interna
 * Convierte URLs absolutas del mismo dominio a rutas relativas
 */
export function normalizeInternalUrl(url: string): string {
  if (!url) return '/';

  const trimmedUrl = url.trim();

  // Si ya es relativa, retornarla tal cual
  if (trimmedUrl.startsWith('/')) return trimmedUrl;

  // Si es absoluta del mismo dominio, convertir a relativa
  try {
    const urlObj = new URL(trimmedUrl);
    const currentHostnames = [
      'pakiip.com',
      'www.pakiip.com',
      'localhost',
      '127.0.0.1'
    ];

    if (currentHostnames.includes(urlObj.hostname)) {
      // Retornar solo el pathname + search + hash
      return urlObj.pathname + urlObj.search + urlObj.hash;
    }

    // Si es de otro dominio, retornar la URL completa
    return trimmedUrl;
  } catch {
    console.error(`[normalizeInternalUrl] Error parsing URL: '${trimmedUrl}'`);

    // Si no es una URL válida, intentar limpiarla
    // Casos como "https:/www.pakiip.com" (un solo slash)
    // Regex mejorado para capturar https:/ o http:/ seguido de cualquier cosa que no sea /
    if (trimmedUrl.match(/^https?:\/[^/]/)) {
      const cleaned = trimmedUrl.replace(/^(https?):\/([^/])/, '$1://$2');
      console.log(`[normalizeInternalUrl] Fixed malformed protocol: ${trimmedUrl} -> ${cleaned}`);
      try {
        const urlObj = new URL(cleaned);
        if (urlObj.hostname === 'pakiip.com' || urlObj.hostname === 'www.pakiip.com') {
          return urlObj.pathname + urlObj.search + urlObj.hash;
        }
        return cleaned; // Retornar la URL absoluta corregida si no es interna
      } catch (e) {
        console.error(`[normalizeInternalUrl] Failed to parse cleaned URL: ${cleaned}`, e);
      }
    }

    // Fallback: Si contiene /vendor/, intentar extraer la ruta relativa
    if (trimmedUrl.includes('/vendor/')) {
      const match = trimmedUrl.match(/(\/vendor\/[^?#]+)/);
      if (match) {
        console.log(`[normalizeInternalUrl] Extracted vendor path: ${match[1]}`);
        return match[1];
      }
    }

    // Si todo falla, retornar como está
    return trimmedUrl;
  }
}
