
"use client";

import { useState, useEffect } from "react";
import Link from 'next/link';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import type { Order } from "@/lib/placeholder-data";
import { MessageSquare, MoreVertical, Check, X, Map, KeyRound } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { formatCurrency } from "@/lib/utils";
import { AuthGuard } from "@/components/AuthGuard";

function DriverOrdersPageContent() {
    const { orders: allOrders, saveOrder, appSettings, addDebtTransaction, currentUser } = useAppData();
    const [orders, setOrders] = useState<Order[]>([]);
    const { toast } = useToast();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO hardcodeado
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;
    
    const [isVerifyDialogOpen, setVerifyDialogOpen] = useState(false);
    const [orderToVerify, setOrderToVerify] = useState<Order | null>(null);
    const [verificationCode, setVerificationCode] = useState('');


    useEffect(() => {
        // Filter orders for the logged-in driver
        const driverOrders = allOrders
            .filter(o => o.driverId === loggedInDriverId)
            .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
        setOrders(driverOrders);
    }, [allOrders]);

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
    
    const handleStatusChange = (order: Order, newStatus: Order['status']) => {
        saveOrder({ ...order, status: newStatus });
        toast({
            title: "Estado del Pedido Actualizado",
            description: `El pedido ${order.id} ahora está ${newStatus}.`
        });
    };

    const handleOpenVerificationDialog = (order: Order) => {
        setOrderToVerify(order);
        setVerificationCode('');
        setVerifyDialogOpen(true);
    };

    const handleVerifyAndDeliver = () => {
        if (!orderToVerify) return;

        if (orderToVerify.verificationCode === verificationCode) {
            saveOrder({ ...orderToVerify, status: 'Entregado' });
            toast({
                title: "¡Entrega Confirmada!",
                description: `El pedido ${orderToVerify.id} ha sido marcado como entregado.`
            });

             // If order is delivered and was cash on delivery, add to driver's debt
            if (orderToVerify.paymentMethod === 'Efectivo' && loggedInDriverId) {
                // 🔒 SEGURIDAD: Usar loggedInDriverId verificado, NO orderToVerify.driverId
                addDebtTransaction(loggedInDriverId, orderToVerify.total, `Cobro del pedido ${orderToVerify.id}`);
                toast({
                    title: "Deuda Registrada",
                    description: `Se ha añadido ${formatCurrency(orderToVerify.total, appSettings.currencySymbol)} a tu deuda por el cobro en efectivo.`,
                    variant: 'default',
                });
            }
            setVerifyDialogOpen(false);
            setOrderToVerify(null);

        } else {
            toast({
                title: "Código Incorrecto",
                description: "El código de verificación no coincide. Por favor, inténtalo de nuevo.",
                variant: "destructive"
            });
        }
    };
    
    const handleRejectOrder = (order: Order) => {
        // Remove driverId and set status back for admin to reassign
        saveOrder({ ...order, status: 'Listo para Recoger', driverId: undefined });
         toast({
            title: "Pedido Rechazado",
            description: `Has rechazado el pedido ${order.id}. El administrador será notificado.`,
            variant: "destructive"
        })
    };
    
    const handleAcceptOrder = (order: Order) => {
        // The order is now officially the driver's responsibility
        saveOrder({ ...order, status: 'Listo para Recoger' });
        toast({
            title: "Pedido Aceptado",
            description: `Has aceptado el pedido ${order.id}. Por favor, procede a recogerlo.`,
        })
    }
    
    const renderActions = (order: Order) => {
        if (order.status === 'Esperando Aceptación') {
            const driver = allOrders.find(o => o.id === order.id)?.driverId ? useAppData().drivers.find(d => d.id === order.driverId) : null;
            const commission = driver ? order.shippingFee * (driver.commissionRate / 100) : 0;
            return (
                <div className="flex flex-col gap-2 mt-2">
                    <div className="text-center text-xs sm:text-sm font-semibold p-2 bg-muted rounded-md">
                        Ganancia por esta entrega: {formatCurrency(commission, appSettings.currencySymbol)}
                    </div>
                    <div className="flex items-center gap-2">
                        <Button size="sm" onClick={() => handleAcceptOrder(order)} className="bg-green-600 hover:bg-green-700 w-full text-xs sm:text-sm">
                            <Check className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Aceptar
                        </Button>
                        <Button size="sm" variant="destructive" onClick={() => handleRejectOrder(order)} className="w-full text-xs sm:text-sm">
                            <X className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Rechazar
                        </Button>
                    </div>
                </div>
            );
        }

        return (
             <div className="flex items-center gap-1 sm:gap-2 mt-2">
                <Button asChild size="sm" variant="outline" className="flex-1 text-xs sm:text-sm px-2 sm:px-3">
                    <Link href={`/driver/orders/${order.id}/map`}>
                        <Map className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" /> <span className="hidden sm:inline">Ver</span> Ruta
                    </Link>
                </Button>
                <Button asChild size="sm" disabled={order.status === 'Cancelado' || order.status === 'Entregado'} className="flex-1 text-xs sm:text-sm px-2 sm:px-3">
                    <Link href={`/driver/orders/${order.id}/chat`}>
                        <MessageSquare className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Chat
                    </Link>
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" size="icon" className="h-8 w-8 sm:h-9 sm:w-9" disabled={order.status === 'Cancelado' || order.status === 'Entregado'}>
                            <MoreVertical className="h-3 w-3 sm:h-4 sm:w-4"/>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem 
                            onClick={() => handleStatusChange(order, 'Enviado')}
                            disabled={order.status !== 'Listo para Recoger'}
                        >
                            Marcar como Enviado
                        </DropdownMenuItem>
                        <DropdownMenuItem 
                            onClick={() => handleOpenVerificationDialog(order)}
                            disabled={order.status !== 'Enviado'}
                        >
                            Marcar como Entregado
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        )
    }

    return (
        <>
        <Card className="mx-2 sm:mx-3 md:mx-4">
            <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                <CardTitle className="text-lg sm:text-xl">Mis Entregas Asignadas</CardTitle>
                <CardDescription className="text-sm">Gestiona tus entregas y comunícate con los clientes.</CardDescription>
            </CardHeader>
            <CardContent className="px-2 sm:px-6 py-3 sm:py-6">
                 {/* Mobile View */}
                 <div className="grid gap-3 sm:gap-4 md:hidden">
                    {orders.map((order) => (
                        <Card key={order.id} className="p-3 sm:p-4 space-y-2 sm:space-y-3">
                            <div className="flex justify-between items-start gap-2">
                                <div className="flex-1 min-w-0">
                                    <p className="font-semibold text-sm sm:text-base truncate">{order.customerName}</p>
                                    <p className="text-xs sm:text-sm text-muted-foreground font-mono">{order.id}</p>
                                </div>
                                <span className="font-bold text-base sm:text-lg shrink-0">{formatCurrency(order.total, appSettings.currencySymbol)}</span>
                            </div>
                            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                               {getStatusBadge(order.status)}
                                <span className="text-xs text-muted-foreground">{new Date(order.date).toLocaleDateString()}</span>
                            </div>
                            {renderActions(order)}
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
                                <TableHead>Total</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead>Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {orders.map((order) => (
                                <TableRow key={order.id}>
                                    <TableCell className="font-mono">{order.id}</TableCell>
                                    <TableCell>{order.customerName}</TableCell>
                                    <TableCell>{appSettings.currencySymbol}{order.total.toFixed(2)}</TableCell>
                                    <TableCell>{getStatusBadge(order.status)}</TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            {renderActions(order)}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
        
        <Dialog open={isVerifyDialogOpen} onOpenChange={setVerifyDialogOpen}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Verificar Entrega</DialogTitle>
                    <DialogDescription>
                        Ingresa el código de 4 dígitos que te proporcionó el cliente para confirmar la entrega del pedido #{orderToVerify?.id}.
                    </DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                    <Label htmlFor="verification-code" className="sr-only">Código de Verificación</Label>
                    <Input 
                        id="verification-code" 
                        value={verificationCode}
                        onChange={(e) => setVerificationCode(e.target.value)}
                        maxLength={4}
                        className="text-center text-2xl tracking-[1em] font-bold"
                    />
                </div>
                <DialogFooter>
                    <Button variant="ghost" onClick={() => setVerifyDialogOpen(false)}>Cancelar</Button>
                    <Button onClick={handleVerifyAndDeliver}>
                        <KeyRound className="mr-2 h-4 w-4" /> Confirmar Entrega
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        </>
    );
}

export default function DriverOrdersPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverOrdersPageContent />
        </AuthGuard>
    );
}
