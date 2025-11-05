// ═══════════════════════════════════════════════════════════════════════════
// 💬 RUTAS - CONVERSACIONES
// ═══════════════════════════════════════════════════════════════════════════
// Gestión completa de conversaciones con mensajes multimodales

import { Router, Request, Response } from 'express';
import { prisma } from '../config/database.js';
import { authenticateToken } from '../middleware/auth.js';
import { z } from 'zod';
import { Prisma, MessageType, MessageRole, Sentiment } from '@prisma/client';
import { logger } from '#/utils/logger.js';

const router = Router();

// ═══════════════════════════════════════════════════════════════════════════
// 📋 SCHEMAS DE VALIDACIÓN CON ZOD
// ═══════════════════════════════════════════════════════════════════════════

const ListConversationsQuerySchema = z.object({
  page: z.string().optional().default('1'),
  limit: z.string().optional().default('20'),
  subscriberId: z.string().uuid().optional(),
  isActive: z.enum(['true', 'false']).optional(),
  sentiment: z.enum(['VERY_NEGATIVE', 'NEGATIVE', 'NEUTRAL', 'POSITIVE', 'VERY_POSITIVE']).optional(),
  topic: z.string().optional(),
  startDate: z.string().datetime().optional(),
  endDate: z.string().datetime().optional(),
  search: z.string().optional(), // Búsqueda en topic
});

const CreateConversationSchema = z.object({
  subscriberId: z.string().uuid({ message: 'subscriberId debe ser un UUID válido' }),
  topic: z.string().optional(),
  sentiment: z.enum(['VERY_NEGATIVE', 'NEGATIVE', 'NEUTRAL', 'POSITIVE', 'VERY_POSITIVE']).optional(),
  isActive: z.boolean().optional().default(true),
});

const UpdateConversationSchema = z.object({
  topic: z.string().optional(),
  sentiment: z.enum(['VERY_NEGATIVE', 'NEGATIVE', 'NEUTRAL', 'POSITIVE', 'VERY_POSITIVE']).optional(),
  isActive: z.boolean().optional(),
  endedAt: z.string().datetime().optional(),
});

const CreateMessageSchema = z.object({
  role: z.enum(['USER', 'ASSISTANT', 'SYSTEM'], { message: 'Rol debe ser USER, ASSISTANT o SYSTEM' }),
  messageType: z.enum(['TEXT', 'AUDIO', 'IMAGE', 'DOCUMENT', 'LOCATION']),
  content: z.string().min(1, 'El contenido no puede estar vacío'),
  audioUrl: z.string().url().optional(),
  imageUrl: z.string().url().optional(),
  audioTranscription: z.string().optional(),
  imageAnalysis: z.any().optional(),
  aiAgent: z.string().optional(),
  aiConfidence: z.number().min(0).max(1).optional(),
  processingTime: z.number().int().positive().optional(),
});

const ListMessagesQuerySchema = z.object({
  page: z.string().optional().default('1'),
  limit: z.string().optional().default('50'),
  role: z.enum(['USER', 'ASSISTANT', 'SYSTEM']).optional(),
  messageType: z.enum(['TEXT', 'AUDIO', 'IMAGE', 'DOCUMENT', 'LOCATION']).optional(),
});

// ═══════════════════════════════════════════════════════════════════════════
// 📍 ENDPOINTS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * GET / - Listar conversaciones con paginación y filtros
 */
router.get('/', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = ListConversationsQuerySchema.parse(req.query);

    const page = parseInt(query.page);
    const limit = parseInt(query.limit);
    const skip = (page - 1) * limit;

    // Construir filtros dinámicos
    const where: Prisma.ConversationWhereInput = {};

    if (query.subscriberId) {
      where.subscriberId = query.subscriberId;
    }

    if (query.isActive !== undefined) {
      where.isActive = query.isActive === 'true';
    }

    if (query.sentiment) {
      where.sentiment = query.sentiment as Sentiment;
    }

    if (query.topic || query.search) {
      where.topic = {
        contains: query.topic || query.search,
        mode: 'insensitive',
      };
    }

    if (query.startDate || query.endDate) {
      where.startedAt = {};
      if (query.startDate) {
        where.startedAt.gte = new Date(query.startDate);
      }
      if (query.endDate) {
        where.startedAt.lte = new Date(query.endDate);
      }
    }

    // Ejecutar queries en paralelo
    const [conversations, total] = await Promise.all([
      prisma.conversation.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        // 🚀 OPTIMIZADO: Select específico en conversation
        select: {
          id: true,
          subscriberId: true,
          topic: true,
          sentiment: true,
          isActive: true,
          startedAt: true,
          endedAt: true,
          createdAt: true,
          updatedAt: true,
          subscriber: {
            select: {
              id: true,
              firstName: true,
              lastName: true,
              email: true,
              platform: true,
              leadStatus: true,
            },
          },
          _count: {
            select: {
              messages: true,
            },
          },
        },
      }),
      prisma.conversation.count({ where }),
    ]);

    const totalPages = Math.ceil(total / limit);

    return res.json({
      success: true,
      data: conversations,
      pagination: {
        page,
        limit,
        total,
        totalPages,
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos de entrada inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error listando conversaciones:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener conversaciones',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /:id - Obtener conversación por ID con todos sus mensajes
 */
router.get('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    // Validar UUID
    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de conversación inválido',
      });
    }

    const conversation = await prisma.conversation.findUnique({
      where: { id },
      // 🚀 OPTIMIZADO: Select específico con messages optimizados
      select: {
        id: true,
        subscriberId: true,
        topic: true,
        sentiment: true,
        isActive: true,
        startedAt: true,
        endedAt: true,
        createdAt: true,
        updatedAt: true,
        subscriber: {
          select: {
            id: true,
            subscriberId: true,
            firstName: true,
            lastName: true,
            email: true,
            phone: true,
            platform: true,
            leadStatus: true,
            leadScore: true,
          },
        },
        messages: {
          orderBy: { createdAt: 'asc' },
          select: {
            id: true,
            role: true,
            messageType: true,
            content: true,
            audioUrl: true,
            imageUrl: true,
            audioTranscription: true,
            imageAnalysis: true,
            aiAgent: true,
            aiConfidence: true,
            processingTime: true,
            createdAt: true,
          },
        },
      },
    });

    if (!conversation) {
      return res.status(404).json({
        success: false,
        message: 'Conversación no encontrada',
      });
    }

    return res.json({
      success: true,
      data: conversation,
    });
  } catch (error) {
    logger.error('❌ Error obteniendo conversación:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener conversación',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /:id/messages - Obtener mensajes de una conversación (paginado)
 */
router.get('/:id/messages', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    // Validar UUID
    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de conversación inválido',
      });
    }

    const query = ListMessagesQuerySchema.parse(req.query);

    const page = parseInt(query.page);
    const limit = parseInt(query.limit);
    const skip = (page - 1) * limit;

    // Verificar que la conversación existe
    const conversationExists = await prisma.conversation.findUnique({
      where: { id },
      select: { id: true },
    });

    if (!conversationExists) {
      return res.status(404).json({
        success: false,
        message: 'Conversación no encontrada',
      });
    }

    // Construir filtros
    const where: Prisma.MessageWhereInput = {
      conversationId: id,
    };

    if (query.role) {
      where.role = query.role as MessageRole;
    }

    if (query.messageType) {
      where.messageType = query.messageType as MessageType;
    }

    // Ejecutar queries en paralelo
    const [messages, total] = await Promise.all([
      prisma.message.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'asc' },
        // 🚀 OPTIMIZADO: Select campos específicos
        select: {
          id: true,
          conversationId: true,
          role: true,
          messageType: true,
          content: true,
          audioUrl: true,
          imageUrl: true,
          audioTranscription: true,
          imageAnalysis: true,
          aiAgent: true,
          aiConfidence: true,
          processingTime: true,
          createdAt: true,
        },
      }),
      prisma.message.count({ where }),
    ]);

    const totalPages = Math.ceil(total / limit);

    return res.json({
      success: true,
      data: messages,
      pagination: {
        page,
        limit,
        total,
        totalPages,
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Parámetros de consulta inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error obteniendo mensajes:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener mensajes',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * POST / - Crear nueva conversación
 */
router.post('/', authenticateToken, async (req: Request, res: Response) => {
  try {
    const data = CreateConversationSchema.parse(req.body);

    // Verificar que el subscriber existe
    const subscriberExists = await prisma.subscriber.findUnique({
      where: { id: data.subscriberId },
      select: { id: true },
    });

    if (!subscriberExists) {
      return res.status(404).json({
        success: false,
        message: 'Subscriber no encontrado',
      });
    }

    const conversation = await prisma.conversation.create({
      data: {
        subscriberId: data.subscriberId,
        topic: data.topic,
        sentiment: data.sentiment,
        isActive: data.isActive ?? true,
      },
      include: {
        subscriber: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            email: true,
            platform: true,
          },
        },
      },
    });

    return res.status(201).json({
      success: true,
      data: conversation,
      message: 'Conversación creada exitosamente',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos de entrada inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error creando conversación:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al crear conversación',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * PUT /:id - Actualizar conversación
 */
router.put('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    // Validar UUID
    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de conversación inválido',
      });
    }

    const data = UpdateConversationSchema.parse(req.body);

    // Verificar que la conversación existe
    const conversationExists = await prisma.conversation.findUnique({
      where: { id },
      select: { id: true },
    });

    if (!conversationExists) {
      return res.status(404).json({
        success: false,
        message: 'Conversación no encontrada',
      });
    }

    // Preparar datos para actualización
    const updateData: Prisma.ConversationUpdateInput = {};

    if (data.topic !== undefined) updateData.topic = data.topic;
    if (data.sentiment !== undefined) updateData.sentiment = data.sentiment;
    if (data.isActive !== undefined) updateData.isActive = data.isActive;
    if (data.endedAt !== undefined) updateData.endedAt = new Date(data.endedAt);

    const conversation = await prisma.conversation.update({
      where: { id },
      data: updateData,
      include: {
        subscriber: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            email: true,
            platform: true,
          },
        },
        _count: {
          select: {
            messages: true,
          },
        },
      },
    });

    return res.json({
      success: true,
      data: conversation,
      message: 'Conversación actualizada exitosamente',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos de entrada inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error actualizando conversación:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al actualizar conversación',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * DELETE /:id - Eliminar conversación (y todos sus mensajes en cascada)
 */
router.delete('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    // Validar UUID
    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de conversación inválido',
      });
    }

    // Verificar que la conversación existe
    const conversation = await prisma.conversation.findUnique({
      where: { id },
      select: { id: true, _count: { select: { messages: true } } },
    });

    if (!conversation) {
      return res.status(404).json({
        success: false,
        message: 'Conversación no encontrada',
      });
    }

    // Eliminar (los mensajes se eliminan en cascada por el schema)
    await prisma.conversation.delete({
      where: { id },
    });

    return res.json({
      success: true,
      message: `Conversación eliminada exitosamente (${conversation._count.messages} mensajes eliminados)`,
    });
  } catch (error) {
    logger.error('❌ Error eliminando conversación:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al eliminar conversación',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * POST /:id/messages - Agregar mensaje a conversación
 */
router.post('/:id/messages', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    // Validar UUID
    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de conversación inválido',
      });
    }

    const data = CreateMessageSchema.parse(req.body);

    // Verificar que la conversación existe
    const conversationExists = await prisma.conversation.findUnique({
      where: { id },
      select: { id: true, isActive: true },
    });

    if (!conversationExists) {
      return res.status(404).json({
        success: false,
        message: 'Conversación no encontrada',
      });
    }

    // Crear el mensaje
    const message = await prisma.message.create({
      data: {
        conversationId: id,
        role: data.role as MessageRole,
        messageType: data.messageType as MessageType,
        content: data.content,
        audioUrl: data.audioUrl,
        imageUrl: data.imageUrl,
        audioTranscription: data.audioTranscription,
        imageAnalysis: data.imageAnalysis as Prisma.InputJsonValue,
        aiAgent: data.aiAgent,
        aiConfidence: data.aiConfidence,
        processingTime: data.processingTime,
      },
    });

    // Actualizar la conversación (timestamp)
    await prisma.conversation.update({
      where: { id },
      data: { updatedAt: new Date() },
    });

    return res.status(201).json({
      success: true,
      data: message,
      message: 'Mensaje agregado exitosamente',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos de entrada inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error agregando mensaje:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al agregar mensaje',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

export default router;
