import { Metadata } from 'next';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Card, CardContent } from '@/components/ui/card';
import { HelpCircle } from 'lucide-react';

export const metadata: Metadata = {
  title: 'Preguntas Frecuentes',
  description: 'Encuentra respuestas a las preguntas más comunes sobre PakiiP y nuestro servicio de delivery.',
};

export default function FAQPage() {
  const faqs = [
    {
      category: "General",
      questions: [
        {
          question: "¿Qué es PakiiP?",
          answer: "PakiiP es una plataforma de delivery que conecta a usuarios con tiendas locales, restaurantes, supermercados y farmacias para entregas rápidas a domicilio."
        },
        {
          question: "¿En qué ciudades están disponibles?",
          answer: "Actualmente operamos en Lima, Arequipa y Cusco. Estamos expandiéndonos constantemente a nuevas ciudades."
        },
        {
          question: "¿Cuál es el horario de servicio?",
          answer: "Nuestro servicio está disponible de lunes a domingo de 8:00 AM a 10:00 PM. Los horarios pueden variar según la tienda."
        }
      ]
    },
    {
      category: "Pedidos",
      questions: [
        {
          question: "¿Cómo hago un pedido?",
          answer: "1) Selecciona tu ciudad, 2) Elige una tienda, 3) Agrega productos al carrito, 4) Completa la información de entrega, 5) Realiza el pago y ¡listo!"
        },
        {
          question: "¿Puedo cancelar mi pedido?",
          answer: "Sí, puedes cancelar tu pedido sin cargo si aún no ha sido preparado por el vendedor. Una vez en camino, no se aceptan cancelaciones."
        },
        {
          question: "¿Cuánto tiempo tarda la entrega?",
          answer: "El tiempo de entrega varía entre 20-60 minutos dependiendo de la distancia, el tráfico y la disponibilidad de repartidores."
        },
        {
          question: "¿Cuál es el pedido mínimo?",
          answer: "El pedido mínimo varía según la tienda. Puedes verlo en la página de cada establecimiento."
        }
      ]
    },
    {
      category: "Pagos",
      questions: [
        {
          question: "¿Qué métodos de pago aceptan?",
          answer: "Aceptamos transferencias bancarias, pagos por Yape, Plin y efectivo contra entrega. Pronto agregaremos tarjetas de crédito/débito."
        },
        {
          question: "¿Es seguro pagar en línea?",
          answer: "Sí, utilizamos tecnología de encriptación para proteger tu información de pago. Nunca almacenamos datos sensibles de tarjetas."
        },
        {
          question: "¿Puedo obtener una factura?",
          answer: "Sí, todas las tiendas pueden emitir facturas electrónicas. Solicítala al momento de hacer tu pedido."
        }
      ]
    },
    {
      category: "Envíos",
      questions: [
        {
          question: "¿Cuánto cuesta el envío?",
          answer: "El costo de envío varía según la distancia desde la tienda hasta tu dirección. Lo puedes ver antes de confirmar tu pedido."
        },
        {
          question: "¿Ofrecen envío gratis?",
          answer: "Algunas tiendas ofrecen envío gratis en pedidos superiores a un monto específico. Verifica las promociones disponibles."
        },
        {
          question: "¿Puedo programar una entrega?",
          answer: "Actualmente solo ofrecemos entregas inmediatas. La opción de programar entregas estará disponible próximamente."
        },
        {
          question: "¿Qué hago si mi pedido llega con problemas?",
          answer: "Contáctanos inmediatamente a través del chat de la app o por WhatsApp. Resolveremos el problema lo antes posible."
        }
      ]
    },
    {
      category: "Cuenta",
      questions: [
        {
          question: "¿Necesito crear una cuenta?",
          answer: "Sí, necesitas una cuenta para hacer pedidos. El registro es rápido y gratuito."
        },
        {
          question: "¿Cómo actualizo mi dirección?",
          answer: "Puedes actualizar tu dirección desde tu perfil o al momento de hacer un pedido."
        },
        {
          question: "¿Qué hago si olvidé mi contraseña?",
          answer: "Haz clic en '¿Olvidaste tu contraseña?' en la página de inicio de sesión y sigue las instrucciones para restablecerla."
        }
      ]
    }
  ];

  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-4xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
        <div className="text-center space-y-3 sm:space-y-4">
          <div className="flex justify-center">
            <HelpCircle className="h-10 w-10 sm:h-12 sm:w-12 md:h-16 md:w-16 text-primary" />
          </div>
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline">
            Preguntas Frecuentes
          </h1>
          <p className="text-sm sm:text-base md:text-lg text-muted-foreground max-w-2xl mx-auto px-2">
            Encuentra respuestas a las dudas más comunes sobre nuestro servicio
          </p>
        </div>

        {faqs.map((category, index) => (
          <Card key={index}>
            <CardContent className="p-4 sm:p-6 md:p-8 space-y-3 sm:space-y-4">
              <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline text-primary">
                {category.category}
              </h2>
              <Accordion type="single" collapsible className="w-full">
                {category.questions.map((faq, faqIndex) => (
                  <AccordionItem key={faqIndex} value={`item-${index}-${faqIndex}`}>
                    <AccordionTrigger className="text-left text-sm sm:text-base md:text-lg font-medium hover:no-underline py-3 sm:py-4">
                      {faq.question}
                    </AccordionTrigger>
                    <AccordionContent className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed pt-2 pb-4">
                      {faq.answer}
                    </AccordionContent>
                  </AccordionItem>
                ))}
              </Accordion>
            </CardContent>
          </Card>
        ))}

        <Card className="bg-primary/5 border-primary/20">
          <CardContent className="p-4 sm:p-6 md:p-8 text-center space-y-3 sm:space-y-4">
            <h2 className="text-lg sm:text-xl md:text-2xl font-bold font-headline">
              ¿No encontraste lo que buscabas?
            </h2>
            <p className="text-sm sm:text-base md:text-lg text-muted-foreground max-w-xl mx-auto px-2">
              Nuestro equipo de soporte está listo para ayudarte
            </p>
            <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 md:gap-4 justify-center items-center mt-3 sm:mt-4">
              <a
                href="mailto:soporte@pakiip.com"
                className="text-sm sm:text-base md:text-lg text-primary hover:underline font-medium break-all"
              >
                soporte@pakiip.com
              </a>
              <span className="hidden sm:inline text-muted-foreground">•</span>
              <a
                href="tel:+51999999999"
                className="text-sm sm:text-base md:text-lg text-primary hover:underline font-medium"
              >
                +51 999 999 999
              </a>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
