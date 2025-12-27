// Custom Image Loader para Next.js
// Maneja URLs firmadas de Firebase Storage sin doble encoding
// Referencia: https://nextjs.org/docs/app/building-your-application/optimizing/images#loaders

interface ImageLoaderParams {
  src: string;
  width: number;
  quality?: number;
}

export default function firebaseImageLoader({ src, width, quality }: ImageLoaderParams): string {
  // Si es una imagen base64, retornarla sin modificar
  // Next.js puede renderizar base64 directamente sin necesidad de optimización
  if (src.startsWith('data:image/') || src.startsWith('data:')) {
    return src;
  }

  // Si es una URL de Firebase Storage con firma (signed URL), retornarla sin modificar
  // para evitar el problema de doble encoding que causa 400 Bad Request
  if (src.includes('storage.googleapis.com') && src.includes('Signature=')) {
    return src;
  }

  // Si es una URL de firebasestorage.googleapis.com (download URL), retornarla sin modificar
  if (src.includes('firebasestorage.googleapis.com')) {
    return src;
  }

  // Si es una URL de Unsplash, retornarla directamente (ya tiene optimización propia)
  if (src.includes('images.unsplash.com')) {
    return src;
  }

  // Si es una URL de placehold.co o picsum, retornarla directamente
  if (src.includes('placehold.co') || src.includes('picsum.photos')) {
    return src;
  }

  // Para otras URLs externas, usar el optimizador de Next.js normalmente
  if (src.startsWith('http://') || src.startsWith('https://')) {
    return `/_next/image?url=${encodeURIComponent(src)}&w=${width}&q=${quality || 75}`;
  }

  // Para imágenes locales, usar el path directo
  return src;
}
