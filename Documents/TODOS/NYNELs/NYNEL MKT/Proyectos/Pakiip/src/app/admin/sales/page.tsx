

"use client";

import React from "react";
import { Bar, BarChart, ResponsiveContainer, XAxis, YAxis, Tooltip, Legend, CartesianGrid } from "recharts";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { DollarSign, ShoppingCart, Users, Activity, Wallet, TrendingUp, Truck, Store, Banknote } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { Order } from "@/lib/placeholder-data";
import { formatCurrency, cn } from "@/lib/utils";
import { calculatePlatformMetrics, calculateVendorPayouts, calculateDriverPayouts } from "@/lib/business-logic";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { AuthGuard } from "@/components/AuthGuard";

function SalesPageContent() {
    const { orders, appSettings: settings, vendors, drivers, markVendorPayoutAsPaid, markDriverPayoutAsPaid } = useAppData();
    const { toast } = useToast();

    const {
        totalRevenue,
        totalOrders,
        uniqueCustomers,
        averageOrderValue,
        platformCommissions,
        netDeliveryRevenue,
        totalDriverPayouts,
        netPlatformProfit
    } = calculatePlatformMetrics(orders, vendors, drivers, settings);

    const vendorPayouts = calculateVendorPayouts(orders, vendors);
    const driverPayouts = calculateDriverPayouts(orders, drivers);

    const salesData = React.useMemo(() => {
        const monthlySales: { [key: string]: number } = {};
        const monthNames = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

        orders.forEach(order => {
            const date = new Date(order.date);
            const monthName = monthNames[date.getMonth()];
            
            if (!monthlySales[monthName]) {
                monthlySales[monthName] = 0;
            }
            monthlySales[monthName] += order.total;
        });

        return monthNames.map(monthName => ({
            name: monthName,
            total: monthlySales[monthName] || 0
        }));

    }, [orders]);

    const handleMarkVendorPaid = (vendorId: string, vendorName: string) => {
        markVendorPayoutAsPaid(vendorId);
        toast({
            title: "Liquidación Pagada",
            description: `Se ha marcado la liquidación para ${vendorName} como pagada.`,
        });
    };
    
    const handleMarkDriverPaid = (driverId: string, driverName: string) => {
        const payoutInfo = driverPayouts.find(p => p.driverId === driverId);
        if (!payoutInfo) return;

        const { netBalance } = payoutInfo;
        const ordersToMarkAsPaid = orders.filter(o => 
            o.driverId === driverId && o.status === 'Entregado' && o.driverPayoutStatus !== 'paid'
        );
        
        markDriverPayoutAsPaid(driverId, netBalance, ordersToMarkAsPaid);

        toast({
            title: "Liquidación a Repartidor Registrada",
            description: `Se ha registrado la liquidación para ${driverName}. Su saldo ahora es 0.`,
        });
    };

    const getStatusBadge = (status: Order['status']) => {
        switch (status) {
          case 'Procesando':
            return <Badge variant="secondary" className="bg-yellow-400 text-yellow-900 hover:bg-yellow-400/80">{status}</Badge>;
          case 'Listo para Recoger':
            return <Badge variant="secondary" className="bg-sky-500 text-white hover:bg-sky-500/80">{status}</Badge>;
          case 'Enviado':
            return <Badge variant="secondary" className="bg-blue-500 text-white hover:bg-blue-500/80">{status}</Badge>;
          case 'Entregado':
            return <Badge variant="secondary" className="bg-green-500 text-white hover:bg-green-500/80">{status}</Badge>;
          case 'Cancelado':
            return <Badge variant="destructive">{status}</Badge>;
          default:
            return <Badge>{status}</Badge>;
        }
    };

    return (
        <div className="space-y-8">
            <div className="mb-8">
                <h1 className="text-4xl font-bold font-headline">Reporte de Ventas Global</h1>
                <p className="text-muted-foreground mt-1">Un resumen del rendimiento de ventas de toda la plataforma.</p>
            </div>
            
            <Card>
                <CardHeader>
                    <CardTitle>Métricas de Ganancias de la Plataforma</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                     <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ganancia Neta Total</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(netPlatformProfit, settings.currencySymbol)}</div>
                            <p className="text-xs text-muted-foreground">Suma de comisiones y ganancia de envío</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ganancias por Comisiones</CardTitle>
                            <Wallet className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(platformCommissions, settings.currencySymbol)}</div>
                            <p className="text-xs text-muted-foreground">Total de comisiones de tiendas</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ganancia Neta por Envío</CardTitle>
                            <Truck className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(netDeliveryRevenue, settings.currencySymbol)}</div>
                            <p className="text-xs text-muted-foreground">Envíos cobrados menos pago a repartidores</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pagado a Repartidores</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalDriverPayouts, settings.currencySymbol)}</div>
                            <p className="text-xs text-muted-foreground">Comisiones totales de repartidores</p>
                        </CardContent>
                    </Card>
                </CardContent>
            </Card>

            <Card>
                 <CardHeader>
                    <CardTitle>Métricas Generales de Ventas</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ingresos Totales (GMV)</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(totalRevenue, settings.currencySymbol)}</div>
                            <p className="text-xs text-muted-foreground">Valor total de los pedidos</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pedidos Totales</CardTitle>
                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">+{totalOrders}</div>
                            <p className="text-xs text-muted-foreground">Pedidos completados</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Valor Promedio de Pedido</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(averageOrderValue, settings.currencySymbol)}</div>
                            <p className="text-xs text-muted-foreground">Por transacción</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Clientes Únicos</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{uniqueCustomers}</div>
                            <p className="text-xs text-muted-foreground">Clientes que han comprado</p>
                        </CardContent>
                    </Card>
                </CardContent>
            </Card>
            
            <Card>
                <CardHeader>
                    <CardTitle>Liquidación de Tiendas</CardTitle>
                    <CardDescription>Resumen de cuánto pagar a cada tienda por ventas no liquidadas.</CardDescription>
                </CardHeader>
                <CardContent>
                    {/* Mobile View */}
                    <div className="grid gap-4 md:hidden">
                        {vendorPayouts.map((payout) => (
                            <Card key={payout.vendorId} className="p-4 space-y-3">
                                <div className="font-bold">{payout.vendorName}</div>
                                <div className="flex justify-between items-center text-sm">
                                    <span className="text-muted-foreground">Pedidos Pendientes:</span>
                                    <span>{payout.totalOrders}</span>
                                </div>
                                <div className="flex justify-between items-center font-semibold">
                                     <span className="text-muted-foreground font-normal">Monto a Pagar:</span>
                                    <span>{formatCurrency(payout.netPayout, settings.currencySymbol)}</span>
                                </div>
                                <Button
                                    size="sm"
                                    className="w-full"
                                    disabled={payout.netPayout <= 0}
                                    onClick={() => handleMarkVendorPaid(payout.vendorId, payout.vendorName)}
                                >
                                    <Banknote className="mr-2 h-4 w-4"/>
                                    Marcar como Pagado
                                </Button>
                            </Card>
                        ))}
                    </div>

                    {/* Desktop View */}
                    <div className="hidden md:block">
                        <div className="overflow-x-auto text-xs sm:text-sm md:text-base">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead><Store className="inline-block mr-2 h-4 w-4" />Nombre de la Tienda</TableHead>
                                        <TableHead className="text-right">Pedidos Pendientes</TableHead>
                                        <TableHead className="text-right font-semibold">Monto a Pagar</TableHead>
                                        <TableHead className="text-center">Acción</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {vendorPayouts.map((payout) => (
                                        <TableRow key={payout.vendorId}>
                                            <TableCell className="font-medium whitespace-nowrap">{payout.vendorName}</TableCell>
                                            <TableCell className="text-right whitespace-nowrap">{payout.totalOrders}</TableCell>
                                            <TableCell className="text-right font-semibold whitespace-nowrap">{formatCurrency(payout.netPayout, settings.currencySymbol)}</TableCell>
                                            <TableCell className="text-center">
                                                <Button 
                                                    size="sm" 
                                                    disabled={payout.netPayout <= 0}
                                                    onClick={() => handleMarkVendorPaid(payout.vendorId, payout.vendorName)}
                                                >
                                                    <Banknote className="mr-2 h-4 w-4"/>
                                                    Marcar como Pagado
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Liquidación de Repartidores</CardTitle>
                    <CardDescription>Resumen del saldo a liquidar para cada repartidor.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="overflow-x-auto text-xs sm:text-sm md:text-base">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead><Truck className="inline-block mr-2 h-4 w-4" />Nombre del Repartidor</TableHead>
                                    <TableHead className="text-right">Comisiones Ganadas</TableHead>
                                    <TableHead className="text-right">Deuda por Cobros</TableHead>
                                    <TableHead className="text-right font-semibold">Saldo Neto a Liquidar</TableHead>
                                    <TableHead className="text-center">Acción</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {driverPayouts.map((payout) => (
                                    <TableRow key={payout.driverId}>
                                        <TableCell className="font-medium whitespace-nowrap">{payout.driverName}</TableCell>
                                        <TableCell className="text-right text-green-600 whitespace-nowrap">{formatCurrency(payout.totalCommissions, settings.currencySymbol)}</TableCell>
                                        <TableCell className="text-right text-red-600 whitespace-nowrap">-{formatCurrency(payout.currentDebt, settings.currencySymbol)}</TableCell>
                                        <TableCell className={cn("text-right font-semibold whitespace-nowrap", payout.netBalance >= 0 ? "text-green-600" : "text-red-600")}>
                                            {formatCurrency(payout.netBalance, settings.currencySymbol)}
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <Button 
                                                size="sm" 
                                                disabled={payout.netBalance === 0}
                                                onClick={() => handleMarkDriverPaid(payout.driverId, payout.driverName)}
                                                className="whitespace-nowrap"
                                            >
                                                <Banknote className="mr-2 h-4 w-4"/>
                                                Registrar Liquidación
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>


            <Card>
                <CardHeader>
                    <CardTitle>Ventas Anuales</CardTitle>
                    <CardDescription>Un vistazo a los ingresos generados cada mes, basado en datos reales.</CardDescription>
                </CardHeader>
                <CardContent className="pl-2">
                    <ResponsiveContainer width="100%" height={350}>
                        <BarChart data={salesData}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis
                                dataKey="name"
                                stroke="#888888"
                                fontSize={12}
                                tickLine={false}
                                axisLine={false}
                            />
                            <YAxis
                                stroke="#888888"
                                fontSize={12}
                                tickLine={false}
                                axisLine={false}
                                tickFormatter={(value) => formatCurrency(value as number, settings.currencySymbol)}
                            />
                            <Tooltip
                                contentStyle={{ backgroundColor: 'hsl(var(--background))', border: '1px solid hsl(var(--border))' }}
                                labelStyle={{ color: 'hsl(var(--foreground))' }}
                                itemStyle={{ color: 'hsl(var(--primary))' }}
                                cursor={{fill: 'hsl(var(--muted))'}}
                            />
                            <Legend />
                            <Bar dataKey="total" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} name="Ventas"/>
                        </BarChart>
                    </ResponsiveContainer>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Pedidos Recientes</CardTitle>
                </CardHeader>
                <CardContent>
                     {/* Mobile View */}
                     <div className="grid gap-4 md:hidden">
                        {orders.slice(0, 10).map((order) => (
                            <Card key={order.id} className="p-4 space-y-3">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <p className="font-semibold">{order.customerName}</p>
                                        <p className="text-sm text-muted-foreground font-mono">{order.id}</p>
                                    </div>
                                    <span className="font-bold">{formatCurrency(order.total, settings.currencySymbol)}</span>
                                </div>
                                <div className="flex justify-between items-center text-sm">
                                    {getStatusBadge(order.status)}
                                    <span className="text-muted-foreground">{new Date(order.date).toLocaleDateString()}</span>
                                </div>
                            </Card>
                        ))}
                    </div>
                    {/* Desktop View */}
                    <div className="hidden md:block">
                        <div className="overflow-x-auto text-xs sm:text-sm md:text-base">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>ID Pedido</TableHead>
                                        <TableHead>Cliente</TableHead>
                                        <TableHead>Fecha</TableHead>
                                        <TableHead>Total</TableHead>
                                        <TableHead>Estado</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {orders.slice(0, 10).map((order) => (
                                        <TableRow key={order.id}>
                                            <TableCell className="font-mono whitespace-nowrap">{order.id}</TableCell>
                                            <TableCell className="whitespace-nowrap">{order.customerName}</TableCell>
                                            <TableCell className="whitespace-nowrap">{new Date(order.date).toLocaleDateString()}</TableCell>
                                            <TableCell className="whitespace-nowrap">{formatCurrency(order.total, settings.currencySymbol)}</TableCell>
                                            <TableCell>{getStatusBadge(order.status)}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

export default function SalesPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
            <SalesPageContent />
        </AuthGuard>
    );
}
