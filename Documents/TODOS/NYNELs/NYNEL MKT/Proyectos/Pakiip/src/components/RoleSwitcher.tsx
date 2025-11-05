"use client";

import React from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ShoppingBag, Store, Truck, Shield, RefreshCw } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';

const ROLE_INFO = {
  customer: {
    icon: ShoppingBag,
    title: 'Cliente',
    color: 'text-blue-500',
  },
  vendor: {
    icon: Store,
    title: 'Vendedor',
    color: 'text-green-500',
  },
  driver: {
    icon: Truck,
    title: 'Conductor',
    color: 'text-orange-500',
  },
  admin: {
    icon: Shield,
    title: 'Administrador',
    color: 'text-purple-500',
  },
};

export function RoleSwitcher() {
  const router = useRouter();
  const { currentUser, availableRoles, switchRole } = useAppData();

  // No mostrar si no hay usuario o solo tiene un rol
  if (!currentUser || availableRoles.length <= 1) {
    return null;
  }

  const handleSwitchRole = (role: 'customer' | 'vendor' | 'driver' | 'admin') => {
    const success = switchRole(role);
    if (success) {
      // Redirigir al dashboard correspondiente
      if (role === 'admin') {
        router.push('/admin/dashboard');
      } else if (role === 'vendor') {
        router.push('/vendor/dashboard');
      } else if (role === 'driver') {
        router.push('/driver/dashboard');
      } else {
        router.push('/');
      }
    }
  };

  const currentRoleInfo = ROLE_INFO[currentUser.role as keyof typeof ROLE_INFO];
  const CurrentIcon = currentRoleInfo.icon;

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="outline" size="sm" className="gap-2">
          <CurrentIcon className={`w-4 h-4 ${currentRoleInfo.color}`} />
          <span className="hidden sm:inline">{currentRoleInfo.title}</span>
          <RefreshCw className="w-3 h-3" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-48">
        <DropdownMenuLabel>Cambiar de rol</DropdownMenuLabel>
        <DropdownMenuSeparator />
        {availableRoles.map((role) => {
          const roleKey = role as keyof typeof ROLE_INFO;
          const info = ROLE_INFO[roleKey];
          const Icon = info.icon;
          const isCurrentRole = currentUser.role === role;

          return (
            <DropdownMenuItem
              key={role}
              onClick={() => !isCurrentRole && handleSwitchRole(roleKey)}
              disabled={isCurrentRole}
              className="cursor-pointer"
            >
              <Icon className={`w-4 h-4 mr-2 ${info.color}`} />
              <span>{info.title}</span>
              {isCurrentRole && (
                <span className="ml-auto text-xs text-muted-foreground">Actual</span>
              )}
            </DropdownMenuItem>
          );
        })}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
