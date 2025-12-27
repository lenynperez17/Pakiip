"use client";

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { useAppData } from '@/hooks/use-app-data';
import { useSession } from 'next-auth/react';
import { Truck, Upload, X, Clock, CheckCircle, XCircle } from 'lucide-react';
import { optimizeAndUploadImage } from '@/lib/image-optimizer';
import { LocationPickerMap } from '@/components/LocationPickerMap';
import { AddressAutocomplete } from '@/components/AddressAutocomplete';
import { GeocodeResult, reverseGeocode } from '@/lib/google-geocoding';
import type { DeliveryDriver } from '@/lib/placeholder-data';

const VEHICLE_TYPES = [
  { value: 'Moto', label: 'Moto' },
  { value: 'Mototaxi', label: 'Mototaxi' },
  { value: 'Coche', label: 'Coche' },
  { value: 'Bicicleta', label: 'Bicicleta' },
];

interface DriverRegistrationFormProps {
  onSuccess?: () => void;
  onCancel?: () => void;
  showHeader?: boolean;
  redirectAfterSuccess?: string;
}

export function DriverRegistrationForm({
  onSuccess,
  onCancel,
  showHeader = true,
  redirectAfterSuccess = '/welcome'
}: DriverRegistrationFormProps) {
  const router = useRouter();
  const { toast } = useToast();
  const { saveDriver, currentUser, cities, drivers } = useAppData();
  const { data: session, status } = useSession();
  const [loading, setLoading] = useState(false);
  const [existingDriver, setExistingDriver] = useState<{ status: string; name: string } | null>(null);
  const [qrImage, setQrImage] = useState<File | null>(null);
  const [qrImagePreview, setQrImagePreview] = useState<string>('');

  const [formData, setFormData] = useState({
    // Datos del usuario (prellenados)
    fullName: '',
    phone: '',
    dni: '',
    city: '',
    sector: '',
    // Datos específicos del conductor
    vehicle: '',
    paymentMethod: 'yape',
    bankAccount: '',
    yapePhone: '',
    plinPhone: '',
  });

  const [coordinates, setCoordinates] = useState<{ lat: number; lng: number } | null>(null);
  const [address, setAddress] = useState('');

  // Verificar si ya está registrado como driver
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

    // Buscar si ya existe un driver con este ID
    const existing = drivers.find(d => d.id === userId);
    if (existing) {
      setExistingDriver({
        status: existing.status || 'Pendiente',
        name: existing.name || 'Repartidor'
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

    // Prellenar coordenadas y dirección si existen
    if (currentUser?.coordinates) {
      setCoordinates(currentUser.coordinates);
    }
    if (currentUser?.address) {
      setAddress(currentUser.address);
    }
  }, [router, toast, currentUser, drivers, session, status]);

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
          // Buscar ciudad que coincida en nuestras ciudades registradas
          const matchedCity = cities.find(c =>
            detectedLocality.toLowerCase().includes(c.name.toLowerCase()) ||
            c.name.toLowerCase().includes(detectedLocality.toLowerCase())
          );
          if (matchedCity) {
            setFormData(prev => ({ ...prev, city: matchedCity.name }));
            // Buscar sector si existe
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
            // Si no hay match exacto, usar la localidad detectada
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
    setFormData(prev => ({ ...prev, yapeQR: '' }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (!session?.user) {
        throw new Error('No hay usuario autenticado');
      }

      const sessionUser = session.user;
      const userId = sessionUser.id || '';

      // Validaciones de datos personales
      if (!formData.fullName.trim()) {
        throw new Error('El nombre completo es obligatorio');
      }
      if (!formData.phone.trim()) {
        throw new Error('El teléfono es obligatorio');
      }
      if (!formData.dni.trim()) {
        throw new Error('El DNI es obligatorio');
      }

      // Convertir nombre a mayúsculas y verificar duplicados
      const driverNameUpper = formData.fullName.trim().toUpperCase();
      const existingDriverWithName = drivers.find(d =>
        d.name?.toUpperCase() === driverNameUpper && d.id !== userId
      );
      if (existingDriverWithName) {
        throw new Error(`Ya existe un repartidor con el nombre "${driverNameUpper}". Por favor verifica tus datos.`);
      }

      // Validaciones específicas del conductor
      if (!formData.vehicle) {
        throw new Error('Debes seleccionar un tipo de vehículo');
      }

      if (formData.paymentMethod === 'bank') {
        if (!formData.bankAccount.trim()) {
          throw new Error('La cuenta bancaria es obligatoria');
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

      let qrImageUrl = '';
      if (qrImage && qrImagePreview && (formData.paymentMethod === 'yape' || formData.paymentMethod === 'plin')) {
        toast({
          title: "Subiendo imagen QR...",
          description: "Por favor espera un momento",
        });

        const uploadResult = await optimizeAndUploadImage(
          qrImagePreview,
          `drivers/${userId}/qr-${Date.now()}.webp`
        );
        if (uploadResult.success && uploadResult.url) {
          qrImageUrl = uploadResult.url;
        }
      }

      const newDriver: Record<string, unknown> = {
        id: userId,
        name: driverNameUpper,
        email: sessionUser.email || '',
        dni: formData.dni,
        phone: formData.phone,
        vehicle: formData.vehicle as 'Moto' | 'Mototaxi' | 'Coche' | 'Bicicleta',
        status: 'Pendiente' as const,
        commissionRate: 0.10,
        debt: 0,
        debtTransactions: [],
        profileImageUrl: sessionUser.image || undefined,
        city: formData.city,
        sector: formData.sector,
        address: address,
        coordinates: coordinates,
      };

      newDriver.paymentMethod = formData.paymentMethod;
      if (formData.paymentMethod === 'bank') {
        newDriver.bankAccount = formData.bankAccount;
      } else if (formData.paymentMethod === 'yape') {
        newDriver.yapePhone = formData.yapePhone;
        if (qrImageUrl) {
          newDriver.yapeQR = qrImageUrl;
        }
      } else if (formData.paymentMethod === 'plin') {
        newDriver.plinPhone = formData.plinPhone;
        if (qrImageUrl) {
          newDriver.plinQR = qrImageUrl;
        }
      }

      // Guardar driver (saveDriver no retorna nada, lanza excepción en error)
      saveDriver(newDriver as DeliveryDriver);

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
      console.error('Error al registrar driver:', error);
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
  if (existingDriver) {
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
        title: '¡Ya eres repartidor!',
        description: 'Tu cuenta de repartidor está activa. Puedes acceder a tu panel.'
      },
      'Rechazado': {
        icon: XCircle,
        color: 'text-red-500',
        bgColor: 'bg-red-500/10',
        title: 'Solicitud rechazada',
        description: 'Tu solicitud fue rechazada. Contacta con soporte para más información.'
      }
    };

    const config = statusConfig[existingDriver.status as keyof typeof statusConfig] || statusConfig['Pendiente'];
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
              <p className="text-muted-foreground mt-1">{existingDriver.name}</p>
            </div>
            <p className="text-sm text-muted-foreground max-w-md mx-auto">
              {config.description}
            </p>
            {existingDriver.status === 'Aprobado' && (
              <Button onClick={() => router.push('/driver/dashboard')} className="mt-4">
                Ir a mi Panel de Repartidor
              </Button>
            )}
            {existingDriver.status !== 'Aprobado' && (
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
            <Truck className="w-8 h-8 text-primary" />
          </div>
          <CardTitle className="text-2xl">Ser Repartidor en Pakiip</CardTitle>
          <CardDescription>
            Completa tu información para empezar a ganar dinero entregando pedidos
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
              <Label htmlFor="dni">DNI *</Label>
              <Input
                id="dni"
                placeholder="12345678"
                value={formData.dni}
                onChange={(e) => handleInputChange('dni', e.target.value)}
                maxLength={8}
                required
              />
            </div>
          </div>

          {/* Sección: Datos del Vehículo */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Datos del Vehículo</h3>

            <div className="space-y-2">
              <Label htmlFor="vehicle">Tipo de Vehículo *</Label>
              <Select value={formData.vehicle} onValueChange={(value) => handleInputChange('vehicle', value)}>
                <SelectTrigger>
                  <SelectValue placeholder="Selecciona tu vehículo" />
                </SelectTrigger>
                <SelectContent>
                  {VEHICLE_TYPES.map((vehicle) => (
                    <SelectItem key={vehicle.value} value={vehicle.value}>
                      {vehicle.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-xs text-muted-foreground">
                Esto ayuda a asignar rutas adecuadas para tu vehículo
              </p>
            </div>
          </div>

          {/* Sección: Método de Pago */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Método de Pago</h3>
            <p className="text-sm text-muted-foreground">
              Selecciona cómo quieres recibir tus ganancias
            </p>

            <div className="space-y-2">
              <Label htmlFor="paymentMethod">¿Cómo recibirás tus pagos? *</Label>
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
              <div className="space-y-2">
                <Label htmlFor="bankAccount">Cuenta Bancaria *</Label>
                <Input
                  id="bankAccount"
                  placeholder="Ej: BCP 123-4567890-1-23"
                  value={formData.bankAccount}
                  onChange={(e) => handleInputChange('bankAccount', e.target.value)}
                  required
                />
                <p className="text-xs text-muted-foreground">
                  Formato: Banco + Número de Cuenta
                </p>
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

          {/* Sección: Ubicación en Mapa */}
          <div className="space-y-4">
            <h3 className="font-semibold text-lg border-b pb-2">Tu Ubicación</h3>

            <div className="space-y-2">
              <AddressAutocomplete
                label="Dirección"
                placeholder="Busca tu dirección..."
                value={address}
                onChange={setAddress}
                onSelectAddress={(result: GeocodeResult) => {
                  setAddress(result.fullAddress);
                  setCoordinates(result.coordinates);
                  // Extraer ciudad automáticamente
                  extractCityFromCoords(result.coordinates);
                }}
                coordinates={coordinates}
                id="driver-address"
              />
              <p className="text-xs text-muted-foreground">
                Tu ciudad se detectará automáticamente de tu ubicación
              </p>
              {formData.city && (
                <p className="text-sm text-primary font-medium">
                  Ciudad detectada: {formData.city}
                  {formData.sector && ` - ${formData.sector}`}
                </p>
              )}
            </div>

            <div className="space-y-2">
              <Label>Ubicación en el mapa</Label>
              <p className="text-xs text-muted-foreground">
                También puedes hacer clic en el mapa o usar el botón de ubicación
              </p>
              <div className="h-64 sm:h-80 w-full rounded-md border overflow-hidden">
                <LocationPickerMap
                  initialMarker={coordinates}
                  onLocationSelect={(selectedAddress, coords) => {
                    setCoordinates(coords);
                    if (selectedAddress && selectedAddress !== 'Error al obtener la dirección.' && selectedAddress !== 'No se pudo encontrar la dirección.') {
                      setAddress(selectedAddress);
                    }
                    // Extraer ciudad automáticamente
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

          {/* Info adicional */}
          <div className="bg-muted p-4 rounded-lg space-y-2">
            <p className="text-sm font-medium">Requisitos importantes:</p>
            <ul className="text-sm text-muted-foreground space-y-1 list-disc list-inside">
              <li>Licencia de conducir vigente (Moto o Coche)</li>
              <li>Vehículo en buen estado</li>
              <li>Smartphone con internet</li>
              <li>Disponibilidad de horarios flexibles</li>
            </ul>
            <p className="text-sm text-muted-foreground mt-3">
              <strong>Nota:</strong> Tu solicitud será revisada por nuestro equipo.
              Una vez aprobada, podrás empezar a recibir pedidos y ganar dinero.
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
              {loading ? 'Registrando...' : 'Enviar Solicitud'}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
