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

  useEffect(() => {
    const auth = getAuth();
    const unsubscribe = onAuthStateChanged(auth, (firebaseUser) => {
      if (!firebaseUser) {
        router.replace('/login');
      }
    });

    return () => unsubscribe();
  }, [router]);

  useEffect(() => {
    if (!currentUser) return;

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

      router.replace(redirectUrl);
    }, 5000);

    return () => clearTimeout(timer);
  }, [router, currentUser]);

  if (!currentUser) {
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
