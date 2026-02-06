"use client";

import React from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Store, Truck, Plus, Clock, CheckCircle } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';
import { useSession } from 'next-auth/react';

export function AddRoleCard() {
  const router = useRouter();
  const { currentUser, availableRoles, getVendorByOwnerId, getDriverByUserId } = useAppData();
  const { data: session } = useSession();

  if (!currentUser) return null;

  // Verificar si el usuario ya tiene un vendor o driver registrado (pendiente o activo)
  const userId = session?.user?.id;
  const existingVendor = userId ? getVendorByOwnerId(userId) : undefined;
  const existingDriver = userId ? getDriverByUserId(userId) : undefined;

  // Determinar qué roles puede agregar
  // No puede agregar si ya tiene el rol O si tiene una solicitud pendiente
  const hasVendorRole = availableRoles.includes('vendor');
  const hasDriverRole = availableRoles.includes('driver');
  const hasPendingVendor = existingVendor?.status === 'Pendiente';
  const hasPendingDriver = existingDriver?.status === 'Pendiente';
  const hasActiveVendor = existingVendor?.status === 'Activo';
  const hasActiveDriver = existingDriver?.status === 'Activo';

  // Mostrar opción de vendor si no tiene el rol y no tiene solicitud pendiente/activa
  const canAddVendor = !hasVendorRole && !existingVendor;
  const canAddDriver = !hasDriverRole && !existingDriver;

  // Si ya tiene todos los roles y no hay pendientes, no mostrar nada
  if (!canAddVendor && !canAddDriver && !hasPendingVendor && !hasPendingDriver) {
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
        {/* Vendor pendiente */}
        {hasPendingVendor && (
          <div className="flex items-start gap-4 p-4 border border-yellow-500/30 rounded-lg bg-yellow-500/5">
            <div className="w-12 h-12 bg-yellow-500/10 rounded-full flex items-center justify-center flex-shrink-0">
              <Clock className="w-6 h-6 text-yellow-500" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold mb-1 text-yellow-700 dark:text-yellow-300">Solicitud en Revisión</h4>
              <p className="text-sm text-muted-foreground mb-3">
                Tu negocio <strong>{existingVendor?.name}</strong> está siendo revisado por nuestro equipo.
              </p>
              <Button size="sm" variant="outline" disabled className="w-full sm:w-auto">
                En Revisión...
              </Button>
            </div>
          </div>
        )}

        {/* Vendor activo pero sin rol asignado todavía */}
        {hasActiveVendor && !hasVendorRole && (
          <div className="flex items-start gap-4 p-4 border border-green-500/30 rounded-lg bg-green-500/5">
            <div className="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center flex-shrink-0">
              <CheckCircle className="w-6 h-6 text-green-500" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold mb-1 text-green-700 dark:text-green-300">¡Negocio Aprobado!</h4>
              <p className="text-sm text-muted-foreground mb-3">
                <strong>{existingVendor?.name}</strong> fue aprobado. Ya puedes acceder a tu panel.
              </p>
              <Button size="sm" onClick={() => router.push('/vendor/dashboard')} className="w-full sm:w-auto">
                Ir a mi Panel
              </Button>
            </div>
          </div>
        )}

        {/* Puede registrar vendor */}
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

        {/* Driver pendiente */}
        {hasPendingDriver && (
          <div className="flex items-start gap-4 p-4 border border-yellow-500/30 rounded-lg bg-yellow-500/5">
            <div className="w-12 h-12 bg-yellow-500/10 rounded-full flex items-center justify-center flex-shrink-0">
              <Clock className="w-6 h-6 text-yellow-500" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold mb-1 text-yellow-700 dark:text-yellow-300">Solicitud en Revisión</h4>
              <p className="text-sm text-muted-foreground mb-3">
                Tu solicitud como conductor está siendo revisada.
              </p>
              <Button size="sm" variant="outline" disabled className="w-full sm:w-auto">
                En Revisión...
              </Button>
            </div>
          </div>
        )}

        {/* Driver activo pero sin rol asignado todavía */}
        {hasActiveDriver && !hasDriverRole && (
          <div className="flex items-start gap-4 p-4 border border-green-500/30 rounded-lg bg-green-500/5">
            <div className="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center flex-shrink-0">
              <CheckCircle className="w-6 h-6 text-green-500" />
            </div>
            <div className="flex-1 min-w-0">
              <h4 className="font-semibold mb-1 text-green-700 dark:text-green-300">¡Conductor Aprobado!</h4>
              <p className="text-sm text-muted-foreground mb-3">
                Ya puedes empezar a realizar entregas.
              </p>
              <Button size="sm" onClick={() => router.push('/driver/dashboard')} className="w-full sm:w-auto">
                Ir a mi Panel
              </Button>
            </div>
          </div>
        )}

        {/* Puede registrar driver */}
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
