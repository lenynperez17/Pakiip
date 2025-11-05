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
  const { getVendorById } = useAppData();
  const [vendor, setVendor] = useState<Vendor | null | undefined>(undefined);

  useEffect(() => {
    const vendorData = getVendorById(vendorId);
    setVendor(vendorData || null);
  }, [vendorId, getVendorById]);

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

  if (vendor === null) {
    notFound();
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
        {vendor.productCategories?.length > 0 ? (
        vendor.productCategories.map(category => {
            const productsInCategory = vendor.products.filter(p => p.vendorCategoryId === category.id);
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
            )
        })
        ) : (
        <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            {vendor.products.map((product) => (
            <ProductCard key={product.id} product={product} />
            ))}
        </div>
        )}
        {vendor.products.length === 0 && (
            <div className="text-center py-12 text-muted-foreground">
                <p>Esta tienda aún no tiene productos.</p>
            </div>
        )}
    </div>
  );
}
