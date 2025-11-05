"use client";

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { PakiipCharacter } from '@/components/PakiipCharacter';
import { Skeleton } from '@/components/ui/skeleton';
import { useAppData } from '@/hooks/use-app-data';
import { getAuth, onAuthStateChanged } from 'firebase/auth';

function WelcomePageContent() {
  const router = useRouter();
  const { appSettings, currentUser } = useAppData();

  console.log('🎯 [WELCOME] Componente montado - currentUser:', currentUser ? `${currentUser.name} (${currentUser.role})` : 'NULL');

  useEffect(() => {
    console.log('🔄 [WELCOME] useEffect de auth ejecutándose');
    const auth = getAuth();
    const unsubscribe = onAuthStateChanged(auth, (firebaseUser) => {
      console.log('🔐 [WELCOME] onAuthStateChanged:', firebaseUser ? firebaseUser.phoneNumber || firebaseUser.email : 'NO USER');
      if (!firebaseUser) {
        console.log('❌ [WELCOME] No hay usuario Firebase, redirigiendo a /login');
        router.replace('/login');
      } else {
        console.log('✅ [WELCOME] Usuario Firebase confirmado');
      }
    });

    return () => {
      console.log('🧹 [WELCOME] Limpiando suscripción de auth');
      unsubscribe();
    };
  }, [router]);

  useEffect(() => {
    console.log('⏰ [WELCOME] useEffect de redirección - currentUser:', currentUser ? 'EXISTE' : 'NULL');
    if (!currentUser) return;

    console.log(`🎯 [WELCOME] Iniciando timer de redirección para rol: ${currentUser.role}`);
    const timer = setTimeout(() => {
      let redirectUrl = '/';

      switch (currentUser.role) {
        case 'admin':
          redirectUrl = '/admin/dashboard';
          break;
        case 'vendor':
          redirectUrl = `/vendor/dashboard?vendorId=${currentUser.id}`;
          break;
        case 'driver':
          redirectUrl = '/driver/dashboard';
          break;
        case 'customer':
          redirectUrl = '/';
          break;
      }

      console.log(`🚀 [WELCOME] Redirigiendo a: ${redirectUrl}`);
      router.replace(redirectUrl);
    }, 5000);

    return () => {
      console.log('🧹 [WELCOME] Limpiando timer de redirección');
      clearTimeout(timer);
    };
  }, [router, currentUser]);

  console.log('🎨 [WELCOME] Renderizando - currentUser:', currentUser ? 'TIENE DATOS' : 'MOSTRANDO SKELETON');

  if (!currentUser) {
    console.log('⏳ [WELCOME] Mostrando skeleton mientras espera currentUser');
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-background mx-auto px-4" style={{ maxWidth: '1600px' }}>
        <Skeleton className="h-24 w-24 rounded-full" />
        <Skeleton className="h-8 w-64 mt-4" />
        <Skeleton className="h-4 w-48 mt-2" />
      </div>
    );
  }

  let message = `¡Bienvenido de nuevo, ${currentUser.name}!`;
  let imageUrl = appSettings.welcomeImageUrl;

  if (currentUser.role === 'admin') {
    message = `¡Bienvenido, Admin! Es hora de gestionar la plataforma.`;
  } else if (currentUser.role === 'vendor') {
    message = `¡Hola, ${currentUser.name}! ¿Listo para vender hoy?`;
  } else if (currentUser.role === 'driver') {
    message = `¡Hola, ${currentUser.name}! Tus entregas te esperan. ¡A rodar!`;
    imageUrl = appSettings.driverWelcomeImageUrl || appSettings.welcomeImageUrl;
  }

  console.log('✨ [WELCOME] Renderizando PakiipCharacter con mensaje:', message);
  return <PakiipCharacter message={message} imageUrl={imageUrl} />;
}

export default function WelcomePage() {
  return (
    <React.Suspense fallback={
      <div className="fixed inset-0 bg-background flex items-center justify-center">
        <p>Cargando...</p>
      </div>
    }>
      <WelcomePageContent />
    </React.Suspense>
  );
}
