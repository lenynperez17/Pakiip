// ═══════════════════════════════════════════════════════════════════════════
// 📅 SERVICIO DE DETECCIÓN Y GESTIÓN COMPLETA DE CITAS DE CALENDARIO
// ═══════════════════════════════════════════════════════════════════════════
// Detecta intenciones de calendario y ejecuta acciones: CREAR, MODIFICAR, CANCELAR, LISTAR

import OpenAI from 'openai';
import { logger } from '../utils/logger.js';
import googleCalendarService from './google-calendar.service.js';
import { prisma } from '../config/database.js';
import { emailNotificationService } from './email-notification.service.js';

// 🔄 USAR OPENROUTER EN LUGAR DE OPENAI DIRECTO
const openai = new OpenAI({
  apiKey: process.env.OPENROUTER_API_KEY,
  baseURL: process.env.OPENROUTER_BASE_URL || 'https://openrouter.ai/api/v1',
});

// ═══════════════════════════════════════════════════════════════════════════
// 📋 INTERFACES Y TIPOS
// ═══════════════════════════════════════════════════════════════════════════

type CalendarActionType = 'CREATE' | 'MODIFY' | 'CANCEL' | 'LIST' | 'NONE';

interface CalendarIntentResult {
  hasCalendarIntent: boolean;
  eventDetails?: {
    summary: string;
    description: string;
    date: string; // ISO format
    time: string; // HH:mm format
    duration?: number; // minutos
    needsMeet: boolean;
  };
  extractedInfo?: string;
}

interface CalendarEventCreated {
  success: boolean;
  eventUrl?: string;
  meetUrl?: string;
  summary?: string;
  dateTime?: string;
  error?: string;
}

interface EventSearchCriteria {
  dateStart?: string; // ISO format
  dateEnd?: string;
  keywords?: string[];
  timeRange?: string; // 'hoy', 'mañana', 'esta_semana', etc.
}

interface CalendarActionResult {
  actionType: CalendarActionType;
  searchCriteria?: EventSearchCriteria;
  modifications?: {
    newDate?: string;
    newTime?: string;
    newDuration?: number;
    newSummary?: string;
  };
  extractedInfo?: string;
}

interface EventMatch {
  id: string;
  summary: string;
  start: string;
  end: string;
  attendees?: any[];
  hangoutLink?: string;
}

// ═══════════════════════════════════════════════════════════════════════════
// 🔢 FUNCIÓN AUXILIAR: GENERAR CÓDIGO ÚNICO DE REUNIÓN
// ═══════════════════════════════════════════════════════════════════════════
function generateMeetingCode(): string {
  const date = new Date();
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const random = Math.floor(Math.random() * 1000)
    .toString()
    .padStart(3, '0');

  return `REU-${year}${month}-${random}`;
}

class CalendarIntentService {
  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔍 PASO 1: DETECTAR TIPO DE ACCIÓN (CREATE / MODIFY / CANCEL / LIST)
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async detectCalendarAction(
    userMessage: string,
    conversationHistory?: Array<{ role: string; content: string }>
  ): Promise<CalendarActionResult> {
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

      const systemPrompt = `Detecta la intención de calendario del usuario.

FECHA Y HORA ACTUAL EN PERÚ: ${peruTime}

REGLAS DE DETECCIÓN (priorizar en este orden):

1. CANCEL si menciona:
   - "cancelar", "eliminar", "quitar", "borrar", "anular"
   - "ya no voy", "ya no puedo", "no podré ir"
   - Habla de UNA CITA EXISTENTE que quiere eliminar

2. MODIFY si menciona:
   - "cambiar", "mover", "reprogramar", "modificar", "pasar"
   - "cambiar la hora", "mover para", "mejor a las"
   - Habla de UNA CITA EXISTENTE que quiere cambiar

3. LIST si pregunta por:
   - "qué citas", "cuáles son mis", "agenda", "calendario"
   - "tengo reuniones", "qué tengo"

4. CREATE solo si quiere agendar NUEVA cita:
   - "agendar", "programar", "apartar", "reservar"
   - "quiero una cita", "reunión para"

5. NONE para todo lo demás

EJEMPLOS CLAROS:
- "elimina mi reunión" → CANCEL
- "cancela la del martes" → CANCEL
- "cambia mi cita a las 5" → MODIFY
- "mueve la reunión al viernes" → MODIFY
- "qué tengo hoy" → LIST
- "agendar para mañana" → CREATE

Para MODIFY/CANCEL extrae:
- timeRange: "hoy"/"mañana"/"martes"/etc
- keywords: palabras del evento si menciona

Para MODIFY también extrae:
- newDate y newTime si los menciona

JSON de respuesta:
{
  "actionType": "CANCEL|MODIFY|LIST|CREATE|NONE",
  "searchCriteria": {"timeRange": "mañana", "keywords": []},
  "modifications": {"newDate": "", "newTime": ""}
}`;

      const messages: OpenAI.Chat.ChatCompletionMessageParam[] = [
        { role: 'system', content: systemPrompt },
      ];

      if (conversationHistory && conversationHistory.length > 0) {
        conversationHistory.slice(-5).forEach((msg) => {
          messages.push({
            role: msg.role as 'user' | 'assistant',
            content: msg.content,
          });
        });
      }

      messages.push({
        role: 'user',
        content: `Analiza este mensaje:\n\n"${userMessage}"`,
      });

      const response = await openai.chat.completions.create({
        model: 'deepseek/deepseek-chat',
        messages,
        temperature: 0.3,
        response_format: { type: 'json_object' },
      });

      const result = JSON.parse(response.choices[0].message.content || '{}');

      logger.info('🔍 Detección de acción de calendario:', result);

      return result;
    } catch (error) {
      logger.error('❌ Error al detectar acción de calendario:', error);
      return { actionType: 'NONE' };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔎 PASO 2: BUSCAR EVENTOS EXISTENTES POR CRITERIOS
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔒 FILTRADO DE PRIVACIDAD: Recibe subscriberId para filtrar solo eventos del usuario
   */
  async searchExistingEvents(
    criteria: EventSearchCriteria,
    subscriberId?: string // 🔒 ID del suscriptor para filtrado
  ): Promise<EventMatch[]> {
    try {
      const timeMin = criteria.dateStart
        ? new Date(criteria.dateStart).toISOString()
        : new Date().toISOString();

      const timeMax = criteria.dateEnd
        ? new Date(criteria.dateEnd + 'T23:59:59').toISOString()
        : new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(); // +30 días

      // 🔒 Pasar subscriberId para filtrar eventos
      const events = await googleCalendarService.listEvents({
        timeMin,
        timeMax,
        maxResults: 50,
        subscriberId, // 🔒 Filtrar por usuario
      });

      logger.info(`📊 Eventos encontrados en rango: ${events.length}`);

      // Filtrar por palabras clave si existen
      let filteredEvents = events;
      if (criteria.keywords && criteria.keywords.length > 0) {
        filteredEvents = events.filter((event: any) => {
          const summary = (event.summary || '').toLowerCase();
          const description = (event.description || '').toLowerCase();

          return criteria.keywords!.some(
            (keyword) =>
              summary.includes(keyword.toLowerCase()) ||
              description.includes(keyword.toLowerCase())
          );
        });
      }

      logger.info(`🔍 Eventos después de filtrar: ${filteredEvents.length}`);

      return filteredEvents.map((event: any) => ({
        id: event.id,
        summary: event.summary,
        start: event.start.dateTime || event.start.date,
        end: event.end.dateTime || event.end.date,
        attendees: event.attendees,
        hangoutLink: event.hangoutLink,
      }));
    } catch (error) {
      logger.error('❌ Error al buscar eventos:', error);
      return [];
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * ✏️ MODIFICAR EVENTO EXISTENTE
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async modifyCalendarEvent(
    eventId: string,
    modifications: CalendarActionResult['modifications'],
    contactEmail?: string
  ): Promise<CalendarEventCreated> {
    try {
      if (!modifications) {
        return {
          success: false,
          error: 'No se proporcionaron modificaciones',
        };
      }

      // Obtener evento actual
      const currentEvent = await googleCalendarService.getEvent(eventId);

      // Construir datos actualizados
      const updatedData: any = {};

      if (modifications.newSummary) {
        updatedData.summary = modifications.newSummary;
      }

      if (modifications.newDate || modifications.newTime) {
        // Obtener fecha/hora actual del evento
        const currentStartISO = currentEvent.start.dateTime || currentEvent.start.date;

        logger.info('📅 Evento original ISO:', currentStartISO);

        // Parsear fecha usando Date, que manejará automáticamente UTC/timezone
        const currentStartDate = new Date(currentStartISO);

        // Convertir a timezone Lima y extraer componentes
        const limaFormatter = new Intl.DateTimeFormat('en-US', {
          timeZone: 'America/Lima',
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
          hour12: false,
        });

        const limaParts = limaFormatter.formatToParts(currentStartDate);
        const limaData: any = {};
        limaParts.forEach(part => {
          if (part.type !== 'literal') {
            limaData[part.type] = part.value;
          }
        });

        let year = parseInt(limaData.year);
        let month = parseInt(limaData.month);
        let day = parseInt(limaData.day);
        let hour = parseInt(limaData.hour);
        let minute = parseInt(limaData.minute);

        logger.info('📅 Hora original del evento en Lima:', {
          year, month, day, hour, minute,
          formatted: `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')} ${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
        });

        // Aplicar nueva fecha si se proporciona
        if (modifications.newDate) {
          const newDateMatch = modifications.newDate.match(/^(\d{4})-(\d{2})-(\d{2})/);
          if (newDateMatch) {
            year = parseInt(newDateMatch[1]);
            month = parseInt(newDateMatch[2]);
            day = parseInt(newDateMatch[3]);
          }
        }

        // Aplicar nueva hora si se proporciona
        if (modifications.newTime) {
          const [h, m] = modifications.newTime.split(':');
          hour = parseInt(h);
          minute = parseInt(m);
        }

        // Construir string ISO directamente en formato America/Lima (UTC-5)
        const startDateTimeISO = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}T${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}:00-05:00`;

        logger.info('📅 Nueva fecha/hora construida:', {
          startDateTimeISO,
          year, month, day, hour, minute,
          readable: `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')} ${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
        });

        // Calcular fecha de fin (sumando duración)
        const durationMinutes = modifications.newDuration || 60;
        const startTimeMinutes = hour * 60 + minute;
        const endTimeMinutes = startTimeMinutes + durationMinutes;
        const endHour = Math.floor(endTimeMinutes / 60);
        const endMinute = endTimeMinutes % 60;

        const endDateTimeISO = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}T${String(endHour).padStart(2, '0')}:${String(endMinute).padStart(2, '0')}:00-05:00`;

        updatedData.start = {
          dateTime: startDateTimeISO,
          timeZone: 'America/Lima',
        };
        updatedData.end = {
          dateTime: endDateTimeISO,
          timeZone: 'America/Lima',
        };

        logger.info('✅ Datos de fecha/hora actualizados:', updatedData.start, updatedData.end);
      }

      // Asegurar que siempre envíe notificaciones
      updatedData.sendUpdates = 'all';

      const updatedEvent = await googleCalendarService.updateEvent(eventId, updatedData);

      logger.info('✅ Evento modificado exitosamente:', {
        id: updatedEvent.id,
        summary: updatedEvent.summary,
      });

      // ═══════════════════════════════════════════════════════════════
      // 💾 ACTUALIZAR REUNIÓN EN BASE DE DATOS
      // ═══════════════════════════════════════════════════════════════
      try {
        const dbUpdateData: any = {
          status: 'RESCHEDULED',
          updatedAt: new Date()
        };

        if (modifications.newSummary) {
          dbUpdateData.topic = modifications.newSummary;
        }

        if (modifications.newDate || modifications.newTime) {
          dbUpdateData.scheduledAt = new Date(updatedData.start.dateTime);
        }

        await prisma.meeting.updateMany({
          where: { calendarEventId: eventId },
          data: dbUpdateData
        });

        logger.info(`✅ Reunión con eventId ${eventId} actualizada en BD`);
      } catch (dbError: any) {
        logger.error('❌ Error actualizando reunión en BD:', dbError);
        // No lanzar error - evento ya está actualizado en Google Calendar
      }

      return {
        success: true,
        eventUrl: updatedEvent.htmlLink,
        meetUrl: updatedEvent.hangoutLink,
        summary: updatedEvent.summary,
        dateTime: updatedEvent.start.dateTime,
      };
    } catch (error: any) {
      logger.error('❌ Error al modificar evento:', error);
      return {
        success: false,
        error: error.message,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * ❌ CANCELAR EVENTO EXISTENTE
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async cancelCalendarEvent(eventId: string): Promise<{
    success: boolean;
    message: string;
  }> {
    try {
      await googleCalendarService.deleteEvent(eventId);

      logger.info('✅ Evento cancelado exitosamente:', eventId);

      // ═══════════════════════════════════════════════════════════════
      // 💾 ACTUALIZAR REUNIÓN COMO CANCELADA EN BASE DE DATOS
      // ═══════════════════════════════════════════════════════════════
      try {
        await prisma.meeting.updateMany({
          where: { calendarEventId: eventId },
          data: {
            status: 'CANCELLED',
            cancelledAt: new Date(),
            cancellationReason: 'Cancelada por solicitud del cliente',
            updatedAt: new Date()
          }
        });

        logger.info(`✅ Reunión con eventId ${eventId} marcada como CANCELADA en BD`);
      } catch (dbError: any) {
        logger.error('❌ Error actualizando estado de reunión en BD:', dbError);
        // No lanzar error - evento ya está eliminado de Google Calendar
      }

      return {
        success: true,
        message: 'Cita cancelada correctamente',
      };
    } catch (error: any) {
      logger.error('❌ Error al cancelar evento:', error);
      return {
        success: false,
        message: error.message,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 📋 LISTAR AGENDA DEL USUARIO
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔒 FILTRADO DE PRIVACIDAD: Recibe subscriberId para filtrar solo eventos del usuario
   */
  async listUserAgenda(
    timeRange: 'hoy' | 'mañana' | 'semana' | 'mes' = 'semana',
    subscriberId?: string // 🔒 ID del suscriptor para filtrado
  ): Promise<string> {
    try {
      let events: any[] = [];

      if (timeRange === 'hoy') {
        // 🔒 Filtrar por subscriberId
        events = await googleCalendarService.getTodayEvents(subscriberId);
      } else {
        const days = timeRange === 'mañana' ? 1 : timeRange === 'semana' ? 7 : 30;
        // 🔒 Filtrar por subscriberId
        events = await googleCalendarService.getUpcomingEvents(days, subscriberId);
      }

      if (events.length === 0) {
        return `No tienes citas programadas para ${timeRange}.`;
      }

      let agendaText = `📅 *Tu agenda para ${timeRange}:*\n\n`;

      events.forEach((event, index) => {
        const start = new Date(event.start.dateTime || event.start.date);
        const formattedDate = start.toLocaleDateString('es-PE', {
          timeZone: 'America/Lima',
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
        });
        const formattedTime = event.start.dateTime
          ? start.toLocaleTimeString('es-PE', {
              timeZone: 'America/Lima',
              hour: '2-digit',
              minute: '2-digit',
            })
          : 'Todo el día';

        agendaText += `${index + 1}. *${event.summary}*\n`;
        agendaText += `   📆 ${formattedDate}\n`;
        agendaText += `   🕒 ${formattedTime}\n`;

        if (event.hangoutLink) {
          agendaText += `   📹 ${event.hangoutLink}\n`;
        }

        agendaText += '\n';
      });

      return agendaText;
    } catch (error) {
      logger.error('❌ Error al listar agenda:', error);
      return 'Hubo un error al obtener tu agenda.';
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔍 BUSCAR EVENTOS SIMILARES (PREVENCIÓN DE DUPLICADOS)
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔒 FILTRADO DE PRIVACIDAD: Recibe subscriberId para filtrar solo eventos del usuario
   */
  async findSimilarEvents(
    summary: string,
    date: string,
    timeDelta: number = 2, // Buscar ±2 días
    subscriberId?: string // 🔒 ID del suscriptor para filtrado
  ): Promise<EventMatch[]> {
    try {
      const targetDate = new Date(date);
      const startDate = new Date(targetDate);
      startDate.setDate(startDate.getDate() - timeDelta);

      const endDate = new Date(targetDate);
      endDate.setDate(endDate.getDate() + timeDelta);

      // 🔒 Pasar subscriberId para filtrar eventos
      const events = await googleCalendarService.listEvents({
        timeMin: startDate.toISOString(),
        timeMax: endDate.toISOString(),
        maxResults: 50,
        subscriberId, // 🔒 Filtrar por usuario
      });

      // Filtrar por similitud de summary
      const summaryLower = summary.toLowerCase();
      const keywords = summaryLower.split(' ').filter(w => w.length > 3);

      const similarEvents = events.filter((event: any) => {
        const eventSummary = (event.summary || '').toLowerCase();
        return keywords.some(keyword => eventSummary.includes(keyword));
      });

      return similarEvents.map((event: any) => ({
        id: event.id,
        summary: event.summary,
        start: event.start.dateTime || event.start.date,
        end: event.end.dateTime || event.end.date,
        attendees: event.attendees,
        hangoutLink: event.hangoutLink,
      }));
    } catch (error) {
      logger.error('❌ Error al buscar eventos similares:', error);
      return [];
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 📅 DETECTAR INTENCIÓN DE CREAR CITA (MÉTODO ORIGINAL MANTENIDO)
   * ═══════════════════════════════════════════════════════════════════════════
   */
  async detectCalendarIntent(
    userMessage: string,
    conversationHistory?: Array<{ role: string; content: string }>
  ): Promise<CalendarIntentResult> {
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

      const systemPrompt = `Eres un asistente experto en detectar intenciones de agendar citas o reuniones.

FECHA Y HORA ACTUAL EN PERÚ (America/Lima):
${peruTime}
${now.toLocaleString('es-PE', { timeZone: 'America/Lima' })}

Tu trabajo es analizar el mensaje del usuario Y EL CONTEXTO CONVERSACIONAL para determinar si quiere agendar una cita, reunión, llamada o evento.

REGLAS DE DETECCIÓN (PRIORIDAD EN ORDEN):

1. **CONTINUACIÓN DE AGENDAMIENTO EXISTENTE** (MUY IMPORTANTE):
   - Si en mensajes PREVIOS el asistente ya mencionó hora/fecha de cita
   - Y está pidiendo confirmación, email, o datos adicionales
   - Y el usuario responde con email, "sí", "claro", "ok", "perfecto" o información solicitada
   - ENTONCES → hasCalendarIntent: TRUE
   - Extrae fecha/hora de los mensajes PREVIOS del asistente

2. **NUEVA SOLICITUD DE AGENDAMIENTO**:
   PALABRAS CLAVE:
   - agendar, agenda, agendame, programar, reservar, apartar
   - cita, reunión, llamada, meet, meeting, junta, encuentro
   - mañana, hoy, tarde, semana, mes, día, hora
   - "para el...", "el día...", "a las..."

EXTRACCIÓN DE DATOS:
1. Título/asunto (default: "Reunión con NYNEL MKT")
2. Fecha (convierte expresiones relativas a formato ISO YYYY-MM-DD)
3. Hora (formato 24h: HH:mm)
4. Duración en minutos (default: 60)
5. needsMeet: true si menciona "meet"/"videollamada"/"virtual"
6. **DESCRIPCIÓN DETALLADA** (MUY IMPORTANTE):
   Genera una descripción profesional y completa incluyendo TODO lo relevante del contexto:

   📋 **INFORMACIÓN A INCLUIR EN description:**
   - **Motivo principal** de la reunión (consulta, cotización, proyecto específico)
   - **Servicios de interés** mencionados (desarrollo web, marketing digital, SEO, etc.)
   - **Presupuesto aproximado** si fue mencionado (ej: "S/3,000 - S/5,000")
   - **Requerimientos técnicos** específicos (lenguajes, plataformas, características)
   - **Objetivos del cliente** (aumentar ventas, mejorar presencia, automatizar, etc.)
   - **Información del prospecto** (nombre, empresa, industria si fue mencionado)
   - **Urgencia o timeline** (fecha de inicio deseada, plazos importantes)
   - **Contexto adicional** relevante de la conversación

   📝 **FORMATO DE LA DESCRIPCIÓN:**

   📌 MOTIVO: [Breve descripción del motivo]

   💼 SERVICIOS DE INTERÉS:
   • [Servicio 1]
   • [Servicio 2]

   💰 PRESUPUESTO ESTIMADO: [S/X - S/Y] o [Por definir]

   🎯 OBJETIVOS:
   • [Objetivo 1]
   • [Objetivo 2]

   🔧 REQUERIMIENTOS TÉCNICOS:
   • [Requerimiento 1]
   • [Requerimiento 2]

   📞 INFORMACIÓN DEL CLIENTE:
   • Nombre: [Nombre si se conoce]
   • Empresa: [Empresa si se conoce]
   • Email: [Email si fue proporcionado]

   ⏰ URGENCIA: [Alta/Media/Baja] - [Detalles de timeline]

   📝 NOTAS ADICIONALES:
   [Cualquier información relevante adicional del contexto]

   ⚠️ INSTRUCCIONES CRÍTICAS PARA description:
   - Si NO hay información para una sección, omítela (no pongas "No especificado")
   - Extrae información de TODO el historial de conversación
   - Sé específico y profesional
   - Incluye números, montos y detalles exactos cuando estén disponibles
   - Si el cliente mencionó problemas/necesidades, inclúyelos

CONVERSIÓN DE FECHAS:
- "hoy" → fecha actual
- "mañana" → fecha actual + 1 día
- "pasado mañana" → fecha actual + 2 días
- "lunes", "martes", etc. → próximo día de esa semana

IMPORTANTE PARA CONTINUACIÓN:
- Busca en mensajes previos frases como "Te confirmo la cita para...", "Te agendo para...", "¿Tu email es...?"
- Si encuentras hora/fecha en mensajes previos del asistente, úsalas
- Si el mensaje actual es solo un email o confirmación, aún así detecta intención si hay contexto previo

Responde en JSON:
{
  "hasCalendarIntent": true/false,
  "eventDetails": {
    "summary": "Título de la reunión",
    "description": "Descripción DETALLADA siguiendo el formato especificado arriba",
    "date": "2025-01-28",
    "time": "15:00",
    "duration": 60,
    "needsMeet": true/false
  },
  "extractedInfo": "Resumen de lo que entendiste (menciona si es continuación)"
}`;

      const messages: OpenAI.Chat.ChatCompletionMessageParam[] = [
        { role: 'system', content: systemPrompt },
      ];

      if (conversationHistory && conversationHistory.length > 0) {
        conversationHistory.slice(-5).forEach((msg) => {
          messages.push({
            role: msg.role as 'user' | 'assistant',
            content: msg.content,
          });
        });
      }

      messages.push({
        role: 'user',
        content: `Analiza este mensaje y determina si quiere agendar algo:\n\n"${userMessage}"`,
      });

      const response = await openai.chat.completions.create({
        model: 'deepseek/deepseek-chat',
        messages,
        temperature: 0.3,
        response_format: { type: 'json_object' },
      });

      const result = JSON.parse(response.choices[0].message.content || '{}');

      logger.info('📅 Detección de intención de calendario:', result);

      return result;
    } catch (error) {
      logger.error('❌ Error al detectar intención de calendario:', error);
      return { hasCalendarIntent: false };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * ➕ CREAR EVENTO DE CALENDARIO
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔒 FILTRADO DE PRIVACIDAD: Recibe subscriberId para asociar evento al usuario
   */
  async createCalendarEvent(
    eventDetails: CalendarIntentResult['eventDetails'],
    contactEmail?: string,
    subscriberId?: string // 🔒 ID del suscriptor para filtrado
  ): Promise<CalendarEventCreated> {
    try {
      if (!eventDetails) {
        return {
          success: false,
          error: 'No se proporcionaron detalles del evento',
        };
      }

      // Construir fecha y hora en formato ISO
      const startDateTime = `${eventDetails.date}T${eventDetails.time}:00-05:00`; // Lima UTC-5
      const durationMinutes = eventDetails.duration || 60;

      // Calcular fecha de fin
      const startDate = new Date(startDateTime);
      const endDate = new Date(startDate.getTime() + durationMinutes * 60000);
      const endDateTime = endDate.toISOString().slice(0, 19) + '-05:00';

      // ═══════════════════════════════════════════════════════════════
      // 📧 SIEMPRE INCLUIR CORREO EMPRESARIAL + CONTACTO DEL CLIENTE
      // ═══════════════════════════════════════════════════════════════
      const attendees = [
        { email: 'empresarial@nynelmkt.com', responseStatus: 'accepted' }, // SIEMPRE incluir
      ];

      // Agregar email del contacto si existe, es válido y es diferente
      // ⚠️ Validar que NO sea un placeholder de ManyChat ni email inválido
      const isValidEmail = (email: string) => {
        if (!email || email.includes('{{') || email.includes('}}')) return false;
        if (!email.includes('@') || !email.includes('.')) return false;
        return true;
      };

      if (
        contactEmail &&
        contactEmail !== 'empresarial@nynelmkt.com' &&
        isValidEmail(contactEmail)
      ) {
        attendees.push({ email: contactEmail, responseStatus: 'needsAction' });
      }

      const eventData = {
        summary: eventDetails.summary,
        description: eventDetails.description,
        start: {
          dateTime: startDateTime,
          timeZone: 'America/Lima',
        },
        end: {
          dateTime: endDateTime,
          timeZone: 'America/Lima',
        },
        attendees: attendees,
        reminders: {
          useDefault: false,
          overrides: [
            { method: 'email', minutes: 24 * 60 }, // 1 día antes
            { method: 'popup', minutes: 30 }, // 30 minutos antes
          ],
        },
        // Asegurar que se envíen notificaciones por email
        sendUpdates: 'all',
        // 🔒 FILTRADO DE PRIVACIDAD: Asociar evento al usuario
        subscriberId, // 🔒 ID del suscriptor
      };

      // ═══════════════════════════════════════════════════════════════
      // 📹 SIEMPRE CREAR CON GOOGLE MEET (FORZADO)
      // ═══════════════════════════════════════════════════════════════
      logger.info(`📹 Creando evento con Google Meet (forzado)${subscriberId ? ` para subscriber: ${subscriberId}` : ''}...`);
      const createdEvent = await googleCalendarService.createEventWithMeet(eventData);

      logger.info('✅ Evento creado exitosamente:', {
        id: createdEvent.id,
        htmlLink: createdEvent.htmlLink,
        hangoutLink: createdEvent.hangoutLink,
      });

      // ═══════════════════════════════════════════════════════════════
      // 💾 GUARDAR REUNIÓN EN BASE DE DATOS
      // ═══════════════════════════════════════════════════════════════
      try {
        const meetingCode = generateMeetingCode();

        // Buscar o crear subscriber
        const subscriber = await prisma.subscriber.upsert({
          where: { subscriberId: eventDetails.description || 'UNKNOWN' },
          update: { lastActiveAt: new Date() },
          create: {
            subscriberId: eventDetails.description || `SUBSCRIBER_${Date.now()}`,
            platform: 'WHATSAPP',
            firstName: eventDetails.summary.split(' ')[0] || 'Cliente',
            lastName: eventDetails.summary.split(' ').slice(1).join(' ') || '',
            email: contactEmail || null,
            leadStatus: 'CONTACTED',
            priority: 'NORMAL'
          }
        });

        await prisma.meeting.create({
          data: {
            meetingCode,
            subscriberId: subscriber.id,
            type: 'CONSULTATION', // Por defecto consulta inicial
            topic: eventDetails.summary,
            description: eventDetails.description || 'Reunión agendada por el bot',
            scheduledAt: new Date(startDateTime),
            duration: durationMinutes,
            timezone: 'America/Lima',
            meetUrl: createdEvent.hangoutLink || null,
            calendarEventId: createdEvent.id,
            status: 'SCHEDULED',
            reminderSent: false,
            agenda: `Reunión programada para: ${eventDetails.summary}`
          }
        });

        logger.info(`✅ Reunión ${meetingCode} guardada en base de datos`);

        // ═══════════════════════════════════════════════════════════════
        // 📧 ENVIAR NOTIFICACIÓN AL EQUIPO POR EMAIL
        // ═══════════════════════════════════════════════════════════════
        await emailNotificationService.notifyNewMeeting({
          meetingCode,
          clientName: `${subscriber.firstName} ${subscriber.lastName}`.trim(),
          clientPhone: subscriber.phone || 'No proporcionado',
          topic: eventDetails.summary,
          scheduledAt: new Date(startDateTime),
          duration: durationMinutes,
          meetUrl: createdEvent.hangoutLink || '',
          description: eventDetails.description,
        });

      } catch (dbError: any) {
        logger.error('❌ Error guardando reunión en BD:', dbError);
        // No lanzar error - evento ya está en Google Calendar
      }

      return {
        success: true,
        eventUrl: createdEvent.htmlLink,
        meetUrl: createdEvent.hangoutLink,
        summary: createdEvent.summary,
        dateTime: startDateTime,
      };
    } catch (error: any) {
      logger.error('❌ Error al crear evento de calendario:', error);
      return {
        success: false,
        error: error.message,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🎯 PROCESO COMPLETO: DETECTAR INTENCIÓN Y CREAR EVENTO SI ES NECESARIO
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔒 FILTRADO DE PRIVACIDAD: Recibe subscriberId para filtrar eventos por usuario
   */
  async processCalendarIntent(
    userMessage: string,
    conversationHistory?: Array<{ role: string; content: string }>,
    contactEmail?: string,
    subscriberId?: string // 🔒 ID del suscriptor para filtrado
  ): Promise<{
    hasIntent: boolean;
    eventCreated: boolean;
    calendarInfo?: CalendarEventCreated;
    suggestedResponse?: string;
    similarEvents?: EventMatch[];
  }> {
    try {
      // 1. Detectar intención
      const intentResult = await this.detectCalendarIntent(userMessage, conversationHistory);

      if (!intentResult.hasCalendarIntent) {
        return {
          hasIntent: false,
          eventCreated: false,
        };
      }

      // 2. 🧠 AGENDAMIENTO INTELIGENTE CON DISPONIBILIDAD REAL
      // Si no tenemos fecha ni hora, consultar próximos slots disponibles
      if (
        !intentResult.eventDetails?.date ||
        !intentResult.eventDetails?.time
      ) {
        logger.info('🔍 No hay fecha/hora específica, consultando slots disponibles...');

        try {
          // Obtener próximos 5 slots disponibles (priorizando hoy/mañana)
          // 🔒 Pasar subscriberId para filtrar eventos al calcular disponibilidad
          const availableSlots = await googleCalendarService.getNextAvailableSlots(
            5, // cantidad de slots
            { start: '09:00', end: '18:00' }, // horario laboral
            60, // duración 60min
            7, // buscar en los próximos 7 días
            subscriberId // 🔒 Filtrar por usuario
          );

          if (availableSlots.length === 0) {
            return {
              hasIntent: true,
              eventCreated: false,
              suggestedResponse: `Entiendo que quieres agendar una reunión. ${intentResult.extractedInfo || ''}\n\nActualmente no tengo disponibilidad en los próximos 7 días. ¿Podrías indicarme una fecha específica que te funcione?`,
            };
          }

          // Formatear slots de manera amigable agrupados por día
          let suggestionText = `Perfecto! Tengo disponibilidad:\n\n`;

          // Agrupar slots por día para mejor presentación
          const slotsByDay: { [key: string]: typeof availableSlots } = {};
          availableSlots.forEach(slot => {
            const slotDate = new Date(slot.startTime);
            const dayKey = slotDate.toLocaleDateString('es-PE', {
              timeZone: 'America/Lima',
              weekday: 'long',
              day: 'numeric',
              month: 'long'
            });

            if (!slotsByDay[dayKey]) {
              slotsByDay[dayKey] = [];
            }
            slotsByDay[dayKey].push(slot);
          });

          // Construir mensaje con slots agrupados
          Object.entries(slotsByDay).forEach(([day, slots]) => {
            const dayLabel = day.charAt(0).toUpperCase() + day.slice(1);
            suggestionText += `📅 *${dayLabel}*\n`;

            slots.forEach(slot => {
              suggestionText += `   • ${slot.displayTime}\n`;
            });
            suggestionText += `\n`;
          });

          suggestionText += `¿Cuál horario te viene mejor? 😊`;

          return {
            hasIntent: true,
            eventCreated: false,
            suggestedResponse: suggestionText,
          };
        } catch (error) {
          logger.error('❌ Error al consultar slots disponibles:', error);
          // Fallback a pregunta simple
          return {
            hasIntent: true,
            eventCreated: false,
            suggestedResponse: `Entiendo que quieres agendar una reunión. ${intentResult.extractedInfo || ''}\n\n¿Qué fecha y hora te funciona mejor?`,
          };
        }
      }

      // 3. 🔍 BUSCAR EVENTOS SIMILARES PARA PREVENIR DUPLICADOS
      // 🔒 Pasar subscriberId para buscar solo en eventos del usuario
      const similarEvents = await this.findSimilarEvents(
        intentResult.eventDetails.summary,
        intentResult.eventDetails.date,
        2, // timeDelta
        subscriberId // 🔒 Filtrar por usuario
      );

      if (similarEvents.length > 0) {
        logger.info(`⚠️ Encontrados ${similarEvents.length} eventos similares`);

        let suggestionText = `Encontré ${similarEvents.length} cita(s) similar(es) ya programada(s):\n\n`;

        similarEvents.forEach((event, index) => {
          const start = new Date(event.start);
          const formattedDate = start.toLocaleDateString('es-PE', {
            timeZone: 'America/Lima',
            weekday: 'long',
            day: 'numeric',
            month: 'long',
          });
          const formattedTime = start.toLocaleTimeString('es-PE', {
            timeZone: 'America/Lima',
            hour: '2-digit',
            minute: '2-digit',
          });

          suggestionText += `${index + 1}. *${event.summary}*\n`;
          suggestionText += `   📆 ${formattedDate} - ${formattedTime}\n\n`;
        });

        suggestionText += '¿Quieres modificar alguna de estas citas o prefieres crear una nueva?';

        return {
          hasIntent: true,
          eventCreated: false,
          similarEvents,
          suggestedResponse: suggestionText,
        };
      }

      // 4. Crear el evento si no hay duplicados
      // 🔒 Pasar subscriberId para asociar evento al usuario
      const eventResult = await this.createCalendarEvent(
        intentResult.eventDetails,
        contactEmail,
        subscriberId // 🔒 Asociar al usuario
      );

      if (!eventResult.success) {
        return {
          hasIntent: true,
          eventCreated: false,
          suggestedResponse: `Entiendo que quieres agendar una reunión, pero hubo un problema al crearla. ¿Podrías darme más detalles sobre la fecha y hora que prefieres?`,
        };
      }

      // 5. Generar respuesta con confirmación
      const formattedDate = new Date(eventResult.dateTime!).toLocaleString('es-PE', {
        timeZone: 'America/Lima',
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });

      let confirmationMessage = `✅ ¡Listo! He agendado tu reunión:\n\n`;
      confirmationMessage += `📅 *${eventResult.summary}*\n`;
      confirmationMessage += `🕒 ${formattedDate}\n\n`;

      if (eventResult.meetUrl) {
        confirmationMessage += `📹 *Link de Google Meet:*\n${eventResult.meetUrl}\n\n`;
      }

      confirmationMessage += `🔗 *Ver en Google Calendar:*\n${eventResult.eventUrl}\n\n`;
      confirmationMessage += `Te llegará un recordatorio por email 1 día antes y una notificación 30 minutos antes.`;

      return {
        hasIntent: true,
        eventCreated: true,
        calendarInfo: eventResult,
        suggestedResponse: confirmationMessage,
      };
    } catch (error) {
      logger.error('❌ Error al procesar intención de calendario:', error);
      return {
        hasIntent: false,
        eventCreated: false,
      };
    }
  }
}

export default new CalendarIntentService();
