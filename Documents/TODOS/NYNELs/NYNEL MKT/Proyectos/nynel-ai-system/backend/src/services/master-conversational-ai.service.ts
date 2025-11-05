// ═══════════════════════════════════════════════════════════════════════════
// 🧠 MASTER CONVERSATIONAL AI SERVICE - SISTEMA UNIVERSAL INTELIGENTE
// ═══════════════════════════════════════════════════════════════════════════
// Sistema que maneja TODAS las conversaciones con inteligencia contextual:
// ✅ Calendario (crear/modificar/cancelar citas)
// ✅ Cotizaciones (precios/presupuestos)
// ✅ Consultas (servicios/tecnologías)
// ✅ Chat General (saludos/preguntas/conversación)

import OpenAI from 'openai';
import { logger } from '#/utils/logger.js';
import { conversationalCalendarAI } from './conversational-calendar-ai.service.js';
import { quotationService } from './quotation.service.js';
import { googleSearchService } from './google-search.service.js';
// ✅ IMPORTAR cliente configurado que apunta a OpenRouter (no crear uno local)
import { openai } from '#/config/ai.js';

// ─────────────────────────────────────────────────────────────────────────────
// 📋 Interfaces
// ─────────────────────────────────────────────────────────────────────────────

interface ConversationalContext {
  userMessage: string;
  conversationHistory: Array<{ role: string; content: string }>;
  subscriberId: string;
  userEmail?: string;
  platform?: string;
  subscriber?: {
    firstName?: string;
    lastName?: string;
    email?: string;
    phone?: string;
    customFields?: Record<string, any>;
  };
}

interface IntelligentDecision {
  intentType: 'calendar' | 'quotation' | 'knowledge' | 'chat' | 'other';
  understanding: string; // Qué entendió del mensaje
  confidence: number; // 0.0 - 1.0
  reasoning: string; // Por qué tomó esta decisión
  suggestedResponse: string; // Respuesta natural y contextual
  generatePdf?: boolean; // ✅ NUEVO: Si debe generar PDF de cotización formal
  actionDetails?: {
    calendarAction?: 'create' | 'modify' | 'cancel' | 'list';
    quotationInfo?: {
      // ═══════════════════════════════════════════════════════════════
      // 📋 INFORMACIÓN DETALLADA DEL PROYECTO (extraída de conversación)
      // ═══════════════════════════════════════════════════════════════
      projectType?: string; // app-movil | web | ecommerce | chatbot | landing | sistema-personalizado
      projectName?: string; // Nombre específico que mencionó ("App para restaurante", "Tienda de ropa", etc.)
      industry?: string; // restaurante | salud | educacion | retail | servicios | tecnologia | otro

      // Funcionalidades específicas que mencionó el cliente
      features?: string[]; // ["autenticación", "pasarela de pagos", "geolocalización", "chat en tiempo real"]

      // Plataformas específicas
      platforms?: string[]; // ["iOS", "Android", "Web", "Desktop"]

      // Integraciones que mencionó
      integrations?: string[]; // ["WhatsApp Business", "MercadoPago", "Google Maps", "Niubiz", "SUNAT"]

      // Tecnologías específicas que mencionó (si aplica)
      technologies?: string[]; // ["Flutter", "React", "Node.js", "Firebase", etc.]

      // Datos básicos
      budget?: string; // "bajo" | "medio" | "alto" | cantidad específica
      urgency?: string; // "normal" | "urgente" | "muy-urgente"
      complexity?: string; // "simple" | "intermedia" | "compleja" | "enterprise"

      // Detalles adicionales específicos que mencionó
      specificRequirements?: string[]; // ["diseño minimalista", "modo offline", "multi-idioma", "reportes PDF"]

      // Usuarios objetivo/escala
      targetUsers?: string; // "pequeño negocio" | "empresa mediana" | "corporativo" | cantidad de usuarios

      // ═══════════════════════════════════════════════════════════════
      // 💡 ANÁLISIS DE NECESIDADES - CAMPOS PROFESIONALES 2025
      // ═══════════════════════════════════════════════════════════════
      // Pain points y problemas actuales del cliente
      problemasIdentificados?: string[]; // ["proceso manual lento", "pérdida de ventas por falta de sistema", "inventario desorganizado"]

      // Objetivos de negocio que quiere lograr
      objetivosNegocio?: string[]; // ["aumentar ventas en 50%", "reducir tiempo operativo", "mejorar experiencia del cliente"]

      // Beneficios que espera obtener
      beneficiosEsperados?: string[]; // ["mayor eficiencia operativa", "mejor control de inventario", "acceso desde cualquier lugar"]

      // Riesgos o consecuencias de NO implementar
      riesgosActuales?: string[]; // ["pérdida de competitividad", "errores manuales costosos", "clientes insatisfechos"]

      // Descripción de la situación actual del negocio
      situacionActual?: string; // "Actualmente manejo todo en Excel y WhatsApp, pierdo muchos pedidos porque no tengo visibilidad en tiempo real"

      // Visión del resultado ideal que quiere lograr
      resultadoDeseado?: string; // "Quiero un sistema donde mis empleados puedan ver todo en tiempo real y los clientes puedan hacer pedidos 24/7"

      // Requisitos especiales o regulaciones
      requisitosEspeciales?: string[]; // ["cumplimiento SUNAT", "facturación electrónica", "reportes detallados"]

      // ═══════════════════════════════════════════════════════════════
      // 📊 MÉTRICAS Y ROI - CAMPOS PROFESIONALES 2025
      // ═══════════════════════════════════════════════════════════════
      // KPIs que espera mejorar
      kpisEsperados?: string[]; // ["tiempo de atención reducido en 60%", "ventas aumentadas en 40%", "errores reducidos en 90%"]

      // ROI estimado o esperado
      roiEstimado?: string; // "300% en el primer año" | "recupero inversión en 6 meses"

      // Tiempo estimado para recuperar la inversión
      tiempoRecuperacion?: string; // "6 meses" | "1 año"
    };
    knowledgeQuery?: string;
  };
  needsMoreInfo?: boolean;
  clarificationQuestions?: string[];
}

interface ActionResult {
  success: boolean;
  response: string;
  intentType: string;
  confidence: number;
  actionTaken?: string;
  eventCreated?: boolean;
  eventModified?: boolean;
  eventCancelled?: boolean;
  eventDetails?: any;
  pdfUrl?: string; // ✅ URL del PDF de cotización generado
}

// ─────────────────────────────────────────────────────────────────────────────
// 🧠 Master Conversational AI Service
// ─────────────────────────────────────────────────────────────────────────────

export class MasterConversationalAI {
  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🧹 UTILIDAD: Limpiar frases internas de las respuestas
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private cleanInternalPhrases(response: string): string {
    let cleanedResponse = response;

    // Lista COMPLETA de frases internas que NO debe ver el cliente
    const forbiddenPhrases = [
      // Búsquedas y análisis
      'busqué en internet',
      'busco en internet',
      'buscando en internet',
      'según google',
      'según internet',
      'según la búsqueda',
      'en base a la búsqueda',
      'tras buscar información',
      'he investigado',
      'he buscado',
      'investigando',
      'según la búsqueda de precios del mercado',
      'de acuerdo a los resultados de búsqueda',
      'basándome en datos externos',
      'basándome en la investigación',

      // Análisis interno
      'según mi análisis',
      'he detectado que',
      'he analizado',
      'analizando',

      // Referencias temporales
      'peruano 2025',
      'del mercado peruano',
      'mercado peruano 2025',
      'actualizado 2025',
    ];

    // Remover frases prohibidas (case-insensitive)
    forbiddenPhrases.forEach(phrase => {
      const regex = new RegExp(phrase, 'gi');
      cleanedResponse = cleanedResponse.replace(regex, '');
    });

    // Limpiar espacios duplicados pero MANTENER saltos de línea
    cleanedResponse = cleanedResponse
      .replace(/  +/g, ' ') // Solo espacios dobles, NO saltos de línea
      .replace(/\n\s*\n\s*\n/g, '\n\n') // Máximo 2 saltos de línea
      .trim();

    return cleanedResponse;
  }

  /**
   * ✅ Formatea la respuesta para que sea ORDENADA, LEGIBLE y AMABLE
   * - Agrega saltos de línea entre ideas
   * - Asegura que no sea muy larga (máx 3-5 líneas)
   * - Mejora la presentación visual
   */
  private formatResponseForReadability(response: string): string {
    let formatted = response.trim();

    // ─────────────────────────────────────────────────────────────────
    // 1. Agregar saltos de línea después de signos de puntuación
    // ─────────────────────────────────────────────────────────────────
    // Después de punto y espacio, si no hay salto de línea, agregar uno
    formatted = formatted.replace(/\. ([A-Z¿¡])/g, '.\n\n$1');

    // Después de pregunta, si no hay salto de línea, agregar uno
    formatted = formatted.replace(/\? ([A-Z¿¡])/g, '?\n\n$1');

    // Después de exclamación, si no hay salto de línea, agregar uno
    formatted = formatted.replace(/! ([A-Z¿¡])/g, '!\n\n$1');

    // ─────────────────────────────────────────────────────────────────
    // 2. Asegurar espacio después de emojis
    // ─────────────────────────────────────────────────────────────────
    formatted = formatted.replace(/([\u{1F300}-\u{1F9FF}])([A-Za-z¿¡])/gu, '$1 $2');

    // ─────────────────────────────────────────────────────────────────
    // 3. Limpiar saltos de línea excesivos (máximo 2)
    // ─────────────────────────────────────────────────────────────────
    formatted = formatted.replace(/\n{3,}/g, '\n\n');

    // ─────────────────────────────────────────────────────────────────
    // 4. Asegurar espacio después de listas de bullets
    // ─────────────────────────────────────────────────────────────────
    formatted = formatted.replace(/([•·-]) /g, '$1 ');

    return formatted.trim();
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔍 EXTRACCIÓN DE INFORMACIÓN PERSONAL - Detectar y extraer datos del cliente
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private extractPersonalInfo(message: string): {
    emails?: string[];
    phones?: string[];
    names?: string[];
  } {
    const extractedInfo: {
      emails?: string[];
      phones?: string[];
      names?: string[];
    } = {};

    // ──────────────────────────────────────────────────────────────────
    // 📧 DETECTAR EMAILS
    // ──────────────────────────────────────────────────────────────────
    const emailRegex = /[\w.-]+@[\w.-]+\.\w+/gi;
    const emailMatches = message.match(emailRegex);
    if (emailMatches && emailMatches.length > 0) {
      extractedInfo.emails = [...new Set(emailMatches.map(e => e.toLowerCase()))];
      logger.info('📧 [EXTRACT INFO] Emails detectados:', extractedInfo.emails);
    }

    // ──────────────────────────────────────────────────────────────────
    // 📱 DETECTAR TELÉFONOS PERUANOS
    // ──────────────────────────────────────────────────────────────────
    // Formatos válidos: +51987654321, 51987654321, 987654321, 9 8765 4321
    const phoneRegex = /(?:\+51|51)?[\s-]?9\d{2}[\s-]?\d{3}[\s-]?\d{3}/g;
    const phoneMatches = message.match(phoneRegex);
    if (phoneMatches && phoneMatches.length > 0) {
      // Normalizar teléfonos (quitar espacios y guiones)
      extractedInfo.phones = [...new Set(
        phoneMatches.map(p => p.replace(/[\s-]/g, ''))
      )];
      logger.info('📱 [EXTRACT INFO] Teléfonos detectados:', extractedInfo.phones);
    }

    // ──────────────────────────────────────────────────────────────────
    // 👤 DETECTAR NOMBRES
    // ──────────────────────────────────────────────────────────────────
    const namePatterns = [
      /(?:me llamo|mi nombre es|soy|mi nombre:)\s+([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*)/gi,
      /(?:llamo)\s+([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*)/gi,
    ];

    const names: string[] = [];
    namePatterns.forEach(pattern => {
      let match;
      while ((match = pattern.exec(message)) !== null) {
        if (match[1]) {
          names.push(match[1].trim());
        }
      }
    });

    if (names.length > 0) {
      extractedInfo.names = [...new Set(names)];
      logger.info('👤 [EXTRACT INFO] Nombres detectados:', extractedInfo.names);
    }

    return extractedInfo;
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 💾 ACTUALIZAR INFORMACIÓN PERSONAL DEL SUBSCRIBER
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private async updateSubscriberPersonalInfo(
    subscriberId: string,
    newInfo: { emails?: string[]; phones?: string[]; names?: string[] }
  ): Promise<void> {
    try {
      // Solo actualizar si hay información nueva
      if (!newInfo.emails && !newInfo.phones && !newInfo.names) {
        return;
      }

      logger.info('💾 [UPDATE SUBSCRIBER] Actualizando información personal...', {
        subscriberId,
        newInfo,
      });

      const { PrismaClient } = await import('@prisma/client');
      const prisma = new PrismaClient();

      try {
        // Obtener subscriber actual
        const subscriber = await prisma.subscriber.findUnique({
          where: { id: subscriberId },
        });

        if (!subscriber) {
          logger.warn('⚠️ [UPDATE SUBSCRIBER] Subscriber no encontrado:', subscriberId);
          return;
        }

        // Obtener customFields existentes
        const currentCustomFields = (subscriber.customFields as Record<string, any>) || {};
        const extractedInfo = currentCustomFields.extractedInfo || {
          emails: [],
          phones: [],
          names: [],
          lastUpdated: null,
        };

        // Agregar nueva información (sin duplicados)
        if (newInfo.emails) {
          extractedInfo.emails = [...new Set([...extractedInfo.emails, ...newInfo.emails])];
        }
        if (newInfo.phones) {
          extractedInfo.phones = [...new Set([...extractedInfo.phones, ...newInfo.phones])];
        }
        if (newInfo.names) {
          extractedInfo.names = [...new Set([...extractedInfo.names, ...newInfo.names])];
        }

        // Actualizar timestamp
        extractedInfo.lastUpdated = new Date().toISOString();

        // Actualizar en base de datos
        await prisma.subscriber.update({
          where: { id: subscriberId },
          data: {
            customFields: {
              ...currentCustomFields,
              extractedInfo,
            },
            // Si tenemos email y no está guardado, actualizarlo también
            ...(newInfo.emails && newInfo.emails.length > 0 && !subscriber.email
              ? { email: newInfo.emails[0] }
              : {}),
            // Si tenemos teléfono y no está guardado, actualizarlo también
            ...(newInfo.phones && newInfo.phones.length > 0 && !subscriber.phone
              ? { phone: newInfo.phones[0] }
              : {}),
          },
        });

        logger.info('✅ [UPDATE SUBSCRIBER] Información actualizada exitosamente');
      } finally {
        await prisma.$disconnect();
      }
    } catch (error: any) {
      logger.error('❌ [UPDATE SUBSCRIBER] Error actualizando información:', error.message);
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔧 UTILIDAD: Parsear JSON de manera robusta con manejo de errores
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private safeJsonParse(content: string, context: string = ''): IntelligentDecision {
    try {
      // Intentar parsear directamente
      const parsed = JSON.parse(content);
      return parsed;
    } catch (error) {
      logger.error(`❌ [SAFE JSON PARSE] Error parseando JSON${context ? ` (${context})` : ''}:`, error);
      logger.error(`📄 [SAFE JSON PARSE] Contenido recibido (primeros 500 chars):`, content.substring(0, 500));

      // Intentar limpiar y re-parsear
      try {
        // Remover espacios/saltos de línea al inicio y final
        let cleaned = content.trim();

        // Si el contenido parece estar envuelto en backticks o comillas, quitarlas
        if (cleaned.startsWith('```json') || cleaned.startsWith('```')) {
          cleaned = cleaned.replace(/^```json?\s*\n?/, '').replace(/\n?```\s*$/, '');
        }

        cleaned = cleaned.trim();

        const parsed = JSON.parse(cleaned);
        logger.info('✅ [SAFE JSON PARSE] JSON parseado exitosamente después de limpieza');
        return parsed;
      } catch (secondError) {
        logger.error(`❌ [SAFE JSON PARSE] Error después de limpieza:`, secondError);

        // Retornar decisión por defecto con el contenido como respuesta
        return {
          intentType: 'chat',
          understanding: 'No se pudo parsear la respuesta del modelo',
          confidence: 0.3,
          reasoning: 'El modelo no devolvió JSON válido - usando fallback',
          suggestedResponse: content.substring(0, 500), // Usar el texto directo como respuesta
          needsMoreInfo: false,
        };
      }
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔍 PASO 1: ANALIZAR MENSAJE - Detectar intención y contexto completo
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async analyzeMessage(context: ConversationalContext): Promise<IntelligentDecision> {
    try {
      const now = new Date();
      const peruTime = new Intl.DateTimeFormat('es-PE', {
        timeZone: 'America/Lima',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        weekday: 'long',
      }).format(now);

      // ✅ CARGAR TODO EL HISTORIAL (sin limitación de .slice)
      const conversationContext = context.conversationHistory
        .map((msg) => `${msg.role === 'user' ? 'Usuario' : 'Asistente'}: ${msg.content}`)
        .join('\n');

      const systemPrompt = `Eres un asistente virtual INTELIGENTE de NYNEL MKT, agencia de desarrollo de software en Perú.

═══════════════════════════════════════════════════════════════════════════
⚠️ INSTRUCCIÓN CRÍTICA #1 - FORMATO DE RESPUESTA (OBLIGATORIO)
═══════════════════════════════════════════════════════════════════════════

🚨 ABSOLUTAMENTE OBLIGATORIO: Debes responder ÚNICAMENTE con JSON válido.
🚨 NO RESPONDAS CON TEXTO CONVERSACIONAL.
🚨 NO RESPONDAS CON EXPLICACIONES FUERA DEL JSON.
🚨 TODA tu respuesta debe ser un objeto JSON válido y parseable.

FORMATO JSON OBLIGATORIO:
{
  "intentType": "calendar|quotation|knowledge|chat|other",
  "understanding": "Explicación de qué entendiste del mensaje completo",
  "confidence": 0.95,
  "reasoning": "Por qué clasificaste esta intención (razonamiento interno)",
  "suggestedResponse": "Tu respuesta natural y conversacional como humano (AQUÍ va el texto amable y corto)",
  "generatePdf": false,
  "actionDetails": {
    "calendarAction": "create|modify|cancel|list (solo si intent es calendar)",
    "quotationInfo": {
      // ═══════════════════════════════════════════════════════════════
      // 🎯 EXTRACCIÓN INTELIGENTE DE INFORMACIÓN DEL PROYECTO
      // ═══════════════════════════════════════════════════════════════
      // ⚠️ MUY IMPORTANTE: Extrae TODA la info de la CONVERSACIÓN COMPLETA
      // NO inventes, NO asumas. Solo lo que el cliente DIJO explícitamente.

      // Tipo de proyecto (lo más específico posible)
      "projectType": "app-movil|web|ecommerce|chatbot|landing|sistema-personalizado",

      // Nombre descriptivo del proyecto (tal como lo mencionó)
      "projectName": "App de delivery para restaurante La Casa del Sabor",

      // Industria/sector específico
      "industry": "restaurante|salud|educacion|retail|servicios|tecnologia|otro",

      // ARRAYS: Funcionalidades ESPECÍFICAS que mencionó
      "features": ["autenticación de usuarios", "pasarela de pagos", "geolocalización", "chat en tiempo real", "notificaciones push"],

      // Plataformas que mencionó explícitamente
      "platforms": ["iOS", "Android", "Web"],

      // Integraciones específicas que pidió
      "integrations": ["WhatsApp Business", "MercadoPago", "Google Maps", "Niubiz"],

      // Tecnologías si las mencionó (sino omitir)
      "technologies": ["Flutter", "Firebase"],

      // Presupuesto
      "budget": "bajo|medio|alto|enterprise|cantidad_especifica|no_especificado",

      // Urgencia
      "urgency": "normal|urgente|muy-urgente|no_especificado",

      // Complejidad (basado en features mencionadas)
      "complexity": "simple|intermedia|compleja|enterprise",

      // Requisitos adicionales específicos
      "specificRequirements": ["diseño minimalista", "modo offline", "multi-idioma español-inglés", "reportes en PDF"],

      // Audiencia/usuarios objetivo
      "targetUsers": "pequeño negocio local|empresa mediana|corporativo|500-1000 usuarios|no_especificado",

      // ═══════════════════════════════════════════════════════════════
      // 💡 ANÁLISIS DE NECESIDADES - PROFESIONAL 2025
      // ═══════════════════════════════════════════════════════════════
      // ⚠️ CRÍTICO: Identifica el CONTEXTO DE NEGOCIO y PAIN POINTS
      // Extrae de frases como: "tengo problemas con...", "actualmente uso...", "necesito porque...", "me está pasando que..."

      // Problemas o pain points que mencionó
      "problemasIdentificados": ["proceso manual muy lento", "pierdo pedidos por WhatsApp", "no tengo control de inventario"],

      // Objetivos de negocio que quiere lograr
      "objetivosNegocio": ["aumentar ventas online", "reducir tiempo de atención", "mejorar experiencia del cliente"],

      // Beneficios que espera obtener con la solución
      "beneficiosEsperados": ["atención 24/7", "automatización de pedidos", "reportes en tiempo real"],

      // Riesgos o consecuencias de NO tener la solución
      "riesgosActuales": ["pérdida de ventas", "clientes insatisfechos", "competencia me está superando"],

      // Descripción de la situación actual (si la menciona)
      "situacionActual": "Actualmente uso Excel y WhatsApp, pierdo muchos pedidos y no tengo visibilidad del negocio",

      // Visión del resultado ideal que quiere (si la menciona)
      "resultadoDeseado": "Quiero que mis clientes puedan ordenar 24/7 y yo tener control total desde mi celular",

      // Requisitos especiales como regulaciones o compliance
      "requisitosEspeciales": ["facturación electrónica SUNAT", "libro de reclamaciones digital"],

      // ═══════════════════════════════════════════════════════════════
      // 📊 MÉTRICAS Y ROI - PROFESIONAL 2025
      // ═══════════════════════════════════════════════════════════════
      // KPIs específicos que mencionó o que son obvios del contexto
      "kpisEsperados": ["reducir tiempo de atención en 60%", "aumentar ventas en 40%", "0 errores en pedidos"],

      // ROI esperado o tiempo de recuperación (si lo menciona)
      "roiEstimado": "300% en el primer año",
      "tiempoRecuperacion": "6-8 meses"
    },
    "knowledgeQuery": "query específico a buscar en docs (solo si necesitas info específica)"
  },
  "needsMoreInfo": false,
  "clarificationQuestions": ["¿Pregunta inteligente si necesitas más info?"]
}

💡 EJEMPLO COMPLETO DE EXTRACCIÓN (CON ANÁLISIS PROFESIONAL):
Conversación:
User: "Necesito una app móvil para mi restaurante"
AI: "¿Qué funciones necesitas?"
User: "Que los clientes puedan pedir delivery, pagar con tarjeta, y recibir notificaciones. Actualmente todo es por WhatsApp y pierdo muchos pedidos"
AI: "¿iOS, Android o ambas?"
User: "Ambas plataformas, quiero integrar MercadoPago. Es que la competencia ya tiene apps y estoy perdiendo ventas. Necesito tener control en tiempo real"

quotationInfo CORRECTO (CON ANÁLISIS DE NEGOCIO):
{
  "projectType": "app-movil",
  "projectName": "App de delivery para restaurante",
  "industry": "restaurante",
  "features": ["pedidos online", "delivery", "pasarela de pagos", "notificaciones push", "control en tiempo real"],
  "platforms": ["iOS", "Android"],
  "integrations": ["MercadoPago"],
  "technologies": [],
  "budget": "no_especificado",
  "urgency": "normal",
  "complexity": "intermedia",
  "specificRequirements": [],
  "targetUsers": "pequeño negocio local",

  // ✅ NUEVOS CAMPOS PROFESIONALES EXTRAÍDOS:
  "problemasIdentificados": ["gestión manual por WhatsApp", "pérdida de pedidos", "falta de control en tiempo real"],
  "objetivosNegocio": ["automatizar pedidos", "recuperar ventas perdidas", "competir con otros restaurantes"],
  "beneficiosEsperados": ["atención 24/7", "control en tiempo real", "no perder pedidos"],
  "riesgosActuales": ["pérdida de ventas frente a competencia", "pedidos perdidos"],
  "situacionActual": "Actualmente gestiona pedidos por WhatsApp sin control en tiempo real",
  "resultadoDeseado": "Sistema automatizado con control total en tiempo real",
  "requisitosEspeciales": [],
  "kpisEsperados": ["reducir pedidos perdidos a 0", "aumentar ventas online"],
  "roiEstimado": "",
  "tiempoRecuperacion": ""
}

⚠️ IMPORTANTE: El campo "suggestedResponse" es donde escribes tu mensaje conversacional amable y corto.
✅ TODO lo demás debe seguir el formato JSON EXACTO mostrado arriba.

═══════════════════════════════════════════════════════════════════════════
📅 CONTEXTO ACTUAL
═══════════════════════════════════════════════════════════════════════════

FECHA Y HORA EN PERÚ: ${peruTime}

CONVERSACIÓN RECIENTE:
${conversationContext || 'Primera interacción'}

INFORMACIÓN SOBRE NYNEL MKT:
- Empresa: Agencia de desarrollo de software en Perú
- Servicios: Desarrollo web, apps móviles, software empresarial, IA, automatización
- Especialidad: Soluciones tecnológicas personalizadas para negocios

═══════════════════════════════════════════════════════════════════════════
🎯 TUS CAPACIDADES
═══════════════════════════════════════════════════════════════════════════

1. 📅 GESTIÓN DE CALENDARIO
   - Crear citas/reuniones
   - Modificar/reprogramar citas
   - Cancelar citas
   - Consultar agenda

2. 💰 COTIZACIONES
   - Dar presupuestos de proyectos
   - Explicar precios y paquetes
   - Recomendar soluciones según presupuesto
   - Preguntas inteligentes para cotizar precisamente

3. 📚 CONSULTAS TÉCNICAS
   - Responder sobre servicios
   - Explicar tecnologías
   - Recomendar soluciones
   - Información sobre procesos

4. 🔍 BÚSQUEDA WEB EN TIEMPO REAL (NUEVO)
   - Buscar precios competitivos en internet
   - Investigar tendencias del mercado
   - Comparar servicios similares
   - Obtener información actualizada 2025
   - Usar cuando necesites datos externos

5. 💬 CONVERSACIÓN GENERAL
   - Saludos y despedidas
   - Preguntas generales
   - Mantener conversación fluida
   - Ser amigable y profesional

═══════════════════════════════════════════════════════════════════════════
🧠 REGLAS DE INTELIGENCIA
═══════════════════════════════════════════════════════════════════════════

1. ANALIZA CONTEXTO COMPLETO
   - No solo palabras clave, entiende la intención real
   - Usa historial conversacional para contexto
   - Detecta emociones y urgencia

2. USA BÚSQUEDA WEB INTERNAMENTE (NUNCA LO MENCIONES AL CLIENTE)
   - Si necesitas datos actualizados → busca SILENCIOSAMENTE
   - Para precios de mercado → busca SIN mencionar que buscaste
   - NUNCA digas al cliente que "buscaste en internet"
   - NUNCA menciones "Google", "búsqueda", "investigué"
   - Habla como si SIEMPRE supieras los datos

3. RESPONDE NATURALMENTE AL CLIENTE (TONO OBLIGATORIO)
   - SÉ SUPER AMABLE: Cálido, cercano, empático
   - SÉ PRECISO: Directo al punto, sin rodeos
   - SÉ SERVICIAL: Ayuda genuinamente, no solo vendas
   - Habla como humano amigable, NO como robot
   - Emojis apropiados (1-2 por mensaje máximo)

   🚨 FORMATO Y LONGITUD DE MENSAJES (MUY IMPORTANTE):
   - Mensajes MUY CORTOS: 3-4 líneas MÁXIMO (no más!)
   - USA saltos de línea para separar ideas claramente
   - Emojis estratégicos (1-2 por mensaje, no más)
   - Listas con bullets (•) solo si son 2-3 items máximo
   - NUNCA bloques de texto largos - los clientes se cansan y no responden

   🚨 NUNCA JAMÁS menciones al cliente:
   - "Busqué en internet" / "Según Google"
   - "Según la búsqueda de precios"
   - "He investigado" / "He analizado"
   - "Basándome en la investigación"
   - Cualquier mención a búsquedas o procesos internos

   ✅ SÍ puedes decir (DE FORMA CORTA):
   - "Para proyectos así → S/X,XXX - S/X,XXX"
   - "El rango está en S/X,XXX aproximadamente"
   - Habla directo y breve

3. SÉ AMIGABLE Y CONSULTIVO (NO VENDEDOR AGRESIVO)
   - PRIMERO conecta con el cliente como humano
   - DESPUÉS ayuda a entender qué necesita
   - FINALMENTE ofrece soluciones (solo si tiene sentido)
   - NO empujes ventas prematuramente
   - NO generes cotizaciones sin que el cliente las pida explícitamente

4. CONSTRUYE CONFIANZA ANTES DE VENDER
   - Responde dudas genuinamente sin agenda de venta
   - Ayuda al cliente a aclarar lo que necesita
   - Educa sobre opciones disponibles
   - Solo sugiere cotización cuando el cliente muestre interés REAL

5. MANEJA AMBIGÜEDAD CON EMPATÍA
   - Si no estás seguro, pregunta naturalmente
   - Si falta información, solicítala de forma amigable
   - Ofrece opciones cuando hay múltiples posibilidades
   - NUNCA asumas que quiere comprar ya

═══════════════════════════════════════════════════════════════════════════
📚 EJEMPLOS DE ANÁLISIS INTELIGENTE
═══════════════════════════════════════════════════════════════════════════

EJEMPLO 1 - Pregunta de Precio (CORTO Y AMIGABLE, SIN PDF):
Usuario: "Cuánto cuesta una app móvil?"
Análisis:
- Intent: quotation
- generatePdf: false (❌ NO generar PDF)
- Understanding: "Cliente explorando, fase inicial"
- Reasoning: "Solo pregunta precio, NO pidió cotización. Respondo BREVE y pregunto."
- Response: "¡Buena pregunta! 😊

Apps móviles → S/5,000 - S/20,000+
(depende de funcionalidades)

¿Qué problema quieres resolver con la app?"

EJEMPLO 2 - Calendario (CORTO):
Usuario: "Quiero agendar una reunión"
Análisis:
- Intent: calendar
- Understanding: "Cliente quiere cita"
- Reasoning: "Delego a calendario"
- Response: "Perfecto 📅

¿Qué día y hora te viene bien?"

EJEMPLO 3 - Consulta (CORTO):
Usuario: "Qué tecnologías usan?"
Análisis:
- Intent: knowledge
- Understanding: "Pregunta stack técnico"
- Reasoning: "Respondo BREVE y claro"
- Response: "Trabajamos con 💻:

• Web → React, Vue, Next.js
• Móvil → Flutter, React Native
• Backend → Node.js, Python
• IA → OpenAI, GPT

¿Tienes proyecto en mente?"

EJEMPLO 4 - Chat General (CORTO):
Usuario: "Hola"
Análisis:
- Intent: chat
- Understanding: "Saludo inicial"
- Reasoning: "Respondo BREVE y amigable"
- Response: "¡Hola! 👋 Bienvenido a NYNEL MKT

¿En qué puedo ayudarte?"

EJEMPLO 5 - Tiempos (CORTO, SIN PDF):
Contexto previo: Preguntó por app móvil
Usuario: "Y en cuánto tiempo lo entregan?"
Análisis:
- Intent: quotation
- generatePdf: false (❌ NO generar PDF)
- Understanding: "Pregunta tiempos"
- Reasoning: "Investiga, NO compra. Respondo BREVE."
- Response: "Los tiempos varían 📅:

• Simple → 1-2 meses
• Medio → 2-3 meses
• Completo → 3-6 meses

¿Tienes fecha objetivo?"

EJEMPLO 6 - Da detalles (CORTO, SIN PDF):
Contexto: Mencionó web
Usuario: "Sí, sería algo informativo para mi empresa"
Análisis:
- Intent: quotation
- generatePdf: false (❌ NO PDF - solo conversa)
- Understanding: "Da detalles, explora"
- Reasoning: "NO pidió cotización. Sigo ayudando BREVE."
- Response: "Perfecto 👍

¿Qué tipo de empresa es?
¿Qué secciones necesitas mostrar?"

EJEMPLO 7 - Solicitud EXPLÍCITA (CORTO, CON PDF ✅):
Usuario: "¿Me puedes enviar una cotización?"
Análisis:
- Intent: quotation
- generatePdf: true (✅ SÍ PDF - solicitud explícita)
- Understanding: "PIDE cotización"
- Reasoning: "Palabra clave: 'enviar cotización'. Genera PDF."
- Response: "¡Claro! 😊

Te preparo la cotización ahora mismo...

📄 Un momento"

EJEMPLO 8 - Pregunta presupuesto (CORTO, SIN PDF):
Usuario: "Qué presupuesto necesito para una app?"
Análisis:
- Intent: quotation
- generatePdf: false (❌ NO PDF - solo pregunta rango)
- Understanding: "Pregunta presupuesto necesario"
- Reasoning: "NO pide cotización. Solo orienta BREVE."
- Response: "Para apps móviles 📱:

• Básica → S/5,000 - S/8,000
• Media → S/10,000 - S/15,000
• Completa → S/20,000+

¿Qué funciones necesitas?"

═══════════════════════════════════════════════════════════════════════════
🚨 REGLAS PARA generatePdf (SÚPER RESTRICTIVO - MUY IMPORTANTE)
═══════════════════════════════════════════════════════════════════════════

⚠️ FILOSOFÍA: Primero AYUDAR y CONECTAR, después VENDER
NO asaltes clientes con cotizaciones. Construye confianza PRIMERO.

✅ generatePdf: true SOLAMENTE cuando el cliente usa PALABRAS EXPLÍCITAS:
  1. "Envíame una cotización" / "Quiero una cotización"
  2. "Mándame un presupuesto" / "Dame un presupuesto"
  3. "Necesito una propuesta formal"
  4. "¿Me puedes cotizar esto?"
  5. "Envíame los precios por escrito"
  6. "Quiero la cotización de lo que hablamos"

❌ generatePdf: false en TODOS estos casos (NO ASALTES AL CLIENTE):
  1. Solo pregunta "¿cuánto cuesta?" → Dale RANGO, NO PDF
  2. Menciona "presupuesto" para SABER qué necesita → Orienta, NO vendas
  3. Da detalles del proyecto → Sigue conversando, NO generes PDF
  4. Dice "sí", "ok", "dale" → Puede ser confirmación de OTRA cosa, NO asumas
  5. Pregunta tiempos de entrega → Da info, NO vendas
  6. Está explorando opciones → Ayuda, NO empujes venta
  7. Menciona un servicio → Explica, NO cotices automáticamente
  8. Responde preguntas tuyas → Continúa conversación, NO generes PDF

🎯 REGLA DE ORO:
SI TIENES DUDA de si generar PDF → NO LO GENERES
Es mejor quedarse corto que asustar al cliente siendo muy vendedor

🎯 ANÁLISIS OBLIGATORIO DEL CONTEXTO (MUY IMPORTANTE):
⚠️ REGLA DE ORO: Lee TODA la conversación, NO solo el último mensaje

PROCESO DE DETECCIÓN:
1. Revisa TODO el historial de conversación
2. Identifica en QUÉ mensajes anteriores se mencionó el tipo de proyecto
3. SI en mensajes previos se mencionó "web", "app", "ecommerce", etc.:
   → ESE es el projectType, NO lo cambies por el último mensaje
4. El último mensaje puede ser solo confirmación ("sí", "dale", "lo más simple")
   → Esto NO cambia el tipo de proyecto ya identificado

EJEMPLOS CRÍTICOS:
❌ MAL:
  Mensaje 1: "Necesito un sitio web informativo"
  Mensaje 2: "Quiero lo más simple posible"
  Detección INCORRECTA: projectType: "chatbot" (porque "simple" = chatbot)

✅ BIEN:
  Mensaje 1: "Necesito un sitio web informativo"
  Mensaje 2: "Quiero lo más simple posible"
  Detección CORRECTA: projectType: "web", complexity: "basico"
  (Porque en mensaje previo claramente dijo "sitio web")

MAPEO DE DETECCIÓN:
- "sitio web" / "página web" / "web informativo" → projectType: "web"
- "app móvil" / "aplicación móvil" → projectType: "mobile"
- "tienda online" / "e-commerce" / "carrito" → projectType: "ecommerce"
- "chatbot" / "bot" / "asistente virtual" → projectType: "chatbot"
- Si menciona SOLO "simple" o "básico" sin contexto previo → pregunta qué tipo de proyecto

💡 FILOSOFÍA DE INTERACCIÓN (MUY IMPORTANTE - LEE BIEN):
- PRIMERO: Sé SUPER AMABLE - conecta como humano cercano (BREVE)
- SEGUNDO: Sé SERVICIAL - ayuda genuinamente sin presionar (3-4 LÍNEAS)
- TERCERO: Sé PRECISO - responde directo y claro (SIN RODEOS)
- CUARTO: Educa con paciencia pero CONCISO (MÁXIMO 4 LÍNEAS)
- FINALMENTE: Si PIDE cotización explícita → genera PDF

⚠️ NUNCA JAMÁS:
- Asumas que quiere comprar ya
- Generes PDF sin solicitud EXPLÍCITA
- Seas vendedor agresivo o insistente
- Escribas bloques largos (máx 4 líneas) - clientes se cansan!
- Menciones búsquedas o análisis internos
- Uses más de 2 emojis por mensaje

✅ SIEMPRE (SÚPER IMPORTANTE):
- Mensajes de 3-4 líneas MÁXIMO (no más!)
- Saltos de línea claros entre ideas
- Tono AMABLE, PRECISO y SERVICIAL
- 1-2 emojis estratégicos por mensaje
- Bullets (•) solo si son 2-3 items
- Deja que el CLIENTE decida cuándo cotizar
`;

      const messages: OpenAI.Chat.ChatCompletionMessageParam[] = [
        { role: 'system', content: systemPrompt },
      ];

      // ✅ Agregar TODO el historial conversacional (sin limitación)
      if (context.conversationHistory && context.conversationHistory.length > 0) {
        context.conversationHistory.forEach((msg) => {
          messages.push({
            role: msg.role as 'user' | 'assistant',
            content: msg.content,
          });
        });
      }

      // Agregar mensaje actual
      messages.push({
        role: 'user',
        content: `Analiza este mensaje y decide cómo responder:\n\n"${context.userMessage}"`,
      });

      // ─────────────────────────────────────────────────────────────────
      // 🔧 DEFINIR FUNCTION CALLING TOOLS (Búsqueda Web)
      // ─────────────────────────────────────────────────────────────────
      const tools: OpenAI.Chat.ChatCompletionTool[] = [
        {
          type: 'function',
          function: {
            name: 'search_google',
            description: 'Buscar información actualizada en Google. Usa esto cuando necesites: precios de competencia, información del mercado 2025, comparativas de servicios, o datos actualizados que no sabes.',
            parameters: {
              type: 'object',
              properties: {
                query: {
                  type: 'string',
                  description: 'Query de búsqueda en español. Ej: "precios desarrollo web Peru 2025", "cuanto cuesta chatbot IA"',
                },
                numResults: {
                  type: 'number',
                  description: 'Número de resultados (default: 5, max: 10)',
                  default: 5,
                },
              },
              required: ['query'],
            },
          },
        },
        {
          type: 'function',
          function: {
            name: 'search_service_prices',
            description: 'Buscar precios específicos de un servicio tecnológico en Perú. Optimizado para encontrar rangos de precios del mercado.',
            parameters: {
              type: 'object',
              properties: {
                serviceType: {
                  type: 'string',
                  description: 'Tipo de servicio. Ej: "desarrollo web", "app movil", "chatbot IA", "marketing digital"',
                },
                country: {
                  type: 'string',
                  description: 'País para búsqueda (default: Peru)',
                  default: 'Peru',
                },
              },
              required: ['serviceType'],
            },
          },
        },
      ];

      logger.info('🧠 [MASTER AI] Analizando mensaje con DeepSeek v3 (OpenRouter) + Function Calling...');

      const response = await openai.chat.completions.create({
        model: 'deepseek/deepseek-chat-v3.1', // ✅ Usando DeepSeek v3.1 vía OpenRouter (más económico y potente)
        messages,
        temperature: 0.5, // Balance entre creatividad y consistencia
        // response_format removido - causa conflicto con tools en OpenRouter/DeepSeek
        tools, // ✅ Habilitar Function Calling
        tool_choice: 'auto', // DeepSeek v3.1 decide cuándo usar las herramientas
      });

      // ─────────────────────────────────────────────────────────────────
      // 🔍 DETECTAR SI DEEPSEEK V3.1 QUIERE USAR FUNCTION CALLING
      // ─────────────────────────────────────────────────────────────────
      const message = response.choices[0].message;

      if (message.tool_calls && message.tool_calls.length > 0) {
        logger.info('🔍 [MASTER AI] DeepSeek v3.1 solicita búsqueda web');

        // Ejecutar búsquedas solicitadas
        const toolMessages: OpenAI.Chat.ChatCompletionMessageParam[] = [
          ...messages,
          message, // Mensaje original con tool_calls
        ];

        for (const toolCall of message.tool_calls) {
          const functionName = toolCall.function.name;
          const functionArgs = JSON.parse(toolCall.function.arguments);

          logger.info(`🛠️  [FUNCTION CALLING] Ejecutando: ${functionName}`, functionArgs);

          let searchResult;

          try {
            if (functionName === 'search_google') {
              searchResult = await googleSearchService.searchGoogle(
                functionArgs.query,
                functionArgs.numResults || 5
              );
            } else if (functionName === 'search_service_prices') {
              searchResult = await googleSearchService.searchServicePrices(
                functionArgs.serviceType,
                functionArgs.country || 'Peru'
              );
            }

            // Formatear resultados para el AI
            const formattedResults = googleSearchService.formatResultsForAI(searchResult!);

            // Agregar resultado de la tool como mensaje
            toolMessages.push({
              role: 'tool',
              tool_call_id: toolCall.id,
              content: formattedResults,
            });

            logger.info(
              `✅ [FUNCTION CALLING] ${searchResult!.results.length} resultados encontrados`
            );
          } catch (error: any) {
            logger.error(`❌ [FUNCTION CALLING] Error en ${functionName}:`, error.message);
            toolMessages.push({
              role: 'tool',
              tool_call_id: toolCall.id,
              content: 'Error al buscar información. Continúa sin estos datos.',
            });
          }
        }

        // ─────────────────────────────────────────────────────────────────
        // 🔄 SEGUNDA LLAMADA A DEEPSEEK V3.1 CON RESULTADOS DE BÚSQUEDA
        // ─────────────────────────────────────────────────────────────────
        logger.info('🔄 [MASTER AI] Procesando respuesta con resultados de búsqueda...');

        const finalResponse = await openai.chat.completions.create({
          model: 'deepseek/deepseek-chat-v3.1', // ✅ Usando DeepSeek v3.1 vía OpenRouter (más económico y potente)
          messages: toolMessages,
          temperature: 0.5,
          // response_format removido - DeepSeek v3.1 infiere JSON del system prompt
        });

        const decision: IntelligentDecision = this.safeJsonParse(
          finalResponse.choices[0].message.content || '{}',
          'respuesta con búsqueda web'
        );

        logger.info('🎯 [MASTER AI] Decisión inteligente (con búsqueda web):', {
          intent: decision.intentType,
          confidence: decision.confidence,
          understanding: decision.understanding,
        });

        return decision;
      }

      const decision: IntelligentDecision = this.safeJsonParse(
        response.choices[0].message.content || '{}',
        'respuesta directa'
      );

      logger.info('🎯 [MASTER AI] Decisión inteligente:', {
        intent: decision.intentType,
        confidence: decision.confidence,
        understanding: decision.understanding,
      });

      return decision;
    } catch (error) {
      logger.error('❌ [MASTER AI] Error en análisis:', error);
      return {
        intentType: 'other',
        understanding: 'Error al analizar mensaje',
        confidence: 0,
        reasoning: 'Error técnico',
        suggestedResponse: 'Disculpa, tuve un problema procesando tu mensaje. ¿Podrías repetir?',
        needsMoreInfo: false,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * ⚡ PASO 2: EJECUTAR ACCIÓN - Según la intención detectada
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async executeAction(
    decision: IntelligentDecision,
    context: ConversationalContext
  ): Promise<ActionResult> {
    try {
      logger.info(`⚡ [MASTER AI] Ejecutando acción para intent: ${decision.intentType}`);

      // ──────────────────────────────────────────────────────────────────
      // 📅 CALENDARIO - Delegar a conversationalCalendarAI especializado
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'calendar') {
        logger.info('📅 [MASTER AI] Delegando a Calendar AI...');

        const calendarResult = await conversationalCalendarAI.processConversationalMessage({
          userMessage: context.userMessage,
          conversationHistory: context.conversationHistory,
          subscriberId: context.subscriberId,
          userEmail: context.userEmail,
        });

        return {
          success: calendarResult.result.success,
          response: calendarResult.result.response,
          intentType: 'calendar',
          confidence: calendarResult.decision.confidence,
          actionTaken: calendarResult.decision.suggestedAction,
          eventCreated: calendarResult.result.eventCreated,
          eventModified: calendarResult.result.eventModified,
          eventCancelled: calendarResult.result.eventCancelled,
          eventDetails: calendarResult.result.eventDetails,
        };
      }

      // ──────────────────────────────────────────────────────────────────
      // 💰 COTIZACIÓN - Generar PDF cuando el cliente confirma
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'quotation' && decision.generatePdf === true) {
        logger.info('💰 [MASTER AI] Cliente solicita cotización - Generando PDF...');

        try {
          // Extraer datos del subscriber para la cotización
          const subscriber = context.subscriber || {};
          const customFields = subscriber.customFields || {};

          const clientName = `${subscriber.firstName || ''} ${subscriber.lastName || ''}`.trim() || 'Cliente';

          // MEJORADO: Extraer descripción de TODA la conversación, no solo último mensaje
          const fullConversationContext = context.conversationHistory
            .filter((msg) => msg.role === 'user') // Solo mensajes del usuario
            .map((msg) => msg.content)
            .join(' | ');

          const descripcionCompleta = fullConversationContext || context.userMessage;

          // ═════════════════════════════════════════════════════════════════════
          // 📋 CONSTRUIR DATOS COMPLETOS Y DETALLADOS PARA LA COTIZACIÓN
          // ═════════════════════════════════════════════════════════════════════
          // Extrae TODA la información de quotationInfo (que viene del Master AI)
          const quotationInfo = decision.actionDetails?.quotationInfo || {};

          const quotationData = {
            // ═══════════════════════════════════════════════════════════════
            // 👤 DATOS DEL CLIENTE
            // ═══════════════════════════════════════════════════════════════
            subscriberId: context.subscriberId,
            clientName,
            clientEmail: subscriber.email || context.userEmail,
            clientPhone: subscriber.phone,
            empresa: customFields.empresa,

            // ═══════════════════════════════════════════════════════════════
            // 📋 DATOS BÁSICOS (legacy, mantener compatibilidad)
            // ═══════════════════════════════════════════════════════════════
            tipoProyecto: customFields.tipoProyecto || quotationInfo.projectType || 'web',
            presupuestoEstimado: customFields.presupuestoEstimado || quotationInfo.budget,
            descripcionProyecto: descripcionCompleta, // ✅ TODA la conversación
            urgencia: customFields.urgencia || quotationInfo.urgency,

            // ═══════════════════════════════════════════════════════════════
            // 🎯 DATOS DETALLADOS DEL PROYECTO
            // ═══════════════════════════════════════════════════════════════
            projectType: quotationInfo.projectType, // app-movil | web | ecommerce | chatbot
            projectName: quotationInfo.projectName, // Nombre específico del proyecto
            industry: quotationInfo.industry, // restaurante | salud | educacion | etc

            // Arrays de información técnica específica
            features: quotationInfo.features || [], // Funcionalidades mencionadas
            platforms: quotationInfo.platforms || [], // iOS, Android, Web
            integrations: quotationInfo.integrations || [], // WhatsApp, MercadoPago, etc
            technologies: quotationInfo.technologies || [], // Flutter, React, Firebase
            specificRequirements: quotationInfo.specificRequirements || [], // Requisitos adicionales
            targetUsers: quotationInfo.targetUsers, // Audiencia objetivo
            complexity: quotationInfo.complexity, // simple | intermedia | compleja | enterprise

            // ═══════════════════════════════════════════════════════════════
            // 💡 ANÁLISIS DE NECESIDADES - PROFESIONAL 2025
            // ═══════════════════════════════════════════════════════════════
            problemasIdentificados: quotationInfo.problemasIdentificados || [],
            objetivosNegocio: quotationInfo.objetivosNegocio || [],
            beneficiosEsperados: quotationInfo.beneficiosEsperados || [],
            riesgosActuales: quotationInfo.riesgosActuales || [],
            situacionActual: quotationInfo.situacionActual,
            resultadoDeseado: quotationInfo.resultadoDeseado,
            requisitosEspeciales: quotationInfo.requisitosEspeciales || [],

            // ═══════════════════════════════════════════════════════════════
            // 📊 MÉTRICAS Y ROI - PROFESIONAL 2025
            // ═══════════════════════════════════════════════════════════════
            kpisEsperados: quotationInfo.kpisEsperados || [],
            roiEstimado: quotationInfo.roiEstimado,
            tiempoRecuperacion: quotationInfo.tiempoRecuperacion,
          };

          logger.info('📄 [QUOTATION] Generando PDF con datos:', quotationData);

          // Generar PDF y enviar
          const quotationResult = await quotationService.generateQuotation(quotationData);

          if (quotationResult.success) {
            logger.info('✅ [QUOTATION] PDF generado exitosamente:', quotationResult.pdfUrl);

            return {
              success: true,
              response: this.formatResponseForReadability(
                this.cleanInternalPhrases(decision.suggestedResponse) + `\n\n📄 **Tu cotización personalizada está lista:** ${quotationResult.pdfUrl}`
              ),
              intentType: 'quotation',
              confidence: decision.confidence,
              actionTaken: 'quotation_pdf_generated',
              pdfUrl: quotationResult.pdfUrl,
            };
          } else {
            logger.warn('⚠️ [QUOTATION] Error generando PDF - Enviando respuesta de texto');

            return {
              success: true,
              response: this.formatResponseForReadability(
                this.cleanInternalPhrases(decision.suggestedResponse)
              ),
              intentType: 'quotation',
              confidence: decision.confidence,
              actionTaken: 'quotation_text_only',
            };
          }
        } catch (error) {
          logger.error('❌ [QUOTATION] Error en generación de PDF:', error);

          // Fallback: responder con texto si falla el PDF
          return {
            success: true,
            response: this.formatResponseForReadability(
              this.cleanInternalPhrases(decision.suggestedResponse)
            ),
            intentType: 'quotation',
            confidence: decision.confidence,
            actionTaken: 'quotation_text_fallback',
          };
        }
      }

      // ──────────────────────────────────────────────────────────────────
      // 💬 COTIZACIÓN SIN PDF - Solo dar información de precios
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'quotation' && decision.generatePdf === false) {
        logger.info('💬 [MASTER AI] Respondiendo sobre precios (sin generar PDF)...');

        return {
          success: true,
          response: this.formatResponseForReadability(
            this.cleanInternalPhrases(decision.suggestedResponse)
          ),
          intentType: 'quotation',
          confidence: decision.confidence,
          actionTaken: 'quotation_info_only',
        };
      }

      // ──────────────────────────────────────────────────────────────────
      // 📚 CONSULTA TÉCNICA - DeepSeek v3 ya respondió con conocimiento
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'knowledge') {
        logger.info('📚 [MASTER AI] Respondiendo consulta técnica...');

        // DeepSeek v3 ya tiene contexto de NYNEL MKT en el system prompt
        // La respuesta ya está en decision.suggestedResponse
        return {
          success: true,
          response: this.formatResponseForReadability(
            this.cleanInternalPhrases(decision.suggestedResponse)
          ),
          intentType: 'knowledge',
          confidence: decision.confidence,
          actionTaken: 'knowledge_provided',
        };
      }

      // ──────────────────────────────────────────────────────────────────
      // 💬 CHAT GENERAL - Conversación natural
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'chat') {
        logger.info('💬 [MASTER AI] Conversación general...');

        return {
          success: true,
          response: this.formatResponseForReadability(
            this.cleanInternalPhrases(decision.suggestedResponse)
          ),
          intentType: 'chat',
          confidence: decision.confidence,
          actionTaken: 'chat_response',
        };
      }

      // ──────────────────────────────────────────────────────────────────
      // ❓ OTROS - Respuesta genérica
      // ──────────────────────────────────────────────────────────────────
      return {
        success: true,
        response: this.formatResponseForReadability(
          this.cleanInternalPhrases(decision.suggestedResponse)
        ),
        intentType: 'other',
        confidence: decision.confidence,
        actionTaken: 'general_response',
      };
    } catch (error) {
      logger.error('❌ [MASTER AI] Error ejecutando acción:', error);
      return {
        success: false,
        response: 'Disculpa, tuve un problema. ¿Podrías intentar de nuevo?',
        intentType: decision.intentType,
        confidence: 0,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🚀 MÉTODO PRINCIPAL - Procesar cualquier mensaje
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async processMessage(context: ConversationalContext): Promise<ActionResult> {
    logger.info('🚀 [MASTER AI] Iniciando procesamiento inteligente universal');

    try {
      // Paso 0: Extraer información personal del mensaje del usuario
      logger.info('🔍 [MASTER AI] Extrayendo información personal del mensaje...');
      const personalInfo = this.extractPersonalInfo(context.userMessage);

      // Si se detectó información personal, actualizar en base de datos
      if (personalInfo.emails || personalInfo.phones || personalInfo.names) {
        logger.info('💾 [MASTER AI] Información personal detectada, guardando en BD...');
        // Ejecutar en background para no bloquear la respuesta
        this.updateSubscriberPersonalInfo(context.subscriberId, personalInfo).catch(error => {
          logger.error('❌ [MASTER AI] Error guardando información personal:', error);
        });
      }

      // Paso 1: Analizar mensaje y detectar intención
      const decision = await this.analyzeMessage(context);

      // Paso 2: Ejecutar acción según intención
      const result = await this.executeAction(decision, context);

      logger.info('✅ [MASTER AI] Procesamiento completado:', {
        intent: result.intentType,
        success: result.success,
      });

      return result;
    } catch (error) {
      logger.error('❌ [MASTER AI] Error en procesamiento:', error);
      return {
        success: false,
        response: 'Disculpa, tuve un problema procesando tu mensaje. ¿Podrías repetir?',
        intentType: 'error',
        confidence: 0,
      };
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 🎯 Singleton Instance
// ─────────────────────────────────────────────────────────────────────────────

export const masterConversationalAI = new MasterConversationalAI();
