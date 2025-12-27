/**
 * Proxy de Next.js 16 para proteccion de rutas
 * Usa NextAuth para verificar autenticacion y roles
 */

import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";
import { getToken } from "next-auth/jwt";

// Rutas que requieren autenticacion
const protectedRoutes = [
  "/admin",
  "/vendor",
  "/driver",
  "/profile",
  "/orders",
  "/checkout",
  "/cart",
];

// Rutas publicas (no requieren autenticacion)
const publicRoutes = [
  "/",
  "/login",
  "/register",
  "/vendor/register",
  "/driver/register",
  "/menu",
  "/store",
  "/category",
  "/search",
  "/about",
  "/contact",
  "/terms",
  "/privacy",
];

// Rutas de vendor que son publicas (tiendas visibles para clientes)
// Incluye paginas de tiendas individuales que cualquier usuario puede ver
const publicVendorRoutes = [
  "/vendor/register",
  "/vendor/login",
];

// Patron para detectar paginas de tienda publica: /vendor/[cuid]
// Los CUIDs tienen formato como: cmj3jkzwk004i6dfjer6c4b5n
const vendorStorePagePattern = /^\/vendor\/[a-z0-9]{20,30}$/i;

// Rutas del dashboard del vendor (requieren rol vendor/admin)
const vendorDashboardRoutes = [
  "/vendor/dashboard",
  "/vendor/products",
  "/vendor/orders",
  "/vendor/settings",
  "/vendor/analytics",
  "/vendor/profile",
];

// Mapeo de rutas a roles requeridos
const roleRoutes: Record<string, string[]> = {
  "/admin": ["admin"],
  "/driver": ["driver", "admin"],
};

export async function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Ignorar rutas de API, recursos estaticos y paginas de login
  if (
    pathname.startsWith("/api") ||
    pathname.startsWith("/_next") ||
    pathname.startsWith("/static") ||
    pathname.includes(".") || // Archivos con extension
    pathname === "/admin/login" ||
    pathname === "/vendor/login" ||
    pathname === "/driver/login"
  ) {
    return NextResponse.next();
  }

  // Verificar si es ruta protegida
  const isProtectedRoute = protectedRoutes.some((route) =>
    pathname.startsWith(route)
  );

  if (!isProtectedRoute) {
    return NextResponse.next();
  }

  // Obtener token de NextAuth - probar diferentes nombres de cookie
  const token = await getToken({
    req: request,
    secret: process.env.NEXTAUTH_SECRET,
    cookieName: process.env.NODE_ENV === "production"
      ? "__Secure-authjs.session-token"
      : "authjs.session-token",
  });

  // Si no hay token, redirigir a login
  if (!token) {
    const loginUrl = new URL("/login", request.url);
    loginUrl.searchParams.set("callbackUrl", pathname);
    return NextResponse.redirect(loginUrl);
  }

  // Verificar si es una pagina de tienda publica (cualquier usuario autenticado puede ver)
  if (pathname.startsWith("/vendor/")) {
    // Si es pagina de tienda publica (/vendor/[cuid]), permitir a cualquier usuario autenticado
    if (vendorStorePagePattern.test(pathname)) {
      return NextResponse.next();
    }

    // Si es ruta publica de vendor (register, login), ya fue manejada arriba
    if (publicVendorRoutes.includes(pathname)) {
      return NextResponse.next();
    }

    // Si es ruta del dashboard del vendor, verificar rol
    const isVendorDashboard = vendorDashboardRoutes.some(route => pathname.startsWith(route));
    if (isVendorDashboard || pathname === "/vendor") {
      const userRole = token.role as string;
      if (!["vendor", "admin"].includes(userRole)) {
        const homeUrl = new URL("/", request.url);
        return NextResponse.redirect(homeUrl);
      }
    }
  }

  // Verificar roles para rutas especificas (admin, driver)
  for (const [route, allowedRoles] of Object.entries(roleRoutes)) {
    if (pathname.startsWith(route)) {
      const userRole = token.role as string;

      if (!allowedRoles.includes(userRole)) {
        // Redirigir a pagina de acceso denegado o home
        const homeUrl = new URL("/", request.url);
        return NextResponse.redirect(homeUrl);
      }
    }
  }

  return NextResponse.next();
}

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
