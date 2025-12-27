"use client";

import React, { useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAppData } from '@/hooks/use-app-data';
import { useSession } from 'next-auth/react';
import { Loader2 } from 'lucide-react';

export default function CompleteRegistrationPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { users, saveUser } = useAppData();
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

      const sessionUser = session.user;

      // Esperar a que los datos estén cargados (validar que users sea un array)
      console.log('🔍 Estado de users:', {
        hasUsers: !!users,
        isArray: Array.isArray(users),
        usersLength: users?.length || 0,
        usersType: typeof users
      });

      if (!Array.isArray(users)) {
        console.log('⏳ Esperando a que los datos se inicialicen...');
        return;
      }

      console.log('✅ Datos de usuarios listos, continuando... (total:', users.length, 'usuarios)');
      console.log('🔍 Role detectado:', role);

      // Si no hay role, redirigir a selección de rol
      if (!role) {
        console.log('❌ No hay role en URL, redirigiendo a select-role...');
        router.replace('/select-role');
        return;
      }

      if (role === 'customer') {
        // Verificar si el usuario ya existe
        const existingUser = users.find(u => u.email === sessionUser.email);

        if (!existingUser) {
          console.log('✨ Creando nuevo usuario customer:', sessionUser.email);

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
        } else {
          console.log('✅ Usuario ya existe:', sessionUser.email);
        }

        // Redirigir a welcome y luego al homepage
        setTimeout(() => {
          router.push('/welcome');
        }, 1000);
      }
    };

    completeRegistration();
  }, [role, router, users, saveUser, session, status]);

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
