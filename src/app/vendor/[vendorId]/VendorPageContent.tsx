// src/app/vendor/[vendorId]/VendorPageContent.tsx
"use client";

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { notFound } from 'next/navigation';
import { useAppData } from '@/hooks/use-app-data';
import { Vendor } from '@/lib/placeholder-data';
import { ProductCard } from '@/components/ProductCard';
import { MapPin, Utensils } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';

// This is a pure Client Component. It receives vendorId as a prop.
export default function VendorPageContent({ vendorId }: { vendorId: string }) {
  const { getVendorById, vendors } = useAppData();
  const [vendor, setVendor] = useState<Vendor | null | undefined>(undefined);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    try {
      console.log('[VendorPage] Buscando vendor con ID:', vendorId);
      console.log('[VendorPage] Total de vendors disponibles:', vendors?.length || 0);
      console.log('[VendorPage] IDs de vendors:', vendors?.map(v => v.id) || []);

      const vendorData = getVendorById(vendorId);
      console.log('[VendorPage] Vendor encontrado:', vendorData ? vendorData.name : 'NO ENCONTRADO');

      if (vendorData) {
        console.log('[VendorPage] Productos del vendor:', vendorData.products?.length || 0);
        console.log('[VendorPage] Categorías del vendor:', vendorData.productCategories?.length || 0);
        console.log('[VendorPage] Categorías IDs:', vendorData.productCategories?.map(c => c.id) || []);
        console.log('[VendorPage] Productos vendorCategoryIds:', vendorData.products?.map(p => ({ name: p.name, catId: p.vendorCategoryId })) || []);
      }

      setVendor(vendorData || null);
      setError(null);
    } catch (err) {
      console.error('[VendorPage] Error al buscar vendor:', err);
      setError(err instanceof Error ? err.message : 'Error desconocido');
      setVendor(null);
    }
  }, [vendorId, getVendorById, vendors]);

  if (vendor === undefined) {
    // Loading state
    return (
        <div>
            <Skeleton className="w-full h-48 md:h-64" />
            <div className="container mx-auto px-4 py-12">
                <Skeleton className="h-9 w-1/3 mb-8" />
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <Skeleton className="h-80 w-full" />
                    <Skeleton className="h-80 w-full" />
                    <Skeleton className="h-80 w-full" />
                    <Skeleton className="h-80 w-full" />
                </div>
            </div>
        </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto max-w-7xl px-4 py-8">
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <h2 className="text-red-800 font-bold">Error al cargar la tienda</h2>
          <p className="text-red-600 mt-2">{error}</p>
          <p className="text-red-500 text-sm mt-2">ID buscado: {vendorId}</p>
        </div>
      </div>
    );
  }

  if (vendor === null) {
    return (
      <div className="container mx-auto max-w-7xl px-4 py-8">
        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h2 className="text-yellow-800 font-bold">Tienda no encontrada</h2>
          <p className="text-yellow-600 mt-2">No se encontró la tienda con ID: {vendorId}</p>
          <p className="text-yellow-500 text-sm mt-2">Vendors disponibles: {vendors?.length || 0}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto max-w-7xl px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-8">
        <div className="flex flex-col items-center text-center mb-6 sm:mb-8">
            <div className="relative w-20 h-20 xs:w-24 xs:h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-2xl overflow-hidden border-2 sm:border-4 border-background shadow-lg mb-3 sm:mb-4">
                <Image
                    src={vendor.imageUrl}
                    alt={`Logo de ${vendor.name}`}
                    fill
                    className="object-cover"
                    sizes="(max-width: 640px) 80px, (max-width: 768px) 112px, 128px"
                    data-ai-hint="logo"
                />
            </div>
            <h1 className="text-2xl xs:text-3xl md:text-4xl lg:text-5xl font-bold font-headline px-2">{vendor.name}</h1>
            <p className="mt-1 sm:mt-2 text-sm sm:text-base md:text-lg text-muted-foreground max-w-xl px-4">{vendor.description}</p>
            <div className="mt-2 sm:mt-3 flex flex-wrap items-center justify-center gap-1.5 sm:gap-2">
                <Badge variant="outline" className="text-xs sm:text-sm">
                    <Utensils className="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" />
                    {vendor.category}
                </Badge>
                <Badge variant="outline" className="text-xs sm:text-sm">
                    <MapPin className="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" />
                    {vendor.location}
                </Badge>
            </div>
        </div>

        <h2 className="text-xl sm:text-2xl md:text-3xl font-bold font-headline mb-4 sm:mb-6 md:mb-8 border-b pb-2">Productos</h2>
        {(() => {
            const allProducts = vendor.products || [];
            const categories = vendor.productCategories || [];

            // Productos con categoría asignada
            const categorizedProducts = allProducts.filter(p => p.vendorCategoryId);
            // Productos sin categoría
            const uncategorizedProducts = allProducts.filter(p => !p.vendorCategoryId);

            // Si hay categorías y productos categorizados, mostrar por categorías
            if (categories.length > 0 && categorizedProducts.length > 0) {
                return (
                    <>
                        {categories.map(category => {
                            const productsInCategory = categorizedProducts.filter(p => p.vendorCategoryId === category.id);
                            if (productsInCategory.length === 0) return null;
                            return (
                                <div key={category.id} className="mb-8 sm:mb-10 md:mb-12">
                                    <h3 className="text-xl sm:text-2xl font-semibold font-headline mb-4 sm:mb-6">{category.name}</h3>
                                    <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                        {productsInCategory.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                        {/* Productos sin categoría */}
                        {uncategorizedProducts.length > 0 && (
                            <div className="mb-8 sm:mb-10 md:mb-12">
                                <h3 className="text-xl sm:text-2xl font-semibold font-headline mb-4 sm:mb-6">Otros productos</h3>
                                <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                    {uncategorizedProducts.map((product) => (
                                        <ProductCard key={product.id} product={product} />
                                    ))}
                                </div>
                            </div>
                        )}
                    </>
                );
            }

            // Si no hay categorías o no hay productos categorizados, mostrar todos los productos
            if (allProducts.length > 0) {
                return (
                    <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                        {allProducts.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                );
            }

            // Sin productos
            return (
                <div className="text-center py-12 text-muted-foreground">
                    <p>Esta tienda aún no tiene productos.</p>
                </div>
            );
        })()}
    </div>
  );
}
