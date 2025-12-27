"use client";

import { useState, useMemo, use } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Separator } from "@/components/ui/separator";
import { useAppData } from "@/hooks/use-app-data";
import { useToast } from "@/hooks/use-toast";
import { formatCurrency, getDistanceFromLatLonInKm } from "@/lib/utils";
import { AuthGuard } from "@/components/AuthGuard";
import { ArrowLeft, MapPin, DollarSign, Star, Truck, Clock, CheckCircle2, AlertCircle } from "lucide-react";
import type { DeliveryDriver } from "@/lib/placeholder-data";

function AssignDriverPageContent({ orderId }: { orderId: string }) {
    const router = useRouter();
    const { orders, drivers, vendors, saveOrder, appSettings } = useAppData();
    const { toast } = useToast();
    const [isAssigning, setIsAssigning] = useState(false);
    const [selectedDriverId, setSelectedDriverId] = useState<string | null>(null);

    const order = orders.find(o => o.id === orderId);
    const vendor = order?.items[0]?.vendor ? vendors.find(v => v.name === order.items[0].vendor) : null;

    // Calcular distancia desde el vendor hasta el cliente
    const deliveryDistance = useMemo(() => {
        if (!vendor?.coordinates || !order?.customerCoordinates) return 0;
        const distance = getDistanceFromLatLonInKm(
            vendor.coordinates.lat,
            vendor.coordinates.lng,
            order.customerCoordinates.lat,
            order.customerCoordinates.lng
        );
        return distance ?? 0; // Asegurar que siempre retorne un número
    }, [vendor, order]);

    // Filtrar y ordenar repartidores
    const availableDrivers = useMemo(() => {
        return drivers
            .filter(d => d.status === 'Activo')
            .map(driver => {
                const commission = order ? order.shippingFee * (driver.commissionRate / 100) : 0;
                return { ...driver, calculatedCommission: commission };
            })
            .sort((a, b) => (b.rating || 0) - (a.rating || 0)); // Ordenar por rating con fallback
    }, [drivers, order]);

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    };

    const handleAssignDriver = async (driverId: string) => {
        if (!order) return;

        setIsAssigning(true);
        setSelectedDriverId(driverId);

        try {
            const driver = drivers.find(d => d.id === driverId);
            await saveOrder({
                ...order,
                driverId: driverId,
                visibleToDriverId: driverId, // Para que el driver pueda ver el pedido en su panel
                status: 'Esperando Aceptación'
            });

            toast({
                title: "✅ Repartidor Asignado",
                description: `${driver?.name} ha sido notificado para aceptar el pedido.`,
            });

            // Redirigir de vuelta a la lista de órdenes después de 1 segundo
            setTimeout(() => {
                router.push('/admin/orders');
            }, 1000);
        } catch (error) {
            toast({
                title: "❌ Error",
                description: "No se pudo asignar el repartidor. Intenta nuevamente.",
                variant: "destructive"
            });
            setIsAssigning(false);
            setSelectedDriverId(null);
        }
    };

    if (!order) {
        return (
            <div className="container mx-auto max-w-6xl px-4 py-12">
                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-12">
                        <AlertCircle className="h-12 w-12 text-muted-foreground mb-4" />
                        <h2 className="text-xl font-semibold mb-2">Pedido No Encontrado</h2>
                        <p className="text-muted-foreground mb-6">El pedido {orderId} no existe.</p>
                        <Button asChild>
                            <Link href="/admin/orders">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Volver a Pedidos
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        );
    }

    const currentDriver = order.driverId ? drivers.find(d => d.id === order.driverId) : null;

    return (
        <div className="container mx-auto max-w-6xl px-4 py-6 md:py-12">
            {/* Header */}
            <div className="mb-6">
                <Button variant="ghost" asChild className="mb-4">
                    <Link href="/admin/orders">
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Volver a Pedidos
                    </Link>
                </Button>
                <h1 className="text-2xl md:text-3xl font-bold">Asignar Repartidor</h1>
                <p className="text-muted-foreground">Pedido {order.id}</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Información del Pedido - Sidebar */}
                <div className="lg:col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Información del Pedido</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Cliente</p>
                                <p className="font-semibold">{order.customerName}</p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Dirección de Entrega</p>
                                <p className="text-sm">{order.customerAddress}</p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Total del Pedido</p>
                                <p className="text-xl font-bold">{formatCurrency(order.total, appSettings.currencySymbol)}</p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground mb-2">Detalles de Envío</p>
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="flex items-center gap-1 text-muted-foreground">
                                            <MapPin className="h-3 w-3" />
                                            Distancia
                                        </span>
                                        <span className="font-semibold">{(deliveryDistance || 0).toFixed(2)} km</span>
                                    </div>
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="flex items-center gap-1 text-muted-foreground">
                                            <DollarSign className="h-3 w-3" />
                                            Tarifa de Envío
                                        </span>
                                        <span className="font-semibold">{formatCurrency(order.shippingFee, appSettings.currencySymbol)}</span>
                                    </div>
                                </div>
                            </div>
                            {currentDriver && (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground mb-2">Repartidor Actual</p>
                                        <div className="flex items-center gap-3 p-3 bg-muted rounded-lg">
                                            <Avatar className="h-10 w-10">
                                                {currentDriver.profileImageUrl && (
                                                    <AvatarImage src={currentDriver.profileImageUrl} alt={currentDriver.name} />
                                                )}
                                                <AvatarFallback>{getInitials(currentDriver.name)}</AvatarFallback>
                                            </Avatar>
                                            <div className="flex-1 min-w-0">
                                                <p className="font-semibold truncate">{currentDriver.name}</p>
                                                <p className="text-xs text-muted-foreground">{currentDriver.vehicle}</p>
                                            </div>
                                        </div>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Lista de Repartidores Disponibles */}
                <div className="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Repartidores Disponibles</CardTitle>
                            <CardDescription>
                                {availableDrivers.length} repartidor{availableDrivers.length !== 1 ? 'es' : ''} activo{availableDrivers.length !== 1 ? 's' : ''} disponible{availableDrivers.length !== 1 ? 's' : ''}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {availableDrivers.length === 0 ? (
                                <div className="flex flex-col items-center justify-center py-12 text-center">
                                    <Truck className="h-12 w-12 text-muted-foreground mb-4" />
                                    <h3 className="text-lg font-semibold mb-2">No hay repartidores disponibles</h3>
                                    <p className="text-muted-foreground">
                                        No hay repartidores activos en este momento. Por favor, intenta más tarde.
                                    </p>
                                </div>
                            ) : (
                                <div className="grid gap-4">
                                    {availableDrivers.map((driver) => {
                                        const isSelected = selectedDriverId === driver.id;
                                        const isCurrent = currentDriver?.id === driver.id;

                                        return (
                                            <Card
                                                key={driver.id}
                                                className={`transition-all ${
                                                    isCurrent ? 'border-primary bg-primary/5' :
                                                    isSelected ? 'border-primary' : ''
                                                }`}
                                            >
                                                <CardContent className="p-4">
                                                    <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                                                        {/* Avatar y Info Básica */}
                                                        <div className="flex items-center gap-3 flex-1 min-w-0">
                                                            <Avatar className="h-12 w-12 flex-shrink-0">
                                                                {driver.profileImageUrl && (
                                                                    <AvatarImage src={driver.profileImageUrl} alt={driver.name} />
                                                                )}
                                                                <AvatarFallback>{getInitials(driver.name)}</AvatarFallback>
                                                            </Avatar>
                                                            <div className="flex-1 min-w-0">
                                                                <div className="flex items-center gap-2 mb-1">
                                                                    <h3 className="font-semibold truncate">{driver.name}</h3>
                                                                    {isCurrent && (
                                                                        <Badge variant="outline" className="text-xs">
                                                                            <CheckCircle2 className="h-3 w-3 mr-1" />
                                                                            Asignado
                                                                        </Badge>
                                                                    )}
                                                                </div>
                                                                <p className="text-sm text-muted-foreground">{driver.vehicle}</p>
                                                                <div className="flex items-center gap-1 mt-1">
                                                                    <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                                                    <span className="text-xs font-semibold">{(driver.rating || 0).toFixed(1)}</span>
                                                                    <span className="text-xs text-muted-foreground">
                                                                        ({driver.deliveries || 0} entregas)
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Comisión y Botón */}
                                                        <div className="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center w-full sm:w-auto">
                                                            <div className="text-center sm:text-right">
                                                                <p className="text-xs text-muted-foreground">Ganará</p>
                                                                <p className="text-lg font-bold text-green-600">
                                                                    {formatCurrency(driver.calculatedCommission, appSettings.currencySymbol)}
                                                                </p>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {driver.commissionRate}% comisión
                                                                </p>
                                                            </div>
                                                            <Button
                                                                onClick={() => handleAssignDriver(driver.id)}
                                                                disabled={isAssigning || isCurrent}
                                                                className="whitespace-nowrap"
                                                            >
                                                                {isAssigning && isSelected ? (
                                                                    <>
                                                                        <Clock className="mr-2 h-4 w-4 animate-spin" />
                                                                        Asignando...
                                                                    </>
                                                                ) : isCurrent ? (
                                                                    <>
                                                                        <CheckCircle2 className="mr-2 h-4 w-4" />
                                                                        Asignado
                                                                    </>
                                                                ) : (
                                                                    <>
                                                                        <Truck className="mr-2 h-4 w-4" />
                                                                        Asignar
                                                                    </>
                                                                )}
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}

// Client Component - En Next.js 15+, params es una Promesa, usar React.use()
export default function AssignDriverPage({ params }: { params: Promise<{ orderId: string }> }) {
    const { orderId } = use(params);

    return (
        <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
            <AssignDriverPageContent orderId={orderId} />
        </AuthGuard>
    );
}
