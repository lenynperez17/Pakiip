// ===================================
// CALENDAR ROUTES - NYNEL AI SYSTEM
// ===================================
// Rutas REST para Google Calendar API

import { Router } from 'express';
import calendarController from '../controllers/calendar.controller.js';

const router = Router();

// ===================================
// AUTENTICACIÓN OAUTH
// ===================================
// Obtener URL de autorización
router.get('/auth', calendarController.getAuthUrl.bind(calendarController));

// Callback OAuth (Google redirige aquí)
router.get('/auth/callback', calendarController.authCallback.bind(calendarController));

// Verificar estado de autenticación
router.get('/status', calendarController.getStatus.bind(calendarController));

// ===================================
// GESTIÓN DE EVENTOS
// ===================================
// Crear evento
router.post('/events', calendarController.createEvent.bind(calendarController));

// Crear evento con Google Meet
router.post('/events/meet', calendarController.createEventWithMeet.bind(calendarController));

// Listar eventos (con filtros opcionales)
router.get('/events', calendarController.listEvents.bind(calendarController));

// Obtener próximos eventos
router.get('/events/upcoming', calendarController.getUpcomingEvents.bind(calendarController));

// Obtener eventos de hoy
router.get('/events/today', calendarController.getTodayEvents.bind(calendarController));

// Obtener evento específico
router.get('/events/:id', calendarController.getEvent.bind(calendarController));

// Actualizar evento
router.put('/events/:id', calendarController.updateEvent.bind(calendarController));

// Eliminar evento
router.delete('/events/:id', calendarController.deleteEvent.bind(calendarController));

// ===================================
// CANCELACIÓN DESDE MANYCHAT
// ===================================
// Cancelar cita desde ManyChat (usando URL de evento)
router.post('/cancel-appointment', calendarController.cancelAppointment.bind(calendarController));

// ===================================
// DISPONIBILIDAD
// ===================================
// Verificar disponibilidad
router.post('/check-availability', calendarController.checkAvailability.bind(calendarController));

export default router;
