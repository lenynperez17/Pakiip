"use client";

/**
 * Pagina de Login Admin - Solo email y contrasena
 */

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
import { Loader2 } from "lucide-react";
import { signIn, useSession } from "next-auth/react";

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
  const { data: session, status } = useSession();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);

  // Redirigir si ya esta autenticado como admin
  useEffect(() => {
    if (status === "authenticated" && currentUser?.role === "admin") {
      router.push("/admin/dashboard");
    }
  }, [currentUser, status, router]);

  const handleEmailLogin = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!email || !password) {
      toast({
        title: "Campos requeridos",
        description: "Por favor ingresa email y contrasena",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);
    try {
      const result = await signIn("credentials", {
        email,
        password,
        redirect: false,
      });

      if (result?.error) {
        toast({
          title: "Error de autenticacion",
          description: result.error,
          variant: "destructive",
        });
        setLoading(false);
        return;
      }

      if (result?.ok) {
        toast({
          title: "Autenticacion exitosa",
          description: "Bienvenido al panel de administrador",
        });
        router.push("/admin/dashboard");
      }
    } catch (error) {
      console.error("Error en login:", error);
      toast({
        title: "Error",
        description: "Ocurrio un error al iniciar sesion",
        variant: "destructive",
      });
      setLoading(false);
    }
  };

  if (status === "loading") {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Loader2 className="w-8 h-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center min-h-[calc(100vh-10rem)] px-4">
      <div className="w-full mx-auto" style={{ maxWidth: "1600px" }}>
        <Card className="w-full max-w-sm mx-auto">
          <CardHeader className="text-center">
            <LoginLogo />
            <CardTitle className="text-2xl font-bold font-headline">Portal de Administrador</CardTitle>
            <CardDescription>Accede al panel para gestionar {settings.appName}</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleEmailLogin} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  placeholder="admin@pakiip.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  disabled={loading}
                  required
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Contrasena</Label>
                <Input
                  id="password"
                  type="password"
                  placeholder="********"
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
                    Iniciando sesion...
                  </>
                ) : (
                  "Iniciar Sesion"
                )}
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
