"use client";

import React, { createContext, useContext } from 'react';
import { useJsApiLoader } from '@react-google-maps/api';
import { Skeleton } from '@/components/ui/skeleton';

interface GoogleMapsContextType {
  isLoaded: boolean;
  loadError: Error | undefined;
}

const GoogleMapsContext = createContext<GoogleMapsContextType>({
  isLoaded: false,
  loadError: undefined,
});

export const useGoogleMaps = () => useContext(GoogleMapsContext);

const libraries: ("drawing" | "geometry" | "places" | "visualization")[] = ["drawing", "places"];

export function GoogleMapsProvider({ children }: { children: React.ReactNode }) {
  const { isLoaded, loadError } = useJsApiLoader({
    id: 'google-map-script-global',
    googleMapsApiKey: process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY!,
    libraries,
    language: 'es',
    region: 'US'
  });

  if (loadError) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-destructive/10 text-destructive">
        <div className="p-8 text-center">
          <p className="text-lg font-semibold mb-2">Error al cargar Google Maps</p>
          <p className="text-sm">Revisa la consola para más detalles.</p>
        </div>
      </div>
    );
  }

  if (!process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-destructive/10 text-destructive">
        <div className="p-8 text-center max-w-md">
          <p className="text-lg font-semibold mb-2">Configuración requerida</p>
          <p className="text-sm">
            La clave de API de Google Maps no está configurada. Por favor, añádela a tu archivo{' '}
            <strong>.env.local</strong> como <strong>NEXT_PUBLIC_GOOGLE_MAPS_API_KEY</strong>.
          </p>
        </div>
      </div>
    );
  }

  if (!isLoaded) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Skeleton className="w-full h-full" />
      </div>
    );
  }

  return (
    <GoogleMapsContext.Provider value={{ isLoaded, loadError }}>
      {children}
    </GoogleMapsContext.Provider>
  );
}
