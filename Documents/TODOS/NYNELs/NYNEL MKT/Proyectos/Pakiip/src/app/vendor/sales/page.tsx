
"use client";

import { useSearchParams } from 'next/navigation';
import { Bar, BarChart, ResponsiveContainer, XAxis, YAxis, Tooltip, Legend, CartesianGrid } from "recharts";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { DollarSign, ShoppingCart, Users, Activity, TrendingUp, Wallet, Banknote } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { Order } from '@/lib/placeholder-data';
import React from 'react';
import { formatCurrency } from "@/lib/utils";
import { calculateVendorMetrics } from '@/lib/business-logic';
import { AuthGuard } from "@/components/AuthGuard";

function VendorSalesContent() {
    const { orders, appSettings: settings, getVendorById, currentUser } = useAppData();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO query params
    const loggedInVendorId = currentUser?.role === 'vendor' ? currentUser.id : null;

    const vendor = loggedInVendorId ? getVendorById(loggedInVendorId) : undefined;
    
    const {
        vendorOrders,
        totalRevenue,
        totalOrders,
        uniqueCustomers,
        averageOrderValue,
        totalCost,
        grossProfit,
        profitMargin,
        platformCommission,
        netPayout,
    } = calculateVendorMetrics(vendor, orders);

    const salesData = React.useMemo(() => {
        const monthlySales: { [key: string]: number } = {};
        const monthNames = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

        vendorOrders.forEach(order => {
            const date = new Date(order.date);
            const monthName = monthNames[date.getMonth()];
            const vendorRevenueInOrder = order.items
                .filter(item => item.vendor === vendor?.name)
                .reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            if (!monthlySales[monthName]) {
                monthlySales[monthName] = 0;
            }
            monthlySales[monthName] += vendorRevenueInOrder;
        });

        return monthNames.map(monthName => ({
            name: monthName,
            total: monthlySales[monthName] || 0
        }));

    }, [vendorOrders, vendor]);
    
    const getStatusBadge = (status: Order['status']) => {
        switch (status) {
          case 'Procesando':
            return <Badge variant="secondary" className="bg-yellow-400 text-yellow-900 hover:bg-yellow-400/80">{status}</Badge>;
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

    if (!vendor) {
        return <div>Cargando reporte...</div>
    }

    return (
        <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
            <div className="mb-6 sm:mb-8">
                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Reporte de Ventas de {vendor.name}</h1>
                <p className="text-sm sm:text-base text-muted-foreground mt-1">Un resumen del rendimiento de tus ventas.</p>
            </div>

            <div className="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Liquidación Pendiente</CardTitle>
                        <Banknote className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold">{formatCurrency(netPayout, settings.currencySymbol)}</div>
                        <p className="text-xs text-muted-foreground">Total a recibir después de comisiones</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Ingresos Totales (GMV)</CardTitle>
                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold">{formatCurrency(totalRevenue, settings.currencySymbol)}</div>
                        <p className="text-xs text-muted-foreground">Ingresos de tus productos</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Comisión de Plataforma</CardTitle>
                        <Wallet className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold text-red-500">-{formatCurrency(platformCommission, settings.currencySymbol)}</div>
                        <p className="text-xs text-muted-foreground">Tarifa por uso de la plataforma</p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Ganancia Bruta</CardTitle>
                        <TrendingUp className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold">{formatCurrency(grossProfit, settings.currencySymbol)}</div>
                        <p className="text-xs text-muted-foreground">Ingresos menos costo de productos</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Margen de Ganancia</CardTitle>
                        <Activity className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold">{profitMargin.toFixed(2)}%</div>
                        <p className="text-xs text-muted-foreground">Margen de ganancia promedio</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Pedidos Totales</CardTitle>
                        <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold">+{totalOrders}</div>
                        <p className="text-xs text-muted-foreground">Pedidos en los que participaste</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Clientes Únicos</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6">
                        <div className="text-xl sm:text-2xl font-bold">{uniqueCustomers}</div>
                        <p className="text-xs text-muted-foreground">Clientes que te compraron</p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader className="px-3 sm:px-4 md:px-6">
                    <CardTitle className="text-lg sm:text-xl">Rendimiento Mensual</CardTitle>
                    <CardDescription className="text-sm">Un vistazo a los ingresos generados cada mes, basado en datos reales.</CardDescription>
                </CardHeader>
                <CardContent className="px-2 sm:px-4 md:px-6">
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
                                formatter={(value) => formatCurrency(value as number, settings.currencySymbol)}
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
                <CardHeader className="px-3 sm:px-4 md:px-6">
                    <CardTitle className="text-lg sm:text-xl">Historial de Pedidos Recientes</CardTitle>
                </CardHeader>
                <CardContent className="px-3 sm:px-4 md:px-6">
                    <div className="overflow-x-auto -mx-3 sm:-mx-4 md:-mx-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID Pedido</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Total (Pedido Completo)</TableHead>
                                    <TableHead>Estado</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {vendorOrders.slice(0, 10).map((order) => (
                                    <TableRow key={order.id}>
                                        <TableCell className="font-mono">{order.id}</TableCell>
                                        <TableCell>{order.customerName}</TableCell>
                                        <TableCell>{new Date(order.date).toLocaleDateString()}</TableCell>
                                        <TableCell>{formatCurrency(order.total, settings.currencySymbol)}</TableCell>
                                        <TableCell>{getStatusBadge(order.status)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

export default function SalesPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="vendor" redirectTo="/vendor/login">
            <React.Suspense fallback={<div>Cargando...</div>}>
                <VendorSalesContent />
            </React.Suspense>
        </AuthGuard>
    )
}

    
