// ═══════════════════════════════════════════════════════════════════════════
// 🔐 MIDDLEWARE - AUTENTICACIÓN JWT
// ═══════════════════════════════════════════════════════════════════════════

import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { UnauthorizedError } from './errorHandler.js';

// Extender Request para incluir user
declare global {
  namespace Express {
    interface Request {
      user?: {
        id: string;
        email: string;
        role: string;
      };
    }
  }
}

/**
 * Verifica que el request tenga un JWT válido
 */
export async function authenticateToken(
  req: Request,
  res: Response,
  next: NextFunction
) {
  try {
    const authHeader = req.headers.authorization;

    if (!authHeader) {
      throw new UnauthorizedError('Token de autenticación requerido');
    }

    const token = authHeader.replace('Bearer ', '');

    if (!token) {
      throw new UnauthorizedError('Token de autenticación requerido');
    }

    const jwtSecret = process.env.JWT_SECRET;
    if (!jwtSecret) {
      throw new Error('JWT_SECRET no configurado');
    }

    const decoded = jwt.verify(token, jwtSecret) as {
      id: string;
      email: string;
      role: string;
    };

    req.user = decoded;
    next();
  } catch (error) {
    if (error instanceof jwt.JsonWebTokenError) {
      throw new UnauthorizedError('Token inválido');
    }
    if (error instanceof jwt.TokenExpiredError) {
      throw new UnauthorizedError('Token expirado');
    }
    next(error);
  }
}

/**
 * Verifica que el usuario tenga un rol específico
 */
export function authorizeRole(...allowedRoles: string[]) {
  return async (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.user) {
        throw new UnauthorizedError('Usuario no autenticado');
      }

      if (!allowedRoles.includes(req.user.role)) {
        throw new UnauthorizedError(
          `Rol requerido: ${allowedRoles.join(' o ')}`
        );
      }

      next();
    } catch (error) {
      next(error);
    }
  };
}

/**
 * Verifica el secret de ManyChat en webhooks
 */
export async function verifyManyChatWebhook(
  req: Request,
  res: Response,
  next: NextFunction
) {
  try {
    const webhookSecret = req.headers['x-webhook-secret'];

    if (!webhookSecret) {
      throw new UnauthorizedError('Webhook secret requerido');
    }

    if (webhookSecret !== process.env.MANYCHAT_WEBHOOK_SECRET) {
      throw new UnauthorizedError('Webhook secret inválido');
    }

    next();
  } catch (error) {
    next(error);
  }
}

// Alias para compatibilidad
export const authenticate = authenticateToken;
