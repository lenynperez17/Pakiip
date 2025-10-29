// ═══════════════════════════════════════════════════════════════════════════
// 🧠 MASTER CONVERSATIONAL AI SERVICE - SISTEMA UNIVERSAL INTELIGENTE
// ═══════════════════════════════════════════════════════════════════════════
// Sistema que maneja TODAS las conversaciones con inteligencia contextual:
// ✅ Calendario (crear/modificar/cancelar citas)
// ✅ Cotizaciones (precios/presupuestos)
// ✅ Consultas (servicios/tecnologías)
// ✅ Chat General (saludos/preguntas/conversación)

import OpenAI from 'openai';
import { logger } from '@/utils/logger';
import { conversationalCalendarAI } from './conversational-calendar-ai.service';
import { quotationService } from './quotation.service';
import { googleSearchService } from './google-search.service';

const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });

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
      projectType?: string;
      budget?: string;
      urgency?: string;
      complexity?: string;
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

      const conversationContext = context.conversationHistory
        .slice(-5)
        .map((msg) => `${msg.role === 'user' ? 'Usuario' : 'Asistente'}: ${msg.content}`)
        .join('\n');

      const systemPrompt = `Eres un asistente virtual INTELIGENTE de NYNEL MKT, agencia de desarrollo de software en Perú.

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

2. USA BÚSQUEDA WEB CUANDO SEA NECESARIO
   - Si te preguntan precios de competencia → busca en Google
   - Si necesitas información actualizada 2025 → busca
   - Si comparan con otras agencias → busca sus precios
   - Para cotizaciones profesionales → busca precios de mercado
   - NUNCA inventes precios de competencia

3. RESPONDE NATURALMENTE
   - Habla como humano, no como robot
   - Usa emojis apropiadamente (no exageres)
   - Sé conversacional pero profesional
   - NO digas "He detectado que..." o "Según mi análisis..."

3. SÉ PROACTIVO
   - Sugiere soluciones
   - Anticipa necesidades
   - Ofrece información útil
   - Haz preguntas inteligentes

4. MANEJA AMBIGÜEDAD
   - Si no estás seguro, pregunta naturalmente
   - Si falta información, solicítala de forma amigable
   - Ofrece opciones cuando hay múltiples posibilidades

═══════════════════════════════════════════════════════════════════════════
📚 EJEMPLOS DE ANÁLISIS INTELIGENTE
═══════════════════════════════════════════════════════════════════════════

EJEMPLO 1 - Pregunta de Precio (SIN PDF):
Usuario: "Cuánto cuesta una app móvil?"
Análisis:
- Intent: quotation (tema de precios)
- generatePdf: false (❌ NO generar PDF - solo pregunta precio)
- Understanding: "Cliente interesado en desarrollo de app móvil, no especificó tipo ni funcionalidades"
- Reasoning: "Solo pregunta precio, no pidió cotización formal. Doy rangos y pregunto detalles."
- Response: "Las apps móviles tienen diferentes precios según funcionalidades 😊
  - App básica (catálogo): S/8,000 - S/12,000
  - App con usuarios y base datos: S/15,000 - S/22,000
  - App compleja (estilo Uber/delivery): S/25,000+

  ¿Qué tipo de app necesitas y qué funcionalidades debe tener?"

EJEMPLO 2 - Calendario:
Usuario: "Quiero agendar una reunión"
Análisis:
- Intent: calendar (crear cita)
- Understanding: "Cliente quiere agendar reunión, no especificó fecha/hora"
- Reasoning: "Delego a sistema de calendario que pedirá detalles"
- Response: "Perfecto, agendemos tu reunión. ¿Qué día y hora te viene bien?"

EJEMPLO 3 - Consulta:
Usuario: "Qué tecnologías usan?"
Análisis:
- Intent: knowledge (consulta técnica)
- Understanding: "Cliente pregunta sobre stack tecnológico de NYNEL"
- Reasoning: "Doy info clara y concisa sobre tecnologías principales"
- Response: "Trabajamos con las últimas tecnologías:
  • Frontend: React, Next.js, Vue
  • Backend: Node.js, Python, PHP
  • Móvil: Flutter, React Native
  • Base de datos: PostgreSQL, MongoDB
  • IA: OpenAI, LangChain

  ¿Tienes algún proyecto en mente?"

EJEMPLO 4 - Chat General:
Usuario: "Hola"
Análisis:
- Intent: chat (saludo)
- Understanding: "Cliente inicia conversación con saludo"
- Reasoning: "Respondo amigablemente y ofrezco ayuda"
- Response: "¡Hola! 👋 Bienvenido a NYNEL MKT. Soy tu asistente virtual y puedo ayudarte con:

  • Agendar una reunión 📅
  • Cotizar tu proyecto 💰
  • Información sobre servicios 📚

  ¿En qué puedo ayudarte hoy?"

EJEMPLO 5 - Contexto Conversacional (SIN PDF):
Contexto previo: Usuario preguntó por app móvil
Usuario: "Y en cuánto tiempo lo entregan?"
Análisis:
- Intent: quotation (tema de precios/tiempos)
- generatePdf: false (❌ NO generar PDF - solo pregunta información)
- Understanding: "Pregunta sobre tiempo de entrega, se refiere a la app móvil que mencionó antes"
- Reasoning: "Uso contexto previo para entender que habla de app móvil. Solo da información, no pide cotización."
- Response: "El tiempo de desarrollo depende de la complejidad:
  • App básica: 4-6 semanas
  • App con funcionalidades medias: 8-12 semanas
  • App compleja: 3-6 meses

  Para tu caso específico, ¿qué funcionalidades necesitas en la app?"

EJEMPLO 6 - Solicitud EXPLÍCITA de Cotización (CON PDF):
Usuario: "Necesito una cotización formal para mi proyecto de e-commerce"
Análisis:
- Intent: quotation
- generatePdf: true (✅ SÍ generar PDF - solicita cotización formal)
- Understanding: "Cliente solicita explícitamente una cotización formal para proyecto e-commerce"
- Reasoning: "Usa palabras clave: 'cotización formal'. Cliente quiere documento oficial."
- Response: "Perfecto, voy a preparar tu cotización formal para el proyecto de e-commerce.

  Para darte el mejor presupuesto, cuéntame:
  • ¿Cuántos productos aproximadamente manejarás?
  • ¿Necesitas pasarela de pagos? ¿Cuál?
  • ¿Tienes un plazo específico?"

EJEMPLO 7 - Solicitud de Presupuesto (CON PDF):
Usuario: "Quiero un presupuesto para desarrollar una app móvil para mi restaurante"
Análisis:
- Intent: quotation
- generatePdf: true (✅ SÍ generar PDF - solicita presupuesto con detalles)
- Understanding: "Cliente pide presupuesto con información específica del proyecto"
- Reasoning: "Dice 'presupuesto' y da detalles del proyecto. Genera PDF."
- Response: "Excelente, voy a preparar un presupuesto personalizado para tu app de restaurante."

═══════════════════════════════════════════════════════════════════════════
📤 FORMATO DE RESPUESTA JSON
═══════════════════════════════════════════════════════════════════════════

{
  "intentType": "calendar|quotation|knowledge|chat|other",
  "understanding": "Explicación de qué entendiste del mensaje completo",
  "confidence": 0.95,
  "reasoning": "Por qué clasificaste esta intención (razonamiento interno)",
  "suggestedResponse": "Tu respuesta natural y conversacional como humano",
  "generatePdf": false,
  "actionDetails": {
    "calendarAction": "create|modify|cancel|list (solo si intent es calendar)",
    "quotationInfo": {
      "projectType": "web|mobile|software|ia|otro",
      "budget": "bajo|medio|alto|enterprise|no_especificado",
      "urgency": "normal|urgente|no_especificado",
      "complexity": "basico|intermedio|complejo|no_especificado"
    },
    "knowledgeQuery": "query específico a buscar en docs (solo si necesitas info específica)"
  },
  "needsMoreInfo": false,
  "clarificationQuestions": ["¿Pregunta inteligente si necesitas más info?"]
}

═══════════════════════════════════════════════════════════════════════════
🚨 REGLAS CRÍTICAS PARA generatePdf (MUY IMPORTANTE)
═══════════════════════════════════════════════════════════════════════════

✅ generatePdf: true SOLO cuando el cliente:
  1. Dice explícitamente "cotización" o "presupuesto"
  2. Pide "enviar cotización", "mandar presupuesto"
  3. Dice "quiero una cotización formal"
  4. Solicita documento oficial de precios
  5. Ya dio detalles específicos del proyecto y pide precio final

❌ generatePdf: false cuando el cliente:
  1. Solo pregunta "¿cuánto cuesta X?"
  2. Pregunta rangos de precios
  3. Compara precios con competencia
  4. Pregunta "¿qué servicios ofrecen?"
  5. Conversación inicial de descubrimiento
  6. Solo pide información general
  7. Pregunta "cómo funciona" o proceso
  8. Aún no tiene claro lo que necesita

IMPORTANTE:
- Siempre analiza el CONTEXTO COMPLETO, no solo el mensaje actual
- Responde como humano natural, nunca robótico
- Si detectas calendar intent, incluye calendarAction en actionDetails
- Si detectas quotation, completa quotationInfo con lo que puedas inferir
- needsMoreInfo: true solo si REALMENTE necesitas más información para responder bien
- generatePdf: SOLO true si el cliente EXPLÍCITAMENTE solicita cotización/presupuesto formal
`;

      const messages: OpenAI.Chat.ChatCompletionMessageParam[] = [
        { role: 'system', content: systemPrompt },
      ];

      // Agregar historial conversacional
      if (context.conversationHistory && context.conversationHistory.length > 0) {
        context.conversationHistory.slice(-5).forEach((msg) => {
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

      logger.info('🧠 [MASTER AI] Analizando mensaje con GPT-4o + Function Calling...');

      const response = await openai.chat.completions.create({
        model: 'gpt-4o',
        messages,
        temperature: 0.5, // Balance entre creatividad y consistencia
        response_format: { type: 'json_object' },
        tools, // ✅ Habilitar Function Calling
        tool_choice: 'auto', // GPT-4o decide cuándo usar las herramientas
      });

      // ─────────────────────────────────────────────────────────────────
      // 🔍 DETECTAR SI GPT-4O QUIERE USAR FUNCTION CALLING
      // ─────────────────────────────────────────────────────────────────
      const message = response.choices[0].message;

      if (message.tool_calls && message.tool_calls.length > 0) {
        logger.info('🔍 [MASTER AI] GPT-4o solicita búsqueda web');

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
        // 🔄 SEGUNDA LLAMADA A GPT-4O CON RESULTADOS DE BÚSQUEDA
        // ─────────────────────────────────────────────────────────────────
        logger.info('🔄 [MASTER AI] Procesando respuesta con resultados de búsqueda...');

        const finalResponse = await openai.chat.completions.create({
          model: 'gpt-4o',
          messages: toolMessages,
          temperature: 0.5,
          response_format: { type: 'json_object' },
        });

        const decision: IntelligentDecision = JSON.parse(
          finalResponse.choices[0].message.content || '{}'
        );

        logger.info('🎯 [MASTER AI] Decisión inteligente (con búsqueda web):', {
          intent: decision.intentType,
          confidence: decision.confidence,
          understanding: decision.understanding,
        });

        return decision;
      }

      const decision: IntelligentDecision = JSON.parse(
        response.choices[0].message.content || '{}'
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
      // 💰 COTIZACIÓN - Generar PDF SOLO si el cliente lo solicita
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'quotation' && decision.generatePdf === true) {
        logger.info('💰 [MASTER AI] Cliente solicita cotización formal - Generando PDF...');

        try {
          // Extraer datos del subscriber para la cotización
          const subscriber = context.subscriber || {};
          const customFields = subscriber.customFields || {};

          const clientName = `${subscriber.firstName || ''} ${subscriber.lastName || ''}`.trim() || 'Cliente';

          // Construir datos para la cotización
          const quotationData = {
            subscriberId: context.subscriberId,
            clientName,
            clientEmail: subscriber.email || context.userEmail,
            clientPhone: subscriber.phone,
            empresa: customFields.empresa,
            tipoProyecto: customFields.tipoProyecto || decision.actionDetails?.quotationInfo?.projectType,
            presupuestoEstimado: customFields.presupuestoEstimado || decision.actionDetails?.quotationInfo?.budget,
            descripcionProyecto: context.userMessage,
            urgencia: customFields.urgencia || decision.actionDetails?.quotationInfo?.urgency,
          };

          logger.info('📄 [QUOTATION] Generando PDF con datos:', quotationData);

          // Generar PDF y enviar
          const quotationResult = await quotationService.generateQuotation(quotationData);

          if (quotationResult.success) {
            logger.info('✅ [QUOTATION] PDF generado exitosamente:', quotationResult.pdfUrl);

            return {
              success: true,
              response: decision.suggestedResponse + `\n\n📄 **Tu cotización personalizada está lista:** ${quotationResult.pdfUrl}`,
              intentType: 'quotation',
              confidence: decision.confidence,
              actionTaken: 'quotation_pdf_generated',
              pdfUrl: quotationResult.pdfUrl,
            };
          } else {
            logger.warn('⚠️ [QUOTATION] Error generando PDF - Enviando respuesta de texto');

            return {
              success: true,
              response: decision.suggestedResponse,
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
            response: decision.suggestedResponse,
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
          response: decision.suggestedResponse,
          intentType: 'quotation',
          confidence: decision.confidence,
          actionTaken: 'quotation_info_only',
        };
      }

      // ──────────────────────────────────────────────────────────────────
      // 📚 CONSULTA TÉCNICA - GPT-4o ya respondió con conocimiento
      // ──────────────────────────────────────────────────────────────────
      if (decision.intentType === 'knowledge') {
        logger.info('📚 [MASTER AI] Respondiendo consulta técnica...');

        // GPT-4o ya tiene contexto de NYNEL MKT en el system prompt
        // La respuesta ya está en decision.suggestedResponse
        return {
          success: true,
          response: decision.suggestedResponse,
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
          response: decision.suggestedResponse,
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
        response: decision.suggestedResponse,
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
