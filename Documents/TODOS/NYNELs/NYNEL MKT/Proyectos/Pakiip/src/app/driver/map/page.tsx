
"use client";

import { useState, useEffect } from 'react';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { Order, Vendor } from '@/lib/placeholder-data';
import { useAppData } from '@/hooks/use-app-data';
import { DeliveryMap, DeliveryPoint } from '@/components/DeliveryMap';
import { Skeleton } from '@/components/ui/skeleton';
import { AuthGuard } from "@/components/AuthGuard";

function GeneralDriverMapPageContent() {
    const { orders, vendors, currentUser } = useAppData();
    const [deliveryPoints, setDeliveryPoints] = useState<DeliveryPoint[]>([]);

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO hardcodeado
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;

    useEffect(() => {
        const activeOrders = orders.filter(o => 
            o.driverId === loggedInDriverId &&
            (o.status === 'Enviado' || o.status === 'Listo para Recoger' || o.status === 'Esperando Aceptación')
        );

        const points: DeliveryPoint[] = activeOrders.flatMap(order => {
            const vendor = vendors.find(v => v.name === order.items[0]?.vendor);
            if (!vendor || !order.customerCoordinates || !vendor.coordinates) {
                return [];
            }
            return [
                { type: 'store', location: vendor.coordinates, name: vendor.name, orderId: order.id },
                { type: 'customer', location: order.customerCoordinates, name: order.customerName, orderId: order.id }
            ];
        });

        // Remove duplicate stores
        const uniqueStores = Array.from(new Map(points.filter(p => p.type === 'store').map(p => [p.name, p])).values());
        const customerPoints = points.filter(p => p.type === 'customer');

        setDeliveryPoints([...uniqueStores, ...customerPoints]);
    }, [orders, vendors, loggedInDriverId]);

    return (
        <div className="h-[calc(100vh-8rem)] flex flex-col gap-3 sm:gap-4 px-2 sm:px-3 md:px-4">
             <Card>
                <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                    <CardTitle className="text-lg sm:text-xl md:text-2xl">Mapa de Entregas Activas</CardTitle>
                    <CardDescription className="text-sm">
                        Vista de todas las recogidas y entregas pendientes.
                    </CardDescription>
                </CardHeader>
            </Card>
            <div className="flex-grow rounded-lg overflow-hidden border shadow-sm h-64 sm:h-96">
                 <DeliveryMap points={deliveryPoints} />
            </div>
        </div>
    );
}

export default function GeneralDriverMapPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <GeneralDriverMapPageContent />
        </AuthGuard>
    );
}
