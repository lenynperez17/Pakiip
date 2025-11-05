// ═══════════════════════════════════════════════════════════════════════════
// 💰 RUTAS - COTIZACIONES
// ═══════════════════════════════════════════════════════════════════════════
// Gestión completa de cotizaciones con generación de PDF y envío

import { Router, Request, Response } from 'express';
import { prisma } from '../config/database.js';
import { authenticateToken } from '../middleware/auth.js';
import { z } from 'zod';
import { Prisma, QuotationStatus, Complexity, Urgency, CompanySize } from '@prisma/client';
import { logger } from '#/utils/logger.js';
import puppeteer from 'puppeteer';
import { Decimal } from '@prisma/client/runtime/library';
import { sendQuotationViaManyChat } from '../services/manychat.service.js';

const router = Router();

// ═══════════════════════════════════════════════════════════════════════════
// 📋 SCHEMAS DE VALIDACIÓN CON ZOD
// ═══════════════════════════════════════════════════════════════════════════

const ListQuotationsQuerySchema = z.object({
  page: z.string().optional().default('1'),
  limit: z.string().optional().default('20'),
  subscriberId: z.string().uuid().optional(),
  serviceId: z.string().uuid().optional(),
  status: z.enum(['DRAFT', 'SENT', 'VIEWED', 'ACCEPTED', 'REJECTED', 'EXPIRED']).optional(),
  minPrice: z.string().optional(),
  maxPrice: z.string().optional(),
  startDate: z.string().datetime().optional(),
  endDate: z.string().datetime().optional(),
  search: z.string().optional(),
});

const QuotationPackageSchema = z.object({
  name: z.string(),
  price: z.number(),
  features: z.array(z.string()),
  recommended: z.boolean().optional(),
});

const CreateQuotationSchema = z.object({
  subscriberId: z.string().uuid(),
  serviceId: z.string().uuid(),
  complexity: z.enum(['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH', 'ENTERPRISE']),
  urgency: z.enum(['NORMAL', 'PRIORITY', 'URGENT']),
  companySize: z.enum(['MICRO', 'SMALL', 'MEDIUM', 'LARGE']),
  discount: z.number().min(0).max(100).optional(),
  description: z.string().optional(),
  includes: z.array(z.string()).optional(),
  deliveryTime: z.string().optional(),
  terms: z.string().optional(),
  packages: z.array(QuotationPackageSchema),
  validUntil: z.string().datetime(),
});

const UpdateQuotationSchema = z.object({
  complexity: z.enum(['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH', 'ENTERPRISE']).optional(),
  urgency: z.enum(['NORMAL', 'PRIORITY', 'URGENT']).optional(),
  companySize: z.enum(['MICRO', 'SMALL', 'MEDIUM', 'LARGE']).optional(),
  discount: z.number().min(0).max(100).optional(),
  description: z.string().optional(),
  includes: z.array(z.string()).optional(),
  deliveryTime: z.string().optional(),
  terms: z.string().optional(),
  packages: z.array(QuotationPackageSchema).optional(),
  status: z.enum(['DRAFT', 'SENT', 'VIEWED', 'ACCEPTED', 'REJECTED', 'EXPIRED']).optional(),
  validUntil: z.string().datetime().optional(),
});

const SendQuotationSchema = z.object({
  method: z.enum(['email', 'whatsapp', 'both']),
  message: z.string().optional(),
});

// ═══════════════════════════════════════════════════════════════════════════
// 🧮 UTILIDADES DE CÁLCULO
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Calcula precios basados en factores de complejidad, urgencia y tamaño
 */
function calculatePrices(
  baseMin: number,
  baseMax: number,
  complexity: Complexity,
  urgency: Urgency,
  companySize: CompanySize,
  discount: number = 0
): { priceMin: number; priceMax: number; priceAverage: number } {
  // Multiplicadores por complejidad
  const complexityMultipliers = {
    LOW: 1.0,
    MEDIUM: 1.5,
    HIGH: 2.0,
    VERY_HIGH: 3.0,
    ENTERPRISE: 5.0,
  };

  // Multiplicadores por urgencia
  const urgencyMultipliers = {
    NORMAL: 1.0,
    PRIORITY: 1.3,
    URGENT: 1.6,
  };

  // Multiplicadores por tamaño de empresa
  const companySizeMultipliers = {
    MICRO: 1.0,
    SMALL: 1.2,
    MEDIUM: 1.5,
    LARGE: 2.0,
  };

  let priceMin = baseMin * complexityMultipliers[complexity];
  let priceMax = baseMax * complexityMultipliers[complexity];

  priceMin *= urgencyMultipliers[urgency];
  priceMax *= urgencyMultipliers[urgency];

  priceMin *= companySizeMultipliers[companySize];
  priceMax *= companySizeMultipliers[companySize];

  // Aplicar descuento
  if (discount > 0) {
    const discountFactor = 1 - discount / 100;
    priceMin *= discountFactor;
    priceMax *= discountFactor;
  }

  const priceAverage = (priceMin + priceMax) / 2;

  return {
    priceMin: Math.round(priceMin * 100) / 100,
    priceMax: Math.round(priceMax * 100) / 100,
    priceAverage: Math.round(priceAverage * 100) / 100,
  };
}

/**
 * Genera código único de cotización (COT-2025-001)
 */
async function generateQuotationCode(): Promise<string> {
  const year = new Date().getFullYear();
  const count = await prisma.quotation.count();
  const number = String(count + 1).padStart(3, '0');
  return `COT-${year}-${number}`;
}

// ═══════════════════════════════════════════════════════════════════════════
// 📍 ENDPOINTS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * GET / - Listar cotizaciones con filtros avanzados
 */
router.get('/', authenticateToken, async (req: Request, res: Response) => {
  try {
    const query = ListQuotationsQuerySchema.parse(req.query);

    const page = parseInt(query.page);
    const limit = parseInt(query.limit);
    const skip = (page - 1) * limit;

    // Construir filtros dinámicos
    const where: Prisma.QuotationWhereInput = {};

    if (query.subscriberId) {
      where.subscriberId = query.subscriberId;
    }

    if (query.serviceId) {
      where.serviceId = query.serviceId;
    }

    if (query.status) {
      where.status = query.status as QuotationStatus;
    }

    if (query.minPrice || query.maxPrice) {
      where.priceAverage = {};
      if (query.minPrice) {
        where.priceAverage.gte = parseFloat(query.minPrice);
      }
      if (query.maxPrice) {
        where.priceAverage.lte = parseFloat(query.maxPrice);
      }
    }

    if (query.startDate || query.endDate) {
      where.createdAt = {};
      if (query.startDate) {
        where.createdAt.gte = new Date(query.startDate);
      }
      if (query.endDate) {
        where.createdAt.lte = new Date(query.endDate);
      }
    }

    if (query.search) {
      where.OR = [
        { quotationCode: { contains: query.search, mode: 'insensitive' } },
        { serviceName: { contains: query.search, mode: 'insensitive' } },
        { description: { contains: query.search, mode: 'insensitive' } },
      ];
    }

    // Ejecutar queries en paralelo
    const [quotations, total] = await Promise.all([
      prisma.quotation.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        include: {
          subscriber: {
            select: {
              id: true,
              firstName: true,
              lastName: true,
              email: true,
              phone: true,
              platform: true,
            },
          },
          service: {
            select: {
              id: true,
              name: true,
              category: true,
            },
          },
        },
      }),
      prisma.quotation.count({ where }),
    ]);

    const totalPages = Math.ceil(total / limit);

    return res.json({
      success: true,
      data: quotations,
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

    logger.error('❌ Error listando cotizaciones:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener cotizaciones',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /:id - Obtener cotización completa por ID
 */
router.get('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de cotización inválido',
      });
    }

    const quotation = await prisma.quotation.findUnique({
      where: { id },
      include: {
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
          },
        },
        service: true,
      },
    });

    if (!quotation) {
      return res.status(404).json({
        success: false,
        message: 'Cotización no encontrada',
      });
    }

    return res.json({
      success: true,
      data: quotation,
    });
  } catch (error) {
    logger.error('❌ Error obteniendo cotización:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al obtener cotización',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * POST / - Crear nueva cotización con cálculo automático
 */
router.post('/', authenticateToken, async (req: Request, res: Response) => {
  try {
    const data = CreateQuotationSchema.parse(req.body);

    // Verificar subscriber
    const subscriber = await prisma.subscriber.findUnique({
      where: { id: data.subscriberId },
      select: { id: true },
    });

    if (!subscriber) {
      return res.status(404).json({
        success: false,
        message: 'Subscriber no encontrado',
      });
    }

    // Verificar servicio y obtener precios base
    const service = await prisma.service.findUnique({
      where: { id: data.serviceId },
    });

    if (!service) {
      return res.status(404).json({
        success: false,
        message: 'Servicio no encontrado',
      });
    }

    // Calcular precios
    const prices = calculatePrices(
      service.priceMin.toNumber(),
      service.priceMax.toNumber(),
      data.complexity,
      data.urgency,
      data.companySize,
      data.discount
    );

    // Generar código
    const quotationCode = await generateQuotationCode();

    // Crear cotización
    const quotation = await prisma.quotation.create({
      data: {
        quotationCode,
        subscriberId: data.subscriberId,
        serviceId: data.serviceId,
        serviceName: service.name,
        priceMin: new Decimal(prices.priceMin),
        priceMax: new Decimal(prices.priceMax),
        priceAverage: new Decimal(prices.priceAverage),
        currency: 'PEN',
        complexity: data.complexity,
        urgency: data.urgency,
        companySize: data.companySize,
        discount: data.discount ? new Decimal(data.discount) : null,
        packages: data.packages as Prisma.InputJsonValue,
        description: data.description,
        includes: data.includes || [],
        deliveryTime: data.deliveryTime,
        terms: data.terms,
        validUntil: new Date(data.validUntil),
        status: 'DRAFT',
      },
      include: {
        subscriber: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            email: true,
            phone: true,
          },
        },
        service: {
          select: {
            id: true,
            name: true,
            category: true,
          },
        },
      },
    });

    return res.status(201).json({
      success: true,
      data: quotation,
      message: 'Cotización creada exitosamente',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos de entrada inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error creando cotización:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al crear cotización',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * PUT /:id - Actualizar cotización y recalcular precios
 */
router.put('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de cotización inválido',
      });
    }

    const data = UpdateQuotationSchema.parse(req.body);

    // Obtener cotización actual
    const currentQuotation = await prisma.quotation.findUnique({
      where: { id },
      include: { service: true },
    });

    if (!currentQuotation) {
      return res.status(404).json({
        success: false,
        message: 'Cotización no encontrada',
      });
    }

    // Preparar datos de actualización
    const updateData: any = {};

    if (data.description !== undefined) updateData.description = data.description;
    if (data.includes !== undefined) updateData.includes = data.includes;
    if (data.deliveryTime !== undefined) updateData.deliveryTime = data.deliveryTime;
    if (data.terms !== undefined) updateData.terms = data.terms;
    if (data.packages !== undefined) updateData.packages = data.packages as Prisma.InputJsonValue;
    if (data.status !== undefined) updateData.status = data.status;
    if (data.validUntil !== undefined) updateData.validUntil = new Date(data.validUntil);

    // Si cambian factores de precio, recalcular
    const needsRecalculation =
      data.complexity || data.urgency || data.companySize || data.discount !== undefined;

    if (needsRecalculation) {
      const complexity = data.complexity || currentQuotation.complexity;
      const urgency = data.urgency || currentQuotation.urgency;
      const companySize = data.companySize || currentQuotation.companySize;
      const discount = data.discount !== undefined ? data.discount : currentQuotation.discount?.toNumber() || 0;

      const prices = calculatePrices(
        currentQuotation.service.priceMin.toNumber(),
        currentQuotation.service.priceMax.toNumber(),
        complexity,
        urgency,
        companySize,
        discount
      );

      updateData.complexity = complexity;
      updateData.urgency = urgency;
      updateData.companySize = companySize;
      updateData.discount = discount > 0 ? new Decimal(discount) : null;
      updateData.priceMin = new Decimal(prices.priceMin);
      updateData.priceMax = new Decimal(prices.priceMax);
      updateData.priceAverage = new Decimal(prices.priceAverage);
    }

    const quotation = await prisma.quotation.update({
      where: { id },
      data: updateData,
      include: {
        subscriber: {
          select: {
            id: true,
            firstName: true,
            lastName: true,
            email: true,
            phone: true,
          },
        },
        service: {
          select: {
            id: true,
            name: true,
            category: true,
          },
        },
      },
    });

    return res.json({
      success: true,
      data: quotation,
      message: 'Cotización actualizada exitosamente',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Datos de entrada inválidos',
        errors: error.errors,
      });
    }

    logger.error('❌ Error actualizando cotización:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al actualizar cotización',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * DELETE /:id - Eliminar cotización
 */
router.delete('/:id', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de cotización inválido',
      });
    }

    const quotation = await prisma.quotation.findUnique({
      where: { id },
      select: { id: true, quotationCode: true },
    });

    if (!quotation) {
      return res.status(404).json({
        success: false,
        message: 'Cotización no encontrada',
      });
    }

    await prisma.quotation.delete({
      where: { id },
    });

    return res.json({
      success: true,
      message: `Cotización ${quotation.quotationCode} eliminada exitosamente`,
    });
  } catch (error) {
    logger.error('❌ Error eliminando cotización:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al eliminar cotización',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * POST /:id/send - Enviar cotización por email/WhatsApp
 */
router.post('/:id/send', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de cotización inválido',
      });
    }

    const data = SendQuotationSchema.parse(req.body);

    const quotation = await prisma.quotation.findUnique({
      where: { id },
      include: {
        subscriber: true,
        service: true,
      },
    });

    if (!quotation) {
      return res.status(404).json({
        success: false,
        message: 'Cotización no encontrada',
      });
    }

    // Validar que tenga subscriberId
    if (!quotation.subscriber.subscriberId) {
      return res.status(400).json({
        success: false,
        message: 'El subscriber no tiene ID de ManyChat. Solo se pueden enviar cotizaciones a usuarios que hayan interactuado vía ManyChat.',
      });
    }

    // Generar URL del PDF (asumiendo que tienes un endpoint público)
    const pdfUrl = `${process.env.API_URL || 'http://localhost:3001'}/api/quotations/${id}/pdf`;

    // Nombre completo del cliente
    const clientName = quotation.subscriber.firstName
      ? `${quotation.subscriber.firstName} ${quotation.subscriber.lastName || ''}`.trim()
      : 'Cliente';

    // Enviar via ManyChat (maneja automáticamente WhatsApp, Instagram, Facebook, Email, etc.)
    const manyChatResult = await sendQuotationViaManyChat(
      quotation.subscriber.subscriberId,
      clientName,
      pdfUrl,
      quotation.quotationCode,
      Number(quotation.priceAverage)
    );

    if (!manyChatResult.sent) {
      return res.status(500).json({
        success: false,
        message: 'Error al enviar cotización via ManyChat',
        error: manyChatResult.error,
      });
    }

    // Actualizar estado a SENT solo si se envió exitosamente
    await prisma.quotation.update({
      where: { id },
      data: {
        status: 'SENT',
      },
    });

    return res.json({
      success: true,
      message: 'Cotización enviada exitosamente via ManyChat',
      data: {
        sent: true,
        subscriberId: quotation.subscriber.subscriberId,
        platform: 'manychat', // ManyChat decide el canal (WhatsApp, Instagram, etc.)
        quotationNumber: quotation.quotationCode,
        pdfUrl,
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

    logger.error('❌ Error enviando cotización:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al enviar cotización',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

/**
 * GET /:id/pdf - Generar y descargar PDF de cotización
 */
router.get('/:id/pdf', authenticateToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    if (!z.string().uuid().safeParse(id).success) {
      return res.status(400).json({
        success: false,
        message: 'ID de cotización inválido',
      });
    }

    const quotation = await prisma.quotation.findUnique({
      where: { id },
      include: {
        subscriber: true,
        service: true,
      },
    });

    if (!quotation) {
      return res.status(404).json({
        success: false,
        message: 'Cotización no encontrada',
      });
    }

    // Generar HTML para PDF
    const htmlContent = generateQuotationHTML(quotation);

    // Generar PDF con Puppeteer
    const browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    const page = await browser.newPage();
    await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

    const pdfBuffer = await page.pdf({
      format: 'A4',
      printBackground: true,
      margin: {
        top: '20mm',
        right: '15mm',
        bottom: '20mm',
        left: '15mm',
      },
    });

    await browser.close();

    // Actualizar registro con fecha de generación
    await prisma.quotation.update({
      where: { id },
      data: { pdfGeneratedAt: new Date() },
    });

    // Enviar PDF como respuesta
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="cotizacion-${quotation.quotationCode}.pdf"`
    );
    res.send(pdfBuffer);
  } catch (error) {
    logger.error('❌ Error generando PDF:', error);
    return res.status(500).json({
      success: false,
      message: 'Error al generar PDF',
      error: process.env.NODE_ENV === 'development' ? String(error) : undefined,
    });
  }
});

// ═══════════════════════════════════════════════════════════════════════════
// 🎨 GENERACIÓN DE HTML PARA PDF
// ═══════════════════════════════════════════════════════════════════════════

function generateQuotationHTML(quotation: any): string {
  const packages = quotation.packages as Array<{
    name: string;
    price: number;
    features: string[];
    recommended?: boolean;
  }>;

  return `
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
      line-height: 1.6;
    }
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 40px;
      text-align: center;
    }
    .header h1 { font-size: 36px; margin-bottom: 10px; }
    .header p { font-size: 18px; opacity: 0.9; }
    .container { padding: 40px; }
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin: 30px 0;
    }
    .info-box {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      border-left: 4px solid #667eea;
    }
    .info-box h3 {
      color: #667eea;
      margin-bottom: 10px;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .info-box p { margin: 5px 0; }
    .packages {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin: 40px 0;
    }
    .package {
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      padding: 30px 20px;
      text-align: center;
      position: relative;
    }
    .package.recommended {
      border-color: #667eea;
      background: #f0f4ff;
    }
    .package.recommended::before {
      content: 'RECOMENDADO';
      position: absolute;
      top: -12px;
      left: 50%;
      transform: translateX(-50%);
      background: #667eea;
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
    }
    .package h4 {
      font-size: 20px;
      margin-bottom: 15px;
      color: #333;
    }
    .package .price {
      font-size: 36px;
      font-weight: bold;
      color: #667eea;
      margin: 20px 0;
    }
    .package .features {
      text-align: left;
      margin-top: 20px;
    }
    .package .features li {
      margin: 10px 0;
      padding-left: 25px;
      position: relative;
    }
    .package .features li::before {
      content: '✓';
      position: absolute;
      left: 0;
      color: #4caf50;
      font-weight: bold;
    }
    .includes {
      background: #fff9e6;
      border-left: 4px solid #ffc107;
      padding: 20px;
      margin: 30px 0;
      border-radius: 4px;
    }
    .includes h3 {
      color: #f57c00;
      margin-bottom: 15px;
    }
    .includes ul { padding-left: 20px; }
    .includes li { margin: 8px 0; }
    .terms {
      background: #f5f5f5;
      padding: 20px;
      margin-top: 30px;
      border-radius: 8px;
      font-size: 12px;
      color: #666;
    }
    .terms h3 {
      color: #333;
      margin-bottom: 10px;
      font-size: 14px;
    }
    .footer {
      text-align: center;
      padding: 30px;
      background: #333;
      color: white;
      margin-top: 50px;
    }
    .footer p { margin: 5px 0; }
    .highlight {
      background: #fff3cd;
      padding: 15px;
      border-radius: 8px;
      margin: 20px 0;
      border-left: 4px solid #ffc107;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>NYNEL MKT</h1>
    <p>Cotización Profesional</p>
    <p style="margin-top: 10px; font-size: 16px;">Código: ${quotation.quotationCode}</p>
  </div>

  <div class="container">
    <div class="info-grid">
      <div class="info-box">
        <h3>Cliente</h3>
        <p><strong>${quotation.subscriber.firstName || ''} ${quotation.subscriber.lastName || ''}</strong></p>
        ${quotation.subscriber.email ? `<p>📧 ${quotation.subscriber.email}</p>` : ''}
        ${quotation.subscriber.phone ? `<p>📱 ${quotation.subscriber.phone}</p>` : ''}
      </div>
      <div class="info-box">
        <h3>Servicio</h3>
        <p><strong>${quotation.serviceName}</strong></p>
        <p>Complejidad: ${quotation.complexity}</p>
        <p>Urgencia: ${quotation.urgency}</p>
        <p>Válida hasta: ${new Date(quotation.validUntil).toLocaleDateString('es-ES')}</p>
      </div>
    </div>

    ${
      quotation.description
        ? `
    <div class="highlight">
      <h3 style="margin-bottom: 10px;">📝 Descripción del Proyecto</h3>
      <p>${quotation.description}</p>
    </div>
    `
        : ''
    }

    <h2 style="text-align: center; margin: 40px 0 30px; color: #667eea;">Paquetes Disponibles</h2>

    <div class="packages">
      ${packages
        .map(
          (pkg) => `
        <div class="package ${pkg.recommended ? 'recommended' : ''}">
          <h4>${pkg.name}</h4>
          <div class="price">S/ ${pkg.price.toLocaleString('es-PE')}</div>
          <ul class="features">
            ${pkg.features.map((feature) => `<li>${feature}</li>`).join('')}
          </ul>
        </div>
      `
        )
        .join('')}
    </div>

    ${
      quotation.includes && quotation.includes.length > 0
        ? `
    <div class="includes">
      <h3>✨ Incluido en Todos los Paquetes</h3>
      <ul>
        ${quotation.includes.map((item: string) => `<li>${item}</li>`).join('')}
      </ul>
    </div>
    `
        : ''
    }

    ${
      quotation.deliveryTime
        ? `
    <div class="highlight">
      <h3 style="margin-bottom: 10px;">⏱️ Tiempo de Entrega</h3>
      <p>${quotation.deliveryTime}</p>
    </div>
    `
        : ''
    }

    ${
      quotation.terms
        ? `
    <div class="terms">
      <h3>📋 Términos y Condiciones</h3>
      <p>${quotation.terms}</p>
    </div>
    `
        : ''
    }
  </div>

  <div class="footer">
    <p><strong>NYNEL MKT</strong></p>
    <p>📧 contacto@nynelmkt.com | 📱 +51 XXX XXX XXX</p>
    <p>🌐 www.nynelmkt.com</p>
    <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
      Generado el ${new Date().toLocaleDateString('es-ES')}
    </p>
  </div>
</body>
</html>
  `;
}

export default router;
