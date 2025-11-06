"use client";

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';

export default function WelcomePage() {
  const router = useRouter();

  console.log('🎯 [WELCOME-SIMPLIFIED] Componente montado');

  useEffect(() => {
    console.log('⏰ [WELCOME-SIMPLIFIED] useEffect ejecutándose - redirigiendo en 2 segundos');

    const timer = setTimeout(() => {
      console.log('🚀 [WELCOME-SIMPLIFIED] Redirigiendo a home');
      router.replace('/');
    }, 2000);

    return () => {
      console.log('🧹 [WELCOME-SIMPLIFIED] Limpiando timer');
      clearTimeout(timer);
    };
  }, [router]);

  console.log('🎨 [WELCOME-SIMPLIFIED] Renderizando mensaje de bienvenida');

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-background mx-auto px-4" style={{ maxWidth: '1600px' }}>
      <div className="text-center">
        <h1 className="text-4xl font-bold mb-4">¡Bienvenido a Pakiip!</h1>
        <p className="text-lg text-muted-foreground mb-8">
          Redirigiendo a la página principal...
        </p>
        <div className="animate-pulse">
          <div className="h-2 w-48 bg-primary rounded mx-auto"></div>
        </div>
      </div>
    </div>
  );
}
