// ═══════════════════════════════════════════════════════════════════════════
// 🔴 CONFIGURACIÓN DE REDIS - CACHÉ Y COLAS
// ═══════════════════════════════════════════════════════════════════════════

import Redis from 'ioredis';
import { logger } from '#/utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// Cliente Redis para caché general
// ─────────────────────────────────────────────────────────────────────────────

const redisConfig = {
  host: process.env.REDIS_HOST || 'localhost',
  port: parseInt(process.env.REDIS_PORT || '6379'),
  password: process.env.REDIS_PASSWORD || undefined,
  db: parseInt(process.env.REDIS_DB || '0'),
  retryStrategy: (times: number) => {
    const delay = Math.min(times * 50, 2000);
    return delay;
  },
  maxRetriesPerRequest: 3,
};

export const redis = new Redis(redisConfig);

// ─────────────────────────────────────────────────────────────────────────────
// Cliente Redis para Bull (job queues)
// ─────────────────────────────────────────────────────────────────────────────

export const bullRedis = new Redis({
  ...redisConfig,
  db: 1, // Usamos una DB diferente para las colas
});

// ─────────────────────────────────────────────────────────────────────────────
// Eventos de conexión
// ─────────────────────────────────────────────────────────────────────────────

redis.on('connect', () => {
  logger.info('🔴 Redis: Conectando...');
});

redis.on('ready', () => {
  logger.info('✅ Redis: Conexión establecida y lista');
});

redis.on('error', (error) => {
  logger.error('❌ Redis Error:', error);
});

redis.on('close', () => {
  logger.warn('🔴 Redis: Conexión cerrada');
});

redis.on('reconnecting', () => {
  logger.info('🔄 Redis: Reconectando...');
});

// ─────────────────────────────────────────────────────────────────────────────
// Utilidades de caché
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Guarda un valor en caché con TTL opcional
 */
export async function setCache(
  key: string,
  value: any,
  ttlSeconds?: number
): Promise<void> {
  try {
    const stringValue = JSON.stringify(value);

    if (ttlSeconds) {
      await redis.setex(key, ttlSeconds, stringValue);
    } else {
      await redis.set(key, stringValue);
    }

    logger.info(`✅ Caché guardado: ${key} (TTL: ${ttlSeconds || 'sin expiración'}s)`);
  } catch (error) {
    logger.error(`❌ Error CRÍTICO al guardar en caché ${key}:`, error);
    throw error; // ✅ Lanzar excepción para detener el flujo si Redis falla
  }
}

/**
 * Obtiene un valor del caché
 */
export async function getCache<T = any>(key: string): Promise<T | null> {
  try {
    const value = await redis.get(key);

    if (!value) {
      return null;
    }

    return JSON.parse(value) as T;
  } catch (error) {
    logger.error(`❌ Error al leer caché ${key}:`, error);
    return null;
  }
}

/**
 * Elimina una o varias claves del caché
 */
export async function deleteCache(keys: string | string[]): Promise<void> {
  try {
    const keysArray = Array.isArray(keys) ? keys : [keys];
    await redis.del(...keysArray);
    logger.debug(`✅ Caché eliminado: ${keysArray.join(', ')}`);
  } catch (error) {
    logger.error('❌ Error al eliminar caché:', error);
  }
}

/**
 * Elimina todas las claves que coincidan con un patrón
 */
export async function deleteCachePattern(pattern: string): Promise<void> {
  try {
    const keys = await redis.keys(pattern);

    if (keys.length > 0) {
      await redis.del(...keys);
      logger.debug(`✅ ${keys.length} claves eliminadas con patrón: ${pattern}`);
    }
  } catch (error) {
    logger.error(`❌ Error al eliminar caché con patrón ${pattern}:`, error);
  }
}

/**
 * Verifica si una clave existe en caché
 */
export async function existsCache(key: string): Promise<boolean> {
  try {
    const exists = await redis.exists(key);
    return exists === 1;
  } catch (error) {
    logger.error(`❌ Error al verificar caché ${key}:`, error);
    return false;
  }
}

/**
 * Obtiene el TTL restante de una clave
 */
export async function getTTL(key: string): Promise<number> {
  try {
    return await redis.ttl(key);
  } catch (error) {
    logger.error(`❌ Error al obtener TTL de ${key}:`, error);
    return -1;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Graceful shutdown
// ─────────────────────────────────────────────────────────────────────────────

async function disconnectRedis() {
  try {
    await redis.quit();
    await bullRedis.quit();
    logger.info('✅ Redis: Desconexión exitosa');
  } catch (error) {
    logger.error('❌ Redis: Error al desconectar:', error);
  }
}

process.on('SIGINT', async () => {
  await disconnectRedis();
});

process.on('SIGTERM', async () => {
  await disconnectRedis();
});
