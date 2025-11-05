
"use client";

import React, { useState, useEffect, useCallback, useRef } from 'react';
import { GoogleMap, PolygonF, DrawingManagerF } from '@react-google-maps/api';
import type { Coordinate, DeliveryZone } from '@/lib/placeholder-data';
import { Skeleton } from './ui/skeleton';
import { Button } from './ui/button';
import { Trash2 } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { useGoogleMaps } from '@/providers/GoogleMapsProvider';

const containerStyle = {
  width: '100%',
  height: '100%',
};

const defaultCenter = {
  lat: -12.046374,
  lng: -77.042793 // Default to Lima, Peru
};

const mapOptions = {
    disableDefaultUI: true,
    zoomControl: true,
};

interface ZoneEditorMapProps {
    center: Coordinate;
    zones: DeliveryZone[];
    onZoneDrawn: (path: Coordinate[]) => void;
    onZoneEdited: (zoneId: string, newPath: Coordinate[]) => void;
}

export function ZoneEditorMap({ center, zones, onZoneDrawn, onZoneEdited }: ZoneEditorMapProps) {
  const { isLoaded, loadError } = useGoogleMaps();
  const [map, setMap] = useState<google.maps.Map | null>(null);
  const polygonRefs = useRef<Map<string, google.maps.Polygon>>(new Map());

  const onMapLoad = useCallback((mapInstance: google.maps.Map) => {
    setMap(mapInstance);
  }, []);
  
  useEffect(() => {
    if (map) {
      map.panTo(center);
    }
  }, [center, map]);
  
  const onPolygonComplete = (polygon: google.maps.Polygon) => {
    const path = polygon.getPath().getArray().map(p => ({ lat: p.lat(), lng: p.lng() }));
    onZoneDrawn(path);
    // After getting the path, remove the drawn polygon from the map
    // as it will be re-rendered via the zones prop.
    polygon.setMap(null); 
  };
  
  const onPolygonEdit = (zoneId: string) => {
    const polygon = polygonRefs.current.get(zoneId);
    if(polygon) {
      const newPath = polygon.getPath().getArray().map(p => ({ lat: p.lat(), lng: p.lng() }));
      onZoneEdited(zoneId, newPath);
    }
  };

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
    <GoogleMap
        mapContainerStyle={containerStyle}
        center={center}
        zoom={13}
        options={mapOptions}
        onLoad={onMapLoad}
        onUnmount={onUnmount}
    >
      <DrawingManagerF
        onPolygonComplete={onPolygonComplete}
        options={{
          drawingControl: true,
          drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [
              google.maps.drawing.OverlayType.POLYGON,
            ],
          },
          polygonOptions: {
            fillColor: '#FFC107',
            fillOpacity: 0.35,
            strokeWeight: 2,
            clickable: true,
            editable: true,
            zIndex: 1,
          },
        }}
      />
       {zones.map((zone) => (
        <PolygonF
          key={zone.id}
          paths={zone.path}
          editable={true}
          onMouseUp={() => onPolygonEdit(zone.id)}
          onDragEnd={() => onPolygonEdit(zone.id)}
          onLoad={(polygon) => {
            if (polygon) {
              polygonRefs.current.set(zone.id, polygon);
            }
          }}
          onUnmount={() => {
            polygonRefs.current.delete(zone.id);
          }}
          options={{
            fillColor: "hsl(var(--primary))",
            fillOpacity: 0.2,
            strokeColor: "hsl(var(--primary))",
            strokeWeight: 2,
          }}
        />
      ))}

    </GoogleMap>
  );
}
