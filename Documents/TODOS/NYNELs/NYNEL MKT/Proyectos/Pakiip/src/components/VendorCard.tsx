
import Image from "next/image";
import Link from "next/link";

import type { Vendor } from "@/lib/placeholder-data";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import { Badge } from "./ui/badge";
import { Star, Clock, Sparkles } from "lucide-react";

interface VendorCardProps {
  vendor: Vendor;
  priority?: boolean;
}

export function VendorCard({ vendor, priority = false }: VendorCardProps) {
  // Generar rating simulado basado en el ID del vendor (para demo)
  const generateRating = (id: string): number => {
    const hash = id.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return 3.5 + (hash % 15) / 10; // Rating entre 3.5 y 5.0
  };

  // Generar tiempo de entrega simulado
  const generateDeliveryTime = (id: string): string => {
    const hash = id.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const min = 20 + (hash % 20); // 20-40 minutos
    return `${min}-${min + 10} min`;
  };

  const rating = generateRating(vendor.id);
  const deliveryTime = generateDeliveryTime(vendor.id);
  const isNew = vendor.status === 'pending' || vendor.products.length < 5; // Consideramos "nuevo" si tiene pocos productos

  return (
    <Link href={`/vendor/${vendor.id}`} className="group block">
      <Card className="overflow-hidden transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1 h-full flex flex-col border-2 border-transparent hover:border-primary/20">
        <div className="relative w-full aspect-square">
          {/* Imagen del vendor */}
          <Image
            src={vendor.imageUrl}
            alt={`Logo de ${vendor.name}`}
            fill
            priority={priority}
            className="object-cover transition-transform duration-300 group-hover:scale-105"
            sizes="(max-width: 375px) 150px, (max-width: 640px) 200px, (max-width: 768px) 250px, (max-width: 1024px) 300px, 350px"
            data-ai-hint="logo"
          />

          {/* Overlay gradient en hover */}
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

          {/* Badges flotantes */}
          <div className="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 flex flex-col gap-1">
            {vendor.isFeatured && (
              <Badge className="bg-yellow-500 text-yellow-950 border-yellow-600 text-[9px] xs:text-[10px] px-1.5 xs:px-2 py-0.5 shadow-lg">
                <Sparkles className="h-2.5 w-2.5 xs:h-3 xs:w-3 mr-0.5 xs:mr-1" />
                Destacado
              </Badge>
            )}
            {isNew && (
              <Badge className="bg-green-500 text-green-950 border-green-600 text-[9px] xs:text-[10px] px-1.5 xs:px-2 py-0.5 shadow-lg">
                Nuevo
              </Badge>
            )}
            {vendor.status === 'inactive' && (
              <Badge variant="destructive" className="text-[9px] xs:text-[10px] px-1.5 xs:px-2 py-0.5 shadow-lg">
                Cerrado
              </Badge>
            )}
          </div>

          {/* Rating flotante */}
          <div className="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 flex items-center gap-0.5 xs:gap-1 bg-white/95 backdrop-blur-sm px-1.5 xs:px-2 py-0.5 xs:py-1 rounded-full shadow-lg">
            <Star className="h-2.5 w-2.5 xs:h-3 xs:w-3 fill-yellow-400 text-yellow-400" />
            <span className="text-[10px] xs:text-xs font-bold">{rating.toFixed(1)}</span>
          </div>
        </div>

        <CardContent className="flex flex-col flex-grow p-2 xs:p-2.5 sm:p-3 space-y-1.5 sm:space-y-2">
          {/* Nombre del vendor */}
          <h3 className="font-bold text-xs xs:text-sm sm:text-base leading-tight group-hover:text-primary transition-colors font-headline line-clamp-1">
            {vendor.name}
          </h3>

          {/* Categoría */}
          <div className="flex items-center justify-start">
            <Badge variant="secondary" className="text-[9px] xs:text-[10px] px-1.5 xs:px-2 py-0.5 h-auto font-medium">
              {vendor.category}
            </Badge>
          </div>

          {/* Tiempo de entrega */}
          <div className="flex items-center gap-1 xs:gap-1.5 text-muted-foreground">
            <Clock className="h-3 w-3 xs:h-3.5 xs:w-3.5 flex-shrink-0" />
            <span className="text-[10px] xs:text-xs">{deliveryTime}</span>
          </div>

          {/* Descripción (oculta en mobile, visible en desktop) */}
          <p className="text-[10px] xs:text-xs text-muted-foreground line-clamp-2 hidden sm:block">
            {vendor.description}
          </p>

          {/* Info adicional */}
          <div className="flex items-center justify-between text-[9px] xs:text-[10px] text-muted-foreground mt-auto pt-1.5 sm:pt-2 border-t">
            <span>{vendor.products.length} productos</span>
            {vendor.additionalFee && (
              <span className="text-primary font-medium">
                Envío desde S/.{vendor.additionalFee.toFixed(2)}
              </span>
            )}
          </div>
        </CardContent>
      </Card>
    </Link>
  );
}
