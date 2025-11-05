"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { Logo } from "@/components/icons/Logo";
import { useAppData } from "@/hooks/use-app-data";
import { initializeFirebase, signInWithGoogle, signInWithEmailPassword } from "@/lib/firebase";
import { Loader2 } from "lucide-react";

function LoginLogo() {
  const { appSettings: settings } = useAppData();
  if (settings.logoUrl) {
    return <Image src={settings.logoUrl} alt={settings.appName} width={40} height={40} className="mx-auto h-10 w-10 object-contain mb-2" />
  }
  return <Logo className="mx-auto h-10 w-10 text-primary mb-2" />;
}

export default function AdminLoginPage() {
  const router = useRouter();
  const { toast } = useToast();
  const { appSettings: settings, currentUser } = useAppData();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [isInitialized, setIsInitialized] = useState(false);

  // Inicializar Firebase
  useEffect(() => {
    if (settings.firebaseConfig && !isInitialized) {
      try {
        initializeFirebase(settings.firebaseConfig);
        setIsInitialized(true);
      } catch (error) {
        console.error('Error al inicializar Firebase:', error);
      }
    }
  }, [settings.firebaseConfig, isInitialized]);

  // Redirigir si ya está autenticado como admin
  useEffect(() => {
    if (currentUser && currentUser.role === 'admin') {
      router.push('/admin/dashboard');
    }
  }, [currentUser, router]);

  const handleGoogleLogin = async () => {
    if (!isInitialized) {
      toast({
        title: "Inicializando...",
        description: "Por favor espera un momento.",
      });
      return;
    }

    setLoading(true);
    try {
      const result = await signInWithGoogle();

      if (result.success && result.user) {
        toast({
          title: "Autenticación exitosa",
          description: "Verificando permisos de administrador...",
        });
        // El useEffect (líneas 46-50) se encargará de redirigir al dashboard correcto
        // solo si el usuario tiene role === 'admin'
      } else {
        toast({
          title: "Error de autenticación",
          description: result.error || "No se pudo iniciar sesión con Google.",
          variant: "destructive"
        });
      }
    } catch (error) {
      console.error('Error en Google Sign In:', error);
      toast({
        title: "Error inesperado",
        description: "Ocurrió un error al intentar iniciar sesión.",
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  const handleEmailLogin = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!email || !password) {
      toast({
        title: "Campos requeridos",
        description: "Por favor ingresa email y contraseña.",
        variant: "destructive"
      });
      return;
    }

    if (!isInitialized) {
      toast({
        title: "Inicializando...",
        description: "Por favor espera un momento.",
      });
      return;
    }

    setLoading(true);
    try {
      const result = await signInWithEmailPassword(email, password);

      if (result.success && result.user) {
        toast({
          title: "Autenticación exitosa",
          description: "Verificando permisos de administrador...",
        });
        // El useEffect (líneas 46-50) se encargará de redirigir al dashboard correcto
        // solo si el usuario tiene role === 'admin'
      } else {
        toast({
          title: "Error de autenticación",
          description: result.error || "Email o contraseña incorrectos.",
          variant: "destructive"
        });
      }
    } catch (error) {
      console.error('Error en Email Sign In:', error);
      toast({
        title: "Error inesperado",
        description: "Ocurrió un error al intentar iniciar sesión.",
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-[calc(100vh-10rem)] px-4">
      <div className="w-full mx-auto" style={{ maxWidth: '1600px' }}>
        <Card className="w-full max-w-sm mx-auto">
          <CardHeader className="text-center">
            <LoginLogo />
            <CardTitle className="text-2xl font-bold font-headline">Portal de Administrador</CardTitle>
            <CardDescription>Accede al panel para gestionar {settings.appName}</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {/* Formulario de Email/Password */}
            <form onSubmit={handleEmailLogin} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="admin@example.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  disabled={loading}
                  required
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Contraseña</Label>
                <Input
                  id="password"
                  type="password"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  disabled={loading}
                  required
                />
              </div>
              <Button type="submit" className="w-full" disabled={loading}>
                {loading ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Iniciando sesión...
                  </>
                ) : (
                  "Iniciar Sesión"
                )}
              </Button>
            </form>

            {/* Divisor */}
            <div className="relative">
              <div className="absolute inset-0 flex items-center">
                <span className="w-full border-t" />
              </div>
              <div className="relative flex justify-center text-xs uppercase">
                <span className="bg-background px-2 text-muted-foreground">O continúa con</span>
              </div>
            </div>

            {/* Botón de Google */}
            <Button
              onClick={handleGoogleLogin}
              variant="outline"
              className="w-full"
              disabled={loading}
              type="button"
            >
              {loading ? (
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
              ) : (
                <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                  />
                  <path
                    fill="currentColor"
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                  />
                  <path
                    fill="currentColor"
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                  />
                  <path
                    fill="currentColor"
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                  />
                </svg>
              )}
              Continuar con Google
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
