import { Metadata } from 'next';
import { Card, CardContent } from '@/components/ui/card';

export const metadata: Metadata = {
  title: 'Términos y Condiciones',
  description: 'Términos y condiciones de uso de la plataforma PakiiP.',
};

export default function TermsPage() {
  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-4xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
        <div className="text-center space-y-2 sm:space-y-3">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline">
            Términos y Condiciones
          </h1>
          <p className="text-sm sm:text-base text-muted-foreground">
            Última actualización: Enero 2025
          </p>
        </div>

        <Card>
          <CardContent className="p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8">
            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">1. Aceptación de Términos</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Al acceder y utilizar PakiiP, aceptas estar sujeto a estos términos y condiciones
                de uso, todas las leyes y regulaciones aplicables, y aceptas que eres responsable
                del cumplimiento de todas las leyes locales aplicables.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">2. Uso del Servicio</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                PakiiP es una plataforma que conecta a usuarios con tiendas locales para
                la entrega de productos a domicilio. Los usuarios deben:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>Proporcionar información precisa y actualizada</li>
                <li>Mantener la confidencialidad de su cuenta</li>
                <li>No utilizar el servicio para actividades ilegales</li>
                <li>Respetar a los repartidores y vendedores</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">3. Pedidos y Pagos</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Al realizar un pedido, el usuario se compromete a:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>Pagar el monto total del pedido, incluyendo el costo de envío</li>
                <li>Estar disponible en la dirección indicada para recibir el pedido</li>
                <li>Verificar el pedido al momento de la entrega</li>
                <li>Reportar cualquier problema dentro de las 24 horas posteriores a la entrega</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">4. Política de Cancelación</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Los pedidos pueden ser cancelados sin cargo si aún no han sido preparados por
                el vendedor. Una vez que el pedido está en camino, no se aceptan cancelaciones.
                En caso de problemas con el pedido, contacta a nuestro equipo de soporte.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">5. Limitación de Responsabilidad</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                PakiiP actúa como intermediario entre vendedores y compradores. No somos
                responsables por:
              </p>
              <ul className="list-disc list-inside space-y-1 sm:space-y-2 text-sm sm:text-base md:text-lg text-muted-foreground ml-2 sm:ml-4">
                <li>La calidad de los productos vendidos por terceros</li>
                <li>Retrasos causados por factores externos (clima, tráfico, etc.)</li>
                <li>Información incorrecta proporcionada por los usuarios</li>
              </ul>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">6. Modificaciones</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Nos reservamos el derecho de modificar estos términos en cualquier momento.
                Los cambios serán efectivos inmediatamente después de su publicación en el sitio web.
              </p>
            </section>

            <section className="space-y-2 sm:space-y-3">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">7. Contacto</h2>
              <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">
                Si tienes preguntas sobre estos términos, contáctanos en:
                <a href="mailto:legal@pakiip.com" className="text-primary hover:underline ml-1 break-all">
                  legal@pakiip.com
                </a>
              </p>
            </section>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
