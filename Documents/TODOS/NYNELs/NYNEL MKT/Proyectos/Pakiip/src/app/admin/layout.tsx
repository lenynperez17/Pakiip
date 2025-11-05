
'use client';

import Link from 'next/link';
import Image from 'next/image';
import { useAppData } from '@/hooks/use-app-data';
import { Button } from '@/components/ui/button';
import { LogOut, Home, User } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { useRouter, usePathname } from 'next/navigation';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { AuthGuard } from "@/components/AuthGuard";


function AdminLogo() {
  const { appSettings: settings } = useAppData();
  return (
    <div className="flex items-center gap-2 font-semibold">
      <div className="flex h-8 w-8 items-center justify-center rounded-md bg-primary text-primary-foreground font-bold">
        A
      </div>
      <span className="font-headline text-xl font-bold">{settings.appName}</span>
    </div>
  )
}

export default function AdminLayout({
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

  // No aplicar AuthGuard en la página de login
  const isLoginPage = pathname === '/admin/login';

  // Si es la página de login, renderizar directamente sin AuthGuard
  if (isLoginPage) {
    return (
      <div className="admin-layout flex min-h-screen w-full flex-col bg-muted/40">
        <main className="admin-main flex flex-1 flex-col gap-4 px-4 sm:px-6 py-4 md:gap-8 md:py-6 lg:px-8 w-full max-w-7xl mx-auto">
          {children}
        </main>
      </div>
    );
  }

  // Para el resto de rutas de admin, aplicar AuthGuard
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <div className="admin-layout flex min-h-screen w-full flex-col bg-muted/40">
         <header className="sticky top-0 z-30 flex h-14 items-center gap-4 border-b bg-background px-4 sm:static sm:h-auto sm:border-0 sm:bg-transparent sm:px-6">
          <div className="flex w-full items-center justify-between">
              <AdminLogo />
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
        <main className="admin-main flex flex-1 flex-col gap-4 px-4 sm:px-6 py-4 md:gap-8 md:py-6 lg:px-8 w-full max-w-7xl mx-auto">
          {children}
        </main>
      </div>
    </AuthGuard>
  );
}
