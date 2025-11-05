
"use client";

import Link from 'next/link';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Truck, Users, ListOrdered, CheckCircle, Map, Wallet, Settings } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';
import { Badge } from '@/components/ui/badge';
import { AuthGuard } from "@/components/AuthGuard";

function DriverDashboardPageContent() {
    const { orders, drivers, currentUser } = useAppData();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO fallback a 'd1'
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;
    const driver = loggedInDriverId ? drivers.find(d => d.id === loggedInDriverId) : undefined;

    if (!driver) {
      return (
        <div className="flex items-center justify-center h-full">
          <p>No se pudo cargar la información del repartidor.</p>
        </div>
      );
    }

    const assignedOrders = orders.filter(o => o.driverId === loggedInDriverId);
    const newOrdersCount = assignedOrders.filter(o => o.status === 'Esperando Aceptación').length;
    const deliveredOrders = assignedOrders.filter(o => o.status === 'Entregado').length;
    
    const navItems = [
        {
            href: '/driver/orders',
            icon: ListOrdered,
            title: 'Gestión de Pedidos',
            badge: newOrdersCount > 0 ? newOrdersCount : undefined
        },
        {
            href: '/driver/map',
            icon: Map,
            title: 'Mapa de Entregas',
        },
        {
            href: '/driver/earnings',
            icon: Wallet,
            title: 'Mis Ganancias',
        },
        {
            href: '/driver/customers',
            icon: Users,
            title: 'Mis Clientes',
        },
        {
            href: '/driver/settings',
            icon: Settings,
            title: 'Configuración',
        },
    ];

    return (
        <div className="space-y-4 sm:space-y-6">
            <div className="flex items-center justify-between mb-4 sm:mb-6 md:mb-8">
                <div>
                    <h1 className="text-xl xs:text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Hola, {driver?.name || 'Repartidor'}</h1>
                    <p className="text-xs xs:text-sm sm:text-base text-muted-foreground mt-1">Tu centro de operaciones para las entregas.</p>
                </div>
            </div>

             <div className="grid grid-cols-3 gap-3 sm:gap-4">
                 <Card className="bg-muted/50 border-dashed">
                    <CardHeader className="flex-row items-center gap-3 sm:gap-4 space-y-0 p-3 sm:p-4 md:p-6">
                        <ListOrdered className="h-6 w-6 sm:h-7 sm:w-7 md:h-8 md:w-8 text-muted-foreground flex-shrink-0" />
                        <div className="min-w-0 flex-1">
                            <CardTitle className="text-base sm:text-lg md:text-xl">{newOrdersCount} Pedidos Nuevos</CardTitle>
                        </div>
                    </CardHeader>
                </Card>
                 <Card className="bg-muted/50 border-dashed">
                    <CardHeader className="flex-row items-center gap-3 sm:gap-4 space-y-0 p-3 sm:p-4 md:p-6">
                        <CheckCircle className="h-6 w-6 sm:h-7 sm:w-7 md:h-8 md:w-8 text-green-500 flex-shrink-0" />
                        <div className="min-w-0 flex-1">
                            <CardTitle className="text-base sm:text-lg md:text-xl">{deliveredOrders} Entregas Completadas</CardTitle>
                        </div>
                    </CardHeader>
                </Card>
                 <Card className="bg-muted/50 border-dashed">
                    <CardHeader className="flex-row items-center gap-3 sm:gap-4 space-y-0 p-3 sm:p-4 md:p-6">
                        <Truck className="h-6 w-6 sm:h-7 sm:w-7 md:h-8 md:w-8 text-primary flex-shrink-0" />
                        <div className="min-w-0 flex-1">
                            <CardTitle className="text-base sm:text-lg md:text-xl">{driver.status}</CardTitle>
                        </div>
                    </CardHeader>
                </Card>
            </div>

            <div className="grid grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
                {navItems.map(item => (
                     <Link href={item.href} key={item.title} className="block hover:-translate-y-1 transition-transform duration-200">
                        <Card className="h-full relative">
                            <CardHeader className="p-3 sm:p-4 md:p-6">
                                <div className="p-2 sm:p-3 bg-muted rounded-lg w-fit mb-3 sm:mb-4">
                                    <item.icon className="h-5 w-5 sm:h-6 sm:w-6 text-primary" />
                                </div>
                                <CardTitle className="text-base sm:text-lg md:text-xl">{item.title}</CardTitle>
                                {item.badge && (
                                    <Badge className="absolute top-3 right-3 sm:top-4 sm:right-4 bg-primary text-xs">{item.badge}</Badge>
                                )}
                            </CardHeader>
                        </Card>
                    </Link>
                ))}
            </div>
        </div>
    );
}

export default function DriverDashboardPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverDashboardPageContent />
        </AuthGuard>
    );
}
