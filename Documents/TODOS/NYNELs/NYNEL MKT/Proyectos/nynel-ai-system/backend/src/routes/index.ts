// ═══════════════════════════════════════════════════════════════════════════
// 🛣️ ROUTES - INDEX PRINCIPAL
// ═══════════════════════════════════════════════════════════════════════════

import { Router } from 'express';
import webhookRoutes from './webhook.routes.js';
import conversationRoutes from './conversation.routes.js';
import quotationRoutes from './quotation.routes.js';
import subscriberRoutes from './subscriber.routes.js';
import authRoutes from './auth.routes.js';
import analyticsRoutes from './analytics.routes.js';
import calendarRoutes from './calendar.routes.js';

const router = Router();

// ─────────────────────────────────────────────────────────────────────────────
// INFO DE LA API
// ─────────────────────────────────────────────────────────────────────────────

router.get('/', (req, res) => {
  res.json({
    success: true,
    data: {
      name: 'Nynel AI System API',
      version: process.env.API_VERSION || 'v1',
      description: 'Sistema Enterprise de IA Multi-Agente para Nynel Mkt',
      documentation: '/api/docs',
      endpoints: {
        webhook: '/api/webhook',
        conversations: '/api/conversations',
        quotations: '/api/quotations',
        subscribers: '/api/subscribers',
        auth: '/api/auth',
        analytics: '/api/analytics',
        calendar: '/api/calendar',
      },
    },
  });
});

// ─────────────────────────────────────────────────────────────────────────────
// ROUTES PÚBLICAS
// ─────────────────────────────────────────────────────────────────────────────

router.use('/webhook', webhookRoutes);
router.use('/auth', authRoutes);
router.use('/calendar', calendarRoutes); // Google Calendar (OAuth callback debe ser público)

// ─────────────────────────────────────────────────────────────────────────────
// ROUTES PROTEGIDAS (requieren autenticación)
// ─────────────────────────────────────────────────────────────────────────────

router.use('/conversations', conversationRoutes);
router.use('/quotations', quotationRoutes);
router.use('/subscribers', subscriberRoutes);
router.use('/analytics', analyticsRoutes);

export default router;
