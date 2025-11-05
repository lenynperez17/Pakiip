

"use client";

import { useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { MoreHorizontal, PlusCircle, Trash, Edit, Package, Percent, MapPin, CheckCircle, XCircle } from "lucide-react";
import { Vendor } from "@/lib/placeholder-data";
import { useAppData } from "@/hooks/use-app-data";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";
import { formatCurrency } from "@/lib/utils";
import { cn } from "@/lib/utils";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { AuthGuard } from "@/components/AuthGuard";
import { AddressAutocomplete } from "@/components/AddressAutocomplete";
import { GeocodeResult } from "@/lib/google-geocoding";


function AdminStoresPageContent() {
  const { vendors, categories, cities, appSettings, saveVendor, deleteVendor } = useAppData();
  const [isAddDialogOpen, setAddDialogOpen] = useState(false);
  const [isEditDialogOpen, setEditDialogOpen] = useState(false);
  const [editingVendor, setEditingVendor] = useState<Vendor | null>(null);
  const [vendorToDelete, setVendorToDelete] = useState<Vendor | null>(null);
  const { toast } = useToast();

  const [addLogoPreview, setAddLogoPreview] = useState<string | null>(null);
  const [addBannerPreview, setAddBannerPreview] = useState<string | null>(null);
  const [editLogoPreview, setEditLogoPreview] = useState<string | null>(null);
  const [editBannerPreview, setEditBannerPreview] = useState<string | null>(null);

  // Estados para dirección y coordenadas
  const [addAddress, setAddAddress] = useState<string>("");
  const [addCoordinates, setAddCoordinates] = useState<{ lat: number; lng: number } | null>(null);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, setPreview: React.Dispatch<React.SetStateAction<string | null>>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    } else {
      setPreview(null);
    }
  };

  const handleAddVendor = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const newVendor: Vendor = {
        id: `v${Date.now()}`,
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        phone: formData.get('phone') as string,
        description: formData.get('description') as string,
        category: formData.get('category') as string,
        location: formData.get('location') as string,
        commissionRate: parseFloat(formData.get('commissionRate') as string) || 15,
        imageUrl: addLogoPreview || 'https://placehold.co/64x64.png',
        bannerUrl: addBannerPreview || 'https://placehold.co/1200x400.png',
        products: [],
        productCategories: [],
        isFeatured: formData.get('isFeatured') === 'on',
        status: 'active',
        address: addAddress,
        dni: '',
        coordinates: addCoordinates || { lat: 0, lng: 0 } // Usa coordenadas reales de Google Maps
    };
    saveVendor(newVendor);
    setAddDialogOpen(false);
    setAddLogoPreview(null);
    setAddBannerPreview(null);
    setAddAddress("");
    setAddCoordinates(null);
    toast({ title: "Tienda Añadida", description: `${newVendor.name} ha sido añadida.` });
  };
  
  const handleEditClick = (vendor: Vendor) => {
    setEditingVendor(vendor);
    setEditLogoPreview(vendor.imageUrl);
    setEditBannerPreview(vendor.bannerUrl || null);
    setEditDialogOpen(true);
  };

  const handleUpdateVendor = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingVendor) return;

    const formData = new FormData(event.currentTarget);
    const updatedVendor: Vendor = {
        ...editingVendor,
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        phone: formData.get('phone') as string,
        description: formData.get('description') as string,
        category: formData.get('category') as string,
        location: formData.get('location') as string,
        isFeatured: formData.get('isFeatured') === 'on',
        status: formData.get('status') as Vendor['status'],
        commissionRate: parseFloat(formData.get('commissionRate') as string),
        imageUrl: editLogoPreview || editingVendor.imageUrl,
        bannerUrl: editBannerPreview || editingVendor.bannerUrl,
    };
    
    saveVendor(updatedVendor);
    setEditDialogOpen(false);
    setEditingVendor(null);
    toast({ title: "Tienda Actualizada", description: `${updatedVendor.name} ha sido actualizada.` });
  };

  const handleStatusToggle = (vendorId: string, status: Vendor['status']) => {
    const vendor = vendors.find(v => v.id === vendorId);
    if (vendor) {
        saveVendor({ ...vendor, status: status === 'active' ? 'inactive' : 'active' });
    }
  };

  const handleDeleteConfirm = () => {
    if (!vendorToDelete) return;
    deleteVendor(vendorToDelete.id);
    toast({ title: "Tienda Eliminada", description: `${vendorToDelete.name} ha sido eliminada.`, variant: "destructive" });
    setVendorToDelete(null);
  };
  
  const handleApprove = (vendorId: string) => {
    const vendor = vendors.find(v => v.id === vendorId);
    if (vendor) {
        saveVendor({ ...vendor, status: 'active' });
        toast({ title: "Tienda Aprobada", description: `${vendor.name} ha sido activada.` });
    }
  };

  const handleReject = (vendorId: string) => {
      const vendor = vendors.find(v => v.id === vendorId);
      if (vendor) {
          saveVendor({ ...vendor, status: 'rejected' });
          toast({ title: "Tienda Rechazada", description: `${vendor.name} ha sido marcada como rechazada.`, variant: "destructive" });
      }
  };

  const renderActions = (vendor: Vendor) => (
      <DropdownMenu>
        <DropdownMenuTrigger asChild><Button size="icon" variant="ghost"><MoreHorizontal /></Button></DropdownMenuTrigger>
        <DropdownMenuContent>
            <DropdownMenuItem onClick={() => handleEditClick(vendor)}><Edit className="mr-2 h-4 w-4" /> Editar Tienda</DropdownMenuItem>
            <DropdownMenuItem asChild>
                <Link href={`/vendor/dashboard?vendorId=${vendor.id}`}>
                    <Package className="mr-2 h-4 w-4" /> Gestionar Productos
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem className="text-destructive" onClick={() => setVendorToDelete(vendor)}>
                <Trash className="mr-2 h-4 w-4" /> Eliminar
            </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
  );
  
  const activeVendors = vendors.filter(v => v.status === 'active' || v.status === 'inactive');
  const pendingVendors = vendors.filter(v => v.status === 'pending');

  return (
    <>
    <Card>
        <CardHeader>
        <div className="flex items-center justify-between">
            <div>
                <CardTitle>Gestionar Tiendas</CardTitle>
                <CardDescription>Añade, edita o elimina tiendas de la plataforma.</CardDescription>
            </div>
            <Dialog open={isAddDialogOpen} onOpenChange={setAddDialogOpen}>
                <DialogTrigger asChild>
                    <Button onClick={() => {
                      setAddLogoPreview(null);
                      setAddBannerPreview(null);
                      setAddAddress("");
                      setAddCoordinates(null);
                    }}>
                        <PlusCircle className="mr-2 h-4 w-4" /> Añadir Tienda
                    </Button>
                </DialogTrigger>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Añadir Nueva Tienda</DialogTitle>
                        <DialogDescription>Completa los detalles de la nueva tienda.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleAddVendor}>
                        <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                            <div className="space-y-1">
                                <Label htmlFor="name">Nombre del Negocio</Label>
                                <Input id="name" name="name" required />
                            </div>
                             <div className="space-y-1">
                                <Label htmlFor="email">Correo Electrónico de Contacto</Label>
                                <Input id="email" name="email" type="email" required />
                            </div>
                             <div className="space-y-1">
                                <Label htmlFor="phone">Teléfono</Label>
                                <Input id="phone" name="phone" type="tel" required />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="description">Descripción</Label>
                                <Textarea id="description" name="description" required />
                            </div>
                            <div className="space-y-1">
                                <AddressAutocomplete
                                    label="Dirección del Negocio"
                                    placeholder="Busca la dirección exacta..."
                                    value={addAddress}
                                    onChange={setAddAddress}
                                    onSelectAddress={(result: GeocodeResult) => {
                                        setAddCoordinates(result.coordinates);
                                        console.log('📍 Admin - Coordenadas guardadas:', result.coordinates);
                                    }}
                                    required
                                    id="address"
                                />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="category">Categoría</Label>
                                <Select name="category" required>
                                    <SelectTrigger id="category">
                                        <SelectValue placeholder="Selecciona una categoría" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categories.map((category) => (
                                            <SelectItem key={category.id} value={category.name}>
                                                {category.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="location">Ubicación</Label>
                                <Select name="location" required>
                                    <SelectTrigger id="location">
                                        <SelectValue placeholder="Selecciona una ubicación" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {cities.map((city) => (
                                            <SelectItem key={city.id} value={city.name}>
                                                {city.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="commissionRate">Tasa de Comisión (%)</Label>
                                <div className="relative">
                                    <Percent className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input id="commissionRate" name="commissionRate" type="number" step="0.01" min="0" max="100" defaultValue="15" className="pl-9" required />
                                </div>
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="logo">Logo</Label>
                                {addLogoPreview && <Image src={addLogoPreview} alt="Vista previa del logo" width={64} height={64} className="rounded-md object-cover my-2" />}
                                <Input id="logo" name="logo" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setAddLogoPreview)} />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="banner">Banner</Label>
                                {addBannerPreview && <Image src={addBannerPreview} alt="Vista previa del banner" width={200} height={100} className="rounded-md object-cover my-2" />}
                                <Input id="banner" name="banner" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setAddBannerPreview)} />
                            </div>
                            <div className="flex items-center justify-between space-x-2 mt-2 rounded-md border p-3">
                                <Label htmlFor="isFeatured" className="flex flex-col space-y-1 cursor-pointer">
                                    <span>Destacar en la página principal</span>
                                    <span className="font-normal text-muted-foreground text-xs">
                                        Costo de servicio: {formatCurrency(appSettings.featuredStoreCost || 0, appSettings.currencySymbol)}
                                    </span>
                                </Label>
                                <Switch id="isFeatured" name="isFeatured" />
                            </div>
                        </div>
                        <DialogFooter className="pt-4">
                            <Button type="button" variant="ghost" onClick={() => setAddDialogOpen(false)}>Cancelar</Button>
                            <Button type="submit">Guardar Tienda</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
        </CardHeader>
        <CardContent>
            <Tabs defaultValue="active">
                <TabsList>
                    <TabsTrigger value="active">Tiendas Activas</TabsTrigger>
                    <TabsTrigger value="pending">
                        Solicitudes Pendientes
                        {pendingVendors.length > 0 && (
                            <Badge className="ml-2 bg-primary">{pendingVendors.length}</Badge>
                        )}
                    </TabsTrigger>
                </TabsList>
                <TabsContent value="active" className="mt-4">
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        {activeVendors.map((vendor) => (
                            <Card key={vendor.id} className="flex flex-col">
                                <CardHeader className="p-4 flex flex-row items-start gap-4">
                                    <Image
                                        alt={vendor.name}
                                        className="aspect-square rounded-md object-cover"
                                        height="64"
                                        src={vendor.imageUrl}
                                        width="64"
                                        data-ai-hint="logo"
                                    />
                                    <div className="flex-grow">
                                        <p className="font-semibold leading-tight line-clamp-2">{vendor.name}</p>
                                        <Badge variant="secondary" className="mt-1">{vendor.category}</Badge>
                                    </div>
                                    {renderActions(vendor)}
                                </CardHeader>
                                <CardContent className="p-4 pt-0 flex flex-col justify-end flex-grow">
                                    <div className="flex justify-between items-center text-sm border-t pt-3 mt-3">
                                        <div className="flex items-center gap-2">
                                            <Switch
                                                checked={vendor.status === 'active'}
                                                onCheckedChange={() => handleStatusToggle(vendor.id, vendor.status)}
                                                aria-label="Activar/Desactivar tienda"
                                                id={`status-switch-${vendor.id}`}
                                            />
                                            <Label htmlFor={`status-switch-${vendor.id}`} className={cn("text-xs", vendor.status === 'active' ? "text-primary" : "text-muted-foreground")}>
                                                {vendor.status === 'active' ? 'Activa' : 'Inactiva'}
                                            </Label>
                                        </div>
                                        <div className="flex items-center gap-2">
                                             <Switch
                                                checked={vendor.isFeatured}
                                                onCheckedChange={(checked) => saveVendor({ ...vendor, isFeatured: checked })}
                                                aria-label="Destacar tienda"
                                                id={`feature-switch-${vendor.id}`}
                                            />
                                            <Label htmlFor={`feature-switch-${vendor.id}`} className="text-xs">Destacada</Label>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </TabsContent>
                 <TabsContent value="pending" className="mt-4">
                     <div className="overflow-x-auto text-xs sm:text-sm md:text-base">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Ubicación</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pendingVendors.map((vendor) => (
                                    <TableRow key={vendor.id}>
                                        <TableCell className="font-medium whitespace-nowrap">{vendor.name}</TableCell>
                                        <TableCell>{vendor.email}</TableCell>
                                        <TableCell>{vendor.location}</TableCell>
                                        <TableCell className="flex gap-2">
                                            <Button size="sm" onClick={() => handleApprove(vendor.id)}><CheckCircle className="mr-2 h-4 w-4"/> Aprobar</Button>
                                            <Button size="sm" variant="destructive" onClick={() => handleReject(vendor.id)}><XCircle className="mr-2 h-4 w-4"/> Rechazar</Button>
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

    {/* Edit Vendor Dialog */}
    <Dialog open={isEditDialogOpen} onOpenChange={setEditDialogOpen}>
        <DialogContent className="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Editar Tienda</DialogTitle>
                <DialogDescription>Actualiza los detalles de la tienda.</DialogDescription>
            </DialogHeader>
            {editingVendor && (
            <form onSubmit={handleUpdateVendor}>
                <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                    <div className="space-y-1">
                        <Label htmlFor="edit-name">Nombre del Negocio</Label>
                        <Input id="edit-name" name="name" defaultValue={editingVendor.name} required />
                    </div>
                     <div className="space-y-1">
                        <Label htmlFor="edit-email">Correo Electrónico de Contacto</Label>
                        <Input id="edit-email" name="email" type="email" defaultValue={editingVendor.email} required />
                    </div>
                     <div className="space-y-1">
                        <Label htmlFor="edit-phone">Teléfono</Label>
                        <Input id="edit-phone" name="phone" type="tel" defaultValue={editingVendor.phone} required />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="edit-description">Descripción</Label>
                        <Textarea id="edit-description" name="description" defaultValue={editingVendor.description} required />
                    </div>
                     <div className="space-y-1">
                        <Label htmlFor="edit-status">Estado de la Tienda</Label>
                        <Select name="status" defaultValue={editingVendor.status}>
                            <SelectTrigger id="edit-status">
                                <SelectValue placeholder="Selecciona un estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Activa</SelectItem>
                                <SelectItem value="inactive">Inactiva</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="edit-category">Categoría</Label>
                         <Select name="category" defaultValue={editingVendor.category}>
                            <SelectTrigger id="edit-category">
                                <SelectValue placeholder="Selecciona una categoría" />
                            </SelectTrigger>
                            <SelectContent>
                                {categories.map((category) => (
                                    <SelectItem key={category.id} value={category.name}>
                                        {category.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                     <div className="space-y-1">
                        <Label htmlFor="edit-location">Ubicación</Label>
                        <Select name="location" defaultValue={editingVendor.location}>
                            <SelectTrigger id="edit-location">
                                <SelectValue placeholder="Selecciona una ubicación" />
                            </SelectTrigger>
                            <SelectContent>
                                {cities.map((city) => (
                                    <SelectItem key={city.id} value={city.name}>
                                        {city.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="edit-commissionRate">Tasa de Comisión (%)</Label>
                        <div className="relative">
                            <Percent className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input id="edit-commissionRate" name="commissionRate" type="number" step="0.01" min="0" max="100" defaultValue={editingVendor.commissionRate} className="pl-9" required />
                        </div>
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="edit-logo">Logo</Label>
                        {editLogoPreview && <Image src={editLogoPreview} alt="Vista previa del logo" width={64} height={64} className="rounded-md object-cover my-2" />}
                        <Input id="edit-logo" name="logo" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setEditLogoPreview)} />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="edit-banner">Banner</Label>
                        {editBannerPreview && <Image src={editBannerPreview} alt="Vista previa del banner" width={200} height={100} className="rounded-md object-cover my-2" />}
                        <Input id="edit-banner" name="banner" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setEditBannerPreview)} />
                    </div>
                     <div className="flex items-center justify-between space-x-2 mt-2 rounded-md border p-3">
                        <Label htmlFor="edit-isFeatured" className="flex flex-col space-y-1 cursor-pointer">
                            <span>Destacar en la página principal</span>
                            <span className="font-normal text-muted-foreground text-xs">
                                Costo de servicio: {formatCurrency(appSettings.featuredStoreCost || 0, appSettings.currencySymbol)}
                            </span>
                        </Label>
                        <Switch id="edit-isFeatured" name="isFeatured" defaultChecked={editingVendor.isFeatured} />
                    </div>
                </div>
                <DialogFooter className="pt-4">
                    <Button type="button" variant="ghost" onClick={() => setEditDialogOpen(false)}>Cancelar</Button>
                    <Button type="submit">Guardar Cambios</Button>
                </DialogFooter>
            </form>
            )}
        </DialogContent>
    </Dialog>
    
    <AlertDialog open={!!vendorToDelete} onOpenChange={() => setVendorToDelete(null)}>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás seguro de que quieres eliminar esta tienda?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Se eliminará la tienda "{vendorToDelete?.name}" permanentemente.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={handleDeleteConfirm} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
    </>
  );
}

export default function AdminStoresPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminStoresPageContent />
    </AuthGuard>
  );
}
