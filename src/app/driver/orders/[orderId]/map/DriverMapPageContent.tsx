// src/app/driver/orders/[orderId]/map/DriverMapPageContent.tsx
"use client";

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { Order, Vendor } from '@/lib/placeholder-data';
import { ArrowLeft, User, Store } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';
import { DeliveryMap } from '@/components/DeliveryMap';
import { Skeleton } from '@/components/ui/skeleton';

// This is a pure Client Component. It receives orderId as a prop.
export default function DriverMapPageContent({ orderId }: { orderId: string }) {
    const { orders, vendors, currentUser } = useAppData();
    const [order, setOrder] = useState<Order | null>(null);
    const [vendor, setVendor] = useState<Vendor | null>(null);
    const router = useRouter();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;

    useEffect(() => {
        // ✅ Validar que el pedido pertenezca al driver logueado
        const foundOrder = orders.find(o => o.id === orderId && o.driverId === loggedInDriverId);
        if (foundOrder) {
            setOrder(foundOrder);
            // Assuming the first item's vendor is the main one for this order
            const mainVendorName = foundOrder.items[0]?.vendor;
            if (mainVendorName) {
                const foundVendor = vendors.find(v => v.name === mainVendorName);
                setVendor(foundVendor || null);
            }
        } else {
            setOrder(null);
        }
    }, [orderId, orders, vendors, loggedInDriverId]);
    
    if (order === null || vendor === null) {
        return (
             <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-1">
                    <Skeleton className="h-48 w-full" />
                </div>
                <div className="lg:col-span-2 h-[70vh]">
                     <Skeleton className="h-full w-full" />
                </div>
            </div>
        );
    }
    
    if (!order.customerCoordinates || !vendor.coordinates) {
        return (
            <div className="flex items-center justify-center h-full">
                <p>Faltan datos de ubicación para este pedido.</p>
            </div>
        )
    }

    const deliveryPoints = [{
        type: 'store' as const,
        location: vendor.coordinates,
        name: vendor.name,
    }, {
        type: 'customer' as const,
        location: order.customerCoordinates,
        name: order.customerName,
    }];

    return (
        <div className="px-2 sm:px-3 md:px-4">
            <div className="mb-3 sm:mb-4">
                 <Button variant="outline" size="sm" onClick={() => router.push('/driver/orders')} className="text-xs sm:text-sm">
                    <ArrowLeft className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Volver a Pedidos
                </Button>
            </div>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 items-start">
                <div className="lg:col-span-1">
                     <Card>
                        <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                            <CardTitle className="text-base sm:text-lg">Ruta para Pedido {order.id}</CardTitle>
                            <CardDescription className="text-xs sm:text-sm">
                                Recoge en {vendor.name} y entrega a {order.customerName}.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
                            <div>
                                <h4 className="font-semibold text-sm sm:text-base mb-2 flex items-center gap-2"><Store className="h-3 w-3 sm:h-4 sm:w-4" /> Recoger en</h4>
                                <p className="text-sm sm:text-base text-muted-foreground">{vendor.name}</p>
                                <p className="text-xs sm:text-sm text-muted-foreground/80">{vendor.address}</p>
                            </div>
                            <div>
                                <h4 className="font-semibold text-sm sm:text-base mb-2 flex items-center gap-2"><User className="h-3 w-3 sm:h-4 sm:w-4" /> Entregar a</h4>
                                <p className="text-sm sm:text-base text-muted-foreground">{order.customerName}</p>
                                <p className="text-xs sm:text-sm text-muted-foreground/80">{order.customerAddress}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <div className="lg:col-span-2 h-64 sm:h-96 lg:h-[70vh] rounded-lg overflow-hidden border shadow-sm">
                    <DeliveryMap points={deliveryPoints} />
                </div>
            </div>
        </div>
    );
}
