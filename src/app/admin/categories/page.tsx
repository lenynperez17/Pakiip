
"use client";

import { useState } from "react";
import Image from "next/image";
import { Button } from "@/components/ui/button";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { MoreHorizontal, PlusCircle, Trash, Edit } from "lucide-react";
import { Category } from "@/lib/placeholder-data";
import { useAppData } from "@/hooks/use-app-data";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { AuthGuard } from "@/components/AuthGuard";

function AdminCategoriesPageContent() {
  const { categories, saveCategory, deleteCategory } = useAppData();
  const [isAddDialogOpen, setAddDialogOpen] = useState(false);
  const [isEditDialogOpen, setEditDialogOpen] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [categoryToDelete, setCategoryToDelete] = useState<Category | null>(null);
  const [addPreview, setAddPreview] = useState<string | null>(null);
  const [editPreview, setEditPreview] = useState<string | null>(null);
  const { toast } = useToast();

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

  const handleAddCategory = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const newCategory: Category = {
        id: `cat${Date.now()}`,
        name: formData.get('name') as string,
        imageUrl: addPreview || 'https://placehold.co/100x80.png',
        imageHint: (formData.get('name') as string).toLowerCase()
    };
    saveCategory(newCategory);
    setAddDialogOpen(false);
    setAddPreview(null);
    toast({ title: "Categoría Añadida", description: `${newCategory.name} ha sido añadida.` });
  };
  
  const handleEditClick = (category: Category) => {
    setEditingCategory(category);
    setEditPreview(category.imageUrl);
    setEditDialogOpen(true);
  };

  const handleUpdateCategory = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingCategory) return;

    const formData = new FormData(event.currentTarget);
    const updatedCategory: Category = {
        ...editingCategory,
        name: formData.get('name') as string,
        imageUrl: editPreview || editingCategory.imageUrl,
        imageHint: (formData.get('name') as string).toLowerCase()
    };
    
    saveCategory(updatedCategory);
    setEditDialogOpen(false);
    setEditingCategory(null);
    setEditPreview(null);
    toast({ title: "Categoría Actualizada", description: `${updatedCategory.name} ha sido actualizada.` });
  };

  const handleDeleteConfirm = () => {
    if (!categoryToDelete) return;
    deleteCategory(categoryToDelete.id);
    toast({ title: "Categoría Eliminada", description: `${categoryToDelete.name} ha sido eliminada.`, variant: "destructive" });
    setCategoryToDelete(null);
  };

  const renderActions = (category: Category) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild><Button size="icon" variant="ghost"><MoreHorizontal /></Button></DropdownMenuTrigger>
        <DropdownMenuContent>
            <DropdownMenuItem onClick={() => handleEditClick(category)}><Edit className="mr-2 h-4 w-4" /> Editar</DropdownMenuItem>
            <DropdownMenuItem onClick={() => setCategoryToDelete(category)} className="text-destructive focus:text-destructive focus:bg-destructive/10"><Trash className="mr-2 h-4 w-4" /> Eliminar</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
  );

  return (
    <>
    <Card>
        <CardHeader>
        <div className="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:items-center sm:justify-between">
            <div>
                <CardTitle className="text-xl sm:text-2xl">Gestionar Categorías</CardTitle>
                <CardDescription className="text-sm sm:text-base">Añade, edita o elimina categorías de la plataforma.</CardDescription>
            </div>
            <Dialog open={isAddDialogOpen} onOpenChange={setAddDialogOpen}>
                <DialogTrigger asChild>
                    <Button onClick={() => setAddPreview(null)} className="h-9 sm:h-10 text-sm sm:text-base px-3 sm:px-4 w-full sm:w-auto">
                        <PlusCircle className="mr-2 h-4 w-4" /> Añadir Categoría
                    </Button>
                </DialogTrigger>
                <DialogContent className="sm:max-w-md w-[95vw] sm:w-full">
                    <DialogHeader>
                        <DialogTitle className="text-lg sm:text-xl">Añadir Nueva Categoría</DialogTitle>
                        <DialogDescription className="text-sm sm:text-base">Completa los detalles de la nueva categoría.</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleAddCategory}>
                        <div className="grid gap-3 sm:gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                            <div className="space-y-2">
                                <Label htmlFor="name" className="text-sm sm:text-base">Nombre de la Categoría</Label>
                                <Input id="name" name="name" required className="h-9 sm:h-10 text-sm sm:text-base" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="image" className="text-sm sm:text-base">Imagen</Label>
                                {addPreview && <Image src={addPreview} alt="Vista previa" width={100} height={80} className="rounded-md object-cover my-2" sizes="(max-width: 640px) 80px, 100px" />}
                                <Input id="image" name="image" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setAddPreview)} className="h-9 sm:h-10 text-sm sm:text-base" />
                            </div>
                        </div>
                        <DialogFooter className="pt-4 flex-col sm:flex-row gap-2">
                            <Button type="button" variant="ghost" onClick={() => setAddDialogOpen(false)} className="h-9 sm:h-10 text-sm sm:text-base w-full sm:w-auto">Cancelar</Button>
                            <Button type="submit" className="h-9 sm:h-10 text-sm sm:text-base w-full sm:w-auto">Guardar Categoría</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
        </CardHeader>
        <CardContent className="px-3 sm:px-6">
            {/* Mobile View: Cards */}
            <div className="grid gap-3 sm:gap-4 md:hidden">
                {categories.map((category) => (
                    <Card key={category.id} className="p-3 sm:p-4 flex justify-between items-center">
                        <div className="flex items-center gap-3 sm:gap-4">
                             <Image alt={category.name} className="rounded-md object-cover aspect-square" height="40" src={category.imageUrl} width="40" data-ai-hint={category.imageHint} sizes="40px" />
                             <p className="font-medium text-sm sm:text-base">{category.name}</p>
                        </div>
                        {renderActions(category)}
                    </Card>
                ))}
            </div>

            {/* Desktop View: Table */}
            <div className="hidden md:block">
                <div className="overflow-x-auto text-sm md:text-base">
                    <Table>
                        <TableHeader>
                        <TableRow>
                            <TableHead className="w-[80px]">Imagen</TableHead>
                            <TableHead>Nombre</TableHead>
                            <TableHead className="text-right">Acciones</TableHead>
                        </TableRow>
                        </TableHeader>
                        <TableBody>
                        {categories.map((category) => (
                            <TableRow key={category.id}>
                            <TableCell>
                                <Image alt={category.name} className="rounded-md object-cover aspect-square" height="40" src={category.imageUrl} width="40" data-ai-hint={category.imageHint} />
                            </TableCell>
                            <TableCell className="font-medium whitespace-nowrap">{category.name}</TableCell>
                            <TableCell className="text-right">
                               {renderActions(category)}
                            </TableCell>
                            </TableRow>
                        ))}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </CardContent>
    </Card>

    <Dialog open={isEditDialogOpen} onOpenChange={setEditDialogOpen}>
        <DialogContent className="sm:max-w-md w-[95vw] sm:w-full">
            <DialogHeader>
                <DialogTitle className="text-lg sm:text-xl">Editar Categoría</DialogTitle>
                <DialogDescription className="text-sm sm:text-base">Actualiza los detalles de la categoría.</DialogDescription>
            </DialogHeader>
            {editingCategory && (
            <form onSubmit={handleUpdateCategory}>
                <div className="grid gap-3 sm:gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                    <div className="space-y-2">
                        <Label htmlFor="edit-name" className="text-sm sm:text-base">Nombre de la Categoría</Label>
                        <Input id="edit-name" name="name" defaultValue={editingCategory.name} required className="h-9 sm:h-10 text-sm sm:text-base" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="edit-image" className="text-sm sm:text-base">Imagen</Label>
                        {editPreview && <Image src={editPreview} alt="Vista previa" width={100} height={80} className="rounded-md object-cover my-2" sizes="(max-width: 640px) 80px, 100px" />}
                        <Input id="edit-image" name="image" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setEditPreview)} className="h-9 sm:h-10 text-sm sm:text-base" />
                    </div>
                </div>
                <DialogFooter className="pt-4 flex-col sm:flex-row gap-2">
                    <Button type="button" variant="ghost" onClick={() => setEditDialogOpen(false)} className="h-9 sm:h-10 text-sm sm:text-base w-full sm:w-auto">Cancelar</Button>
                    <Button type="submit" className="h-9 sm:h-10 text-sm sm:text-base w-full sm:w-auto">Guardar Cambios</Button>
                </DialogFooter>
            </form>
            )}
        </DialogContent>
    </Dialog>
    
    <AlertDialog open={!!categoryToDelete} onOpenChange={() => setCategoryToDelete(null)}>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás seguro de que quieres eliminar esta categoría?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Se eliminará la categoría "{categoryToDelete?.name}" permanentemente.
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

export default function AdminCategoriesPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminCategoriesPageContent />
    </AuthGuard>
  );
}
