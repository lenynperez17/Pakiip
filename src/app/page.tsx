
"use client";

import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useAppData } from '@/hooks/use-app-data';
import { VendorCard } from '@/components/VendorCard';
import { MapPin, Search, Sparkles } from "lucide-react";
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Carousel, CarouselContent, CarouselItem } from '@/components/ui/carousel';
import Autoplay from "embla-carousel-autoplay";
import { ProductCard } from '@/components/ProductCard';
import { Product } from '@/lib/placeholder-data';
import { Skeleton } from '@/components/ui/skeleton';
import { filterVendorsByDistance } from '@/lib/distance-calculator';

export default function Home() {
  const {
    vendors,
    categories,
    appSettings,
    selectedCity,
    getAllProducts,
    isLoading,
    // Ubicación compartida del contexto
    userLocation,
    isLoadingLocation,
    refreshLocation
  } = useAppData();
  const [searchTerm, setSearchTerm] = useState('');
  const [filteredVendors, setFilteredVendors] = useState(vendors);
  const [maxDistanceKm] = useState(20); // Radio de búsqueda: 20km
  const [showingAllVendors, setShowingAllVendors] = useState(false);

  // Filtrar vendors: radio GPS → ciudad → todas
  useEffect(() => {
    if (isLoading) return;

    const activeVendors = vendors.filter(v => v.status === 'Activo');

    // PASO 1: Si hay GPS, intentar filtrar por radio de distancia
    if (userLocation) {
      const nearbyVendors = filterVendorsByDistance(
        activeVendors, userLocation.latitude, userLocation.longitude, maxDistanceKm
      ).filter(v =>
        v.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        v.category.toLowerCase().includes(searchTerm.toLowerCase())
      );

      if (nearbyVendors.length > 0) {
        setFilteredVendors(nearbyVendors);
        setShowingAllVendors(false);
        return;
      }
      // Sin tiendas en el radio → caer al fallback de ciudad
    }

    // PASO 2: Fallback — filtrar por ciudad seleccionada
    const cityFiltered = selectedCity
      ? activeVendors.filter(v => v.location === selectedCity || v.city === selectedCity)
      : activeVendors;

    const searchFiltered = cityFiltered.filter(v =>
      v.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      v.category.toLowerCase().includes(searchTerm.toLowerCase())
    );

    setFilteredVendors(searchFiltered);
    setShowingAllVendors(true);
  }, [vendors, searchTerm, userLocation, maxDistanceKm, isLoading, selectedCity]);


  const featuredVendors = filteredVendors.filter(v => v.isFeatured);

  // Mostrar TODAS las ofertas (sin filtrar por ciudad)
  const offerProducts: Product[] = getAllProducts().filter(p => p.isOffer);

  // Mostrar TODOS los banners de anuncios (sin filtrar por ciudad)
  const cityAnnouncementBanners = appSettings?.announcementBanners || [];
  const promotionalBanners = appSettings?.promotionalBanners || [];

   // Eliminado: No mostrar mensaje de "Selecciona tu ubicación"
  // La app usa geolocalización automática para mostrar vendedores cercanos

  return (
      <div className="space-y-6 sm:space-y-8 py-4 sm:py-6">
           <div className="relative">
             <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
             <Input
                placeholder="Busca tiendas o categorías..."
                className="pl-10 w-full h-12 text-base"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
            />
           </div>

        {/* Featured Vendors - DEBAJO DEL BUSCADOR */}
            {!searchTerm && featuredVendors.length > 0 && (
            <section className="space-y-3 sm:space-y-4" aria-label="Tiendas destacadas">
                <div className="flex items-center gap-2">
                  <Sparkles className="h-5 w-5 text-primary" />
                  <h2 className="font-bold font-headline text-lg sm:text-xl">Tiendas Destacadas</h2>
                </div>
                <div className="flex overflow-x-auto space-x-3 sm:space-x-4 md:space-x-5 lg:space-x-6 pb-4 scrollbar-hide snap-x snap-mandatory">
                    {featuredVendors.map((vendor) => (
                        <Link
                          key={vendor.id}
                          href={`/vendor/${vendor.id}`}
                          className="group flex flex-col items-center gap-2 flex-shrink-0 w-24 xs:w-28 sm:w-32 md:w-36 lg:w-40 snap-start"
                        >
                            <div className="w-24 h-24 xs:w-28 xs:h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 lg:w-40 lg:h-40 rounded-full overflow-hidden shadow-lg border-2 border-transparent group-hover:border-primary transition-all duration-300 transform group-hover:scale-105">
                                <Image
                                    src={vendor.imageUrl}
                                    alt={vendor.name}
                                    width={160}
                                    height={160}
                                    className="object-cover w-full h-full"
                                    sizes="(max-width: 640px) 96px, (max-width: 768px) 112px, (max-width: 1024px) 128px, 160px"
                                    data-ai-hint="logo"
                                />
                            </div>
                            <p className="text-xs sm:text-sm md:text-base font-semibold text-center truncate w-full group-hover:text-primary transition-colors duration-200">{vendor.name}</p>
                        </Link>
                    ))}
                </div>
            </section>
            )}

          {/* Promotional Banners */}
          {!searchTerm && promotionalBanners.length > 0 && (
          <section>
            <Carousel
              plugins={[Autoplay({ delay: 5000, stopOnInteraction: true })]}
              opts={{ loop: true }}
              className="w-full"
            >
              <CarouselContent>
                {promotionalBanners.map((banner) => (
                  <CarouselItem key={banner.id}>
                    <Link href={banner.link}>
                      <Card className="overflow-hidden">
                        <CardContent className="p-0">
                          <div className="aspect-[16/9] sm:aspect-[2/1] relative">
                            <Image
                              src={banner.imageUrl}
                              alt={banner.title}
                              fill
                              className="object-cover"
                              sizes="(max-width: 640px) 100vw, (max-width: 1024px) 90vw, 80vw"
                              data-ai-hint={banner.imageHint}
                            />
                          </div>
                        </CardContent>
                      </Card>
                    </Link>
                  </CarouselItem>
                ))}
              </CarouselContent>
            </Carousel>
          </section>
          )}

          {/* Offers Section */}
          {!searchTerm && offerProducts.length > 0 && (
            <section className="space-y-3 sm:space-y-4" aria-label="Ofertas especiales">
                <h2 className="text-lg sm:text-xl font-bold font-headline">¡No te pierdas estas ofertas!</h2>
                <div className="flex overflow-x-auto space-x-3 sm:space-x-4 md:space-x-5 lg:space-x-6 pb-4 scrollbar-hide snap-x snap-mandatory">
                    {offerProducts.map((product) => (
                       <div key={product.id} className="flex-shrink-0 w-48 xs:w-52 sm:w-60 md:w-64 lg:w-72 snap-start">
                           <ProductCard product={product} />
                       </div>
                    ))}
                </div>
            </section>
           )}

          {/* Categories Section */}
            {!searchTerm && categories.length > 0 && (
            <section className="space-y-3 sm:space-y-4" aria-label="Categorías de tiendas">
                <h2 className="font-bold font-headline text-lg sm:text-xl">Explorar Categorías</h2>
                <Carousel opts={{ align: "start", dragFree: true }} className="w-full">
                <CarouselContent className="-ml-2 sm:-ml-4">
                    {categories.map((category) => (
                    <CarouselItem key={category.id} className="basis-1/3 xs:basis-1/4 sm:basis-1/5 md:basis-1/6 lg:basis-1/7 pl-2 sm:pl-4">
                        <Link href={`/?category=${category.name}`} className="group flex flex-col items-center gap-2">
                            <div className="w-20 h-20 xs:w-24 xs:h-24 sm:w-28 sm:h-28 rounded-full overflow-hidden shadow-md border-2 border-transparent group-hover:border-primary transition-all duration-300 transform group-hover:scale-105">
                                <Image
                                    src={category.imageUrl}
                                    alt={category.name}
                                    width={112}
                                    height={112}
                                    className="object-cover w-full h-full"
                                    sizes="(max-width: 640px) 80px, (max-width: 768px) 96px, 112px"
                                    data-ai-hint={category.imageHint}
                                />
                            </div>
                            <p className="text-xs sm:text-sm font-semibold text-center truncate w-full group-hover:text-primary transition-colors duration-200">{category.name}</p>
                        </Link>
                    </CarouselItem>
                    ))}
                </CarouselContent>
                </Carousel>
            </section>
            )}
            
            <section aria-label="Listado de tiendas">
                <div className="flex flex-col md:flex-row items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-4">
                    <h2 className="text-base sm:text-lg font-bold font-headline">
                        {searchTerm ? `Resultados para "${searchTerm}"` :
                         !showingAllVendors ? "Tiendas Cercanas" :
                         selectedCity ? `Tiendas en ${selectedCity}` :
                         "Todas las Tiendas"}
                    </h2>
                    {!searchTerm && filteredVendors.length > 0 && (
                      <p className="text-xs sm:text-sm text-muted-foreground">
                        {filteredVendors.length} {filteredVendors.length === 1 ? 'tienda' : 'tiendas'}
                        {!showingAllVendors && ` a menos de ${maxDistanceKm}km`}
                      </p>
                    )}
                </div>

                {/* Mensaje sutil con info de filtrado */}
                {showingAllVendors && !isLoadingLocation && selectedCity && (
                  <p className="text-xs text-muted-foreground mb-3 flex items-center gap-1.5">
                    <MapPin className="h-3 w-3" />
                    Mostrando tiendas en {selectedCity}.
                    {!userLocation && (
                      <button
                        onClick={() => refreshLocation()}
                        className="text-primary hover:underline"
                      >
                        Activar GPS para ver las más cercanas
                      </button>
                    )}
                  </p>
                )}

                {isLoading ? (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3 md:gap-4 lg:gap-5">
                  {[...Array(6)].map((_, i) => (
                    <Skeleton key={i} className="h-48 sm:h-52 rounded-lg" />
                  ))}
                </div>
                ) : filteredVendors.length > 0 ? (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3 md:gap-4 lg:gap-5">
                    {filteredVendors.map((vendor, index) => (
                    <VendorCard key={vendor.id} vendor={vendor} priority={index === 0} />
                    ))}
                </div>
                ) : (
                <div className="text-center py-12 sm:py-16 space-y-4">
                  <div className="text-5xl sm:text-6xl">🏪</div>
                  {searchTerm ? (
                    <p className="text-sm sm:text-base text-muted-foreground">
                      No se encontraron tiendas que coincidan con tu búsqueda.
                    </p>
                  ) : (
                    <div className="space-y-2">
                      <p className="text-base sm:text-lg font-semibold">
                        No hay tiendas en {selectedCity || "tu zona"}
                      </p>
                      <p className="text-sm sm:text-base text-muted-foreground">
                        Estamos trabajando para abrir más tiendas
                      </p>
                    </div>
                  )}
                </div>
                )}
            </section>

        {/* Announcements Section */}
        {!searchTerm && cityAnnouncementBanners.length > 0 && (
          <section className="space-y-3 sm:space-y-4" aria-label="Anuncios">
            <h2 className="font-bold font-headline text-base sm:text-lg">Anuncios</h2>
             <Carousel
              opts={{ loop: true, align: "start" }}
              className="w-full"
            >
              <CarouselContent className="-ml-4">
                {cityAnnouncementBanners.map((banner) => (
                  <CarouselItem key={banner.id} className="pl-4 basis-1/2 md:basis-1/3 lg:basis-1/4">
                     <Link href={banner.link}>
                        <Card className="overflow-hidden group">
                           <div className="aspect-square relative overflow-hidden">
                             <Image
                                src={banner.imageUrl}
                                alt={banner.title}
                                fill
                                sizes="(max-width: 768px) 50vw, (max-width: 1024px) 33vw, 25vw"
                                className="object-cover transition-transform duration-300 group-hover:scale-105"
                                data-ai-hint={banner.imageHint}
                              />
                           </div>
                           <div className="p-3 bg-card">
                              <h3 className="font-semibold text-sm truncate">{banner.title}</h3>
                           </div>
                        </Card>
                     </Link>
                  </CarouselItem>
                ))}
              </CarouselContent>
            </Carousel>
          </section>
          )}

      </div>
  );
}
