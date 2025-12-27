"use client";

import React from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ShoppingBag, Store, Truck, Shield } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';

const ROLE_INFO = {
  customer: {
    icon: ShoppingBag,
    title: 'Cliente',
    description: 'Hacer pedidos y comprar productos',
    color: 'text-blue-500',
    bgColor: 'bg-blue-500/10',
  },
  vendor: {
    icon: Store,
    title: 'Vendedor',
    description: 'Gestionar mi negocio y productos',
    color: 'text-green-500',
    bgColor: 'bg-green-500/10',
  },
  driver: {
    icon: Truck,
    title: 'Conductor',
    description: 'Ver y entregar pedidos',
    color: 'text-orange-500',
    bgColor: 'bg-orange-500/10',
  },
  admin: {
    icon: Shield,
    title: 'Administrador',
    description: 'Gestionar la plataforma',
    color: 'text-purple-500',
    bgColor: 'bg-purple-500/10',
  },
};

export default function SelectActiveRolePage() {
  const router = useRouter();
  const { availableRoles, switchRole, currentUser } = useAppData();
  const [loading, setLoading] = React.useState(false);

  // Si no hay usuario, redirigir a login
  React.useEffect(() => {
    if (!currentUser && availableRoles.length === 0) {
      router.replace('/login');
    }
  }, [currentUser, availableRoles, router]);

  // Si solo tiene un rol, redirigir directamente
  React.useEffect(() => {
    if (availableRoles.length === 1 && currentUser) {
      router.replace('/welcome');
    }
  }, [availableRoles, currentUser, router]);

  const handleRoleSelection = async (role: 'customer' | 'vendor' | 'driver' | 'admin') => {
    setLoading(true);

    try {
      const success = switchRole(role);

      if (success) {
        // Redirigir a welcome que luego redirigirá al dashboard correspondiente
        router.push('/welcome');
      } else {
        console.error('No se pudo cambiar de rol');
        setLoading(false);
      }
    } catch (error) {
      console.error('Error al cambiar de rol:', error);
      setLoading(false);
    }
  };

  // Mostrar loading mientras se verifica
  if (!currentUser || availableRoles.length === 0) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p>Cargando...</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-primary/10 to-background flex items-center justify-center p-4">
      <div className="max-w-5xl w-full">
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold mb-2">¡Hola {currentUser.name}! 👋</h1>
          <p className="text-muted-foreground text-lg">
            Tienes múltiples roles. ¿Cómo quieres entrar hoy?
          </p>
        </div>

        <div className={`grid gap-6 ${availableRoles.length === 2 ? 'md:grid-cols-2' : availableRoles.length === 3 ? 'md:grid-cols-3' : 'md:grid-cols-4'}`}>
          {availableRoles.map((role) => {
            const roleKey = role as keyof typeof ROLE_INFO;
            const info = ROLE_INFO[roleKey];
            const Icon = info.icon;

            return (
              <Card
                key={role}
                className="hover:shadow-lg transition-shadow cursor-pointer border-2 hover:border-primary"
              >
                <CardHeader>
                  <div className={`w-16 h-16 ${info.bgColor} rounded-full flex items-center justify-center mb-4 mx-auto`}>
                    <Icon className={`w-8 h-8 ${info.color}`} />
                  </div>
                  <CardTitle className="text-center">{info.title}</CardTitle>
                  <CardDescription className="text-center">
                    {info.description}
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Button
                    className="w-full"
                    onClick={() => handleRoleSelection(roleKey)}
                    disabled={loading}
                  >
                    Entrar como {info.title}
                  </Button>
                </CardContent>
              </Card>
            );
          })}
        </div>

        <div className="text-center mt-8 text-sm text-muted-foreground">
          <p>Podrás cambiar de rol en cualquier momento desde tu perfil</p>
        </div>
      </div>
    </div>
  );
}
