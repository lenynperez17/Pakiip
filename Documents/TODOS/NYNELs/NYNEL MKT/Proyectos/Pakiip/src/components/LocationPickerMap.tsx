
"use client";

import React, { useState, useCallback, useEffect } from 'react';
import { GoogleMap, Marker } from '@react-google-maps/api';
import type { Coordinate } from '@/lib/placeholder-data';
import { Skeleton } from './ui/skeleton';
import { Button } from './ui/button';
import { Crosshair } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { useGoogleMaps } from '@/providers/GoogleMapsProvider';
import { reverseGeocode } from '@/lib/google-geocoding';

interface LocationPickerMapProps {
    onLocationSelect: (address: string, coords: Coordinate) => void;
    initialCenter?: Coordinate;
    initialMarker?: Coordinate | null;
}

const containerStyle = {
  width: '100%',
  height: '100%',
};

const defaultCenter = {
  lat: -12.046374,
  lng: -77.042793 // Default to Lima, Peru
};

const mapOptions: google.maps.MapOptions = {
    disableDefaultUI: true,
    zoomControl: true,
};

export function LocationPickerMap({ onLocationSelect, initialCenter, initialMarker }: LocationPickerMapProps) {
  const { isLoaded, loadError } = useGoogleMaps();
  const [map, setMap] = useState<google.maps.Map | null>(null);
  const [markerPosition, setMarkerPosition] = useState<Coordinate | null>(initialMarker || null);
  const { toast } = useToast();
  
  const getAddressFromCoordinates = useCallback(async (coords: Coordinate) => {
    try {
      // Usar la función centralizada de Google Places API
      const result = await reverseGeocode(coords.lat, coords.lng);

      if (result) {
        // Usar formattedAddress (versión corta) para mejor UX
        onLocationSelect(result.formattedAddress, coords);
      } else {
        onLocationSelect('No se pudo encontrar la dirección.', coords);
      }
    } catch (error) {
      console.error('Error al obtener dirección:', error);
      onLocationSelect('Error al obtener la dirección.', coords);
    }
  }, [onLocationSelect]);

  useEffect(() => {
    if(initialMarker) {
        setMarkerPosition(initialMarker);
    }
  }, [initialMarker]);
  
  useEffect(() => {
    if (map && initialCenter && !initialMarker) {
      map.panTo(initialCenter);
    }
  }, [map, initialCenter, initialMarker]);

  const handleMapClick = useCallback((event: google.maps.MapMouseEvent) => {
    if (event.latLng) {
      const newPosition = {
        lat: event.latLng.lat(),
        lng: event.latLng.lng()
      };
      setMarkerPosition(newPosition);
      getAddressFromCoordinates(newPosition);
    }
  }, [getAddressFromCoordinates]);

  const handleCenterOnUser = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const userLocation: Coordinate = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          };
          map?.panTo(userLocation);
          map?.setZoom(16);
          setMarkerPosition(userLocation);
          getAddressFromCoordinates(userLocation);
        },
        () => {
          toast({
            title: "Error de Ubicación",
            description: "No se pudo obtener tu ubicación. Asegúrate de haber concedido los permisos.",
            variant: "destructive"
          });
        }
      );
    } else {
        toast({
            title: "Error de Ubicación",
            description: "Tu navegador no soporta la geolocalización.",
            variant: "destructive"
        });
    }
  };

  const onMapLoad = useCallback((mapInstance: google.maps.Map) => {
      setMap(mapInstance);
      if(initialMarker) {
          mapInstance.panTo(initialMarker);
          mapInstance.setZoom(16);
      } else if (initialCenter) {
           mapInstance.panTo(initialCenter);
      }
  }, [initialCenter, initialMarker]);

  const onUnmount = useCallback(() => {
    setMap(null);
  }, []);

  if (loadError) {
    return (
        <div className="flex items-center justify-center h-full bg-destructive/10 text-destructive rounded-lg">
            <p className="p-4 text-center text-sm">Error al cargar el mapa. Revisa la consola para más detalles.</p>
        </div>
    );
  }

  if (!process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY) {
    return (
        <div className="flex items-center justify-center h-full bg-destructive/10 text-destructive rounded-lg">
            <p className="p-4 text-center text-sm">La clave de API de Google Maps no está configurada. Por favor, añádela a tu archivo <strong>.env</strong> como <strong>NEXT_PUBLIC_GOOGLE_MAPS_API_KEY</strong>.</p>
        </div>
    );
  }

  if (!isLoaded) {
    return <Skeleton className="w-full h-full" />;
  }

  return (
    <div className="relative w-full h-full">
        <GoogleMap
            mapContainerStyle={containerStyle}
            center={initialCenter || defaultCenter}
            zoom={initialCenter ? 14 : 12}
            options={mapOptions}
            onClick={handleMapClick}
            onLoad={onMapLoad}
            onUnmount={onUnmount}
        >
            {markerPosition && <Marker position={markerPosition} />}
        </GoogleMap>
        <div className="absolute top-2 right-2">
            <Button
                type="button"
                size="icon"
                variant="outline"
                onClick={handleCenterOnUser}
                title="Usar mi ubicación actual"
                className="bg-background hover:bg-muted"
            >
                <Crosshair className="h-5 w-5" />
            </Button>
        </div>
    </div>
  );
}
