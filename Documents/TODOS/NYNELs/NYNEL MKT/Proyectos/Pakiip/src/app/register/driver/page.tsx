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
import { getAuth } from 'firebase/auth';
import { Truck, ArrowLeft } from 'lucide-react';

const VEHICLE_TYPES = [
  { value: 'Moto', label: 'Moto 🏍️' },
  { value: 'Mototaxi', label: 'Mototaxi 🛺' },
  { value: 'Coche', label: 'Coche 🚗' },
  { value: 'Bicicleta', label: 'Bicicleta 🚴' },
];

export default function DriverRegistrationPage() {
  const router = useRouter();
  const { toast } = useToast();
  const { data, setData, currentUser } = useAppData();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    fullName: '',
    phone: '',
    dni: '',
    vehicle: '',
    paymentMethod: 'bank', // 'bank' o 'yape'
    bankAccount: '',
    yapePhone: '',
    yapeQR: '', // URL del QR de Yape (opcional)
  });

  // Verificar autenticación y prellenar datos desde Google y currentUser
  useEffect(() => {
    const auth = getAuth();
    const user = auth.currentUser;

    if (!user) {
      toast({
        title: "Sesión requerida",
        description: "Debes iniciar sesión para continuar",
        variant: "destructive"
      });
      router.replace('/login');
      return;
    }

    // Prellenar nombre y teléfono
    const updates: any = {};

    if (user.displayName) {
      updates.fullName = user.displayName;
    } else if (currentUser?.name) {
      updates.fullName = currentUser.name;
    }

    if (currentUser?.phone) {
      updates.phone = currentUser.phone;
    } else if (user.phoneNumber) {
      updates.phone = user.phoneNumber;
    }

    if (Object.keys(updates).length > 0) {
      setFormData(prev => ({ ...prev, ...updates }));
    }
  }, [router, toast, currentUser]);

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      const auth = getAuth();
      const firebaseUser = auth.currentUser;

      if (!firebaseUser) {
        throw new Error('No hay usuario autenticado');
      }

      // Validaciones
      if (!formData.fullName.trim()) {
        throw new Error('El nombre completo es obligatorio');
      }
      if (!formData.phone.trim()) {
        throw new Error('El teléfono es obligatorio');
      }
      if (!formData.dni.trim()) {
        throw new Error('El DNI es obligatorio');
      }
      if (!formData.vehicle) {
        throw new Error('Debes seleccionar un tipo de vehículo');
      }
      if (!formData.bankAccount.trim()) {
        throw new Error('La cuenta bancaria es obligatoria');
      }

      // Crear nuevo driver
      const newDriver = {
        id: firebaseUser.uid,
        name: formData.fullName,
        email: firebaseUser.email || '',
        dni: formData.dni,
        phone: formData.phone,
        bankAccount: formData.bankAccount,
        vehicle: formData.vehicle as 'Moto' | 'Coche' | 'Bicicleta',
        status: 'Pendiente' as const, // Requiere aprobación de admin
        commissionRate: 0.10, // 10% por defecto - admin puede modificarlo
        debt: 0,
        debtTransactions: [],
        profileImageUrl: firebaseUser.photoURL || undefined,
      };

      // Agregar driver a la base de datos
      setData(prevData => ({
        ...prevData,
        drivers: [...prevData.drivers, newDriver]
      }));

      toast({
        title: "¡Registro exitoso!",
        description: "Tu solicitud ha sido enviada. Un administrador la revisará pronto.",
      });

      // Redirigir a welcome después de 1 segundo
      setTimeout(() => {
        router.push('/welcome');
      }, 1500);

    } catch (error: any) {
      console.error('Error al registrar driver:', error);
      toast({
        title: "Error en el registro",
        description: error.message || "No se pudo completar el registro. Intenta nuevamente.",
        variant: "destructive"
      });
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background p-4">
      <div className="max-w-3xl mx-auto py-8">
        <Button
          variant="ghost"
          className="mb-4"
          onClick={() => router.back()}
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Volver
        </Button>

        <Card>
          <CardHeader>
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
              <Truck className="w-8 h-8 text-primary" />
            </div>
            <CardTitle className="text-2xl">Ser Conductor en Pakiip</CardTitle>
            <CardDescription>
              Completa tu información para empezar a ganar dinero entregando pedidos
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Nombre Completo */}
              <div className="space-y-2">
                <Label htmlFor="fullName">Nombre Completo *</Label>
                <Input
                  id="fullName"
                  placeholder="Ej: Juan Pérez García"
                  value={formData.fullName}
                  onChange={(e) => handleInputChange('fullName', e.target.value)}
                  required
                />
              </div>

              {/* Teléfono */}
              <div className="space-y-2">
                <Label htmlFor="phone">Teléfono de Contacto *</Label>
                <Input
                  id="phone"
                  type="tel"
                  placeholder="Ej: 987654321"
                  value={formData.phone}
                  onChange={(e) => handleInputChange('phone', e.target.value)}
                  required
                />
                <p className="text-xs text-muted-foreground">
                  Usaremos este número para notificarte sobre nuevos pedidos
                </p>
              </div>

              {/* DNI */}
              <div className="space-y-2">
                <Label htmlFor="dni">DNI *</Label>
                <Input
                  id="dni"
                  placeholder="Ej: 12345678"
                  value={formData.dni}
                  onChange={(e) => handleInputChange('dni', e.target.value)}
                  maxLength={8}
                  required
                />
              </div>

              {/* Tipo de Vehículo */}
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

              {/* Método de Pago */}
              <div className="space-y-2">
                <Label htmlFor="paymentMethod">Método de Pago *</Label>
                <Select value={formData.paymentMethod} onValueChange={(value) => handleInputChange('paymentMethod', value)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona cómo recibirás tus pagos" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="bank">Cuenta Bancaria 🏦</SelectItem>
                    <SelectItem value="yape">Yape 📱</SelectItem>
                  </SelectContent>
                </Select>
                <p className="text-xs text-muted-foreground">
                  Elige cómo quieres recibir tus ganancias
                </p>
              </div>

              {/* Cuenta Bancaria - Solo si selecciona Banco */}
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
                    Formato: Banco + Número de Cuenta (donde recibirás tus pagos)
                  </p>
                </div>
              )}

              {/* Datos Yape - Solo si selecciona Yape */}
              {formData.paymentMethod === 'yape' && (
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="yapePhone">Número de Yape *</Label>
                    <Input
                      id="yapePhone"
                      type="tel"
                      placeholder="Ej: 999888777"
                      value={formData.yapePhone}
                      onChange={(e) => handleInputChange('yapePhone', e.target.value)}
                      maxLength={9}
                      required
                    />
                    <p className="text-xs text-muted-foreground">
                      Tu número de celular asociado a Yape
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="yapeQR">Código QR de Yape (Opcional)</Label>
                    <Input
                      id="yapeQR"
                      placeholder="URL de tu QR de Yape"
                      value={formData.yapeQR}
                      onChange={(e) => handleInputChange('yapeQR', e.target.value)}
                    />
                    <p className="text-xs text-muted-foreground">
                      Puedes agregar un enlace a tu código QR de Yape para pagos rápidos
                    </p>
                  </div>
                </div>
              )}

              {/* Info adicional */}
              <div className="bg-muted p-4 rounded-lg space-y-2">
                <p className="text-sm font-medium">📋 Requisitos importantes:</p>
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
                  onClick={() => router.back()}
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
      </div>
    </div>
  );
}
