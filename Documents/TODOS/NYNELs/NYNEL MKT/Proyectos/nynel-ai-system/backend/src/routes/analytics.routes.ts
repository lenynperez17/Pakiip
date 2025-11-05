// ═══════════════════════════════════════════════════════════════════════════
// 📊 RUTAS - ANALYTICS Y ESTADÍSTICAS
// ═══════════════════════════════════════════════════════════════════════════
// Endpoints para métricas y KPIs del negocio

import { Router, Request, Response } from 'express';
import { prisma } from '../config/database.js';
import { Prisma } from '@prisma/client';
import { authenticateToken } from '../middleware/auth.js';
import { z } from 'zod';
import { logger } from '#/utils/logger.js';

const router = Router();

// ═══════════════════════════════════════════════════════════════════════════
// 📋 SCHEMAS DE VALIDACIÓN
// ═══════════════════════════════════════════════════════════════════════════

const DateRangeQuerySchema = z.object({
  startDate: z.string().datetime().optional(),
  endDate: z.string().datetime().optional(),
});

const TrendQuerySchema = DateRangeQuerySchema.extend({
  period: z.enum(['day', 'week', 'month']).optional().default('day'),
});

// ═══════════════════════════════════════════════════════════════════════════
// 📍 ENDPOINTS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * GET /stats - Estadísticas generales del dashboard
 */
router.get('/stats', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = DateRangeQuerySchema.parse(req.query);

    // Construir filtro de fechas
    const dateFilter: any = {};
    if (query.startDate || query.endDate) {
      dateFilter.createdAt = {};
      if (query.startDate) dateFilter.createdAt.gte = new Date(query.startDate);
      if (query.endDate) dateFilter.createdAt.lte = new Date(query.endDate);
    }

    // Ejecutar todas las queries en paralelo para máxima performance
    const [
      totalSubscribers,
      newSubscribersCount,
      totalConversations,
      activeConversationsCount,
      totalQuotations,
      quotationsByStatus,
      quotationsAccepted,
      totalRevenue,
      averageLeadScore,
      subscribersByPlatform,
      topServices,
    ] = await Promise.all([
      // Total de subscribers
      prisma.subscriber.count(),

      // Nuevos subscribers en el período
      prisma.subscriber.count({ where: dateFilter }),

      // Total de conversaciones
      prisma.conversation.count(),

      // Conversaciones activas
      prisma.conversation.count({ where: { isActive: true } }),

      // Total de cotizaciones
      prisma.quotation.count(),

      // Cotizaciones por estado
      prisma.quotation.groupBy({
        by: ['status'],
        _count: { id: true },
        ...(Object.keys(dateFilter).length > 0 && { where: dateFilter }),
      }),

      // Cotizaciones aceptadas
      prisma.quotation.count({
        where: {
          status: 'ACCEPTED',
          ...dateFilter
        },
      }),

      // Ingresos totales (suma de cotizaciones aceptadas)
      prisma.quotation.aggregate({
        where: {
          status: 'ACCEPTED',
          ...dateFilter
        },
        _sum: { priceAverage: true },
      }),

      // Lead score promedio
      prisma.subscriber.aggregate({
        _avg: { leadScore: true },
      }),

      // Subscribers por plataforma
      prisma.subscriber.groupBy({
        by: ['platform'],
        _count: { id: true },
      }),

      // Servicios más cotizados
      prisma.quotation.groupBy({
        by: ['serviceName'],
        _count: { id: true },
        orderBy: { _count: { id: 'desc' } },
        take: 5,
        ...(Object.keys(dateFilter).length > 0 && { where: dateFilter }),
      }),
    ]);

    // Calcular tasa de conversión
    const conversionRate = totalQuotations > 0
      ? ((quotationsAccepted / totalQuotations) * 100).toFixed(2)
      : '0.00';

    return res.json({
      success: true,
      data: {
        subscribers: {
          total: totalSubscribers,
          new: newSubscribersCount,
          averageLeadScore: Math.round(averageLeadScore._avg.leadScore || 0),
          byPlatform: subscribersByPlatform.map(item => ({
            platform: item.platform,
            count: item._count.id,
          })),
        },
        conversations: {
          total: totalConversations,
          active: activeConversationsCount,
          inactive: totalConversations - activeConversationsCount,
        },
        quotations: {
          total: totalQuotations,
          accepted: quotationsAccepted,
          byStatus: quotationsByStatus.map(item => ({
            status: item.status,
            count: item._count.id,
          })),
        },
        revenue: {
          total: totalRevenue._sum.priceAverage?.toNumber() || 0,
          currency: 'PEN',
          conversionRate: `${conversionRate}%`,
        },
        topServices: topServices.map(item => ({
          service: item.serviceName,
          quotations: item._count.id,
        })),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Parámetros inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error obteniendo estadísticas:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener estadísticas',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /conversations-trend - Tendencia de conversaciones por período
 */
router.get('/conversations-trend', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = TrendQuerySchema.parse(req.query);

    // Construir query según el período
    let dateFormat: string;
    switch (query.period) {
      case 'day':
        dateFormat = '%Y-%m-%d';
        break;
      case 'week':
        dateFormat = '%Y-%W';
        break;
      case 'month':
        dateFormat = '%Y-%m';
        break;
      default:
        dateFormat = '%Y-%m-%d';
    }

    // Construir filtro de fechas
    const whereClause: any = {};
    if (query.startDate || query.endDate) {
      whereClause.createdAt = {};
      if (query.startDate) whereClause.createdAt.gte = new Date(query.startDate);
      if (query.endDate) whereClause.createdAt.lte = new Date(query.endDate);
    }

    // Query raw para agrupar por fecha (PostgreSQL syntax con nombres calificados)
    const trend = await prisma.$queryRaw<Array<{ date: string; count: bigint }>>`
      SELECT
        TO_CHAR("public"."conversations"."createdAt", ${dateFormat}) as date,
        COUNT(*) as count
      FROM "public"."conversations"
      ${Object.keys(whereClause).length > 0 ? Prisma.sql`WHERE "public"."conversations"."createdAt" >= ${whereClause.createdAt?.gte || new Date('1970-01-01')} AND "public"."conversations"."createdAt" <= ${whereClause.createdAt?.lte || new Date('2100-01-01')}` : Prisma.empty}
      GROUP BY date
      ORDER BY date ASC
    `;

    return res.json({
      success: true,
      data: {
        period: query.period,
        trend: trend.map(item => ({
          date: item.date,
          count: Number(item.count),
        })),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Parámetros inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error obteniendo tendencia de conversaciones:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener tendencia',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /leads-by-status - Distribución de leads por status
 */
router.get('/leads-by-status', authenticateToken, async (req: Request, res: Response) => {
  try {
    const leadsByStatus = await prisma.subscriber.groupBy({
      by: ['leadStatus'],
      _count: { id: true },
      orderBy: { _count: { id: 'desc' } },
    });

    const total = leadsByStatus.reduce((sum, item) => sum + item._count.id, 0);

    return res.json({
      success: true,
      data: {
        total,
        distribution: leadsByStatus.map(item => ({
          status: item.leadStatus,
          count: item._count.id,
          percentage: ((item._count.id / total) * 100).toFixed(2),
        })),
      },
    });
  } catch (error) {
    logger.error('❌ Error obteniendo leads por status:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener distribución de leads',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /top-services - Servicios más cotizados/solicitados
 */
router.get('/top-services', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = DateRangeQuerySchema.parse(req.query);

    const whereClause: any = {};
    if (query.startDate || query.endDate) {
      whereClause.createdAt = {};
      if (query.startDate) whereClause.createdAt.gte = new Date(query.startDate);
      if (query.endDate) whereClause.createdAt.lte = new Date(query.endDate);
    }

    const topServices = await prisma.quotation.groupBy({
      by: ['serviceName', 'serviceId'],
      _count: { id: true },
      _sum: { priceAverage: true },
      _avg: { priceAverage: true },
      where: whereClause,
      orderBy: { _count: { id: 'desc' } },
      take: 10,
    });

    return res.json({
      success: true,
      data: topServices.map(item => ({
        serviceId: item.serviceId,
        serviceName: item.serviceName,
        quotationsCount: item._count.id,
        totalRevenue: item._sum.priceAverage?.toNumber() || 0,
        averagePrice: item._avg.priceAverage?.toNumber() || 0,
      })),
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Parámetros inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error obteniendo top servicios:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener servicios',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /revenue-trend - Tendencia de ingresos por período
 */
router.get('/revenue-trend', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = TrendQuerySchema.parse(req.query);

    let dateFormat: string;
    switch (query.period) {
      case 'day':
        dateFormat = '%Y-%m-%d';
        break;
      case 'week':
        dateFormat = '%Y-%W';
        break;
      case 'month':
        dateFormat = '%Y-%m';
        break;
      default:
        dateFormat = '%Y-%m-%d';
    }

    const whereClause: any = { status: 'ACCEPTED' };
    if (query.startDate || query.endDate) {
      whereClause.createdAt = {};
      if (query.startDate) whereClause.createdAt.gte = new Date(query.startDate);
      if (query.endDate) whereClause.createdAt.lte = new Date(query.endDate);
    }

    // Query raw para ingresos por fecha
    const revenueTrend = await prisma.$queryRaw<Array<{ date: string; revenue: number; count: bigint }>>`
      SELECT
        DATE_FORMAT(created_at, ${dateFormat}) as date,
        SUM(price_average) as revenue,
        COUNT(*) as count
      FROM quotations
      WHERE status = 'ACCEPTED'
      ${query.startDate ? `AND created_at >= ${query.startDate}` : ''}
      ${query.endDate ? `AND created_at <= ${query.endDate}` : ''}
      GROUP BY date
      ORDER BY date ASC
    `;

    return res.json({
      success: true,
      data: {
        period: query.period,
        currency: 'PEN',
        trend: revenueTrend.map(item => ({
          date: item.date,
          revenue: Number(item.revenue) || 0,
          quotations: Number(item.count),
        })),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Parámetros inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error obteniendo tendencia de ingresos:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener tendencia de ingresos',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /conversion-rate - Tasa de conversión de leads a clientes
 */
router.get('/conversion-rate', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = DateRangeQuerySchema.parse(req.query);

    const whereClause: any = {};
    if (query.startDate || query.endDate) {
      whereClause.createdAt = {};
      if (query.startDate) whereClause.createdAt.gte = new Date(query.startDate);
      if (query.endDate) whereClause.createdAt.lte = new Date(query.endDate);
    }

    // Obtener métricas en paralelo
    const [
      totalLeads,
      contactedLeads,
      qualifiedLeads,
      proposalsSent,
      wonLeads,
      lostLeads,
      totalQuotations,
      acceptedQuotations,
      rejectedQuotations,
    ] = await Promise.all([
      prisma.subscriber.count({ where: whereClause }),
      prisma.subscriber.count({ where: { ...whereClause, leadStatus: 'CONTACTED' } }),
      prisma.subscriber.count({ where: { ...whereClause, leadStatus: 'QUALIFIED' } }),
      prisma.subscriber.count({ where: { ...whereClause, leadStatus: 'PROPOSAL_SENT' } }),
      prisma.subscriber.count({ where: { ...whereClause, leadStatus: 'WON' } }),
      prisma.subscriber.count({ where: { ...whereClause, leadStatus: 'LOST' } }),
      prisma.quotation.count({ where: whereClause }),
      prisma.quotation.count({ where: { ...whereClause, status: 'ACCEPTED' } }),
      prisma.quotation.count({ where: { ...whereClause, status: 'REJECTED' } }),
    ]);

    // Calcular tasas de conversión
    const leadConversionRate = totalLeads > 0 ? ((wonLeads / totalLeads) * 100).toFixed(2) : '0.00';
    const quotationConversionRate = totalQuotations > 0
      ? ((acceptedQuotations / totalQuotations) * 100).toFixed(2)
      : '0.00';
    const contactToQualifiedRate = contactedLeads > 0
      ? ((qualifiedLeads / contactedLeads) * 100).toFixed(2)
      : '0.00';
    const qualifiedToProposalRate = qualifiedLeads > 0
      ? ((proposalsSent / qualifiedLeads) * 100).toFixed(2)
      : '0.00';

    return res.json({
      success: true,
      data: {
        leads: {
          total: totalLeads,
          contacted: contactedLeads,
          qualified: qualifiedLeads,
          proposalsSent: proposalsSent,
          won: wonLeads,
          lost: lostLeads,
        },
        quotations: {
          total: totalQuotations,
          accepted: acceptedQuotations,
          rejected: rejectedQuotations,
        },
        conversionRates: {
          leadToCustomer: `${leadConversionRate}%`,
          quotationAcceptance: `${quotationConversionRate}%`,
          contactToQualified: `${contactToQualifiedRate}%`,
          qualifiedToProposal: `${qualifiedToProposalRate}%`,
        },
        funnel: [
          { stage: 'Leads Totales', count: totalLeads, percentage: '100.00' },
          { stage: 'Contactados', count: contactedLeads, percentage: totalLeads > 0 ? ((contactedLeads / totalLeads) * 100).toFixed(2) : '0.00' },
          { stage: 'Calificados', count: qualifiedLeads, percentage: totalLeads > 0 ? ((qualifiedLeads / totalLeads) * 100).toFixed(2) : '0.00' },
          { stage: 'Propuestas Enviadas', count: proposalsSent, percentage: totalLeads > 0 ? ((proposalsSent / totalLeads) * 100).toFixed(2) : '0.00' },
          { stage: 'Ganados', count: wonLeads, percentage: totalLeads > 0 ? ((wonLeads / totalLeads) * 100).toFixed(2) : '0.00' },
        ],
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Parámetros inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error calculando tasa de conversión:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al calcular tasa de conversión',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

export default router;
