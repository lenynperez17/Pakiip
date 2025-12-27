
"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { MoreHorizontal, KeyRound, Trash, Eye, User, Store, Truck } from "lucide-react";
import type { User as AppUser, Vendor, DeliveryDriver } from "@/lib/placeholder-data";
import { useToast } from "@/hooks/use-toast";
import { useAppData } from "@/hooks/use-app-data";
import { AuthGuard } from "@/components/AuthGuard";

type UserToDelete = {
    id: string;
    name: string;
    type: 'customer' | 'vendor' | 'driver';
};

function AdminUsersPageContent() {
    const { users, vendors, drivers, deleteUser, deleteVendor, deleteDriver } = useAppData();
    const [userToDelete, setUserToDelete] = useState<UserToDelete | null>(null);
    const { toast } = useToast();

    const handleResetPassword = (email: string) => {
        toast({
            title: "Función no implementada",
            description: `Se simularía un envío de restablecimiento de contraseña a ${email}.`,
        });
    };
    
    const handleDeleteClick = (user: {id: string, name: string, email: string}, type: UserToDelete['type']) => {
        setUserToDelete({ ...user, type });
    };

    const confirmDelete = () => {
        if (!userToDelete) return;

        // 🔒 SEGURIDAD: Validar que no se elimine el último admin
        if (userToDelete.type === 'customer') {
            const userToDeleteData = users.find(u => u.id === userToDelete.id);
            if (userToDeleteData?.role === 'admin') {
                const adminCount = users.filter(u => u.role === 'admin').length;
                if (adminCount <= 1) {
                    toast({
                        title: "No se puede eliminar",
                        description: "No puedes eliminar el último administrador del sistema. Debe haber al menos un administrador activo.",
                        variant: "destructive"
                    });
                    setUserToDelete(null);
                    return;
                }
            }
        }

        try {
            switch(userToDelete.type) {
                case 'customer':
                    deleteUser(userToDelete.id);
                    break;
                case 'vendor':
                    deleteVendor(userToDelete.id);
                    break;
                case 'driver':
                    deleteDriver(userToDelete.id);
                    break;
            }
            toast({
                title: "Usuario Eliminado",
                description: `La cuenta de ${userToDelete.name} ha sido eliminada.`,
                variant: "destructive"
            });
        } catch (error) {
            toast({
                title: "Error",
                description: `No se pudo eliminar la cuenta.`,
                variant: "destructive"
            });
        }

        setUserToDelete(null);
    };

  const renderActions = (user: {id: string, name: string, email: string}, type: UserToDelete['type']) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <Button size="icon" variant="ghost"><MoreHorizontal /></Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent>
            <DropdownMenuItem><Eye className="mr-2 h-4 w-4" /> Ver Perfil</DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleResetPassword(user.email)}><KeyRound className="mr-2 h-4 w-4" /> Restablecer Contraseña</DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleDeleteClick(user, type)} className="text-destructive focus:text-destructive focus:bg-destructive/10"><Trash className="mr-2 h-4 w-4" /> Eliminar</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
  );

  return (
    <>
    <Card>
      <CardHeader>
        <CardTitle className="text-xl sm:text-2xl">Gestión de Usuarios</CardTitle>
        <CardDescription className="text-sm sm:text-base">
          Administra todas las cuentas de la plataforma: clientes, tiendas y repartidores.
        </CardDescription>
      </CardHeader>
      <CardContent className="px-3 sm:px-6">
        <Tabs defaultValue="customers">
          <TabsList className="grid w-full grid-cols-3 h-auto">
            <TabsTrigger value="customers" className="text-xs sm:text-sm"><User className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" />Clientes</TabsTrigger>
            <TabsTrigger value="vendors" className="text-xs sm:text-sm"><Store className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4"/>Tiendas</TabsTrigger>
            <TabsTrigger value="drivers" className="text-xs sm:text-sm"><Truck className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4"/>Repartidores</TabsTrigger>
          </TabsList>
          
          <TabsContent value="customers" className="mt-3 sm:mt-4">
            {/* Mobile View */}
             <div className="grid gap-3 sm:gap-4 md:hidden">
                {users.map((user) => (
                    <Card key={user.id} className="p-3 sm:p-4 flex justify-between items-center">
                        <div>
                            <p className="font-medium text-sm sm:text-base">{user.name}</p>
                            <p className="text-xs sm:text-sm text-muted-foreground">{user.email}</p>
                        </div>
                        {renderActions(user, 'customer')}
                    </Card>
                ))}
            </div>
            {/* Desktop View */}
            <div className="hidden md:block">
                <div className="overflow-x-auto text-sm md:text-base">
                    <Table>
                    <TableHeader>
                        <TableRow>
                        <TableHead>Nombre</TableHead>
                        <TableHead>Correo Electrónico</TableHead>
                        <TableHead className="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {users.map((user) => (
                        <TableRow key={user.id}>
                            <TableCell className="font-medium whitespace-nowrap">{user.name}</TableCell>
                            <TableCell className="whitespace-nowrap">{user.email}</TableCell>
                            <TableCell className="text-right">
                                {renderActions(user, 'customer')}
                            </TableCell>
                        </TableRow>
                        ))}
                    </TableBody>
                    </Table>
                </div>
            </div>
          </TabsContent>

          <TabsContent value="vendors" className="mt-4">
             {/* Mobile View */}
             <div className="grid gap-4 md:hidden">
                {vendors.map((vendor) => (
                    <Card key={vendor.id} className="p-4 flex justify-between items-center">
                        <div>
                            <p className="font-medium">{vendor.name}</p>
                            <p className="text-sm text-muted-foreground">{vendor.email}</p>
                        </div>
                        {renderActions(vendor, 'vendor')}
                    </Card>
                ))}
            </div>
            {/* Desktop View */}
            <div className="hidden md:block">
                <div className="overflow-x-auto text-xs sm:text-sm md:text-base">
                    <Table>
                    <TableHeader>
                        <TableRow>
                        <TableHead>Nombre de la Tienda</TableHead>
                        <TableHead>Correo de Contacto</TableHead>
                        <TableHead className="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {vendors.map((vendor) => (
                        <TableRow key={vendor.id}>
                            <TableCell className="font-medium whitespace-nowrap">{vendor.name}</TableCell>
                            <TableCell className="whitespace-nowrap">{vendor.email}</TableCell>
                            <TableCell className="text-right">
                                {renderActions(vendor, 'vendor')}
                            </TableCell>
                        </TableRow>
                        ))}
                    </TableBody>
                    </Table>
                </div>
            </div>
          </TabsContent>

          <TabsContent value="drivers" className="mt-4">
             {/* Mobile View */}
             <div className="grid gap-4 md:hidden">
                {drivers.map((driver) => (
                     <Card key={driver.id} className="p-4 flex justify-between items-center">
                        <div>
                            <p className="font-medium">{driver.name}</p>
                            <p className="text-sm text-muted-foreground">{driver.email}</p>
                        </div>
                        {renderActions(driver, 'driver')}
                    </Card>
                ))}
            </div>
            {/* Desktop View */}
            <div className="hidden md:block">
                <div className="overflow-x-auto text-xs sm:text-sm md:text-base">
                    <Table>
                    <TableHeader>
                        <TableRow>
                        <TableHead>Nombre del Repartidor</TableHead>
                        <TableHead>Correo Electrónico</TableHead>
                        <TableHead className="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {drivers.map((driver) => (
                        <TableRow key={driver.id}>
                            <TableCell className="font-medium whitespace-nowrap">{driver.name}</TableCell>
                            <TableCell className="whitespace-nowrap">{driver.email}</TableCell>
                            <TableCell className="text-right">
                                {renderActions(driver, 'driver')}
                            </TableCell>
                        </TableRow>
                        ))}
                    </TableBody>
                    </Table>
                </div>
            </div>
          </TabsContent>

        </Tabs>
      </CardContent>
    </Card>

    <AlertDialog open={!!userToDelete} onOpenChange={() => setUserToDelete(null)}>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás realmente seguro?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Esto eliminará permanentemente la cuenta de {userToDelete?.name} y todos sus datos asociados.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={confirmDelete} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
    </>
  );
}

export default function AdminUsersPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
            <AdminUsersPageContent />
        </AuthGuard>
    );
}
