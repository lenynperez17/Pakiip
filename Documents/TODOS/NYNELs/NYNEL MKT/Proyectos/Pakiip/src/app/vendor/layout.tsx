
'use client';

import Link from 'next/link';
import Image from 'next/image';
import { useAppData } from '@/hooks/use-app-data';
import { Button } from '@/components/ui/button';
import { LogOut, Home, User } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { useRouter, usePathname } from 'next/navigation';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Logo } from '@/components/icons/Logo';
import { AuthGuard } from "@/components/AuthGuard";
import React from 'react';

function VendorLogo() {
  const { appSettings: settings } = useAppData();
  if (settings.logoUrl) {
    return <Image src={settings.logoUrl} alt={settings.appName} width={24} height={24} className="h-6 w-6 object-contain" />
  }
  return <Logo className="h-6 w-6 text-primary" />;
}

export default function VendorLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const { currentUser, logout } = useAppData();
  const router = useRouter();
  const pathname = usePathname();

  const handleLogout = async () => {
    await logout();
    router.push('/');
  }

  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
  }

  // No aplicar AuthGuard en páginas públicas (login y registro)
  const isPublicPage = pathname === '/vendor/login' || pathname === '/vendor/register' || pathname?.startsWith('/vendor/') && pathname.split('/').length === 3 && pathname !== '/vendor/dashboard';

  // Si es página pública, renderizar directamente sin AuthGuard
  if (isPublicPage) {
    return (
      <div className="vendor-layout flex min-h-screen w-full flex-col bg-muted/40">
        <main className="vendor-main flex flex-1 flex-col gap-4 px-4 sm:px-6 py-4 md:gap-8 md:py-6 lg:px-8 w-full max-w-7xl mx-auto">
          {children}
        </main>
      </div>
    );
  }

  // Para el resto de rutas de vendor, aplicar AuthGuard
  return (
    <AuthGuard requireAuth={true} requireRole="vendor" redirectTo="/vendor/login">
      <div className="vendor-layout flex min-h-screen w-full flex-col bg-muted/40">
       <header className="sticky top-0 z-30 flex h-14 items-center gap-4 border-b bg-background px-4 sm:static sm:h-auto sm:border-0 sm:bg-transparent sm:px-6">
        <div className="flex w-full items-center justify-between">
            <div className="flex items-center gap-2 font-semibold">
              <VendorLogo />
              <span className="font-headline text-xl font-bold">Vendedor</span>
            </div>
            <div className="flex items-center gap-4">
               <Button variant="outline" size="sm" asChild>
                    <Link href="/"><Home className="mr-2 h-4 w-4"/> Ir a Inicio</Link>
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" size="icon" className="overflow-hidden rounded-full">
                           {currentUser ? (
                                <Avatar className="h-8 w-8">
                                    <AvatarFallback>{getInitials(currentUser.name)}</AvatarFallback>
                                </Avatar>
                            ) : (
                                <User className="h-5 w-5" />
                            )}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {currentUser && (
                            <>
                            <DropdownMenuLabel>{currentUser.name}</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            </>
                        )}
                        <DropdownMenuItem onClick={handleLogout}>
                            <LogOut className="mr-2 h-4 w-4" />
                            Cerrar Sesión
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
      </header>
      <main className="vendor-main flex flex-1 flex-col gap-4 px-4 sm:px-6 py-4 md:gap-8 md:py-6 lg:px-8 w-full max-w-7xl mx-auto">
        {children}
      </main>
    </div>
    </AuthGuard>
  );
}
