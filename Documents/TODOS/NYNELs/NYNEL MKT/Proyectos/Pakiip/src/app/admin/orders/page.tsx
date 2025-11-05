

"use client";

import { useState } from "react";
import Link from 'next/link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuPortal } from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import type { Order } from "@/lib/placeholder-data";
import { useAppData } from "@/hooks/use-app-data";
import { useToast } from "@/hooks/use-toast";
import { Truck, MoreHorizontal, Trash, Edit, FileText, DollarSign, MapPin } from "lucide-react";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { formatCurrency, getDistanceFromLatLonInKm } from "@/lib/utils";
import { AuthGuard } from "@/components/AuthGuard";

function AdminOrdersPageContent() {
    const { orders, drivers, saveOrder, deleteOrder, appSettings, vendors } = useAppData();
    const { toast } = useToast();
    const [orderToDelete, setOrderToDelete] = useState<Order | null>(null);

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

    const handleAssignDriver = (orderId: string, driverId: string) => {
        const order = orders.find(o => o.id === orderId);
        const driver = drivers.find(d => d.id === driverId);
        if (order && driver) {
            saveOrder({ ...order, driverId: driverId, status: 'Esperando Aceptación' });
            toast({
                title: "Repartidor Asignado",
                description: `${driver.name} ha sido notificado para aceptar el pedido ${order.id}.`,
            });
        }
    };

    const handleDeleteConfirm = () => {
        if (!orderToDelete) return;
        deleteOrder(orderToDelete.id);
        toast({ title: "Pedido Eliminado", description: `El pedido ${orderToDelete.id} ha sido eliminado.`, variant: "destructive" });
        setOrderToDelete(null);
    };

    const renderActions = (order: Order) => {
        const vendor = vendors.find(v => v.name === order.items[0]?.vendor);
        let distance = 0;
        if (vendor?.coordinates && order.customerCoordinates) {
            distance = getDistanceFromLatLonInKm(vendor.coordinates.lat, vendor.coordinates.lng, order.customerCoordinates.lat, order.customerCoordinates.lng);
        }

        return (
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="h-8 w-8">
                        <MoreHorizontal className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                    <DropdownMenuSeparator/>
                    <DropdownMenuItem asChild>
                        <Link href={`/order/${order.id}/receipt`}>
                            <FileText className="mr-2 h-4 w-4" /> Ver Recibo
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSub>
                         <DropdownMenuSubTrigger disabled={order.status !== 'Listo para Recoger'}>
                            <Truck className="mr-2 h-4 w-4" />
                            Asignar Repartidor
                        </DropdownMenuSubTrigger>
                        <DropdownMenuPortal>
                        <DropdownMenuSubContent>
                             <DropdownMenuLabel className="flex flex-col space-y-1">
                                <span>Detalles de Entrega</span>
                                <div className="flex justify-between items-center text-xs font-normal text-muted-foreground">
                                    <div className="flex items-center gap-1"><MapPin className="h-3 w-3"/> {distance.toFixed(2)} km</div>
                                    <div className="flex items-center gap-1"><DollarSign className="h-3 w-3"/> Envío: {formatCurrency(order.shippingFee, appSettings.currencySymbol)}</div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {drivers.filter(d => d.status === 'Activo').map((driver) => {
                                const commission = order.shippingFee * (driver.commissionRate / 100);
                                return (
                                <DropdownMenuItem 
                                    key={driver.id} 
                                    onClick={() => handleAssignDriver(order.id, driver.id)}
                                >
                                    <span>Asignar a {driver.name}</span>
                                    <span className="ml-auto text-xs text-muted-foreground">
                                        Gana: {formatCurrency(commission, appSettings.currencySymbol)}
                                    </span>
                                </DropdownMenuItem>
                            )})}
                        </DropdownMenuSubContent>
                        </DropdownMenuPortal>
                    </DropdownMenuSub>
                    <DropdownMenuItem onClick={() => setOrderToDelete(order)} className="text-destructive">
                        <Trash className="mr-2 h-4 w-4" />
                        Eliminar
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }

    return (
        <div className="space-y-6">
        <Card>
            <CardHeader>
                <CardTitle>Gestión de Pedidos</CardTitle>
                <CardDescription>Supervisa y asigna repartidores a todos los pedidos de la plataforma.</CardDescription>
            </CardHeader>
            <CardContent>
                {/* Mobile View */}
                <div className="grid gap-4 md:hidden">
                    {orders.map((order) => {
                        const assignedDriver = drivers.find(d => d.id === order.driverId);
                        return (
                        <Card key={order.id} className="p-4 space-y-3">
                            <div className="flex justify-between items-start">
                                <div>
                                    <p className="font-semibold">{order.customerName}</p>
                                    <p className="text-sm text-muted-foreground font-mono">{order.id}</p>
                                </div>
                                {renderActions(order)}
                            </div>
                            <div className="flex justify-between items-center text-sm">
                                {getStatusBadge(order.status)}
                                <span className="font-bold text-lg">{formatCurrency(order.total, appSettings.currencySymbol)}</span>
                            </div>
                            <div className="text-sm">
                                <span className="text-muted-foreground">Repartidor: </span>
                                {assignedDriver ? (
                                    <Badge variant="outline">{assignedDriver.name}</Badge>
                                ) : (
                                    <Badge variant="secondary">Sin asignar</Badge>
                                )}
                            </div>
                        </Card>
                        )
                    })}
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
                                    <TableHead>Repartidor</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {orders.map((order) => {
                                    const assignedDriver = drivers.find(d => d.id === order.driverId);
                                    return (
                                        <TableRow key={order.id}>
                                            <TableCell className="font-mono whitespace-nowrap">{order.id}</TableCell>
                                            <TableCell className="whitespace-nowrap">{order.customerName}</TableCell>
                                            <TableCell className="whitespace-nowrap">{new Date(order.date).toLocaleDateString()}</TableCell>
                                            <TableCell className="whitespace-nowrap">{formatCurrency(order.total, appSettings.currencySymbol)}</TableCell>
                                            <TableCell>{getStatusBadge(order.status)}</TableCell>
                                            <TableCell>
                                                {assignedDriver ? (
                                                    <Badge variant="outline">{assignedDriver.name}</Badge>
                                                ) : (
                                                    <Badge variant="secondary">Sin asignar</Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {renderActions(order)}
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </CardContent>
        </Card>
        
        <AlertDialog open={!!orderToDelete} onOpenChange={() => setOrderToDelete(null)}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro de que quieres eliminar este pedido?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Esta acción no se puede deshacer. Se eliminará el pedido {orderToDelete?.id} permanentemente.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction onClick={handleDeleteConfirm} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
        </div>
    );
}

export default function AdminOrdersPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
            <AdminOrdersPageContent />
        </AuthGuard>
    );
}
