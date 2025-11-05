import { Metadata } from 'next';
import { Card, CardContent } from '@/components/ui/card';
import { Mail, Phone, MapPin, Clock, MessageSquare } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ContactForm } from '@/components/ContactForm';

export const metadata: Metadata = {
  title: 'Contacto',
  description: 'Ponte en contacto con el equipo de PakiiP. Estamos aquí para ayudarte.',
};

export default function ContactPage() {
  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-6xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
        <div className="text-center space-y-3 sm:space-y-4">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline">
            Contáctanos
          </h1>
          <p className="text-sm sm:text-base md:text-lg text-muted-foreground max-w-2xl mx-auto px-2">
            ¿Tienes preguntas, comentarios o sugerencias? Estamos aquí para ayudarte.
            Elige tu método de contacto preferido.
          </p>
        </div>

        <div className="grid gap-4 sm:gap-6 md:grid-cols-2">
          {/* Información de contacto */}
          <div className="space-y-4 sm:space-y-6">
            <Card>
              <CardContent className="p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-6">
                <div className="space-y-2">
                  <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                    Información de Contacto
                  </h2>
                  <p className="text-sm sm:text-base text-muted-foreground">
                    Utiliza cualquiera de estos medios para comunicarte con nosotros
                  </p>
                </div>

                <div className="space-y-3 sm:space-y-4">
                  <div className="flex items-start gap-3 sm:gap-4">
                    <div className="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                      <Mail className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                    </div>
                    <div className="space-y-1 min-w-0 flex-1">
                      <h3 className="font-medium text-sm sm:text-base">Email</h3>
                      <a
                        href="mailto:contacto@pakiip.com"
                        className="text-sm sm:text-base text-muted-foreground hover:text-primary transition-colors block truncate"
                      >
                        contacto@pakiip.com
                      </a>
                      <p className="text-xs sm:text-sm text-muted-foreground">
                        Respuesta en 24-48 horas
                      </p>
                    </div>
                  </div>

                  <div className="flex items-start gap-3 sm:gap-4">
                    <div className="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                      <Phone className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                    </div>
                    <div className="space-y-1 min-w-0 flex-1">
                      <h3 className="font-medium text-sm sm:text-base">Teléfono</h3>
                      <a
                        href="tel:+51999999999"
                        className="text-sm sm:text-base text-muted-foreground hover:text-primary transition-colors block"
                      >
                        +51 999 999 999
                      </a>
                      <p className="text-xs sm:text-sm text-muted-foreground">
                        Lun - Dom: 8:00 AM - 10:00 PM
                      </p>
                    </div>
                  </div>

                  <div className="flex items-start gap-3 sm:gap-4">
                    <div className="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                      <MessageSquare className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                    </div>
                    <div className="space-y-1 min-w-0 flex-1">
                      <h3 className="font-medium text-sm sm:text-base">WhatsApp</h3>
                      <a
                        href="https://wa.me/51999999999"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-sm sm:text-base text-muted-foreground hover:text-primary transition-colors block"
                      >
                        +51 999 999 999
                      </a>
                      <p className="text-xs sm:text-sm text-muted-foreground">
                        Respuesta inmediata
                      </p>
                    </div>
                  </div>

                  <div className="flex items-start gap-3 sm:gap-4">
                    <div className="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                      <MapPin className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                    </div>
                    <div className="space-y-1 min-w-0 flex-1">
                      <h3 className="font-medium text-sm sm:text-base">Dirección</h3>
                      <p className="text-sm sm:text-base text-muted-foreground">
                        Av. Principal 123<br />
                        Miraflores, Lima<br />
                        Perú
                      </p>
                    </div>
                  </div>

                  <div className="flex items-start gap-3 sm:gap-4">
                    <div className="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                      <Clock className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
                    </div>
                    <div className="space-y-1 min-w-0 flex-1">
                      <h3 className="font-medium text-sm sm:text-base">Horario de Atención</h3>
                      <p className="text-sm sm:text-base text-muted-foreground">
                        Lunes a Domingo<br />
                        8:00 AM - 10:00 PM
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card className="bg-primary/5 border-primary/20">
              <CardContent className="p-4 sm:p-6 space-y-2 sm:space-y-3">
                <h3 className="font-bold text-base sm:text-lg">¿Necesitas ayuda inmediata?</h3>
                <p className="text-sm sm:text-base text-muted-foreground">
                  Para problemas urgentes con un pedido activo, contáctanos directamente por WhatsApp
                  para una atención más rápida.
                </p>
                <Button asChild className="w-full">
                  <a
                    href="https://wa.me/51999999999"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <MessageSquare className="mr-2 h-4 w-4" />
                    Abrir WhatsApp
                  </a>
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Formulario de contacto */}
          <Card>
            <CardContent className="p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-6">
              <div className="space-y-2">
                <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
                  Envíanos un Mensaje
                </h2>
                <p className="text-sm sm:text-base text-muted-foreground">
                  Completa el formulario y te responderemos lo antes posible
                </p>
              </div>

              <ContactForm />
            </CardContent>
          </Card>
        </div>

        {/* Sección de soporte adicional */}
        <Card>
          <CardContent className="p-4 sm:p-6 md:p-8">
            <div className="text-center space-y-3 sm:space-y-4">
              <h2 className="text-xl sm:text-2xl md:text-3xl font-bold font-headline">
                Otros Recursos de Ayuda
              </h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground max-w-2xl mx-auto px-2">
                Antes de contactarnos, tal vez encuentres tu respuesta en estos recursos
              </p>
              <div className="flex flex-wrap gap-2 sm:gap-3 justify-center mt-4 sm:mt-6">
                <Button variant="outline" asChild>
                  <a href="/faq">Preguntas Frecuentes</a>
                </Button>
                <Button variant="outline" asChild>
                  <a href="/terms">Términos y Condiciones</a>
                </Button>
                <Button variant="outline" asChild>
                  <a href="/about">Sobre Nosotros</a>
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
