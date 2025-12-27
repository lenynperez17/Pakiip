/**
 * Middleware de Next.js para proteccion de rutas
 * Re-exporta el proxy desde src/proxy.ts como middleware
 */

export { proxy as middleware } from "./src/proxy";

// Configuracion del matcher para optimizar rendimiento
export const config = {
  matcher: [
    // Rutas que necesitan verificacion
    "/admin/:path*",
    "/vendor/:path*",
    "/driver/:path*",
    "/profile/:path*",
    "/orders/:path*",
    "/checkout/:path*",
    "/cart/:path*",
  ],
};
