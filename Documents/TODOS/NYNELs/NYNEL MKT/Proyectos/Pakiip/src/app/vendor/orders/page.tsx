
"use client";

import React, { useState, useEffect } from "react";
import { useSearchParams } from 'next/navigation';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import type { Order, Vendor } from "@/lib/placeholder-data";
import { CheckCircle, MoreVertical, Package, XCircle } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { useToast } from "@/hooks/use-toast";
import { formatCurrency } from "@/lib/utils";
import { AuthGuard } from "@/components/AuthGuard";

function VendorOrdersContent() {
    const { orders: allOrders, saveOrder, appSettings, getVendorById, currentUser } = useAppData();
    const [vendorOrders, setVendorOrders] = useState<Order[]>([]);
    const { toast } = useToast();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO query params
    const loggedInVendorId = currentUser?.role === 'vendor' ? currentUser.id : null;

    const vendor = loggedInVendorId ? getVendorById(loggedInVendorId) : undefined;

    useEffect(() => {
        if (vendor) {
            const filteredOrders = allOrders
                .filter(o => o.items.some(item => item.vendor === vendor.name))
                .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
            setVendorOrders(filteredOrders);
        }
    }, [allOrders, vendor]);

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
    
    const handleStatusChange = (order: Order, newStatus: Order['status']) => {
        saveOrder({ ...order, status: newStatus });
        toast({
            title: "Estado del Pedido Actualizado",
            description: `El pedido ${order.id} ahora está ${newStatus}.`
        })
    };

    if (!vendor) {
        return <p>Cargando pedidos...</p>
    }

    const renderActions = (order: Order) => (
         <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="h-8 w-8">
                    <MoreVertical className="h-4 w-4"/>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem 
                    onClick={() => handleStatusChange(order, 'Listo para Recoger')}
                    disabled={order.status !== 'Procesando'}
                >
                    <Package className="mr-2 h-4 w-4" /> Marcar como Listo
                </DropdownMenuItem>
                    <DropdownMenuItem 
                    onClick={() => handleStatusChange(order, 'Cancelado')}
                    className="text-destructive focus:text-destructive focus:bg-destructive/10"
                    disabled={order.status === 'Entregado' || order.status === 'Cancelado'}
                >
                    <XCircle className="mr-2 h-4 w-4" /> Cancelar Pedido
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );

    return (
        <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
            <Card>
                <CardHeader className="px-3 sm:px-4 md:px-6">
                    <CardTitle className="text-lg sm:text-xl">Pedidos de {vendor.name}</CardTitle>
                    <CardDescription className="text-sm">Gestiona y prepara los pedidos de tus clientes.</CardDescription>
                </CardHeader>
                <CardContent className="px-3 sm:px-4 md:px-6">
                    {/* Mobile View */}
                    <div className="grid gap-3 sm:gap-4 md:hidden">
                        {vendorOrders.map((order) => (
                            <Card key={order.id} className="p-3 sm:p-4 space-y-3">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <p className="font-semibold text-sm sm:text-base">{order.customerName}</p>
                                        <p className="text-xs sm:text-sm text-muted-foreground font-mono">{order.id}</p>
                                        <p className="text-xs sm:text-sm text-muted-foreground">{new Date(order.date).toLocaleDateString()}</p>
                                    </div>
                                    {renderActions(order)}
                                </div>
                                <div>
                                    <p className="font-medium text-xs sm:text-sm mb-1">Tus Artículos:</p>
                                    <ul className="list-disc list-inside text-xs sm:text-sm text-muted-foreground pl-2">
                                        {order.items.filter(i => i.vendor === vendor?.name).map(item => (
                                            <li key={item.productName}>{item.quantity}x {item.productName}</li>
                                        ))}
                                    </ul>
                                </div>
                                <div className="flex justify-between items-center">
                                    {getStatusBadge(order.status)}
                                    <span className="font-bold text-base sm:text-lg">{formatCurrency(order.total, appSettings.currencySymbol)}</span>
                                </div>
                            </Card>
                        ))}
                    </div>

                    {/* Desktop View */}
                    <div className="hidden md:block overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID Pedido</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Tus Artículos</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {vendorOrders.map((order) => (
                                    <TableRow key={order.id}>
                                        <TableCell className="font-mono">{order.id}</TableCell>
                                        <TableCell>{order.customerName}</TableCell>
                                        <TableCell>{new Date(order.date).toLocaleDateString()}</TableCell>
                                        <TableCell>{getStatusBadge(order.status)}</TableCell>
                                        <TableCell>
                                            <ul className="list-disc list-inside text-sm">
                                                {order.items.filter(i => i.vendor === vendor?.name).map(item => (
                                                    <li key={item.productName}>{item.quantity}x {item.productName}</li>
                                                ))}
                                            </ul>
                                        </TableCell>
                                        <TableCell>
                                        {renderActions(order)}
                                        </TableCell>
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

export default function VendorOrdersPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="vendor" redirectTo="/vendor/login">
            <React.Suspense fallback={<div>Cargando...</div>}>
                <VendorOrdersContent />
            </React.Suspense>
        </AuthGuard>
    )
}
