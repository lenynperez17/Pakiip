

"use client";

import { useState } from "react";
import Link from 'next/link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger, DropdownMenuPortal } from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import type { Favor } from "@/lib/placeholder-data";
import { useAppData } from "@/hooks/use-app-data";
import { useToast } from "@/hooks/use-toast";
import { Truck, MoreHorizontal, Trash, DollarSign, MapPin, HandHeart, Eye } from "lucide-react";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { formatCurrency, getDistanceFromLatLonInKm } from "@/lib/utils";
import { Separator } from "@/components/ui/separator";
import { AuthGuard } from "@/components/AuthGuard";

function AdminFavorsPageContent() {
    const { favors, drivers, saveFavor, deleteFavor, appSettings } = useAppData();
    const { toast } = useToast();
    const [favorToDelete, setFavorToDelete] = useState<Favor | null>(null);
    const [favorToView, setFavorToView] = useState<Favor | null>(null);

    const getStatusBadge = (status: Favor['status']) => {
        switch (status) {
          case 'Pendiente':
            return <Badge variant="secondary" className="bg-orange-500 text-white hover:bg-orange-500/80">{status}</Badge>;
          case 'Aceptado':
             return <Badge variant="secondary" className="bg-sky-500 text-white hover:bg-sky-500/80">Asignado</Badge>;
          case 'En Camino':
            return <Badge variant="secondary" className="bg-blue-500 text-white hover:bg-blue-500/80">{status}</Badge>;
          case 'Completado':
            return <Badge variant="secondary" className="bg-green-500 text-white hover:bg-green-500/80">{status}</Badge>;
          case 'Cancelado':
            return <Badge variant="destructive">{status}</Badge>;
          default:
            return <Badge>{status}</Badge>;
        }
    };

    const handleAssignDriver = (favorId: string, driverId: string) => {
        const favor = favors.find(f => f.id === favorId);
        const driver = drivers.find(d => d.id === driverId);
        if (favor && driver) {
            saveFavor({ ...favor, driverId: driverId, status: 'Aceptado' });
            toast({
                title: "Repartidor Asignado",
                description: `${driver.name} ha sido notificado para realizar el favor.`,
            });
        }
    };

    const handleDeleteConfirm = () => {
        if (!favorToDelete) return;
        deleteFavor(favorToDelete.id);
        toast({ title: "Favor Eliminado", description: `El favor ${favorToDelete.id} ha sido eliminado.`, variant: "destructive" });
        setFavorToDelete(null);
    };
    
     const handleViewDetails = (favor: Favor) => {
        setFavorToView(favor);
    };


    const renderActions = (favor: Favor) => {
        const distance = getDistanceFromLatLonInKm(favor.pickupLocation.lat, favor.pickupLocation.lng, favor.deliveryLocation.lat, favor.deliveryLocation.lng);

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
                     <DropdownMenuItem onClick={() => handleViewDetails(favor)}>
                        <Eye className="mr-2 h-4 w-4" /> Ver Detalles
                    </DropdownMenuItem>
                    <DropdownMenuSub>
                         <DropdownMenuSubTrigger disabled={favor.status !== 'Pendiente'}>
                            <Truck className="mr-2 h-4 w-4" />
                            Asignar Repartidor
                        </DropdownMenuSubTrigger>
                        <DropdownMenuPortal>
                        <DropdownMenuSubContent>
                             <DropdownMenuLabel className="flex flex-col space-y-1">
                                <span>Detalles de Entrega</span>
                                <div className="flex justify-between items-center text-xs font-normal text-muted-foreground">
                                    <div className="flex items-center gap-1"><MapPin className="h-3 w-3"/> {distance.toFixed(2)} km</div>
                                    <div className="flex items-center gap-1"><DollarSign className="h-3 w-3"/> Envío: {formatCurrency(favor.quote.shippingFee, appSettings.currencySymbol)}</div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {drivers.filter(d => d.status === 'Activo').map((driver) => {
                                const commission = favor.quote.shippingFee * (driver.commissionRate / 100);
                                return (
                                <DropdownMenuItem 
                                    key={driver.id} 
                                    onClick={() => handleAssignDriver(favor.id, driver.id)}
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
                    <DropdownMenuItem onClick={() => setFavorToDelete(favor)} className="text-destructive">
                        <Trash className="mr-2 h-4 w-4" />
                        Eliminar
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }

    return (
        <div className="space-y-4 sm:space-y-6">
        <Card>
            <CardHeader className="px-4 sm:px-6">
                <CardTitle className="text-xl sm:text-2xl">Gestión de Favores</CardTitle>
                <CardDescription className="text-sm sm:text-base">Supervisa y asigna repartidores a las solicitudes de favores de los clientes.</CardDescription>
            </CardHeader>
            <CardContent className="px-4 sm:px-6">
                {/* Mobile View */}
                <div className="grid gap-3 sm:gap-4 md:hidden">
                    {favors.map((favor) => {
                        const assignedDriver = drivers.find(d => d.id === favor.driverId);
                        return (
                        <Card key={favor.id} className="p-3 sm:p-4 space-y-3">
                            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                                <div className="flex-1">
                                    <p className="font-semibold text-base sm:text-lg">{favor.userName}</p>
                                    <p className="text-xs sm:text-sm text-muted-foreground font-mono">{favor.id}</p>
                                </div>
                                <div className="flex items-center justify-between sm:flex-col sm:items-end gap-2">
                                    <span className="font-bold text-base sm:text-lg">{formatCurrency(favor.quote.totalFee, appSettings.currencySymbol)}</span>
                                    {renderActions(favor)}
                                </div>
                            </div>
                            <div className="flex items-center">
                                {getStatusBadge(favor.status)}
                            </div>
                             <div className="text-xs sm:text-sm line-clamp-2">
                                <span className="text-muted-foreground">Solicitud: </span>{favor.description}
                            </div>
                            <div className="text-xs sm:text-sm">
                                <span className="text-muted-foreground">Repartidor: </span>
                                {assignedDriver ? (
                                    <Badge variant="outline" className="text-xs">{assignedDriver.name}</Badge>
                                ) : (
                                    <Badge variant="secondary" className="text-xs">Sin asignar</Badge>
                                )}
                            </div>
                        </Card>
                        )
                    })}
                </div>
                {/* Desktop View */}
                <div className="hidden md:block">
                    <div className="overflow-x-auto">
                        <Table className="text-xs sm:text-sm">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID Favor</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Tarifa Servicio</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Repartidor</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {favors.map((favor) => {
                                    const assignedDriver = drivers.find(d => d.id === favor.driverId);
                                    return (
                                        <TableRow key={favor.id}>
                                            <TableCell className="font-mono whitespace-nowrap">{favor.id}</TableCell>
                                            <TableCell className="whitespace-nowrap">{favor.userName}</TableCell>
                                            <TableCell className="whitespace-nowrap">{new Date(favor.date).toLocaleDateString()}</TableCell>
                                            <TableCell className="whitespace-nowrap">{formatCurrency(favor.quote.totalFee, appSettings.currencySymbol)}</TableCell>
                                            <TableCell>{getStatusBadge(favor.status)}</TableCell>
                                            <TableCell>
                                                {assignedDriver ? (
                                                    <Badge variant="outline">{assignedDriver.name}</Badge>
                                                ) : (
                                                    <Badge variant="secondary">Sin asignar</Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {renderActions(favor)}
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

        <AlertDialog open={!!favorToDelete} onOpenChange={() => setFavorToDelete(null)}>
            <AlertDialogContent className="mx-4 sm:mx-0">
                <AlertDialogHeader className="px-2 sm:px-0">
                    <AlertDialogTitle className="text-lg sm:text-xl">¿Estás seguro de que quieres eliminar este favor?</AlertDialogTitle>
                    <AlertDialogDescription className="text-sm sm:text-base">
                        Esta acción no se puede deshacer. Se eliminará el favor {favorToDelete?.id} permanentemente.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter className="px-2 sm:px-0 gap-2 sm:gap-0">
                    <AlertDialogCancel className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Cancelar</AlertDialogCancel>
                    <AlertDialogAction onClick={handleDeleteConfirm} className="bg-destructive hover:bg-destructive/90 h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <AlertDialog open={!!favorToView} onOpenChange={() => setFavorToView(null)}>
            <AlertDialogContent className="mx-2 sm:mx-0">
                <AlertDialogHeader className="px-2 sm:px-0">
                    <AlertDialogTitle className="text-lg sm:text-xl">Detalles del Favor #{favorToView?.id}</AlertDialogTitle>
                </AlertDialogHeader>
                {favorToView && (
                    <div className="space-y-4 text-xs sm:text-sm px-2 sm:px-0">
                        <p><span className="font-semibold">Descripción:</span> {favorToView.description}</p>
                        <Separator/>
                        <p><span className="font-semibold">Dirección de Recogida:</span> {favorToView.pickupAddress}</p>
                        <p><span className="font-semibold">Dirección de Entrega:</span> {favorToView.deliveryAddress}</p>
                        <Separator/>
                        <p><span className="font-semibold">Costo Estimado Productos:</span> {formatCurrency(favorToView.estimatedProductCost, appSettings.currencySymbol)}</p>
                        <Separator/>
                        <div className="font-semibold">Cotización:</div>
                        <ul className="list-disc list-inside pl-4 text-muted-foreground">
                            <li>Tarifa de Servicio: {formatCurrency(favorToView.quote.serviceFee, appSettings.currencySymbol)}</li>
                            <li>Tarifa de Envío: {formatCurrency(favorToView.quote.shippingFee, appSettings.currencySymbol)}</li>
                            <li className="font-bold text-foreground">Total Servicio: {formatCurrency(favorToView.quote.totalFee, appSettings.currencySymbol)}</li>
                        </ul>
                         {favorToView.photoDataUri && (
                            <div>
                                <p className="font-semibold mb-2 text-sm sm:text-base">Foto de Referencia:</p>
                                <img src={favorToView.photoDataUri} alt="Referencia" className="rounded-md max-h-48 w-full sm:w-auto object-contain" />
                            </div>
                        )}
                    </div>
                )}
                <AlertDialogFooter className="px-2 sm:px-0">
                    <AlertDialogCancel className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Cerrar</AlertDialogCancel>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
        </div>
    );
}

export default function AdminFavorsPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
            <AdminFavorsPageContent />
        </AuthGuard>
    );
}
