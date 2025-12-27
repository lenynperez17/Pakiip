"use client";

import React from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Store, Truck, Plus } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';

export function AddRoleCard() {
  const router = useRouter();
  const { currentUser, availableRoles } = useAppData();

  if (!currentUser) return null;

  // Determinar qué roles puede agregar (roles que aún no tiene)
  const canAddVendor = !availableRoles.includes('vendor');
  const canAddDriver = !availableRoles.includes('driver');

  // Si ya tiene todos los roles (excepto admin que no se puede auto-registrar), no mostrar nada
  if (!canAddVendor && !canAddDriver) {
    return null;
  }

  const handleAddRole = (role: 'vendor' | 'driver') => {
    if (role === 'vendor') {
      router.push('/register/vendor');
    } else if (role === 'driver') {
      router.push('/register/driver');
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Plus className="w-5 h-5" />
          Agregar Otro Rol
        </CardTitle>
        <CardDescription>
          Expande tus oportunidades en Pakiip
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        {canAddVendor && (
          <div className="flex items-start gap-4 p-4 border rounded-lg hover:bg-accent transition-colors">
            <div className="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center flex-shrink-0">
              <Store className="w-6 h-6 text-green-500" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold mb-1">Registrar mi Negocio</h4>
              <p className="text-sm text-muted-foreground mb-3">
                Vende tus productos y alcanza más clientes
              </p>
              <Button
                size="sm"
                onClick={() => handleAddRole('vendor')}
                className="w-full sm:w-auto"
              >
                Ser Vendedor
              </Button>
            </div>
          </div>
        )}

        {canAddDriver && (
          <div className="flex items-start gap-4 p-4 border rounded-lg hover:bg-accent transition-colors">
            <div className="w-12 h-12 bg-orange-500/10 rounded-full flex items-center justify-center flex-shrink-0">
              <Truck className="w-6 h-6 text-orange-500" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold mb-1">Ser Conductor</h4>
              <p className="text-sm text-muted-foreground mb-3">
                Gana dinero entregando pedidos en tu tiempo libre
              </p>
              <Button
                size="sm"
                onClick={() => handleAddRole('driver')}
                className="w-full sm:w-auto"
              >
                Registrarme
              </Button>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
