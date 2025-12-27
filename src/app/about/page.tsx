import { Metadata } from 'next';
import { Card, CardContent } from '@/components/ui/card';
import { Heart, Users, Zap, Shield } from 'lucide-react';

export const metadata: Metadata = {
  title: 'Sobre Nosotros',
  description: 'Conoce más sobre PakiiP, tu mercado en línea favorito para delivery de comida y productos a domicilio en Perú.',
};

export default function AboutPage() {
  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-4xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
        {/* Header */}
        <div className="text-center space-y-3 sm:space-y-4">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline">
            Sobre Nosotros
          </h1>
          <p className="text-sm sm:text-base md:text-lg text-muted-foreground max-w-2xl mx-auto px-2">
            Conectamos a las mejores tiendas locales con clientes como tú,
            ofreciendo entregas rápidas y un servicio excepcional.
          </p>
        </div>

        {/* Misión y Visión */}
        <div className="grid gap-4 sm:gap-6 md:grid-cols-2">
          <Card>
            <CardContent className="p-4 sm:p-6 md:p-8 space-y-2 sm:space-y-3">
              <div className="flex items-center gap-2 sm:gap-3">
                <Heart className="h-5 w-5 sm:h-6 sm:w-6 text-primary flex-shrink-0" />
                <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">Nuestra Misión</h2>
              </div>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Facilitar el acceso a productos de calidad desde tiendas locales,
                promoviendo el comercio local y ofreciendo la mejor experiencia de
                compra online con entregas rápidas y seguras.
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4 sm:p-6 md:p-8 space-y-2 sm:space-y-3">
              <div className="flex items-center gap-2 sm:gap-3">
                <Zap className="h-5 w-5 sm:h-6 sm:w-6 text-primary flex-shrink-0" />
                <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">Nuestra Visión</h2>
              </div>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Ser la plataforma líder de delivery en Perú, conocida por nuestra
                rapidez, variedad y compromiso con el desarrollo de las comunidades
                locales que servimos.
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Valores */}
        <div className="space-y-4 sm:space-y-6">
          <h2 className="text-xl sm:text-2xl md:text-3xl font-bold font-headline text-center">
            Nuestros Valores
          </h2>
          <div className="grid gap-3 sm:gap-4 md:gap-6 sm:grid-cols-2">
            <Card>
              <CardContent className="p-4 sm:p-6 space-y-2 sm:space-y-3">
                <div className="flex items-center gap-2">
                  <Users className="h-4 w-4 sm:h-5 sm:w-5 text-primary flex-shrink-0" />
                  <h3 className="font-bold text-base sm:text-lg">Comunidad</h3>
                </div>
                <p className="text-sm sm:text-base text-muted-foreground leading-relaxed">
                  Apoyamos a las tiendas locales y fortalecemos las economías de nuestras comunidades.
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-4 sm:p-6 space-y-2 sm:space-y-3">
                <div className="flex items-center gap-2">
                  <Shield className="h-4 w-4 sm:h-5 sm:w-5 text-primary flex-shrink-0" />
                  <h3 className="font-bold text-base sm:text-lg">Confianza</h3>
                </div>
                <p className="text-sm sm:text-base text-muted-foreground leading-relaxed">
                  Garantizamos seguridad en cada transacción y calidad en cada entrega.
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-4 sm:p-6 space-y-2 sm:space-y-3">
                <div className="flex items-center gap-2">
                  <Zap className="h-4 w-4 sm:h-5 sm:w-5 text-primary flex-shrink-0" />
                  <h3 className="font-bold text-base sm:text-lg">Rapidez</h3>
                </div>
                <p className="text-sm sm:text-base text-muted-foreground leading-relaxed">
                  Entregamos tus pedidos lo más rápido posible sin comprometer la calidad.
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-4 sm:p-6 space-y-2 sm:space-y-3">
                <div className="flex items-center gap-2">
                  <Heart className="h-4 w-4 sm:h-5 sm:w-5 text-primary flex-shrink-0" />
                  <h3 className="font-bold text-base sm:text-lg">Pasión</h3>
                </div>
                <p className="text-sm sm:text-base text-muted-foreground leading-relaxed">
                  Amamos lo que hacemos y nos esforzamos por superar tus expectativas cada día.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Historia */}
        <Card>
          <CardContent className="p-4 sm:p-6 md:p-8 space-y-3 sm:space-y-4 md:space-y-5">
            <h2 className="text-xl sm:text-2xl md:text-3xl font-bold font-headline">Nuestra Historia</h2>
            <div className="space-y-3 sm:space-y-4 text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
              <p>
                PakiiP nació en 2024 con una misión clara: revolucionar la forma en que
                las personas acceden a productos y servicios en su localidad. Comenzamos
                en Lima con un puñado de tiendas asociadas y una visión de crecimiento.
              </p>
              <p>
                Hoy, nos enorgullece servir a múltiples ciudades en Perú, conectando a
                cientos de comercios locales con miles de clientes satisfechos. Nuestro
                equipo de repartidores trabaja incansablemente para garantizar entregas
                rápidas y seguras.
              </p>
              <p>
                Seguimos creciendo e innovando, siempre con el objetivo de mejorar la
                experiencia de nuestros usuarios y apoyar el desarrollo de las comunidades
                que servimos.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
