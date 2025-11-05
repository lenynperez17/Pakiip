"use client";

import React from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ShoppingBag, Store, Truck } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';
import { getAuth } from 'firebase/auth';

export default function SelectRolePage() {
  const router = useRouter();
  const { currentUser } = useAppData();
  const [loading, setLoading] = React.useState(false);

  // Verificar que el usuario esté autenticado
  React.useEffect(() => {
    const auth = getAuth();
    const user = auth.currentUser;

    if (!user) {
      router.replace('/login');
    }
  }, [router]);

  const handleRoleSelection = async (role: 'customer' | 'vendor' | 'driver') => {
    setLoading(true);

    try {
      if (role === 'customer') {
        // Customer no necesita datos adicionales, ir directo a completar registro
        router.push('/complete-registration?role=customer');
      } else if (role === 'vendor') {
        // Vendor necesita formulario adicional
        router.push('/register/vendor');
      } else if (role === 'driver') {
        // Driver necesita formulario adicional
        router.push('/register/driver');
      }
    } catch (error) {
      console.error('Error al seleccionar rol:', error);
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-primary/10 to-background flex items-center justify-center p-4">
      <div className="max-w-6xl w-full">
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold mb-2">¡Bienvenido a Pakiip! 🚀</h1>
          <p className="text-muted-foreground text-lg">
            Selecciona cómo quieres usar nuestra plataforma
          </p>
        </div>

        <div className="grid md:grid-cols-3 gap-6">
          {/* Opción Customer */}
          <Card className="hover:shadow-lg transition-shadow cursor-pointer border-2 hover:border-primary">
            <CardHeader>
              <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                <ShoppingBag className="w-8 h-8 text-primary" />
              </div>
              <CardTitle className="text-center">Cliente</CardTitle>
              <CardDescription className="text-center">
                Ordena productos de tus tiendas favoritas
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 mb-6 text-sm text-muted-foreground">
                <li>✓ Navega miles de productos</li>
                <li>✓ Entrega rápida a tu puerta</li>
                <li>✓ Pagos seguros y fáciles</li>
                <li>✓ Rastreo en tiempo real</li>
              </ul>
              <Button
                className="w-full"
                onClick={() => handleRoleSelection('customer')}
                disabled={loading}
              >
                Continuar como Cliente
              </Button>
            </CardContent>
          </Card>

          {/* Opción Vendor */}
          <Card className="hover:shadow-lg transition-shadow cursor-pointer border-2 hover:border-primary">
            <CardHeader>
              <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                <Store className="w-8 h-8 text-primary" />
              </div>
              <CardTitle className="text-center">Vendedor</CardTitle>
              <CardDescription className="text-center">
                Vende tus productos y haz crecer tu negocio
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 mb-6 text-sm text-muted-foreground">
                <li>✓ Gestiona tu catálogo</li>
                <li>✓ Recibe pedidos automáticamente</li>
                <li>✓ Panel de ventas en tiempo real</li>
                <li>✓ Crece tu base de clientes</li>
              </ul>
              <Button
                className="w-full"
                onClick={() => handleRoleSelection('vendor')}
                disabled={loading}
              >
                Registrar mi Negocio
              </Button>
            </CardContent>
          </Card>

          {/* Opción Driver */}
          <Card className="hover:shadow-lg transition-shadow cursor-pointer border-2 hover:border-primary">
            <CardHeader>
              <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                <Truck className="w-8 h-8 text-primary" />
              </div>
              <CardTitle className="text-center">Conductor</CardTitle>
              <CardDescription className="text-center">
                Gana dinero entregando pedidos en tu zona
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 mb-6 text-sm text-muted-foreground">
                <li>✓ Horarios flexibles</li>
                <li>✓ Gana por cada entrega</li>
                <li>✓ Rutas optimizadas</li>
                <li>✓ Pagos semanales</li>
              </ul>
              <Button
                className="w-full"
                onClick={() => handleRoleSelection('driver')}
                disabled={loading}
              >
                Ser Conductor
              </Button>
            </CardContent>
          </Card>
        </div>

        <div className="text-center mt-8 text-sm text-muted-foreground">
          <p>Podrás agregar otros roles más adelante desde tu perfil</p>
        </div>
      </div>
    </div>
  );
}
