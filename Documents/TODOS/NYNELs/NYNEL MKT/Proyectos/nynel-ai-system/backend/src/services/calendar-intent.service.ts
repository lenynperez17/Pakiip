// ═══════════════════════════════════════════════════════════════════════════
// 📅 SERVICIO DE DETECCIÓN Y GESTIÓN COMPLETA DE CITAS DE CALENDARIO
// ═══════════════════════════════════════════════════════════════════════════
// Detecta intenciones de calendario y ejecuta acciones: CREAR, MODIFICAR, CANCELAR, LISTAR

import OpenAI from 'openai';
import { logger } from '../utils/logger';
import googleCalendarService from './google-calendar.service';

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
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
        model: 'gpt-4o-mini',
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
   */
  async searchExistingEvents(
    criteria: EventSearchCriteria
  ): Promise<EventMatch[]> {
    try {
      const timeMin = criteria.dateStart
        ? new Date(criteria.dateStart).toISOString()
        : new Date().toISOString();

      const timeMax = criteria.dateEnd
        ? new Date(criteria.dateEnd + 'T23:59:59').toISOString()
        : new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(); // +30 días

      const events = await googleCalendarService.listEvents({
        timeMin,
        timeMax,
        maxResults: 50,
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
        const currentStart = new Date(currentEvent.start.dateTime || currentEvent.start.date);

        let year = currentStart.getFullYear();
        let month = currentStart.getMonth();
        let day = currentStart.getDate();
        let hour = currentStart.getHours();
        let minute = currentStart.getMinutes();

        if (modifications.newDate) {
          const newDate = new Date(modifications.newDate);
          year = newDate.getFullYear();
          month = newDate.getMonth();
          day = newDate.getDate();
        }

        if (modifications.newTime) {
          const [h, m] = modifications.newTime.split(':');
          hour = parseInt(h);
          minute = parseInt(m);
        }

        const startDateTime = new Date(year, month, day, hour, minute);
        const durationMinutes = modifications.newDuration || 60;
        const endDateTime = new Date(startDateTime.getTime() + durationMinutes * 60000);

        updatedData.start = {
          dateTime: startDateTime.toISOString().slice(0, 19) + '-05:00',
          timeZone: 'America/Lima',
        };
        updatedData.end = {
          dateTime: endDateTime.toISOString().slice(0, 19) + '-05:00',
          timeZone: 'America/Lima',
        };
      }

      // Asegurar que siempre envíe notificaciones
      updatedData.sendUpdates = 'all';

      const updatedEvent = await googleCalendarService.updateEvent(eventId, updatedData);

      logger.info('✅ Evento modificado exitosamente:', {
        id: updatedEvent.id,
        summary: updatedEvent.summary,
      });

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
   */
  async listUserAgenda(timeRange: 'hoy' | 'mañana' | 'semana' | 'mes' = 'semana'): Promise<string> {
    try {
      let events: any[] = [];

      if (timeRange === 'hoy') {
        events = await googleCalendarService.getTodayEvents();
      } else {
        const days = timeRange === 'mañana' ? 1 : timeRange === 'semana' ? 7 : 30;
        events = await googleCalendarService.getUpcomingEvents(days);
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
   */
  async findSimilarEvents(
    summary: string,
    date: string,
    timeDelta: number = 2 // Buscar ±2 días
  ): Promise<EventMatch[]> {
    try {
      const targetDate = new Date(date);
      const startDate = new Date(targetDate);
      startDate.setDate(startDate.getDate() - timeDelta);

      const endDate = new Date(targetDate);
      endDate.setDate(endDate.getDate() + timeDelta);

      const events = await googleCalendarService.listEvents({
        timeMin: startDate.toISOString(),
        timeMax: endDate.toISOString(),
        maxResults: 50,
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

Tu trabajo es analizar el mensaje del usuario y determinar si quiere agendar una cita, reunión, llamada o evento.

PALABRAS CLAVE DE INTENCIÓN:
- agendar, agenda, agendame, programar, reservar, apartar
- cita, reunión, llamada, meet, meeting, junta, encuentro
- mañana, hoy, tarde, semana, mes, día, hora
- "para el...", "el día...", "a las..."

Si detectas intención de agendar, extrae:
1. Título/asunto de la reunión (si no lo menciona, usa "Reunión con NYNEL MKT")
2. Fecha (convierte expresiones como "mañana", "pasado mañana" a formato ISO)
3. Hora (formato 24h: HH:mm)
4. Duración estimada en minutos (por defecto 60 min)
5. Si menciona "meet", "videollamada", "virtual" → needsMeet: true

IMPORTANTE:
- Si dice "hoy", usa la fecha de HOY
- Si dice "mañana", suma 1 día a HOY
- Si dice "pasado mañana", suma 2 días
- Si dice "el lunes", "el martes", etc., calcula la fecha del próximo día de esa semana
- Si no menciona hora, pregunta en tu respuesta
- Si no menciona fecha, pregunta en tu respuesta

Responde en JSON:
{
  "hasCalendarIntent": true/false,
  "eventDetails": {
    "summary": "Título de la reunión",
    "description": "Descripción o motivo",
    "date": "2025-01-28",
    "time": "15:00",
    "duration": 60,
    "needsMeet": true/false
  },
  "extractedInfo": "Resumen de lo que entendiste"
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
        model: 'gpt-4o-mini',
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
   */
  async createCalendarEvent(
    eventDetails: CalendarIntentResult['eventDetails'],
    contactEmail?: string
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

      // Agregar email del contacto si existe y es diferente
      if (contactEmail && contactEmail !== 'empresarial@nynelmkt.com') {
        attendees.push({ email: contactEmail });
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
      };

      // ═══════════════════════════════════════════════════════════════
      // 📹 SIEMPRE CREAR CON GOOGLE MEET (FORZADO)
      // ═══════════════════════════════════════════════════════════════
      logger.info('📹 Creando evento con Google Meet (forzado)...');
      const createdEvent = await googleCalendarService.createEventWithMeet(eventData);

      logger.info('✅ Evento creado exitosamente:', {
        id: createdEvent.id,
        htmlLink: createdEvent.htmlLink,
        hangoutLink: createdEvent.hangoutLink,
      });

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
   */
  async processCalendarIntent(
    userMessage: string,
    conversationHistory?: Array<{ role: string; content: string }>,
    contactEmail?: string
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
          const availableSlots = await googleCalendarService.getNextAvailableSlots(
            5, // cantidad de slots
            { start: '09:00', end: '18:00' }, // horario laboral
            60, // duración 60min
            7 // buscar en los próximos 7 días
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
      const similarEvents = await this.findSimilarEvents(
        intentResult.eventDetails.summary,
        intentResult.eventDetails.date
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
      const eventResult = await this.createCalendarEvent(
        intentResult.eventDetails,
        contactEmail
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
