"use client";

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { useAppData } from '@/hooks/use-app-data';
import { useSession } from 'next-auth/react';
import { Store, Upload, X, Clock, CheckCircle, XCircle } from 'lucide-react';
import { optimizeAndUploadImage } from '@/lib/image-optimizer';
import { LocationPickerMap } from '@/components/LocationPickerMap';
import { AddressAutocomplete } from '@/components/AddressAutocomplete';
import { GeocodeResult, reverseGeocode } from '@/lib/google-geocoding';
import type { Vendor } from '@/lib/placeholder-data';

interface VendorRegistrationFormProps {
  onSuccess?: () => void;
  onCancel?: () => void;
  showHeader?: boolean;
  redirectAfterSuccess?: string;
}

export function VendorRegistrationForm({
  onSuccess,
  onCancel,
  showHeader = true,
  redirectAfterSuccess = '/welcome'
}: VendorRegistrationFormProps) {
  const router = useRouter();
  const { toast } = useToast();
  const { saveVendor, currentUser, categories, cities, vendors } = useAppData();
  const { data: session, status } = useSession();
  const [loading, setLoading] = useState(false);
  const [existingVendor, setExistingVendor] = useState<{ status: string; name: string } | null>(null);
  const [qrImage, setQrImage] = useState<File | null>(null);
  const [qrImagePreview, setQrImagePreview] = useState<string>('');

  const [formData, setFormData] = useState({
    // Datos del usuario (prellenados)
    fullName: '',
    phone: '',
    dni: '',
    city: '',
    sector: '',
    // Datos específicos del negocio
    businessName: '',
    category: '',
    description: '',
    // Información de pago
    paymentMethod: 'yape',
    bankName: '',
    bankAccount: '',
    yapePhone: '',
    plinPhone: '',
  });

  const [coordinates, setCoordinates] = useState<{ lat: number; lng: number } | null>(null);
  const [address, setAddress] = useState('');

  // Verificar si ya está registrado como vendor
  useEffect(() => {
    if (status === 'loading') return;

    if (status === 'unauthenticated' || !session?.user) {
      toast({
        title: "Sesión requerida",
        description: "Debes iniciar sesión para continuar",
        variant: "destructive"
      });
      router.replace('/login');
      return;
    }

    const userId = session.user.id || '';

    // Buscar si ya existe un vendor con este ID
    const existing = vendors.find(v => v.id === userId || v.ownerId === userId);
    if (existing) {
      setExistingVendor({
        status: existing.status || 'Pendiente',
        name: existing.name || existing.businessName || 'Tu negocio'
      });
      return;
    }

    // Prellenar con datos del currentUser o session
    const userName = currentUser?.name || session.user.name || '';
    const userPhone = currentUser?.phone || '';

    setFormData(prev => ({
      ...prev,
      fullName: userName,
      phone: userPhone,
      dni: currentUser?.dni || '',
      city: currentUser?.city || '',
      sector: currentUser?.sector || '',
      yapePhone: userPhone,
      plinPhone: userPhone,
    }));

    // Prellenar coordenadas si existen
    if (currentUser?.coordinates) {
      setCoordinates(currentUser.coordinates);
    }
    if (currentUser?.address) {
      setAddress(currentUser.address);
    }
  }, [router, toast, currentUser, vendors, session, status]);

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  // Función para extraer ciudad automáticamente de las coordenadas
  const extractCityFromCoords = async (coords: { lat: number; lng: number }) => {
    try {
      const geocodeResult = await reverseGeocode(coords.lat, coords.lng);
      if (geocodeResult) {
        const detectedLocality = geocodeResult.locality || geocodeResult.district || geocodeResult.city;
        if (detectedLocality) {
          const matchedCity = cities.find(c =>
            detectedLocality.toLowerCase().includes(c.name.toLowerCase()) ||
            c.name.toLowerCase().includes(detectedLocality.toLowerCase())
          );
          if (matchedCity) {
            setFormData(prev => ({ ...prev, city: matchedCity.name }));
            if (matchedCity.sectors && matchedCity.sectors.length > 0) {
              const detectedSector = geocodeResult.sublocality || geocodeResult.neighborhood;
              if (detectedSector) {
                const matchedSector = matchedCity.sectors.find(s =>
                  detectedSector.toLowerCase().includes(s.name.toLowerCase()) ||
                  s.name.toLowerCase().includes(detectedSector.toLowerCase())
                );
                if (matchedSector) {
                  setFormData(prev => ({ ...prev, sector: matchedSector.name }));
                }
              }
            }
          } else {
            setFormData(prev => ({ ...prev, city: detectedLocality }));
          }
        }
      }
    } catch (error) {
      console.error('Error al extraer ciudad:', error);
    }
  };

  const handleQrImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (!file.type.startsWith('image/')) {
        toast({
          title: "Archivo inválido",
          description: "Por favor selecciona una imagen (JPG, PNG, etc.)",
          variant: "destructive"
        });
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        toast({
          title: "Imagen muy grande",
          description: "La imagen debe pesar menos de 5MB",
          variant: "destructive"
        });
        return;
      }

      setQrImage(file);

      const reader = new FileReader();
      reader.onloadend = () => {
        setQrImagePreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleRemoveQrImage = () => {
    setQrImage(null);
    setQrImagePreview('');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (!session?.user) {
        throw new Error('No hay usuario autenticado');
      }

      const sessionUser = session.user;

      // Validaciones de datos personales
      if (!formData.fullName.trim()) {
        throw new Error('El nombre completo es obligatorio');
      }
      if (!formData.phone.trim()) {
        throw new Error('El teléfono es obligatorio');
      }
      if (!formData.dni.trim()) {
        throw new Error('El DNI/RUC es obligatorio');
      }

      // Validaciones específicas del negocio
      if (!formData.businessName.trim()) {
        throw new Error('El nombre del negocio es obligatorio');
      }

      // Convertir nombre a mayúsculas y verificar duplicados
      const businessNameUpper = formData.businessName.trim().toUpperCase();
      const existingVendorWithName = vendors.find(v =>
        v.name?.toUpperCase() === businessNameUpper ||
        v.businessName?.toUpperCase() === businessNameUpper
      );
      if (existingVendorWithName) {
        throw new Error(`Ya existe una tienda con el nombre "${businessNameUpper}". Por favor elige otro nombre.`);
      }

      if (!formData.category) {
        throw new Error('Debes seleccionar una categoría');
      }
      if (!formData.description.trim()) {
        throw new Error('La descripción es obligatoria');
      }
      if (!address.trim()) {
        throw new Error('La dirección del negocio es obligatoria');
      }
      if (!coordinates) {
        throw new Error('Debes marcar la ubicación de tu negocio en el mapa');
      }

      // Validaciones de método de pago
      if (formData.paymentMethod === 'bank') {
        if (!formData.bankName.trim()) {
          throw new Error('El nombre del banco es obligatorio');
        }
        if (!formData.bankAccount.trim()) {
          throw new Error('El número de cuenta es obligatorio');
        }
      } else if (formData.paymentMethod === 'yape') {
        if (!formData.yapePhone.trim()) {
          throw new Error('El número de Yape es obligatorio');
        }
      } else if (formData.paymentMethod === 'plin') {
        if (!formData.plinPhone.trim()) {
          throw new Error('El número de Plin es obligatorio');
        }
      }

      const userId = sessionUser.id || '';

      // Subir QR si existe
      let qrImageUrl = '';
      if (qrImage && qrImagePreview && (formData.paymentMethod === 'yape' || formData.paymentMethod === 'plin')) {
        toast({
          title: "Subiendo imagen QR...",
          description: "Por favor espera un momento",
        });

        const uploadResult = await optimizeAndUploadImage(
          qrImagePreview,
          `vendors/${userId}/qr-${Date.now()}.webp`
        );
        if (uploadResult.success && uploadResult.url) {
          qrImageUrl = uploadResult.url;
        }
      }

      // Convertir nombres a mayúsculas
      const ownerNameUpper = formData.fullName.trim().toUpperCase();

      const newVendor: Record<string, unknown> = {
        id: userId,
        ownerId: userId,
        name: businessNameUpper,
        email: sessionUser.email || '',
        phone: formData.phone,
        dni: formData.dni,
        description: formData.description,
        category: formData.category,
        logoUrl: '',
        bannerUrl: '',
        address: address,
        location: formData.city,
        coordinates: coordinates,
        products: [],
        productCategories: [],
        isFeatured: false,
        commissionRate: 0.15,
        status: 'Pendiente' as const,
        additionalFee: 0,
        businessName: businessNameUpper,
        ownerName: ownerNameUpper,
        sector: formData.sector,
        // Información de pago
        paymentMethod: formData.paymentMethod,
      };

      // Agregar datos de pago según el método seleccionado
      if (formData.paymentMethod === 'bank') {
        newVendor.bankName = formData.bankName;
        newVendor.bankAccount = formData.bankAccount;
      } else if (formData.paymentMethod === 'yape') {
        newVendor.yapePhone = formData.yapePhone;
        if (qrImageUrl) {
          newVendor.qrPaymentImageUrl = qrImageUrl;
        }
      } else if (formData.paymentMethod === 'plin') {
        newVendor.plinPhone = formData.plinPhone;
        if (qrImageUrl) {
          newVendor.qrPaymentImageUrl = qrImageUrl;
        }
      }

      // Guardar vendor (saveVendor no retorna nada, lanza excepción en error)
      saveVendor(newVendor as Vendor);

      toast({
        title: "¡Registro exitoso!",
        description: "Tu solicitud ha sido enviada al administrador. Te notificaremos cuando sea aprobada.",
      });

      if (onSuccess) {
        onSuccess();
      } else {
        setTimeout(() => {
          router.push(redirectAfterSuccess);
        }, 1500);
      }

    } catch (error: unknown) {
      console.error('Error al registrar vendor:', error);
      toast({
        title: "Error en el registro",
        description: error instanceof Error ? error.message : "No se pudo completar el registro. Intenta nuevamente.",
        variant: "destructive"
      });
      setLoading(false);
    }
  };

  const handleCancel = () => {
    if (onCancel) {
      onCancel();
    } else {
      router.back();
    }
  };

  // Si ya existe un registro, mostrar el estado
  if (existingVendor) {
    const statusConfig = {
      'Pendiente': {
        icon: Clock,
        color: 'text-yellow-500',
        bgColor: 'bg-yellow-500/10',
        title: 'Solicitud en revisión',
        description: 'Tu solicitud está siendo revisada por nuestro equipo. Te notificaremos cuando sea aprobada.'
      },
      'Aprobado': {
        icon: CheckCircle,
        color: 'text-green-500',
        bgColor: 'bg-green-500/10',
        title: '¡Negocio aprobado!',
        description: 'Tu negocio ya está activo. Puedes acceder a tu panel de vendedor.'
      },
      'Rechazado': {
        icon: XCircle,
        color: 'text-red-500',
        bgColor: 'bg-red-500/10',
        title: 'Solicitud rechazada',
        description: 'Tu solicitud fue rechazada. Contacta con soporte para más información.'
      }
    };

    const config = statusConfig[existingVendor.status as keyof typeof statusConfig] || statusConfig['Pendiente'];
    const StatusIcon = config.icon;

    return (
      <Card>
        <CardContent className="pt-6">
          <div className="text-center space-y-4">
            <div className={`w-20 h-20 ${config.bgColor} rounded-full flex items-center justify-center mx-auto`}>
              <StatusIcon className={`w-10 h-10 ${config.color}`} />
            </div>
            <div>
              <h3 className="text-xl font-semibold">{config.title}</h3>
              <p className="text-muted-foreground mt-1">{existingVendor.name}</p>
            </div>
            <p className="text-sm text-muted-foreground max-w-md mx-auto">
              {config.description}
            </p>
            {existingVendor.status === 'Aprobado' && (
              <Button onClick={() => router.push('/vendor/dashboard')} className="mt-4">
                Ir a mi Panel de Vendedor
              </Button>
            )}
            {existingVendor.status !== 'Aprobado' && (
              <Button variant="outline" onClick={() => router.push('/')} className="mt-4">
                Volver al Inicio
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      {showHeader && (
        <CardHeader>
          <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
            <Store className="w-8 h-8 text-primary" />
          </div>
          <CardTitle className="text-2xl">Registrar mi Negocio</CardTitle>
          <CardDescription>
            Completa la información para empezar a vender en Pakiip
          </CardDescription>
        </CardHeader>
      )}
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Sección: Datos Personales */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Datos Personales</h3>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="fullName">Nombre Completo *</Label>
                <Input
                  id="fullName"
                  placeholder="Tu nombre completo"
                  value={formData.fullName}
                  onChange={(e) => handleInputChange('fullName', e.target.value)}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="phone">Teléfono *</Label>
                <Input
                  id="phone"
                  type="tel"
                  placeholder="987654321"
                  value={formData.phone}
                  onChange={(e) => handleInputChange('phone', e.target.value)}
                  required
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="dni">DNI o RUC *</Label>
              <Input
                id="dni"
                placeholder="12345678 o 20123456789"
                value={formData.dni}
                onChange={(e) => handleInputChange('dni', e.target.value)}
                required
              />
            </div>
          </div>

          {/* Sección: Datos del Negocio */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Datos del Negocio</h3>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="businessName">Nombre del Negocio *</Label>
                <Input
                  id="businessName"
                  placeholder="Ej: Restaurante El Buen Sabor"
                  value={formData.businessName}
                  onChange={(e) => handleInputChange('businessName', e.target.value)}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="category">Categoría *</Label>
                <Select value={formData.category} onValueChange={(value) => handleInputChange('category', value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona una categoría" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map((cat) => (
                      <SelectItem key={cat.id} value={cat.name}>
                        {cat.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="description">Descripción del Negocio *</Label>
              <Textarea
                id="description"
                placeholder="Describe tu negocio: qué vendes, qué te hace especial, horarios, etc."
                value={formData.description}
                onChange={(e) => handleInputChange('description', e.target.value)}
                rows={3}
                required
              />
            </div>
          </div>

          {/* Sección: Ubicación del Negocio */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Ubicación del Negocio</h3>

            <div className="space-y-2">
              <AddressAutocomplete
                label="Dirección del Local *"
                placeholder="Busca la dirección exacta de tu negocio..."
                value={address}
                onChange={setAddress}
                onSelectAddress={(result: GeocodeResult) => {
                  setAddress(result.fullAddress);
                  setCoordinates(result.coordinates);
                  extractCityFromCoords(result.coordinates);
                }}
                coordinates={coordinates}
                required
                id="business-address"
              />
              <p className="text-xs text-muted-foreground">
                Tu ciudad se detectará automáticamente de la ubicación
              </p>
              {formData.city && (
                <p className="text-sm text-primary font-medium">
                  Ciudad detectada: {formData.city}
                  {formData.sector && ` - ${formData.sector}`}
                </p>
              )}
            </div>

            <div className="space-y-2">
              <Label>Ubicación en el mapa *</Label>
              <p className="text-xs text-muted-foreground">
                Haz clic en el mapa o usa el botón de ubicación para marcar dónde está tu negocio
              </p>
              <div className="h-64 sm:h-80 w-full rounded-md border overflow-hidden">
                <LocationPickerMap
                  initialMarker={coordinates}
                  onLocationSelect={(selectedAddress, coords) => {
                    setCoordinates(coords);
                    if (selectedAddress && selectedAddress !== 'Error al obtener la dirección.' && selectedAddress !== 'No se pudo encontrar la dirección.') {
                      setAddress(selectedAddress);
                    }
                    extractCityFromCoords(coords);
                  }}
                />
              </div>
              {coordinates && coordinates.lat && coordinates.lng && (
                <p className="text-xs text-muted-foreground">
                  Coordenadas: {coordinates.lat.toFixed(6)}, {coordinates.lng.toFixed(6)}
                </p>
              )}
            </div>
          </div>

          {/* Sección: Información Bancaria / Pagos */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Información de Pago</h3>
            <p className="text-sm text-muted-foreground">
              Selecciona cómo quieres recibir los pagos de tus ventas
            </p>

            <div className="space-y-2">
              <Label htmlFor="paymentMethod">Método de Pago *</Label>
              <Select value={formData.paymentMethod} onValueChange={(value) => handleInputChange('paymentMethod', value)}>
                <SelectTrigger>
                  <SelectValue placeholder="Selecciona método de pago" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="yape">Yape</SelectItem>
                  <SelectItem value="plin">Plin</SelectItem>
                  <SelectItem value="bank">Cuenta Bancaria</SelectItem>
                </SelectContent>
              </Select>
            </div>

            {formData.paymentMethod === 'bank' && (
              <div className="space-y-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="bankName">Banco *</Label>
                    <Select value={formData.bankName} onValueChange={(value) => handleInputChange('bankName', value)}>
                      <SelectTrigger>
                        <SelectValue placeholder="Selecciona tu banco" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="BCP">BCP</SelectItem>
                        <SelectItem value="Interbank">Interbank</SelectItem>
                        <SelectItem value="BBVA">BBVA</SelectItem>
                        <SelectItem value="Scotiabank">Scotiabank</SelectItem>
                        <SelectItem value="BanBif">BanBif</SelectItem>
                        <SelectItem value="Pichincha">Pichincha</SelectItem>
                        <SelectItem value="Otro">Otro</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="bankAccount">Número de Cuenta *</Label>
                    <Input
                      id="bankAccount"
                      placeholder="Ej: 123-4567890-1-23"
                      value={formData.bankAccount}
                      onChange={(e) => handleInputChange('bankAccount', e.target.value)}
                      required
                    />
                  </div>
                </div>
              </div>
            )}

            {formData.paymentMethod === 'yape' && (
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="yapePhone">Número de Yape *</Label>
                  <Input
                    id="yapePhone"
                    type="tel"
                    placeholder="999888777"
                    value={formData.yapePhone}
                    onChange={(e) => handleInputChange('yapePhone', e.target.value)}
                    maxLength={9}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="yapeQR">Código QR de Yape (Opcional)</Label>
                  {!qrImagePreview ? (
                    <div className="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 hover:border-primary/50 transition-colors">
                      <input
                        id="yapeQR"
                        type="file"
                        accept="image/*"
                        onChange={handleQrImageChange}
                        className="hidden"
                      />
                      <label
                        htmlFor="yapeQR"
                        className="flex flex-col items-center justify-center cursor-pointer"
                      >
                        <Upload className="w-10 h-10 text-muted-foreground mb-2" />
                        <p className="text-sm font-medium text-foreground">
                          Haz clic para subir tu QR de Yape
                        </p>
                        <p className="text-xs text-muted-foreground mt-1">
                          PNG, JPG hasta 5MB
                        </p>
                      </label>
                    </div>
                  ) : (
                    <div className="relative border-2 border-primary rounded-lg p-4">
                      <div className="flex items-start gap-4">
                        <img
                          src={qrImagePreview}
                          alt="QR Preview"
                          className="w-32 h-32 object-contain rounded border"
                        />
                        <div className="flex-1">
                          <p className="text-sm font-medium mb-1">
                            QR de Yape seleccionado
                          </p>
                          <p className="text-xs text-muted-foreground mb-3">
                            {qrImage?.name}
                          </p>
                          <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            onClick={handleRemoveQrImage}
                          >
                            <X className="w-4 h-4 mr-1" />
                            Eliminar
                          </Button>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}

            {formData.paymentMethod === 'plin' && (
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="plinPhone">Número de Plin *</Label>
                  <Input
                    id="plinPhone"
                    type="tel"
                    placeholder="999888777"
                    value={formData.plinPhone}
                    onChange={(e) => handleInputChange('plinPhone', e.target.value)}
                    maxLength={9}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="plinQR">Código QR de Plin (Opcional)</Label>
                  {!qrImagePreview ? (
                    <div className="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 hover:border-primary/50 transition-colors">
                      <input
                        id="plinQR"
                        type="file"
                        accept="image/*"
                        onChange={handleQrImageChange}
                        className="hidden"
                      />
                      <label
                        htmlFor="plinQR"
                        className="flex flex-col items-center justify-center cursor-pointer"
                      >
                        <Upload className="w-10 h-10 text-muted-foreground mb-2" />
                        <p className="text-sm font-medium text-foreground">
                          Haz clic para subir tu QR de Plin
                        </p>
                        <p className="text-xs text-muted-foreground mt-1">
                          PNG, JPG hasta 5MB
                        </p>
                      </label>
                    </div>
                  ) : (
                    <div className="relative border-2 border-primary rounded-lg p-4">
                      <div className="flex items-start gap-4">
                        <img
                          src={qrImagePreview}
                          alt="QR Preview"
                          className="w-32 h-32 object-contain rounded border"
                        />
                        <div className="flex-1">
                          <p className="text-sm font-medium mb-1">
                            QR de Plin seleccionado
                          </p>
                          <p className="text-xs text-muted-foreground mb-3">
                            {qrImage?.name}
                          </p>
                          <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            onClick={handleRemoveQrImage}
                          >
                            <X className="w-4 h-4 mr-1" />
                            Eliminar
                          </Button>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>

          {/* Info adicional */}
          <div className="bg-muted p-4 rounded-lg">
            <p className="text-sm text-muted-foreground">
              <strong>Nota importante:</strong> Tu solicitud será revisada por nuestro equipo.
              Una vez aprobada, podrás empezar a gestionar tu catálogo de productos y recibir pedidos.
            </p>
          </div>

          {/* Botones */}
          <div className="flex gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={handleCancel}
              disabled={loading}
              className="flex-1"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              disabled={loading}
              className="flex-1"
            >
              {loading ? 'Registrando...' : 'Registrar Negocio'}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
