
"use client";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { useAppData } from "@/hooks/use-app-data";
import { User, Calendar, Hash } from 'lucide-react';
import { AuthGuard } from "@/components/AuthGuard";

function CustomersPageContent() {
    const { orders, currentUser } = useAppData();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado y FILTRAR pedidos
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;

    // ✅ Filtrar SOLO los pedidos del driver logueado
    const driverOrders = loggedInDriverId ? orders.filter(o => o.driverId === loggedInDriverId) : [];

    const customerData = driverOrders.reduce((acc, order) => {
        if (!acc[order.customerName]) {
            acc[order.customerName] = {
                name: order.customerName,
                totalOrders: 0,
                lastOrder: new Date(0),
            };
        }
        acc[order.customerName].totalOrders += 1;
        const orderDate = new Date(order.date);
        if (orderDate > acc[order.customerName].lastOrder) {
            acc[order.customerName].lastOrder = orderDate;
        }
        return acc;
    }, {} as Record<string, { name: string, totalOrders: number, lastOrder: Date }>);

    const customers = Object.values(customerData);

    return (
        <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
            <div className="mb-6 sm:mb-8">
                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Lista de Clientes</h1>
                <p className="text-sm sm:text-base text-muted-foreground mt-1">Un resumen de los clientes a los que has entregado.</p>
            </div>
            <Card>
                <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                    <CardTitle className="text-lg sm:text-xl">Todos los Clientes</CardTitle>
                    <CardDescription className="text-sm">Aquí puedes ver un listado de los clientes de tus rutas de entrega.</CardDescription>
                </CardHeader>
                <CardContent className="px-2 sm:px-6 py-3 sm:py-6">
                    {/* Mobile View */}
                    <div className="grid gap-3 sm:gap-4 md:hidden">
                        {customers.map((customer) => (
                            <Card key={customer.name} className="p-3 sm:p-4 space-y-2">
                                <p className="font-bold text-sm sm:text-base">{customer.name}</p>
                                <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 sm:gap-0 text-xs sm:text-sm text-muted-foreground">
                                    <span>Pedidos: <span className="font-semibold text-foreground">{customer.totalOrders}</span></span>
                                    <span>Último: {customer.lastOrder.toLocaleDateString('es-ES')}</span>
                                </div>
                            </Card>
                        ))}
                    </div>

                    {/* Desktop View */}
                    <div className="hidden md:block overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead><User className="inline-block mr-2 h-4 w-4" />Nombre del Cliente</TableHead>
                                    <TableHead><Hash className="inline-block mr-2 h-4 w-4" />Pedidos Totales</TableHead>
                                    <TableHead><Calendar className="inline-block mr-2 h-4 w-4" />Último Pedido</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {customers.map((customer) => (
                                    <TableRow key={customer.name}>
                                        <TableCell className="font-medium">{customer.name}</TableCell>
                                        <TableCell>{customer.totalOrders}</TableCell>
                                        <TableCell>{customer.lastOrder.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</TableCell>
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

export default function CustomersPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <CustomersPageContent />
        </AuthGuard>
    );
}
