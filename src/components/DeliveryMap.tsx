
"use client";

import React, { useState, useEffect, useCallback } from 'react';
import { GoogleMap, MarkerF, InfoWindow } from '@react-google-maps/api';
import type { Coordinate } from '@/lib/placeholder-data';
import { Skeleton } from './ui/skeleton';
import { useGoogleMaps } from '@/providers/GoogleMapsProvider';

export interface DeliveryPoint {
    type: 'store' | 'customer';
    location: Coordinate;
    name: string;
    orderId?: string;
}

interface DeliveryMapProps {
    points: DeliveryPoint[];
}

const containerStyle = {
  width: '100%',
  height: '100%',
};

const defaultCenter = {
  lat: -12.046374,
  lng: -77.042793
};

const mapOptions = {
    disableDefaultUI: true,
    zoomControl: true,
    styles: [
        { featureType: "all", elementType: "labels.text.fill", stylers: [ { "color": "#7c93a3" }, { "lightness": "-10" } ] },
    ]
};

export function DeliveryMap({ points }: DeliveryMapProps) {
  const { isLoaded, loadError } = useGoogleMaps();
  const [activeMarker, setActiveMarker] = useState<string | null>(null);
  const [map, setMap] = useState<google.maps.Map | null>(null);

  const handleActiveMarker = (marker: string | null) => {
    setActiveMarker(marker);
  };
  
  const getMarkerLabel = (type: 'store' | 'customer') => {
      return { text: type === 'store' ? "T" : "C", color: "white", fontWeight: "bold" };
  }

  const onLoad = useCallback((mapInstance: google.maps.Map) => {
    setMap(mapInstance);
  }, []);

  const onUnmount = useCallback(() => {
    setMap(null);
  }, []);

  useEffect(() => {
    if (map && points.length > 0) {
      const bounds = new window.google.maps.LatLngBounds();
      points.forEach(point => bounds.extend(point.location));
      map.fitBounds(bounds);
    }
  }, [map, points]);

  if (loadError) {
    return (
        <div className="flex items-center justify-center h-full bg-destructive/10 text-destructive rounded-lg">
            <p className="p-4 text-center">Error al cargar el mapa. Revisa la consola para más detalles.</p>
        </div>
    );
  }
  
   if (!process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY) {
    return (
        <div className="flex items-center justify-center h-full bg-destructive/10 text-destructive rounded-lg">
            <p className="p-4 text-center">La clave de API de Google Maps no está configurada. Por favor, añádela a tu archivo <strong>.env</strong> como <strong>NEXT_PUBLIC_GOOGLE_MAPS_API_KEY</strong>.</p>
        </div>
    );
  }

  if (!isLoaded) {
    return <Skeleton className="w-full h-full" />;
  }

  return (
    <GoogleMap
        mapContainerStyle={containerStyle}
        center={points[0]?.location || defaultCenter}
        zoom={12}
        options={mapOptions}
        onLoad={onLoad}
        onUnmount={onUnmount}
    >
        {points.map((point) => (
             <MarkerF
                key={`${point.type}-${point.name}-${point.orderId || ''}`}
                position={point.location}
                label={getMarkerLabel(point.type)}
                onClick={() => handleActiveMarker(`${point.type}-${point.name}`)}
            >
                {activeMarker === `${point.type}-${point.name}` ? (
                  <InfoWindow onCloseClick={() => handleActiveMarker(null)}>
                      <div>
                        <p className="font-bold">{point.name}</p>
                        {point.orderId && <p className="text-sm">Pedido: {point.orderId}</p>}
                      </div>
                  </InfoWindow>
                ) : null}
            </MarkerF>
        ))}
    </GoogleMap>
  );
}
