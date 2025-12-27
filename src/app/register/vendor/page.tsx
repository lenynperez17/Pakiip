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
import { Store, ArrowLeft } from 'lucide-react';
import { AddressAutocomplete } from '@/components/AddressAutocomplete';
import { GeocodeResult } from '@/lib/google-geocoding';
import type { Vendor } from '@/lib/placeholder-data';

export default function VendorRegistrationPage() {
  const router = useRouter();
  const { toast } = useToast();
  // Usar categorías desde la API
  const { saveVendor, switchRole, currentUser, categories, cities } = useAppData();
  const { data: session, status } = useSession();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    businessName: '',
    phone: '',
    dni: '',
    description: '',
    category: '',
    address: '',
    city: '',
  });
  const [coordinates, setCoordinates] = useState<{ lat: number; lng: number } | null>(null);

  // Verificar autenticación y pre-llenar datos
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

    // Pre-llenar teléfono si existe en currentUser
    if (currentUser && 'phone' in currentUser && currentUser.phone) {
      setFormData(prev => ({ ...prev, phone: currentUser.phone || '' }));
    }
  }, [router, toast, currentUser, session, status]);

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (!session?.user) {
        throw new Error('No hay usuario autenticado');
      }

      const sessionUser = session.user;

      // Validaciones
      if (!formData.businessName.trim()) {
        throw new Error('El nombre del negocio es obligatorio');
      }
      if (!formData.phone.trim()) {
        throw new Error('El teléfono es obligatorio');
      }
      if (!formData.dni.trim()) {
        throw new Error('El DNI/RUC es obligatorio');
      }
      if (!formData.description.trim()) {
        throw new Error('La descripción es obligatoria');
      }
      if (!formData.category) {
        throw new Error('Debes seleccionar una categoría');
      }
      if (!formData.address.trim()) {
        throw new Error('La dirección es obligatoria');
      }
      if (!formData.city.trim()) {
        throw new Error('La ciudad es obligatoria');
      }

      // Crear nuevo vendor
      const newVendor: Vendor = {
        id: sessionUser.id || `v${Date.now()}`,
        ownerId: sessionUser.id || '', // ID del propietario
        name: formData.businessName,
        email: sessionUser.email || '',
        phone: formData.phone,
        dni: formData.dni,
        description: formData.description,
        category: formData.category,
        imageUrl: 'https://placehold.co/64x64.png', // Placeholder inicial
        logoUrl: '', // Vacío inicialmente - se puede subir después
        bannerUrl: '', // Vacío inicialmente - se puede subir después
        qrPaymentImageUrl: '', // Vacío inicialmente - se puede subir después
        address: formData.address,
        location: formData.city,
        coordinates: coordinates || { lat: -12.0464, lng: -77.0428 }, // Usa coordenadas reales o default Lima, Peru
        products: [],
        productCategories: [],
        isFeatured: false,
        commissionRate: 0.15, // 15% por defecto - admin puede modificarlo
        status: 'Pendiente', // Requiere aprobación de admin
        additionalFee: 0,
        businessName: formData.businessName,
      };

      // Guardar vendor
      saveVendor(newVendor);


      // 🔥 NO LLAMAR switchRole AQUÍ - El vendor tiene status='Pendiente' y requiere aprobación del admin
      // El usuario verá un mensaje de "Esperando aprobación" en su dashboard

      toast({
        title: "¡Registro exitoso!",
        description: "Tu solicitud ha sido enviada al administrador. Te notificaremos cuando sea aprobada.",
      });

      // Redirigir a welcome después de 1 segundo
      setTimeout(() => {
        router.push('/welcome');
      }, 1500);

    } catch (error: any) {
      console.error('Error al registrar vendor:', error);
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
              <Store className="w-8 h-8 text-primary" />
            </div>
            <CardTitle className="text-2xl">Registrar mi Negocio</CardTitle>
            <CardDescription>
              Completa la información de tu negocio para empezar a vender en Pakiip
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Nombre del Negocio */}
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
              </div>

              {/* DNI/RUC */}
              <div className="space-y-2">
                <Label htmlFor="dni">DNI o RUC *</Label>
                <Input
                  id="dni"
                  placeholder="Ej: 12345678 o 20123456789"
                  value={formData.dni}
                  onChange={(e) => handleInputChange('dni', e.target.value)}
                  required
                />
              </div>

              {/* Categoría */}
              <div className="space-y-2">
                <Label htmlFor="category">Categoría del Negocio *</Label>
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

              {/* Descripción */}
              <div className="space-y-2">
                <Label htmlFor="description">Descripción de tu Negocio *</Label>
                <Textarea
                  id="description"
                  placeholder="Describe tu negocio: qué vendes, qué te hace especial, horarios, etc."
                  value={formData.description}
                  onChange={(e) => handleInputChange('description', e.target.value)}
                  rows={4}
                  required
                />
              </div>

              {/* Dirección */}
              <AddressAutocomplete
                label="Dirección del Negocio"
                placeholder="Busca la dirección de tu negocio..."
                value={formData.address}
                onChange={(value) => handleInputChange('address', value)}
                onSelectAddress={(result: GeocodeResult) => {
                  // Guardar coordenadas exactas para mejor ubicación
                  setCoordinates(result.coordinates);
                }}
                required
                id="address"
              />

              {/* Ciudad */}
              <div className="space-y-2">
                <Label htmlFor="city">Ciudad *</Label>
                <Input
                  id="city"
                  placeholder="Ej: Lima"
                  value={formData.city}
                  onChange={(e) => handleInputChange('city', e.target.value)}
                  required
                />
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
                  {loading ? 'Registrando...' : 'Registrar Negocio'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
