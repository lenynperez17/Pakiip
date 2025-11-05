// ═══════════════════════════════════════════════════════════════════════════
// 💬 MANYCHAT SERVICE - ENVÍO REAL VIA MANYCHAT API
// ═══════════════════════════════════════════════════════════════════════════
// ManyChat maneja TODAS las comunicaciones: WhatsApp, Instagram, Facebook,
// Telegram, Email, SMS, TikTok, Messenger
// ═══════════════════════════════════════════════════════════════════════════

import axios from 'axios';
import { logger } from '#/utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// CONFIGURACIÓN MANYCHAT
// ─────────────────────────────────────────────────────────────────────────────

const MANYCHAT_API_URL = 'https://api.manychat.com/fb/sending/sendContent';
const MANYCHAT_API_TOKEN = process.env.MANYCHAT_API_TOKEN;

// ─────────────────────────────────────────────────────────────────────────────
// INTERFACES
// ─────────────────────────────────────────────────────────────────────────────

export interface SendManyChatMessageOptions {
  subscriberId: string; // ID del suscriptor en ManyChat
  message: string; // Mensaje de texto
  fileUrl?: string; // URL del archivo (PDF, imagen, etc.)
  buttons?: Array<{
    type: 'url' | 'flow' | 'node';
    caption: string;
    url?: string;
    target?: string;
  }>;
}

export interface ManyChatResult {
  sent: boolean;
  subscriberId: string;
  message: string;
  error?: string;
  manyChatResponse?: any;
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIONES PRINCIPALES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Envía un mensaje a través de ManyChat
 * ManyChat se encarga de entregarlo en la plataforma correcta
 * (WhatsApp, Instagram, Facebook, etc.)
 */
export async function sendManyChatMessage(
  options: SendManyChatMessageOptions
): Promise<ManyChatResult> {
  try {
    // Validar configuración
    if (!MANYCHAT_API_TOKEN) {
      logger.error('❌ API Key de ManyChat no configurada');
      return {
        sent: false,
        subscriberId: options.subscriberId,
        message: 'Configuración de ManyChat incompleta',
        error: 'MANYCHAT_API_TOKEN not configured',
      };
    }

    // Construir mensajes para ManyChat
    const messages: any[] = [];

    // Mensaje de texto
    if (options.message) {
      messages.push({
        type: 'text',
        text: options.message,
      });
    }

    // Archivo adjunto (PDF, imagen, etc.)
    if (options.fileUrl) {
      messages.push({
        type: 'file',
        url: options.fileUrl,
      });
    }

    // Botones interactivos
    if (options.buttons && options.buttons.length > 0) {
      messages.push({
        type: 'cards',
        elements: [
          {
            title: '¿Qué deseas hacer?',
            buttons: options.buttons,
          },
        ],
      });
    }

    // Payload para ManyChat API
    const payload = {
      subscriber_id: options.subscriberId,
      data: {
        version: 'v2',
        content: {
          messages,
        },
      },
    };

    logger.info('📤 Enviando a ManyChat:', {
      subscriberId: options.subscriberId,
      messagesCount: messages.length,
    });

    // Enviar request a ManyChat API
    const response = await axios.post(MANYCHAT_API_URL, payload, {
      headers: {
        Authorization: `Bearer ${MANYCHAT_API_TOKEN}`,
        'Content-Type': 'application/json',
      },
      timeout: 15000, // 15 segundos
    });

    logger.info('✅ Mensaje enviado via ManyChat:', response.data);

    return {
      sent: true,
      subscriberId: options.subscriberId,
      message: 'Mensaje enviado exitosamente via ManyChat',
      manyChatResponse: response.data,
    };
  } catch (error) {
    logger.error('❌ Error enviando mensaje via ManyChat:', error);

    if (axios.isAxiosError(error)) {
      const errorMessage = error.response?.data?.message || error.message;
      logger.error('  Detalles del error:', error.response?.data);

      // Log completo del error con JSON.stringify para ver [Object] ocultos
      logger.error('  🔍 Detalles completos del error (JSON):', JSON.stringify(error.response?.data, null, 2));

      // Si hay detalles específicos de validación, mostrarlos
      if (error.response?.data?.details) {
        logger.error('  🔍 Details específicos:', JSON.stringify(error.response.data.details, null, 2));
      }

      return {
        sent: false,
        subscriberId: options.subscriberId,
        message: 'Error al enviar mensaje via ManyChat',
        error: errorMessage,
      };
    }

    return {
      sent: false,
      subscriberId: options.subscriberId,
      message: 'Error al enviar mensaje via ManyChat',
      error: error instanceof Error ? error.message : 'Unknown error',
    };
  }
}

/**
 * Envía una cotización completa via ManyChat
 * ManyChat decide automáticamente por qué canal enviarla
 * (WhatsApp, Instagram, Email, etc. según donde vino el usuario)
 */
export async function sendQuotationViaManyChat(
  subscriberId: string,
  subscriberName: string,
  pdfUrl: string,
  quotationNumber: string,
  totalPrice: number
): Promise<ManyChatResult> {
  const message = `
🎯 *Hola ${subscriberName}*

¡Tu cotización está lista! 📄

*Número:* ${quotationNumber}
*Inversión Total:* S/ ${totalPrice.toLocaleString('es-PE', { minimumFractionDigits: 2 })}

He preparado un documento PDF completo con todos los detalles del proyecto.

Si tienes alguna pregunta o necesitas ajustar algo, ¡estoy aquí para ayudarte! 🚀

_Nynel Mkt - Soluciones Digitales_
  `.trim();

  return sendManyChatMessage({
    subscriberId,
    message,
    fileUrl: pdfUrl,
    buttons: [
      {
        type: 'url',
        caption: '📥 Ver Cotización',
        url: pdfUrl,
      },
      {
        type: 'flow',
        caption: '✅ Aceptar Cotización',
        target: 'accept_quotation_flow', // Flow en ManyChat
      },
      {
        type: 'flow',
        caption: '❓ Hacer Pregunta',
        target: 'ask_question_flow', // Flow en ManyChat
      },
    ],
  });
}

/**
 * Envía un recordatorio de cotización pendiente
 */
export async function sendQuotationReminder(
  subscriberId: string,
  subscriberName: string,
  quotationNumber: string,
  daysAgo: number
): Promise<ManyChatResult> {
  const message = `
👋 *Hola ${subscriberName}*

Te escribo para recordarte que tienes una cotización pendiente de hace ${daysAgo} días.

*Número:* ${quotationNumber}

¿Te gustaría revisarla nuevamente o tienes alguna duda?

Estoy aquí para ayudarte. 😊

_Nynel Mkt_
  `.trim();

  return sendManyChatMessage({
    subscriberId,
    message,
    buttons: [
      {
        type: 'flow',
        caption: '📄 Ver Cotización',
        target: 'view_quotation_flow',
      },
      {
        type: 'flow',
        caption: '💬 Hablar con Asesor',
        target: 'talk_to_advisor_flow',
      },
    ],
  });
}

/**
 * Envía una notificación de bienvenida al nuevo lead
 */
export async function sendWelcomeMessage(
  subscriberId: string,
  subscriberName: string
): Promise<ManyChatResult> {
  const message = `
🎉 *¡Hola ${subscriberName}! Bienvenido a Nynel Mkt*

Soy tu asistente de IA especializado en soluciones digitales.

Puedo ayudarte con:
✅ Desarrollo web y móvil
✅ Marketing digital
✅ Diseño gráfico
✅ Consultoría tecnológica

¿En qué proyecto estás pensando? 🚀
  `.trim();

  return sendManyChatMessage({
    subscriberId,
    message,
  });
}

/**
 * Obtiene información de un suscriptor desde ManyChat
 */
export async function getManyChatSubscriber(subscriberId: string): Promise<any> {
  try {
    if (!MANYCHAT_API_TOKEN) {
      throw new Error('MANYCHAT_API_TOKEN not configured');
    }

    const response = await axios.get(
      `https://api.manychat.com/fb/subscriber/getInfo?subscriber_id=${subscriberId}`,
      {
        headers: {
          Authorization: `Bearer ${MANYCHAT_API_TOKEN}`,
        },
        timeout: 10000,
      }
    );

    logger.info('✅ Info de suscriptor obtenida:', response.data);
    return response.data;
  } catch (error) {
    logger.error('❌ Error obteniendo info de suscriptor:', error);
    throw error;
  }
}

/**
 * Actualiza campos personalizados de un suscriptor en ManyChat
 */
export async function updateManyChatSubscriberFields(
  subscriberId: string,
  fields: Record<string, any>
): Promise<boolean> {
  try {
    if (!MANYCHAT_API_TOKEN) {
      throw new Error('MANYCHAT_API_TOKEN not configured');
    }

    const response = await axios.post(
      'https://api.manychat.com/fb/subscriber/setCustomField',
      {
        subscriber_id: subscriberId,
        fields,
      },
      {
        headers: {
          Authorization: `Bearer ${MANYCHAT_API_TOKEN}`,
          'Content-Type': 'application/json',
        },
        timeout: 10000,
      }
    );

    logger.info('✅ Campos de suscriptor actualizados:', response.data);
    return true;
  } catch (error) {
    logger.error('❌ Error actualizando campos de suscriptor:', error);
    return false;
  }
}
