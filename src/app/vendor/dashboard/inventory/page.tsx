
"use client";

import React, { useState, useEffect, Suspense } from "react";
import Image from "next/image";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { AuthGuard } from "@/components/AuthGuard";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { MoreHorizontal, PlusCircle, Edit, Trash, Store, ArrowLeft, XCircle, DollarSign, ListPlus, Tag, Star, Ruler } from "lucide-react";
import { Product, DrinkOption, VendorProductCategory, ProductVariant } from "@/lib/placeholder-data";
import type { Vendor } from "@/lib/types";
import { useAppData } from "@/hooks/use-app-data";
import { uploadImageFromBase64 } from "@/lib/upload";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { Switch } from "@/components/ui/switch";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Separator } from "@/components/ui/separator";

const ManageDrinkOptions = ({ drinkOptions, setDrinkOptions, currencySymbol }: { drinkOptions: DrinkOption[], setDrinkOptions: React.Dispatch<React.SetStateAction<DrinkOption[]>>, currencySymbol: string }) => {

    const handleAddDrink = () => {
        setDrinkOptions([...drinkOptions, { name: '', price: 0, costPrice: 0 }]);
    };

    const handleRemoveDrink = (index: number) => {
        setDrinkOptions(drinkOptions.filter((_, i) => i !== index));
    };

    const handleDrinkChange = (index: number, field: keyof DrinkOption, value: string | number) => {
        const newDrinks = [...drinkOptions];
        if ((field === 'price' || field === 'costPrice') && typeof value === 'string') {
            newDrinks[index][field] = parseFloat(value) || 0;
        } else {
            newDrinks[index][field] = value as string & number;
        }
        setDrinkOptions(newDrinks);
    };

    return (
        <Card className="p-4 space-y-4">
            <CardTitle className="text-base">Gestionar Opciones de Bebidas</CardTitle>
            <div className="space-y-3 max-h-48 overflow-y-auto pr-2">
                {drinkOptions.map((drink, index) => (
                    <div key={index} className="grid grid-cols-[1fr_auto_auto_auto] items-center gap-2">
                        <Input
                            placeholder="Nombre de la Bebida"
                            value={drink.name}
                            onChange={(e) => handleDrinkChange(index, 'name', e.target.value)}
                        />
                         <div className="relative">
                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{currencySymbol}</span>
                            <Input
                                type="number"
                                placeholder="Precio Venta"
                                value={drink.price}
                                step="0.01"
                                onChange={(e) => handleDrinkChange(index, 'price', e.target.value)}
                                className="pl-7 w-32"
                            />
                        </div>
                        <div className="relative">
                           <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{currencySymbol}</span>
                           <Input
                                type="number"
                                placeholder="Costo"
                                value={drink.costPrice}
                                step="0.01"
                                onChange={(e) => handleDrinkChange(index, 'costPrice', e.target.value)}
                                className="pl-7 w-24"
                            />
                        </div>
                        <Button type="button" variant="ghost" size="icon" onClick={() => handleRemoveDrink(index)} className="text-destructive">
                            <XCircle className="h-4 w-4"/>
                        </Button>
                    </div>
                ))}
            </div>
            <Button type="button" variant="outline" size="sm" onClick={handleAddDrink}>Añadir Bebida</Button>
        </Card>
    );
};

// Componente para gestionar variantes/tamaños del producto
const ManageVariantOptions = ({
    variants,
    setVariants,
    currencySymbol,
    hasVariants,
    setHasVariants
}: {
    variants: ProductVariant[],
    setVariants: React.Dispatch<React.SetStateAction<ProductVariant[]>>,
    currencySymbol: string,
    hasVariants: boolean,
    setHasVariants: React.Dispatch<React.SetStateAction<boolean>>
}) => {

    const handleAddVariant = () => {
        const newVariant: ProductVariant = {
            id: `var${Date.now()}`,
            name: '',
            price: 0,
            costPrice: 0,
            isDefault: variants.length === 0 // Primera variante es default
        };
        setVariants([...variants, newVariant]);
    };

    const handleRemoveVariant = (index: number) => {
        const newVariants = variants.filter((_, i) => i !== index);
        // Si eliminamos el default, hacer la primera variante el nuevo default
        if (newVariants.length > 0 && !newVariants.some(v => v.isDefault)) {
            newVariants[0].isDefault = true;
        }
        setVariants(newVariants);
    };

    const handleVariantChange = (index: number, field: keyof ProductVariant, value: string | number | boolean) => {
        const newVariants = [...variants];
        if ((field === 'price' || field === 'costPrice') && typeof value === 'string') {
            (newVariants[index] as any)[field] = parseFloat(value) || 0;
        } else if (field === 'isDefault' && value === true) {
            // Solo una variante puede ser default
            newVariants.forEach((v, i) => {
                v.isDefault = i === index;
            });
        } else {
            (newVariants[index] as any)[field] = value;
        }
        setVariants(newVariants);
    };

    return (
        <Card className="p-4 space-y-4">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <Ruler className="h-4 w-4 text-primary" />
                    <CardTitle className="text-base">Variantes / Tamaños</CardTitle>
                </div>
                <div className="flex items-center gap-2">
                    <Label htmlFor="hasVariants" className="text-sm">Activar</Label>
                    <Switch
                        id="hasVariants"
                        checked={hasVariants}
                        onCheckedChange={(checked) => {
                            setHasVariants(checked);
                            if (checked && variants.length === 0) {
                                handleAddVariant();
                            }
                        }}
                    />
                </div>
            </div>

            {hasVariants && (
                <>
                    <p className="text-xs text-muted-foreground">
                        Define diferentes tamaños o presentaciones con sus precios. Ejemplo: Personal, Mediana, Familiar.
                    </p>
                    <div className="space-y-3 max-h-64 overflow-y-auto pr-2">
                        {variants.map((variant, index) => (
                            <div key={variant.id} className="p-3 border rounded-lg space-y-2">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Input
                                            placeholder="Ej: Personal, Mediana..."
                                            value={variant.name}
                                            onChange={(e) => handleVariantChange(index, 'name', e.target.value)}
                                            className="w-40"
                                        />
                                        <div className="flex items-center gap-1">
                                            <input
                                                type="radio"
                                                id={`default-${variant.id}`}
                                                name="defaultVariant"
                                                checked={variant.isDefault}
                                                onChange={() => handleVariantChange(index, 'isDefault', true)}
                                                className="h-4 w-4"
                                            />
                                            <Label htmlFor={`default-${variant.id}`} className="text-xs text-muted-foreground">
                                                Por defecto
                                            </Label>
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        onClick={() => handleRemoveVariant(index)}
                                        className="text-destructive h-8 w-8"
                                        disabled={variants.length === 1}
                                    >
                                        <XCircle className="h-4 w-4"/>
                                    </Button>
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <div className="relative">
                                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">{currencySymbol}</span>
                                        <Input
                                            type="number"
                                            placeholder="Precio Venta"
                                            value={variant.price}
                                            step="0.01"
                                            onChange={(e) => handleVariantChange(index, 'price', e.target.value)}
                                            className="pl-7 text-sm"
                                        />
                                    </div>
                                    <div className="relative">
                                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">{currencySymbol}</span>
                                        <Input
                                            type="number"
                                            placeholder="Costo"
                                            value={variant.costPrice}
                                            step="0.01"
                                            onChange={(e) => handleVariantChange(index, 'costPrice', e.target.value)}
                                            className="pl-7 text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                    <Button type="button" variant="outline" size="sm" onClick={handleAddVariant}>
                        <PlusCircle className="mr-2 h-4 w-4" /> Añadir Variante
                    </Button>
                </>
            )}
        </Card>
    );
};


function VendorInventoryPageContent() {
  const { vendors, appSettings, saveVendor, currentUser } = useAppData();
  const { toast } = useToast();
  const searchParams = useSearchParams();

  // Admin puede acceder con ?vendorId=xxx
  const vendorIdFromUrl = searchParams.get('vendorId');

  // 🔒 SEGURIDAD: Cuando el rol es vendor, currentUser YA es el objeto vendor
  const vendor = vendorIdFromUrl
    ? vendors.find(v => v.id === vendorIdFromUrl)
    : (currentUser?.role === 'vendor' ? (currentUser as Vendor) : undefined);


  const [isAddProductDialogOpen, setAddProductDialogOpen] = useState(false);
  const [isEditProductDialogOpen, setEditProductDialogOpen] = useState(false);
  const [isEditStoreDialogOpen, setEditStoreDialogOpen] = useState(false);
  const [isCategoryDialogOpen, setCategoryDialogOpen] = useState(false);
  
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [editingCategory, setEditingCategory] = useState<VendorProductCategory | null>(null);
  const [productToDelete, setProductToDelete] = useState<Product | null>(null);
  const [categoryToDelete, setCategoryToDelete] = useState<VendorProductCategory | null>(null);

  const [addProductPreview, setAddProductPreview] = useState<string | null>(null);
  const [editProductPreview, setEditProductPreview] = useState<string | null>(null);
  const [editLogoPreview, setEditLogoPreview] = useState<string | null>(null);
  const [editBannerPreview, setEditBannerPreview] = useState<string | null>(null);

  const [currentDrinkOptions, setCurrentDrinkOptions] = useState<DrinkOption[]>([]);
  const [currentVariants, setCurrentVariants] = useState<ProductVariant[]>([]);
  const [hasVariants, setHasVariants] = useState(false);

  const products = vendor?.products || [];

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

  const [isUploading, setIsUploading] = useState(false);

  const handleAddProduct = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!vendor) return;

    const formData = new FormData(event.currentTarget);
    const productId = `p${Date.now()}`;

    // Subir imagen al servidor si hay una nueva
    let imageUrl = 'https://placehold.co/400x300.png';
    if (addProductPreview && addProductPreview.startsWith('data:')) {
      setIsUploading(true);
      toast({ title: "Subiendo imagen...", description: "Por favor espera mientras se sube la imagen." });

      const uploadResult = await uploadImageFromBase64(
        addProductPreview,
        `vendors/${vendor.id}/products`
      );

      if (uploadResult.success && uploadResult.url) {
        imageUrl = uploadResult.url;
      } else {
        toast({ title: "Error", description: "No se pudo subir la imagen. Se usará una imagen por defecto.", variant: "destructive" });
      }
      setIsUploading(false);
    } else if (addProductPreview) {
      imageUrl = addProductPreview;
    }

    // Filtrar variantes válidas (con nombre)
    const validVariants = currentVariants.filter(v => v.name.trim() !== '');

    const newProduct: Product = {
        id: productId,
        vendorId: vendor?.id,
        name: formData.get('name') as string,
        description: formData.get('description') as string,
        price: parseFloat(formData.get('price') as string),
        costPrice: parseFloat(formData.get('costPrice') as string),
        offerPrice: parseFloat(formData.get('offerPrice') as string) || undefined,
        isOffer: formData.get('isOffer') === 'on',
        isFeatured: formData.get('isFeatured') === 'on',
        stock: parseInt(formData.get('stock') as string, 10),
        imageUrl: imageUrl,
        vendorCategoryId: formData.get('vendorCategoryId') as string,
        options: {
            packagingFee: parseFloat(formData.get('packagingFee') as string) || undefined,
            cutleryPrice: parseFloat(formData.get('cutleryPrice') as string) || undefined,
            cutleryCostPrice: parseFloat(formData.get('cutleryCostPrice') as string) || undefined,
            drinks: currentDrinkOptions.filter(d => d.name.trim() !== ''),
        },
        hasVariants: hasVariants && validVariants.length > 0,
        variants: hasVariants ? validVariants : undefined,
    };

    const updatedVendor = { ...vendor, products: [...vendor.products, newProduct] };
    saveVendor(updatedVendor);

    setAddProductDialogOpen(false);
    setAddProductPreview(null);
    setCurrentDrinkOptions([]);
    setCurrentVariants([]);
    setHasVariants(false);
    toast({ title: "Producto Añadido", description: `${newProduct.name} ha sido añadido a tu inventario.` });
  };
  
  const handleEditProductClick = (product: Product) => {
    setEditingProduct(product);
    setEditProductPreview(product.imageUrl);
    setCurrentDrinkOptions(product.options?.drinks || []);
    setCurrentVariants(product.variants || []);
    setHasVariants(product.hasVariants || false);
    setEditProductDialogOpen(true);
  };

  const handleUpdateProduct = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingProduct || !vendor) return;

    const formData = new FormData(event.currentTarget);

    // Subir imagen al servidor si hay una nueva (base64)
    let imageUrl = editingProduct.imageUrl;
    if (editProductPreview && editProductPreview.startsWith('data:')) {
      setIsUploading(true);
      toast({ title: "Subiendo imagen...", description: "Por favor espera mientras se sube la imagen." });

      const uploadResult = await uploadImageFromBase64(
        editProductPreview,
        `vendors/${vendor.id}/products`
      );

      if (uploadResult.success && uploadResult.url) {
        imageUrl = uploadResult.url;
      } else {
        toast({ title: "Error", description: "No se pudo subir la imagen. Se mantendrá la imagen anterior.", variant: "destructive" });
      }
      setIsUploading(false);
    } else if (editProductPreview) {
      imageUrl = editProductPreview;
    }

    // Filtrar variantes válidas (con nombre)
    const validVariants = currentVariants.filter(v => v.name.trim() !== '');

    const updatedProduct: Product = {
        ...editingProduct,
        name: formData.get('name') as string,
        description: formData.get('description') as string,
        price: parseFloat(formData.get('price') as string),
        costPrice: parseFloat(formData.get('costPrice') as string),
        offerPrice: parseFloat(formData.get('offerPrice') as string) || undefined,
        isOffer: formData.get('isOffer') === 'on',
        isFeatured: formData.get('isFeatured') === 'on',
        stock: parseInt(formData.get('stock') as string, 10),
        imageUrl: imageUrl,
        vendorCategoryId: formData.get('vendorCategoryId') as string,
        options: {
            packagingFee: parseFloat(formData.get('packagingFee') as string) || undefined,
            cutleryPrice: parseFloat(formData.get('cutleryPrice') as string) || undefined,
            cutleryCostPrice: parseFloat(formData.get('cutleryCostPrice') as string) || undefined,
            drinks: currentDrinkOptions.filter(d => d.name.trim() !== ''),
        },
        hasVariants: hasVariants && validVariants.length > 0,
        variants: hasVariants ? validVariants : undefined,
    };

    const updatedVendor = {
        ...vendor,
        products: vendor.products.map(p => p.id === updatedProduct.id ? updatedProduct : p)
    };
    saveVendor(updatedVendor);

    setEditProductDialogOpen(false);
    setEditingProduct(null);
    setCurrentDrinkOptions([]);
    setCurrentVariants([]);
    setHasVariants(false);
    toast({ title: "Producto Actualizado", description: `${updatedProduct.name} ha sido actualizado.` });
  };
  
  const handleOfferToggle = (productId: string, isOffer: boolean) => {
    if (!vendor) return;
    const updatedProducts = vendor.products.map(p => 
        p.id === productId ? { ...p, isOffer } : p
    );
    saveVendor({ ...vendor, products: updatedProducts });
  };

  const handleFeatureToggle = (productId: string, isFeatured: boolean) => {
    if (!vendor) return;
    const updatedProducts = vendor.products.map(p => 
        p.id === productId ? { ...p, isFeatured } : p
    );
    saveVendor({ ...vendor, products: updatedProducts });
  };


  const handleDeleteClick = (product: Product) => {
    setProductToDelete(product);
  };

  const confirmDelete = () => {
    if (!productToDelete || !vendor) return;
    
    const updatedVendor = {
        ...vendor,
        products: vendor.products.filter(p => p.id !== productToDelete.id)
    };
    saveVendor(updatedVendor);

    toast({ title: "Producto Eliminado", description: `${productToDelete.name} ha sido eliminado.`, variant: "destructive" });
    setProductToDelete(null);
  };

  const handleEditStoreClick = () => {
      if (vendor) {
          setEditLogoPreview(vendor.imageUrl);
          setEditBannerPreview(vendor.bannerUrl || null);
          setEditStoreDialogOpen(true);
      }
  };

  const handleUpdateStore = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!vendor) return;

    const formData = new FormData(event.currentTarget);

    // Subir logo al servidor si hay uno nuevo (base64)
    let logoUrl = vendor.imageUrl;
    if (editLogoPreview && editLogoPreview.startsWith('data:')) {
      setIsUploading(true);
      toast({ title: "Subiendo logo...", description: "Por favor espera." });

      const uploadResult = await uploadImageFromBase64(
        editLogoPreview,
        `vendors/${vendor.id}`
      );

      if (uploadResult.success && uploadResult.url) {
        logoUrl = uploadResult.url;
      } else {
        toast({ title: "Error", description: "No se pudo subir el logo.", variant: "destructive" });
      }
    } else if (editLogoPreview) {
      logoUrl = editLogoPreview;
    }

    // Subir banner al servidor si hay uno nuevo (base64)
    let bannerUrl = vendor.bannerUrl;
    if (editBannerPreview && editBannerPreview.startsWith('data:')) {
      toast({ title: "Subiendo banner...", description: "Por favor espera." });

      const uploadResult = await uploadImageFromBase64(
        editBannerPreview,
        `vendors/${vendor.id}`
      );

      if (uploadResult.success && uploadResult.url) {
        bannerUrl = uploadResult.url;
      } else {
        toast({ title: "Error", description: "No se pudo subir el banner.", variant: "destructive" });
      }
    } else if (editBannerPreview) {
      bannerUrl = editBannerPreview;
    }

    setIsUploading(false);

    const updatedVendor: Vendor = {
        ...vendor,
        name: formData.get('name') as string,
        description: formData.get('description') as string,
        imageUrl: logoUrl,
        bannerUrl: bannerUrl,
    };

    saveVendor(updatedVendor);
    setEditStoreDialogOpen(false);
    toast({ title: "Perfil de Tienda Actualizado", description: "La información de tu tienda ha sido actualizada." });
  };

  const handleSaveCategory = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!vendor) return;

    const formData = new FormData(event.currentTarget);
    const categoryName = formData.get('categoryName') as string;

    if (editingCategory) {
        // Update existing category
        const updatedCategories = vendor.productCategories.map(cat => 
            cat.id === editingCategory.id ? { ...cat, name: categoryName } : cat
        );
        saveVendor({ ...vendor, productCategories: updatedCategories });
        toast({ title: 'Categoría Actualizada', description: `El nombre de la categoría ha sido actualizado a ${categoryName}.` });
    } else {
        // Add new category
        const newCategory: VendorProductCategory = { id: `vc${Date.now()}`, name: categoryName };
        const existingCategories = vendor.productCategories || [];
        saveVendor({ ...vendor, productCategories: [...existingCategories, newCategory] });
        toast({ title: 'Categoría Añadida', description: `${categoryName} ha sido añadida.` });
    }
    setCategoryDialogOpen(false);
    setEditingCategory(null);
  };

  const handleEditCategoryClick = (category: VendorProductCategory) => {
    setEditingCategory(category);
    setCategoryDialogOpen(true);
  };

  const handleDeleteCategoryClick = (category: VendorProductCategory) => {
    setCategoryToDelete(category);
  };
  
  const confirmDeleteCategory = () => {
    if (!categoryToDelete || !vendor) return;

    // Filter out the category to delete
    const updatedCategories = vendor.productCategories.filter(cat => cat.id !== categoryToDelete.id);

    // Un-assign category from products that were using it
    const updatedProducts = vendor.products.map(prod => {
        if (prod.vendorCategoryId === categoryToDelete.id) {
            return { ...prod, vendorCategoryId: undefined };
        }
        return prod;
    });

    saveVendor({ ...vendor, productCategories: updatedCategories, products: updatedProducts });
    
    toast({ title: "Categoría Eliminada", description: `${categoryToDelete.name} ha sido eliminada.`, variant: "destructive" });
    setCategoryToDelete(null);
  };


  const getStockBadge = (stock: number) => {
    if (stock === 0) {
        return <Badge variant="destructive">Agotado</Badge>;
    }
    if (stock <= 10) {
        return <Badge variant="secondary" className="bg-yellow-400 text-yellow-900 hover:bg-yellow-400/80">Poco Stock</Badge>;
    }
    return <Badge variant="secondary" className="bg-green-500 text-white hover:bg-green-500/80">En Stock</Badge>;
  }

  // Validar estado del vendor (Inactivo o Rechazado no pueden gestionar inventario)
  if (vendor && (vendor.status === 'Inactivo' || vendor.status === 'Rechazado')) {
    return (
      <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
        <Card>
          <CardContent className="py-12 flex flex-col items-center justify-center text-center">
            <Store className="h-12 w-12 text-destructive mb-4"/>
            <h3 className="text-xl font-semibold text-destructive">
              {vendor.status === 'Rechazado' ? 'Cuenta Rechazada' : 'Cuenta Inactiva'}
            </h3>
            <p className="text-muted-foreground mt-2">
              {vendor.status === 'Rechazado'
                ? 'Tu solicitud de tienda ha sido rechazada. No puedes gestionar el inventario.'
                : 'Tu tienda se encuentra inactiva. No puedes gestionar el inventario en este momento.'}
            </p>
            <p className="text-sm text-muted-foreground mt-1">Contacta con soporte para más información.</p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">

      {vendor ? (
        <div className="space-y-4 sm:space-y-6">
        <Card>
          <CardHeader className="px-3 sm:px-4 md:px-6">
            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <CardTitle className="text-lg sm:text-xl">Gestionar Categorías de Productos</CardTitle>
                 <Dialog open={isCategoryDialogOpen} onOpenChange={setCategoryDialogOpen}>
                    <DialogTrigger asChild>
                        <Button size="sm" onClick={() => setEditingCategory(null)} className="h-9 sm:h-10 text-sm sm:text-base w-full sm:w-auto">
                            <PlusCircle className="mr-2 h-4 w-4" /> Añadir Categoría
                        </Button>
                    </DialogTrigger>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>{editingCategory ? 'Editar' : 'Añadir'} Categoría</DialogTitle>
                        </DialogHeader>
                        <form onSubmit={handleSaveCategory}>
                            <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                                <Label htmlFor="categoryName">Nombre de la Categoría</Label>
                                <Input id="categoryName" name="categoryName" defaultValue={editingCategory?.name ?? ''} required />
                            </div>
                            <DialogFooter>
                                <Button type="button" variant="ghost" onClick={() => setCategoryDialogOpen(false)}>Cancelar</Button>
                                <Button type="submit">Guardar</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
             <CardDescription className="text-sm">Organiza tus productos en categorías para que los clientes los encuentren fácilmente.</CardDescription>
          </CardHeader>
          <CardContent className="px-3 sm:px-4 md:px-6">
            {vendor?.productCategories?.length > 0 ? (
                <div className="flex flex-wrap gap-2">
                    {vendor.productCategories.map(cat => (
                        <div key={cat.id} className="group relative">
                            <Badge variant="outline" className="text-sm sm:text-base pr-8 py-1">{cat.name}</Badge>
                            <div className="absolute top-1/2 right-1 -translate-y-1/2 flex items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button variant="ghost" size="icon" className="h-5 w-5" onClick={() => handleEditCategoryClick(cat)}><Edit className="h-3 w-3"/></Button>
                                <Button variant="ghost" size="icon" className="h-5 w-5 text-destructive" onClick={() => handleDeleteCategoryClick(cat)}><Trash className="h-3 w-3"/></Button>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <p className="text-sm text-muted-foreground">Aún no has creado ninguna categoría.</p>
            )}
          </CardContent>
        </Card>

        <Card>
            <CardHeader className="px-3 sm:px-4 md:px-6">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                    <div>
                        <CardTitle className="text-lg sm:text-xl">Inventario de: {vendor.name}</CardTitle>
                        <CardDescription className="text-sm">Añade, edita y actualiza el stock de tus productos.</CardDescription>
                    </div>
                    <div className="flex items-center gap-2 w-full sm:w-auto">
                        <Dialog open={isAddProductDialogOpen} onOpenChange={(isOpen) => { setAddProductDialogOpen(isOpen); if (!isOpen) { setCurrentDrinkOptions([]); setCurrentVariants([]); setHasVariants(false); } }}>
                            <DialogTrigger asChild>
                                <Button onClick={() => setAddProductPreview(null)} className="h-9 sm:h-10 text-sm sm:text-base flex-1 sm:flex-none">
                                    <PlusCircle className="mr-2 h-4 w-4" /> Añadir Producto
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="sm:max-w-lg">
                                <DialogHeader>
                                    <DialogTitle>Añadir Nuevo Producto</DialogTitle>
                                    <DialogDescription>
                                        Completa los detalles de tu nuevo producto. Haz clic en guardar cuando termines.
                                    </DialogDescription>
                                </DialogHeader>
                                <form onSubmit={handleAddProduct}>
                                    <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-4">
                                        <div className="space-y-1">
                                            <Label htmlFor="name">Nombre</Label>
                                            <Input id="name" name="name" required />
                                        </div>
                                         <div className="space-y-1">
                                            <Label htmlFor="vendorCategoryId">Categoría del Producto</Label>
                                            <Select name="vendorCategoryId" required>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Selecciona una categoría" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {vendor?.productCategories?.map(cat => (
                                                        <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-1">
                                            <Label htmlFor="description">Descripción</Label>
                                            <Textarea id="description" name="description" required rows={3} />
                                        </div>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="space-y-1">
                                                <Label htmlFor="price">Precio de Venta</Label>
                                                <div className="relative">
                                                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                                  <Input id="price" name="price" type="number" step="0.01" required className="pl-7"/>
                                                </div>
                                            </div>
                                             <div className="space-y-1">
                                                <Label htmlFor="costPrice">Precio de Costo</Label>
                                                <div className="relative">
                                                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                                  <Input id="costPrice" name="costPrice" type="number" step="0.01" required className="pl-7"/>
                                                </div>
                                            </div>
                                            <div className="space-y-1">
                                                <Label htmlFor="offerPrice">Precio de Oferta (Opcional)</Label>
                                                <div className="relative">
                                                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                                  <Input id="offerPrice" name="offerPrice" type="number" step="0.01" placeholder="Ej. 9.99" className="pl-7"/>
                                                </div>
                                            </div>
                                            <div className="space-y-1">
                                                <Label htmlFor="stock">Inventario (Stock)</Label>
                                                <Input id="stock" name="stock" type="number" defaultValue="10" required />
                                            </div>
                                        </div>

                                        {/* Variantes / Tamaños - Visible al inicio */}
                                        <ManageVariantOptions
                                            variants={currentVariants}
                                            setVariants={setCurrentVariants}
                                            currencySymbol={appSettings.currencySymbol}
                                            hasVariants={hasVariants}
                                            setHasVariants={setHasVariants}
                                        />

                                        <div className="flex items-center space-x-2">
                                            <Switch id="isOffer" name="isOffer" />
                                            <Label htmlFor="isOffer">Marcar como producto en oferta</Label>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Switch id="isFeatured" name="isFeatured" />
                                            <Label htmlFor="isFeatured">Marcar como producto destacado</Label>
                                        </div>
                                         <div className="space-y-1">
                                            <Label htmlFor="image">Imagen del Producto</Label>
                                            {addProductPreview && <Image src={addProductPreview} alt="Vista previa del producto" width={100} height={100} className="rounded-md object-cover my-2" />}
                                            <Input id="image" name="image" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setAddProductPreview)} />
                                        </div>

                                        <Card className="p-4 space-y-4">
                                            <CardTitle className="text-base">Opciones y Tarifas</CardTitle>
                                             <div className="grid grid-cols-2 gap-4">
                                                <div className="space-y-1">
                                                    <Label htmlFor="packagingFee">Costo de Empaque (Opcional)</Label>
                                                    <div className="relative">
                                                      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                                      <Input id="packagingFee" name="packagingFee" type="number" step="0.01" defaultValue="0" className="pl-7"/>
                                                    </div>
                                                </div>
                                                <div className="space-y-1">
                                                    <Label htmlFor="cutleryPrice">Precio Venta Cubiertos</Label>
                                                    <div className="relative">
                                                      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                                      <Input id="cutleryPrice" name="cutleryPrice" type="number" step="0.01" defaultValue="0" className="pl-7"/>
                                                    </div>
                                                </div>
                                                 <div className="space-y-1">
                                                    <Label htmlFor="cutleryCostPrice">Costo Cubiertos</Label>
                                                    <div className="relative">
                                                      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                                      <Input id="cutleryCostPrice" name="cutleryCostPrice" type="number" step="0.01" defaultValue="0" className="pl-7"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <p className="text-xs text-muted-foreground">Dejar en 0 para no ofrecer.</p>
                                        </Card>
                                        
                                        <ManageDrinkOptions drinkOptions={currentDrinkOptions} setDrinkOptions={setCurrentDrinkOptions} currencySymbol={appSettings.currencySymbol} />

                                    </div>
                                    <DialogFooter className="pt-4">
                                        <Button type="button" variant="ghost" onClick={() => setAddProductDialogOpen(false)} disabled={isUploading}>Cancelar</Button>
                                        <Button type="submit" disabled={isUploading}>{isUploading ? "Subiendo..." : "Guardar Producto"}</Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="px-3 sm:px-4 md:px-6">
                {/* Mobile View: Cards */}
                <div className="grid gap-3 sm:gap-4 md:hidden">
                    {products.map((product) => (
                        <Card key={product.id} className="flex flex-col">
                            <CardHeader className="flex flex-row items-start gap-4 p-4">
                                <Image
                                    alt={product.name}
                                    className="aspect-square rounded-md object-cover"
                                    height="80"
                                    src={product.imageUrl}
                                    width="80"
                                    data-ai-hint="food item"
                                />
                                <div className="flex-grow">
                                    <p className="font-semibold">{product.name}</p>
                                    <p className="text-xs text-muted-foreground">{vendor.productCategories?.find(c => c.id === product.vendorCategoryId)?.name || 'Sin categoría'}</p>
                                    <div className="mt-2">{getStockBadge(product.stock)}</div>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button size="icon" variant="ghost"><MoreHorizontal /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem onClick={() => handleEditProductClick(product)}><Edit className="mr-2 h-4 w-4" /> Editar</DropdownMenuItem>
                                        <DropdownMenuItem className="text-destructive focus:text-destructive focus:bg-destructive/10" onClick={() => handleDeleteClick(product)}><Trash className="mr-2 h-4 w-4" /> Eliminar</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </CardHeader>
                            <CardContent className="p-4 pt-0 flex flex-col gap-3">
                               <div className="flex justify-between items-center text-sm">
                                  <div className="flex items-center gap-2">
                                     <Switch
                                            checked={product.isOffer}
                                            onCheckedChange={(checked) => handleOfferToggle(product.id, checked)}
                                            aria-label="Marcar como oferta"
                                            id={`offer-switch-mobile-${product.id}`}
                                        />
                                        <Label htmlFor={`offer-switch-mobile-${product.id}`} className="text-xs">En Oferta</Label>
                                  </div>
                                   <div className="flex items-center gap-2">
                                     <Switch
                                            checked={product.isFeatured}
                                            onCheckedChange={(checked) => handleFeatureToggle(product.id, checked)}
                                            aria-label="Marcar como destacado"
                                            id={`feature-switch-mobile-${product.id}`}
                                        />
                                        <Label htmlFor={`feature-switch-mobile-${product.id}`} className="text-xs">Destacado</Label>
                                  </div>
                               </div>
                               <div className="text-right">
                                 <span className="text-muted-foreground mr-2 text-sm">Precio:</span>
                                 <span className="font-bold">{appSettings.currencySymbol}{product.price.toFixed(2)}</span>
                               </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Desktop View: Table */}
                <div className="hidden md:block overflow-x-auto">
                    <Table>
                        <TableHeader>
                        <TableRow>
                            <TableHead className="w-[100px]">Imagen</TableHead>
                            <TableHead>Nombre</TableHead>
                            <TableHead>Categoría</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead>En Oferta</TableHead>
                            <TableHead>Destacado</TableHead>
                            <TableHead className="text-right">Inventario</TableHead>
                            <TableHead className="text-right">Venta</TableHead>
                            <TableHead>Acciones</TableHead>
                        </TableRow>
                        </TableHeader>
                        <TableBody>
                        {products.map((product) => (
                            <TableRow key={product.id}>
                            <TableCell>
                                <Image
                                alt={product.name}
                                className="aspect-square rounded-md object-cover"
                                height="64"
                                src={product.imageUrl}
                                width="64"
                                data-ai-hint="food item"
                                />
                            </TableCell>
                            <TableCell className="font-medium">{product.name}</TableCell>
                            <TableCell><Badge variant="outline">{vendor.productCategories?.find(c => c.id === product.vendorCategoryId)?.name || 'Sin categoría'}</Badge></TableCell>
                            <TableCell>{getStockBadge(product.stock)}</TableCell>
                            <TableCell>
                                <Switch
                                    checked={product.isOffer}
                                    onCheckedChange={(checked) => handleOfferToggle(product.id, checked)}
                                    aria-label="Marcar como oferta"
                                />
                            </TableCell>
                            <TableCell>
                                <Switch
                                    checked={product.isFeatured}
                                    onCheckedChange={(checked) => handleFeatureToggle(product.id, checked)}
                                    aria-label="Marcar como destacado"
                                />
                            </TableCell>
                            <TableCell className="text-right">{product.stock}</TableCell>
                            <TableCell className="text-right">{appSettings.currencySymbol}{product.price.toFixed(2)}</TableCell>
                            <TableCell>
                                <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button aria-haspopup="true" size="icon" variant="ghost">
                                    <MoreHorizontal className="h-4 w-4" />
                                    <span className="sr-only">Alternar menú</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>Acciones</DropdownMenuLabel>
                                    <DropdownMenuItem onClick={() => handleEditProductClick(product)}>
                                        <Edit className="mr-2 h-4 w-4" /> Editar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem className="text-destructive focus:text-destructive focus:bg-destructive/10" onClick={() => handleDeleteClick(product)}>
                                        <Trash className="mr-2 h-4 w-4" /> Eliminar
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                            </TableRow>
                        ))}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
        </div>
      ) : (
        <Card>
            <CardContent className="py-12 flex flex-col items-center justify-center text-center">
                <Store className="h-12 w-12 text-muted-foreground mb-4"/>
                <h3 className="text-xl font-semibold">Tienda no encontrada</h3>
                <p className="text-muted-foreground mt-2">No se pudo cargar la información de tu tienda. Contacta con soporte.</p>
            </CardContent>
        </Card>
      )}

      {/* Edit Product Dialog */}
      <Dialog open={isEditProductDialogOpen} onOpenChange={(isOpen) => { setEditProductDialogOpen(isOpen); if (!isOpen) { setCurrentDrinkOptions([]); setCurrentVariants([]); setHasVariants(false); } }}>
        <DialogContent className="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Editar Producto</DialogTitle>
            <DialogDescription>
              Actualiza los detalles de tu producto. Haz clic en guardar para aplicar los cambios.
            </DialogDescription>
          </DialogHeader>
          {editingProduct && vendor &&(
            <form onSubmit={handleUpdateProduct}>
              <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-4">
                <div className="space-y-1">
                    <Label htmlFor="edit-name">Nombre</Label>
                    <Input id="edit-name" name="name" defaultValue={editingProduct.name} required />
                </div>
                 <div className="space-y-1">
                    <Label htmlFor="edit-vendorCategoryId">Categoría del Producto</Label>
                    <Select name="vendorCategoryId" defaultValue={editingProduct.vendorCategoryId} required>
                        <SelectTrigger>
                            <SelectValue placeholder="Selecciona una categoría" />
                        </SelectTrigger>
                        <SelectContent>
                            {vendor?.productCategories?.map(cat => (
                                <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-1">
                    <Label htmlFor="edit-description">Descripción</Label>
                    <Textarea id="edit-description" name="description" defaultValue={editingProduct.description} required rows={3}/>
                </div>
                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-1">
                        <Label htmlFor="edit-price">Precio de Venta</Label>
                        <div className="relative">
                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                            <Input id="edit-price" name="price" type="number" step="0.01" defaultValue={editingProduct.price} required className="pl-7"/>
                        </div>
                    </div>
                     <div className="space-y-1">
                        <Label htmlFor="edit-costPrice">Precio de Costo</Label>
                        <div className="relative">
                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                            <Input id="edit-costPrice" name="costPrice" type="number" step="0.01" defaultValue={editingProduct.costPrice} required className="pl-7"/>
                        </div>
                    </div>
                     <div className="space-y-1">
                        <Label htmlFor="edit-offerPrice">Precio de Oferta (Opcional)</Label>
                        <div className="relative">
                          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                          <Input id="edit-offerPrice" name="offerPrice" type="number" step="0.01" defaultValue={editingProduct.offerPrice} placeholder="Ej. 9.99" className="pl-7"/>
                        </div>
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="edit-stock">Inventario (Stock)</Label>
                        <Input id="edit-stock" name="stock" type="number" defaultValue={editingProduct.stock} required />
                    </div>
                </div>
                {/* Variantes / Tamaños - Visible al inicio */}
                <ManageVariantOptions
                    variants={currentVariants}
                    setVariants={setCurrentVariants}
                    currencySymbol={appSettings.currencySymbol}
                    hasVariants={hasVariants}
                    setHasVariants={setHasVariants}
                />

                <div className="flex items-center space-x-2">
                    <Switch id="edit-isOffer" name="isOffer" defaultChecked={editingProduct.isOffer} />
                    <Label htmlFor="edit-isOffer">Marcar como producto en oferta</Label>
                </div>
                 <div className="flex items-center space-x-2">
                    <Switch id="edit-isFeatured" name="isFeatured" defaultChecked={editingProduct.isFeatured} />
                    <Label htmlFor="edit-isFeatured">Marcar como producto destacado</Label>
                </div>
                <div className="space-y-1">
                    <Label htmlFor="edit-image">Imagen del Producto</Label>
                    {editProductPreview && <Image src={editProductPreview} alt="Vista previa del producto" width={100} height={100} className="rounded-md object-cover my-2" />}
                    <Input id="edit-image" name="image" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setEditProductPreview)} />
                </div>
                <Card className="p-4 space-y-4">
                    <CardTitle className="text-base">Opciones y Tarifas</CardTitle>
                    <div className="grid grid-cols-2 gap-4">
                         <div className="space-y-1">
                            <Label htmlFor="edit-packagingFee">Costo de Empaque (Opcional)</Label>
                            <div className="relative">
                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                <Input id="edit-packagingFee" name="packagingFee" type="number" step="0.01" defaultValue={editingProduct.options?.packagingFee || 0} className="pl-7"/>
                            </div>
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="edit-cutleryPrice">Precio Venta Cubiertos</Label>
                            <div className="relative">
                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                <Input id="edit-cutleryPrice" name="cutleryPrice" type="number" step="0.01" defaultValue={editingProduct.options?.cutleryPrice || 0} className="pl-7"/>
                            </div>
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="edit-cutleryCostPrice">Costo Cubiertos</Label>
                            <div className="relative">
                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                                <Input id="edit-cutleryCostPrice" name="cutleryCostPrice" type="number" step="0.01" defaultValue={editingProduct.options?.cutleryCostPrice || 0} className="pl-7"/>
                            </div>
                        </div>
                    </div>
                    <p className="text-xs text-muted-foreground">Dejar en 0 para no ofrecer.</p>
                </Card>

                <ManageDrinkOptions drinkOptions={currentDrinkOptions} setDrinkOptions={setCurrentDrinkOptions} currencySymbol={appSettings.currencySymbol} />

              </div>
              <DialogFooter className="pt-4">
                <Button type="button" variant="ghost" onClick={() => setEditProductDialogOpen(false)} disabled={isUploading}>Cancelar</Button>
                <Button type="submit" disabled={isUploading}>{isUploading ? "Subiendo..." : "Guardar Cambios"}</Button>
              </DialogFooter>
            </form>
          )}
        </DialogContent>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <AlertDialog open={!!productToDelete} onOpenChange={() => setProductToDelete(null)}>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>¿Estás realmente seguro?</AlertDialogTitle>
                <AlertDialogDescription>
                    Esta acción no se puede deshacer. Esto eliminará permanentemente el producto
                    de tus listados.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel onClick={() => setProductToDelete(null)}>Cancelar</AlertDialogCancel>
                <AlertDialogAction onClick={confirmDelete} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      {/* Edit Store Dialog */}
        <Dialog open={isEditStoreDialogOpen} onOpenChange={setEditStoreDialogOpen}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Editar Perfil de la Tienda</DialogTitle>
                    <DialogDescription>Actualiza el nombre, la descripción y las imágenes de tu tienda.</DialogDescription>
                </DialogHeader>
                {vendor && (
                <form onSubmit={handleUpdateStore}>
                    <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                        <div className="space-y-1">
                            <Label htmlFor="store-name">Nombre del Negocio</Label>
                            <Input id="store-name" name="name" defaultValue={vendor.name} required />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="store-description">Descripción</Label>
                            <Textarea id="store-description" name="description" defaultValue={vendor.description} required />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="store-logo">Logo</Label>
                            {editLogoPreview && <Image src={editLogoPreview} alt="Vista previa del logo" width={64} height={64} className="rounded-md object-cover my-2" />}
                            <Input id="store-logo" name="logo" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setEditLogoPreview)} />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="store-banner">Banner</Label>
                            {editBannerPreview && <Image src={editBannerPreview} alt="Vista previa del banner" width={200} height={100} className="rounded-md object-cover my-2" />}
                            <Input id="store-banner" name="banner" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setEditBannerPreview)} />
                        </div>
                    </div>
                    <DialogFooter className="pt-4">
                        <Button type="button" variant="ghost" onClick={() => setEditStoreDialogOpen(false)} disabled={isUploading}>Cancelar</Button>
                        <Button type="submit" disabled={isUploading}>{isUploading ? "Subiendo..." : "Guardar Cambios"}</Button>
                    </DialogFooter>
                </form>
                )}
            </DialogContent>
        </Dialog>

        {/* Delete Category Confirmation */}
        <AlertDialog open={!!categoryToDelete} onOpenChange={() => setCategoryToDelete(null)}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Estás seguro?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Esta acción eliminará la categoría "{categoryToDelete?.name}". Los productos en esta categoría no serán eliminados, pero quedarán sin categoría.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction onClick={confirmDeleteCategory} className="bg-destructive hover:bg-destructive/90">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
  );
}

export default function VendorInventoryPage() {
    return (
        <AuthGuard requireAuth={true} requireRole={["vendor", "admin"]} redirectTo="/vendor/login">
            <Suspense fallback={<div>Cargando inventario...</div>}>
                <VendorInventoryPageContent />
            </Suspense>
        </AuthGuard>
    )
}
