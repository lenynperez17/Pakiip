"use client";

import React, { useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAppData } from '@/hooks/use-app-data';
import { getAuth } from 'firebase/auth';
import { Loader2 } from 'lucide-react';

export default function CompleteRegistrationPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { users, saveUser } = useAppData();
  const role = searchParams.get('role');

  useEffect(() => {
    const completeRegistration = async () => {
      const auth = getAuth();
      const firebaseUser = auth.currentUser;

      if (!firebaseUser) {
        router.replace('/login');
        return;
      }

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
        const existingUser = users.find(u => u.email === firebaseUser.email);

        if (!existingUser) {
          console.log('✨ Creando nuevo usuario customer:', firebaseUser.email);

          // Crear usuario customer con los datos de Google
          const newUser = {
            id: firebaseUser.uid,
            name: firebaseUser.displayName || 'Usuario',
            email: firebaseUser.email || '',
            phone: firebaseUser.phoneNumber || '',
            totalOrders: 0,
            totalSpent: 0,
          };

          // Agregar a la base de datos usando saveUser
          saveUser(newUser);
        } else {
          console.log('✅ Usuario ya existe:', firebaseUser.email);
        }

        // Redirigir a welcome y luego al homepage
        setTimeout(() => {
          router.push('/welcome');
        }, 1000);
      }
    };

    completeRegistration();
  }, [role, router, users, saveUser]);

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
