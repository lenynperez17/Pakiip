
"use client";

import { useAppData } from "@/hooks/use-app-data";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { DollarSign, Truck, BarChart2, Wallet, Scale, ArrowDownCircle, ArrowUpCircle } from 'lucide-react';
import { formatCurrency } from "@/lib/utils";
import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";
import { AuthGuard } from "@/components/AuthGuard";

function DriverEarningsPageContent() {
    const { orders, drivers, appSettings, currentUser } = useAppData();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO hardcodeado
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;

    const driver = loggedInDriverId ? drivers.find(d => d.id === loggedInDriverId) : undefined;

    if (!driver) {
        return (
            <div className="flex items-center justify-center h-full">
                <p>No se pudo cargar la información del repartidor.</p>
            </div>
        );
    }
    
    const deliveredOrders = orders.filter(o => o.driverId === loggedInDriverId && o.status === 'Entregado');
    
    const calculateCommission = (shippingFee: number) => {
        const commission = shippingFee * (driver.commissionRate / 100);
        return commission;
    };
    
    const totalEarnings = deliveredOrders.reduce((acc, order) => acc + calculateCommission(order.shippingFee), 0);
    const totalDeliveries = deliveredOrders.length;
    
    const netBalance = totalEarnings - driver.debt;

    const debtTransactions = driver.debtTransactions || [];

    return (
        <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
            <div className="mb-6 sm:mb-8">
                <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Mis Ganancias y Deudas</h1>
                <p className="text-sm sm:text-base text-muted-foreground mt-1">Un resumen de tus comisiones, deudas por cobros y saldo neto.</p>
            </div>

            <div className="grid gap-3 sm:gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6 sm:mb-8">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6 pt-3 sm:pt-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Ganancias por Comisión</CardTitle>
                        <Wallet className="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6 pb-3 sm:pb-6">
                        <div className="text-xl sm:text-2xl font-bold text-green-600">{formatCurrency(totalEarnings, appSettings.currencySymbol)}</div>
                        <p className="text-xs text-muted-foreground">Comisiones totales acumuladas</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6 pt-3 sm:pt-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Deuda por Cobros</CardTitle>
                        <Scale className="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6 pb-3 sm:pb-6">
                        <div className="text-xl sm:text-2xl font-bold text-red-600">{formatCurrency(driver.debt, appSettings.currencySymbol)}</div>
                        <p className="text-xs text-muted-foreground">Dinero de pedidos contra entrega</p>
                    </CardContent>
                </Card>
                 <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6 pt-3 sm:pt-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Saldo Neto a Liquidar</CardTitle>
                        <DollarSign className="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6 pb-3 sm:pb-6">
                        <div className={cn("text-xl sm:text-2xl font-bold", netBalance >= 0 ? "text-green-600" : "text-red-600")}>
                            {formatCurrency(netBalance, appSettings.currencySymbol)}
                        </div>
                        <p className="text-xs text-muted-foreground">{netBalance >= 0 ? 'Saldo a tu favor' : 'Saldo a pagar'}</p>
                    </CardContent>
                </Card>
                 <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2 px-3 sm:px-6 pt-3 sm:pt-6">
                        <CardTitle className="text-xs sm:text-sm font-medium">Entregas Completadas</CardTitle>
                        <Truck className="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent className="px-3 sm:px-6 pb-3 sm:pb-6">
                        <div className="text-xl sm:text-2xl font-bold">{totalDeliveries}</div>
                        <p className="text-xs text-muted-foreground">Total de pedidos entregados</p>
                    </CardContent>
                </Card>
            </div>
            
             <Card>
                <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                    <CardTitle className="text-lg sm:text-xl">Historial de Transacciones de Deuda</CardTitle>
                    <CardDescription className="text-sm">Detalle de los cargos por pedidos y los pagos que has realizado.</CardDescription>
                </CardHeader>
                <CardContent className="px-2 sm:px-6 py-3 sm:py-6">
                    {/* Mobile View */}
                    <div className="grid gap-3 sm:gap-4 md:hidden">
                        {debtTransactions.length > 0 ? debtTransactions.map((tx) => (
                            <Card key={tx.id} className="p-3 sm:p-4 space-y-2">
                                <div className="flex justify-between items-start gap-2">
                                    <div className="flex-1 min-w-0">
                                        <p className="text-xs sm:text-sm font-medium capitalize">{tx.type === 'debit' ? 'Cargo' : 'Pago'}</p>
                                        <p className="text-xs text-muted-foreground truncate">{new Date(tx.date).toLocaleString('es-ES')}</p>
                                    </div>
                                    <p className={cn("font-semibold text-sm sm:text-base shrink-0", tx.type === 'debit' ? 'text-red-600' : 'text-green-600')}>
                                        {tx.type === 'debit' ? '+' : '-'}{formatCurrency(tx.amount, appSettings.currencySymbol)}
                                    </p>
                                </div>
                                <p className="text-xs sm:text-sm text-muted-foreground">{tx.description}</p>
                            </Card>
                        )) : (
                            <p className="text-xs sm:text-sm text-muted-foreground text-center py-6 sm:py-8">
                                No hay transacciones de deuda registradas.
                            </p>
                        )}
                    </div>

                    {/* Desktop View */}
                    <div className="hidden md:block overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Descripción</TableHead>
                                    <TableHead className="text-right">Monto</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {debtTransactions.length > 0 ? debtTransactions.map((tx) => (
                                    <TableRow key={tx.id}>
                                        <TableCell>{new Date(tx.date).toLocaleString('es-ES')}</TableCell>
                                        <TableCell>
                                            <Badge variant={tx.type === 'debit' ? 'destructive' : 'default'} className="whitespace-nowrap">
                                                {tx.type === 'debit' ? 
                                                    <><ArrowUpCircle className="mr-2 h-3 w-3"/> Cargo</> : 
                                                    <><ArrowDownCircle className="mr-2 h-3 w-3" /> Pago</>
                                                }
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{tx.description}</TableCell>
                                        <TableCell className={cn(
                                            "text-right font-semibold",
                                            tx.type === 'debit' ? 'text-red-600' : 'text-green-600'
                                        )}>
                                            {formatCurrency(tx.amount, appSettings.currencySymbol)}
                                        </TableCell>
                                    </TableRow>
                                )) : (
                                    <TableRow>
                                        <TableCell colSpan={4} className="text-center text-muted-foreground h-24">
                                            No hay transacciones de deuda registradas.
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

export default function DriverEarningsPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverEarningsPageContent />
        </AuthGuard>
    );
}
