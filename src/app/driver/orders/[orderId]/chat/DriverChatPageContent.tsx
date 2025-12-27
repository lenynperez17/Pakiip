// src/app/driver/orders/[orderId]/chat/DriverChatPageContent.tsx
"use client";

import { useState, useEffect } from 'react';
import { notFound, useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { Order, DeliveryDriver } from '@/lib/placeholder-data';
import { ChatInterface } from '@/components/ChatInterface';
import { ArrowLeft, User } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';

// This is a pure Client Component. It receives orderId as a prop.
export default function DriverChatPageContent({ orderId }: { orderId: string }) {
    const { orders, drivers, currentUser } = useAppData();
    const [order, setOrder] = useState<Order | null>(null);
    const [driver, setDriver] = useState<DeliveryDriver | null>(null);
    const router = useRouter();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO hardcodeado
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;

    useEffect(() => {
        const foundOrder = orders.find(o => o.id === orderId);
        // Security check: ensure the order belongs to the logged-in driver
        if (foundOrder && foundOrder.driverId === loggedInDriverId) {
            setOrder(foundOrder);
            const foundDriver = drivers.find(d => d.id === foundOrder.driverId);
            setDriver(foundDriver || null);
        } else {
            setOrder(null);
        }
    }, [orderId, orders, drivers, loggedInDriverId]);

    if (order === null) {
        notFound();
    }

    return (
        <div className="px-2 sm:px-3 md:px-4">
            <div className="mb-3 sm:mb-4">
                 <Button variant="outline" size="sm" onClick={() => router.push('/driver/orders')} className="text-xs sm:text-sm">
                    <ArrowLeft className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Volver a Pedidos
                </Button>
            </div>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                <div className="lg:col-span-1">
                     <Card>
                        <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                            <CardTitle className="text-base sm:text-lg">Pedido {order.id}</CardTitle>
                            <CardDescription className="text-xs sm:text-sm">
                                Asignado a ti el {new Date(order.date).toLocaleDateString()}.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
                            <div>
                                <h4 className="font-semibold text-sm sm:text-base mb-2 flex items-center gap-2"><User className="h-3 w-3 sm:h-4 sm:w-4" /> Cliente</h4>
                                <p className="text-sm sm:text-base text-muted-foreground">{order.customerName}</p>
                            </div>
                            <div>
                                <h4 className="font-semibold text-sm sm:text-base mb-2">Artículos</h4>
                                <ul className="list-disc list-inside text-xs sm:text-sm text-muted-foreground space-y-1">
                                    {order.items.map(item => (
                                        <li key={item.productName}>{item.quantity}x {item.productName}</li>
                                    ))}
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <div className="lg:col-span-2">
                    <Card className="h-[60vh] sm:h-[70vh] flex flex-col">
                        <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                            <CardTitle className="text-base sm:text-lg">Chat con {order.customerName}</CardTitle>
                        </CardHeader>
                        {driver ? (
                             <ChatInterface
                                orderId={order.id}
                                userType="driver"
                                userName={driver.name}
                                receiverName={order.customerName}
                            />
                        ) : (
                             <div className="flex-grow flex items-center justify-center">
                                <p className="text-sm sm:text-base">Cargando información del chat...</p>
                            </div>
                        )}
                    </Card>
                </div>
            </div>
        </div>
    );
}
