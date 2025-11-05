// ═══════════════════════════════════════════════════════════════════════════
// 🧠 SERVICIO DE IA CONVERSACIONAL INTELIGENTE PARA CALENDARIO
// ═══════════════════════════════════════════════════════════════════════════
// Sistema que entiende contexto completo y toma decisiones autónomas

import OpenAI from 'openai';
import { logger } from '#/utils/logger.js';
import googleCalendarService from './google-calendar.service.js';
import calendarIntentService from './calendar-intent.service.js';

// 🔄 USAR OPENROUTER EN LUGAR DE OPENAI DIRECTO
const openai = new OpenAI({
  apiKey: process.env.OPENROUTER_API_KEY,
  baseURL: process.env.OPENROUTER_BASE_URL || 'https://openrouter.ai/api/v1',
});

// ─────────────────────────────────────────────────────────────────────────────
// Interfaces
// ─────────────────────────────────────────────────────────────────────────────

interface ConversationalContext {
  userMessage: string;
  conversationHistory: Array<{ role: string; content: string }>;
  upcomingEvents?: any[];
  userEmail?: string;
  subscriberId: string;
}

interface IntelligentDecision {
  understanding: string; // Qué entendió del contexto
  userNeed: string; // Qué necesita el usuario
  suggestedAction: 'create' | 'modify' | 'cancel' | 'list' | 'clarify' | 'none';
  confidence: number; // 0.0 - 1.0
  reasoning: string; // Por qué tomó esta decisión
  proactiveResponse: string; // Respuesta natural y contextual
  actionDetails?: {
    eventId?: string;
    eventTitle?: string;
    modifications?: {
      newDate?: string;
      newTime?: string;
      newDuration?: number;
    };
    searchCriteria?: {
      timeRange?: string;
      keywords?: string[];
    };
  };
  needsClarification: boolean;
  clarificationQuestions?: string[];
}

interface ActionResult {
  success: boolean;
  response: string;
  eventCreated?: boolean;
  eventModified?: boolean;
  eventCancelled?: boolean;
  eventDetails?: any;
}

// ─────────────────────────────────────────────────────────────────────────────
// Servicio de IA Conversacional
// ─────────────────────────────────────────────────────────────────────────────

export class ConversationalCalendarAI {
  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔍 PASO 1: CONTEXT ANALYZER - Analizar contexto completo
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async analyzeContext(context: ConversationalContext): Promise<IntelligentDecision> {
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

      // Obtener eventos próximos si no se proporcionaron
      // 🔒 FILTRAR por subscriberId para evitar mostrar citas de otros usuarios
      let upcomingEvents = context.upcomingEvents;
      if (!upcomingEvents) {
        try {
          upcomingEvents = await googleCalendarService.getUpcomingEvents(5, context.subscriberId);
          logger.info(`🔒 Eventos próximos filtrados para subscriber: ${context.subscriberId}`);
        } catch (error) {
          logger.warn('No se pudieron obtener eventos próximos:', error);
          upcomingEvents = [];
        }
      }

      const eventsContext = upcomingEvents && upcomingEvents.length > 0
        ? upcomingEvents.map((e: any, idx: number) => {
            const startDate = new Date(e.start?.dateTime || e.start?.date);
            return `${idx + 1}. "${e.summary}" - ${startDate.toLocaleDateString('es-PE', {
              weekday: 'long',
              day: 'numeric',
              month: 'long',
              hour: '2-digit',
              minute: '2-digit'
            })}`;
          }).join('\n')
        : 'No hay eventos próximos agendados';

      const conversationContext = context.conversationHistory
        .slice(-5)
        .map((msg) => `${msg.role === 'user' ? 'Usuario' : 'Asistente'}: ${msg.content}`)
        .join('\n');

      const systemPrompt = `Eres un asistente de calendario INTELIGENTE y CONVERSACIONAL. Tu objetivo es entender el CONTEXTO COMPLETO de la conversación y EJECUTAR ACCIONES REALES.

═══════════════════════════════════════════════════════════════════════════
📅 CONTEXTO ACTUAL
═══════════════════════════════════════════════════════════════════════════

FECHA Y HORA ACTUAL EN PERÚ: ${peruTime}

EVENTOS PRÓXIMOS DEL USUARIO:
${eventsContext}

CONVERSACIÓN RECIENTE:
${conversationContext || 'Primera interacción'}

═══════════════════════════════════════════════════════════════════════════
🧠 TUS CAPACIDADES Y RESPONSABILIDADES
═══════════════════════════════════════════════════════════════════════════

PUEDES:
1. ✅ Crear citas nuevas
2. ✏️ Modificar citas existentes (fecha, hora, duración)
3. ❌ Cancelar citas
4. 📋 Consultar agenda
5. 🔄 Reprogramar automáticamente por conflictos
6. 💡 Sugerir horarios alternativos proactivamente
7. ❓ Pedir aclaraciones SOLO cuando NO tengas información suficiente

═══════════════════════════════════════════════════════════════════════════
🎯 REGLAS CRÍTICAS PARA EJECUTAR ACCIONES (NO SOLO CONVERSAR)
═══════════════════════════════════════════════════════════════════════════

⚠️ REGLA #1: USA 'clarify' SOLO SI TE FALTA INFORMACIÓN CRÍTICA
   - ❌ MAL: Usuario dice "reprogramar para mañana" → clarify (preguntando hora)
   - ✅ BIEN: Usuario dice "reprogramar para mañana" → modify (con hora por defecto)

   - ❌ MAL: Usuario pregunta por enlace de reunión → clarify
   - ✅ BIEN: Usuario pregunta por enlace de reunión → list o none (ya tienes el enlace)

⚠️ REGLA #2: SI YA DECIDISTE EN CONVERSACIÓN PREVIA, EJECUTA AHORA
   - Si en mensaje previo dijiste "cambiaré tu cita a las 4pm" → ahora EJECUTA modify
   - Si usuario confirma con "sí" o "ok" → EJECUTA la acción previamente propuesta
   - NO vuelvas a preguntar si ya lo confirmaste

⚠️ REGLA #3: ANALIZA EL HISTORIAL DE CONVERSACIÓN
   - Si acabas de decir "he reprogramado tu cita" → la acción YA SE EJECUTÓ
   - Si usuario pregunta algo sobre esa cita → usa list para confirmar o none para responder
   - NO intentes modificar algo que ya modificaste

⚠️ REGLA #4: TOMA DECISIONES AUTÓNOMAS CON INFORMACIÓN PARCIAL
   - Si dice "mover para mañana" SIN especificar hora → usa la misma hora que tiene ahora
   - Si dice "cancelar mi reunión" y hay 1 solo evento → cancela ese
   - Si hay múltiples eventos → ENTONCES pide aclaración

⚠️ REGLA #5: DIFERENCIA ENTRE PREGUNTA DE INFO vs ACCIÓN
   - "¿Cuál es el enlace?" → list (mostrar info del evento)
   - "Cambia la fecha" → modify (ejecutar modificación)
   - "¿Tengo reuniones mañana?" → list (consultar agenda)

═══════════════════════════════════════════════════════════════════════════
📚 EJEMPLOS DE DECISIONES INTELIGENTES
═══════════════════════════════════════════════════════════════════════════

EJEMPLO 1 - Usuario llega tarde:
Usuario: "Llego en 20 minutos más, estoy en tráfico"
Contexto: Tiene cita en 15 minutos
Decisión: modify (reprogramar automáticamente)
Respuesta: "Perfecto, veo que tu cita es a las 3pm. ¿La muevo para las 3:30pm así llegas tranquilo?"

EJEMPLO 2 - Cancelación implícita:
Usuario: "Ya no voy a poder ir"
Contexto: Tiene cita hoy a las 4pm
Decisión: cancel
Respuesta: "Entendido. ¿Cancelo tu cita de hoy a las 4pm entonces?"

EJEMPLO 3 - Conflicto de agenda:
Usuario: "Me salió otra reunión importante"
Contexto: Tiene cita mañana
Decisión: modify (ofrecer reprogramar)
Respuesta: "Entiendo, tienes un conflicto con tu cita de mañana. ¿Prefieres moverla para otro día? ¿Qué día te viene bien?"

EJEMPLO 4 - Pregunta ambigua con contexto:
Usuario: "Se puede cambiar?"
Contexto: Acaban de hablar de cita del martes
Decisión: clarify (pero con contexto)
Respuesta: "Sí, claro. ¿A qué hora quieres que cambie tu cita del martes?"

EJEMPLO 5 - Sin evento próximo:
Usuario: "Cambia mi reunión"
Contexto: No hay eventos próximos
Decisión: clarify
Respuesta: "No veo citas próximas agendadas. ¿De qué reunión hablas? ¿Cuándo era?"

EJEMPLO 6 - Confirmación simple:
Usuario: "Sí"
Contexto: Acabas de preguntar si quiere reprogramar para las 4pm
Decisión: modify (ejecutar cambio confirmado)
Respuesta: "¡Listo! Cambié tu cita para las 4pm de hoy."

EJEMPLO 7 - Pregunta sobre reunión ya reprogramada:
Usuario: "¿El enlace es el mismo?"
Contexto: Acaba de reprogramar reunión en conversación previa
Decisión: list (consultar info del evento)
Respuesta: "Sí, el enlace de Google Meet sigue siendo el mismo: https://meet.google.com/xyz"

EJEMPLO 8 - Usuario pide verificar algo de una cita:
Usuario: "Si por favor, verifica el enlace"
Contexto: Conversación sobre una reunión específica
Decisión: list (NO clarify - ya sabes qué evento es)
Respuesta: "He verificado tu reunión. El enlace es: https://meet.google.com/xyz"

EJEMPLO 9 - Reprogramación con confirmación previa:
Asistente (mensaje anterior): "¿Quieres que cambie tu cita del 29 al 30 de octubre?"
Usuario (ahora): "Sí por favor"
Decisión: modify (EJECUTAR la modificación confirmada)
Respuesta: "¡Perfecto! He reprogramado tu reunión para el 30 de octubre a las 2:30 PM."

EJEMPLO 10 - NO volver a preguntar:
Asistente (hace 2 mensajes): "He reprogramado tu reunión para mañana"
Usuario (ahora): "Gracias, ¿y el enlace?"
Decisión: list o none (NO modify de nuevo - ya se ejecutó)
Respuesta: "El enlace de la reunión es: https://meet.google.com/xyz"

═══════════════════════════════════════════════════════════════════════════
📤 FORMATO DE RESPUESTA JSON
═══════════════════════════════════════════════════════════════════════════

Debes devolver JSON con esta estructura:

{
  "understanding": "Qué entendiste del contexto completo (incluyendo eventos y conversación previa)",
  "userNeed": "Qué necesita realmente el usuario (más allá de lo que dice literalmente)",
  "suggestedAction": "create|modify|cancel|list|clarify|none",
  "confidence": 0.95,
  "reasoning": "Por qué tomaste esta decisión (tu razonamiento interno)",
  "proactiveResponse": "Tu respuesta natural como si fueras un humano real (sin sonar robótico)",
  "actionDetails": {
    "eventId": "ID del evento si es relevante (null si no aplica)",
    "eventTitle": "Título del evento si lo mencionas",
    "modifications": {
      "newDate": "2025-10-29 (formato YYYY-MM-DD, solo si el usuario especifica nueva fecha)",
      "newTime": "16:30 (formato HH:MM, solo si el usuario especifica nueva hora)",
      "newDuration": 60
    },
    "searchCriteria": {
      "timeRange": "hoy|mañana|martes|etc",
      "keywords": ["palabras", "relevantes"]
    }
  },
  "needsClarification": false,
  "clarificationQuestions": ["¿Qué hora te viene bien?"]
}

IMPORTANTE:
- Solo incluye "modifications" si el usuario da información específica
- Si dice "llego en 20 minutos" SIN especificar hora → sugiere una hora TÚ basándote en la cita actual
- Si hay 1 solo evento próximo y habla de "mi cita" → asume que es ese evento
- needsClarification: true solo si REALMENTE no puedes decidir con el contexto disponible
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

      // Agregar mensaje actual del usuario
      messages.push({
        role: 'user',
        content: `Analiza este mensaje del usuario y decide qué hacer:\n\n"${context.userMessage}"`,
      });

      logger.info('🧠 Analizando contexto completo con IA conversacional...');

      const response = await openai.chat.completions.create({
        model: 'deepseek/deepseek-chat',
        messages,
        temperature: 0.4, // Balance entre creatividad y consistencia
        response_format: { type: 'json_object' },
      });

      const decision: IntelligentDecision = JSON.parse(
        response.choices[0].message.content || '{}'
      );

      logger.info('🎯 Decisión inteligente tomada:', {
        action: decision.suggestedAction,
        confidence: decision.confidence,
        understanding: decision.understanding,
        needsClarification: decision.needsClarification,
      });

      return decision;
    } catch (error) {
      logger.error('❌ Error en análisis de contexto:', error);
      return {
        understanding: 'Error al analizar contexto',
        userNeed: 'Desconocido',
        suggestedAction: 'none',
        confidence: 0,
        reasoning: 'Error técnico',
        proactiveResponse: 'Disculpa, tuve un problema procesando tu mensaje. ¿Podrías repetir?',
        needsClarification: true,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * ⚡ PASO 2: ACTION EXECUTOR - Ejecutar acción decidida
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async executeDecision(
    decision: IntelligentDecision,
    context: ConversationalContext
  ): Promise<ActionResult> {
    try {
      logger.info(`⚡ Ejecutando acción: ${decision.suggestedAction}`);

      // Si necesita aclaración, devolver respuesta sin ejecutar acción
      if (decision.needsClarification || decision.suggestedAction === 'clarify') {
        return {
          success: true,
          response: decision.proactiveResponse,
        };
      }

      // Si no hay acción necesaria
      if (decision.suggestedAction === 'none') {
        return {
          success: true,
          response: decision.proactiveResponse,
        };
      }

      // CREAR nueva cita
      if (decision.suggestedAction === 'create') {
        // Usar el servicio existente para crear eventos
        // 🔒 FILTRADO DE PRIVACIDAD: Pasar subscriberId para asociar evento al usuario
        const result = await calendarIntentService.processCalendarIntent(
          context.userMessage,
          context.conversationHistory,
          context.userEmail,
          context.subscriberId // 🔒 Asociar al usuario
        );

        if (result.eventCreated && result.calendarInfo) {
          return {
            success: true,
            response: decision.proactiveResponse,
            eventCreated: true,
            eventDetails: result.calendarInfo,
          };
        }

        // ✅ Si no se creó pero hay suggestedResponse (eventos similares, slots disponibles, etc.)
        if (result.suggestedResponse) {
          logger.info('✅ [CALENDAR AI] Respondiendo con sugerencia del servicio');
          return {
            success: true,
            response: result.suggestedResponse,
            eventCreated: false,
          };
        }

        // ❌ Si falló la creación SIN sugerencia, responder con error honesto
        logger.error('❌ [CALENDAR AI] Fallo al crear evento - Informando al usuario');
        return {
          success: false,
          response: 'Disculpa, tuve un problema técnico al intentar crear el evento en el calendario. ¿Podrías proporcionarme tu email para que pueda enviarte la invitación correctamente?',
        };
      }

      // LISTAR eventos
      if (decision.suggestedAction === 'list') {
        // 🔒 FILTRAR por subscriberId para evitar mostrar citas de otros usuarios
        const events = await googleCalendarService.getUpcomingEvents(10, context.subscriberId);
        logger.info(`🔒 Listando eventos filtrados para subscriber: ${context.subscriberId}`);

        if (events.length === 0) {
          return {
            success: true,
            response: 'No tienes eventos próximos agendados.',
          };
        }

        // Si hay conversación reciente sobre una reunión específica, buscar ese evento
        const searchCriteria = decision.actionDetails?.searchCriteria;
        let targetEvent = null;

        if (searchCriteria) {
          // 🔒 FILTRADO DE PRIVACIDAD: Buscar solo en eventos del usuario
          const foundEvents = await calendarIntentService.searchExistingEvents(
            searchCriteria,
            context.subscriberId // 🔒 Filtrar por usuario
          );
          if (foundEvents.length > 0) {
            targetEvent = foundEvents[0];
          }
        }

        // Si encontramos un evento específico, devolver su info completa
        if (targetEvent) {
          const startDate = new Date(targetEvent.start);
          const meetLink = targetEvent.hangoutLink;

          let response = `📅 *${targetEvent.summary}*\n`;
          response += `📆 ${startDate.toLocaleDateString('es-PE', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            hour: '2-digit',
            minute: '2-digit',
          })}\n`;

          if (meetLink) {
            response += `🔗 Enlace: ${meetLink}`;
          }

          return {
            success: true,
            response: decision.proactiveResponse + (meetLink ? `\n\n${meetLink}` : ''),
            eventDetails: targetEvent,
          };
        }

        // Si no, listar todos los eventos
        const eventsList = events
          .map((e: any, idx: number) => {
            const startDate = new Date(e.start?.dateTime || e.start?.date);
            const meetLink = e.hangoutLink || e.conferenceData?.entryPoints?.[0]?.uri;
            let eventStr = `${idx + 1}. *${e.summary}*\n📅 ${startDate.toLocaleDateString('es-PE', {
              weekday: 'long',
              day: 'numeric',
              month: 'long',
              hour: '2-digit',
              minute: '2-digit',
            })}`;
            if (meetLink) {
              eventStr += `\n🔗 ${meetLink}`;
            }
            return eventStr;
          })
          .join('\n\n');

        return {
          success: true,
          response: `Aquí están tus próximos eventos:\n\n${eventsList}`,
        };
      }

      // MODIFICAR o CANCELAR - necesitamos buscar el evento
      if (decision.suggestedAction === 'modify' || decision.suggestedAction === 'cancel') {
        const searchCriteria = decision.actionDetails?.searchCriteria || {};

        // 🔒 FILTRADO DE PRIVACIDAD: Buscar solo en eventos del usuario
        const foundEvents = await calendarIntentService.searchExistingEvents(
          searchCriteria,
          context.subscriberId // 🔒 Filtrar por usuario
        );

        if (foundEvents.length === 0) {
          return {
            success: false,
            response: 'No encontré la cita que mencionas. ¿Podrías darme más detalles? Por ejemplo, ¿cuándo era?',
          };
        }

        // Si hay 1 solo evento, usarlo directamente
        const targetEvent = foundEvents[0];

        if (decision.suggestedAction === 'modify') {
          // Modificar evento
          const modifications = decision.actionDetails?.modifications || {};

          const modifyResult = await calendarIntentService.modifyCalendarEvent(
            targetEvent.id,
            modifications,
            context.userEmail
          );

          if (modifyResult.success) {
            return {
              success: true,
              response: decision.proactiveResponse,
              eventModified: true,
              eventDetails: modifyResult,
            };
          }

          return {
            success: false,
            response: 'Tuve un problema al modificar la cita. ¿Intentamos de nuevo?',
          };
        }

        if (decision.suggestedAction === 'cancel') {
          // Cancelar evento
          const cancelResult = await calendarIntentService.cancelCalendarEvent(targetEvent.id);

          if (cancelResult.success) {
            return {
              success: true,
              response: decision.proactiveResponse,
              eventCancelled: true,
            };
          }

          return {
            success: false,
            response: 'Tuve un problema al cancelar la cita. ¿Intentamos de nuevo?',
          };
        }
      }

      return {
        success: false,
        response: 'No pude procesar tu solicitud. ¿Podrías reformularla?',
      };
    } catch (error) {
      logger.error('❌ Error al ejecutar decisión:', error);
      return {
        success: false,
        response: 'Tuve un problema procesando tu solicitud. ¿Podrías intentar de nuevo?',
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🚀 MÉTODO PRINCIPAL - Procesar mensaje completo
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async processConversationalMessage(
    context: ConversationalContext
  ): Promise<{
    decision: IntelligentDecision;
    result: ActionResult;
  }> {
    logger.info('🧠 Iniciando procesamiento conversacional inteligente');

    // Paso 1: Analizar contexto y tomar decisión
    const decision = await this.analyzeContext(context);

    // Paso 2: Ejecutar decisión
    const result = await this.executeDecision(decision, context);

    return { decision, result };
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Singleton Instance
// ─────────────────────────────────────────────────────────────────────────────

export const conversationalCalendarAI = new ConversationalCalendarAI();
