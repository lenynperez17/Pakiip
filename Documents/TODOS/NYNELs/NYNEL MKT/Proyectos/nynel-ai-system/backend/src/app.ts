// ═══════════════════════════════════════════════════════════════════════════
// 🚀 EXPRESS APP - CONFIGURACIÓN PRINCIPAL
// ═══════════════════════════════════════════════════════════════════════════

import express, { Application } from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import path from 'path';
import { errorHandler, notFoundHandler } from './middleware/errorHandler.js';
import { logger } from './utils/logger.js';

// Importar routes (Node.js ESM requiere extensión .js explícita)
import apiRoutes from './routes/index.js';

// ─────────────────────────────────────────────────────────────────────────────
// Crear aplicación Express
// ─────────────────────────────────────────────────────────────────────────────

export function createApp(): Application {
  const app = express();

  // Confiar en proxy reverso (nginx)
  // Esto permite que Express confíe en los headers X-Forwarded-* enviados por nginx
  app.set('trust proxy', 1);

  // ─────────────────────────────────────────────────────────────────────────
  // MIDDLEWARE DE SEGURIDAD
  // ─────────────────────────────────────────────────────────────────────────

  // Helmet - seguridad HTTP headers
  app.use(helmet());

  // CORS - Permitir múltiples orígenes (desarrollo)
  const allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:3002',
    'http://localhost:7001',
    process.env.CORS_ORIGIN,
  ].filter(Boolean); // Eliminar valores undefined

  app.use(cors({
    origin: (origin, callback) => {
      // Permitir peticiones sin origin (por ejemplo, Postman, curl)
      if (!origin) return callback(null, true);

      if (allowedOrigins.includes(origin)) {
        callback(null, true);
      } else {
        callback(new Error('Not allowed by CORS'));
      }
    },
    credentials: true,
  }));

  // Rate limiting con keyGenerator personalizado para manejar proxies
  const limiter = rateLimit({
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS || '900000'), // 15 minutos
    max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS || '100'),
    message: {
      success: false,
      error: {
        message: 'Demasiadas peticiones, por favor intenta más tarde',
        statusCode: 429,
      },
    },
    standardHeaders: true,
    legacyHeaders: false,
    // Key generator personalizado para manejar IPs del proxy
    keyGenerator: (req) => {
      // Intentar obtener IP del header X-Forwarded-For (nginx)
      const forwarded = req.headers['x-forwarded-for'];
      if (forwarded) {
        const ips = Array.isArray(forwarded) ? forwarded[0] : forwarded;
        return ips.split(',')[0].trim();
      }
      // Fallback a req.ip si no hay X-Forwarded-For
      return req.ip || 'unknown';
    },
    // Desactivar TODAS las validaciones porque usamos keyGenerator personalizado
    validate: {
      ip: false, // Desactivar validación estricta de IP
      trustProxy: false, // Desactivar validación de trust proxy
      xForwardedForHeader: false, // Desactivar validación de X-Forwarded-For
    },
  });

  app.use('/api/', limiter);

  // ─────────────────────────────────────────────────────────────────────────
  // MIDDLEWARE DE PARSEO
  // ─────────────────────────────────────────────────────────────────────────

  app.use(express.json({ limit: '10mb' }));
  app.use(express.urlencoded({ extended: true, limit: '10mb' }));

  // ─────────────────────────────────────────────────────────────────────────
  // ARCHIVOS ESTÁTICOS - PDFs DE COTIZACIONES
  // ─────────────────────────────────────────────────────────────────────────

  const publicDir = path.join(process.cwd(), 'public');
  app.use('/quotations', express.static(path.join(publicDir, 'quotations')));
  logger.info(`📁 Serviendo archivos estáticos desde: ${publicDir}/quotations`);

  // ─────────────────────────────────────────────────────────────────────────
  // LOGGING
  // ─────────────────────────────────────────────────────────────────────────

  if (process.env.NODE_ENV === 'development') {
    app.use(morgan('dev'));
  } else {
    app.use(morgan('combined', {
      stream: {
        write: (message) => logger.info(message.trim()),
      },
    }));
  }

  // ─────────────────────────────────────────────────────────────────────────
  // HEALTH CHECK CON VERIFICACIÓN REAL (PRODUCTION-READY)
  // ─────────────────────────────────────────────────────────────────────────
  // ✅ Verifica conexión a BD (Prisma)
  // ✅ Verifica conexión a Redis
  // ✅ Retorna status detallado de cada servicio
  // ─────────────────────────────────────────────────────────────────────────

  app.get('/health', async (req, res) => {
    const healthCheck = {
      status: 'healthy' as 'healthy' | 'degraded' | 'unhealthy',
      timestamp: new Date().toISOString(),
      uptime: process.uptime(),
      environment: process.env.NODE_ENV,
      version: process.env.API_VERSION || 'v1',
      services: {
        database: { status: 'unknown' as 'up' | 'down' | 'unknown', responseTime: 0 },
        redis: { status: 'unknown' as 'up' | 'down' | 'unknown', responseTime: 0 },
      },
    };

    try {
      // ═══════════════════════════════════════════════════════════════
      // 🗄️  VERIFICAR CONEXIÓN A BASE DE DATOS
      // ═══════════════════════════════════════════════════════════════
      const dbStart = Date.now();
      try {
        const { prisma } = await import('./config/database');
        await prisma.$queryRaw`SELECT 1`; // Query simple para verificar conexión
        healthCheck.services.database.status = 'up';
        healthCheck.services.database.responseTime = Date.now() - dbStart;
      } catch (dbError: any) {
        logger.error('❌ Health check: Database DOWN:', dbError);
        healthCheck.services.database.status = 'down';
        healthCheck.status = 'unhealthy';
      }

      // ═══════════════════════════════════════════════════════════════
      // 🔴 VERIFICAR CONEXIÓN A REDIS
      // ═══════════════════════════════════════════════════════════════
      const redisStart = Date.now();
      try {
        const { redis } = await import('./config/redis');
        await redis.ping(); // Ping simple para verificar conexión
        healthCheck.services.redis.status = 'up';
        healthCheck.services.redis.responseTime = Date.now() - redisStart;
      } catch (redisError: any) {
        logger.error('❌ Health check: Redis DOWN:', redisError);
        healthCheck.services.redis.status = 'down';
        healthCheck.status = healthCheck.status === 'unhealthy' ? 'unhealthy' : 'degraded';
      }

      // Determinar status HTTP basado en health
      const httpStatus = healthCheck.status === 'healthy' ? 200 : healthCheck.status === 'degraded' ? 503 : 503;

      res.status(httpStatus).json({
        success: healthCheck.status === 'healthy',
        data: healthCheck,
      });
    } catch (error: any) {
      logger.error('❌ Health check: Error general:', error);
      res.status(503).json({
        success: false,
        data: {
          ...healthCheck,
          status: 'unhealthy',
          error: error.message,
        },
      });
    }
  });

  // ─────────────────────────────────────────────────────────────────────────
  // API ROUTES
  // ─────────────────────────────────────────────────────────────────────────

  app.use('/api', apiRoutes);

  // ─────────────────────────────────────────────────────────────────────────
  // ERROR HANDLERS
  // ─────────────────────────────────────────────────────────────────────────

  app.use(notFoundHandler);
  app.use(errorHandler);

  return app;
}
