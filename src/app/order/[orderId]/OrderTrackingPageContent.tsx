
// src/app/order/[orderId]/OrderTrackingPageContent.tsx
"use client";

import { useState, useEffect } from 'react';
import { notFound } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import type { Order, DeliveryDriver } from '@/lib/placeholder-data';
import { ChatInterface } from '@/components/ChatInterface';
import { Truck, User, KeyRound } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';
import { formatCurrency } from '@/lib/utils';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

// This is a pure Client Component. It receives orderId as a prop.
export default function OrderTrackingPageContent({ orderId }: { orderId: string }) {
    const { orders, drivers, appSettings: settings } = useAppData();
    const [order, setOrder] = useState<Order | null>(null);
    const [driver, setDriver] = useState<DeliveryDriver | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [notFoundError, setNotFoundError] = useState(false);

    useEffect(() => {
        // Primero buscar en los datos locales
        const foundOrder = orders.find(o => o.id === orderId);
        if (foundOrder) {
            setOrder(foundOrder);
            if (foundOrder.driverId) {
                const foundDriver = drivers.find(d => d.id === foundOrder.driverId);
                setDriver(foundDriver || null);
            }
            setIsLoading(false);
            return;
        }

        // Si no está en local, buscar en la API directamente
        const fetchOrder = async () => {
            try {
                const response = await fetch(`/api/data/orders?id=${orderId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    // Si la API devuelve un array, buscar el pedido específico
                    const orderData = Array.isArray(result.data)
                        ? result.data.find((o: Order) => o.id === orderId)
                        : result.data;

                    if (orderData) {
                        setOrder(orderData);
                        if (orderData.driverId) {
                            const foundDriver = drivers.find(d => d.id === orderData.driverId);
                            setDriver(foundDriver || null);
                        }
                    } else {
                        setNotFoundError(true);
                    }
                } else {
                    setNotFoundError(true);
                }
            } catch (error) {
                console.error('Error fetching order:', error);
                setNotFoundError(true);
            } finally {
                setIsLoading(false);
            }
        };

        fetchOrder();
    }, [orderId, orders, drivers]);

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    };

    // Mostrar carga mientras se obtienen los datos
    if (isLoading) {
        return (
            <div className="container mx-auto max-w-6xl px-4 py-12 text-center">
                <div className="animate-pulse">
                    <div className="h-8 bg-muted rounded w-48 mx-auto mb-4"></div>
                    <div className="h-4 bg-muted rounded w-32 mx-auto"></div>
                </div>
                <p className="text-muted-foreground mt-4">Cargando pedido...</p>
            </div>
        );
    }

    // Mostrar 404 si no se encontró
    if (notFoundError || order === null) {
        notFound();
    }
    
    const getStatusBadge = (status: Order['status']) => {
        const baseClasses = "text-xs sm:text-sm px-2 py-0.5 sm:px-2.5 sm:py-1";
        switch (status) {
          case 'Procesando':
            return <Badge variant="secondary" className={`bg-yellow-400 text-yellow-900 hover:bg-yellow-400/80 ${baseClasses}`}>{status}</Badge>;
          case 'Listo para Recoger':
            return <Badge variant="secondary" className={`bg-sky-500 text-white hover:bg-sky-500/80 ${baseClasses}`}>
                <span className="hidden sm:inline">Listo para Recoger</span>
                <span className="sm:hidden">Listo</span>
            </Badge>;
          case 'Esperando Aceptación':
            return <Badge variant="secondary" className={`bg-orange-500 text-white hover:bg-orange-500/80 ${baseClasses}`}>
                <span className="hidden sm:inline">Buscando Repartidor</span>
                <span className="sm:hidden">Buscando</span>
            </Badge>;
          case 'Enviado':
            return <Badge variant="secondary" className={`bg-blue-500 text-white hover:bg-blue-500/80 ${baseClasses}`}>{status}</Badge>;
          case 'Entregado':
            return <Badge variant="secondary" className={`bg-green-500 text-white hover:bg-green-500/80 ${baseClasses}`}>{status}</Badge>;
          case 'Cancelado':
            return <Badge variant="destructive" className={baseClasses}>{status}</Badge>;
          default:
            return <Badge className={baseClasses}>{status}</Badge>;
        }
    };

    return (
        <div className="container mx-auto max-w-6xl px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-12">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                <div className="lg:col-span-1 space-y-3 sm:space-y-4 lg:space-y-6">
                    <Card>
                        <CardHeader className="p-3 sm:p-4 md:p-6">
                            <CardTitle className="text-lg sm:text-xl">Pedido {order.id}</CardTitle>
                            <CardDescription className="text-xs sm:text-sm">Realizado el {new Date(order.date).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 sm:space-y-4 p-3 sm:p-4 md:p-6">
                            <div className="flex justify-between items-center gap-2">
                                <span className="text-muted-foreground text-xs sm:text-sm">Estado</span>
                                {getStatusBadge(order.status)}
                            </div>
                            <Separator />
                            <div>
                                <h4 className="font-semibold mb-2 text-sm sm:text-base">Resumen</h4>
                                {order.items.map(item => (
                                    <div key={item.productName} className="flex justify-between text-xs sm:text-sm gap-2">
                                        <span className="break-words">{item.quantity} x {item.productName}</span>
                                        <span className="flex-shrink-0">{formatCurrency(item.quantity * item.price, settings.currencySymbol)}</span>
                                    </div>
                                ))}
                                <Separator className="my-2" />
                                <div className="flex justify-between font-bold text-sm sm:text-base gap-2">
                                    <span>Total</span>
                                    <span className="flex-shrink-0">{formatCurrency(order.total, settings.currencySymbol)}</span>
                                </div>
                            </div>
                            <Separator />
                             {driver && (
                                <div>
                                    <h4 className="font-semibold mb-2 text-sm sm:text-base">Tu Repartidor</h4>
                                    <div className="flex items-center gap-2 sm:gap-3">
                                        <Avatar className="h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0">
                                            {driver.profileImageUrl && <AvatarImage src={driver.profileImageUrl} alt={driver.name} />}
                                            <AvatarFallback className="text-xs sm:text-sm">{getInitials(driver.name)}</AvatarFallback>
                                        </Avatar>
                                        <div className="min-w-0">
                                            <p className="font-medium text-sm sm:text-base truncate">{driver.name}</p>
                                            <p className="text-xs sm:text-sm text-muted-foreground truncate">{driver.vehicle}</p>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    {order.status !== 'Entregado' && order.status !== 'Cancelado' && order.verificationCode && (
                         <Card>
                            <CardHeader className="p-3 sm:p-4 md:p-6">
                                <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
                                    <KeyRound className="h-4 w-4 sm:h-5 sm:w-5"/>
                                    Código de Entrega
                                </CardTitle>
                                <CardDescription className="text-xs sm:text-sm">Entrega este código a tu repartidor para confirmar que has recibido tu pedido.</CardDescription>
                            </CardHeader>
                            <CardContent className="text-center p-3 sm:p-4 md:p-6">
                                <p className="text-2xl sm:text-3xl md:text-4xl font-bold tracking-[0.3em] sm:tracking-[0.5em] bg-muted p-3 sm:p-4 rounded-lg">{order.verificationCode}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>

                <div className="lg:col-span-2">
                    <Card className="h-[60vh] sm:h-[65vh] md:h-[70vh] flex flex-col">
                         <CardHeader className="p-3 sm:p-4 md:p-6">
                            <CardTitle className="text-lg sm:text-xl">Chat de Entrega</CardTitle>
                             <CardDescription className="text-xs sm:text-sm">Comunícate directamente con tu repartidor.</CardDescription>
                        </CardHeader>
                        {driver && order.status !== 'Esperando Aceptación' ? (
                             <ChatInterface
                                orderId={order.id}
                                userType="customer"
                                userName={order.customerName}
                                receiverName={driver.name}
                            />
                        ) : (
                             <div className="flex-grow flex flex-col items-center justify-center text-center p-3 sm:p-4">
                                <User className="h-10 w-10 sm:h-12 sm:w-12 text-muted-foreground mb-3 sm:mb-4" />
                                <h3 className="font-semibold text-sm sm:text-base">Aún no se ha asignado un repartidor</h3>
                                <p className="text-xs sm:text-sm text-muted-foreground mt-1 sm:mt-2 px-2">El chat estará disponible cuando un repartidor acepte tu pedido.</p>
                            </div>
                        )}
                    </Card>
                </div>
            </div>
        </div>
    );
}
