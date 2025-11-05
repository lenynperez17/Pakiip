// ═══════════════════════════════════════════════════════════════════════════
// 📋 SUBSCRIBER ROUTES - CRUD COMPLETO
// ═══════════════════════════════════════════════════════════════════════════

import { Router, Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { z } from 'zod';
import { authenticate } from '../middleware/auth.js';
import { logger } from '#/utils/logger.js';

const router = Router();
const prisma = new PrismaClient();

// Schema de validación para crear/actualizar subscriber
const subscriberSchema = z.object({
  subscriberId: z.string().min(1, 'El ID de ManyChat es requerido'),
  platform: z.enum(['INSTAGRAM', 'WHATSAPP', 'FACEBOOK', 'TELEGRAM', 'EMAIL', 'WEB']),
  firstName: z.string().optional(),
  lastName: z.string().optional(),
  phone: z.string().optional(),
  email: z.string().email('Email inválido').optional(),
  tags: z.array(z.string()).optional(),
  customFields: z.record(z.any()).optional(),
  leadStatus: z.enum(['NEW', 'CONTACTED', 'QUALIFIED', 'PROPOSAL_SENT', 'NEGOTIATION', 'WON', 'LOST', 'INACTIVE']).optional(),
  priority: z.enum(['LOW', 'NORMAL', 'HIGH', 'URGENT']).optional(),
  leadScore: z.number().optional(),
});

// GET /api/subscribers - Listar todos los subscribers (con paginación y búsqueda)
router.get('/', authenticate, async (req: Request, res: Response) => {
  try {
    const page = parseInt(req.query.page as string) || 1;
    const limit = parseInt(req.query.limit as string) || 10;
    const search = (req.query.search as string) || '';
    const status = req.query.status as string;

    const skip = (page - 1) * limit;

    // Construir filtros
    const where: any = {};

    if (search) {
      where.OR = [
        { firstName: { contains: search, mode: 'insensitive' } },
        { lastName: { contains: search, mode: 'insensitive' } },
        { email: { contains: search, mode: 'insensitive' } },
        { phone: { contains: search, mode: 'insensitive' } },
      ];
    }

    if (status) {
      where.leadStatus = status;
    }

    // Obtener subscribers con paginación
    const [subscribers, total] = await Promise.all([
      prisma.subscriber.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        // 🚀 OPTIMIZADO: Select campos específicos + _count
        select: {
          id: true,
          subscriberId: true,
          platform: true,
          firstName: true,
          lastName: true,
          email: true,
          phone: true,
          leadStatus: true,
          leadScore: true,
          priority: true,
          tags: true,
          createdAt: true,
          lastActiveAt: true,
          _count: {
            select: {
              conversations: true,
            },
          },
        },
      }),
      prisma.subscriber.count({ where }),
    ]);

    res.json({
      success: true,
      data: subscribers,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error: any) {
    logger.error('Error al obtener subscribers:', error);
    res.status(500).json({
      success: false,
      message: 'Error al obtener subscribers',
      error: error.message,
    });
  }
});

// GET /api/subscribers/:id - Obtener un subscriber por ID
router.get('/:id', authenticate, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const subscriber = await prisma.subscriber.findUnique({
      where: { id },
      // 🚀 OPTIMIZADO: Select específico con conversations limitadas
      select: {
        id: true,
        subscriberId: true,
        platform: true,
        firstName: true,
        lastName: true,
        email: true,
        phone: true,
        leadStatus: true,
        leadScore: true,
        priority: true,
        tags: true,
        customFields: true,
        createdAt: true,
        updatedAt: true,
        lastActiveAt: true,
        conversations: {
          orderBy: { createdAt: 'desc' },
          take: 10,
          select: {
            id: true,
            topic: true,
            sentiment: true,
            isActive: true,
            createdAt: true,
            updatedAt: true,
          },
        },
        _count: {
          select: {
            conversations: true,
          },
        },
      },
    });

    if (!subscriber) {
      return res.status(404).json({
        success: false,
        message: 'Subscriber no encontrado',
      });
    }

    res.json({
      success: true,
      data: subscriber,
    });
  } catch (error: any) {
    logger.error('Error al obtener subscriber:', error);
    res.status(500).json({
      success: false,
      message: 'Error al obtener subscriber',
      error: error.message,
    });
  }
});

// POST /api/subscribers - Crear un nuevo subscriber
router.post('/', authenticate, async (req: Request, res: Response) => {
  try {
    const validatedData = subscriberSchema.parse(req.body);

    // Verificar si ya existe un subscriber con el mismo subscriberId (ManyChat ID)
    const existing = await prisma.subscriber.findUnique({
      where: { subscriberId: validatedData.subscriberId },
    });

    if (existing) {
      return res.status(400).json({
        success: false,
        message: 'Ya existe un subscriber con este ID de ManyChat',
      });
    }

    const subscriber = await prisma.subscriber.create({
      data: validatedData,
    });

    res.status(201).json({
      success: true,
      data: subscriber,
      message: 'Subscriber creado exitosamente',
    });
  } catch (error: any) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos inválidos',
        errors: error.errors,
      });
    }

    logger.error('Error al crear subscriber:', error);
    res.status(500).json({
      success: false,
      message: 'Error al crear subscriber',
      error: error.message,
    });
  }
});

// PUT /api/subscribers/:id - Actualizar un subscriber
router.put('/:id', authenticate, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const validatedData = subscriberSchema.partial().parse(req.body);

    // Verificar que el subscriber existe
    const existing = await prisma.subscriber.findUnique({
      where: { id },
    });

    if (!existing) {
      return res.status(404).json({
        success: false,
        message: 'Subscriber no encontrado',
      });
    }

    const subscriber = await prisma.subscriber.update({
      where: { id },
      data: validatedData,
    });

    res.json({
      success: true,
      data: subscriber,
      message: 'Subscriber actualizado exitosamente',
    });
  } catch (error: any) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos inválidos',
        errors: error.errors,
      });
    }

    logger.error('Error al actualizar subscriber:', error);
    res.status(500).json({
      success: false,
      message: 'Error al actualizar subscriber',
      error: error.message,
    });
  }
});

// DELETE /api/subscribers/:id - Eliminar un subscriber
router.delete('/:id', authenticate, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    // Verificar que el subscriber existe
    const existing = await prisma.subscriber.findUnique({
      where: { id },
    });

    if (!existing) {
      return res.status(404).json({
        success: false,
        message: 'Subscriber no encontrado',
      });
    }

    // Eliminar subscriber (esto también eliminará sus conversaciones en cascada)
    await prisma.subscriber.delete({
      where: { id },
    });

    res.json({
      success: true,
      message: 'Subscriber eliminado exitosamente',
    });
  } catch (error: any) {
    logger.error('Error al eliminar subscriber:', error);
    res.status(500).json({
      success: false,
      message: 'Error al eliminar subscriber',
      error: error.message,
    });
  }
});

// GET /api/subscribers/:id/conversations - Obtener todas las conversaciones de un subscriber
router.get('/:id/conversations', authenticate, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const page = parseInt(req.query.page as string) || 1;
    const limit = parseInt(req.query.limit as string) || 10;
    const skip = (page - 1) * limit;

    const [conversations, total] = await Promise.all([
      prisma.conversation.findMany({
        where: { subscriberId: id },
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        // 🚀 OPTIMIZADO: Select específico + _count
        select: {
          id: true,
          subscriberId: true,
          topic: true,
          sentiment: true,
          isActive: true,
          createdAt: true,
          updatedAt: true,
          _count: {
            select: {
              messages: true,
            },
          },
        },
      }),
      prisma.conversation.count({ where: { subscriberId: id } }),
    ]);

    res.json({
      success: true,
      data: conversations,
      pagination: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error: any) {
    logger.error('Error al obtener conversaciones:', error);
    res.status(500).json({
      success: false,
      message: 'Error al obtener conversaciones',
      error: error.message,
    });
  }
});

export default router;
