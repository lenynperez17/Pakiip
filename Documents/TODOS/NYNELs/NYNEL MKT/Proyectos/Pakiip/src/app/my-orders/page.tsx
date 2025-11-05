
"use client";

import React from 'react';
import Link from 'next/link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Order } from "@/lib/placeholder-data";
import { ArrowRight } from "lucide-react";
import { useAppData } from '@/hooks/use-app-data';
import { formatCurrency } from '@/lib/utils';
import { AuthGuard } from "@/components/AuthGuard";

function MyOrdersPageContent() {
    const { orders, appSettings: settings, currentUser } = useAppData();

    // Filtrar pedidos del usuario actual
    const userOrders = currentUser
        ? orders.filter(order => order.customerName === currentUser.name || order.id.includes(currentUser.id))
        : [];

    const getStatusBadge = (status: Order['status']) => {
        switch (status) {
          case 'Procesando':
            return <Badge variant="secondary" className="bg-yellow-400 text-yellow-900 hover:bg-yellow-400/80">{status}</Badge>;
          case 'Listo para Recoger':
            return <Badge variant="secondary" className="bg-sky-500 text-white hover:bg-sky-500/80">{status}</Badge>;
          case 'Esperando Aceptación':
            return <Badge variant="secondary" className="bg-orange-500 text-white hover:bg-orange-500/80">{status}</Badge>;
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
    
    const renderOrderCard = (order: Order) => (
         <Card key={order.id} className="p-3 sm:p-4 space-y-2 sm:space-y-3">
            <div className="flex justify-between items-start gap-2">
                <div className="min-w-0 flex-1">
                    <p className="font-semibold font-mono text-sm sm:text-base truncate">Pedido #{order.id}</p>
                    <p className="text-xs sm:text-sm text-muted-foreground">{new Date(order.date).toLocaleDateString()}</p>
                </div>
                <span className="font-bold text-base sm:text-lg flex-shrink-0">{formatCurrency(order.total, settings.currencySymbol)}</span>
            </div>
            <div className="flex flex-col xs:flex-row justify-between items-stretch xs:items-center gap-2 sm:gap-3">
                {getStatusBadge(order.status)}
                <Button asChild variant="secondary" size="sm" className="text-xs sm:text-sm w-full xs:w-auto">
                    <Link href={`/order/${order.id}`}>
                        Ver Detalles <ArrowRight className="ml-1 sm:ml-2 h-3 w-3 sm:h-4 sm:w-4" />
                    </Link>
                </Button>
            </div>
        </Card>
    );

    return (
        <div className="mx-auto px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-8 lg:py-12" style={{ maxWidth: '1600px' }}>
            <div className="mb-4 sm:mb-6 md:mb-8">
                <h1 className="text-xl xs:text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Mis Pedidos</h1>
                <p className="text-xs xs:text-sm sm:text-base text-muted-foreground mt-1">Sigue el estado de tus pedidos y comunícate con el repartidor.</p>
            </div>

            <Card>
                <CardHeader className="p-3 sm:p-4 md:p-6">
                    <CardTitle className="text-lg sm:text-xl md:text-2xl">Historial de Pedidos</CardTitle>
                </CardHeader>
                <CardContent className="p-3 sm:p-4 md:p-6">
                    {/* Mobile View */}
                    <div className="grid gap-3 sm:gap-4 md:hidden">
                        {userOrders.length > 0 ? (
                            userOrders.map(renderOrderCard)
                        ) : (
                            <p className="text-center text-sm sm:text-base text-muted-foreground py-6 sm:py-8">No tienes pedidos aún.</p>
                        )}
                    </div>
                    {/* Desktop View */}
                    <div className="hidden md:block overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="text-sm sm:text-base">ID Pedido</TableHead>
                                    <TableHead className="text-sm sm:text-base">Fecha</TableHead>
                                    <TableHead className="text-sm sm:text-base">Total</TableHead>
                                    <TableHead className="text-sm sm:text-base">Estado</TableHead>
                                    <TableHead></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {userOrders.length > 0 ? (
                                    userOrders.map((order) => (
                                    <TableRow key={order.id}>
                                        <TableCell className="font-mono text-sm sm:text-base">{order.id}</TableCell>
                                        <TableCell className="text-sm sm:text-base">{new Date(order.date).toLocaleDateString()}</TableCell>
                                        <TableCell className="text-sm sm:text-base font-medium">{formatCurrency(order.total, settings.currencySymbol)}</TableCell>
                                        <TableCell>{getStatusBadge(order.status)}</TableCell>
                                        <TableCell className="text-right">
                                            <Button asChild variant="ghost" size="sm" className="text-sm sm:text-base">
                                                <Link href={`/order/${order.id}`}>
                                                    Ver Detalles <ArrowRight className="ml-1 sm:ml-2 h-3 w-3 sm:h-4 sm:w-4" />
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={5} className="text-center text-sm sm:text-base text-muted-foreground py-6 sm:py-8">
                                            No tienes pedidos aún.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

export default function MyOrdersPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
            <MyOrdersPageContent />
        </AuthGuard>
    );
}
