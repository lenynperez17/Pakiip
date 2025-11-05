

"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import type { AppSettings, BankAccount, QRPayment, PaymentGatewaySettings, VerificationMethods, Collaborator, PromotionalBanner, City } from "@/lib/placeholder-data";
import { DollarSign, Percent, Save, Star, Globe, Image as ImageIcon, Truck, PlusCircle, Trash, MoreHorizontal, CreditCard, ShieldCheck, FileText, Wallet, Users, PartyPopper, Megaphone, Link as LinkIcon, Loader2 } from "lucide-react";
import { Separator } from "@/components/ui/separator";
import { Switch } from "@/components/ui/switch";
import { useAppData } from "@/hooks/use-app-data";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Textarea } from "@/components/ui/textarea";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { Badge } from "@/components/ui/badge";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { initializeFirebase, uploadImage } from "@/lib/firebase";
import { AuthGuard } from "@/components/AuthGuard";


type VerificationInstructions = {
    type: string;
    host: string;
    value: string;
};

const peruDepartments = [
    "Amazonas", "Áncash", "Apurímac", "Arequipa", "Ayacucho", "Cajamarca", "Callao",
    "Cusco", "Huancavelica", "Huánuco", "Ica", "Junín", "La Libertad", "Lambayeque",
    "Lima", "Loreto", "Madre de Dios", "Moquegua", "Pasco", "Piura", "Puno",
    "San Martín", "Tacna", "Tumbes", "Ucayali"
];

const availablePermissions = [
    { id: 'manage_orders', label: 'Gestionar Pedidos' },
    { id: 'manage_stores', label: 'Gestionar Tiendas' },
    { id: 'manage_drivers', label: 'Gestionar Repartidores' },
    { id: 'manage_users', label: 'Gestionar Usuarios' },
    { id: 'view_reports', label: 'Ver Reportes de Ventas' },
    { id: 'manage_settings', label: 'Gestionar Configuración General' },
];


function AdminSettingsPageContent() {
  const { appSettings, saveSettings, collaborators, saveCollaborator, deleteCollaborator, cities } = useAppData();
  const [settings, setSettings] = useState<AppSettings>(appSettings);
  const { toast } = useToast();
  const [verificationInstructions, setVerificationInstructions] = useState<VerificationInstructions | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [heroImagePreview, setHeroImagePreview] = useState<string | null>(null);
  const [welcomeImagePreview, setWelcomeImagePreview] = useState<string | null>(null);
  const [driverWelcomeImagePreview, setDriverWelcomeImagePreview] = useState<string | null>(null);
  const [loginBackgroundImagePreview, setLoginBackgroundImagePreview] = useState<string | null>(null);

  // Estados para trackear archivos originales antes de subir a Firebase
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [heroImageFile, setHeroImageFile] = useState<File | null>(null);
  const [welcomeImageFile, setWelcomeImageFile] = useState<File | null>(null);
  const [driverWelcomeImageFile, setDriverWelcomeImageFile] = useState<File | null>(null);
  const [loginBackgroundImageFile, setLoginBackgroundImageFile] = useState<File | null>(null);

  // Estado para mostrar progreso de subida
  const [isUploading, setIsUploading] = useState(false);

  const [isBankAccountDialogOpen, setBankAccountDialogOpen] = useState(false);
  const [editingBankAccount, setEditingBankAccount] = useState<BankAccount | null>(null);
  const [isQrPaymentDialogOpen, setQrPaymentDialogOpen] = useState(false);
  const [qrImagePreview, setQrImagePreview] = useState<string | null>(null);
  const [qrImageFile, setQrImageFile] = useState<File | null>(null);

  const [isTeamDialogOpen, setTeamDialogOpen] = useState(false);
  const [editingCollaborator, setEditingCollaborator] = useState<Collaborator | null>(null);
  const [collaboratorPermissions, setCollaboratorPermissions] = useState<string[]>([]);

  const [bannerPreviews, setBannerPreviews] = useState<Record<string, string>>({});


  useEffect(() => {
    setSettings(appSettings);
    if (appSettings.customDomain) {
      setVerificationInstructions({
        type: 'TXT',
        host: '@',
        value: `mercado-listo-verification=${btoa(appSettings.customDomain).slice(0, 20)}`
      });
    }
  }, [appSettings]);
  
    const handleBannerFileChange = (e: React.ChangeEvent<HTMLInputElement>, bannerId: string, type: 'promo' | 'announcement') => {
        const file = e.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                const result = reader.result as string;
                setBannerPreviews(prev => ({ ...prev, [bannerId]: result }));
                
                const bannerKey = type === 'promo' ? 'promotionalBanners' : 'announcementBanners';
                setSettings(prev => {
                    const updatedBanners = prev[bannerKey].map(banner => 
                        banner.id === bannerId ? { ...banner, imageUrl: result } : banner
                    );
                    return { ...prev, [bannerKey]: updatedBanners };
                });
            };
            reader.readAsDataURL(file);
        }
    };
    
    const handleBannerChange = (bannerId: string, field: keyof PromotionalBanner, value: string, type: 'promo' | 'announcement') => {
        const bannerKey = type === 'promo' ? 'promotionalBanners' : 'announcementBanners';
        setSettings(prev => {
            const updatedBanners = prev[bannerKey].map(banner => 
                banner.id === bannerId ? { ...banner, [field]: value } : banner
            );
            return { ...prev, [bannerKey]: updatedBanners };
        });
    };
    
    const handleBannerLocationsChange = (bannerId: string, locations: string[], type: 'promo' | 'announcement') => {
        const bannerKey = type === 'promo' ? 'promotionalBanners' : 'announcementBanners';
         setSettings(prev => {
            const updatedBanners = prev[bannerKey].map(banner => 
                banner.id === bannerId ? { ...banner, locations: locations } : banner
            );
            return { ...prev, [bannerKey]: updatedBanners };
        });
    };


    const handleAddBanner = (type: 'promo' | 'announcement') => {
        const newBanner: PromotionalBanner = {
            id: `${type}${Date.now()}`,
            title: 'Nuevo Banner',
            description: 'Descripción corta',
            link: '/',
            imageUrl: 'https://placehold.co/600x400.png',
            imageHint: 'banner',
            locations: []
        };
        const bannerKey = type === 'promo' ? 'promotionalBanners' : 'announcementBanners';
        setSettings(prev => ({
            ...prev,
            [bannerKey]: [...(prev[bannerKey] || []), newBanner]
        }));
    };
    
    const handleDeleteBanner = (bannerId: string, type: 'promo' | 'announcement') => {
        const bannerKey = type === 'promo' ? 'promotionalBanners' : 'announcementBanners';
        setSettings(prev => ({
            ...prev,
            [bannerKey]: prev[bannerKey].filter(banner => banner.id !== bannerId)
        }));
    };


  const handleEditCollaboratorClick = (collaborator: Collaborator) => {
    setEditingCollaborator(collaborator);
    setCollaboratorPermissions(collaborator.permissions);
    setTeamDialogOpen(true);
  };
  
  const handlePermissionChange = (permissionId: string, checked: boolean) => {
    setCollaboratorPermissions(prev =>
      checked ? [...prev, permissionId] : prev.filter(p => p !== permissionId)
    );
  };
  
  const handleSaveCollaborator = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const collaboratorData: Collaborator = {
        id: editingCollaborator?.id || `collab${Date.now()}`,
        name: formData.get('name') as string,
        email: formData.get('email') as string,
        role: formData.get('role') as string,
        permissions: collaboratorPermissions
    };

    saveCollaborator(collaboratorData);
    setTeamDialogOpen(false);
    setEditingCollaborator(null);
    setCollaboratorPermissions([]);
    toast({ title: "Colaborador Guardado", description: `Se han guardado los datos de ${collaboratorData.name}.` });
  };
  
  const handleDeleteCollaborator = (collaboratorId: string) => {
      deleteCollaborator(collaboratorId);
      toast({ title: "Colaborador Eliminado", variant: "destructive" });
  };


  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type } = e.target;
    setSettings(prev => ({ 
        ...prev, 
        [name]: type === 'number' ? parseFloat(value) || 0 : value 
    }));
  };
  
  const handleShippingChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
     setSettings(prev => ({ 
        ...prev, 
        shipping: {
            ...prev.shipping,
            [name]: parseFloat(value) || 0,
        }
    }));
  }

  const handlePaymentGatewayChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
     setSettings(prev => ({ 
        ...prev, 
        paymentMethods: {
            ...prev.paymentMethods,
            gateway: {
                ...prev.paymentMethods.gateway,
                [name]: value
            }
        }
    }));
  }

  const handleFileChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    setPreview: (url: string | null) => void,
    setFile: (file: File | null) => void
  ) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const result = reader.result as string;
        setPreview(result);
      };
      reader.readAsDataURL(file);
      setFile(file); // Guardar el archivo original para subirlo después
    }
  };
  
  const handleVerificationMethodChange = (checked: boolean, method: keyof VerificationMethods) => {
    setSettings(prev => ({
      ...prev,
      verificationMethods: {
          ...prev.verificationMethods,
          [method]: checked,
      }
    }));
  };

  const handleTaxExemptRegionChange = (department: string, isChecked: boolean) => {
      setSettings(prev => {
          const currentRegions = prev.taxExemptRegions || [];
          if (isChecked) {
              return { ...prev, taxExemptRegions: [...currentRegions, department] };
          } else {
              return { ...prev, taxExemptRegions: currentRegions.filter(d => d !== department) };
          }
      });
  };


  const handleSwitchChange = (checked: boolean, name: string) => {
    if (name.startsWith('paymentMethods.')) {
        const key = name.split('.')[1] as keyof AppSettings['paymentMethods'];
        setSettings(prev => ({
            ...prev,
            paymentMethods: {
                ...prev.paymentMethods,
                [key]: checked,
            }
        }));
    } else {
        setSettings(prev => ({
            ...prev,
            [name]: checked
        }));
    }
};

  const handleSaveSettings = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsUploading(true);

    try {
      // Inicializar Firebase con la configuración del usuario
      if (settings.firebaseConfig?.apiKey && settings.firebaseConfig?.projectId) {
        try {
          initializeFirebase({
            apiKey: settings.firebaseConfig.apiKey,
            authDomain: settings.firebaseConfig.authDomain || '',
            projectId: settings.firebaseConfig.projectId,
            storageBucket: `${settings.firebaseConfig.projectId}.appspot.com`
          });
        } catch (error) {
          console.error('Error al inicializar Firebase:', error);
          toast({
            variant: "destructive",
            title: "Error de Firebase",
            description: "No se pudo inicializar Firebase. Verifica tu configuración o las imágenes se guardarán en Base64.",
          });
        }
      }

      let finalSettings = { ...settings };

      // Función helper para subir imagen a Firebase o usar Base64
      const uploadOrUsePreview = async (
        file: File | null,
        preview: string | null,
        path: string
      ): Promise<string | undefined> => {
        if (!file) return undefined;

        // Intentar subir a Firebase si está configurado
        if (settings.firebaseConfig?.apiKey && settings.firebaseConfig?.projectId) {
          try {
            const result = await uploadImage(file, path);
            if (result.success && result.url) {
              return result.url;
            }
          } catch (error) {
            console.error('Error al subir a Firebase:', error);
          }
        }

        // Si falla o Firebase no está configurado, usar Base64
        return preview || undefined;
      };

      // Subir cada imagen a Firebase (o usar Base64 como fallback)
      const [logoUrl, heroUrl, welcomeUrl, driverWelcomeUrl, loginBgUrl] = await Promise.all([
        uploadOrUsePreview(logoFile, logoPreview, `settings/logo_${Date.now()}.jpg`),
        uploadOrUsePreview(heroImageFile, heroImagePreview, `settings/hero_${Date.now()}.jpg`),
        uploadOrUsePreview(welcomeImageFile, welcomeImagePreview, `settings/welcome_${Date.now()}.jpg`),
        uploadOrUsePreview(driverWelcomeImageFile, driverWelcomeImagePreview, `settings/driver_welcome_${Date.now()}.jpg`),
        uploadOrUsePreview(loginBackgroundImageFile, loginBackgroundImagePreview, `settings/login_bg_${Date.now()}.jpg`)
      ]);

      // Actualizar finalSettings con las URLs de Firebase
      if (logoUrl) finalSettings.logoUrl = logoUrl;
      if (heroUrl) finalSettings.heroImageUrl = heroUrl;
      if (welcomeUrl) finalSettings.welcomeImageUrl = welcomeUrl;
      if (driverWelcomeUrl) finalSettings.driverWelcomeImageUrl = driverWelcomeUrl;
      if (loginBgUrl) finalSettings.loginBackgroundImageUrl = loginBgUrl;

      // Limpiar estados de archivos después de subir
      setLogoFile(null);
      setHeroImageFile(null);
      setWelcomeImageFile(null);
      setDriverWelcomeImageFile(null);
      setLoginBackgroundImageFile(null);

      saveSettings(finalSettings);
      toast({
        title: "Configuración Guardada",
        description: settings.firebaseConfig?.apiKey
          ? "Imágenes subidas a Firebase Storage correctamente."
          : "Los cambios se han guardado. Configura Firebase para persistencia en la nube.",
      });

      if (settings.customDomain) {
        setVerificationInstructions({
          type: 'TXT',
          host: '@',
          value: `mercado-listo-verification=${btoa(settings.customDomain).slice(0, 20)}`
        });
      } else {
        setVerificationInstructions(null);
      }
    } catch (error) {
      console.error('Error al guardar configuración:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: "Hubo un problema al guardar la configuración.",
      });
    } finally {
      setIsUploading(false);
    }
  };

  const handleSaveBankAccount = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const bankAccountData = {
        id: editingBankAccount?.id || `bank${Date.now()}`,
        bankName: formData.get('bankName') as string,
        accountHolder: formData.get('accountHolder') as string,
        accountNumber: formData.get('accountNumber') as string,
        country: formData.get('country') as string,
    };

    let updatedAccounts;
    if (editingBankAccount) {
        updatedAccounts = appSettings.paymentMethods.bankAccounts.map(b => b.id === editingBankAccount.id ? bankAccountData : b);
    } else {
        updatedAccounts = [...appSettings.paymentMethods.bankAccounts, bankAccountData];
    }

    const updatedSettings = { ...appSettings, paymentMethods: { ...appSettings.paymentMethods, bankAccounts: updatedAccounts }};
    saveSettings(updatedSettings);
    setBankAccountDialogOpen(false);
    setEditingBankAccount(null);
    toast({ title: "Cuenta Guardada", description: "La cuenta bancaria se ha guardado correctamente." });
  };

  const handleDeleteBankAccount = (id: string) => {
      const updatedAccounts = appSettings.paymentMethods.bankAccounts.filter(b => b.id !== id);
      const updatedSettings = { ...appSettings, paymentMethods: { ...appSettings.paymentMethods, bankAccounts: updatedAccounts }};
      saveSettings(updatedSettings);
      toast({ title: "Cuenta Eliminada", description: "La cuenta bancaria se ha eliminado correctamente.", variant: "destructive" });
  };

  const handleSaveQrPayment = async (event: React.FormEvent<HTMLFormElement>) => {
      event.preventDefault();
      const formData = new FormData(event.currentTarget);

      const qrName = formData.get('qrName') as string;
      const qrInstructions = formData.get('qrInstructions') as string;

      // Subir imagen a Firebase si hay un archivo seleccionado
      let qrImageUrl = '';
      if (qrImageFile) {
          try {
              setIsUploading(true);

              // Generar nombre único para el archivo
              const timestamp = Date.now();
              const fileExtension = qrImageFile.name.split('.').pop() || 'jpg';
              const fileName = `qr_payment_${timestamp}.${fileExtension}`;
              const filePath = `qr-payments/${fileName}`;

              const result = await uploadImage(qrImageFile, filePath);

              if (result.success && result.url) {
                  qrImageUrl = result.url;
              } else {
                  throw new Error(result.error || 'Error al subir imagen');
              }
          } catch (error) {
              console.error('Error al subir imagen QR:', error);
              toast({ variant: 'destructive', title: 'Error', description: 'No se pudo subir la imagen QR. Intenta de nuevo.' });
              setIsUploading(false);
              return;
          } finally {
              setIsUploading(false);
          }
      }

      const newQrPayment: QRPayment = {
          id: `qr${Date.now()}`,
          name: qrName,
          qrImageUrl: qrImageUrl,
          instructions: qrInstructions,
      };

      if (!newQrPayment.qrImageUrl) {
          toast({ variant: 'destructive', title: 'Error', description: 'Por favor, sube una imagen para el código QR.' });
          return;
      }

      const updatedQrPayments = [...appSettings.paymentMethods.qrPayments, newQrPayment];
      const updatedSettings = { ...appSettings, paymentMethods: { ...appSettings.paymentMethods, qrPayments: updatedQrPayments }};
      saveSettings(updatedSettings);

      setQrPaymentDialogOpen(false);
      setQrImagePreview(null);
      setQrImageFile(null);

      toast({ title: 'Éxito', description: 'Método de pago QR guardado correctamente.' });
  };

  const handleDeleteQrPayment = (id: string) => {
      const updatedQrPayments = appSettings.paymentMethods.qrPayments.filter(p => p.id !== id);
      const updatedSettings = { ...appSettings, paymentMethods: { ...appSettings.paymentMethods, qrPayments: updatedQrPayments }};
      saveSettings(updatedSettings);
      toast({ title: "Pago QR Eliminado", description: "El método de pago QR se ha eliminado correctamente.", variant: "destructive" });
  };

 const renderBannerManager = (type: 'promo' | 'announcement') => {
    const banners = (type === 'promo' ? settings.promotionalBanners : settings.announcementBanners) || [];
    
    return (
      <div className="space-y-4">
        {banners.map((banner, index) => (
          <Card key={banner.id} className="p-4 relative">
            <div className="flex justify-end mb-2 absolute top-2 right-2 z-10">
              <Button type="button" size="icon" variant="destructive" className="h-7 w-7" onClick={() => handleDeleteBanner(banner.id, type)}>
                <Trash className="h-4 w-4"/>
              </Button>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
              <div className="space-y-2">
                <Label htmlFor={`banner-image-${type}-${index}`}>Imagen del Banner</Label>
                <Image src={bannerPreviews[banner.id] || banner.imageUrl} alt={banner.title} width={300} height={150} className="rounded-md object-cover aspect-video bg-muted"/>
                <Input id={`banner-image-${type}-${index}`} type="file" accept="image/*" onChange={(e) => handleBannerFileChange(e, banner.id, type)} />
              </div>
              <div className="space-y-4">
                <div>
                  <Label htmlFor={`banner-title-${type}-${index}`}>Título</Label>
                  <Input id={`banner-title-${type}-${index}`} value={banner.title} onChange={(e) => handleBannerChange(banner.id, 'title', e.target.value, type)} />
                </div>
                <div>
                  <Label htmlFor={`banner-desc-${type}-${index}`}>Descripción</Label>
                  <Input id={`banner-desc-${type}-${index}`} value={banner.description} onChange={(e) => handleBannerChange(banner.id, 'description', e.target.value, type)} />
                </div>
                <div>
                  <Label htmlFor={`banner-link-${type}-${index}`}>Enlace</Label>
                  <Input id={`banner-link-${type}-${index}`} value={banner.link} onChange={(e) => handleBannerChange(banner.id, 'link', e.target.value, type)} />
                </div>
                 {type === 'announcement' && (
                    <div>
                        <Label>Ubicaciones</Label>
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button variant="outline" className="w-full justify-start font-normal">
                                    {banner.locations && banner.locations.length > 0 ? banner.locations.join(', ') : 'Global (todas las ciudades)'}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-auto p-0">
                                <div className="p-4 space-y-2">
                                     <div className="flex items-center space-x-2">
                                        <Checkbox 
                                            id={`all-cities-${banner.id}`}
                                            checked={!banner.locations || banner.locations.length === 0}
                                            onCheckedChange={(checked) => {
                                                if(checked) handleBannerLocationsChange(banner.id, [], type);
                                            }}
                                        />
                                        <Label htmlFor={`all-cities-${banner.id}`} className="font-normal">Global (todas las ciudades)</Label>
                                    </div>
                                    <Separator />
                                    <p className="text-xs text-muted-foreground">O selecciona ciudades específicas:</p>
                                    {cities.map((city) => (
                                        <div key={city.id} className="flex items-center space-x-2">
                                            <Checkbox
                                                id={`${banner.id}-${city.id}`}
                                                checked={banner.locations?.includes(city.name)}
                                                onCheckedChange={(checked) => {
                                                    const currentLocations = banner.locations || [];
                                                    const newLocations = checked
                                                        ? [...currentLocations, city.name]
                                                        : currentLocations.filter(loc => loc !== city.name);
                                                    handleBannerLocationsChange(banner.id, newLocations, type);
                                                }}
                                            />
                                            <Label htmlFor={`${banner.id}-${city.id}`} className="font-normal">{city.name}</Label>
                                        </div>
                                    ))}
                                </div>
                            </PopoverContent>
                        </Popover>
                    </div>
                )}
              </div>
            </div>
          </Card>
        ))}
        <div className="mt-6">
          <Button type="button" variant="outline" onClick={() => handleAddBanner(type)}>
            <PlusCircle className="mr-2 h-4 w-4"/> Añadir Nuevo Banner
          </Button>
        </div>
      </div>
    );
  };

  return (
    <div className="w-full space-y-6 px-2 sm:px-3 md:px-4 py-4">
        <form onSubmit={handleSaveSettings} className="space-y-6">
        <Tabs defaultValue="general">
            <TabsList className="w-full overflow-x-auto h-auto justify-start">
                <TabsTrigger value="general">General</TabsTrigger>
                <TabsTrigger value="marketing">Marketing</TabsTrigger>
                <TabsTrigger value="announcements">Anuncios</TabsTrigger>
                <TabsTrigger value="taxes">Impuestos</TabsTrigger>
                <TabsTrigger value="payments">Pagos</TabsTrigger>
                <TabsTrigger value="team">Equipo</TabsTrigger>
            </TabsList>
            <TabsContent value="general">
                <Card>
                <CardHeader>
                    <CardTitle>Configuración General</CardTitle>
                    <CardDescription>
                    Gestiona la configuración global de la aplicación.
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="appName">Nombre de la Aplicación</Label>
                        <Input
                        id="appName"
                        name="appName"
                        value={settings.appName}
                        onChange={handleInputChange}
                        />
                    </div>

                    <Separator />

                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Marca</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <Label htmlFor="logoUrl">Logo de la Plataforma</Label>
                                <div className="flex items-center gap-4">
                                    {(logoPreview || settings.logoUrl) && (
                                        <Image src={logoPreview || settings.logoUrl} alt="Logo preview" width={64} height={64} className="rounded-md object-contain bg-muted p-1" />
                                    )}
                                    <Input id="logoUrl" name="logoUrl" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setLogoPreview, setLogoFile)} className="max-w-xs" />
                                </div>
                                <p className="text-sm text-muted-foreground">Sube el logo que aparecerá en toda la aplicación.</p>
                            </div>
                             <div className="space-y-4">
                                <Label htmlFor="heroImageUrl">Imagen de Portada Principal</Label>
                                <div className="flex items-center gap-4">
                                    {(heroImagePreview || settings.heroImageUrl) && (
                                        <Image src={heroImagePreview || settings.heroImageUrl} alt="Hero image preview" width={128} height={72} className="rounded-md object-cover bg-muted p-1" />
                                    )}
                                    <Input id="heroImageUrl" name="heroImageUrl" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setHeroImagePreview, setHeroImageFile)} className="max-w-xs" />
                                </div>
                                <p className="text-sm text-muted-foreground">Sube una imagen de portada para la página de inicio (recomendado: 1200x400px).</p>
                            </div>
                            <div className="space-y-4">
                                <Label htmlFor="welcomeImageUrl">Imagen de Bienvenida (General)</Label>
                                <div className="flex items-center gap-4">
                                    {(welcomeImagePreview || settings.welcomeImageUrl) && (
                                        <Image src={welcomeImagePreview || settings.welcomeImageUrl} alt="Welcome character preview" width={72} height={72} className="rounded-md object-cover bg-muted p-1" />
                                    )}
                                    <Input id="welcomeImageUrl" name="welcomeImageUrl" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setWelcomeImagePreview, setWelcomeImageFile)} className="max-w-xs" />
                                </div>
                                <p className="text-sm text-muted-foreground">Sube la imagen de la caricatura que da la bienvenida a los usuarios.</p>
                            </div>
                            <div className="space-y-4">
                                <Label htmlFor="driverWelcomeImageUrl">Imagen de Bienvenida (Repartidores)</Label>
                                <div className="flex items-center gap-4">
                                    {(driverWelcomeImagePreview || settings.driverWelcomeImageUrl) && (
                                        <Image src={driverWelcomeImagePreview || settings.driverWelcomeImageUrl} alt="Driver welcome character preview" width={72} height={72} className="rounded-md object-cover bg-muted p-1" />
                                    )}
                                    <Input id="driverWelcomeImageUrl" name="driverWelcomeImageUrl" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setDriverWelcomeImagePreview, setDriverWelcomeImageFile)} className="max-w-xs" />
                                </div>
                                <p className="text-sm text-muted-foreground">Sube la imagen para la bienvenida exclusiva de los repartidores.</p>
                            </div>
                            <div className="space-y-4">
                                <Label htmlFor="loginBackgroundImageUrl">Imagen de Fondo del Login</Label>
                                <div className="flex items-center gap-4">
                                    {(loginBackgroundImagePreview || settings.loginBackgroundImageUrl) && (
                                        <Image src={loginBackgroundImagePreview || settings.loginBackgroundImageUrl} alt="Login background preview" width={128} height={72} className="rounded-md object-cover bg-muted p-1" />
                                    )}
                                    <Input id="loginBackgroundImageUrl" name="loginBackgroundImageUrl" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setLoginBackgroundImagePreview, setLoginBackgroundImageFile)} className="max-w-xs" />
                                </div>
                                <p className="text-sm text-muted-foreground">Sube una imagen de fondo para la página de login (recomendado: 1920x1080px).</p>
                            </div>
                        </div>
                    </div>
                    
                    <Separator />
                    
                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Configuración de Envío por Distancia</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-md">
                            <div className="space-y-2">
                            <Label htmlFor="shipping-baseRadiusKm">Radio Base (km)</Label>
                            <Input id="shipping-baseRadiusKm" name="baseRadiusKm" type="number" step="0.1" value={settings.shipping.baseRadiusKm} onChange={handleShippingChange} />
                            <p className="text-xs text-muted-foreground">Distancia cubierta por la tarifa base.</p>
                            </div>
                            <div className="space-y-2">
                            <Label htmlFor="shipping-baseFee">Tarifa Base</Label>
                            <Input id="shipping-baseFee" name="baseFee" type="number" step="0.01" value={settings.shipping.baseFee} onChange={handleShippingChange} />
                            <p className="text-xs text-muted-foreground">Costo de envío dentro del radio base.</p>
                            </div>
                            <div className="space-y-2">
                            <Label htmlFor="shipping-feePerKm">Costo Adicional por KM</Label>
                            <Input id="shipping-feePerKm" name="feePerKm" type="number" step="0.01" value={settings.shipping.feePerKm} onChange={handleShippingChange} />
                                <p className="text-xs text-muted-foreground">Costo por cada km fuera del radio base.</p>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="currencySymbol">Símbolo de Moneda</Label>
                            <div className="relative">
                                <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                id="currencySymbol"
                                name="currencySymbol"
                                value={settings.currencySymbol}
                                onChange={handleInputChange}
                                className="pl-9"
                                maxLength={2}
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                        <Label htmlFor="featuredStoreCost">Costo por Destacar</Label>
                        <div className="relative">
                            <Star className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                            id="featuredStoreCost"
                            name="featuredStoreCost"
                            type="number"
                            step="0.01"
                            value={settings.featuredStoreCost || 0}
                            onChange={handleInputChange}
                            className="pl-9"
                            />
                        </div>
                        </div>
                    </div>
                    
                    <Separator />

                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Seguridad y Autenticación</h3>
                        <div className="space-y-4 rounded-md border p-4">
                            <div className="space-y-2">
                                <Label>Métodos de Verificación Habilitados</Label>
                                <p className="text-sm text-muted-foreground">
                                    Selecciona los métodos que los usuarios podrán elegir para verificar su cuenta.
                                </p>
                                <div className="space-y-3 pt-2">
                                    <div className="flex flex-row items-center justify-between">
                                        <Label htmlFor="verification-email" className="font-normal">Verificación por Correo Electrónico</Label>
                                        <Switch id="verification-email" checked={settings.verificationMethods.email} onCheckedChange={(c) => handleVerificationMethodChange(c, 'email')} />
                                    </div>
                                    <div className="flex flex-row items-center justify-between">
                                        <Label htmlFor="verification-sms" className="font-normal">Verificación por SMS</Label>
                                        <Switch id="verification-sms" checked={settings.verificationMethods.sms} onCheckedChange={(c) => handleVerificationMethodChange(c, 'sms')} />
                                    </div>
                                    <div className="flex flex-row items-center justify-between">
                                        <Label htmlFor="verification-whatsapp" className="font-normal">Verificación por WhatsApp</Label>
                                        <Switch id="verification-whatsapp" checked={settings.verificationMethods.whatsapp} onCheckedChange={(c) => handleVerificationMethodChange(c, 'whatsapp')} />
                                    </div>
                                </div>
                            </div>
                            <Separator />
                            <div className="flex flex-row items-center justify-between">
                                <div className="space-y-0.5">
                                    <Label htmlFor="enablePasswordRecovery">Recuperación de Contraseña</Label>
                                    <p className="text-sm text-muted-foreground">
                                        Permitir a los usuarios restablecer su contraseña si la olvidan.
                                    </p>
                                </div>
                                <Switch
                                    id="enablePasswordRecovery"
                                    name="enablePasswordRecovery"
                                    checked={settings.enablePasswordRecovery}
                                    onCheckedChange={(checked) => handleSwitchChange(checked, 'enablePasswordRecovery')}
                                />
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Configuración de Interfaz</h3>
                        <div className="space-y-4 rounded-md border p-4">
                            <div className="flex flex-row items-center justify-between">
                                <div className="space-y-0.5">
                                    <Label htmlFor="hideFooter">Ocultar Footer</Label>
                                    <p className="text-sm text-muted-foreground">
                                        Ocultar el footer en todas las páginas de la aplicación.
                                    </p>
                                </div>
                                <Switch
                                    id="hideFooter"
                                    name="hideFooter"
                                    checked={settings.hideFooter || false}
                                    onCheckedChange={(checked) => handleSwitchChange(checked, 'hideFooter')}
                                />
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Configuración de Autenticación OAuth</h3>
                        <div className="space-y-4 rounded-md border p-4">
                            <div className="space-y-2">
                                <Label htmlFor="googleClientId">Google Client ID</Label>
                                <Input
                                    id="googleClientId"
                                    name="googleClientId"
                                    placeholder="ej. 123456789-abc.apps.googleusercontent.com"
                                    value={settings.googleClientId || ''}
                                    onChange={handleInputChange}
                                />
                                <p className="text-sm text-muted-foreground">
                                    ID de cliente de Google OAuth para autenticación con Google. Obtén uno en <a href="https://console.cloud.google.com" target="_blank" rel="noopener noreferrer" className="text-primary underline">Google Cloud Console</a>.
                                </p>
                            </div>
                            <Separator />
                            <div className="space-y-4">
                                <Label>Firebase Configuration (para autenticación por teléfono)</Label>
                                <p className="text-sm text-muted-foreground mb-2">
                                    Configura Firebase para habilitar autenticación por SMS/WhatsApp.
                                </p>
                                <div className="grid gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="firebaseApiKey">API Key</Label>
                                        <Input
                                            id="firebaseApiKey"
                                            placeholder="ej. AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
                                            value={settings.firebaseConfig?.apiKey || ''}
                                            onChange={(e) => setSettings(prev => ({
                                                ...prev,
                                                firebaseConfig: {
                                                    ...prev.firebaseConfig,
                                                    apiKey: e.target.value,
                                                    authDomain: prev.firebaseConfig?.authDomain || '',
                                                    projectId: prev.firebaseConfig?.projectId || ''
                                                }
                                            }))}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="firebaseAuthDomain">Auth Domain</Label>
                                        <Input
                                            id="firebaseAuthDomain"
                                            placeholder="ej. tu-proyecto.firebaseapp.com"
                                            value={settings.firebaseConfig?.authDomain || ''}
                                            onChange={(e) => setSettings(prev => ({
                                                ...prev,
                                                firebaseConfig: {
                                                    ...prev.firebaseConfig,
                                                    apiKey: prev.firebaseConfig?.apiKey || '',
                                                    authDomain: e.target.value,
                                                    projectId: prev.firebaseConfig?.projectId || ''
                                                }
                                            }))}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="firebaseProjectId">Project ID</Label>
                                        <Input
                                            id="firebaseProjectId"
                                            placeholder="ej. tu-proyecto-123"
                                            value={settings.firebaseConfig?.projectId || ''}
                                            onChange={(e) => setSettings(prev => ({
                                                ...prev,
                                                firebaseConfig: {
                                                    ...prev.firebaseConfig,
                                                    apiKey: prev.firebaseConfig?.apiKey || '',
                                                    authDomain: prev.firebaseConfig?.authDomain || '',
                                                    projectId: e.target.value
                                                }
                                            }))}
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Dominio Personalizado</h3>
                        <div className="space-y-2">
                            <Label htmlFor="customDomain">Nombre de Dominio</Label>
                            <div className="relative">
                                <Globe className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="customDomain"
                                    name="customDomain"
                                    placeholder="ej. tumarca.com"
                                    value={settings.customDomain || ''}
                                    onChange={handleInputChange}
                                    className="pl-9"
                                />
                            </div>
                            {verificationInstructions && (
                                <Card className="mt-4 bg-muted/50">
                                    <CardHeader>
                                        <CardTitle>Verifica la propiedad de tu dominio</CardTitle>
                                        <CardDescription>Añade el siguiente registro a la configuración DNS de tu proveedor de dominio para conectar <span className="font-bold">{settings.customDomain}</span>.</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-3 font-mono text-sm">
                                    <div>
                                            <p className="font-semibold">Tipo:</p>
                                            <p className="p-2 bg-background rounded-md">{verificationInstructions.type}</p>
                                    </div>
                                    <div>
                                            <p className="font-semibold">Host/Nombre:</p>
                                            <p className="p-2 bg-background rounded-md">{verificationInstructions.host}</p>
                                    </div>
                                    <div>
                                            <p className="font-semibold">Valor/Contenido:</p>
                                            <p className="p-2 bg-background rounded-md break-all">{verificationInstructions.value}</p>
                                    </div>
                                    <Button size="sm" variant="outline" className="font-sans mt-2" onClick={() => toast({ title: "Verificación Simulada", description: "En un entorno real, esto comprobaría el registro DNS."})}>Verificar ahora</Button>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </div>
                </CardContent>
                </Card>
            </TabsContent>
            <TabsContent value="marketing">
                <Card>
                    <CardHeader>
                        <CardTitle>Banners Promocionales (Página de Inicio)</CardTitle>
                        <CardDescription>Gestiona los banners promocionales principales de la página de inicio.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {renderBannerManager('promo')}
                    </CardContent>
                </Card>
            </TabsContent>
            <TabsContent value="announcements">
                 <Card>
                    <CardHeader>
                        <CardTitle>Banners de Anuncios</CardTitle>
                        <CardDescription>Gestiona las imágenes para la sección de anuncios en la página de inicio.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {renderBannerManager('announcement')}
                    </CardContent>
                </Card>
            </TabsContent>
            <TabsContent value="taxes">
                <Card>
                <CardHeader>
                    <CardTitle>Configuración de Impuestos</CardTitle>
                    <CardDescription>Gestiona el régimen fiscal y las exoneraciones de la plataforma.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="taxType">Tipo de Operación Tributaria</Label>
                        <Select
                            value={settings.taxType}
                            onValueChange={(value: AppSettings['taxType']) => setSettings(prev => ({...prev, taxType: value}))}
                        >
                            <SelectTrigger id="taxType" className="w-full md:w-64">
                                <SelectValue placeholder="Selecciona un tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="gravada">Operación Onerosa - Gravada</SelectItem>
                                <SelectItem value="exonerada">Operación Onerosa - Exonerada</SelectItem>
                                <SelectItem value="inafecta">Operación Onerosa - Inafecta</SelectItem>
                            </SelectContent>
                        </Select>
                        <p className="text-xs text-muted-foreground">Define el régimen fiscal por defecto para las ventas.</p>
                    </div>
                    
                    {settings.taxType === 'gravada' && (
                        <div className="space-y-6 pt-4 border-t">
                            <div className="space-y-2">
                                <Label htmlFor="taxRate">Tasa General de Impuestos (IGV/IVA) (%)</Label>
                                <div className="relative w-48">
                                    <Percent className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                    id="taxRate"
                                    name="taxRate"
                                    type="number"
                                    step="0.1"
                                    value={settings.taxRate}
                                    onChange={handleInputChange}
                                    />
                                </div>
                            </div>
                            <Separator />
                            <div className="space-y-4">
                                <Label>Departamentos Exonerados de IGV (Perú)</Label>
                                <p className="text-sm text-muted-foreground">
                                    Selecciona los departamentos que están exonerados del Impuesto General a las Ventas.
                                </p>
                                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 p-4 border rounded-md">
                                    {peruDepartments.map(department => (
                                        <div key={department} className="flex items-center space-x-2">
                                            <Checkbox
                                                id={`tax-exempt-${department}`}
                                                checked={settings.taxExemptRegions?.includes(department)}
                                                onCheckedChange={(checked) => handleTaxExemptRegionChange(department, !!checked)}
                                            />
                                            <Label htmlFor={`tax-exempt-${department}`} className="font-normal">{department}</Label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}

                </CardContent>
                </Card>
            </TabsContent>
            <TabsContent value="payments">
                <Card>
                    <CardHeader>
                        <CardTitle>Configuración de Pagos</CardTitle>
                        <CardDescription>Gestiona los métodos de pago que la plataforma acepta.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs defaultValue="bankAccounts">
                            <TabsList className="grid w-full grid-cols-2 md:grid-cols-3">
                                <TabsTrigger value="bankAccounts">Cuentas Bancarias</TabsTrigger>
                                <TabsTrigger value="qrPayments">Pagos con QR</TabsTrigger>
                                <TabsTrigger value="gateways">Pasarelas y Otros</TabsTrigger>
                            </TabsList>

                            <TabsContent value="bankAccounts" className="mt-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h4 className="text-base font-medium">Cuentas Bancarias (para transferencias)</h4>
                                    <Dialog open={isBankAccountDialogOpen} onOpenChange={setBankAccountDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button type="button" size="sm" onClick={() => setEditingBankAccount(null)}><PlusCircle className="mr-2"/> Añadir Cuenta</Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>{editingBankAccount ? 'Editar' : 'Añadir'} Cuenta Bancaria</DialogTitle>
                                            </DialogHeader>
                                            <form onSubmit={handleSaveBankAccount}>
                                                <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                                                    <Input name="bankName" placeholder="Nombre del Banco" defaultValue={editingBankAccount?.bankName} required/>
                                                    <Input name="accountHolder" placeholder="Titular de la Cuenta" defaultValue={editingBankAccount?.accountHolder} required/>
                                                    <Input name="accountNumber" placeholder="Número de Cuenta" defaultValue={editingBankAccount?.accountNumber} required/>
                                                    <Input name="country" placeholder="País" defaultValue={editingBankAccount?.country} required/>
                                                </div>
                                                <DialogFooter>
                                                    <Button type="submit">Guardar</Button>
                                                </DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader><TableRow><TableHead>Banco</TableHead><TableHead>Titular</TableHead><TableHead>Número</TableHead><TableHead>País</TableHead><TableHead>Acciones</TableHead></TableRow></TableHeader>
                                        <TableBody>
                                            {appSettings.paymentMethods.bankAccounts.map(account => (
                                                <TableRow key={account.id}>
                                                    <TableCell>{account.bankName}</TableCell>
                                                    <TableCell>{account.accountHolder}</TableCell>
                                                    <TableCell>{account.accountNumber}</TableCell>
                                                    <TableCell>{account.country}</TableCell>
                                                    <TableCell>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild><Button type="button" size="icon" variant="ghost"><MoreHorizontal /></Button></DropdownMenuTrigger>
                                                            <DropdownMenuContent>
                                                                <DropdownMenuItem onClick={() => { setEditingBankAccount(account); setBankAccountDialogOpen(true); }}>Editar</DropdownMenuItem>
                                                                <DropdownMenuItem className="text-destructive focus:text-destructive focus:bg-destructive/10" onClick={() => handleDeleteBankAccount(account.id)}>Eliminar</DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </TabsContent>

                            <TabsContent value="qrPayments" className="mt-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h4 className="text-base font-medium">Pagos con QR (Yape, Plin, etc.)</h4>
                                    <Dialog open={isQrPaymentDialogOpen} onOpenChange={setQrPaymentDialogOpen}>
                                        <DialogTrigger asChild>
                                            <Button type="button" size="sm" onClick={() => { setQrImagePreview(null); setQrImageFile(null); }}><PlusCircle className="mr-2"/> Añadir Pago QR</Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader><DialogTitle>Añadir Nuevo Pago QR</DialogTitle></DialogHeader>
                                            <form onSubmit={handleSaveQrPayment} className="space-y-4">
                                                <Input name="qrName" placeholder="Nombre (ej. Yape, Plin)" required />
                                                <Textarea name="qrInstructions" placeholder="Instrucciones para el cliente" required />
                                                <div>
                                                    <Label htmlFor="qrImage">Imagen del Código QR</Label>
                                                    {qrImagePreview && (
                                                        <div className="my-2">
                                                            <img src={qrImagePreview} alt="QR preview" width={128} height={128} className="rounded-md border object-cover"/>
                                                        </div>
                                                    )}
                                                    <Input id="qrImage" name="qrImage" type="file" accept="image/*" onChange={(e) => handleFileChange(e, setQrImagePreview, setQrImageFile)} required/>
                                                </div>
                                                <DialogFooter><Button type="submit">Guardar QR</Button></DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader><TableRow><TableHead>Imagen QR</TableHead><TableHead>Nombre</TableHead><TableHead>Instrucciones</TableHead><TableHead>Acciones</TableHead></TableRow></TableHeader>
                                        <TableBody>
                                            {appSettings.paymentMethods.qrPayments.map(qr => {
                                                // Extraer URL si es objeto (datos viejos) o usar directamente si es string
                                                let imageUrl = '';
                                                if (typeof qr.qrImageUrl === 'object' && qr.qrImageUrl !== null && 'url' in qr.qrImageUrl) {
                                                    imageUrl = (qr.qrImageUrl as any).url;
                                                } else if (typeof qr.qrImageUrl === 'string') {
                                                    imageUrl = qr.qrImageUrl;
                                                }

                                                // Verificar si la URL es válida (no Base64 ni vacía)
                                                const isValidUrl = imageUrl &&
                                                                   imageUrl.trim() !== '' &&
                                                                   !imageUrl.startsWith('data:');

                                                return (
                                                    <TableRow key={qr.id}>
                                                        <TableCell>
                                                            {isValidUrl ? (
                                                                <Image
                                                                    src={imageUrl}
                                                                    alt={qr.name}
                                                                    width={48}
                                                                    height={48}
                                                                    className="rounded-md object-cover"
                                                                    style={{ width: '48px', height: '48px' }}
                                                                />
                                                            ) : (
                                                                <div className="w-12 h-12 bg-muted rounded-md flex items-center justify-center">
                                                                    <ImageIcon className="h-6 w-6 text-muted-foreground" />
                                                                </div>
                                                            )}
                                                        </TableCell>
                                                        <TableCell>{qr.name}</TableCell>
                                                        <TableCell>{qr.instructions}</TableCell>
                                                        <TableCell>
                                                            <Button type="button" variant="destructive" size="icon" onClick={() => handleDeleteQrPayment(qr.id)}>
                                                                <Trash className="h-4 w-4"/>
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                </div>
                            </TabsContent>

                            <TabsContent value="gateways" className="mt-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between p-4 border rounded-md">
                                        <div>
                                            <Label htmlFor="cod-enabled" className="text-base font-medium">Pago en Efectivo (Contra Entrega)</Label>
                                            <p className="text-sm text-muted-foreground">Permitir a los clientes pagar en efectivo al recibir el pedido.</p>
                                        </div>
                                        <Switch
                                            id="cod-enabled"
                                            checked={settings.paymentMethods.cashOnDeliveryEnabled}
                                            onCheckedChange={(checked) => handleSwitchChange(checked, 'paymentMethods.cashOnDeliveryEnabled')}
                                        />
                                    </div>
                                    <div className="flex items-center justify-between p-4 border rounded-md">
                                        <div>
                                            <Label htmlFor="gateway-enabled" className="text-base font-medium">Pasarela de Pago (Tarjetas)</Label>
                                            <p className="text-sm text-muted-foreground">Habilitar pagos con tarjeta de crédito/débito.</p>
                                        </div>
                                        <Switch
                                            id="gateway-enabled"
                                            checked={settings.paymentMethods.gateway?.enabled}
                                            onCheckedChange={(checked) => setSettings(prev => ({...prev, paymentMethods: {...prev.paymentMethods, gateway: {...prev.paymentMethods.gateway, enabled: checked}}}) )}
                                        />
                                    </div>
                                    {settings.paymentMethods.gateway?.enabled && (
                                    <div className="p-4 border rounded-md space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="gateway-provider">Proveedor de Pasarela</Label>
                                            <Select
                                                name="provider"
                                                value={settings.paymentMethods.gateway?.provider}
                                                onValueChange={(value: PaymentGatewaySettings['provider']) => setSettings(prev => ({...prev, paymentMethods: {...prev.paymentMethods, gateway: {...prev.paymentMethods.gateway, provider: value}}}) )}
                                            >
                                                <SelectTrigger id="gateway-provider">
                                                    <SelectValue placeholder="Selecciona un proveedor" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">Ninguno</SelectItem>
                                                    <SelectItem value="stripe">Stripe</SelectItem>
                                                    <SelectItem value="mercadopago">Mercado Pago</SelectItem>
                                                    <SelectItem value="custom">Personalizado</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="gateway-publicKey">Clave Pública</Label>
                                            <Input id="gateway-publicKey" name="publicKey" value={settings.paymentMethods.gateway?.publicKey} onChange={handlePaymentGatewayChange} />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="gateway-secretKey">Clave Secreta</Label>
                                            <Input id="gateway-secretKey" name="secretKey" type="password" value={settings.paymentMethods.gateway?.secretKey} onChange={handlePaymentGatewayChange} />
                                        </div>
                                    </div>
                                    )}
                                </div>
                            </TabsContent>
                        </Tabs>
                    </CardContent>
                </Card>
            </TabsContent>
            <TabsContent value="team">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Equipo y Permisos</CardTitle>
                                <CardDescription>Gestiona quién tiene acceso a tu panel de administración.</CardDescription>
                            </div>
                            <Dialog open={isTeamDialogOpen} onOpenChange={setTeamDialogOpen}>
                                <DialogTrigger asChild>
                                    <Button size="sm" onClick={() => handleEditCollaboratorClick({ id: '', name: '', email: '', role: 'Colaborador', permissions: [] })}>
                                        <Users className="mr-2 h-4 w-4" /> Añadir Miembro
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="sm:max-w-2xl">
                                    <DialogHeader>
                                        <DialogTitle>{editingCollaborator?.id ? 'Editar' : 'Añadir'} Colaborador</DialogTitle>
                                        <DialogDescription>
                                            Configura los detalles y permisos del miembro del equipo.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form onSubmit={handleSaveCollaborator}>
                                        <div className="grid gap-6 py-4 max-h-[70vh] overflow-y-auto pr-4">
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div className="space-y-1">
                                                    <Label htmlFor="name">Nombre</Label>
                                                    <Input id="name" name="name" defaultValue={editingCollaborator?.name} required />
                                                </div>
                                                <div className="space-y-1">
                                                    <Label htmlFor="email">Correo Electrónico</Label>
                                                    <Input id="email" name="email" type="email" defaultValue={editingCollaborator?.email} required />
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div className="space-y-1">
                                                    <Label htmlFor="role">Rol</Label>
                                                    <Input id="role" name="role" defaultValue={editingCollaborator?.role} placeholder="Ej: Gestor de Tiendas" required />
                                                </div>
                                                <div className="space-y-1">
                                                    <Label htmlFor="password">Contraseña (dejar en blanco para no cambiar)</Label>
                                                    <Input id="password" name="password" type="password" />
                                                </div>
                                            </div>
                                            <div>
                                                <Label>Permisos</Label>
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 rounded-md border p-4 mt-2">
                                                    {availablePermissions.map(permission => (
                                                        <div key={permission.id} className="flex items-center space-x-2">
                                                            <Checkbox
                                                                id={permission.id}
                                                                checked={collaboratorPermissions.includes(permission.id)}
                                                                onCheckedChange={(checked) => handlePermissionChange(permission.id, !!checked)}
                                                            />
                                                            <Label htmlFor={permission.id} className="font-normal">{permission.label}</Label>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button type="button" variant="ghost" onClick={() => setTeamDialogOpen(false)}>Cancelar</Button>
                                            <Button type="submit">Guardar Colaborador</Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nombre</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Rol</TableHead>
                                        <TableHead>Acciones</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {collaborators.map(collaborator => (
                                        <TableRow key={collaborator.id}>
                                            <TableCell className="font-medium">{collaborator.name}</TableCell>
                                            <TableCell>{collaborator.email}</TableCell>
                                            <TableCell><Badge variant="secondary">{collaborator.role}</Badge></TableCell>
                                            <TableCell>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild><Button size="icon" variant="ghost"><MoreHorizontal /></Button></DropdownMenuTrigger>
                                                    <DropdownMenuContent>
                                                        <DropdownMenuItem onClick={() => handleEditCollaboratorClick(collaborator)}>Editar</DropdownMenuItem>
                                                        <DropdownMenuItem className="text-destructive focus:text-destructive focus:bg-destructive/10" onClick={() => handleDeleteCollaborator(collaborator.id)}>Eliminar</DropdownMenuItem>
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
            </TabsContent>
        </Tabs>

        <div className="flex justify-end pt-4">
        <Button type="submit" disabled={isUploading}>
            {isUploading ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Subiendo a Firebase...
              </>
            ) : (
              <>
                <Save className="mr-2 h-4 w-4" />
                Guardar Todos los Cambios
              </>
            )}
        </Button>
        </div>
    </form>
    </div>
  );
}

export default function AdminSettingsPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminSettingsPageContent />
    </AuthGuard>
  );
}

