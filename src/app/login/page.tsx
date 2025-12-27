"use client";

/**
 * Pagina de Login - Solo Google OAuth
 */

import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { useAppData } from "@/hooks/use-app-data";
import { Loader2 } from "lucide-react";
import { useState, Suspense } from "react";
import { signIn } from "next-auth/react";

function LoginForm() {
  const router = useRouter();
  const { toast } = useToast();
  const { appSettings: settings } = useAppData();
  const [isLoading, setIsLoading] = useState(false);

  const handleGoogleLogin = async () => {
    setIsLoading(true);
    try {
      await signIn("google", { callbackUrl: "/welcome" });
    } catch (error) {
      toast({
        title: "Error",
        description: "No se pudo iniciar sesion con Google",
        variant: "destructive",
      });
      setIsLoading(false);
    }
  };

  return (
    <div className="relative flex items-center justify-center min-h-screen py-8 sm:py-12 w-full">
      {/* Imagen de fondo si esta configurada */}
      {settings.loginBackgroundImageUrl && (
        <>
          <div className="absolute inset-0 z-0">
            <img
              src={settings.loginBackgroundImageUrl}
              alt="Fondo del login"
              className="w-full h-full object-cover"
            />
          </div>
          <div className="absolute inset-0 z-0 bg-black/40" />
        </>
      )}

      <div
        className="w-full relative z-10"
        style={{ maxWidth: "1600px", margin: "0 auto", padding: "0 0.75rem" }}
      >
        <div className="w-full max-w-sm xs:max-w-md mx-auto">
          {/* Logo o titulo */}
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold text-white mb-2">
              {settings.appName || "Pakiip"}
            </h1>
            <p className="text-white/80">Inicia sesion para continuar</p>
          </div>

          {/* Boton de Google */}
          <Button
            onClick={handleGoogleLogin}
            className="w-full bg-white hover:bg-gray-100 text-gray-900 font-semibold h-12 sm:h-14 text-sm sm:text-base rounded-xl shadow-lg transition-all"
            type="button"
            disabled={isLoading}
          >
            {isLoading ? (
              <Loader2 className="w-5 h-5 animate-spin" />
            ) : (
              <>
                <svg
                  className="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"
                  viewBox="0 0 24 24"
                >
                  <path
                    fill="#4285F4"
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                  />
                  <path
                    fill="#34A853"
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                  />
                  <path
                    fill="#FBBC05"
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                  />
                  <path
                    fill="#EA4335"
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                  />
                </svg>
                Continuar con Google
              </>
            )}
          </Button>

          {/* Links */}
          <div className="mt-6 sm:mt-8 text-center space-y-2">
            <p className="text-white/90 text-xs sm:text-sm">
              No tienes una cuenta?{" "}
              <Link
                href="/register"
                className="font-semibold underline hover:text-white"
              >
                Registrate aqui
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense
      fallback={
        <div className="flex items-center justify-center min-h-screen">
          <Loader2 className="w-8 h-8 animate-spin text-primary" />
        </div>
      }
    >
      <LoginForm />
    </Suspense>
  );
}
