// ═══════════════════════════════════════════════════════════════════════════
// 📨 MESSAGE QUEUE SERVICE - COLA DE MENSAJES CON REDIS
// ═══════════════════════════════════════════════════════════════════════════

import { redis } from '#/config/redis.js';
import { logger } from '#/utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// Interfaces
// ─────────────────────────────────────────────────────────────────────────────

interface QueuedMessage {
  message: string;
  timestamp: number;
  payload: any;
}

// ─────────────────────────────────────────────────────────────────────────────
// Message Queue Service Class
// ─────────────────────────────────────────────────────────────────────────────

export class MessageQueueService {
  private readonly KEY_PREFIX = 'manychat:messages';
  private readonly TTL_SECONDS = 60; // Auto-limpieza después de 1 minuto

  /**
   * Construir clave de Redis para un subscriber
   */
  private getKey(subscriberId: string): string {
    return `${this.KEY_PREFIX}:${subscriberId}`;
  }

  /**
   * Agregar mensaje a la cola de un subscriber
   */
  async addMessage(
    subscriberId: string,
    message: string,
    payload: any
  ): Promise<void> {
    try {
      const key = this.getKey(subscriberId);
      const queuedMessage: QueuedMessage = {
        message,
        timestamp: Date.now(),
        payload,
      };

      // Obtener mensajes existentes
      const existingData = await redis.get(key);
      const messages: QueuedMessage[] = existingData
        ? JSON.parse(existingData)
        : [];

      // Agregar nuevo mensaje
      messages.push(queuedMessage);

      // Guardar en Redis con TTL
      await redis.setex(key, this.TTL_SECONDS, JSON.stringify(messages));

      logger.info(
        `✅ Mensaje agregado a cola para ${subscriberId} (total: ${messages.length})`
      );
    } catch (error) {
      logger.error(
        `❌ Error al agregar mensaje a cola para ${subscriberId}:`,
        error
      );
      throw error;
    }
  }

  /**
   * Obtener todos los mensajes pendientes de un subscriber
   */
  async getMessages(subscriberId: string): Promise<QueuedMessage[]> {
    try {
      const key = this.getKey(subscriberId);
      const data = await redis.get(key);

      if (!data) {
        return [];
      }

      const messages: QueuedMessage[] = JSON.parse(data);
      logger.info(`📥 Recuperados ${messages.length} mensajes para ${subscriberId}`);
      return messages;
    } catch (error) {
      logger.error(
        `❌ Error al obtener mensajes para ${subscriberId}:`,
        error
      );
      return [];
    }
  }

  /**
   * Limpiar mensajes de un subscriber después de procesarlos
   */
  async clearMessages(subscriberId: string): Promise<void> {
    try {
      const key = this.getKey(subscriberId);
      await redis.del(key);
      logger.info(`🗑️  Cola limpiada para ${subscriberId}`);
    } catch (error) {
      logger.error(
        `❌ Error al limpiar cola para ${subscriberId}:`,
        error
      );
    }
  }

  /**
   * Obtener todos los subscribers con mensajes pendientes
   */
  async getAllSubscribersWithMessages(): Promise<string[]> {
    try {
      const pattern = `${this.KEY_PREFIX}:*`;
      const keys = await redis.keys(pattern);

      // Extraer subscriber IDs de las claves
      const subscriberIds = keys.map((key) =>
        key.replace(`${this.KEY_PREFIX}:`, '')
      );

      return subscriberIds;
    } catch (error) {
      logger.error('❌ Error al obtener subscribers con mensajes:', error);
      return [];
    }
  }

  /**
   * Obtener tiempo transcurrido desde el último mensaje (en ms)
   */
  async getTimeSinceLastMessage(subscriberId: string): Promise<number> {
    try {
      const messages = await this.getMessages(subscriberId);

      if (messages.length === 0) {
        return Infinity; // No hay mensajes
      }

      // Obtener timestamp del último mensaje
      const lastMessage = messages[messages.length - 1];
      const timeSince = Date.now() - lastMessage.timestamp;

      return timeSince;
    } catch (error) {
      logger.error(
        `❌ Error al calcular tiempo desde último mensaje para ${subscriberId}:`,
        error
      );
      return Infinity;
    }
  }

  /**
   * Verificar si un subscriber tiene mensajes pendientes
   */
  async hasMessages(subscriberId: string): Promise<boolean> {
    try {
      const key = this.getKey(subscriberId);
      const exists = await redis.exists(key);
      return exists === 1;
    } catch (error) {
      logger.error(
        `❌ Error al verificar mensajes para ${subscriberId}:`,
        error
      );
      return false;
    }
  }

  /**
   * Obtener cantidad de mensajes en cola
   */
  async getMessageCount(subscriberId: string): Promise<number> {
    try {
      const messages = await this.getMessages(subscriberId);
      return messages.length;
    } catch (error) {
      logger.error(
        `❌ Error al contar mensajes para ${subscriberId}:`,
        error
      );
      return 0;
    }
  }

  /**
   * Health check del servicio
   */
  async healthCheck(): Promise<boolean> {
    try {
      await redis.ping();
      return true;
    } catch (error) {
      logger.error('❌ Message Queue Service no disponible:', error);
      return false;
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Singleton Instance
// ─────────────────────────────────────────────────────────────────────────────

export const messageQueue = new MessageQueueService();
