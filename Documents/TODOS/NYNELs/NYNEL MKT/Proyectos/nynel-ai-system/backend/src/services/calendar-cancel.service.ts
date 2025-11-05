// ═══════════════════════════════════════════════════════════════════════════
// 🗑️ SERVICIO DE CANCELACIÓN DE CITAS EN GOOGLE CALENDAR
// ═══════════════════════════════════════════════════════════════════════════

import googleCalendarService from './google-calendar.service.js';
import { logger } from '#/utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// Interfaces
// ─────────────────────────────────────────────────────────────────────────────

interface CancelEventResult {
  success: boolean;
  message: string;
  eventId?: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Calendar Cancel Service Class
// ─────────────────────────────────────────────────────────────────────────────

export class CalendarCancelService {
  /**
   * Cancelar un evento de Google Calendar desde URL
   *
   * La URL de Google Calendar tiene formato:
   * https://calendar.google.com/calendar/event?eid=BASE64_ENCODED_ID
   *
   * El eventId real está dentro del eid codificado en base64
   */
  async cancelEventFromUrl(eventUrl: string): Promise<CancelEventResult> {
    try {
      logger.info(`🗑️  Iniciando cancelación de evento desde URL: ${eventUrl}`);

      // ─────────────────────────────────────────────────────────────
      // 1. Extraer event ID de la URL
      // ─────────────────────────────────────────────────────────────

      // Formato típico: https://calendar.google.com/calendar/event?eid=XXXX
      const match = eventUrl.match(/eid=([^&]+)/);

      if (!match) {
        logger.error('❌ No se pudo extraer event ID de URL');
        return {
          success: false,
          message: 'URL de evento inválida - no se encontró parámetro eid',
        };
      }

      const encodedEventId = match[1];
      logger.info(`🔍 EID codificado encontrado: ${encodedEventId.substring(0, 20)}...`);

      // ─────────────────────────────────────────────────────────────
      // 2. Decodificar event ID (base64)
      // ─────────────────────────────────────────────────────────────

      // El eid es base64url (URL-safe base64)
      // Convertir de base64url a base64 estándar
      const base64 = encodedEventId
        .replace(/-/g, '+')
        .replace(/_/g, '/');

      const decodedBuffer = Buffer.from(base64, 'base64');
      const decodedString = decodedBuffer.toString('utf-8');

      // El string decodificado tiene formato: "eventId calendarId"
      // Ejemplo: "abc123xyz empresarial@nynelmkt.com"
      const parts = decodedString.split(' ');
      const eventId = parts[0];

      if (!eventId) {
        logger.error('❌ No se pudo decodificar event ID');
        return {
          success: false,
          message: 'No se pudo decodificar el ID del evento',
        };
      }

      logger.info(`✅ Event ID decodificado: ${eventId}`);

      // ─────────────────────────────────────────────────────────────
      // 3. Eliminar evento usando Google Calendar API
      // ─────────────────────────────────────────────────────────────

      await googleCalendarService.deleteEvent(eventId);

      logger.info(`✅ Evento ${eventId} eliminado exitosamente de Google Calendar`);

      return {
        success: true,
        message: 'Evento cancelado exitosamente',
        eventId,
      };

    } catch (error: any) {
      logger.error('❌ Error al cancelar evento:', {
        message: error.message,
        stack: error.stack,
      });

      return {
        success: false,
        message: `Error al cancelar evento: ${error.message}`,
      };
    }
  }

  /**
   * Cancelar un evento directamente por su ID
   */
  async cancelEventById(eventId: string): Promise<CancelEventResult> {
    try {
      logger.info(`🗑️  Cancelando evento por ID: ${eventId}`);

      await googleCalendarService.deleteEvent(eventId);

      logger.info(`✅ Evento ${eventId} eliminado exitosamente`);

      return {
        success: true,
        message: 'Evento cancelado exitosamente',
        eventId,
      };

    } catch (error: any) {
      logger.error('❌ Error al cancelar evento:', error);

      return {
        success: false,
        message: `Error al cancelar evento: ${error.message}`,
      };
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Singleton Instance
// ─────────────────────────────────────────────────────────────────────────────

export const calendarCancelService = new CalendarCancelService();
