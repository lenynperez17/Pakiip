"use client";

import React from 'react';
import { GoogleMapsProvider } from '@/providers/GoogleMapsProvider';

interface GoogleMapsWrapperProps {
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

/**
 * Wrapper que carga Google Maps de forma lazy solo cuando se necesita.
 * Usar este componente envolviendo cualquier componente que necesite Google Maps.
 */
export function GoogleMapsWrapper({ children, fallback }: GoogleMapsWrapperProps) {
  return (
    <GoogleMapsProvider>
      {children}
    </GoogleMapsProvider>
  );
}
