

"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { MoreHorizontal, PlusCircle, Trash, Edit, Percent, CheckCircle, XCircle, FileText } from "lucide-react";
import type { DeliveryDriver } from "@/lib/placeholder-data";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";
import { useAppData } from "@/hooks/use-app-data";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import Image from "next/image";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { AuthGuard } from "@/components/AuthGuard";

function AdminDriversPageContent() {
  const { drivers, saveDriver, deleteDriver } = useAppData();
  const [isAddDialogOpen, setAddDialogOpen] = useState(false);
  const [isEditDialogOpen, setEditDialogOpen] = useState(false);
  const [isImageViewOpen, setImageViewOpen] = useState(false);
  const [imageToView, setImageToView] = useState<string | null>(null);
  const [editingDriver, setEditingDriver] = useState<DeliveryDriver | null>(null);
  const [driverToDelete, setDriverToDelete] = useState<DeliveryDriver | null>(null);
  const { toast } = useToast();

  const handleAddDriver = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const newDriver: DeliveryDriver = {
        id: `d${Date.now()}`,
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        dni: formData.get('dni') as string,
        phone: formData.get('phone') as string,
        bankAccount: formData.get('bankAccount') as string,
        vehicle: formData.get('vehicle') as DeliveryDriver['vehicle'],
        commissionRate: parseFloat(formData.get('commissionRate') as string) || 80,
        status: 'Activo',
        debt: 0,
    };
    saveDriver(newDriver);
    setAddDialogOpen(false);
    toast({ title: "Repartidor Añadido", description: `${newDriver.name} ha sido añadido.` });
  };
  
  const handleEditClick = (driver: DeliveryDriver) => {
    setEditingDriver(driver);
    setEditDialogOpen(true);
  };

  const handleUpdateDriver = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingDriver) return;

    const formData = new FormData(event.currentTarget);
    const updatedDriver: DeliveryDriver = {
        ...editingDriver,
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        dni: formData.get('dni') as string,
        phone: formData.get('phone') as string,
        bankAccount: formData.get('bankAccount') as string,
        vehicle: formData.get('vehicle') as DeliveryDriver['vehicle'],
        commissionRate: parseFloat(formData.get('commissionRate') as string) || editingDriver.commissionRate,
        status: formData.get('status') as DeliveryDriver['status'],
    };

    saveDriver(updatedDriver);
    setEditDialogOpen(false);
    setEditingDriver(null);
    toast({ title: "Repartidor Actualizado", description: `${updatedDriver.name} ha sido actualizado.` });
  };

  const handleDeleteClick = (driver: DeliveryDriver) => {
    setDriverToDelete(driver);
  };

  const handleDeleteConfirm = () => {
    if (!driverToDelete) return;
    deleteDriver(driverToDelete.id);
    toast({ title: "Repartidor Eliminado", description: `${driverToDelete.name} ha sido eliminado.`, variant: "destructive" });
    setDriverToDelete(null);
  };
  
  const handleApprove = (driverId: string) => {
      const driver = drivers.find(d => d.id === driverId);
      if (driver) {
          saveDriver({ ...driver, status: 'Activo' });
          toast({ title: "Repartidor Aprobado", description: `${driver.name} ha sido activado.` });
      }
  };

  const handleReject = (driverId: string) => {
      const driver = drivers.find(d => d.id === driverId);
      if (driver) {
          saveDriver({ ...driver, status: 'Rechazado' });
          toast({ title: "Repartidor Rechazado", description: `${driver.name} ha sido marcado como rechazado.`, variant: "destructive" });
      }
  };

  const handleViewImage = (imageUrl: string) => {
    setImageToView(imageUrl);
    setImageViewOpen(true);
  }
  
  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
  }

  const renderActions = (driver: DeliveryDriver) => (
     <DropdownMenu>
        <DropdownMenuTrigger asChild><Button size="icon" variant="ghost"><MoreHorizontal /></Button></DropdownMenuTrigger>
        <DropdownMenuContent>
            <DropdownMenuItem onClick={() => handleEditClick(driver)}><Edit className="mr-2 h-4 w-4" /> Editar</DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleDeleteClick(driver)} className="text-destructive"><Trash className="mr-2 h-4 w-4" /> Eliminar</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
  );
  
  const activeDrivers = drivers.filter(d => d.status === 'Activo' || d.status === 'Inactivo');
  const pendingDrivers = drivers.filter(d => d.status === 'Pendiente');

  return (
    <div className="space-y-4 sm:space-y-6">
    <Card>
        <CardHeader className="px-4 sm:px-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <CardTitle className="text-xl sm:text-2xl">Gestionar Repartidores</CardTitle>
                    <CardDescription className="text-sm sm:text-base">Añade, edita o elimina repartidores del sistema.</CardDescription>
                </div>
                <Dialog open={isAddDialogOpen} onOpenChange={setAddDialogOpen}>
                    <DialogTrigger asChild>
                        <Button className="w-full sm:w-auto h-9 sm:h-10 text-sm sm:text-base">
                            <PlusCircle className="mr-2 h-4 w-4" /> Añadir Repartidor
                        </Button>
                    </DialogTrigger>
                    <DialogContent className="mx-2 sm:mx-0 sm:max-w-md">
                        <DialogHeader className="px-2 sm:px-0">
                            <DialogTitle className="text-lg sm:text-xl">Añadir Nuevo Repartidor</DialogTitle>
                            <DialogDescription className="text-sm sm:text-base">Completa los detalles del nuevo repartidor.</DialogDescription>
                        </DialogHeader>
                        <form onSubmit={handleAddDriver}>
                            <div className="grid gap-3 py-4 max-h-[70vh] overflow-y-auto px-2 sm:px-0">
                                <Label htmlFor="name" className="text-sm sm:text-base">Nombre Completo</Label>
                                <Input id="name" name="name" required className="h-9 sm:h-10 text-sm sm:text-base" />
                                <Label htmlFor="email" className="text-sm sm:text-base">Correo Electrónico</Label>
                                <Input id="email" name="email" type="email" required className="h-9 sm:h-10 text-sm sm:text-base" />
                                <Label htmlFor="dni" className="text-sm sm:text-base">DNI</Label>
                                <Input id="dni" name="dni" required className="h-9 sm:h-10 text-sm sm:text-base" />
                                <Label htmlFor="phone" className="text-sm sm:text-base">Celular</Label>
                                <Input id="phone" name="phone" type="tel" required className="h-9 sm:h-10 text-sm sm:text-base" />
                                <Label htmlFor="bankAccount" className="text-sm sm:text-base">Cuenta Bancaria</Label>
                                <Input id="bankAccount" name="bankAccount" required className="h-9 sm:h-10 text-sm sm:text-base" />
                                <Label className="text-sm sm:text-base">Vehículo</Label>
                                <Select name="vehicle" required>
                                    <SelectTrigger className="h-9 sm:h-10 text-sm sm:text-base"><SelectValue placeholder="Selecciona un vehículo" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Moto">Moto</SelectItem>
                                        <SelectItem value="Coche">Coche</SelectItem>
                                        <SelectItem value="Bicicleta">Bicicleta</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Label htmlFor="commissionRate" className="text-sm sm:text-base">Tasa de Comisión (%)</Label>
                                <div className="relative">
                                  <Percent className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                  <Input id="commissionRate" name="commissionRate" type="number" step="0.01" min="0" max="100" defaultValue="80" required className="pl-9 h-9 sm:h-10 text-sm sm:text-base" />
                                </div>
                            </div>
                            <DialogFooter className="px-2 sm:px-0 gap-2 sm:gap-0">
                                <Button type="submit" className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Guardar Repartidor</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </CardHeader>
        <CardContent className="px-4 sm:px-6">
            <Tabs defaultValue="active">
                <TabsList className="w-full sm:w-auto">
                    <TabsTrigger value="active" className="text-xs sm:text-sm">Repartidores Activos</TabsTrigger>
                    <TabsTrigger value="pending" className="text-xs sm:text-sm">
                        Solicitudes Pendientes
                        {pendingDrivers.length > 0 && (
                            <Badge className="ml-2 bg-primary">{pendingDrivers.length}</Badge>
                        )}
                    </TabsTrigger>
                </TabsList>
                <TabsContent value="active" className="mt-4">
                     <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                        {activeDrivers.map((driver) => (
                            <Card key={driver.id} className="p-3 sm:p-4 space-y-3 flex flex-col">
                                <div className="flex justify-between items-start gap-2">
                                    <div className="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                        <Avatar className="h-10 w-10 sm:h-12 sm:w-12">
                                            {driver.profileImageUrl && <AvatarImage src={driver.profileImageUrl} alt={driver.name} />}
                                            <AvatarFallback className="text-xs sm:text-sm">{getInitials(driver.name)}</AvatarFallback>
                                        </Avatar>
                                        <div className="flex-1 min-w-0">
                                            <p className="font-bold text-sm sm:text-base truncate">{driver.name}</p>
                                            <p className="text-xs sm:text-sm text-muted-foreground truncate">{driver.email}</p>
                                        </div>
                                    </div>
                                    {renderActions(driver)}
                                </div>
                                <div className="flex justify-between items-center text-xs sm:text-sm pt-2 border-t mt-auto">
                                    <Badge variant="outline" className="text-xs">{driver.vehicle}</Badge>
                                    <Badge variant={driver.status === 'Activo' ? 'default' : 'secondary'} className="text-xs">{driver.status}</Badge>
                                </div>
                            </Card>
                        ))}
                    </div>
                </TabsContent>
                <TabsContent value="pending" className="mt-4">
                     <div className="overflow-x-auto">
                        <Table className="text-xs sm:text-sm">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Vehículo</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pendingDrivers.map((driver) => (
                                    <TableRow key={driver.id}>
                                        <TableCell className="font-medium whitespace-nowrap">{driver.name}</TableCell>
                                        <TableCell className="whitespace-nowrap">{driver.email}</TableCell>
                                        <TableCell>{driver.vehicle}</TableCell>
                                        <TableCell>
                                          {driver.documentImageUrl ? (
                                              <Button variant="outline" size="sm" onClick={() => handleViewImage(driver.documentImageUrl!)} className="h-8 sm:h-9 text-xs sm:text-sm">
                                                  <FileText className="mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Ver
                                              </Button>
                                          ) : (
                                              <span className="text-xs text-muted-foreground">No subido</span>
                                          )}
                                        </TableCell>
                                        <TableCell className="flex gap-2">
                                            <Button size="sm" onClick={() => handleApprove(driver.id)} className="h-8 sm:h-9 text-xs sm:text-sm"><CheckCircle className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4"/> Aprobar</Button>
                                            <Button size="sm" variant="destructive" onClick={() => handleReject(driver.id)} className="h-8 sm:h-9 text-xs sm:text-sm"><XCircle className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4"/> Rechazar</Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                     </div>
                </TabsContent>
            </Tabs>

        </CardContent>
    </Card>

    {/* Edit Driver Dialog */}
    <Dialog open={isEditDialogOpen} onOpenChange={setEditDialogOpen}>
        <DialogContent className="mx-2 sm:mx-0 sm:max-w-lg">
            <DialogHeader className="px-2 sm:px-0">
                <DialogTitle className="text-lg sm:text-xl">Editar Repartidor</DialogTitle>
                <DialogDescription className="text-sm sm:text-base">Actualiza los detalles del repartidor.</DialogDescription>
            </DialogHeader>
            {editingDriver && (
            <form onSubmit={handleUpdateDriver}>
                <div className="grid gap-3 py-4 max-h-[70vh] overflow-y-auto px-2 sm:px-0">
                    <Label htmlFor="edit-name" className="text-sm sm:text-base">Nombre Completo</Label>
                    <Input id="edit-name" name="name" defaultValue={editingDriver.name} required className="h-9 sm:h-10 text-sm sm:text-base" />
                    <Label htmlFor="edit-email" className="text-sm sm:text-base">Correo Electrónico</Label>
                    <Input id="edit-email" name="email" type="email" defaultValue={editingDriver.email} required className="h-9 sm:h-10 text-sm sm:text-base" />
                    <Label htmlFor="edit-dni" className="text-sm sm:text-base">DNI</Label>
                    <Input id="edit-dni" name="dni" defaultValue={editingDriver.dni} required className="h-9 sm:h-10 text-sm sm:text-base" />
                    <Label htmlFor="edit-phone" className="text-sm sm:text-base">Celular</Label>
                    <Input id="edit-phone" name="phone" type="tel" defaultValue={editingDriver.phone} required className="h-9 sm:h-10 text-sm sm:text-base" />
                    <Label htmlFor="edit-bankAccount" className="text-sm sm:text-base">Cuenta Bancaria</Label>
                    <Input id="edit-bankAccount" name="bankAccount" defaultValue={editingDriver.bankAccount} required className="h-9 sm:h-10 text-sm sm:text-base" />
                    <Label className="text-sm sm:text-base">Vehículo</Label>
                    <Select name="vehicle" defaultValue={editingDriver.vehicle} required>
                        <SelectTrigger className="h-9 sm:h-10 text-sm sm:text-base"><SelectValue placeholder="Selecciona un vehículo" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="Moto">Moto</SelectItem>
                            <SelectItem value="Coche">Coche</SelectItem>
                            <SelectItem value="Bicicleta">Bicicleta</SelectItem>
                        </SelectContent>
                    </Select>
                     <Label className="text-sm sm:text-base">Estado</Label>
                    <Select name="status" defaultValue={editingDriver.status} required>
                        <SelectTrigger className="h-9 sm:h-10 text-sm sm:text-base"><SelectValue placeholder="Selecciona un estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="Activo">Activo</SelectItem>
                            <SelectItem value="Inactivo">Inactivo</SelectItem>
                        </SelectContent>
                    </Select>
                    <Label htmlFor="edit-commissionRate" className="text-sm sm:text-base">Tasa de Comisión (%)</Label>
                    <div className="relative">
                        <Percent className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input id="edit-commissionRate" name="commissionRate" type="number" step="0.01" min="0" max="100" defaultValue={editingDriver.commissionRate} className="pl-9 h-9 sm:h-10 text-sm sm:text-base" required />
                    </div>
                </div>
                <DialogFooter className="px-2 sm:px-0 gap-2 sm:gap-0">
                    <Button type="button" variant="ghost" onClick={() => setEditDialogOpen(false)} className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Cancelar</Button>
                    <Button type="submit" className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Guardar Cambios</Button>
                </DialogFooter>
            </form>
            )}
        </DialogContent>
    </Dialog>

    <AlertDialog open={!!driverToDelete} onOpenChange={() => setDriverToDelete(null)}>
        <AlertDialogContent className="mx-4 sm:mx-0">
            <AlertDialogHeader className="px-2 sm:px-0">
                <AlertDialogTitle className="text-lg sm:text-xl">¿Estás seguro de que quieres eliminar a este repartidor?</AlertDialogTitle>
                <AlertDialogDescription className="text-sm sm:text-base">
                    Esta acción no se puede deshacer. Se eliminará a "{driverToDelete?.name}" permanentemente.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter className="px-2 sm:px-0 gap-2 sm:gap-0">
                <AlertDialogCancel className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={handleDeleteConfirm} className="bg-destructive hover:bg-destructive/90 h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Eliminar</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <Dialog open={isImageViewOpen} onOpenChange={setImageViewOpen}>
        <DialogContent className="mx-2 sm:mx-0 max-w-xl">
            <DialogHeader className="px-2 sm:px-0">
                <DialogTitle className="text-lg sm:text-xl">Documento del Repartidor</DialogTitle>
            </DialogHeader>
            {imageToView && (
                <div className="flex justify-center p-2 sm:p-4">
                    <Image src={imageToView} alt="Documento" width={500} height={300} className="object-contain w-full h-auto" />
                </div>
            )}
            <DialogFooter className="px-2 sm:px-0">
                <Button type="button" variant="secondary" onClick={() => setImageViewOpen(false)} className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Cerrar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
    </div>
  );
}

export default function AdminDriversPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminDriversPageContent />
    </AuthGuard>
  );
}

    
