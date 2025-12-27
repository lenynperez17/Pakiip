"use client";

import Link from "next/link";
import { Logo } from "@/components/icons/Logo";
import { Facebook, Instagram, Twitter, Mail, Phone, MapPin, Youtube } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import Image from "next/image";

export function Footer() {
  const { appSettings } = useAppData();
  const currentYear = new Date().getFullYear();

  return (
    <footer className="border-t bg-muted/30">
      <div className="mx-auto px-4 py-8 md:py-12" style={{ maxWidth: '1600px' }}>
        <div className="grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
          {/* Columna 1: Logo y Descripción */}
          <div className="space-y-4">
            <Link href="/" className="flex items-center gap-2">
              {appSettings.logoUrl ? (
                <Image
                  src={appSettings.logoUrl}
                  alt={appSettings.appName}
                  width={32}
                  height={32}
                  className="h-8 w-8 object-contain"
                />
              ) : (
                <Logo className="h-8 w-8 text-primary" />
              )}
              <span className="font-headline text-xl font-bold">{appSettings.appName}</span>
            </Link>
            <p className="text-sm text-muted-foreground">
              Tu mercado en línea favorito. Encuentra las mejores tiendas, productos frescos
              y ofertas increíbles, todo con entrega a domicilio.
            </p>
            {/* Redes Sociales */}
            <div className="flex gap-3">
              <Link
                href="https://facebook.com"
                target="_blank"
                rel="noopener noreferrer"
                className="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground"
                aria-label="Síguenos en Facebook"
              >
                <Facebook className="h-4 w-4" />
              </Link>
              <Link
                href="https://instagram.com"
                target="_blank"
                rel="noopener noreferrer"
                className="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground"
                aria-label="Síguenos en Instagram"
              >
                <Instagram className="h-4 w-4" />
              </Link>
              <Link
                href="https://twitter.com"
                target="_blank"
                rel="noopener noreferrer"
                className="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground"
                aria-label="Síguenos en Twitter"
              >
                <Twitter className="h-4 w-4" />
              </Link>
              <Link
                href="https://youtube.com"
                target="_blank"
                rel="noopener noreferrer"
                className="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground"
                aria-label="Síguenos en YouTube"
              >
                <Youtube className="h-4 w-4" />
              </Link>
            </div>
          </div>

          {/* Columna 2: Enlaces Rápidos */}
          <div className="space-y-4">
            <h3 className="font-headline text-base font-bold">Enlaces Rápidos</h3>
            <ul className="space-y-2 text-sm">
              <li>
                <Link href="/" className="text-muted-foreground transition-colors hover:text-primary">
                  Inicio
                </Link>
              </li>
              <li>
                <Link href="/ask-a-favor" className="text-muted-foreground transition-colors hover:text-primary">
                  Pide un Favor
                </Link>
              </li>
              <li>
                <Link href="/recommendations" className="text-muted-foreground transition-colors hover:text-primary">
                  Recomendaciones
                </Link>
              </li>
              <li>
                <Link href="/my-orders" className="text-muted-foreground transition-colors hover:text-primary">
                  Mis Pedidos
                </Link>
              </li>
              <li>
                <Link href="/driver/register" className="text-muted-foreground transition-colors hover:text-primary">
                  Hazte Repartidor
                </Link>
              </li>
            </ul>
          </div>

          {/* Columna 3: Información Legal */}
          <div className="space-y-4">
            <h3 className="font-headline text-base font-bold">Información</h3>
            <ul className="space-y-2 text-sm">
              <li>
                <Link href="/about" className="text-muted-foreground transition-colors hover:text-primary">
                  Sobre Nosotros
                </Link>
              </li>
              <li>
                <Link href="/terms" className="text-muted-foreground transition-colors hover:text-primary">
                  Términos y Condiciones
                </Link>
              </li>
              <li>
                <Link href="/privacy" className="text-muted-foreground transition-colors hover:text-primary">
                  Política de Privacidad
                </Link>
              </li>
              <li>
                <Link href="/faq" className="text-muted-foreground transition-colors hover:text-primary">
                  Preguntas Frecuentes
                </Link>
              </li>
              <li>
                <Link href="/contact" className="text-muted-foreground transition-colors hover:text-primary">
                  Contacto
                </Link>
              </li>
            </ul>
          </div>

          {/* Columna 4: Contacto */}
          <div className="space-y-4">
            <h3 className="font-headline text-base font-bold">Contacto</h3>
            <ul className="space-y-3 text-sm">
              <li className="flex items-start gap-2">
                <Mail className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                <a
                  href="mailto:contacto@pakiip.com"
                  className="text-muted-foreground transition-colors hover:text-primary break-all"
                >
                  contacto@pakiip.com
                </a>
              </li>
              <li className="flex items-start gap-2">
                <Phone className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                <a
                  href="tel:+51999999999"
                  className="text-muted-foreground transition-colors hover:text-primary"
                >
                  +51 999 999 999
                </a>
              </li>
              <li className="flex items-start gap-2">
                <MapPin className="h-4 w-4 text-primary mt-0.5 flex-shrink-0" />
                <span className="text-muted-foreground">
                  Av. Principal 123, Lima, Perú
                </span>
              </li>
            </ul>
            <div className="pt-2">
              <p className="text-xs text-muted-foreground">
                Horario de atención:<br />
                Lunes a Domingo: 8:00 AM - 10:00 PM
              </p>
            </div>
          </div>
        </div>

        {/* Línea divisoria */}
        <div className="mt-8 border-t pt-6">
          <div className="flex flex-col items-center justify-between gap-4 text-sm text-muted-foreground md:flex-row">
            <p>
              © {currentYear} {appSettings.appName}. Todos los derechos reservados.
            </p>
            <div className="flex flex-wrap items-center justify-center gap-4">
              <Link href="/terms" className="transition-colors hover:text-primary">
                Términos
              </Link>
              <span className="text-muted-foreground/50">•</span>
              <Link href="/privacy" className="transition-colors hover:text-primary">
                Privacidad
              </Link>
              <span className="text-muted-foreground/50">•</span>
              <Link href="/cookies" className="transition-colors hover:text-primary">
                Cookies
              </Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
