
"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { useAppData } from "@/hooks/use-app-data";
import { Phone } from "lucide-react";
import { useState, useEffect } from "react";
import { initializeFirebase, signInWithGoogle } from "@/lib/firebase";
import { PhoneAuthModal } from "@/components/PhoneAuthModal";

export default function LoginPage() {
  const router = useRouter();
  const { toast } = useToast();
  const { appSettings: settings, currentUser, getUserRoles } = useAppData();
  const [isPhoneModalOpen, setIsPhoneModalOpen] = useState(false);
  const [isInitialized, setIsInitialized] = useState(false);

  const searchParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
  const returnUrl = searchParams?.get('returnUrl') || '/';

  // Inicializar Firebase cuando el componente se monta
  useEffect(() => {
    if (settings.firebaseConfig && !isInitialized) {
      try {
        initializeFirebase(settings.firebaseConfig);
        setIsInitialized(true);
      } catch (error) {
        console.error('Error al inicializar Firebase:', error);
        toast({
          title: "Error de configuración",
          description: "No se pudo inicializar el sistema de autenticación. Por favor, contacta al administrador.",
          variant: "destructive"
        });
      }
    }
  }, [settings.firebaseConfig, isInitialized, toast]);

  const handleGoogleLogin = async () => {
    // Verificar que Firebase esté configurado
    if (!settings.firebaseConfig) {
      toast({
        title: "Configuración pendiente",
        description: "El inicio de sesión con Google no está configurado. Por favor, contacta al administrador.",
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

    toast({
      title: "Iniciando sesión con Google",
      description: "Abriendo ventana de autenticación...",
    });

    try {
      const result = await signInWithGoogle();

      if (result.success && result.user) {
        toast({
          title: "¡Bienvenido!",
          description: `Hola ${result.user.displayName || 'Usuario'}`,
        });

        // Esperar un momento a que el hook procese el usuario
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Verificar roles disponibles del usuario
        const userRoles = getUserRoles(result.user.email || '');

        // Redirigir según el estado del usuario
        if (returnUrl && returnUrl !== '/') {
          router.push(returnUrl);
        } else if (userRoles.length === 0) {
          // Usuario nuevo sin ningún rol → ir a seleccionar rol inicial
          router.push('/select-role');
        } else if (userRoles.length > 1) {
          // Usuario con múltiples roles → ir a seleccionar rol activo
          router.push('/select-active-role');
        } else {
          // Usuario con un solo rol → ir directo a welcome
          router.push('/welcome');
        }
      } else {
        toast({
          title: "Error al iniciar sesión",
          description: result.error || "No se pudo completar el inicio de sesión con Google.",
          variant: "destructive"
        });
      }
    } catch (error) {
      console.error('Error en Google Sign In:', error);
      toast({
        title: "Error inesperado",
        description: "Ocurrió un error al intentar iniciar sesión. Por favor, intenta nuevamente.",
        variant: "destructive"
      });
    }
  };

  const handlePhoneLogin = () => {
    // Verificar que Firebase esté configurado
    if (!settings.firebaseConfig) {
      toast({
        title: "Configuración pendiente",
        description: "El inicio de sesión por teléfono no está configurado. Por favor, contacta al administrador.",
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

    // Abrir modal de autenticación por teléfono
    setIsPhoneModalOpen(true);
  };

  const handlePhoneAuthSuccess = (user: { uid: string; phoneNumber: string | null; displayName: string | null }) => {
    toast({
      title: "¡Bienvenido!",
      description: `Sesión iniciada correctamente`,
    });

    // Redirigir al returnUrl si existe, sino a la página de bienvenida
    if (returnUrl && returnUrl !== '/') {
      router.push(returnUrl);
    } else {
      router.push(`/welcome?name=${encodeURIComponent(user.displayName || 'Usuario')}&role=customer`);
    }
  };

  return (
    <div className="relative flex items-center justify-center min-h-screen py-8 sm:py-12 w-full">
      {/* Imagen de fondo si está configurada */}
      {settings.loginBackgroundImageUrl && (
        <>
          <div className="absolute inset-0 z-0">
            <img
              src={settings.loginBackgroundImageUrl}
              alt="Fondo del login"
              className="w-full h-full object-cover"
            />
          </div>
          {/* Overlay oscuro para mejorar legibilidad */}
          <div className="absolute inset-0 z-0 bg-black/40" />
        </>
      )}

      {/* Contenedor del contenido centrado con ancho limitado */}
      <div className="w-full relative z-10" style={{ maxWidth: '1600px', margin: '0 auto', paddingLeft: '0.75rem', paddingRight: '0.75rem' }}>
        {/* Contenedor del login sin fondo */}
        <div className="w-full max-w-sm xs:max-w-md mx-auto">
        <div className="space-y-3 sm:space-y-4">
          {/* Botón de Google */}
          <Button
            onClick={handleGoogleLogin}
            className="w-full bg-white hover:bg-gray-100 text-gray-900 font-semibold h-12 sm:h-14 text-sm sm:text-base rounded-xl shadow-lg transition-all"
            type="button"
          >
            <svg className="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" viewBox="0 0 24 24">
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
            Continuar con Google
          </Button>

          {/* Botón de Celular */}
          <Button
            onClick={handlePhoneLogin}
            className="w-full bg-white hover:bg-gray-100 text-gray-900 font-semibold h-12 sm:h-14 text-sm sm:text-base rounded-xl shadow-lg transition-all"
            type="button"
          >
            <Phone className="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" />
            Continuar con Celular
          </Button>
        </div>

        {/* Link de registro */}
        <div className="mt-6 sm:mt-8 text-center">
          <p className="text-white/90 text-xs sm:text-sm">
            ¿No tienes una cuenta?{" "}
            <Link href="/register" className="font-semibold underline hover:text-white">
              Regístrate aquí
            </Link>
          </p>
        </div>
        </div>
      </div>

      {/* Modal de autenticación por teléfono */}
      <PhoneAuthModal
        isOpen={isPhoneModalOpen}
        onClose={() => setIsPhoneModalOpen(false)}
        onSuccess={handlePhoneAuthSuccess}
      />
    </div>
  );
}
