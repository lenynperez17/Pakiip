'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useSession } from 'next-auth/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Loader2, ShieldCheck } from 'lucide-react';

/**
 * Página de Bootstrap para crear el primer administrador
 * Esta página permite al usuario autenticado crear su documento de admin
 * Solo debe usarse para el setup inicial
 */
export default function AdminBootstrapPage() {
  const router = useRouter();
  const { data: session, status } = useSession();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    if (status === 'loading') return;
    if (status === 'unauthenticated') {
      router.replace('/login');
    }
  }, [router, status]);

  const createAdminDocument = async () => {
    if (!session?.user) return;

    setLoading(true);
    setError(null);

    try {
      const adminData = {
        id: session.user.id || '',
        name: session.user.name || session.user.email || 'Admin',
        email: session.user.email || '',
        phone: '',
        role: 'admin',
        permissions: [
          'manage_orders',
          'manage_stores',
          'manage_drivers',
          'manage_users',
          'view_reports',
          'manage_settings'
        ],
        active: true,
        photoUrl: session.user.image || ''
      };

      // Usar la API REST para crear el admin
      const response = await fetch('/api/data/admins', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(adminData)
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || 'Error al crear admin');
      }

      setSuccess(true);

      // Esperar un momento y redirigir al dashboard de admin
      setTimeout(() => {
        router.push('/admin');
      }, 2000);

    } catch (err: any) {
      console.error('Error al crear documento de admin:', err);
      setError(err.message || 'Error al crear documento de admin');
      setLoading(false);
    }
  };

  if (status === 'loading' || !session?.user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-primary/10 to-background flex items-center justify-center p-4">
      <Card className="w-full max-w-md">
        <CardHeader>
          <div className="flex items-center justify-center mb-4">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
              <ShieldCheck className="w-8 h-8 text-primary" />
            </div>
          </div>
          <CardTitle className="text-center text-2xl">Bootstrap de Administrador</CardTitle>
          <CardDescription className="text-center">
            Crear tu cuenta de administrador en Pakiip
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {error && (
            <Alert variant="destructive">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}

          {success && (
            <Alert className="bg-green-50 text-green-900 border-green-200">
              <AlertDescription>
                ¡Cuenta de administrador creada exitosamente! Redirigiendo...
              </AlertDescription>
            </Alert>
          )}

          {!success && (
            <>
              <div className="space-y-2 text-sm">
                <p className="font-medium">Usuario autenticado:</p>
                <div className="bg-muted p-3 rounded-md space-y-1">
                  <p><span className="font-medium">Nombre:</span> {session.user.name}</p>
                  <p><span className="font-medium">Email:</span> {session.user.email}</p>
                  <p><span className="font-medium">ID:</span> <code className="text-xs">{session.user.id}</code></p>
                </div>
              </div>

              <div className="bg-yellow-50 border border-yellow-200 rounded-md p-3 text-sm text-yellow-900">
                <p className="font-medium mb-1">Importante:</p>
                <p>Esta página solo debe usarse para el setup inicial. Al hacer clic en el botón, se creará tu cuenta de administrador con permisos completos.</p>
              </div>

              <Button
                onClick={createAdminDocument}
                disabled={loading}
                className="w-full"
                size="lg"
              >
                {loading ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Creando cuenta...
                  </>
                ) : (
                  <>
                    <ShieldCheck className="mr-2 h-4 w-4" />
                    Crear mi cuenta de Administrador
                  </>
                )}
              </Button>
            </>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
