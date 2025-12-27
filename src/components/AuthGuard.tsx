"use client";

/**
 * Componente AuthGuard - Usa NextAuth
 * Protege rutas que requieren autenticacion y roles especificos
 */

import { useEffect, useState } from "react";
import { useRouter, usePathname } from "next/navigation";
import { useSession } from "next-auth/react";
import { Loader2, AlertTriangle } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";

type UserRole = "customer" | "vendor" | "driver" | "admin";

interface AuthGuardProps {
  children: React.ReactNode;
  requireAuth?: boolean;
  requireRole?: UserRole | UserRole[];
  redirectTo?: string;
}

export function AuthGuard({
  children,
  requireAuth = true,
  requireRole,
  redirectTo = "/login",
}: AuthGuardProps) {
  const router = useRouter();
  const pathname = usePathname();
  const { data: session, status } = useSession();
  const { currentUser } = useAppData();
  const [roleError, setRoleError] = useState(false);

  // Redirigir si no esta autenticado
  useEffect(() => {
    if (status === "loading") return;

    if (requireAuth && status === "unauthenticated") {
      const callbackUrl = encodeURIComponent(pathname || "/");
      router.push(`${redirectTo}?callbackUrl=${callbackUrl}`);
    }
  }, [status, requireAuth, redirectTo, router, pathname]);

  // Validar rol si es requerido
  useEffect(() => {
    if (status === "loading") return;
    if (status === "unauthenticated") return;
    if (!requireRole) return;

    // Usar el rol de la sesion de NextAuth o del currentUser
    const userRole = session?.user?.role || currentUser?.role;

    // Convertir requireRole a array para facilitar la comparación
    const allowedRoles = Array.isArray(requireRole) ? requireRole : [requireRole];

    if (userRole && !allowedRoles.includes(userRole as UserRole)) {
      // Admin puede acceder a cualquier ruta
      if (userRole === "admin") {
        return;
      }

      console.error(
        `Acceso denegado. Roles permitidos: ${allowedRoles.join(', ')}, Rol actual: ${userRole}`
      );
      setRoleError(true);

      // Redirigir al dashboard correspondiente
      const roleRedirects: Record<UserRole, string> = {
        admin: "/admin/dashboard",
        vendor: "/vendor/dashboard",
        driver: "/driver/dashboard",
        customer: "/",
      };

      setTimeout(() => {
        router.push(roleRedirects[userRole as UserRole] || "/");
      }, 2000);
    }
  }, [status, session, requireRole, currentUser, router]);

  // Mostrar loading mientras se verifica la sesion
  if (status === "loading") {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin mx-auto text-primary" />
          <p className="mt-4 text-muted-foreground">
            Verificando autenticacion...
          </p>
        </div>
      </div>
    );
  }

  // No renderizar si requiere auth y no hay sesion
  if (requireAuth && status === "unauthenticated") {
    return null;
  }

  // Mostrar error de rol
  if (roleError) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center max-w-md mx-auto px-4">
          <AlertTriangle className="h-12 w-12 mx-auto text-destructive mb-4" />
          <h1 className="text-2xl font-bold mb-2">Acceso Denegado</h1>
          <p className="text-muted-foreground mb-4">
            No tienes permisos para acceder a esta pagina.
          </p>
          <p className="text-sm text-muted-foreground">
            Redirigiendo a tu dashboard...
          </p>
        </div>
      </div>
    );
  }

  return <>{children}</>;
}
