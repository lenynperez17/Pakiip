// ═══════════════════════════════════════════════════════════════════════════
// 💰 SERVICIO DE LÓGICA DE PRICING INTELIGENTE
// ═══════════════════════════════════════════════════════════════════════════
// Centraliza toda la lógica de precios basada en los servicios REALES de nynelmkt.com
// Implementa multiplicadores inteligentes según complejidad del proyecto
// ═══════════════════════════════════════════════════════════════════════════

import { logger } from '../utils/logger.js';

// ═══════════════════════════════════════════════════════════════════════════
// 📋 CATÁLOGO DE SERVICIOS REALES DE NYNEL MKT (nynelmkt.com)
// ═══════════════════════════════════════════════════════════════════════════
export const NYNEL_SERVICES = {
  SOFTWARE_A_MEDIDA: {
    id: 'software-medida',
    nombre: 'Implementación de Software a Medida',
    precioBase: 2500, // desde S/2,500
    descripcion: 'Soluciones personalizadas que se adaptan a las necesidades de tu empresa',
    categoria: 'desarrollo',
    multipliers: {
      simple: 1, // S/2,500
      intermedia: 2, // S/5,000
      compleja: 3, // S/7,500
      enterprise: 4, // S/10,000+
    },
  },
  SEO_MARKETING: {
    id: 'seo-marketing',
    nombre: 'SEO y Marketing Digital',
    precioBase: 500, // desde S/500
    descripcion: 'Optimización web para atraer tráfico cualificado y convertirlo en clientes',
    categoria: 'marketing',
    multipliers: {
      simple: 1, // S/500 (básico)
      intermedia: 2, // S/1,000 (profesional)
      compleja: 3, // S/1,500 (avanzado)
      enterprise: 4, // S/2,000+ (enterprise)
    },
  },
  EMAIL_MARKETING: {
    id: 'email-marketing',
    nombre: 'Email Marketing y Eventos',
    precioBase: 300, // desde S/300
    descripcion: 'Campañas que convierten suscriptores en clientes y eventos estratégicos',
    categoria: 'marketing',
    multipliers: {
      simple: 1, // S/300 (1 campaña)
      intermedia: 2, // S/600 (múltiples campañas)
      compleja: 3, // S/900 (automation avanzada)
      enterprise: 4, // S/1,200+ (enterprise)
    },
  },
  PAGINAS_WEB: {
    id: 'paginas-web',
    nombre: 'Creación de Páginas Web Avanzadas',
    precioBase: 650, // desde S/650
    descripcion: 'Sitios web profesionales, responsive y orientados a conversión',
    categoria: 'desarrollo',
    multipliers: {
      simple: 1, // S/650 (landing/informativa 5 páginas)
      intermedia: 2, // S/1,300 (web 8-10 páginas)
      compleja: 3, // S/1,950 (web 15+ páginas)
      enterprise: 4, // S/2,600+ (corporativa)
    },
  },
  CHATBOT_AI: {
    id: 'chatbot-ai',
    nombre: 'Automatización de Procesos (Chatbot/AI Agent)',
    precioBase: 350, // S/350 instalación
    precioMensual: 89.90, // + S/89.90/mes
    descripcion: 'Flujos automatizados, WhatsApp 24/7, cotizaciones automáticas, N8N',
    categoria: 'automatizacion',
    multipliers: {
      simple: 1, // S/350 setup (básico)
      intermedia: 1.5, // S/525 setup (profesional)
      compleja: 2, // S/700 setup (avanzado)
      enterprise: 3, // S/1,050 setup (enterprise)
    },
  },
  APPS_MOVILES: {
    id: 'apps-moviles',
    nombre: 'Desarrollo de Apps Móviles',
    precioBase: 5000, // desde S/5,000
    descripcion: 'Apps intuitivas de alto rendimiento para iOS y Android',
    categoria: 'desarrollo',
    multipliers: {
      simple: 1, // S/5,000 (app básica 3-5 pantallas)
      intermedia: 2, // S/10,000 (app 6-10 pantallas)
      compleja: 4, // S/20,000 (app compleja)
      enterprise: 7, // S/35,000+ (enterprise)
    },
  },
  ANALITICA_DATOS: {
    id: 'analitica-datos',
    nombre: 'Analítica de Datos Empresariales',
    precioBase: 350, // desde S/350
    descripcion: 'Convertimos datos en insights accionables para mejores decisiones',
    categoria: 'datos',
    multipliers: {
      simple: 1, // S/350 (dashboard básico)
      intermedia: 2, // S/700 (analytics profesional)
      compleja: 3, // S/1,050 (BI completo)
      enterprise: 4, // S/1,400+ (enterprise analytics)
    },
  },
  CAMPAÑAS_PUBLICITARIAS: {
    id: 'campañas-publicitarias',
    nombre: 'Campañas Publicitarias Integrales',
    precioBase: 2000, // desde S/2,000
    descripcion: 'Estrategias ATL, BTL y TTL que maximizan visibilidad',
    categoria: 'marketing',
    multipliers: {
      simple: 1, // S/2,000 (campaña digital básica)
      intermedia: 2, // S/4,000 (campaña integral)
      compleja: 3, // S/6,000 (multi-canal)
      enterprise: 4, // S/8,000+ (enterprise 360°)
    },
  },
} as const;

// ═══════════════════════════════════════════════════════════════════════════
// 🎯 INTERFACES
// ═══════════════════════════════════════════════════════════════════════════

export interface PricingInput {
  // Tipo de servicio (uno de los IDs de NYNEL_SERVICES)
  serviceId: string;

  // Complejidad del proyecto
  complexity?: 'simple' | 'intermedia' | 'compleja' | 'enterprise';

  // Servicios adicionales (para calcular descuento por paquete)
  additionalServices?: string[];

  // Features específicas que incrementan precio
  features?: string[];

  // Integraciones que incrementan precio
  integrations?: string[];

  // Plataformas (para apps móviles)
  platforms?: string[];

  // Presupuesto estimado del cliente (opcional, para validación)
  clientBudget?: number;
}

export interface PricingResult {
  // Precio base del servicio
  precioBase: number;

  // Multiplicador aplicado (1, 2, 3, 4)
  multiplicador: number;

  // Precio después de aplicar multiplicador
  precioConMultiplicador: number;

  // Incrementos por features/integraciones/plataformas
  incrementos: {
    features: number;
    integrations: number;
    platforms: number;
    total: number;
  };

  // Precio final calculado
  precioFinal: number;

  // Descuento si aplica (por múltiples servicios)
  descuento?: {
    porcentaje: number;
    monto: number;
    precioConDescuento: number;
  };

  // Rango de precio estimado (para cotizaciones)
  rango: {
    minimo: number;
    maximo: number;
  };

  // Información del servicio
  servicio: {
    id: string;
    nombre: string;
    descripcion: string;
    categoria: string;
  };

  // Recomendaciones
  recomendaciones: string[];
}

// ═══════════════════════════════════════════════════════════════════════════
// 💡 CLASE PRINCIPAL: PricingLogicService
// ═══════════════════════════════════════════════════════════════════════════

class PricingLogicService {
  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎯 CALCULAR PRECIO INTELIGENTE
   * ═══════════════════════════════════════════════════════════════
   * Calcula precio basándose en:
   * 1. Precio base del servicio (de nynelmkt.com)
   * 2. Multiplicador según complejidad (× 1, × 2, × 3, × 4)
   * 3. Incrementos por features, integraciones, plataformas
   * 4. Descuento si hay múltiples servicios (5-10%)
   */
  calculatePrice(input: PricingInput): PricingResult {
    logger.info('💰 [PRICING] Calculando precio inteligente:', {
      serviceId: input.serviceId,
      complexity: input.complexity,
      additionalServices: input.additionalServices?.length || 0,
    });

    // ─────────────────────────────────────────────────────────────
    // 1. Obtener servicio base
    // ─────────────────────────────────────────────────────────────
    const service = this.getServiceById(input.serviceId);
    if (!service) {
      throw new Error(`Servicio no encontrado: ${input.serviceId}`);
    }

    const precioBase = service.precioBase;

    // ─────────────────────────────────────────────────────────────
    // 2. Aplicar multiplicador según complejidad
    // ─────────────────────────────────────────────────────────────
    const complexity = input.complexity || 'simple';
    const multiplicador = service.multipliers[complexity];
    const precioConMultiplicador = precioBase * multiplicador;

    logger.info(
      `💰 [PRICING] Precio base: S/${precioBase} × ${multiplicador} (${complexity}) = S/${precioConMultiplicador}`
    );

    // ─────────────────────────────────────────────────────────────
    // 3. Calcular incrementos
    // ─────────────────────────────────────────────────────────────
    const incrementos = this.calculateIncrements(input, service);

    // ─────────────────────────────────────────────────────────────
    // 4. Precio final sin descuento
    // ─────────────────────────────────────────────────────────────
    let precioFinal = precioConMultiplicador + incrementos.total;

    // ─────────────────────────────────────────────────────────────
    // 5. Aplicar descuento si hay múltiples servicios
    // ─────────────────────────────────────────────────────────────
    let descuento: PricingResult['descuento'] | undefined;

    if (input.additionalServices && input.additionalServices.length > 0) {
      const porcentajeDescuento = this.calculateBundleDiscount(input.additionalServices.length);
      const montoDescuento = (precioFinal * porcentajeDescuento) / 100;
      const precioConDescuento = precioFinal - montoDescuento;

      descuento = {
        porcentaje: porcentajeDescuento,
        monto: montoDescuento,
        precioConDescuento,
      };

      precioFinal = precioConDescuento;

      logger.info(
        `💰 [PRICING] Descuento por paquete: ${porcentajeDescuento}% (-S/${montoDescuento.toFixed(2)}) = S/${precioFinal.toFixed(2)}`
      );
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Calcular rango de precio (para cotizaciones)
    // ─────────────────────────────────────────────────────────────
    const rango = this.calculatePriceRange(precioFinal, complexity);

    // ─────────────────────────────────────────────────────────────
    // 7. Generar recomendaciones
    // ─────────────────────────────────────────────────────────────
    const recomendaciones = this.generateRecommendations(precioFinal, input);

    // ─────────────────────────────────────────────────────────────
    // 8. Retornar resultado
    // ─────────────────────────────────────────────────────────────
    const result: PricingResult = {
      precioBase,
      multiplicador,
      precioConMultiplicador,
      incrementos,
      precioFinal: Math.round(precioFinal),
      descuento,
      rango: {
        minimo: Math.round(rango.minimo),
        maximo: Math.round(rango.maximo),
      },
      servicio: {
        id: service.id,
        nombre: service.nombre,
        descripcion: service.descripcion,
        categoria: service.categoria,
      },
      recomendaciones,
    };

    logger.info('💰 [PRICING] Resultado final:', {
      precioFinal: result.precioFinal,
      rango: result.rango,
      descuento: result.descuento?.porcentaje,
    });

    return result;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📈 CALCULAR INCREMENTOS POR FEATURES/INTEGRATIONS/PLATFORMS
   * ═══════════════════════════════════════════════════════════════
   */
  private calculateIncrements(
    input: PricingInput,
    service: (typeof NYNEL_SERVICES)[keyof typeof NYNEL_SERVICES]
  ): PricingResult['incrementos'] {
    let features = 0;
    let integrations = 0;
    let platforms = 0;

    // Features específicas que incrementan precio
    if (input.features && input.features.length > 0) {
      const featuresLower = input.features.map((f) => f.toLowerCase());

      // Features premium comunes
      if (featuresLower.some((f) => f.includes('pago') || f.includes('pasarela'))) {
        features += service.precioBase * 0.3; // +30% por pasarela de pagos
      }
      if (
        featuresLower.some((f) => f.includes('chat') || f.includes('tiempo real') || f.includes('websocket'))
      ) {
        features += service.precioBase * 0.2; // +20% por chat en tiempo real
      }
      if (featuresLower.some((f) => f.includes('geolocalización') || f.includes('mapa') || f.includes('gps'))) {
        features += service.precioBase * 0.15; // +15% por geolocalización
      }
      if (
        featuresLower.some((f) => f.includes('video') || f.includes('streaming') || f.includes('llamada'))
      ) {
        features += service.precioBase * 0.25; // +25% por video/streaming
      }
      if (featuresLower.some((f) => f.includes('offline') || f.includes('sin conexión'))) {
        features += service.precioBase * 0.2; // +20% por funcionalidad offline
      }
      if (featuresLower.some((f) => f.includes('ia') || f.includes('inteligencia artificial') || f.includes('machine learning'))) {
        features += service.precioBase * 0.4; // +40% por IA
      }
    }

    // Integraciones externas
    if (input.integrations && input.integrations.length > 0) {
      integrations = input.integrations.length * 500; // S/500 por integración
    }

    // Plataformas (para apps móviles principalmente)
    if (input.platforms && input.platforms.length > 0) {
      if (input.platforms.length === 1) {
        platforms = 0; // Una sola plataforma no incrementa (ya contemplada en base)
      } else if (input.platforms.length === 2) {
        platforms = 0; // iOS + Android (híbrida ya contemplada)
      } else if (input.platforms.length >= 3) {
        platforms = 1000; // Web adicional u otras plataformas
      }
    }

    const total = features + integrations + platforms;

    return {
      features: Math.round(features),
      integrations: Math.round(integrations),
      platforms: Math.round(platforms),
      total: Math.round(total),
    };
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎁 CALCULAR DESCUENTO POR PAQUETE DE MÚLTIPLES SERVICIOS
   * ═══════════════════════════════════════════════════════════════
   */
  private calculateBundleDiscount(numServicios: number): number {
    if (numServicios === 0) return 0;
    if (numServicios === 1) return 5; // 5% por 1 servicio adicional
    if (numServicios === 2) return 7; // 7% por 2 servicios adicionales
    if (numServicios >= 3) return 10; // 10% por 3+ servicios adicionales

    return 0;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📊 CALCULAR RANGO DE PRECIO PARA COTIZACIONES
   * ═══════════════════════════════════════════════════════════════
   */
  private calculatePriceRange(
    precioFinal: number,
    complexity: 'simple' | 'intermedia' | 'compleja' | 'enterprise'
  ): { minimo: number; maximo: number } {
    // Rango de variación según complejidad
    const variacion = complexity === 'enterprise' ? 0.3 : complexity === 'compleja' ? 0.25 : 0.2;

    return {
      minimo: precioFinal * (1 - variacion),
      maximo: precioFinal * (1 + variacion),
    };
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 💡 GENERAR RECOMENDACIONES INTELIGENTES
   * ═══════════════════════════════════════════════════════════════
   */
  private generateRecommendations(precioFinal: number, input: PricingInput): string[] {
    const recomendaciones: string[] = [];

    // Recomendar consultoría presencial si el precio es alto
    if (precioFinal > 20000) {
      recomendaciones.push(
        'Proyecto de alto presupuesto - Recomendamos agendar consultoría presencial para definir alcance exacto'
      );
    }

    // Recomendar paquete de múltiples servicios
    if (!input.additionalServices || input.additionalServices.length === 0) {
      recomendaciones.push(
        'Puedes ahorrar 5-10% combinando este servicio con otros (ej: Web + SEO, App + Chatbot)'
      );
    }

    // Recomendar mantenimiento para proyectos complejos
    if (input.complexity === 'compleja' || input.complexity === 'enterprise') {
      recomendaciones.push('Para proyectos complejos, ofrecemos planes de mantenimiento mensual a partir de S/200/mes');
    }

    // Recomendar hosting/dominio para webs
    if (input.serviceId === 'paginas-web' && input.complexity === 'simple') {
      recomendaciones.push('Hosting y dominio .com/.pe incluidos GRATIS por 1 año');
    }

    return recomendaciones;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🔍 OBTENER SERVICIO POR ID
   * ═══════════════════════════════════════════════════════════════
   */
  private getServiceById(serviceId: string): (typeof NYNEL_SERVICES)[keyof typeof NYNEL_SERVICES] | null {
    const serviceKey = Object.keys(NYNEL_SERVICES).find(
      (key) => NYNEL_SERVICES[key as keyof typeof NYNEL_SERVICES].id === serviceId
    );

    if (!serviceKey) {
      logger.warn(`⚠️ [PRICING] Servicio no encontrado: ${serviceId}`);
      return null;
    }

    return NYNEL_SERVICES[serviceKey as keyof typeof NYNEL_SERVICES];
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📋 LISTAR TODOS LOS SERVICIOS DISPONIBLES
   * ═══════════════════════════════════════════════════════════════
   */
  listServices(): Array<{
    id: string;
    nombre: string;
    precioBase: number;
    descripcion: string;
    categoria: string;
  }> {
    return Object.values(NYNEL_SERVICES).map((service) => ({
      id: service.id,
      nombre: service.nombre,
      precioBase: service.precioBase,
      descripcion: service.descripcion,
      categoria: service.categoria,
    }));
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🔎 BUSCAR SERVICIO POR PALABRAS CLAVE
   * ═══════════════════════════════════════════════════════════════
   */
  findServiceByKeywords(keywords: string): string | null {
    const lowerKeywords = keywords.toLowerCase();

    // Mapeo de keywords a service IDs
    const keywordMap: Record<string, string> = {
      'app móvil': 'apps-moviles',
      'aplicación móvil': 'apps-moviles',
      'mobile app': 'apps-moviles',
      'ios android': 'apps-moviles',
      'web': 'paginas-web',
      'página web': 'paginas-web',
      'sitio web': 'paginas-web',
      'website': 'paginas-web',
      'landing': 'paginas-web',
      'chatbot': 'chatbot-ai',
      'bot': 'chatbot-ai',
      'whatsapp': 'chatbot-ai',
      'automatización': 'chatbot-ai',
      'seo': 'seo-marketing',
      'marketing': 'seo-marketing',
      'publicidad': 'campañas-publicitarias',
      'campaña': 'campañas-publicitarias',
      'email': 'email-marketing',
      'correo': 'email-marketing',
      'analytics': 'analitica-datos',
      'datos': 'analitica-datos',
      'bi': 'analitica-datos',
      'software': 'software-medida',
      'sistema': 'software-medida',
      'crm': 'software-medida',
      'erp': 'software-medida',
    };

    for (const [keyword, serviceId] of Object.entries(keywordMap)) {
      if (lowerKeywords.includes(keyword)) {
        logger.info(`🔎 [PRICING] Servicio encontrado: ${keyword} → ${serviceId}`);
        return serviceId;
      }
    }

    logger.warn(`⚠️ [PRICING] No se encontró servicio para keywords: ${keywords}`);
    return null;
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// EXPORTAR INSTANCIA ÚNICA (SINGLETON)
// ═══════════════════════════════════════════════════════════════════════════
export const pricingLogicService = new PricingLogicService();
