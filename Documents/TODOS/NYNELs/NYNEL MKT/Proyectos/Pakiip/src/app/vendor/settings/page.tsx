

"use client";

import { useState } from 'react';
import { useSearchParams } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';
import { useAppData } from '@/hooks/use-app-data';
import { Save, Bell, KeyRound, Landmark, Building, User, Mail, FileText, Percent, Phone, QrCode } from 'lucide-react';
import { initializeFirebase, uploadImage } from '@/lib/firebase';
import Image from 'next/image';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import type { VendorBankAccount } from '@/lib/placeholder-data';
import { formatCurrency } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { AuthGuard } from "@/components/AuthGuard";

function VendorSettingsPageContent() {
    const { vendors, saveVendor, appSettings, currentUser } = useAppData();
    const { toast } = useToast();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO query params
    const loggedInVendorId = currentUser?.role === 'vendor' ? currentUser.id : null;
    const vendor = loggedInVendorId ? vendors.find(v => v.id === loggedInVendorId) : undefined;

    const [email, setEmail] = useState(vendor?.email || '');
    const [phone, setPhone] = useState(vendor?.phone || '');
    const [businessName, setBusinessName] = useState(vendor?.name || '');
    const [dni, setDni] = useState(vendor?.dni || '');
    const [bankAccount, setBankAccount] = useState<VendorBankAccount>(vendor?.bankAccount || { bankName: '', accountHolder: '', accountNumber: '' });

    // Estados para método de pago QR
    const [paymentMethod, setPaymentMethod] = useState<'bank' | 'qr'>(vendor?.paymentMethod || 'bank');
    const [qrImagePreview, setQrImagePreview] = useState<string | null>(vendor?.qrImageUrl || null);
    const [qrImageFile, setQrImageFile] = useState<File | null>(null);
    const [qrPaymentName, setQrPaymentName] = useState(vendor?.qrPaymentName || '');
    const [isUploading, setIsUploading] = useState(false);

    const handleBankAccountChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setBankAccount(prev => ({...prev, [e.target.name]: e.target.value}));
    }

    const handleQrFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setQrImageFile(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setQrImagePreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSaveChanges = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!vendor) return;

        // Si el método es QR y hay un archivo nuevo, subirlo a Firebase
        let qrImageUrl = vendor.qrImageUrl;
        if (paymentMethod === 'qr' && qrImageFile) {
            setIsUploading(true);
            try {
                const timestamp = Date.now();
                const fileExtension = qrImageFile.name.split('.').pop() || 'jpg';
                const fileName = `vendor_qr_${vendor.id}_${timestamp}.${fileExtension}`;
                const filePath = `vendor-qr-payments/${fileName}`;
                const result = await uploadImage(qrImageFile, filePath);

                if (result.success && result.url) {
                    qrImageUrl = result.url;
                } else {
                    throw new Error(result.error || 'Error al subir imagen QR');
                }
            } catch (error) {
                console.error('Error al subir imagen QR:', error);
                toast({
                    title: "Error",
                    description: "No se pudo subir la imagen QR. Intenta de nuevo.",
                    variant: "destructive"
                });
                setIsUploading(false);
                return;
            } finally {
                setIsUploading(false);
            }
        }

        // Validación: si es método QR, debe tener imagen y nombre
        if (paymentMethod === 'qr') {
            if (!qrImageUrl) {
                toast({
                    title: "Error",
                    description: "Por favor, sube una imagen QR para el método de pago.",
                    variant: "destructive"
                });
                return;
            }
            if (!qrPaymentName.trim()) {
                toast({
                    title: "Error",
                    description: "Por favor, ingresa el nombre del método de pago (Yape, Plin, etc.).",
                    variant: "destructive"
                });
                return;
            }
        }

        saveVendor({
            ...vendor,
            email,
            phone,
            name: businessName,
            dni,
            paymentMethod,
            bankAccount: paymentMethod === 'bank' ? bankAccount : undefined,
            qrImageUrl: paymentMethod === 'qr' ? qrImageUrl : undefined,
            qrPaymentName: paymentMethod === 'qr' ? qrPaymentName : undefined,
        });

        toast({
            title: "Configuración Guardada",
            description: "Tus cambios han sido guardados exitosamente.",
        });
    };
    
     if (!vendor) {
        return (
            <Card>
                <CardHeader><CardTitle>Error</CardTitle></CardHeader>
                <CardContent><p>No se pudo cargar la información del vendedor. Por favor, intenta de nuevo.</p></CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
            <form onSubmit={handleSaveChanges} className="space-y-6 sm:space-y-8">
                <div className="mb-6 sm:mb-8">
                    <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Configuración de la Tienda</h1>
                    <p className="text-sm sm:text-base text-muted-foreground mt-1">Gestiona tu perfil, notificaciones y seguridad.</p>
                </div>

                <Card>
                    <CardHeader className="px-3 sm:px-4 md:px-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><User className="h-5 w-5" /> Perfil de la Tienda</CardTitle>
                        <CardDescription className="text-sm">Esta es la información que verán los clientes y administradores.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-4 md:px-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="businessName" className="text-sm">Nombre del Negocio</Label>
                                <div className="relative">
                                    <Building className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input id="businessName" value={businessName} onChange={(e) => setBusinessName(e.target.value)} className="pl-9 h-9 sm:h-10 text-sm sm:text-base" />
                                </div>
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="email" className="text-sm">Correo Electrónico</Label>
                                <div className="relative">
                                    <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input id="email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} className="pl-9 h-9 sm:h-10 text-sm sm:text-base" />
                                </div>
                            </div>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="dni" className="text-sm">DNI / RUC</Label>
                                <div className="relative">
                                    <FileText className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input id="dni" value={dni} onChange={(e) => setDni(e.target.value)} className="pl-9 h-9 sm:h-10 text-sm sm:text-base" />
                                </div>
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="phone" className="text-sm">Teléfono</Label>
                                <div className="relative">
                                    <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input id="phone" type="tel" value={phone} onChange={(e) => setPhone(e.target.value)} className="pl-9 h-9 sm:h-10 text-sm sm:text-base" />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8 items-start">
                    <Card>
                        <CardHeader className="px-3 sm:px-4 md:px-6">
                            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
                                <Landmark className="h-5 w-5" />
                                Información de Liquidación
                            </CardTitle>
                            <CardDescription className="text-sm">
                                Aquí es donde recibirás tus pagos.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 px-3 sm:px-4 md:px-6">
                            {/* Selector de método de pago */}
                            <div className="space-y-2">
                                <Label className="text-sm font-medium">Método de Pago</Label>
                                <div className="grid grid-cols-2 gap-2">
                                    <Button
                                        type="button"
                                        variant={paymentMethod === 'bank' ? 'default' : 'outline'}
                                        onClick={() => setPaymentMethod('bank')}
                                        className="text-sm"
                                    >
                                        <Landmark className="h-4 w-4 mr-2" />
                                        Cuenta Bancaria
                                    </Button>
                                    <Button
                                        type="button"
                                        variant={paymentMethod === 'qr' ? 'default' : 'outline'}
                                        onClick={() => setPaymentMethod('qr')}
                                        className="text-sm"
                                    >
                                        <QrCode className="h-4 w-4 mr-2" />
                                        Yape/Plin (QR)
                                    </Button>
                                </div>
                            </div>

                            {/* Campos condicionales según método seleccionado */}
                            {paymentMethod === 'bank' ? (
                                <div className="space-y-3 sm:space-y-4">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="bankName" className="text-sm">
                                            Nombre del Banco
                                        </Label>
                                        <Input
                                            id="bankName"
                                            name="bankName"
                                            value={bankAccount.bankName}
                                            onChange={handleBankAccountChange}
                                            placeholder="Ej: BCP, Interbank"
                                            className="h-9 sm:h-10 text-sm sm:text-base"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="accountHolder" className="text-sm">
                                            Titular de la Cuenta
                                        </Label>
                                        <Input
                                            id="accountHolder"
                                            name="accountHolder"
                                            value={bankAccount.accountHolder}
                                            onChange={handleBankAccountChange}
                                            placeholder="Ej: Juan Pérez"
                                            className="h-9 sm:h-10 text-sm sm:text-base"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="accountNumber" className="text-sm">
                                            Número de Cuenta
                                        </Label>
                                        <Input
                                            id="accountNumber"
                                            name="accountNumber"
                                            value={bankAccount.accountNumber}
                                            onChange={handleBankAccountChange}
                                            placeholder="Ej: 123-4567890-1-23"
                                            className="h-9 sm:h-10 text-sm sm:text-base"
                                        />
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {/* Nombre del método QR */}
                                    <div className="space-y-1.5">
                                        <Label htmlFor="qrPaymentName" className="text-sm">
                                            Nombre del Método (Yape, Plin, etc.)
                                        </Label>
                                        <Input
                                            id="qrPaymentName"
                                            value={qrPaymentName}
                                            onChange={(e) => setQrPaymentName(e.target.value)}
                                            placeholder="Ej: Yape"
                                            className="h-9 sm:h-10 text-sm sm:text-base"
                                        />
                                    </div>

                                    {/* Subir imagen QR */}
                                    <div className="space-y-2">
                                        <Label htmlFor="qrImage" className="text-sm">
                                            Código QR
                                        </Label>
                                        <div className="flex flex-col items-center gap-3">
                                            {qrImagePreview && (
                                                <div className="relative w-40 h-40 border-2 border-dashed rounded-lg overflow-hidden">
                                                    <Image
                                                        src={qrImagePreview}
                                                        alt="QR Preview"
                                                        fill
                                                        className="object-contain"
                                                    />
                                                </div>
                                            )}
                                            <Input
                                                id="qrImage"
                                                type="file"
                                                accept="image/*"
                                                onChange={handleQrFileChange}
                                                className="h-9 sm:h-10 text-sm sm:text-base"
                                            />
                                            <p className="text-xs text-muted-foreground text-center">
                                                Sube una imagen de tu código QR de Yape, Plin u otro método de pago
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="px-3 sm:px-4 md:px-6">
                            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><Percent className="h-5 w-5" /> Información de Comisión</CardTitle>
                            <CardDescription className="text-sm">
                            Esta es la comisión que se te aplica por cada venta en la plataforma.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 px-3 sm:px-4 md:px-6">
                            <div className="flex items-baseline justify-center p-6 bg-muted rounded-lg">
                                <p className="text-4xl font-bold">{vendor.commissionRate}%</p>
                            </div>
                            <p className="text-xs text-muted-foreground text-center">
                                Esta comisión es gestionada por el administrador de la plataforma.
                            </p>
                        </CardContent>
                    </Card>
                </div>
                
                <Card>
                    <CardHeader className="px-3 sm:px-4 md:px-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><Bell className="h-5 w-5" /> Notificaciones</CardTitle>
                        <CardDescription className="text-sm">Elige cómo quieres que te notifiquemos.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-4 md:px-6">
                        <div className="flex items-center justify-between p-3 sm:p-4 rounded-lg border">
                            <Label htmlFor="new-order-email" className="flex flex-col gap-1 cursor-pointer">
                                <span className="text-sm sm:text-base">Email por nuevo pedido</span>
                                <span className="text-xs font-normal text-muted-foreground">Recibe un correo cada vez que un cliente haga un pedido.</span>
                            </Label>
                            <Switch id="new-order-email" defaultChecked />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="px-3 sm:px-4 md:px-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><KeyRound className="h-5 w-5" /> Seguridad</CardTitle>
                        <CardDescription className="text-sm">Gestiona la seguridad de tu cuenta.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-4 md:px-6">
                        <div className="space-y-1.5">
                            <Label htmlFor="current-password" className="text-sm">Contraseña Actual</Label>
                            <Input id="current-password" type="password" className="h-9 sm:h-10 text-sm sm:text-base" />
                        </div>
                        <div className="space-y-1.5">
                            <Label htmlFor="new-password" className="text-sm">Nueva Contraseña</Label>
                            <Input id="new-password" type="password" className="h-9 sm:h-10 text-sm sm:text-base" />
                        </div>
                        <Button type="button" onClick={() => toast({ title: "Contraseña Actualizada (Simulado)" })} className="h-9 sm:h-10 text-sm sm:text-base">Cambiar Contraseña</Button>
                    </CardContent>
                </Card>

                <div className="flex justify-end pt-4">
                    <Button type="submit" className="h-9 sm:h-10 text-sm sm:text-base w-full sm:w-auto">
                        <Save className="mr-2 h-4 w-4" />
                        Guardar Cambios
                    </Button>
                </div>
            </form>
        </div>
    );
}

export default function VendorSettingsPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="vendor" redirectTo="/vendor/login">
            <VendorSettingsPageContent />
        </AuthGuard>
    );
}
