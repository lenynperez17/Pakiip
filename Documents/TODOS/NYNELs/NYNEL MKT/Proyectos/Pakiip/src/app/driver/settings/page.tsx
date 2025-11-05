

"use client";

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';
import { useAppData } from '@/hooks/use-app-data';
import { Save, Bell, KeyRound, Landmark, User, Mail, FileText, Phone, Bike, Percent, ImagePlus, Link2, Unlink, Shield, QrCode } from 'lucide-react';
import { initializeFirebase, uploadImage } from '@/lib/firebase';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import type { DeliveryDriver } from '@/lib/placeholder-data';
import { formatCurrency } from '@/lib/utils';
import Image from 'next/image';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { AuthGuard } from "@/components/AuthGuard";
import {
    getLinkedProviders,
    linkAccountWithGoogle,
    startPhoneLinking,
    linkAccountWithPhone,
    unlinkProvider,
    setupRecaptcha,
    type RecaptchaVerifier,
    type ConfirmationResult
} from '@/lib/firebase';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

function DriverSettingsPageContent() {
    const { drivers, saveDriver, appSettings, currentUser } = useAppData();
    const { toast } = useToast();

    // 🔒 SEGURIDAD: Usar SOLO el ID del usuario logueado, NO hardcodeado
    const loggedInDriverId = currentUser?.role === 'driver' ? currentUser.id : null;
    const driver = loggedInDriverId ? drivers.find(d => d.id === loggedInDriverId) : undefined;

    const [name, setName] = useState(driver?.name || '');
    const [phone, setPhone] = useState(driver?.phone || '');
    const [vehicle, setVehicle] = useState<DeliveryDriver['vehicle']>(driver?.vehicle || 'Moto');
    const [status, setStatus] = useState<DeliveryDriver['status']>(driver?.status || 'Inactivo');
    const [bankAccount, setBankAccount] = useState(driver?.bankAccount || '');
    const [profilePreview, setProfilePreview] = useState<string | null>(driver?.profileImageUrl || null);

    // Estados para método de pago QR
    const [paymentMethod, setPaymentMethod] = useState<'bank' | 'qr'>(driver?.paymentMethod || 'bank');
    const [qrImagePreview, setQrImagePreview] = useState<string | null>(driver?.qrImageUrl || null);
    const [qrImageFile, setQrImageFile] = useState<File | null>(null);
    const [qrPaymentName, setQrPaymentName] = useState(driver?.qrPaymentName || '');
    const [isUploading, setIsUploading] = useState(false);

    // Estados para vinculación de cuentas
    const [linkedProviders, setLinkedProviders] = useState<Array<{
        providerId: string;
        displayName: string;
        email?: string;
        phoneNumber?: string;
    }>>([]);
    const [isLinkingGoogle, setIsLinkingGoogle] = useState(false);
    const [isLinkingPhone, setIsLinkingPhone] = useState(false);
    const [showPhoneLinkDialog, setShowPhoneLinkDialog] = useState(false);
    const [linkPhoneNumber, setLinkPhoneNumber] = useState('');
    const [linkVerificationCode, setLinkVerificationCode] = useState('');
    const [linkStep, setLinkStep] = useState<'phone' | 'code'>('phone');
    const [recaptchaVerifier, setRecaptchaVerifier] = useState<RecaptchaVerifier | null>(null);
    const [confirmationResult, setConfirmationResult] = useState<ConfirmationResult | null>(null);

    // Cargar proveedores vinculados al montar el componente
    useEffect(() => {
        loadLinkedProviders();
    }, []);

    // Función para cargar los proveedores vinculados
    const loadLinkedProviders = () => {
        const result = getLinkedProviders();
        if (result.success && result.providers) {
            setLinkedProviders(result.providers);
        }
    };

    // Verificar si un proveedor está vinculado
    const isProviderLinked = (providerId: string) => {
        return linkedProviders.some(p => p.providerId === providerId);
    };

    // Vincular con Google
    const handleLinkGoogle = async () => {
        setIsLinkingGoogle(true);
        try {
            const result = await linkAccountWithGoogle();
            if (result.success) {
                toast({
                    title: "✅ Google Vinculado",
                    description: "Tu cuenta de Google se vinculó exitosamente.",
                });
                loadLinkedProviders();
            } else {
                toast({
                    title: "Error al Vincular",
                    description: result.error,
                    variant: "destructive"
                });
            }
        } catch (error: any) {
            toast({
                title: "Error",
                description: error.message || "Error al vincular Google",
                variant: "destructive"
            });
        } finally {
            setIsLinkingGoogle(false);
        }
    };

    // Iniciar vinculación con teléfono
    const handleStartPhoneLinking = () => {
        setShowPhoneLinkDialog(true);
        setLinkStep('phone');
        setLinkPhoneNumber('');
        setLinkVerificationCode('');

        // Configurar reCAPTCHA después de que el dialog se muestre
        setTimeout(() => {
            try {
                const verifier = setupRecaptcha('recaptcha-container-link');
                setRecaptchaVerifier(verifier);
            } catch (error) {
                console.error('Error al configurar reCAPTCHA:', error);
            }
        }, 500);
    };

    // Enviar código SMS para vincular teléfono
    const handleSendPhoneLinkCode = async () => {
        if (!linkPhoneNumber || !recaptchaVerifier) {
            toast({
                title: "Error",
                description: "Por favor ingresa un número de teléfono válido",
                variant: "destructive"
            });
            return;
        }

        setIsLinkingPhone(true);
        try {
            const result = await startPhoneLinking(linkPhoneNumber, recaptchaVerifier);
            if (result.success && result.confirmationResult) {
                setConfirmationResult(result.confirmationResult);
                setLinkStep('code');
                toast({
                    title: "Código Enviado",
                    description: "Revisa tu teléfono e ingresa el código",
                });
            } else {
                toast({
                    title: "Error",
                    description: result.error || "Error al enviar código",
                    variant: "destructive"
                });
            }
        } catch (error: any) {
            toast({
                title: "Error",
                description: error.message || "Error al enviar código",
                variant: "destructive"
            });
        } finally {
            setIsLinkingPhone(false);
        }
    };

    // Verificar código y completar vinculación
    const handleVerifyPhoneLinkCode = async () => {
        if (!confirmationResult || !linkVerificationCode) {
            toast({
                title: "Error",
                description: "Por favor ingresa el código de verificación",
                variant: "destructive"
            });
            return;
        }

        setIsLinkingPhone(true);
        try {
            const result = await linkAccountWithPhone(confirmationResult, linkVerificationCode);
            if (result.success) {
                toast({
                    title: "✅ Teléfono Vinculado",
                    description: "Tu número de teléfono se vinculó exitosamente.",
                });
                loadLinkedProviders();
                setShowPhoneLinkDialog(false);
            } else {
                toast({
                    title: "Error",
                    description: result.error || "Código inválido",
                    variant: "destructive"
                });
            }
        } catch (error: any) {
            toast({
                title: "Error",
                description: error.message || "Error al verificar código",
                variant: "destructive"
            });
        } finally {
            setIsLinkingPhone(false);
        }
    };

    // Desvincular proveedor
    const handleUnlinkProvider = async (providerId: string, displayName: string) => {
        if (!confirm(`¿Seguro que quieres desvincular ${displayName}?`)) {
            return;
        }

        try {
            const result = await unlinkProvider(providerId);
            if (result.success) {
                toast({
                    title: "Proveedor Desvinculado",
                    description: `${displayName} se desvinculó exitosamente.`,
                });
                loadLinkedProviders();
            } else {
                toast({
                    title: "Error",
                    description: result.error,
                    variant: "destructive"
                });
            }
        } catch (error: any) {
            toast({
                title: "Error",
                description: error.message || "Error al desvincular",
                variant: "destructive"
            });
        }
    };

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                setProfilePreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

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
        if (!driver) return;

        // Si el método es QR y hay un archivo nuevo, subirlo a Firebase
        let qrImageUrl = driver.qrImageUrl;
        if (paymentMethod === 'qr' && qrImageFile) {
            setIsUploading(true);
            try {
                const timestamp = Date.now();
                const fileExtension = qrImageFile.name.split('.').pop() || 'jpg';
                const fileName = `driver_qr_${driver.id}_${timestamp}.${fileExtension}`;
                const filePath = `driver-qr-payments/${fileName}`;
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

        saveDriver({
            ...driver,
            name,
            phone,
            vehicle,
            status,
            paymentMethod,
            bankAccount: paymentMethod === 'bank' ? bankAccount : undefined,
            qrImageUrl: paymentMethod === 'qr' ? qrImageUrl : undefined,
            qrPaymentName: paymentMethod === 'qr' ? qrPaymentName : undefined,
            profileImageUrl: profilePreview || driver.profileImageUrl,
        });

        toast({
            title: "Configuración Guardada",
            description: "Tus cambios han sido guardados exitosamente.",
        });
    };
    
     if (!driver) {
        return (
            <Card>
                <CardHeader><CardTitle>Error</CardTitle></CardHeader>
                <CardContent><p>No se pudo cargar tu información. Por favor, intenta de nuevo.</p></CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-4 sm:space-y-6 px-2 sm:px-3 md:px-4">
            <form onSubmit={handleSaveChanges} className="space-y-6 sm:space-y-8">
                <div className="mb-6 sm:mb-8">
                    <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Tu Configuración</h1>
                    <p className="text-sm sm:text-base text-muted-foreground mt-1">Gestiona tu perfil, disponibilidad y seguridad.</p>
                </div>

                <Card>
                    <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><User className="h-4 w-4 sm:h-5 sm:w-5" /> Tu Perfil</CardTitle>
                        <CardDescription className="text-sm">Mantén tu información personal actualizada.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
                         <div className="space-y-2">
                            <Label className="text-sm">Foto de Perfil</Label>
                            <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                                <Avatar className="h-16 w-16 sm:h-20 sm:w-20">
                                    {profilePreview ? <AvatarImage src={profilePreview} alt={driver.name} /> : null}
                                    <AvatarFallback>{getInitials(driver.name)}</AvatarFallback>
                                </Avatar>
                                <Input id="profile-image" type="file" accept="image/*" onChange={handleFileChange} className="w-full sm:max-w-xs text-sm" />
                            </div>
                        </div>
                        <div className="grid md:grid-cols-2 gap-3 sm:gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="name" className="text-sm">Nombre Completo</Label>
                                <Input id="name" value={name} onChange={(e) => setName(e.target.value)} className="text-sm" />
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="phone" className="text-sm">Celular</Label>
                                <div className="relative">
                                    <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                                    <Input id="phone" type="tel" value={phone} onChange={(e) => setPhone(e.target.value)} className="pl-8 sm:pl-9 text-sm" />
                                </div>
                            </div>
                        </div>
                        <div className="grid md:grid-cols-2 gap-3 sm:gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="vehicle" className="text-sm">Vehículo</Label>
                                <div className="relative">
                                    <Bike className="absolute left-3 top-1/2 -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                                    <Select value={vehicle} onValueChange={(v: DeliveryDriver['vehicle']) => setVehicle(v)}>
                                        <SelectTrigger className="pl-8 sm:pl-9 text-sm"><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Moto">Moto</SelectItem>
                                            <SelectItem value="Coche">Coche</SelectItem>
                                            <SelectItem value="Bicicleta">Bicicleta</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <div className="space-y-1">
                                <Label htmlFor="status" className="text-sm">Mi Estado</Label>
                                <Select value={status} onValueChange={(v: DeliveryDriver['status']) => setStatus(v)}>
                                    <SelectTrigger className="text-sm"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Activo">Activo (Disponible para entregas)</SelectItem>
                                        <SelectItem value="Inactivo">Inactivo (No disponible)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8 items-start">
                    <Card>
                        <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
                                <Landmark className="h-4 w-4 sm:h-5 sm:w-5" />
                                Información de Pago
                            </CardTitle>
                            <CardDescription className="text-sm">
                                Aquí es donde recibirás tus comisiones.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
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
                                <div className="space-y-1">
                                    <Label htmlFor="bankAccount" className="text-sm">
                                        Número de Cuenta Bancaria
                                    </Label>
                                    <Input
                                        id="bankAccount"
                                        value={bankAccount}
                                        onChange={(e) => setBankAccount(e.target.value)}
                                        placeholder="Ej: 123-456-789012"
                                        className="text-sm"
                                    />
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {/* Nombre del método QR */}
                                    <div className="space-y-1">
                                        <Label htmlFor="qrPaymentName" className="text-sm">
                                            Nombre del Método (Yape, Plin, etc.)
                                        </Label>
                                        <Input
                                            id="qrPaymentName"
                                            value={qrPaymentName}
                                            onChange={(e) => setQrPaymentName(e.target.value)}
                                            placeholder="Ej: Yape"
                                            className="text-sm"
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
                                                className="text-sm"
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
                        <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><Percent className="h-4 w-4 sm:h-5 sm:w-5" /> Información de Comisión</CardTitle>
                            <CardDescription className="text-sm">Esta es la comisión que ganas por cada entrega.</CardDescription>
                        </CardHeader>
                        <CardContent className="px-3 sm:px-6 pb-4 sm:pb-6">
                            <div className="flex items-baseline justify-center p-4 sm:p-6 bg-muted rounded-lg">
                                <p className="text-3xl sm:text-4xl font-bold">{driver.commissionRate.toFixed(2)}%</p>
                            </div>
                            <p className="text-xs text-center text-muted-foreground mt-2">
                            Ganas el {driver.commissionRate}% de la tarifa de envío de cada entrega.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><Bell className="h-4 w-4 sm:h-5 sm:w-5" /> Notificaciones</CardTitle>
                        <CardDescription className="text-sm">Elige cómo quieres que te notifiquemos sobre nuevas entregas.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
                        <div className="flex items-center justify-between p-3 sm:p-4 rounded-lg border gap-3">
                            <Label htmlFor="new-order-push" className="flex flex-col gap-1 flex-1">
                                <span className="text-sm sm:text-base">Notificación Push por nuevo pedido</span>
                                <span className="text-xs font-normal text-muted-foreground">Recibe una alerta en tu celular cuando se te asigne un pedido.</span>
                            </Label>
                            <Switch id="new-order-push" defaultChecked />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl"><KeyRound className="h-4 w-4 sm:h-5 sm:w-5" /> Seguridad</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
                        <div className="space-y-1">
                            <Label htmlFor="current-password" className="text-sm">Contraseña Actual</Label>
                            <Input id="current-password" type="password" className="text-sm" />
                        </div>
                        <div className="space-y-1">
                            <Label htmlFor="new-password" className="text-sm">Nueva Contraseña</Label>
                            <Input id="new-password" type="password" className="text-sm" />
                        </div>
                        <Button type="button" size="sm" onClick={() => toast({ title: "Contraseña Actualizada (Simulado)" })} className="text-sm">Cambiar Contraseña</Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="px-3 sm:px-6 py-4 sm:py-6">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
                            <Shield className="h-4 w-4 sm:h-5 sm:w-5" />
                            Métodos de Inicio de Sesión
                        </CardTitle>
                        <CardDescription className="text-sm">
                            Vincula Google o teléfono para tener más opciones de acceso a tu cuenta.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4 px-3 sm:px-6 pb-4 sm:pb-6">
                        {/* Lista de proveedores vinculados */}
                        {linkedProviders.length > 0 && (
                            <div className="space-y-2">
                                <Label className="text-sm font-medium">Métodos Activos:</Label>
                                {linkedProviders.map((provider) => (
                                    <div
                                        key={provider.providerId}
                                        className="flex items-center justify-between p-3 rounded-lg border bg-muted/30"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                {provider.providerId === 'google.com' && '🔵'}
                                                {provider.providerId === 'phone' && '📱'}
                                                {provider.providerId === 'password' && '🔑'}
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium">{provider.displayName}</p>
                                                {provider.email && (
                                                    <p className="text-xs text-muted-foreground">{provider.email}</p>
                                                )}
                                                {provider.phoneNumber && (
                                                    <p className="text-xs text-muted-foreground">{provider.phoneNumber}</p>
                                                )}
                                            </div>
                                        </div>
                                        {linkedProviders.length > 1 && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleUnlinkProvider(provider.providerId, provider.displayName)}
                                                className="text-xs"
                                            >
                                                <Unlink className="h-3 w-3 mr-1" />
                                                Desvincular
                                            </Button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}

                        {/* Botones para vincular */}
                        <div className="space-y-2">
                            <Label className="text-sm font-medium">Agregar Método:</Label>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                {!isProviderLinked('google.com') && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleLinkGoogle}
                                        disabled={isLinkingGoogle}
                                        className="text-sm"
                                    >
                                        <Link2 className="h-3 w-3 sm:h-4 sm:w-4 mr-2" />
                                        {isLinkingGoogle ? 'Vinculando...' : 'Vincular Google'}
                                    </Button>
                                )}
                                {!isProviderLinked('phone') && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleStartPhoneLinking}
                                        className="text-sm"
                                    >
                                        <Link2 className="h-3 w-3 sm:h-4 sm:w-4 mr-2" />
                                        Vincular Teléfono
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Mensaje informativo */}
                        <div className="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <p className="text-xs text-blue-800 dark:text-blue-200">
                                💡 <strong>Recomendación:</strong> Vincula al menos 2 métodos para mayor seguridad
                                y para no perder acceso a tu cuenta.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                {/* Dialog para vincular teléfono */}
                <Dialog open={showPhoneLinkDialog} onOpenChange={setShowPhoneLinkDialog}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Vincular Número de Teléfono</DialogTitle>
                            <DialogDescription>
                                {linkStep === 'phone'
                                    ? 'Ingresa tu número de teléfono para recibir un código de verificación'
                                    : 'Ingresa el código que recibiste por SMS'}
                            </DialogDescription>
                        </DialogHeader>

                        {linkStep === 'phone' ? (
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="link-phone">Número de Teléfono</Label>
                                    <Input
                                        id="link-phone"
                                        type="tel"
                                        placeholder="+51999999999"
                                        value={linkPhoneNumber}
                                        onChange={(e) => setLinkPhoneNumber(e.target.value)}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Incluye el código de país (ej: +51 para Perú)
                                    </p>
                                </div>
                                <div id="recaptcha-container-link"></div>
                                <Button
                                    onClick={handleSendPhoneLinkCode}
                                    disabled={isLinkingPhone}
                                    className="w-full"
                                >
                                    {isLinkingPhone ? 'Enviando...' : 'Enviar Código'}
                                </Button>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="link-code">Código de Verificación</Label>
                                    <Input
                                        id="link-code"
                                        type="text"
                                        placeholder="123456"
                                        value={linkVerificationCode}
                                        onChange={(e) => setLinkVerificationCode(e.target.value)}
                                        maxLength={6}
                                    />
                                </div>
                                <Button
                                    onClick={handleVerifyPhoneLinkCode}
                                    disabled={isLinkingPhone}
                                    className="w-full"
                                >
                                    {isLinkingPhone ? 'Verificando...' : 'Verificar y Vincular'}
                                </Button>
                            </div>
                        )}
                    </DialogContent>
                </Dialog>

                <div className="flex justify-end pt-2 sm:pt-4">
                    <Button type="submit" size="sm" className="w-full sm:w-auto text-sm">
                        <Save className="mr-1 sm:mr-2 h-3 w-3 sm:h-4 sm:w-4" />
                        Guardar Cambios
                    </Button>
                </div>
            </form>
        </div>
    );
}

export default function DriverSettingsPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverSettingsPageContent />
        </AuthGuard>
    );
}
