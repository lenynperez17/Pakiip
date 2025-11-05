// ═══════════════════════════════════════════════════════════════════════════
// 🔍 GOOGLE SEARCH SERVICE - BÚSQUEDA EN TIEMPO REAL CON GOOGLE OFICIAL
// ═══════════════════════════════════════════════════════════════════════════
// Servicio para búsquedas web en tiempo real usando Google Custom Search API
//
// CARACTERÍSTICAS:
// ✅ Google Custom Search API oficial
// ✅ 3,000 búsquedas GRATIS por mes (100/día)
// ✅ Resultados de Google Search en tiempo real
// ✅ Caché inteligente de 30 minutos
// ✅ Fallback automático si no hay API key
// ✅ Optimizado para investigación de precios

import axios from 'axios';
import { logger } from '#/utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// INTERFACES
// ─────────────────────────────────────────────────────────────────────────────

export interface SearchResult {
  title: string;
  link: string;
  snippet: string;
  position?: number;
  domain?: string;
}

export interface SearchResponse {
  success: boolean;
  results: SearchResult[];
  searchQuery: string;
  timestamp: Date;
  source: 'google' | 'cache' | 'fallback';
  totalResults?: number;
}

// ─────────────────────────────────────────────────────────────────────────────
// CACHÉ DE BÚSQUEDAS (para evitar búsquedas duplicadas en 30 min)
// ─────────────────────────────────────────────────────────────────────────────

interface CacheEntry {
  results: SearchResult[];
  timestamp: Date;
  totalResults?: number;
}

const searchCache = new Map<string, CacheEntry>();
const CACHE_DURATION_MS = 30 * 60 * 1000; // 30 minutos

/**
 * Limpiar caché de búsquedas antiguas
 */
function cleanCache(): void {
  const now = Date.now();
  for (const [key, entry] of searchCache.entries()) {
    if (now - entry.timestamp.getTime() > CACHE_DURATION_MS) {
      searchCache.delete(key);
    }
  }
}

// Limpiar caché cada 10 minutos
setInterval(cleanCache, 10 * 60 * 1000);

// ─────────────────────────────────────────────────────────────────────────────
// SERVICIO DE BÚSQUEDA EN GOOGLE
// ─────────────────────────────────────────────────────────────────────────────

class GoogleSearchService {
  private googleApiKey: string | undefined;
  private searchEngineId: string | undefined;
  private readonly googleSearchUrl = 'https://www.googleapis.com/customsearch/v1';

  constructor() {
    this.googleApiKey = process.env.GOOGLE_API_KEY;
    this.searchEngineId = process.env.GOOGLE_SEARCH_ENGINE_ID;

    if (!this.googleApiKey || !this.searchEngineId) {
      logger.warn('⚠️  Google Custom Search API no configurada');
      logger.warn('    Necesitas configurar en .env:');
      logger.warn('    1. GOOGLE_API_KEY (obtener en Google Cloud Console)');
      logger.warn('    2. GOOGLE_SEARCH_ENGINE_ID (crear en https://cse.google.com)');
      logger.warn('    ✅ GRATIS: 100 búsquedas/día (3,000/mes)');
    } else {
      logger.info('✅ Google Custom Search API configurada correctamente');
    }
  }

  /**
   * Buscar en Google usando Custom Search API
   *
   * @param query - Término de búsqueda
   * @param numResults - Número de resultados (máx 10 por request)
   * @returns SearchResponse con resultados
   */
  async searchGoogle(query: string, numResults: number = 5): Promise<SearchResponse> {
    try {
      logger.info(`🔍 [GOOGLE SEARCH] Búsqueda: "${query}"`);

      // Validar query
      if (!query || query.trim().length === 0) {
        logger.warn('⚠️  Query vacío, usando fallback');
        return this.getFallbackResults(query);
      }

      // Verificar caché primero (30 minutos)
      const cacheKey = `${query}_${numResults}`;
      const cached = searchCache.get(cacheKey);

      if (cached && Date.now() - cached.timestamp.getTime() < CACHE_DURATION_MS) {
        logger.info('✅ [GOOGLE SEARCH] Resultados desde caché (válido 30 min)');
        return {
          success: true,
          results: cached.results,
          searchQuery: query,
          timestamp: cached.timestamp,
          source: 'cache',
          totalResults: cached.totalResults,
        };
      }

      // Si no hay configuración, usar fallback
      if (!this.googleApiKey || !this.searchEngineId) {
        logger.warn('⚠️  [GOOGLE SEARCH] API no configurada, usando fallback');
        return this.getFallbackResults(query);
      }

      // Realizar búsqueda en Google Custom Search API
      const response = await axios.get(this.googleSearchUrl, {
        params: {
          key: this.googleApiKey,
          cx: this.searchEngineId,
          q: query,
          num: Math.min(numResults, 10), // Máximo 10 por request
          lr: 'lang_es', // Idioma español
          gl: 'pe', // Resultados desde Perú
          safe: 'off', // Sin filtro de contenido
        },
        timeout: 10000, // 10 segundos timeout
      });

      // Procesar resultados
      const items = response.data.items || [];
      const totalResults = parseInt(response.data.searchInformation?.totalResults || '0', 10);

      const results: SearchResult[] = items.map((item: any, index: number) => ({
        title: item.title,
        link: item.link,
        snippet: item.snippet || '',
        position: index + 1,
        domain: this.extractDomain(item.link),
      }));

      // Guardar en caché
      searchCache.set(cacheKey, {
        results,
        timestamp: new Date(),
        totalResults,
      });

      logger.info(
        `✅ [GOOGLE SEARCH] ${results.length} resultados encontrados (${totalResults.toLocaleString()} totales)`
      );

      return {
        success: true,
        results,
        searchQuery: query,
        timestamp: new Date(),
        source: 'google',
        totalResults,
      };
    } catch (error: any) {
      // Detectar si es error de cuota excedida
      if (error.response?.status === 429 || error.response?.data?.error?.code === 429) {
        logger.error('❌ [GOOGLE SEARCH] Cuota de Google excedida (100 búsquedas/día)');
      } else if (error.response?.status === 403) {
        logger.error('❌ [GOOGLE SEARCH] API key inválida o sin permisos');
      } else {
        logger.error('❌ [GOOGLE SEARCH] Error:', error.message);
      }

      // Retornar fallback en caso de error
      return this.getFallbackResults(query);
    }
  }

  /**
   * Buscar precios de servicios específicos (optimizado)
   *
   * @param serviceType - Tipo de servicio (ej: "desarrollo web", "chatbot")
   * @param country - País (default: Peru)
   * @returns SearchResponse con resultados de precios
   */
  async searchServicePrices(
    serviceType: string,
    country: string = 'Peru'
  ): Promise<SearchResponse> {
    const query = `cuanto cuesta ${serviceType} ${country} 2025 precios`;
    logger.info(`💰 [GOOGLE SEARCH] Búsqueda de precios: "${serviceType}" en ${country}`);
    return this.searchGoogle(query, 7);
  }

  /**
   * Buscar comparativa de precios de competencia
   *
   * @param service - Servicio a comparar
   * @returns SearchResponse con precios de competidores
   */
  async searchCompetitorPrices(service: string): Promise<SearchResponse> {
    const query = `${service} peru precios agencias desarrollo software 2025`;
    logger.info(`🔍 [GOOGLE SEARCH] Comparativa de competencia: "${service}"`);
    return this.searchGoogle(query, 8);
  }

  /**
   * Buscar información general sobre un tema
   *
   * @param topic - Tema a buscar
   * @returns SearchResponse con información general
   */
  async searchTopic(topic: string): Promise<SearchResponse> {
    logger.info(`📚 [GOOGLE SEARCH] Búsqueda de tema: "${topic}"`);
    return this.searchGoogle(topic, 6);
  }

  /**
   * Formatear resultados de búsqueda para el AI
   *
   * @param searchResponse - Respuesta de búsqueda
   * @returns String formateado con los resultados
   */
  formatResultsForAI(searchResponse: SearchResponse): string {
    if (!searchResponse.success || searchResponse.results.length === 0) {
      return '\n🔍 No se encontraron resultados de búsqueda relevantes.\n';
    }

    const sourceLabel = {
      google: '🌐 GOOGLE (tiempo real)',
      cache: '💾 CACHÉ (válido 30 min)',
      fallback: '📋 FALLBACK (sin API)',
    }[searchResponse.source];

    let formatted = `\n╔═══════════════════════════════════════════════════════════════╗\n`;
    formatted += `║ ${sourceLabel.padEnd(60, ' ')}║\n`;
    formatted += `║ Query: "${searchResponse.searchQuery}"${' '.repeat(
      Math.max(0, 52 - searchResponse.searchQuery.length)
    )}║\n`;
    if (searchResponse.totalResults) {
      formatted += `║ Total: ${searchResponse.totalResults.toLocaleString()} resultados${' '.repeat(
        Math.max(0, 42 - searchResponse.totalResults.toLocaleString().length)
      )}║\n`;
    }
    formatted += `╚═══════════════════════════════════════════════════════════════╝\n\n`;

    searchResponse.results.forEach((result, index) => {
      formatted += `${index + 1}. **${result.title}**\n`;
      formatted += `   ${result.snippet}\n`;
      formatted += `   🔗 ${result.link}\n`;
      formatted += `   🌐 Dominio: ${result.domain}\n\n`;
    });

    formatted += `───────────────────────────────────────────────────────────────\n`;
    formatted += `Resultados del: ${searchResponse.timestamp.toLocaleString('es-PE', {
      dateStyle: 'short',
      timeStyle: 'short',
    })}\n`;
    formatted += `═══════════════════════════════════════════════════════════════\n`;

    return formatted;
  }

  /**
   * Resultados de respaldo cuando no hay API key o hay error
   *
   * @param query - Query de búsqueda
   * @returns SearchResponse con resultados de fallback
   */
  private getFallbackResults(query: string): SearchResponse {
    logger.info('📋 [GOOGLE SEARCH] Usando resultados de respaldo para: ' + query);

    // Resultados de respaldo actualizados sobre NYNEL MKT y mercado peruano
    const fallbackResults: SearchResult[] = [
      {
        title: 'NYNEL MKT - Agencia de Desarrollo y Marketing Digital en Perú',
        link: 'https://nynelmkt.com',
        snippet:
          '🚀 PROMOCIONES 2025: Chatbot IA WhatsApp S/350 instalación + S/89.90/mes (respuestas 24/7). Páginas web desde S/650. Apps móviles desde S/5,000. Desarrollo a medida, SEO, marketing digital. Contacto: empresarial@nynelmkt.com | WhatsApp: +51 932 255 932',
        position: 1,
        domain: 'nynelmkt.com',
      },
      {
        title: 'Precios de Desarrollo Web en Perú 2025 - Guía de Mercado',
        link: 'https://mercado.com/desarrollo-web-peru-precios',
        snippet:
          'Páginas web en Perú: Landing pages S/800-S/1,500, webs corporativas S/1,500-S/4,000, e-commerce S/3,500-S/10,000. Incluye diseño responsive, hosting inicial y soporte. Tiempos: 2-8 semanas según complejidad.',
        position: 2,
        domain: 'mercado.com',
      },
      {
        title: 'Chatbots con Inteligencia Artificial - Precios Perú 2025',
        link: 'https://tech.com/chatbots-ia-peru',
        snippet:
          'Chatbots IA para WhatsApp, Instagram, Facebook: Instalación S/500-S/1,200 + mantenimiento S/100-S/200/mes. Incluye integración con CRM, respuestas automáticas 24/7, analytics. Ideal para ventas y atención al cliente.',
        position: 3,
        domain: 'tech.com',
      },
      {
        title: 'Desarrollo de Apps Móviles - Costos y Tiempos en Perú',
        link: 'https://apps.com/desarrollo-apps-moviles-peru',
        snippet:
          'Apps móviles iOS y Android: Básicas S/8,000-S/15,000, con backend S/18,000-S/30,000, complejas S/35,000+. Flutter, React Native o nativo. Incluye diseño UX/UI, API REST, testing. Tiempo: 2-6 meses.',
        position: 4,
        domain: 'apps.com',
      },
      {
        title: 'Marketing Digital en Perú - Servicios y Tarifas 2025',
        link: 'https://marketing.com/servicios-marketing-digital-peru',
        snippet:
          'Servicios marketing digital: SEO S/500-S/1,200/mes, SEM S/800-S/2,000/mes + presupuesto ads, Social Media S/600-S/1,500/mes, Email marketing S/300-S/800/mes. Paquetes integrales disponibles con descuento.',
        position: 5,
        domain: 'marketing.com',
      },
      {
        title: 'Software a Medida para Empresas - Precios Perú 2025',
        link: 'https://software.com/desarrollo-software-medida-peru',
        snippet:
          'Desarrollo de software empresarial: Sistemas de gestión desde S/12,000, CRMs desde S/15,000, ERPs desde S/30,000. Incluye análisis, desarrollo, capacitación y soporte. Tecnologías modernas y escalables.',
        position: 6,
        domain: 'software.com',
      },
    ];

    // Filtrar resultados relevantes según la query
    const relevantResults = this.filterRelevantResults(query, fallbackResults);

    return {
      success: true,
      results: relevantResults.length > 0 ? relevantResults : fallbackResults.slice(0, 4),
      searchQuery: query,
      timestamp: new Date(),
      source: 'fallback',
    };
  }

  /**
   * Filtrar resultados relevantes según términos de búsqueda
   */
  private filterRelevantResults(query: string, results: SearchResult[]): SearchResult[] {
    const searchTerms = query
      .toLowerCase()
      .split(' ')
      .filter((term) => term.length > 3); // Solo términos con más de 3 caracteres

    if (searchTerms.length === 0) {
      return results.slice(0, 4); // Retornar primeros 4 si no hay términos válidos
    }

    return results.filter((item) => {
      const text = `${item.title} ${item.snippet}`.toLowerCase();
      return searchTerms.some((term) => text.includes(term));
    });
  }

  /**
   * Extraer dominio limpio de una URL
   */
  private extractDomain(url: string): string {
    try {
      const hostname = new URL(url).hostname;
      return hostname.replace(/^www\./, ''); // Remover www. si existe
    } catch {
      return 'desconocido';
    }
  }

  /**
   * Verificar si el servicio está disponible
   *
   * @returns true si Google Custom Search API está configurada
   */
  isAvailable(): boolean {
    return !!(this.googleApiKey && this.searchEngineId);
  }

  /**
   * Obtener estadísticas del caché
   *
   * @returns Estadísticas de caché (tamaño y tiempo máximo)
   */
  getCacheStats(): { size: number; maxAgeMinutes: number; entries: string[] } {
    return {
      size: searchCache.size,
      maxAgeMinutes: CACHE_DURATION_MS / 1000 / 60,
      entries: Array.from(searchCache.keys()),
    };
  }

  /**
   * Limpiar todo el caché manualmente
   */
  clearCache(): void {
    searchCache.clear();
    logger.info('✅ [GOOGLE SEARCH] Caché limpiado completamente');
  }

  /**
   * Obtener información de configuración
   */
  getConfigInfo(): {
    isConfigured: boolean;
    hasApiKey: boolean;
    hasSearchEngineId: boolean;
    quotaInfo: string;
  } {
    return {
      isConfigured: this.isAvailable(),
      hasApiKey: !!this.googleApiKey,
      hasSearchEngineId: !!this.searchEngineId,
      quotaInfo: '100 búsquedas/día (3,000/mes) GRATIS',
    };
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// EXPORTAR INSTANCIA ÚNICA
// ─────────────────────────────────────────────────────────────────────────────

export const googleSearchService = new GoogleSearchService();
export default googleSearchService;
