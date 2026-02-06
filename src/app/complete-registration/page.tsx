"use client";

import React, { Suspense, useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAppData } from '@/hooks/use-app-data';
import { useSession } from 'next-auth/react';
import { Loader2 } from 'lucide-react';

function CompleteRegistrationContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { users, saveUser, isLoading } = useAppData();
  const { data: session, status } = useSession();
  const role = searchParams.get('role');

  useEffect(() => {
    const completeRegistration = async () => {
      // Esperar a que la sesión esté lista
      if (status === 'loading') return;

      if (status === 'unauthenticated' || !session?.user) {
        router.replace('/login');
        return;
      }

      // Esperar a que los datos estén cargados
      if (isLoading) return;

      const sessionUser = session.user;

      if (!Array.isArray(users)) {
        return;
      }

      // Si no hay role, redirigir a selección de rol
      if (!role) {
        router.replace('/select-role');
        return;
      }

      if (role === 'customer') {
        // Verificar si el usuario ya existe
        const existingUser = users.find(u => u.email === sessionUser.email);

        if (!existingUser) {
          // Crear usuario customer con los datos de la sesión
          const newUser = {
            id: sessionUser.id || '',
            name: sessionUser.name || 'Usuario',
            email: sessionUser.email || '',
            phone: '',
            totalOrders: 0,
            totalSpent: 0,
          };

          // Agregar a la base de datos usando saveUser
          saveUser(newUser);
        }

        // Redirigir a welcome y luego al homepage
        setTimeout(() => {
          router.push('/welcome');
        }, 1000);
      }
    };

    completeRegistration();
  }, [role, router, users, saveUser, session, status, isLoading]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-background">
      <div className="text-center">
        <Loader2 className="w-12 h-12 animate-spin text-primary mx-auto mb-4" />
        <h2 className="text-2xl font-semibold mb-2">Completando tu registro...</h2>
        <p className="text-muted-foreground">
          En un momento estarás listo para empezar
        </p>
      </div>
    </div>
  );
}

export default function CompleteRegistrationPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen flex items-center justify-center bg-background">
        <Loader2 className="w-12 h-12 animate-spin text-primary" />
      </div>
    }>
      <CompleteRegistrationContent />
    </Suspense>
  );
}
