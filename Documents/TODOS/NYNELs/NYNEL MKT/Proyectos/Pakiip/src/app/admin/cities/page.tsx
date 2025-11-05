

"use client";

import React, { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { PlusCircle, Edit, Trash2 } from "lucide-react";
import { City, DeliveryZone, Coordinate } from "@/lib/placeholder-data";
import { useAppData } from "@/hooks/use-app-data";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ZoneEditorMap } from "@/components/ZoneEditorMap";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { formatCurrency } from "@/lib/utils";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { LocationPickerMap } from "@/components/LocationPickerMap";
import { Skeleton } from "@/components/ui/skeleton";
import { AuthGuard } from "@/components/AuthGuard";

function AdminDeliveryZonesPageContent() {
  const { cities, deliveryZones, saveDeliveryZone, deleteDeliveryZone, appSettings, saveCity, deleteCity } = useAppData();
  const [selectedCity, setSelectedCity] = useState<City | null>(cities[0] || null);
  const [editingZone, setEditingZone] = useState<DeliveryZone | null>(null);
  const [zoneToDelete, setZoneToDelete] = useState<DeliveryZone | null>(null);
  const [isZoneFormOpen, setZoneFormOpen] = useState(false);
  
  const [isCityFormOpen, setCityFormOpen] = useState(false);
  const [isMapLoading, setIsMapLoading] = useState(true);
  const [editingCity, setEditingCity] = useState<City | null>(null);
  const [cityToDelete, setCityToDelete] = useState<City | null>(null);
  const [cityCoordinates, setCityCoordinates] = useState<Coordinate | undefined>(undefined);

  const { toast } = useToast();

  useEffect(() => {
      // If the selected city is deleted, reset the selection
      if (selectedCity && !cities.find(c => c.id === selectedCity.id)) {
          setSelectedCity(cities[0] || null);
      }
  }, [cities, selectedCity]);

  // Handle map loading within the dialog
   useEffect(() => {
    if (isCityFormOpen) {
      setIsMapLoading(true);
      const timer = setTimeout(() => {
        setIsMapLoading(false);
      }, 300); // Small delay to ensure dialog is rendered
      return () => clearTimeout(timer);
    }
  }, [isCityFormOpen]);


  const handleCityChange = (cityId: string) => {
    const city = cities.find(c => c.id === cityId) || null;
    setSelectedCity(city);
    setEditingZone(null); // Clear editing when city changes
  };
  
  const handleEditCityClick = (city: City) => {
      setEditingCity(city);
      setCityCoordinates(city.coordinates);
      setCityFormOpen(true);
  }

  const handleAddNewCityClick = () => {
    setEditingCity(null);
    setCityCoordinates(undefined);
    setCityFormOpen(true);
  };
  
  const handleSaveCity = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!cityCoordinates) {
        toast({ variant: 'destructive', title: 'Error', description: 'Por favor, marca las coordenadas de la ubicación en el mapa.' });
        return;
    }
    
    const formData = new FormData(event.currentTarget);
    const updatedCity: City = {
        id: editingCity?.id || `city${Date.now()}`,
        name: formData.get('name') as string,
        coordinates: cityCoordinates,
    };
    saveCity(updatedCity);
    toast({ title: "Ubicación Guardada", description: `La ubicación "${updatedCity.name}" ha sido guardada.` });
    setCityFormOpen(false);
    setEditingCity(null);
  }
  
  const handleDeleteCityConfirm = () => {
      if (!cityToDelete) return;
      // You might want to add logic here to check if the city has zones before deleting
      deleteCity(cityToDelete.id);
      toast({ title: "Ubicación Eliminada", description: `La ubicación "${cityToDelete.name}" ha sido eliminada.`, variant: "destructive" });
      setCityToDelete(null);
  }

  const handleZoneDrawn = (path: Coordinate[]) => {
    if (!selectedCity) {
      toast({ title: "Selecciona una ubicación", description: "Debes seleccionar una ubicación antes de dibujar una zona.", variant: "destructive" });
      return;
    }
    setEditingZone({
      id: `zone${Date.now()}`,
      name: "",
      cityId: selectedCity.id,
      path: path,
      shippingFee: 0,
    });
    setZoneFormOpen(true);
  };
  
  const handleEditZoneClick = (zone: DeliveryZone) => {
      setEditingZone(zone);
      setZoneFormOpen(true);
  }

  const handleSaveZone = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingZone) return;

    const formData = new FormData(event.currentTarget);
    const updatedZone: DeliveryZone = {
      ...editingZone,
      name: formData.get('name') as string,
      shippingFee: parseFloat(formData.get('shippingFee') as string),
    };

    saveDeliveryZone(updatedZone);
    toast({ title: "Zona Guardada", description: `La zona "${updatedZone.name}" ha sido guardada.` });
    setEditingZone(null);
    setZoneFormOpen(false);
  };

  const handleDeleteZoneConfirm = () => {
    if (!zoneToDelete) return;
    deleteDeliveryZone(zoneToDelete.id);
    toast({ title: "Zona Eliminada", description: `La zona "${zoneToDelete.name}" ha sido eliminada.`, variant: "destructive" });
    setZoneToDelete(null);
  };

  const cityZones = selectedCity ? deliveryZones.filter(z => z.cityId === selectedCity.id) : [];

  return (
    <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4 py-4 sm:py-6">

        <Card>
            <CardHeader>
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                    <div>
                        <CardTitle className="text-xl sm:text-2xl">Gestión de Ubicaciones</CardTitle>
                        <CardDescription className="text-sm sm:text-base">Añade, edita o elimina las ubicaciones donde operas.</CardDescription>
                    </div>
                     <Button onClick={handleAddNewCityClick} className="h-9 sm:h-10 text-sm sm:text-base px-3 sm:px-4 w-full sm:w-auto">
                        <PlusCircle className="mr-2 h-4 w-4" /> Añadir Ubicación
                    </Button>
                </div>
            </CardHeader>
            <CardContent className="px-3 sm:px-6">
                 <div className="overflow-x-auto text-sm md:text-base">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre de la Ubicación</TableHead>
                                <TableHead className="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {cities.map((city) => (
                                <TableRow key={city.id}>
                                    <TableCell className="font-medium">{city.name}</TableCell>
                                    <TableCell className="text-right">
                                        <Button size="icon" variant="ghost" onClick={() => handleEditCityClick(city)}>
                                            <Edit className="h-4 w-4"/>
                                        </Button>
                                        <Button size="icon" variant="ghost" className="text-destructive" onClick={() => setCityToDelete(city)}>
                                            <Trash2 className="h-4 w-4"/>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

      <div className="space-y-4 sm:space-y-6">
        <Card className="w-full">
            <CardHeader>
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                <div>
                    <CardTitle className="text-xl sm:text-2xl">Editor de Zonas de Entrega</CardTitle>
                    <CardDescription className="text-sm sm:text-base">Dibuja polígonos en el mapa para definir tus zonas y tarifas de envío.</CardDescription>
                </div>
                <Select onValueChange={handleCityChange} defaultValue={selectedCity?.id}>
                  <SelectTrigger className="w-full sm:w-[200px] h-9 sm:h-10 text-sm sm:text-base">
                    <SelectValue placeholder="Selecciona una ubicación" />
                  </SelectTrigger>
                  <SelectContent>
                    {cities.map(city => (
                      <SelectItem key={city.id} value={city.id}>{city.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </CardHeader>
            <CardContent className="h-[50vh] sm:h-[60vh] p-0">
                <ZoneEditorMap
                    center={selectedCity?.coordinates || { lat: -12.046374, lng: -77.042793 }}
                    zones={cityZones}
                    onZoneDrawn={handleZoneDrawn}
                    onZoneEdited={(zoneId, newPath) => {
                        const zone = deliveryZones.find(z => z.id === zoneId);
                        if (zone) {
                            saveDeliveryZone({ ...zone, path: newPath });
                             toast({ title: "Geometría Actualizada", description: `La forma de la zona "${zone.name}" ha sido actualizada.` });
                        }
                    }}
                />
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle className="text-xl sm:text-2xl">Zonas en {selectedCity?.name || '...'}</CardTitle>
                <CardDescription className="text-sm sm:text-base">Lista de zonas de entrega definidas para la ubicación seleccionada.</CardDescription>
            </CardHeader>
            <CardContent className="px-3 sm:px-6">
                {cityZones.length > 0 ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        {cityZones.map(zone => (
                            <Card key={zone.id} className="p-3 sm:p-4">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <p className="font-semibold text-sm sm:text-base">{zone.name}</p>
                                        <p className="text-xs sm:text-sm text-primary font-medium">{formatCurrency(zone.shippingFee, appSettings.currencySymbol)}</p>
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <Button size="icon" variant="ghost" onClick={() => handleEditZoneClick(zone)} className="h-8 w-8 sm:h-9 sm:w-9">
                                            <Edit className="h-4 w-4"/>
                                        </Button>
                                        <Button size="icon" variant="ghost" className="text-destructive h-8 w-8 sm:h-9 sm:w-9" onClick={() => setZoneToDelete(zone)}>
                                            <Trash2 className="h-4 w-4"/>
                                        </Button>
                                    </div>
                                </div>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <p className="text-xs sm:text-sm text-muted-foreground text-center py-6 sm:py-8">No hay zonas definidas para esta ubicación. ¡Dibuja una en el mapa!</p>
                )}
            </CardContent>
        </Card>
      </div>
      
      <Dialog open={isZoneFormOpen} onOpenChange={setZoneFormOpen}>
          <DialogContent>
            <DialogHeader>
                <DialogTitle>Detalles de la Zona</DialogTitle>
                <DialogDescription>Asigna un nombre y una tarifa a la zona que has definido.</DialogDescription>
            </DialogHeader>
            {editingZone && (
                 <form onSubmit={handleSaveZone}>
                    <div className="grid gap-4 py-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Nombre de la Zona</Label>
                            <Input id="name" name="name" defaultValue={editingZone.name} placeholder="Ej: Zona Centro" required />
                        </div>
                         <div className="space-y-2">
                            <Label htmlFor="shippingFee">Tarifa de Envío</Label>
                             <div className="relative">
                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                <Input id="shippingFee" name="shippingFee" type="number" step="0.01" defaultValue={editingZone.shippingFee} required className="pl-8"/>
                            </div>
                        </div>
                    </div>
                     <DialogFooter>
                        <Button type="button" variant="ghost" onClick={() => setZoneFormOpen(false)}>Cancelar</Button>
                        <Button type="submit">Guardar Zona</Button>
                    </DialogFooter>
                 </form>
            )}
          </DialogContent>
      </Dialog>
      
       <AlertDialog open={!!zoneToDelete} onOpenChange={() => setZoneToDelete(null)}>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás seguro de que quieres eliminar esta zona?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Se eliminará la zona "{zoneToDelete?.name}" permanentemente.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={handleDeleteZoneConfirm} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
    
      <Dialog open={isCityFormOpen} onOpenChange={setCityFormOpen}>
          <DialogContent className="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{editingCity ? 'Editar' : 'Añadir'} Ubicación</DialogTitle>
                <DialogDescription>Define el nombre y la ubicación central.</DialogDescription>
            </DialogHeader>
             <form onSubmit={handleSaveCity}>
                <div className="grid gap-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">Nombre de la Ubicación</Label>
                        <Input id="name" name="name" defaultValue={editingCity?.name} placeholder="Ej: Lima" required />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="coordinates">Coordenadas Centrales</Label>
                        <div className="h-64 w-full rounded-md border">
                           {isMapLoading ? (
                                <Skeleton className="w-full h-full" />
                            ) : (
                                <LocationPickerMap
                                    onLocationSelect={(coords) => setCityCoordinates(coords)}
                                    initialCenter={editingCity?.coordinates}
                                    initialMarker={editingCity?.coordinates}
                                />
                            )}
                        </div>
                         <p className="text-xs text-muted-foreground">Haz clic en el mapa para marcar el centro de la ubicación.</p>
                    </div>
                </div>
                 <DialogFooter>
                    <Button type="button" variant="ghost" onClick={() => setCityFormOpen(false)}>Cancelar</Button>
                    <Button type="submit">Guardar Ubicación</Button>
                </DialogFooter>
             </form>
          </DialogContent>
      </Dialog>
      
      <AlertDialog open={!!cityToDelete} onOpenChange={() => setCityToDelete(null)}>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás seguro de que quieres eliminar esta ubicación?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Se eliminará la ubicación "{cityToDelete?.name}" y todas sus zonas asociadas permanentemente.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={handleDeleteCityConfirm} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
    </div>
  );
}

export default function AdminDeliveryZonesPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminDeliveryZonesPageContent />
    </AuthGuard>
  );
}
