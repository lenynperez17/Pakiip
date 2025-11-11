"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/hooks/use-toast";
import { Shield, Loader2, CheckCircle } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { initializeFirebase, createUserWithEmail } from "@/lib/firebase";
import type { Admin } from "@/lib/placeholder-data";

export default function SetupPage() {
  const router = useRouter();
  const { toast } = useToast();
  const { appSettings, admins, saveAdmin } = useAppData();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [isInitialized, setIsInitialized] = useState(false);
  const [isChecking, setIsChecking] = useState(true);

  // Inicializar Firebase
  useEffect(() => {
    if (appSettings.firebaseConfig && !isInitialized) {
      try {
        initializeFirebase(appSettings.firebaseConfig);
        setIsInitialized(true);
      } catch (error) {
        console.error('Error al inicializar Firebase:', error);
      }
    }
  }, [appSettings.firebaseConfig, isInitialized]);

  // Verificar si ya existen administradores
  useEffect(() => {
    // Pequeño delay para asegurar que los datos se cargaron
    const timer = setTimeout(() => {
      if (admins && admins.length > 0) {
        // Ya hay administradores, redirigir a login
        router.push('/admin/login');
      } else {
        setIsChecking(false);
      }
    }, 1000);

    return () => clearTimeout(timer);
  }, [admins, router]);

  const handleSetup = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validaciones
    if (!name || !email || !phone || !password || !confirmPassword) {
      toast({
        title: "Campos requeridos",
        description: "Por favor completa todos los campos.",
        variant: "destructive"
      });
      return;
    }

    if (password !== confirmPassword) {
      toast({
        title: "Error",
        description: "Las contraseñas no coinciden.",
        variant: "destructive"
      });
      return;
    }

    if (password.length < 6) {
      toast({
        title: "Contraseña débil",
        description: "La contraseña debe tener al menos 6 caracteres.",
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
      // 1. Crear usuario en Firebase Authentication
      const authResult = await createUserWithEmail(email, password);

      if (!authResult.success) {
        toast({
          title: "Error en autenticación",
          description: authResult.error || "No se pudo crear el usuario en Firebase Auth.",
          variant: "destructive"
        });
        setLoading(false);
        return;
      }

      // 2. Crear admin en Firestore
      const newAdmin: Admin = {
        id: `ADMIN-${Date.now()}`,
        name,
        email: email.toLowerCase(),
        phone,
        permissions: [
          'manage_orders',
          'manage_stores',
          'manage_drivers',
          'manage_users',
          'view_reports',
          'manage_settings'
        ] // Permisos completos para el primer admin
      };

      // CRÍTICO: Esperar a que se guarde en Firestore ANTES de continuar
      await saveAdmin(newAdmin);

      // Esperar un momento adicional para asegurar sincronización
      await new Promise(resolve => setTimeout(resolve, 1000));

      toast({
        title: "¡Configuración completada!",
        description: "Administrador creado exitosamente. Redirigiendo...",
      });

      // 3. Redirigir a login después de 1 segundo
      setTimeout(() => {
        router.push('/admin/login');
      }, 1500);

    } catch (error: any) {
      console.error('Error durante setup:', error);
      toast({
        title: "Error inesperado",
        description: error?.message || "Ocurrió un error durante la configuración.",
        variant: "destructive"
      });
      setLoading(false);
    }
  };

  // Mostrar loading mientras verifica si ya hay admins
  if (isChecking) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin mx-auto text-primary mb-4" />
          <p className="text-muted-foreground">Verificando configuración...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center min-h-screen px-4 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
      <Card className="w-full max-w-md shadow-xl">
        <CardHeader className="text-center space-y-2">
          <div className="mx-auto w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-2">
            <Shield className="h-6 w-6 text-primary" />
          </div>
          <CardTitle className="text-2xl font-bold">Configuración Inicial</CardTitle>
          <CardDescription>
            Crea la cuenta de administrador principal para {appSettings.appName}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSetup} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="name">Nombre Completo</Label>
              <Input
                id="name"
                type="text"
                placeholder="Juan Pérez"
                value={name}
                onChange={(e) => setName(e.target.value)}
                disabled={loading}
                required
              />
            </div>

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
              <Label htmlFor="phone">Teléfono</Label>
              <Input
                id="phone"
                type="tel"
                placeholder="+51 999 999 999"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                disabled={loading}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Contraseña</Label>
              <Input
                id="password"
                type="password"
                placeholder="Mínimo 6 caracteres"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                disabled={loading}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="confirmPassword">Confirmar Contraseña</Label>
              <Input
                id="confirmPassword"
                type="password"
                placeholder="Repite la contraseña"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                disabled={loading}
                required
              />
            </div>

            <Button type="submit" className="w-full" disabled={loading}>
              {loading ? (
                <>
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                  Configurando...
                </>
              ) : (
                <>
                  <CheckCircle className="mr-2 h-4 w-4" />
                  Crear Administrador
                </>
              )}
            </Button>

            <p className="text-xs text-center text-muted-foreground mt-4">
              Esta página solo está disponible durante la configuración inicial.
              Una vez creado el primer administrador, será redirigido al login.
            </p>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
