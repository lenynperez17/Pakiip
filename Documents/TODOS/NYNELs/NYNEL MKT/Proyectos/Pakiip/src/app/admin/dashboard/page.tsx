
"use client";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Truck, Store, DollarSign, ListOrdered, HandHeart, BarChartHorizontal, Map, LayoutGrid, Users, Scale, Settings } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { formatCurrency } from "@/lib/utils";
import { calculatePlatformMetrics } from "@/lib/business-logic";
import Link from 'next/link';
import { AuthGuard } from "@/components/AuthGuard";

function AdminDashboardPageContent() {
    const { appSettings, orders, vendors, drivers, favors, currentUser } = useAppData();

    const { totalRevenue } = calculatePlatformMetrics(orders, vendors, drivers, appSettings);
    const activeDrivers = drivers.filter(d => d.status === 'Activo').length;
    
    const hasPermission = (permission: string) => {
        if (currentUser?.role !== 'admin') return false;
        return currentUser.permissions.includes(permission);
    };

    const navItems = [
        ...(hasPermission('manage_orders') ? [{ href: '/admin/orders', icon: ListOrdered, title: 'Pedidos' }] : []),
        ...(hasPermission('manage_orders') ? [{ href: '/admin/favors', icon: HandHeart, title: 'Favores' }] : []),
        ...(hasPermission('view_reports') ? [{ href: '/admin/sales', icon: BarChartHorizontal, title: 'Reporte de Ventas' }] : []),
        ...(hasPermission('manage_stores') ? [{ href: '/admin/stores', icon: Store, title: 'Tiendas' }] : []),
        ...(hasPermission('manage_settings') ? [{ href: '/admin/cities', icon: Map, title: 'Zonas de Entrega' }] : []),
        ...(hasPermission('manage_settings') ? [{ href: '/admin/categories', icon: LayoutGrid, title: 'Categorías' }] : []),
        ...(hasPermission('manage_users') ? [{ href: '/admin/users', icon: Users, title: 'Usuarios' }] : []),
        ...(hasPermission('manage_drivers') ? [{ href: '/admin/drivers', icon: Truck, title: 'Repartidores' }] : []),
        ...(hasPermission('manage_drivers') ? [{ href: '/admin/driver-debts', icon: Scale, title: 'Deudas de Repartidores' }] : []),
        ...(hasPermission('manage_settings') ? [{ href: '/admin/settings', icon: Settings, title: 'Configuración' }] : []),
    ];

  return (
    <div className="flex flex-col space-y-4 sm:space-y-6 md:space-y-8">
        <div className="flex items-center justify-between px-2 sm:px-3 md:px-4">
            <div>
                <h1 className="text-2xl xs:text-3xl md:text-4xl lg:text-5xl font-bold font-headline">Panel de Administrador</h1>
                <p className="text-xs sm:text-sm md:text-base text-muted-foreground mt-1">Tu centro de control para {appSettings.appName}.</p>
            </div>
        </div>

        <div className="grid gap-3 sm:gap-4 md:gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 px-2 sm:px-3 md:px-4">
            <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                    <CardTitle className="text-xs sm:text-sm font-medium">Tiendas Totales</CardTitle>
                    <Store className="h-4 w-4 sm:h-5 sm:w-5 text-muted-foreground" />
                </CardHeader>
                <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                    <div className="text-xl sm:text-2xl md:text-3xl font-bold text-left">{vendors.length}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                    <CardTitle className="text-xs sm:text-sm font-medium">Repartidores Activos</CardTitle>
                    <Truck className="h-4 w-4 sm:h-5 sm:w-5 text-muted-foreground" />
                </CardHeader>
                <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                    <div className="text-xl sm:text-2xl md:text-3xl font-bold text-left">{activeDrivers}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 p-3 sm:p-4 md:p-6">
                    <CardTitle className="text-xs sm:text-sm font-medium">Ingresos Totales (GMV)</CardTitle>
                    <DollarSign className="h-4 w-4 sm:h-5 sm:w-5 text-muted-foreground" />
                </CardHeader>
                <CardContent className="p-3 sm:p-4 md:p-6 pt-0">
                    <div className="text-xl sm:text-2xl md:text-3xl font-bold text-left">{formatCurrency(totalRevenue, appSettings.currencySymbol)}</div>
                </CardContent>
            </Card>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-5 lg:gap-6 px-2 sm:px-3 md:px-4">
            {navItems.map(item => (
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

export default function AdminDashboardPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminDashboardPageContent />
    </AuthGuard>
  );
}
