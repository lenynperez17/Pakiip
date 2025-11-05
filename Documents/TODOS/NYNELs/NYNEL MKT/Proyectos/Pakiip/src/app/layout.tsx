
import type { Metadata } from 'next';
import './globals.css';
import { cn } from '@/lib/utils';
import { Toaster } from "@/components/ui/toaster"
import { CartProvider } from '@/hooks/use-cart';
import { LayoutWrapper } from '@/components/layout/LayoutWrapper';
import { AppDataProvider } from '@/hooks/use-app-data';
import { HydrationFix } from './hydration-fix';
import { GoogleMapsProvider } from '@/providers/GoogleMapsProvider';

export const metadata: Metadata = {
  metadataBase: new URL('https://pakiip.com'),
  title: {
    default: 'PakiiP - Tu Mercado en Línea | Delivery de Comida a Domicilio',
    template: '%s | PakiiP'
  },
  description: 'Descubre las mejores tiendas y restaurantes cerca de ti. Entrega rápida a domicilio de comida, supermercado, farmacia y más. Ofertas exclusivas todos los días en Perú.',
  keywords: ['delivery', 'comida a domicilio', 'restaurantes', 'supermercado online', 'farmacia delivery', 'pedidos online peru', 'app de comida', 'delivery rapido'],
  authors: [{ name: 'PakiiP' }],
  creator: 'PakiiP',
  publisher: 'PakiiP',
  formatDetection: {
    email: false,
    address: false,
    telephone: false,
  },
  openGraph: {
    type: 'website',
    locale: 'es_PE',
    url: 'https://pakiip.com',
    title: 'PakiiP - Tu Mercado en Línea',
    description: 'Descubre las mejores tiendas y restaurantes cerca de ti. Entrega rápida a domicilio.',
    siteName: 'PakiiP',
    images: [
      {
        url: '/og-image.png',
        width: 1200,
        height: 630,
        alt: 'PakiiP - Tu Mercado en Línea',
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'PakiiP - Tu Mercado en Línea',
    description: 'Descubre las mejores tiendas y restaurantes cerca de ti. Entrega rápida a domicilio.',
    images: ['/og-image.png'],
    creator: '@pakiip',
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-video-preview': -1,
      'max-image-preview': 'large',
      'max-snippet': -1,
    },
  },
  verification: {
    google: 'google-site-verification-code',
    yandex: 'yandex-verification-code',
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="es" suppressHydrationWarning>
      <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
        <meta name="theme-color" content="#ff2301" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="PakiiP" />
        <link rel="manifest" href="/manifest.json" />
        <link rel="icon" href="/favicon.ico" sizes="any" />
        <link rel="icon" href="/icon.svg" type="image/svg+xml" />
        <link rel="apple-touch-icon" href="/apple-touch-icon.png" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet" />
        {/* Script para limpiar atributos de extensiones ANTES de la hidratación de React */}
        <script dangerouslySetInnerHTML={{
          __html: `
            (function() {
              var attrs = ['bis_register', 'bis_skin_checked', '__processed_', 'data-lastpass-', 'data-1p-', 'data-dashlane-', 'gramm', 'data-gr-ext-installed'];
              function clean(el) {
                if (!el || !el.hasAttribute) return;
                attrs.forEach(function(attr) {
                  if (el.hasAttribute(attr)) {
                    el.removeAttribute(attr);
                  }
                  var allAttrs = Array.from(el.attributes || []);
                  allAttrs.forEach(function(a) {
                    if (a.name.startsWith(attr)) {
                      el.removeAttribute(a.name);
                    }
                  });
                });
              }
              function cleanAll() {
                if (document.body) {
                  clean(document.body);
                  var elements = document.body.querySelectorAll('*');
                  elements.forEach(clean);
                }
              }
              // Ejecutar inmediatamente si DOM ya está listo
              if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', cleanAll);
              } else {
                cleanAll();
              }
              // También limpiar antes de la hidratación
              if (document.readyState !== 'complete') {
                window.addEventListener('load', cleanAll);
              }
            })();
          `
        }} />
      </head>
      <body
        className={cn('min-h-screen bg-background font-body antialiased')}
        suppressHydrationWarning
      >
        <HydrationFix />
        <GoogleMapsProvider>
          <AppDataProvider>
            <CartProvider>
              <LayoutWrapper>
                {children}
              </LayoutWrapper>
              <Toaster />
            </CartProvider>
          </AppDataProvider>
        </GoogleMapsProvider>
      </body>
    </html>
  );
}
