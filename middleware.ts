/**
 * Middleware de Next.js para proteccion de rutas
 * Re-exporta el proxy desde src/proxy.ts como middleware
 */

export { proxy as middleware } from "./src/proxy";

// Configuracion del matcher para TODAS las rutas (modo mantenimiento)
export const config = {
  matcher: [
    // Interceptar TODAS las rutas excepto recursos estaticos
    "/((?!_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp|ico)$).*)",
  ],
};
