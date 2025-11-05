import { Metadata } from 'next';
import { Card, CardContent } from '@/components/ui/card';
import { Cookie, Shield, Settings, Info } from 'lucide-react';
import Link from 'next/link';
import { Button } from '@/components/ui/button';

export const metadata: Metadata = {
  title: 'Política de Cookies',
  description: 'Conoce cómo PakiiP utiliza cookies para mejorar tu experiencia y personalizar el contenido según tus preferencias.',
};

export default function CookiesPage() {
  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-4xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
        <div className="text-center space-y-2 sm:space-y-3">
          <div className="flex justify-center">
            <Cookie className="h-10 w-10 sm:h-12 sm:w-12 md:h-16 md:w-16 text-primary" />
          </div>
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline">
            Política de Cookies
          </h1>
          <p className="text-sm sm:text-base text-muted-foreground">
            Última actualización: Enero 2025
          </p>
        </div>

        <Card>
          <CardContent className="p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8">
            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                ¿Qué son las cookies?
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo (ordenador,
                tablet o smartphone) cuando visitas nuestro sitio web. Nos ayudan a mejorar tu experiencia,
                recordar tus preferencias y personalizar el contenido que ves.
              </p>
            </section>

            <section className="space-y-4">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Tipos de cookies que utilizamos
              </h2>

              <div className="space-y-4">
                <div className="flex gap-3 p-4 rounded-lg bg-muted/50">
                  <Shield className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" />
                  <div className="space-y-1">
                    <h3 className="font-semibold text-base sm:text-lg">Cookies Esenciales</h3>
                    <p className="text-sm sm:text-base text-muted-foreground">
                      Son necesarias para el funcionamiento básico del sitio web. Incluyen cookies de
                      autenticación, carrito de compras y preferencias de idioma. No pueden ser desactivadas.
                    </p>
                  </div>
                </div>

                <div className="flex gap-3 p-4 rounded-lg bg-muted/50">
                  <Settings className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" />
                  <div className="space-y-1">
                    <h3 className="font-semibold text-base sm:text-lg">Cookies de Funcionalidad</h3>
                    <p className="text-sm sm:text-base text-muted-foreground">
                      Permiten recordar tus preferencias (como tema oscuro/claro, ciudad seleccionada)
                      para ofrecerte una experiencia personalizada en tus próximas visitas.
                    </p>
                  </div>
                </div>

                <div className="flex gap-3 p-4 rounded-lg bg-muted/50">
                  <Info className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" />
                  <div className="space-y-1">
                    <h3 className="font-semibold text-base sm:text-lg">Cookies de Rendimiento</h3>
                    <p className="text-sm sm:text-base text-muted-foreground">
                      Nos ayudan a entender cómo los visitantes interactúan con nuestro sitio web,
                      recopilando información anónima sobre las páginas visitadas y errores encontrados.
                    </p>
                  </div>
                </div>

                <div className="flex gap-3 p-4 rounded-lg bg-muted/50">
                  <Cookie className="h-5 w-5 text-primary flex-shrink-0 mt-0.5" />
                  <div className="space-y-1">
                    <h3 className="font-semibold text-base sm:text-lg">Cookies de Marketing</h3>
                    <p className="text-sm sm:text-base text-muted-foreground">
                      Se utilizan para mostrar anuncios relevantes según tus intereses. También nos
                      ayudan a medir la efectividad de nuestras campañas publicitarias.
                    </p>
                  </div>
                </div>
              </div>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Cookies de terceros
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Algunos de nuestros servicios y características requieren el uso de cookies de terceros:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li><strong>Google Maps:</strong> Para mostrar ubicaciones de tiendas y calcular rutas de entrega</li>
                <li><strong>Firebase:</strong> Para autenticación segura y almacenamiento de datos</li>
                <li><strong>Google Analytics:</strong> Para analizar el tráfico y comportamiento del usuario</li>
                <li><strong>Servicios de pago:</strong> Para procesar transacciones de forma segura</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Gestión de cookies
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Puedes controlar y/o eliminar las cookies como desees. Puedes eliminar todas las cookies
                que ya están en tu dispositivo y configurar la mayoría de los navegadores para que no se
                guarden. Sin embargo, si haces esto, es posible que tengas que ajustar manualmente algunas
                preferencias cada vez que visites el sitio y que algunos servicios y funcionalidades no funcionen.
              </p>
              <div className="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mt-4">
                <p className="text-sm text-amber-800 dark:text-amber-200">
                  <strong>Importante:</strong> Si bloqueas las cookies esenciales, algunas funciones del
                  sitio web pueden no estar disponibles, como iniciar sesión o realizar pedidos.
                </p>
              </div>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Duración de las cookies
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Las cookies pueden ser temporales (cookies de sesión) o permanentes (cookies persistentes):
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li><strong>Cookies de sesión:</strong> Se eliminan automáticamente cuando cierras tu navegador</li>
                <li><strong>Cookies persistentes:</strong> Permanecen en tu dispositivo durante un período específico (hasta 2 años)</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Tus derechos
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Tienes derecho a:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>Saber qué cookies utilizamos y por qué</li>
                <li>Configurar tus preferencias de cookies</li>
                <li>Retirar tu consentimiento en cualquier momento</li>
                <li>Eliminar las cookies almacenadas en tu dispositivo</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Actualizaciones de esta política
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Podemos actualizar esta Política de Cookies ocasionalmente para reflejar cambios en las
                cookies que utilizamos o por otras razones operativas, legales o regulatorias. Te
                recomendamos revisar esta página periódicamente.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3 pt-4 border-t">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                Más información
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Para más información sobre cómo protegemos tu privacidad, consulta nuestra{' '}
                <Link href="/privacy" className="text-primary hover:underline font-medium">
                  Política de Privacidad
                </Link>.
              </p>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Si tienes preguntas sobre nuestro uso de cookies, contáctanos en{' '}
                <a href="mailto:privacidad@pakiip.com" className="text-primary hover:underline font-medium">
                  privacidad@pakiip.com
                </a>
              </p>
            </section>

            <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-6">
              <Button asChild className="w-full sm:w-auto">
                <Link href="/">Volver al Inicio</Link>
              </Button>
              <Button asChild variant="outline" className="w-full sm:w-auto">
                <Link href="/privacy">Ver Política de Privacidad</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
