import { Router, Request, Response } from 'express';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { PrismaClient } from '@prisma/client';
import { z } from 'zod';
import { logger } from '#/utils/logger.js';

const router = Router();
const prisma = new PrismaClient();

// Schema de validación para login
const loginSchema = z.object({
  email: z.string().email('Email inválido'),
  password: z.string().min(6, 'La contraseña debe tener al menos 6 caracteres'),
});

// POST /api/auth/login - Autenticar usuario
router.post('/login', async (req: Request, res: Response) => {
  try {
    // Validar datos de entrada
    const { email, password } = loginSchema.parse(req.body);

    // Buscar usuario por email
    const user = await prisma.user.findUnique({
      where: { email },
      select: {
        id: true,
        email: true,
        name: true,
        password: true,
        role: true,
        createdAt: true,
      },
    });

    // Verificar si el usuario existe
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Credenciales inválidas',
      });
    }

    // Verificar contraseña
    const passwordMatch = await bcrypt.compare(password, user.password);

    if (!passwordMatch) {
      return res.status(401).json({
        success: false,
        message: 'Credenciales inválidas',
      });
    }

    // Generar JWT
    const token = jwt.sign(
      {
        userId: user.id,
        email: user.email,
        role: user.role,
      },
      process.env.JWT_SECRET || 'secret_key',
      { expiresIn: '7d' } // Token válido por 7 días
    );

    // Remover password de la respuesta
    const { password: _, ...userWithoutPassword } = user;

    // Retornar token y datos de usuario
    res.json({
      success: true,
      token,
      user: userWithoutPassword,
    });
  } catch (error: any) {
    // Manejar errores de validación de Zod
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos inválidos',
        errors: error.errors,
      });
    }

    logger.error('Error en login:', error);
    res.status(500).json({
      success: false,
      message: 'Error interno del servidor',
    });
  }
});

// GET /api/auth/me - Obtener usuario actual (requiere autenticación)
router.get('/me', async (req: Request, res: Response) => {
  try {
    // Obtener token del header Authorization
    const authHeader = req.headers.authorization;

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Token no proporcionado',
      });
    }

    const token = authHeader.substring(7); // Remover "Bearer "

    // Verificar y decodificar token
    const decoded = jwt.verify(
      token,
      process.env.JWT_SECRET || 'secret_key'
    ) as { userId: string };

    // Buscar usuario
    const user = await prisma.user.findUnique({
      where: { id: decoded.userId },
      select: {
        id: true,
        email: true,
        name: true,
        role: true,
        createdAt: true,
      },
    });

    if (!user) {
      return res.status(404).json({
        success: false,
        message: 'Usuario no encontrado',
      });
    }

    res.json({
      success: true,
      user,
    });
  } catch (error: any) {
    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        success: false,
        message: 'Token inválido',
      });
    }

    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Token expirado',
      });
    }

    logger.error('Error en /me:', error);
    res.status(500).json({
      success: false,
      message: 'Error interno del servidor',
    });
  }
});

export default router;
