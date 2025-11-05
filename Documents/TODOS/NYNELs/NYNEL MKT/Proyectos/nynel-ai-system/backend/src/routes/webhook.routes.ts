// ═══════════════════════════════════════════════════════════════════════════
// 🔗 WEBHOOK ROUTES - MANYCHAT
// ═══════════════════════════════════════════════════════════════════════════

import { Router } from 'express';
import { webhookControllerV2 } from '../controllers/webhook.controller.v2.js';
import { verifyManyChatWebhook } from '../middleware/auth.js';

const router = Router();

// Webhook principal de ManyChat (POST)
// USANDO V2: Respuesta directa en webhook (sin Send API, evita restricción 24h)
router.post(
  '/manychat',
  verifyManyChatWebhook, // ✅ Validación de webhook activada
  (req, res) => webhookControllerV2.handleManyChatMessage(req, res)
);

export default router;
