"use client";

import { useEffect, useState } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import { getAuth, onAuthStateChanged, User as FirebaseUser } from 'firebase/auth';
import { Loader2, AlertTriangle } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';

type UserRole = 'customer' | 'vendor' | 'driver' | 'admin';

interface AuthGuardProps {
  children: React.ReactNode;
  requireAuth?: boolean;
  requireRole?: UserRole;
  redirectTo?: string;
}

/**
 * Componente AuthGuard profesional con validación de roles
 * Protege rutas que requieren autenticación y roles específicos
 * Redirige automáticamente si el usuario no está autenticado o no tiene el rol correcto
 */
export function AuthGuard({
  children,
  requireAuth = true,
  requireRole,
  redirectTo = '/login'
}: AuthGuardProps) {
  const router = useRouter();
  const pathname = usePathname();
  const { currentUser } = useAppData();
  const [firebaseUser, setFirebaseUser] = useState<FirebaseUser | null>(null);
  const [loading, setLoading] = useState(true);
  const [roleError, setRoleError] = useState(false);

  useEffect(() => {
    const auth = getAuth();

    const unsubscribe = onAuthStateChanged(auth, (user) => {
      setFirebaseUser(user);
      setLoading(false);

      if (requireAuth && !user) {
        // Usuario no autenticado - redirigir a login
        const returnUrl = encodeURIComponent(pathname || '/');
        router.push(`${redirectTo}?returnUrl=${returnUrl}`);
      }
    });

    return () => unsubscribe();
  }, [requireAuth, redirectTo, router, pathname]);

  // Validar rol si es requerido
  useEffect(() => {
    if (!loading && firebaseUser && requireRole && currentUser) {
      if (currentUser.role !== requireRole) {
        console.error(`Acceso denegado. Rol requerido: ${requireRole}, Rol actual: ${currentUser.role}`);
        setRoleError(true);

        // Redirigir al dashboard correspondiente según el rol del usuario
        const roleRedirects: Record<UserRole, string> = {
          admin: '/admin/dashboard',
          vendor: '/vendor/dashboard',
          driver: '/driver/dashboard',
          customer: '/',
        };

        setTimeout(() => {
          router.push(roleRedirects[currentUser.role] || '/');
        }, 2000);
      }
    }
  }, [loading, firebaseUser, requireRole, currentUser, router]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin mx-auto text-primary" />
          <p className="mt-4 text-muted-foreground">Verificando autenticación...</p>
        </div>
      </div>
    );
  }

  if (requireAuth && !firebaseUser) {
    return null;
  }

  if (roleError) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center max-w-md mx-auto px-4">
          <AlertTriangle className="h-12 w-12 mx-auto text-destructive mb-4" />
          <h1 className="text-2xl font-bold mb-2">Acceso Denegado</h1>
          <p className="text-muted-foreground mb-4">
            No tienes permisos para acceder a esta página.
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
