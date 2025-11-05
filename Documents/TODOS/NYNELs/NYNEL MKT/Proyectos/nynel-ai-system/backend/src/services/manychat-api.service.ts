// ═══════════════════════════════════════════════════════════════════════════
// 🤖 MANYCHAT API SERVICE - ENVÍO DE MENSAJES PROGRAMÁTICO
// ═══════════════════════════════════════════════════════════════════════════

import axios, { AxiosInstance, AxiosError } from 'axios';
import pRetry from 'p-retry';
import { logger } from '#/utils/logger.js';

// ═══════════════════════════════════════════════════════════════════════════
// 🔄 RETRY CONFIGURATION (PRODUCTION-READY)
// ═══════════════════════════════════════════════════════════════════════════
// VENTAJAS:
// ✅ Previene pérdida de mensajes por fallos temporales de red
// ✅ Backoff exponencial - evita saturar el servidor
// ✅ Retry selectivo - solo reintenta errores recuperables (5xx, network)
// ✅ NO reintenta errores de cliente (4xx) que nunca tendrán éxito
// ═══════════════════════════════════════════════════════════════════════════

const RETRY_OPTIONS = {
  retries: 3, // Máximo 3 reintentos
  factor: 2, // Backoff exponencial: 1s, 2s, 4s
  minTimeout: 1000, // Primer reintento después de 1 segundo
  maxTimeout: 5000, // Timeout máximo entre reintentos
  onFailedAttempt: (error: any) => {
    logger.warn(`⚠️  ManyChat API reintento ${error.attemptNumber}/${error.retriesLeft + error.attemptNumber}:`, {
      message: error.message,
      retriesLeft: error.retriesLeft,
    });
  },
};

/**
 * Helper: Determina si un error de axios debe reintentar
 */
function shouldRetry(error: AxiosError): boolean {
  // Network errors (sin response) - SIEMPRE REINTENTAR
  if (!error.response) {
    return true;
  }

  const status = error.response.status;

  // 5xx (server errors) - REINTENTAR
  if (status >= 500 && status < 600) {
    return true;
  }

  // 429 (rate limit) - REINTENTAR
  if (status === 429) {
    return true;
  }

  // 408 (request timeout) - REINTENTAR
  if (status === 408) {
    return true;
  }

  // 4xx (client errors) - NO REINTENTAR (error del cliente, no se arreglará solo)
  if (status >= 400 && status < 500) {
    return false;
  }

  // Otros casos - NO REINTENTAR
  return false;
}

// ─────────────────────────────────────────────────────────────────────────────
// Interfaces
// ─────────────────────────────────────────────────────────────────────────────

interface ManyChatMessage {
  type: 'text';
  text: string;
}

interface SendMessagePayload {
  subscriber_id: string;
  data: {
    version: string;
    content: {
      messages: ManyChatMessage[];
    };
  };
}

interface SendMessageResponse {
  status: string;
  data?: any;
}

// ─────────────────────────────────────────────────────────────────────────────
// ManyChat API Service Class
// ─────────────────────────────────────────────────────────────────────────────

export class ManyChatAPIService {
  private client: AxiosInstance;
  private apiToken: string;

  constructor() {
    this.apiToken = process.env.MANYCHAT_API_TOKEN || '';

    if (!this.apiToken) {
      logger.error('❌ MANYCHAT_API_TOKEN no está configurado en .env');
      throw new Error('MANYCHAT_API_TOKEN es requerido');
    }

    this.client = axios.create({
      baseURL: 'https://api.manychat.com/fb',
      headers: {
        'Authorization': `Bearer ${this.apiToken}`,
        'Content-Type': 'application/json',
      },
      timeout: 10000, // 10 segundos timeout
    });

    logger.info('✅ ManyChat API Service inicializado');
  }

  /**
   * Enviar mensaje de texto a un subscriber (CON RETRY LOGIC)
   */
  async sendTextMessage(subscriberId: string, text: string): Promise<boolean> {
    try {
      logger.info(`📤 Enviando mensaje a ${subscriberId} vía ManyChat API`);

      // ═══════════════════════════════════════════════════════════════
      // 🔄 ENVOLVER LLAMADA CON P-RETRY
      // ═══════════════════════════════════════════════════════════════
      const response = await pRetry(
        async () => {
          const payload: SendMessagePayload = {
            subscriber_id: subscriberId,
            data: {
              version: 'v2',
              content: {
                messages: [
                  {
                    type: 'text',
                    text: text,
                  },
                ],
              },
            },
          };

          return await this.client.post<SendMessageResponse>(
            '/sending/sendContent',
            payload
          );
        },
        {
          ...RETRY_OPTIONS,
          shouldRetry: (error) => {
            // Verificar si es un AxiosError y si debe reintentar
            if (axios.isAxiosError(error)) {
              return shouldRetry(error);
            }
            // Otros errores - no reintentar
            return false;
          },
        }
      );

      if (response.data.status === 'success') {
        logger.info(`✅ Mensaje enviado exitosamente a ${subscriberId}`);
        return true;
      } else {
        logger.error(`❌ Error al enviar mensaje a ${subscriberId}:`, response.data);
        return false;
      }
    } catch (error: any) {
      logger.error(`❌ Error al llamar ManyChat API para ${subscriberId} (después de reintentos):`, {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
      });
      return false;
    }
  }

  /**
   * Enviar múltiples mensajes de texto a un subscriber
   */
  async sendMultipleTextMessages(subscriberId: string, texts: string[]): Promise<boolean> {
    try {
      logger.info(`📤 Enviando ${texts.length} mensajes a ${subscriberId} vía ManyChat API`);

      const payload: SendMessagePayload = {
        subscriber_id: subscriberId,
        data: {
          version: 'v2',
          content: {
            messages: texts.map((text) => ({
              type: 'text' as const,
              text,
            })),
          },
        },
      };

      const response = await this.client.post<SendMessageResponse>(
        '/sending/sendContent',
        payload
      );

      if (response.data.status === 'success') {
        logger.info(`✅ ${texts.length} mensajes enviados exitosamente a ${subscriberId}`);
        return true;
      } else {
        logger.error(`❌ Error al enviar mensajes a ${subscriberId}:`, response.data);
        return false;
      }
    } catch (error: any) {
      logger.error(`❌ Error al llamar ManyChat API para ${subscriberId}:`, {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
      });
      return false;
    }
  }

  /**
   * Establecer un Custom Field para un subscriber (CON RETRY LOGIC)
   */
  async setCustomField(subscriberId: string, fieldName: string, value: string): Promise<boolean> {
    try {
      logger.info(`📝 Estableciendo custom field '${fieldName}' para ${subscriberId}`);

      // ═══════════════════════════════════════════════════════════════
      // 🔄 ENVOLVER LLAMADA CON P-RETRY
      // ═══════════════════════════════════════════════════════════════
      const response = await pRetry(
        async () => {
          const payload = {
            subscriber_id: subscriberId,
            field_name: fieldName,
            field_value: value,
          };

          return await this.client.post(
            '/subscriber/setCustomField',
            payload
          );
        },
        {
          ...RETRY_OPTIONS,
          shouldRetry: (error) => {
            if (axios.isAxiosError(error)) {
              return shouldRetry(error);
            }
            return false;
          },
        }
      );

      if (response.data.status === 'success') {
        logger.info(`✅ Custom field '${fieldName}' establecido exitosamente`);
        return true;
      } else {
        logger.error(`❌ Error al establecer custom field:`, response.data);
        return false;
      }
    } catch (error: any) {
      logger.error(`❌ Error al llamar setCustomField (después de reintentos):`, {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
      });
      return false;
    }
  }

  /**
   * Activar un Flow para un subscriber con Custom Field Set (CON RETRY LOGIC)
   */
  async sendFlow(subscriberId: string, flowNs: string, customFieldValue?: string): Promise<boolean> {
    try {
      logger.info(`🔄 Activando flow '${flowNs}' para ${subscriberId}`);

      // Si hay un valor de custom field, primero establecerlo usando setCustomFieldByName
      if (customFieldValue) {
        const fieldSet = await this.setCustomFieldByName(subscriberId, 'ai_response', customFieldValue);
        if (!fieldSet) {
          logger.warn('⚠️  Custom field no establecido, pero continuando con Flow');
        }
      }

      // ═══════════════════════════════════════════════════════════════
      // 🔄 ENVOLVER LLAMADA CON P-RETRY
      // ═══════════════════════════════════════════════════════════════
      const response = await pRetry(
        async () => {
          const payload = {
            subscriber_id: subscriberId,
            flow_ns: flowNs,
          };

          return await this.client.post(
            '/sending/sendFlow',
            payload
          );
        },
        {
          ...RETRY_OPTIONS,
          shouldRetry: (error) => {
            if (axios.isAxiosError(error)) {
              return shouldRetry(error);
            }
            return false;
          },
        }
      );

      if (response.data.status === 'success') {
        logger.info(`✅ Flow '${flowNs}' activado exitosamente`);
        return true;
      } else {
        logger.error(`❌ Error al activar flow:`, response.data);
        return false;
      }
    } catch (error: any) {
      logger.error(`❌ Error al llamar sendFlow (después de reintentos):`, {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
      });
      return false;
    }
  }

  /**
   * Establecer Custom Field usando setCustomFieldByName (alternativa)
   */
  async setCustomFieldByName(subscriberId: string, fieldName: string, value: string): Promise<boolean> {
    try {
      logger.info(`📝 Estableciendo custom field '${fieldName}' (método alternativo)`);

      const payload = {
        subscriber_id: subscriberId,
        field_name: fieldName,
        field_value: value,
      };

      const response = await this.client.post(
        '/subscriber/setCustomFieldByName',
        payload
      );

      if (response.data.status === 'success') {
        logger.info(`✅ Custom field '${fieldName}' establecido exitosamente`);
        return true;
      } else {
        logger.error(`❌ Error al establecer custom field:`, response.data);
        return false;
      }
    } catch (error: any) {
      logger.error(`❌ Error al llamar setCustomFieldByName:`, {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
      });
      return false;
    }
  }

  /**
   * Verificar si el servicio está disponible
   */
  async healthCheck(): Promise<boolean> {
    try {
      // ManyChat no tiene endpoint de health, intentamos con timeout corto
      return true;
    } catch (error) {
      logger.error('❌ ManyChat API Service no disponible:', error);
      return false;
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Singleton Instance
// ─────────────────────────────────────────────────────────────────────────────

export const manyChatAPI = new ManyChatAPIService();
