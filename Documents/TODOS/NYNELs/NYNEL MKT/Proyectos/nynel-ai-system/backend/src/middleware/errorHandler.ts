// ═══════════════════════════════════════════════════════════════════════════
// ⚠️ MIDDLEWARE - ERROR HANDLER GLOBAL
// ═══════════════════════════════════════════════════════════════════════════

import { Request, Response, NextFunction } from 'express';
import { Prisma } from '@prisma/client';
import { logger } from '#/utils/logger.js';

// Tipos de errores personalizados
export class AppError extends Error {
  constructor(
    public statusCode: number,
    public message: string,
    public isOperational = true
  ) {
    super(message);
    Object.setPrototypeOf(this, AppError.prototype);
  }
}

export class ValidationError extends AppError {
  constructor(message: string) {
    super(400, message);
  }
}

export class UnauthorizedError extends AppError {
  constructor(message: string = 'No autorizado') {
    super(401, message);
  }
}

export class ForbiddenError extends AppError {
  constructor(message: string = 'Acceso prohibido') {
    super(403, message);
  }
}

export class NotFoundError extends AppError {
  constructor(message: string = 'Recurso no encontrado') {
    super(404, message);
  }
}

// Handler principal de errores
export function errorHandler(
  error: Error,
  req: Request,
  res: Response,
  next: NextFunction
) {
  logger.error('Error capturado:', error);

  // Error operacional conocido
  if (error instanceof AppError) {
    return res.status(error.statusCode).json({
      success: false,
      error: {
        message: error.message,
        statusCode: error.statusCode,
      },
    });
  }

  // Error de validación de Prisma
  if (error instanceof Prisma.PrismaClientValidationError) {
    return res.status(400).json({
      success: false,
      error: {
        message: 'Error de validación en la base de datos',
        statusCode: 400,
      },
    });
  }

  // Error de registro duplicado
  if (error instanceof Prisma.PrismaClientKnownRequestError) {
    if (error.code === 'P2002') {
      return res.status(409).json({
        success: false,
        error: {
          message: 'Ya existe un registro con estos datos',
          statusCode: 409,
        },
      });
    }

    if (error.code === 'P2025') {
      return res.status(404).json({
        success: false,
        error: {
          message: 'Registro no encontrado',
          statusCode: 404,
        },
      });
    }
  }

  // Error desconocido - no exponer detalles en producción
  const isDevelopment = process.env.NODE_ENV === 'development';

  return res.status(500).json({
    success: false,
    error: {
      message: isDevelopment
        ? error.message
        : 'Ha ocurrido un error interno del servidor',
      statusCode: 500,
      ...(isDevelopment && { stack: error.stack }),
    },
  });
}

// Middleware para rutas no encontradas
export function notFoundHandler(req: Request, res: Response) {
  res.status(404).json({
    success: false,
    error: {
      message: `Ruta no encontrada: ${req.method} ${req.path}`,
      statusCode: 404,
    },
  });
}
