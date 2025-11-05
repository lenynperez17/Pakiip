
"use client";

import React from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Card, CardDescription, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Package, ListOrdered, BarChartHorizontal, Settings, Store } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { AuthGuard } from "@/components/AuthGuard";

function VendorDashboardPageContent() {
  const { vendors, orders, currentUser } = useAppData();
  const searchParams = useSearchParams();

  // Obtener vendorId de la URL o del currentUser si está logueado como vendor
  const vendorIdFromUrl = searchParams.get('vendorId');
  const vendorId = vendorIdFromUrl || (currentUser?.role === 'vendor' ? currentUser.id : null);

  // Buscar el vendor por ID o por email del currentUser
  const vendor = vendors.find(v =>
    v.id === vendorId ||
    (currentUser?.role === 'vendor' && v.email === currentUser.email)
  );

  if (!vendor) {
    return (
      <div className="flex items-center justify-center h-full">
         <Card>
            <CardContent className="py-12 flex flex-col items-center justify-center text-center">
                <Store className="h-12 w-12 text-muted-foreground mb-4"/>
                <h3 className="text-xl font-semibold">Tienda no encontrada</h3>
                <p className="text-muted-foreground mt-2">No se pudo cargar la información. Por favor, inicia sesión de nuevo.</p>
            </CardContent>
        </Card>
      </div>
    );
  }

  const navItems = [
    {
      href: `/vendor/dashboard/inventory?vendorId=${vendor.id}`,
      icon: Package,
      title: 'Inventario',
    },
    {
      href: `/vendor/orders?vendorId=${vendor.id}`,
      icon: ListOrdered,
      title: 'Pedidos',
    },
    {
      href: `/vendor/sales?vendorId=${vendor.id}`,
      icon: BarChartHorizontal,
      title: 'Reporte de Ventas',
    },
    {
      href: `/vendor/settings?vendorId=${vendor.id}`,
      icon: Settings,
      title: 'Configuración',
    },
  ];

  const activeProducts = vendor.products.filter(p => p.stock > 0).length;
  const pendingOrders = orders.filter(o => o.items.some(i => i.vendor === vendor.name) && o.status === 'Procesando').length;

  return (
    <div className="space-y-4 sm:space-y-6 md:space-y-8">
      <div className="flex items-center justify-between mb-4 sm:mb-6 md:mb-8 px-2 sm:px-3 md:px-4">
        <div>
          <h1 className="text-2xl xs:text-3xl md:text-4xl lg:text-5xl font-bold font-headline">Panel de {vendor.name}</h1>
          <p className="text-xs sm:text-sm md:text-base text-muted-foreground mt-1">Tu centro de mando para gestionar tu tienda en línea.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-5 px-2 sm:px-3 md:px-4">
         <Card className="bg-muted/50 border-dashed">
            <CardHeader className="flex-row items-center gap-3 sm:gap-4 space-y-0 p-3 sm:p-4 md:p-6">
                <Package className="h-6 w-6 sm:h-7 sm:w-7 md:h-8 md:w-8 text-muted-foreground flex-shrink-0" />
                <div className="min-w-0 flex-1">
                    <CardTitle className="text-base sm:text-lg md:text-xl">{activeProducts} Productos Activos</CardTitle>
                </div>
            </CardHeader>
        </Card>
        <Card className="bg-muted/50 border-dashed">
            <CardHeader className="flex-row items-center gap-3 sm:gap-4 space-y-0 p-3 sm:p-4 md:p-6">
                <ListOrdered className="h-6 w-6 sm:h-7 sm:w-7 md:h-8 md:w-8 text-muted-foreground flex-shrink-0" />
                <div className="min-w-0 flex-1">
                    <CardTitle className="text-base sm:text-lg md:text-xl">{pendingOrders} Pedidos Pendientes</CardTitle>
                </div>
            </CardHeader>
        </Card>
        <Card className="bg-muted/50 border-dashed">
            <CardHeader className="flex-row items-center gap-3 sm:gap-4 space-y-0 p-3 sm:p-4 md:p-6">
                <Store className="h-6 w-6 sm:h-7 sm:w-7 md:h-8 md:w-8 text-primary flex-shrink-0" />
                <div className="min-w-0 flex-1">
                    <CardTitle className="text-base sm:text-lg md:text-xl">{vendor.status}</CardTitle>
                </div>
            </CardHeader>
        </Card>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-5 lg:gap-6 px-2 sm:px-3 md:px-4">
        {navItems.map((item) => (
          <Link href={item.href} key={item.title} className="block hover:-translate-y-1 transition-transform duration-200">
            <Card className="h-full">
              <CardHeader className="p-3 sm:p-4 md:p-6">
                  <div className="p-2 sm:p-3 bg-muted rounded-lg w-fit mb-3 sm:mb-4">
                    <item.icon className="h-5 w-5 sm:h-6 sm:w-6 text-primary" />
                  </div>
                  <CardTitle className="text-base sm:text-lg md:text-xl">{item.title}</CardTitle>
              </CardHeader>
            </Card>
          </Link>
        ))}
      </div>
    </div>
  );
}


// Use a wrapper to handle Suspense for useSearchParams
export default function VendorDashboardPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="vendor" redirectTo="/vendor/login">
            <React.Suspense fallback={<div>Cargando panel...</div>}>
                <VendorDashboardPageContent />
            </React.Suspense>
        </AuthGuard>
    )
}
