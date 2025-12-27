import { Metadata } from 'next';
import { Card, CardContent } from '@/components/ui/card';
import { Shield, Lock, Eye, Users } from 'lucide-react';

export const metadata: Metadata = {
  title: 'Política de Privacidad',
  description: 'Conoce cómo PakiiP protege y maneja tu información personal.',
};

export default function PrivacyPage() {
  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-4xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
        <div className="text-center space-y-2 sm:space-y-3">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline">
            Política de Privacidad
          </h1>
          <p className="text-sm sm:text-base text-muted-foreground">
            Última actualización: Enero 2025
          </p>
        </div>

        {/* Principios */}
        <div className="grid gap-3 sm:gap-4 grid-cols-2 md:grid-cols-4">
          <Card>
            <CardContent className="p-3 sm:p-4 md:p-5 text-center space-y-1 sm:space-y-2">
              <Shield className="h-6 w-6 sm:h-8 sm:w-8 md:h-10 md:w-10 text-primary mx-auto" />
              <h3 className="font-bold text-xs sm:text-sm md:text-base">Seguridad</h3>
              <p className="text-xs sm:text-sm text-muted-foreground">
                Protegemos tus datos
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-3 sm:p-4 md:p-5 text-center space-y-1 sm:space-y-2">
              <Lock className="h-6 w-6 sm:h-8 sm:w-8 md:h-10 md:w-10 text-primary mx-auto" />
              <h3 className="font-bold text-xs sm:text-sm md:text-base">Privacidad</h3>
              <p className="text-xs sm:text-sm text-muted-foreground">
                Tu info es confidencial
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-3 sm:p-4 md:p-5 text-center space-y-1 sm:space-y-2">
              <Eye className="h-6 w-6 sm:h-8 sm:w-8 md:h-10 md:w-10 text-primary mx-auto" />
              <h3 className="font-bold text-xs sm:text-sm md:text-base">Transparencia</h3>
              <p className="text-xs sm:text-sm text-muted-foreground">
                Uso claro de datos
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-3 sm:p-4 md:p-5 text-center space-y-1 sm:space-y-2">
              <Users className="h-6 w-6 sm:h-8 sm:w-8 md:h-10 md:w-10 text-primary mx-auto" />
              <h3 className="font-bold text-xs sm:text-sm md:text-base">Control</h3>
              <p className="text-xs sm:text-sm text-muted-foreground">
                Tú decides
              </p>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardContent className="p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8">
            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">1. Información que Recopilamos</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Recopilamos información que nos proporcionas directamente cuando:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>Te registras en nuestra plataforma</li>
                <li>Realizas un pedido</li>
                <li>Contactas con nuestro servicio al cliente</li>
                <li>Participas en encuestas o promociones</li>
              </ul>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed mt-2 sm:mt-3">
                Esta información puede incluir: nombre, email, teléfono, dirección,
                información de pago y preferencias de compra.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">2. Uso de la Información</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Utilizamos tu información para:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>Procesar y entregar tus pedidos</li>
                <li>Comunicarnos contigo sobre tu pedido</li>
                <li>Mejorar nuestros servicios</li>
                <li>Personalizar tu experiencia</li>
                <li>Enviar ofertas y promociones (con tu consentimiento)</li>
                <li>Prevenir fraudes y garantizar la seguridad</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">3. Compartir Información</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Podemos compartir tu información con:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li><strong>Vendedores:</strong> Para procesar tu pedido</li>
                <li><strong>Repartidores:</strong> Para realizar la entrega</li>
                <li><strong>Proveedores de servicios:</strong> Que nos ayudan a operar nuestra plataforma</li>
                <li><strong>Autoridades:</strong> Cuando sea requerido por ley</li>
              </ul>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed mt-2 sm:mt-3">
                <strong>Nunca vendemos</strong> tu información personal a terceros.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">4. Seguridad de Datos</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Implementamos medidas de seguridad técnicas y organizativas para proteger
                tu información personal contra acceso no autorizado, pérdida o alteración.
                Estas medidas incluyen encriptación, acceso restringido y auditorías regulares.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">5. Tus Derechos</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Tienes derecho a:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>Acceder a tu información personal</li>
                <li>Corregir información inexacta</li>
                <li>Solicitar la eliminación de tus datos</li>
                <li>Oponerte al procesamiento de tu información</li>
                <li>Retirar tu consentimiento en cualquier momento</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">6. Cookies</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Utilizamos cookies y tecnologías similares para mejorar tu experiencia,
                analizar el uso de nuestro sitio y personalizar el contenido. Puedes
                configurar tu navegador para rechazar cookies, aunque esto puede afectar
                la funcionalidad del sitio.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">7. Contacto</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Para ejercer tus derechos o si tienes preguntas sobre esta política, contáctanos en:
                <a href="mailto:privacidad@pakiip.com" className="text-primary hover:underline ml-1 break-all">
                  privacidad@pakiip.com
                </a>
              </p>
            </section>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
