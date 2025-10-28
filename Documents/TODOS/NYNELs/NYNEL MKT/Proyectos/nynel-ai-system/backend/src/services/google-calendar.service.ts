// ===================================
// GOOGLE CALENDAR SERVICE - NYNEL AI SYSTEM
// ===================================
// Servicio completo para gestión de eventos de Google Calendar
// con autenticación OAuth 2.0 y funciones CRUD

import { google } from 'googleapis';
import { OAuth2Client } from 'google-auth-library';
import * as fs from 'fs';
import * as path from 'path';

// Tipos para eventos de calendario
export interface CalendarEvent {
  id?: string;
  summary: string; // Título del evento
  description?: string;
  location?: string;
  start: {
    dateTime: string; // Formato ISO: "2025-01-27T10:00:00-05:00"
    timeZone?: string;
  };
  end: {
    dateTime: string;
    timeZone?: string;
  };
  attendees?: Array<{ email: string }>;
  reminders?: {
    useDefault: boolean;
    overrides?: Array<{ method: string; minutes: number }>;
  };
  conferenceData?: {
    createRequest?: {
      requestId: string;
      conferenceSolutionKey: { type: string };
    };
  };
}

export interface EventSearchParams {
  timeMin?: string; // Fecha mínima
  timeMax?: string; // Fecha máxima
  q?: string; // Búsqueda por texto
  maxResults?: number;
}

// Interfaz para slots de tiempo disponibles
export interface TimeSlot {
  startTime: string; // ISO format: "2025-01-28T09:00:00-05:00"
  endTime: string;   // ISO format: "2025-01-28T10:00:00-05:00"
  available: boolean;
  displayTime?: string; // Formato amigable: "9:00 AM"
}

// Interfaz para horario laboral
export interface WorkingHours {
  start: string; // Formato "HH:mm" -> "09:00"
  end: string;   // Formato "HH:mm" -> "18:00"
}

class GoogleCalendarService {
  private oauth2Client: OAuth2Client;
  private calendar: any;
  private credentialsPath: string;
  private tokenPath: string;
  private readonly SCOPES = [
    'https://www.googleapis.com/auth/calendar',
    'https://www.googleapis.com/auth/calendar.events'
  ];

  constructor() {
    this.credentialsPath = path.join(
      __dirname,
      '../../',
      process.env.GOOGLE_CREDENTIALS_PATH || 'config/google/credentials.json'
    );
    this.tokenPath = path.join(
      __dirname,
      '../../',
      process.env.GOOGLE_TOKEN_PATH || 'config/google/token.json'
    );

    this.oauth2Client = new OAuth2Client();
    this.initializeClient();
  }

  /**
   * Inicializa el cliente OAuth 2.0
   */
  private async initializeClient(): Promise<void> {
    try {
      // Leer credenciales
      const credentials = JSON.parse(fs.readFileSync(this.credentialsPath, 'utf8'));
      const { client_secret, client_id, redirect_uris } = credentials.web;

      this.oauth2Client = new google.auth.OAuth2(
        client_id,
        client_secret,
        redirect_uris[0]
      );

      // Intentar cargar token guardado
      try {
        const token = JSON.parse(fs.readFileSync(this.tokenPath, 'utf8'));
        this.oauth2Client.setCredentials(token);
        console.log('✅ Token de Google Calendar cargado correctamente');
      } catch (error) {
        console.log('⚠️ No se encontró token guardado. Se requiere autenticación.');
      }

      // Inicializar API de Calendar
      this.calendar = google.calendar({ version: 'v3', auth: this.oauth2Client });
    } catch (error) {
      console.error('❌ Error al inicializar Google Calendar:', error);
      throw error;
    }
  }

  /**
   * Genera la URL de autorización OAuth
   */
  public getAuthUrl(): string {
    const authUrl = this.oauth2Client.generateAuthUrl({
      access_type: 'offline',
      scope: this.SCOPES,
      prompt: 'consent' // Fuerza consentimiento para obtener refresh token
    });
    return authUrl;
  }

  /**
   * Intercambia el código de autorización por tokens
   */
  public async getTokenFromCode(code: string): Promise<void> {
    try {
      const { tokens } = await this.oauth2Client.getToken(code);
      this.oauth2Client.setCredentials(tokens);

      // Guardar tokens en archivo
      const tokenDir = path.dirname(this.tokenPath);
      if (!fs.existsSync(tokenDir)) {
        fs.mkdirSync(tokenDir, { recursive: true });
      }
      fs.writeFileSync(this.tokenPath, JSON.stringify(tokens, null, 2));

      console.log('✅ Tokens de Google Calendar guardados correctamente');
    } catch (error) {
      console.error('❌ Error al obtener tokens:', error);
      throw error;
    }
  }

  /**
   * Verifica si el usuario está autenticado
   */
  public isAuthenticated(): boolean {
    return this.oauth2Client.credentials && !!this.oauth2Client.credentials.access_token;
  }

  /**
   * Refresca el token de acceso si es necesario
   */
  private async refreshTokenIfNeeded(): Promise<void> {
    try {
      const credentials = this.oauth2Client.credentials;

      if (!credentials || !credentials.expiry_date) {
        throw new Error('No hay credenciales válidas');
      }

      // Si el token expira en menos de 5 minutos, refrescarlo
      const expiryDate = new Date(credentials.expiry_date);
      const now = new Date();
      const fiveMinutes = 5 * 60 * 1000;

      if (expiryDate.getTime() - now.getTime() < fiveMinutes) {
        console.log('🔄 Refrescando token de Google Calendar...');
        const { credentials: newCredentials } = await this.oauth2Client.refreshAccessToken();
        this.oauth2Client.setCredentials(newCredentials);

        // Guardar nuevos tokens
        fs.writeFileSync(this.tokenPath, JSON.stringify(newCredentials, null, 2));
        console.log('✅ Token refrescado correctamente');
      }
    } catch (error) {
      console.error('❌ Error al refrescar token:', error);
      throw error;
    }
  }

  /**
   * Crear un evento en el calendario
   */
  public async createEvent(eventData: CalendarEvent): Promise<any> {
    try {
      await this.refreshTokenIfNeeded();

      const calendarId = process.env.GOOGLE_CALENDAR_ID || 'primary';
      const timezone = process.env.GOOGLE_CALENDAR_TIMEZONE || 'America/Lima';

      // Asegurar que las fechas tengan timezone
      if (!eventData.start.timeZone) {
        eventData.start.timeZone = timezone;
      }
      if (!eventData.end.timeZone) {
        eventData.end.timeZone = timezone;
      }

      const event = await this.calendar.events.insert({
        calendarId,
        resource: eventData,
        conferenceDataVersion: eventData.conferenceData ? 1 : 0, // Para Google Meet
        sendUpdates: 'all' // Enviar notificaciones a asistentes
      });

      console.log('✅ Evento creado:', event.data.htmlLink);
      return event.data;
    } catch (error) {
      console.error('❌ Error al crear evento:', error);
      throw error;
    }
  }

  /**
   * Listar eventos del calendario
   */
  public async listEvents(params: EventSearchParams = {}): Promise<any[]> {
    try {
      await this.refreshTokenIfNeeded();

      const calendarId = process.env.GOOGLE_CALENDAR_ID || 'primary';
      const timezone = process.env.GOOGLE_CALENDAR_TIMEZONE || 'America/Lima';

      const response = await this.calendar.events.list({
        calendarId,
        timeMin: params.timeMin || new Date().toISOString(),
        timeMax: params.timeMax,
        maxResults: params.maxResults || 10,
        singleEvents: true,
        orderBy: 'startTime',
        q: params.q,
        timeZone: timezone
      });

      return response.data.items || [];
    } catch (error) {
      console.error('❌ Error al listar eventos:', error);
      throw error;
    }
  }

  /**
   * Obtener un evento específico
   */
  public async getEvent(eventId: string): Promise<any> {
    try {
      await this.refreshTokenIfNeeded();

      const calendarId = process.env.GOOGLE_CALENDAR_ID || 'primary';
      const response = await this.calendar.events.get({
        calendarId,
        eventId
      });

      return response.data;
    } catch (error) {
      console.error('❌ Error al obtener evento:', error);
      throw error;
    }
  }

  /**
   * Actualizar un evento existente
   */
  public async updateEvent(eventId: string, eventData: Partial<CalendarEvent>): Promise<any> {
    try {
      await this.refreshTokenIfNeeded();

      const calendarId = process.env.GOOGLE_CALENDAR_ID || 'primary';
      const timezone = process.env.GOOGLE_CALENDAR_TIMEZONE || 'America/Lima';

      // Asegurar timezone si se actualizan fechas
      if (eventData.start && !eventData.start.timeZone) {
        eventData.start.timeZone = timezone;
      }
      if (eventData.end && !eventData.end.timeZone) {
        eventData.end.timeZone = timezone;
      }

      const event = await this.calendar.events.patch({
        calendarId,
        eventId,
        resource: eventData,
        sendUpdates: 'all'
      });

      console.log('✅ Evento actualizado:', event.data.htmlLink);
      return event.data;
    } catch (error) {
      console.error('❌ Error al actualizar evento:', error);
      throw error;
    }
  }

  /**
   * Eliminar un evento
   */
  public async deleteEvent(eventId: string): Promise<void> {
    try {
      await this.refreshTokenIfNeeded();

      const calendarId = process.env.GOOGLE_CALENDAR_ID || 'primary';
      await this.calendar.events.delete({
        calendarId,
        eventId,
        sendUpdates: 'all'
      });

      console.log('✅ Evento eliminado:', eventId);
    } catch (error) {
      console.error('❌ Error al eliminar evento:', error);
      throw error;
    }
  }

  /**
   * Verificar disponibilidad en un rango de fechas
   */
  public async checkAvailability(timeMin: string, timeMax: string): Promise<boolean> {
    try {
      const events = await this.listEvents({ timeMin, timeMax });
      return events.length === 0;
    } catch (error) {
      console.error('❌ Error al verificar disponibilidad:', error);
      throw error;
    }
  }

  /**
   * 🆕 OBTENER SLOTS DISPONIBLES EN UN DÍA ESPECÍFICO
   *
   * Este método consulta el calendario real y retorna todos los slots
   * de tiempo disponibles/ocupados para una fecha específica.
   *
   * @param date - Fecha en formato "YYYY-MM-DD" (ej: "2025-01-28")
   * @param workingHours - Horario laboral {start: "09:00", end: "18:00"}
   * @param slotDuration - Duración de cada slot en minutos (default: 60)
   * @returns Array de TimeSlot con información de disponibilidad
   *
   * @example
   * const slots = await getAvailableSlots("2025-01-28", {start: "09:00", end: "18:00"}, 60);
   * // Retorna: [
   * //   {startTime: "2025-01-28T09:00:00-05:00", endTime: "2025-01-28T10:00:00-05:00", available: true, displayTime: "9:00 AM"},
   * //   {startTime: "2025-01-28T10:00:00-05:00", endTime: "2025-01-28T11:00:00-05:00", available: false, displayTime: "10:00 AM"},
   * //   ...
   * // ]
   */
  public async getAvailableSlots(
    date: string, // "YYYY-MM-DD"
    workingHours: WorkingHours = { start: '09:00', end: '18:00' },
    slotDuration: number = 60 // minutos
  ): Promise<TimeSlot[]> {
    try {
      await this.refreshTokenIfNeeded();

      const timezone = process.env.GOOGLE_CALENDAR_TIMEZONE || 'America/Lima';

      // Parsear fecha y horario laboral
      const [year, month, day] = date.split('-').map(Number);
      const [startHour, startMinute] = workingHours.start.split(':').map(Number);
      const [endHour, endMinute] = workingHours.end.split(':').map(Number);

      // Crear Date objects para inicio y fin del día laboral
      const startOfWorkday = new Date(year, month - 1, day, startHour, startMinute, 0);
      const endOfWorkday = new Date(year, month - 1, day, endHour, endMinute, 0);

      // Obtener eventos ocupados del calendario en este rango
      const busyEvents = await this.listEvents({
        timeMin: startOfWorkday.toISOString(),
        timeMax: endOfWorkday.toISOString(),
        maxResults: 250 // Asegurar que obtenemos todos los eventos del día
      });

      console.log(`📅 Analizando disponibilidad para ${date}: ${busyEvents.length} eventos ocupados`);

      // Generar todos los slots posibles del día
      const allSlots: TimeSlot[] = [];
      let currentTime = new Date(startOfWorkday);

      while (currentTime < endOfWorkday) {
        const slotStart = new Date(currentTime);
        const slotEnd = new Date(currentTime.getTime() + slotDuration * 60 * 1000);

        // No agregar slots que excedan el horario laboral
        if (slotEnd > endOfWorkday) {
          break;
        }

        // Verificar si este slot tiene conflicto con algún evento ocupado
        const hasConflict = busyEvents.some((event: any) => {
          if (!event.start?.dateTime || !event.end?.dateTime) return false;

          const eventStart = new Date(event.start.dateTime);
          const eventEnd = new Date(event.end.dateTime);

          // Hay conflicto si:
          // 1. El slot empieza durante un evento
          // 2. El slot termina durante un evento
          // 3. El slot contiene completamente un evento
          return (
            (slotStart >= eventStart && slotStart < eventEnd) ||
            (slotEnd > eventStart && slotEnd <= eventEnd) ||
            (slotStart <= eventStart && slotEnd >= eventEnd)
          );
        });

        // Formatear hora para display amigable
        const displayTime = slotStart.toLocaleTimeString('es-PE', {
          hour: 'numeric',
          minute: '2-digit',
          hour12: true,
          timeZone: timezone
        });

        allSlots.push({
          startTime: slotStart.toISOString(),
          endTime: slotEnd.toISOString(),
          available: !hasConflict,
          displayTime
        });

        // Avanzar al siguiente slot
        currentTime = new Date(currentTime.getTime() + slotDuration * 60 * 1000);
      }

      const availableCount = allSlots.filter(s => s.available).length;
      console.log(`✅ Generados ${allSlots.length} slots totales (${availableCount} disponibles, ${allSlots.length - availableCount} ocupados)`);

      return allSlots;
    } catch (error) {
      console.error('❌ Error al obtener slots disponibles:', error);
      throw error;
    }
  }

  /**
   * 🆕 OBTENER PRÓXIMOS N SLOTS DISPONIBLES
   *
   * Busca los próximos N slots disponibles comenzando desde hoy,
   * priorizando mismo día y día siguiente.
   *
   * @param count - Cantidad de slots disponibles a retornar (default: 5)
   * @param workingHours - Horario laboral
   * @param slotDuration - Duración de cada slot en minutos
   * @param daysToSearch - Cuántos días hacia adelante buscar (default: 7)
   * @returns Array de TimeSlot disponibles
   */
  public async getNextAvailableSlots(
    count: number = 5,
    workingHours: WorkingHours = { start: '09:00', end: '18:00' },
    slotDuration: number = 60,
    daysToSearch: number = 7
  ): Promise<TimeSlot[]> {
    try {
      const availableSlots: TimeSlot[] = [];
      const today = new Date();

      // Buscar en los próximos N días
      for (let dayOffset = 0; dayOffset < daysToSearch; dayOffset++) {
        if (availableSlots.length >= count) {
          break; // Ya encontramos suficientes slots
        }

        const searchDate = new Date(today);
        searchDate.setDate(searchDate.getDate() + dayOffset);

        // Formatear fecha como "YYYY-MM-DD"
        const dateStr = searchDate.toISOString().split('T')[0];

        // Obtener slots del día
        const daySlots = await this.getAvailableSlots(dateStr, workingHours, slotDuration);

        // Filtrar solo disponibles y que sean en el futuro (si es hoy)
        const now = new Date();
        const futureAvailableSlots = daySlots.filter(slot => {
          if (!slot.available) return false;

          // Si es hoy, solo slots futuros (con 30min de margen)
          if (dayOffset === 0) {
            const slotTime = new Date(slot.startTime);
            const marginMs = 30 * 60 * 1000; // 30 minutos
            return slotTime.getTime() > (now.getTime() + marginMs);
          }

          return true;
        });

        // Agregar slots disponibles
        availableSlots.push(...futureAvailableSlots);
      }

      // Retornar solo los primeros N slots
      const finalSlots = availableSlots.slice(0, count);

      console.log(`🎯 Encontrados ${finalSlots.length} slots disponibles en los próximos ${daysToSearch} días`);

      return finalSlots;
    } catch (error) {
      console.error('❌ Error al buscar próximos slots:', error);
      throw error;
    }
  }

  /**
   * Crear evento con Google Meet
   */
  public async createEventWithMeet(eventData: CalendarEvent): Promise<any> {
    const eventWithMeet = {
      ...eventData,
      conferenceData: {
        createRequest: {
          requestId: `meet-${Date.now()}`,
          conferenceSolutionKey: { type: 'hangoutsMeet' }
        }
      }
    };

    return this.createEvent(eventWithMeet);
  }

  /**
   * Buscar próximos eventos
   */
  public async getUpcomingEvents(maxResults: number = 5): Promise<any[]> {
    const now = new Date();
    const futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + 30); // Próximos 30 días

    return this.listEvents({
      timeMin: now.toISOString(),
      timeMax: futureDate.toISOString(),
      maxResults
    });
  }

  /**
   * Buscar eventos de hoy
   */
  public async getTodayEvents(): Promise<any[]> {
    const now = new Date();
    const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);

    return this.listEvents({
      timeMin: startOfDay.toISOString(),
      timeMax: endOfDay.toISOString()
    });
  }
}

// Exportar instancia única (Singleton)
export default new GoogleCalendarService();
