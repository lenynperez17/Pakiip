"use client";

import { Bar, BarChart, ResponsiveContainer, XAxis, YAxis, Tooltip, Legend, CartesianGrid } from "recharts";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { DollarSign, ShoppingCart, Users, Activity } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { AuthGuard } from "@/components/AuthGuard";

const salesData = [
  { name: 'Ene', total: 1200 },
  { name: 'Feb', total: 2100 },
  { name: 'Mar', total: 1500 },
  { name: 'Abr', total: 3200 },
  { name: 'May', total: 2500 },
  { name: 'Jun', total: 4100 },
  { name: 'Jul', total: 3800 },
  { name: 'Ago', total: 4500 },
  { name: 'Sep', total: 3900 },
  { name: 'Oct', total: 5200 },
  { name: 'Nov', total: 4800 },
  { name: 'Dic', total: 6100 },
];

function SalesPageContent() {
    const { orders, appSettings: settings } = useAppData();

    const totalRevenue = orders.reduce((sum, order) => sum + order.total, 0);
    const totalOrders = orders.length;
    const uniqueCustomers = new Set(orders.map(o => o.customerName)).size;
    const averageOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;

    return (
        <div className="mx-auto px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-12" style={{ maxWidth: '1600px' }}>
            <div className="mb-4 sm:mb-6 md:mb-8">
                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Reporte de Ventas</h1>
                <p className="text-muted-foreground mt-1 text-sm sm:text-base">Un resumen del rendimiento de tus ventas.</p>
            </div>

            <div className="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mb-4 sm:mb-6 md:mb-8">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Ingresos Totales</CardTitle>
                        <DollarSign className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-muted-foreground flex-shrink-0" />
                    </CardHeader>
                    <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                        <div className="text-xl sm:text-2xl font-bold truncate">{settings.currencySymbol}{totalRevenue.toFixed(2)}</div>
                        <p className="text-[10px] sm:text-xs text-muted-foreground mt-1">(Basado en datos de demostración)</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Pedidos Totales</CardTitle>
                        <ShoppingCart className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-muted-foreground flex-shrink-0" />
                    </CardHeader>
                    <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                        <div className="text-xl sm:text-2xl font-bold">+{totalOrders}</div>
                        <p className="text-[10px] sm:text-xs text-muted-foreground mt-1">Pedidos completados</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Valor Promedio de Pedido</CardTitle>
                        <Activity className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-muted-foreground flex-shrink-0" />
                    </CardHeader>
                    <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                        <div className="text-xl sm:text-2xl font-bold truncate">{settings.currencySymbol}{averageOrderValue.toFixed(2)}</div>
                        <p className="text-[10px] sm:text-xs text-muted-foreground mt-1">Por transacción</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Clientes Únicos</CardTitle>
                        <Users className="h-3.5 w-3.5 sm:h-4 sm:w-4 text-muted-foreground flex-shrink-0" />
                    </CardHeader>
                    <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                        <div className="text-xl sm:text-2xl font-bold">{uniqueCustomers}</div>
                        <p className="text-[10px] sm:text-xs text-muted-foreground mt-1">Clientes que han comprado</p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader className="p-3 sm:p-4 md:p-6">
                    <CardTitle className="text-lg sm:text-xl">Ventas Anuales</CardTitle>
                    <CardDescription className="text-xs sm:text-sm">Un vistazo a los ingresos generados cada mes.</CardDescription>
                </CardHeader>
                <CardContent className="pl-1 sm:pl-2 pr-3 sm:pr-4 md:pr-6 pb-3 sm:pb-4 md:pb-6">
                    <ResponsiveContainer width="100%" height={300} className="sm:h-[350px]">
                        <BarChart data={salesData}>
                            <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
                            <XAxis
                                dataKey="name"
                                stroke="#888888"
                                fontSize={10}
                                className="sm:text-xs"
                                tickLine={false}
                                axisLine={false}
                            />
                            <YAxis
                                stroke="#888888"
                                fontSize={10}
                                className="sm:text-xs"
                                tickLine={false}
                                axisLine={false}
                                tickFormatter={(value) => `${settings.currencySymbol}${value}`}
                                width={35}
                                className="sm:w-auto"
                            />
                            <Tooltip
                                contentStyle={{
                                    backgroundColor: 'hsl(var(--background))',
                                    border: '1px solid hsl(var(--border))',
                                    fontSize: '12px',
                                    borderRadius: '6px',
                                    padding: '8px'
                                }}
                                labelStyle={{ color: 'hsl(var(--foreground))', fontSize: '11px' }}
                                itemStyle={{ color: 'hsl(var(--primary))', fontSize: '11px' }}
                                cursor={{fill: 'hsl(var(--muted))'}}
                            />
                            <Legend
                                wrapperStyle={{ fontSize: '11px' }}
                                className="sm:text-sm"
                            />
                            <Bar
                                dataKey="total"
                                fill="hsl(var(--primary))"
                                radius={[4, 4, 0, 0]}
                                name="Ventas"
                                maxBarSize={60}
                            />
                        </BarChart>
                    </ResponsiveContainer>
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
