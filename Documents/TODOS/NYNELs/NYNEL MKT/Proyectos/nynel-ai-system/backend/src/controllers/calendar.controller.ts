// ===================================
// CALENDAR CONTROLLER - NYNEL AI SYSTEM
// ===================================
// Controlador REST para gestión de calendario vía API

import { Request, Response } from 'express';
import googleCalendarService, { CalendarEvent } from '../services/google-calendar.service.js';
import { logger } from '#/utils/logger.js';

class CalendarController {
  /**
   * GET /api/calendar/auth
   * Obtiene la URL de autorización OAuth
   */
  public async getAuthUrl(req: Request, res: Response): Promise<void> {
    try {
      const authUrl = googleCalendarService.getAuthUrl();
      res.json({
        success: true,
        authUrl,
        message: 'Visita esta URL para autorizar el acceso a Google Calendar'
      });
    } catch (error: any) {
      logger.error('❌ Error al obtener URL de autorización:', error);
      res.status(500).json({
        success: false,
        message: 'Error al obtener URL de autorización',
        error: error.message
      });
    }
  }

  /**
   * GET /api/calendar/auth/callback
   * Callback de OAuth - Recibe el código de autorización
   */
  public async authCallback(req: Request, res: Response): Promise<void> {
    try {
      const { code } = req.query;

      if (!code || typeof code !== 'string') {
        res.status(400).json({
          success: false,
          message: 'Código de autorización no proporcionado'
        });
        return;
      }

      await googleCalendarService.getTokenFromCode(code);

      res.send(`
        <!DOCTYPE html>
        <html>
          <head>
            <title>Autorización Exitosa</title>
            <style>
              body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
              }
              .container {
                text-align: center;
                background: rgba(255, 255, 255, 0.1);
                padding: 40px;
                border-radius: 20px;
                backdrop-filter: blur(10px);
              }
              h1 { font-size: 48px; margin-bottom: 20px; }
              p { font-size: 18px; }
              .check { font-size: 80px; margin-bottom: 20px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="check">✅</div>
              <h1>¡Autorización Exitosa!</h1>
              <p>Google Calendar se ha conectado correctamente.</p>
              <p>Ya puedes agendar eventos a través del chat.</p>
              <p style="margin-top: 30px; opacity: 0.8;">Puedes cerrar esta ventana.</p>
            </div>
          </body>
        </html>
      `);
    } catch (error: any) {
      logger.error('❌ Error en callback de autorización:', error);
      res.status(500).send(`
        <!DOCTYPE html>
        <html>
          <head>
            <title>Error de Autorización</title>
            <style>
              body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
              }
              .container {
                text-align: center;
                background: rgba(255, 255, 255, 0.1);
                padding: 40px;
                border-radius: 20px;
                backdrop-filter: blur(10px);
              }
              h1 { font-size: 48px; margin-bottom: 20px; }
              p { font-size: 18px; }
              .error { font-size: 80px; margin-bottom: 20px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="error">❌</div>
              <h1>Error de Autorización</h1>
              <p>${error.message}</p>
              <p style="margin-top: 30px; opacity: 0.8;">Intenta nuevamente.</p>
            </div>
          </body>
        </html>
      `);
    }
  }

  /**
   * GET /api/calendar/status
   * Verifica si el calendario está autenticado
   */
  public async getStatus(req: Request, res: Response): Promise<void> {
    try {
      const isAuthenticated = googleCalendarService.isAuthenticated();
      res.json({
        success: true,
        authenticated: isAuthenticated,
        message: isAuthenticated
          ? 'Google Calendar está autenticado y listo para usar'
          : 'Se requiere autenticación. Usa /api/calendar/auth'
      });
    } catch (error: any) {
      logger.error('❌ Error al verificar estado:', error);
      res.status(500).json({
        success: false,
        message: 'Error al verificar estado de autenticación',
        error: error.message
      });
    }
  }

  /**
   * POST /api/calendar/events
   * Crea un nuevo evento
   */
  public async createEvent(req: Request, res: Response): Promise<void> {
    try {
      const eventData: CalendarEvent = req.body;

      // Validaciones básicas
      if (!eventData.summary) {
        res.status(400).json({
          success: false,
          message: 'El título del evento es requerido'
        });
        return;
      }

      if (!eventData.start || !eventData.start.dateTime) {
        res.status(400).json({
          success: false,
          message: 'La fecha de inicio es requerida'
        });
        return;
      }

      if (!eventData.end || !eventData.end.dateTime) {
        res.status(400).json({
          success: false,
          message: 'La fecha de fin es requerida'
        });
        return;
      }

      const event = await googleCalendarService.createEvent(eventData);

      res.json({
        success: true,
        message: 'Evento creado exitosamente',
        event: {
          id: event.id,
          summary: event.summary,
          start: event.start,
          end: event.end,
          htmlLink: event.htmlLink,
          hangoutLink: event.hangoutLink // Google Meet si se creó
        }
      });
    } catch (error: any) {
      logger.error('❌ Error al crear evento:', error);
      res.status(500).json({
        success: false,
        message: 'Error al crear evento',
        error: error.message
      });
    }
  }

  /**
   * POST /api/calendar/events/meet
   * Crea un evento con Google Meet
   */
  public async createEventWithMeet(req: Request, res: Response): Promise<void> {
    try {
      const eventData: CalendarEvent = req.body;

      if (!eventData.summary || !eventData.start?.dateTime || !eventData.end?.dateTime) {
        res.status(400).json({
          success: false,
          message: 'Título, fecha de inicio y fin son requeridos'
        });
        return;
      }

      const event = await googleCalendarService.createEventWithMeet(eventData);

      res.json({
        success: true,
        message: 'Evento con Google Meet creado exitosamente',
        event: {
          id: event.id,
          summary: event.summary,
          start: event.start,
          end: event.end,
          htmlLink: event.htmlLink,
          meetLink: event.hangoutLink // Link de Google Meet
        }
      });
    } catch (error: any) {
      logger.error('❌ Error al crear evento con Meet:', error);
      res.status(500).json({
        success: false,
        message: 'Error al crear evento con Google Meet',
        error: error.message
      });
    }
  }

  /**
   * GET /api/calendar/events
   * Lista eventos del calendario
   */
  public async listEvents(req: Request, res: Response): Promise<void> {
    try {
      const { timeMin, timeMax, q, maxResults } = req.query;

      const events = await googleCalendarService.listEvents({
        timeMin: timeMin as string,
        timeMax: timeMax as string,
        q: q as string,
        maxResults: maxResults ? parseInt(maxResults as string) : 10
      });

      res.json({
        success: true,
        count: events.length,
        events: events.map((event) => ({
          id: event.id,
          summary: event.summary,
          description: event.description,
          start: event.start,
          end: event.end,
          location: event.location,
          htmlLink: event.htmlLink,
          meetLink: event.hangoutLink
        }))
      });
    } catch (error: any) {
      logger.error('❌ Error al listar eventos:', error);
      res.status(500).json({
        success: false,
        message: 'Error al listar eventos',
        error: error.message
      });
    }
  }

  /**
   * GET /api/calendar/events/upcoming
   * Obtiene próximos eventos
   */
  public async getUpcomingEvents(req: Request, res: Response): Promise<void> {
    try {
      const { maxResults } = req.query;
      const max = maxResults ? parseInt(maxResults as string) : 5;

      const events = await googleCalendarService.getUpcomingEvents(max);

      res.json({
        success: true,
        count: events.length,
        events: events.map((event) => ({
          id: event.id,
          summary: event.summary,
          start: event.start,
          end: event.end,
          htmlLink: event.htmlLink
        }))
      });
    } catch (error: any) {
      logger.error('❌ Error al obtener próximos eventos:', error);
      res.status(500).json({
        success: false,
        message: 'Error al obtener próximos eventos',
        error: error.message
      });
    }
  }

  /**
   * GET /api/calendar/events/today
   * Obtiene eventos de hoy
   */
  public async getTodayEvents(req: Request, res: Response): Promise<void> {
    try {
      const events = await googleCalendarService.getTodayEvents();

      res.json({
        success: true,
        count: events.length,
        events: events.map((event) => ({
          id: event.id,
          summary: event.summary,
          start: event.start,
          end: event.end,
          htmlLink: event.htmlLink
        }))
      });
    } catch (error: any) {
      logger.error('❌ Error al obtener eventos de hoy:', error);
      res.status(500).json({
        success: false,
        message: 'Error al obtener eventos de hoy',
        error: error.message
      });
    }
  }

  /**
   * GET /api/calendar/events/:id
   * Obtiene un evento específico
   */
  public async getEvent(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const event = await googleCalendarService.getEvent(id);

      res.json({
        success: true,
        event: {
          id: event.id,
          summary: event.summary,
          description: event.description,
          location: event.location,
          start: event.start,
          end: event.end,
          attendees: event.attendees,
          htmlLink: event.htmlLink,
          meetLink: event.hangoutLink
        }
      });
    } catch (error: any) {
      logger.error('❌ Error al obtener evento:', error);
      res.status(404).json({
        success: false,
        message: 'Evento no encontrado',
        error: error.message
      });
    }
  }

  /**
   * PUT /api/calendar/events/:id
   * Actualiza un evento
   */
  public async updateEvent(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const eventData = req.body;

      const event = await googleCalendarService.updateEvent(id, eventData);

      res.json({
        success: true,
        message: 'Evento actualizado exitosamente',
        event: {
          id: event.id,
          summary: event.summary,
          start: event.start,
          end: event.end,
          htmlLink: event.htmlLink
        }
      });
    } catch (error: any) {
      logger.error('❌ Error al actualizar evento:', error);
      res.status(500).json({
        success: false,
        message: 'Error al actualizar evento',
        error: error.message
      });
    }
  }

  /**
   * DELETE /api/calendar/events/:id
   * Elimina un evento
   */
  public async deleteEvent(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      await googleCalendarService.deleteEvent(id);

      res.json({
        success: true,
        message: 'Evento eliminado exitosamente'
      });
    } catch (error: any) {
      logger.error('❌ Error al eliminar evento:', error);
      res.status(500).json({
        success: false,
        message: 'Error al eliminar evento',
        error: error.message
      });
    }
  }

  /**
   * POST /api/calendar/check-availability
   * Verifica disponibilidad en un rango de fechas
   */
  public async checkAvailability(req: Request, res: Response): Promise<void> {
    try {
      const { timeMin, timeMax } = req.body;

      if (!timeMin || !timeMax) {
        res.status(400).json({
          success: false,
          message: 'timeMin y timeMax son requeridos'
        });
        return;
      }

      const isAvailable = await googleCalendarService.checkAvailability(timeMin, timeMax);

      res.json({
        success: true,
        available: isAvailable,
        message: isAvailable
          ? 'Horario disponible'
          : 'Ya hay eventos agendados en ese horario'
      });
    } catch (error: any) {
      logger.error('❌ Error al verificar disponibilidad:', error);
      res.status(500).json({
        success: false,
        message: 'Error al verificar disponibilidad',
        error: error.message
      });
    }
  }

  /**
   * POST /api/calendar/cancel-appointment
   * Cancelar una cita desde ManyChat usando URL del evento
   */
  public async cancelAppointment(req: Request, res: Response): Promise<void> {
    try {
      const { subscriber_id, event_url } = req.body;

      // Validar parámetros requeridos
      if (!subscriber_id || !event_url) {
        res.status(400).json({
          success: false,
          message: 'subscriber_id y event_url son requeridos'
        });
        return;
      }

      logger.info(`🗑️  Solicitud de cancelación de cita de subscriber: ${subscriber_id}`);

      // Importar dinámicamente el servicio de cancelación y ManyChat API
      const { calendarCancelService } = await import('../services/calendar-cancel.service');
      const { manyChatAPI } = await import('../services/manychat-api.service');

      // Cancelar evento en Google Calendar
      const cancelResult = await calendarCancelService.cancelEventFromUrl(event_url);

      if (cancelResult.success) {
        logger.info(`✅ Evento cancelado exitosamente: ${cancelResult.eventId}`);

        // Actualizar custom fields en ManyChat
        await manyChatAPI.setCustomField(subscriber_id, 'appointment_status', 'cancelled');
        await manyChatAPI.setCustomField(subscriber_id, 'next_appointment_datetime', '');
        await manyChatAPI.setCustomField(subscriber_id, 'meeting_url', '');
        await manyChatAPI.setCustomField(subscriber_id, 'calendar_event_url', '');

        logger.info(`✅ Custom fields actualizados en ManyChat`);

        res.json({
          success: true,
          message: 'Cita cancelada exitosamente',
          eventId: cancelResult.eventId
        });
      } else {
        logger.error(`❌ Error al cancelar evento: ${cancelResult.message}`);

        res.status(400).json({
          success: false,
          message: cancelResult.message
        });
      }

    } catch (error: any) {
      logger.error('❌ Error en endpoint de cancelación:', error);
      res.status(500).json({
        success: false,
        message: 'Error al cancelar cita',
        error: error.message
      });
    }
  }
}

export default new CalendarController();
