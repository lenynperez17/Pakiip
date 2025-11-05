// ═══════════════════════════════════════════════════════════════════════════
// 💰 SERVICIO DE COTIZACIONES PROFESIONALES CON PDF
// ═══════════════════════════════════════════════════════════════════════════
// Genera cotizaciones profesionales en PDF con branding de NYNEL MKT
// Envía por email (PDF adjunto) y WhatsApp (link al PDF)
// ═══════════════════════════════════════════════════════════════════════════

import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { logger } from '../utils/logger.js';
import nodemailer from 'nodemailer';
import { prisma } from '../config/database.js';
import { manyChatAPI } from './manychat-api.service.js';
import { emailNotificationService } from './email-notification.service.js';
import { quotationHTMLTemplateService } from './quotation-html-template.service.js';
import { pricingLogicService } from './pricing-logic.service.js';

interface QuotationData {
  subscriberId: string;
  clientName: string;
  clientEmail?: string;
  clientPhone?: string;
  empresa?: string;

  // ═══════════════════════════════════════════════════════════════
  // 📋 DATOS BÁSICOS DEL PROYECTO
  // ═══════════════════════════════════════════════════════════════
  tipoProyecto?: string; // Tipo general
  descripcionProyecto?: string; // Descripción completa de la conversación
  presupuestoEstimado?: string;
  urgencia?: string;
  tipoNegocio?: string; // Startup | PYME | Empresa | Corporativo
  tamañoEmpresa?: string; // 1-10 | 11-50 | 51-200 | 200+

  // ═══════════════════════════════════════════════════════════════
  // 🎯 INFORMACIÓN DETALLADA EXTRAÍDA DE LA CONVERSACIÓN
  // ═══════════════════════════════════════════════════════════════
  projectType?: string; // app-movil | web | ecommerce | chatbot | landing
  projectName?: string; // "App de delivery para restaurante"
  industry?: string; // restaurante | salud | educacion | retail | etc

  // Arrays de datos específicos
  features?: string[]; // Funcionalidades que mencionó
  platforms?: string[]; // iOS, Android, Web
  integrations?: string[]; // WhatsApp, MercadoPago, Google Maps
  technologies?: string[]; // Flutter, React, Firebase
  specificRequirements?: string[]; // Requisitos adicionales
  targetUsers?: string; // Audiencia objetivo
  complexity?: string; // simple | intermedia | compleja | enterprise

  // ═══════════════════════════════════════════════════════════════
  // 💡 ANÁLISIS DE NECESIDADES (NUEVOS CAMPOS PROFESIONALES 2025)
  // ═══════════════════════════════════════════════════════════════
  problemasIdentificados?: string[]; // Pain points específicos del cliente
  objetivosNegocio?: string[]; // Objetivos de negocio que quiere lograr
  beneficiosEsperados?: string[]; // Beneficios que espera obtener
  riesgosActuales?: string[]; // Riesgos de no implementar la solución
  situacionActual?: string; // Descripción de la situación actual/problema
  resultadoDeseado?: string; // Visión del resultado ideal
  requisitosEspeciales?: string[]; // Requisitos especiales mencionados

  // ═══════════════════════════════════════════════════════════════
  // 📊 MÉTRICAS Y ROI (NUEVOS CAMPOS PROFESIONALES 2025)
  // ═══════════════════════════════════════════════════════════════
  kpisEsperados?: string[]; // KPIs específicos que quiere mejorar
  roiEstimado?: string; // ROI proyectado (ej: "300% en 12 meses")
  tiempoRecuperacion?: string; // Tiempo para recuperar inversión (ej: "6 meses")
}

interface QuotationPackage {
  name: string;
  price: string;
  features: string[];
  deliveryTime: string;
}

class QuotationService {
  private pdfDir: string;

  constructor() {
    // Directorio para guardar PDFs
    this.pdfDir = path.join(process.cwd(), 'public', 'quotations');

    // Crear directorio si no existe
    if (!fs.existsSync(this.pdfDir)) {
      fs.mkdirSync(this.pdfDir, { recursive: true });
      logger.info(`📁 Directorio de cotizaciones creado: ${this.pdfDir}`);
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📄 GENERAR COTIZACIÓN COMPLETA (PDF + EMAIL + WHATSAPP)
   * ═══════════════════════════════════════════════════════════════
   */
  async generateQuotation(data: QuotationData): Promise<{
    success: boolean;
    pdfPath?: string;
    pdfUrl?: string;
    message: string;
  }> {
    try {
      logger.info('💰 Generando cotización profesional...');

      // 1. Generar código único de cotización
      const quotationCode = this.generateQuotationCode();

      // 2. Determinar paquetes según tipo de proyecto
      const packages = this.generatePackages(data);

      // 3. Generar PDF
      const pdfFilename = `${quotationCode}.pdf`;
      const pdfPath = path.join(this.pdfDir, pdfFilename);

      await this.createPDF(pdfPath, {
        quotationCode,
        data,
        packages,
      });

      // 4. Generar URL pública del PDF
      const pdfUrl = `${process.env.BASE_URL || 'https://api.nyneldigital.com'}/quotations/${pdfFilename}`;

      logger.info(`✅ PDF generado: ${pdfUrl}`);

      // 5. Enviar por email si hay email disponible
      if (data.clientEmail) {
        await this.sendEmail(data, pdfPath, pdfUrl);
      }

      // 6. Enviar link por WhatsApp
      await this.sendWhatsAppLink(data.subscriberId, pdfUrl, quotationCode);

      // 7. Guardar en base de datos
      await this.saveQuotationToDB({
        quotationCode,
        subscriberId: data.subscriberId,
        pdfUrl,
        data,
        packages,
      });

      return {
        success: true,
        pdfPath,
        pdfUrl,
        message: `Cotización ${quotationCode} generada exitosamente`,
      };
    } catch (error: any) {
      logger.error('❌ Error generando cotización:', error);
      return {
        success: false,
        message: `Error: ${error.message}`,
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎨 CREAR PDF PROFESIONAL
   * ═══════════════════════════════════════════════════════════════
   */
  /**
   * ═══════════════════════════════════════════════════════════════
   * 📄 CREAR PDF PROFESIONAL USANDO PUPPETEER Y PLANTILLA HTML
   * ═══════════════════════════════════════════════════════════════
   * Genera PDF de alta calidad con diseño profesional usando:
   * - Plantilla HTML con diseño de slides moderno
   * - Puppeteer para conversión HTML -> PDF
   * - Gradientes, fuentes personalizadas y diseño responsive
   */
  private async createPDF(
    filePath: string,
    content: {
      quotationCode: string;
      data: QuotationData;
      packages: QuotationPackage[];
    }
  ): Promise<void> {
    try {
      logger.info('🎨 Generando PDF profesional con Puppeteer...');

      // ─────────────────────────────────────────────────────────
      // 1️⃣ CALCULAR INVERSIÓN TOTAL
      // ─────────────────────────────────────────────────────────
      const totalInversion = this.calculateTotalInversion(content.packages);
      logger.info(`💰 Inversión total calculada: ${totalInversion}`);

      // ─────────────────────────────────────────────────────────
      // 2️⃣ PREPARAR DATOS PARA LA PLANTILLA HTML
      // ─────────────────────────────────────────────────────────
      const htmlData = {
        // ═══════════════════════════════════════════════════════
        // 📋 DATOS BÁSICOS DEL CLIENTE
        // ═══════════════════════════════════════════════════════
        quotationCode: content.quotationCode,
        clientName: content.data.clientName,
        empresa: content.data.empresa,
        clientEmail: content.data.clientEmail,
        clientPhone: content.data.clientPhone,

        // ═══════════════════════════════════════════════════════
        // 🎯 CONTEXTO DEL NEGOCIO Y PROYECTO
        // ═══════════════════════════════════════════════════════
        tipoProyecto: content.data.tipoProyecto,
        descripcionProyecto: content.data.descripcionProyecto,
        presupuestoEstimado: content.data.presupuestoEstimado,
        urgencia: content.data.urgencia,
        tipoNegocio: content.data.tipoNegocio,
        tamañoEmpresa: content.data.tamañoEmpresa,
        industria: content.data.industry,

        // ═══════════════════════════════════════════════════════
        // 💡 ANÁLISIS DE NECESIDADES (NUEVOS CAMPOS PROFESIONALES)
        // ═══════════════════════════════════════════════════════
        problemasIdentificados: content.data.problemasIdentificados || [],
        objetivosNegocio: content.data.objetivosNegocio || [],
        beneficiosEsperados: content.data.beneficiosEsperados || [],
        riesgosActuales: content.data.riesgosActuales || [],
        situacionActual: content.data.situacionActual,
        resultadoDeseado: content.data.resultadoDeseado,

        // ═══════════════════════════════════════════════════════
        // 🔧 ESPECIFICACIONES TÉCNICAS
        // ═══════════════════════════════════════════════════════
        objetivosEspecificos: content.data.features, // Features del servicio
        tecnologiasPreferidas: content.data.technologies,
        plataformas: content.data.platforms,
        integraciones: content.data.integrations,
        requisitosEspeciales: content.data.requisitosEspeciales || [],

        // ═══════════════════════════════════════════════════════
        // 📊 MÉTRICAS Y ROI (NUEVOS CAMPOS PROFESIONALES)
        // ═══════════════════════════════════════════════════════
        kpisEsperados: content.data.kpisEsperados || [],
        roiEstimado: content.data.roiEstimado,
        tiempoRecuperacion: content.data.tiempoRecuperacion,

        // ═══════════════════════════════════════════════════════
        // 📦 PROPUESTA Y PAQUETES
        // ═══════════════════════════════════════════════════════
        packages: content.packages,
        totalInversion,
        fecha: new Date().toLocaleDateString('es-PE', {
          day: '2-digit',
          month: 'long',
          year: 'numeric',
        }),
        validez: this.getExpirationDate(),
      };

      // ─────────────────────────────────────────────────────────
      // 3️⃣ GENERAR HTML USANDO EL SERVICIO DE PLANTILLAS
      // ─────────────────────────────────────────────────────────
      const html = quotationHTMLTemplateService.generateHTML(htmlData);
      logger.info('✅ HTML generado exitosamente');

      // ─────────────────────────────────────────────────────────
      // 4️⃣ CONVERTIR HTML A PDF CON PUPPETEER
      // ─────────────────────────────────────────────────────────
      const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
      });

      const page = await browser.newPage();
      await page.setContent(html, { waitUntil: 'networkidle0' });

      // Generar PDF con opciones optimizadas (landscape desde CSS)
      await page.pdf({
        path: filePath,
        printBackground: true, // ⚡ CRÍTICO: Mantener gradientes y colores
        preferCSSPageSize: true, // ✅ Usar configuración del CSS (@page { size: landscape })
        margin: {
          top: '0px',
          right: '0px',
          bottom: '0px',
          left: '0px',
        },
      });

      await browser.close();

      logger.info('✅ PDF profesional creado exitosamente con Puppeteer');
      logger.info(`📁 Ubicación: ${filePath}`);
    } catch (error) {
      logger.error('❌ Error generando PDF con Puppeteer:', error);
      throw error;
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 💰 CALCULAR INVERSIÓN TOTAL - Precio medio/recomendado
   * ═══════════════════════════════════════════════════════════════
   * Usa el paquete MEDIO (profesional) como inversión recomendada
   * en lugar del máximo, para dar presupuestos más realistas
   */
  private calculateTotalInversion(packages: QuotationPackage[]): string {
    if (packages.length === 0) {
      return 'S/ 0';
    }

    // Extraer precios numéricos de todos los paquetes
    const prices = packages.map((pkg) => {
      const priceMatch = pkg.price.match(/[\d,]+/);
      return priceMatch ? parseFloat(priceMatch[0].replace(/,/g, '')) : 0;
    });

    // Usar el precio MEDIO (paquete profesional/estándar)
    // Si hay 3 paquetes: toma el del medio
    // Si hay 2: toma el primero (básico)
    // Si hay 1: toma ese
    let recommendedPrice: number;

    if (packages.length >= 3) {
      // Tomar el paquete del medio (índice 1 para 3 paquetes)
      recommendedPrice = prices[1];
    } else if (packages.length === 2) {
      // Para 2 paquetes, tomar el básico (más conservador)
      recommendedPrice = prices[0];
    } else {
      // Solo 1 paquete
      recommendedPrice = prices[0];
    }

    return `S/ ${recommendedPrice.toLocaleString('es-PE', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    })}`;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 💰 GENERAR COTIZACIÓN PERSONALIZADA BASADA EN DATOS REALES
   * ═══════════════════════════════════════════════════════════════
   * ✅ NUEVA FILOSOFÍA: 100% basado en lo que el cliente DIJO
   * ❌ NO detecta con keywords
   * ✅ USA datos extraídos por el Master AI de la conversación
   *
   * Usa directamente:
   * - data.projectType (app-movil, web, ecommerce, chatbot)
   * - data.features[] (funcionalidades mencionadas)
   * - data.platforms[] (iOS, Android, Web)
   * - data.integrations[] (WhatsApp, MercadoPago, etc)
   * - data.industry (restaurante, salud, educacion, etc)
   * - data.complexity (simple, intermedia, compleja, enterprise)
   */
  private generatePackages(data: QuotationData): QuotationPackage[] {
    logger.info('💰 [QUOTATION] Generando cotización con datos:', {
      projectType: data.projectType,
      industry: data.industry,
      features: data.features?.length || 0,
      platforms: data.platforms?.length || 0,
      integrations: data.integrations?.length || 0,
      complexity: data.complexity,
    });

    // ═══════════════════════════════════════════════════════════════
    // 🎯 USAR DATOS DIRECTOS DEL MASTER AI (prioridad)
    // ═══════════════════════════════════════════════════════════════
    const projectType = data.projectType || data.tipoProyecto?.toLowerCase();
    const projectName = data.projectName || '';
    const industry = data.industry || '';
    const features = data.features || [];
    const platforms = data.platforms || [];
    const integrations = data.integrations || [];
    const specificRequirements = data.specificRequirements || [];
    const complexity = data.complexity || 'intermedia';

    // Fallback a análisis de descripción solo si no hay projectType
    const descripcion = data.descripcionProyecto?.toLowerCase() || '';
    const presupuesto = data.presupuestoEstimado?.toLowerCase() || '';

    // ═══════════════════════════════════════════════════════════════════════════
    // 📱 1. APP MÓVIL - Prioridad #1
    // ═══════════════════════════════════════════════════════════════════════════
    if (projectType === 'app-movil' || projectType?.includes('app') || projectType?.includes('móvil') || projectType?.includes('mobile')) {
      return this.generateMobileAppQuotation(data, {
        projectName,
        industry,
        features,
        platforms,
        integrations,
        specificRequirements,
        complexity
      });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 🛒 2. E-COMMERCE - Prioridad #2
    // ═══════════════════════════════════════════════════════════════════════════
    if (projectType === 'ecommerce' || projectType?.includes('tienda') || projectType?.includes('commerce')) {
      return this.generateEcommerceQuotation(data, {
        projectName,
        industry,
        features,
        platforms,
        integrations,
        specificRequirements,
        complexity
      });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 🌐 3. PÁGINA WEB / LANDING - Prioridad #3
    // ═══════════════════════════════════════════════════════════════════════════
    if (projectType === 'web' || projectType === 'landing' || projectType?.includes('página') || projectType?.includes('sitio')) {
      return this.generateWebsiteQuotation(data, {
        projectName,
        industry,
        features,
        platforms,
        integrations,
        specificRequirements,
        complexity
      });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 🤖 4. CHATBOT - Prioridad #4 (último para no capturar apps con WhatsApp)
    // ═══════════════════════════════════════════════════════════════════════════
    if (projectType === 'chatbot' || projectType?.includes('bot') || projectType?.includes('asistente')) {
      return this.generateChatbotQuotation(data, {
        projectName,
        industry,
        features,
        platforms,
        integrations,
        specificRequirements,
        complexity
      });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 💡 5. FALLBACK - Sistema personalizado genérico
    // ═══════════════════════════════════════════════════════════════════════════
    return this.generateCustomQuotation(data, {
      projectName,
      industry,
      features,
      platforms,
      integrations,
      specificRequirements,
      complexity
    });
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 📱 GENERAR COTIZACIÓN PARA APP MÓVIL
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private generateMobileAppQuotation(
    data: QuotationData,
    details: {
      projectName: string;
      industry: string;
      features: string[];
      platforms: string[];
      integrations: string[];
      specificRequirements: string[];
      complexity: string;
    }
  ): QuotationPackage[] {
    // Precio base según complejidad
    let precioBase = 5000; // Básico por defecto

    if (details.complexity === 'simple') {
      precioBase = 5000;
    } else if (details.complexity === 'intermedia') {
      precioBase = 10000;
    } else if (details.complexity === 'compleja') {
      precioBase = 20000;
    } else if (details.complexity === 'enterprise') {
      precioBase = 35000;
    }

    // Incrementos por plataformas
    const numPlataformas = details.platforms.length;
    if (numPlataformas === 0 || numPlataformas === 2) {
      // Asumimos iOS + Android (híbrida)
      precioBase = precioBase; // Sin cambio, ya contemplado
    } else if (numPlataformas === 1) {
      // Solo una plataforma, reducir 20%
      precioBase = precioBase * 0.8;
    } else if (numPlataformas >= 3) {
      // Más de 2 plataformas (incluye Web, etc)
      precioBase += 5000;
    }

    // Incrementos por funcionalidades específicas
    const featuresLower = details.features.map(f => f.toLowerCase());

    if (featuresLower.some(f => f.includes('pago') || f.includes('pasarela') || f.includes('compra'))) {
      precioBase += 5000;
    }
    if (featuresLower.some(f => f.includes('chat') || f.includes('mensaje') || f.includes('tiempo real'))) {
      precioBase += 3000;
    }
    if (featuresLower.some(f => f.includes('geolocalización') || f.includes('mapa') || f.includes('GPS'))) {
      precioBase += 2000;
    }
    if (featuresLower.some(f => f.includes('video') || f.includes('streaming') || f.includes('llamada'))) {
      precioBase += 4000;
    }
    if (featuresLower.some(f => f.includes('offline') || f.includes('sin conexión'))) {
      precioBase += 2500;
    }

    // Incrementos por integraciones
    if (details.integrations.length > 0) {
      precioBase += details.integrations.length * 1500;
    }

    // Construir lista de features para el PDF
    const packageFeatures: string[] = [];

    // Features base según plataformas
    if (details.platforms.length >= 2 || details.platforms.length === 0) {
      packageFeatures.push('✅ App híbrida Android + iOS (Flutter/React Native)');
    } else {
      packageFeatures.push(`✅ App para ${details.platforms.join(', ')}`);
    }

    // Pantallas según complejidad
    if (details.complexity === 'simple') {
      packageFeatures.push('📱 3-5 pantallas principales');
    } else if (details.complexity === 'intermedia') {
      packageFeatures.push('📱 6-10 pantallas');
    } else {
      packageFeatures.push('📱 Pantallas ilimitadas según necesidad');
    }

    packageFeatures.push('🎨 Diseño UI/UX profesional');

    // Features personalizadas según industry
    if (details.industry === 'restaurante') {
      packageFeatures.push('🍽️ Menú digital interactivo');
      packageFeatures.push('🛵 Sistema de delivery integrado');
    } else if (details.industry === 'salud') {
      packageFeatures.push('🏥 Gestión de citas médicas');
      packageFeatures.push('📋 Historial clínico digital');
    } else if (details.industry === 'educacion') {
      packageFeatures.push('📚 Plataforma de aprendizaje');
      packageFeatures.push('📝 Sistema de evaluaciones');
    }

    // Agregar features mencionadas
    details.features.forEach(feature => {
      if (!packageFeatures.some(f => f.toLowerCase().includes(feature.toLowerCase()))) {
        packageFeatures.push(`⭐ ${feature}`);
      }
    });

    // Agregar integraciones
    details.integrations.forEach(integration => {
      packageFeatures.push(`🔗 Integración con ${integration}`);
    });

    // Agregar requisitos específicos
    details.specificRequirements.forEach(req => {
      packageFeatures.push(`💎 ${req}`);
    });

    // Features técnicas estándar
    if (details.complexity !== 'simple') {
      packageFeatures.push('🔐 Sistema de autenticación');
      packageFeatures.push('☁️ Backend API REST incluido');
      packageFeatures.push('📊 Analytics integrado');
      packageFeatures.push('🔔 Push notifications');
    }

    packageFeatures.push('📱 Publicación en stores');
    packageFeatures.push('✅ Testing QA completo');

    // Soporte y entrega
    const mesesSoporte = details.complexity === 'enterprise' ? 12 : details.complexity === 'compleja' ? 6 : 3;
    const semanasEntrega = details.complexity === 'enterprise' ? '10-16' : details.complexity === 'compleja' ? '8-12' : details.complexity === 'intermedia' ? '6-8' : '4-6';

    packageFeatures.push(`🛠️ ${mesesSoporte} meses de soporte técnico`);
    if (details.complexity !== 'simple') {
      packageFeatures.push('📚 Capacitación incluida');
    }

    return [
      {
        name: details.projectName || `📱 ${data.clientName ? 'Tu' : 'Nuestra'} App Móvil Personalizada`,
        price: `S/ ${precioBase.toLocaleString('es-PE')}`,
        features: packageFeatures,
        deliveryTime: `${semanasEntrega} semanas`,
      },
    ];
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🛒 GENERAR COTIZACIÓN PARA E-COMMERCE
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private generateEcommerceQuotation(
    data: QuotationData,
    details: {
      projectName: string;
      industry: string;
      features: string[];
      platforms: string[];
      integrations: string[];
      specificRequirements: string[];
      complexity: string;
    }
  ): QuotationPackage[] {
    let precioBase = 2500; // E-commerce básico sin pasarela

    const packageFeatures: string[] = [
      '🛍️ Catálogo de productos profesional',
      '🛒 Carrito de compras optimizado',
      '📱 Diseño responsive (móvil, tablet, desktop)',
      '⚙️ Panel de administración completo',
      '🎁 Hosting + Dominio 1 año GRATIS',
      '🔒 Certificado SSL incluido',
      '🚀 SEO básico optimizado',
    ];

    // Verificar si tiene pasarela de pagos
    const tienePagos = details.integrations.some(i =>
      i.toLowerCase().includes('pago') ||
      i.toLowerCase().includes('niubiz') ||
      i.toLowerCase().includes('culqi') ||
      i.toLowerCase().includes('mercadopago') ||
      i.toLowerCase().includes('izipay')
    ) || details.features.some(f =>
      f.toLowerCase().includes('pago') ||
      f.toLowerCase().includes('pasarela')
    );

    if (tienePagos) {
      precioBase = 5000;
      packageFeatures.push('💳 Pasarela de pagos integrada');
      packageFeatures.push('📦 Productos ilimitados');
      packageFeatures.push('🎟️ Sistema de cupones y descuentos');
      packageFeatures.push('📊 Reportes de ventas');
    } else {
      packageFeatures.push('📲 Formulario de pedidos vía WhatsApp');
      packageFeatures.push('📦 Hasta 50 productos');
    }

    // Verificar envíos
    const tieneEnvios = details.features.some(f =>
      f.toLowerCase().includes('envío') ||
      f.toLowerCase().includes('delivery') ||
      f.toLowerCase().includes('shipping')
    );

    if (tieneEnvios) {
      precioBase += 1500;
      packageFeatures.push('🚚 Sistema de envíos automatizado');
    }

    // Si es compleja/enterprise
    if (details.complexity === 'compleja' || details.complexity === 'enterprise') {
      precioBase = Math.max(precioBase, 8500);
      packageFeatures.push('🌍 Multi-moneda y multi-idioma');
      packageFeatures.push('📈 Dashboard de ventas avanzado');
      packageFeatures.push('📱 Integración con WhatsApp Business API');
      packageFeatures.push('🧾 Sistema de facturación electrónica SUNAT');
    }

    // Agregar integraciones específicas
    details.integrations.forEach(integration => {
      if (!packageFeatures.some(f => f.includes(integration))) {
        packageFeatures.push(`🔗 Integración con ${integration}`);
      }
    });

    // Requisitos específicos
    details.specificRequirements.forEach(req => {
      packageFeatures.push(`💎 ${req}`);
    });

    // Soporte
    const mesesSoporte = details.complexity === 'enterprise' ? 12 : tienePagos ? 6 : 3;
    const semanasEntrega = details.complexity === 'enterprise' ? '6-10' : tienePagos ? '4-6' : '3-4';

    packageFeatures.push(`🛠️ ${mesesSoporte} meses de soporte técnico`);
    if (tienePagos) packageFeatures.push('📚 Capacitación incluida');

    return [
      {
        name: details.projectName || `🛒 ${data.clientName ? 'Tu' : 'Nuestra'} Tienda Online Personalizada`,
        price: `S/ ${precioBase.toLocaleString('es-PE')}`,
        features: packageFeatures,
        deliveryTime: `${semanasEntrega} semanas`,
      },
    ];
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🌐 GENERAR COTIZACIÓN PARA PÁGINA WEB
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private generateWebsiteQuotation(
    data: QuotationData,
    details: {
      projectName: string;
      industry: string;
      features: string[];
      platforms: string[];
      integrations: string[];
      specificRequirements: string[];
      complexity: string;
    }
  ): QuotationPackage[] {
    let precioBase = 650; // Web informativa básica

    const packageFeatures: string[] = [
      '🌐 Diseño responsive profesional',
      '📧 Formulario de contacto',
      '📱 Integración redes sociales',
      '🚀 SEO básico optimizado',
      '🎁 Hosting 1 año GRATIS',
      '🌍 Dominio .com/.pe 1 año GRATIS',
      '🔒 Certificado SSL incluido',
    ];

    // Determinar si es landing o web completa
    const esLanding = details.complexity === 'simple' || details.projectName?.toLowerCase().includes('landing');

    if (esLanding) {
      precioBase = 500;
      packageFeatures[0] = '📄 Landing page profesional de 1 página';
      packageFeatures[4] = '☁️ Hosting disponible (costo adicional)';
      packageFeatures[5] = '🌍 Dominio disponible (costo adicional)';
    } else {
      // Determinar número de páginas
      let numeroPaginas = 5;

      if (details.complexity === 'intermedia') {
        numeroPaginas = 8;
        precioBase = 1200;
      } else if (details.complexity === 'compleja') {
        numeroPaginas = 15;
        precioBase = 2000;
      } else if (details.complexity === 'enterprise') {
        precioBase = 3500;
        packageFeatures.unshift('🏢 Sitio web corporativo de alto nivel');
      } else {
        packageFeatures.unshift(`📄 Hasta ${numeroPaginas} páginas`);
      }
    }

    // Features según industry
    if (details.industry === 'restaurante') {
      packageFeatures.push('🍽️ Menú digital interactivo');
      packageFeatures.push('📍 Ubicación con Google Maps');
    } else if (details.industry === 'salud') {
      packageFeatures.push('🏥 Sistema de citas online');
      packageFeatures.push('👨‍⚕️ Perfiles de profesionales');
    } else if (details.industry === 'educacion') {
      packageFeatures.push('📚 Portal de cursos');
      packageFeatures.push('👨‍🎓 Área de estudiantes');
    }

    // Agregar features mencionadas
    details.features.forEach(feature => {
      packageFeatures.push(`⭐ ${feature}`);
    });

    // Agregar integraciones
    details.integrations.forEach(integration => {
      packageFeatures.push(`🔗 Integración con ${integration}`);
    });

    // Requisitos específicos
    details.specificRequirements.forEach(req => {
      packageFeatures.push(`💎 ${req}`);
    });

    // Soporte
    const mesesSoporte = details.complexity === 'enterprise' ? 6 : precioBase >= 1200 ? 4 : 2;
    const semanasEntrega = details.complexity === 'enterprise' ? '5-7' : precioBase >= 1200 ? '3-4' : esLanding ? '1-2' : '2-3';

    packageFeatures.push(`🛠️ ${mesesSoporte} meses de soporte técnico`);
    if (precioBase >= 1200) packageFeatures.push('📚 Capacitación incluida');

    return [
      {
        name: details.projectName || `🌐 ${data.clientName ? 'Tu' : 'Nuestro'} Sitio Web Personalizado`,
        price: `S/ ${precioBase.toLocaleString('es-PE')}`,
        features: packageFeatures,
        deliveryTime: `${semanasEntrega} semanas`,
      },
    ];
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🤖 GENERAR COTIZACIÓN PARA CHATBOT
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private generateChatbotQuotation(
    data: QuotationData,
    details: {
      projectName: string;
      industry: string;
      features: string[];
      platforms: string[];
      integrations: string[];
      specificRequirements: string[];
      complexity: string;
    }
  ): QuotationPackage[] {
    const precioSetup = 350; // Setup inicial
    const precioMensual = 89.90; // Mensualidad

    const packageFeatures: string[] = [
      '🤖 Chatbot con IA conversacional',
      '💬 Respuestas automáticas 24/7',
      '🎯 Entrenamiento personalizado para tu negocio',
      '📊 Dashboard de métricas',
      '🔄 Actualizaciones de contenido',
      '🛠️ Soporte técnico continuo',
    ];

    // Plataformas
    if (details.platforms.length > 0) {
      details.platforms.forEach(platform => {
        packageFeatures.push(`📱 Integración con ${platform}`);
      });
    } else {
      packageFeatures.push('📱 WhatsApp Business API');
    }

    // Features según industry
    if (details.industry === 'restaurante') {
      packageFeatures.push('🍽️ Automatización de pedidos');
      packageFeatures.push('📅 Reservas automáticas');
    } else if (details.industry === 'retail') {
      packageFeatures.push('🛍️ Catálogo de productos');
      packageFeatures.push('💳 Procesamiento de órdenes');
    } else if (details.industry === 'salud') {
      packageFeatures.push('🏥 Agendamiento de citas');
      packageFeatures.push('📋 Pre-consultas automatizadas');
    }

    // Agregar features mencionadas
    details.features.forEach(feature => {
      packageFeatures.push(`⭐ ${feature}`);
    });

    // Integrations
    details.integrations.forEach(integration => {
      if (!packageFeatures.some(f => f.includes(integration))) {
        packageFeatures.push(`🔗 ${integration}`);
      }
    });

    // Requisitos específicos
    details.specificRequirements.forEach(req => {
      packageFeatures.push(`💎 ${req}`);
    });

    return [
      {
        name: details.projectName || `🤖 ${data.clientName ? 'Tu' : 'Nuestro'} Chatbot IA Personalizado`,
        price: `S/ ${precioSetup} + S/ ${precioMensual}/mes`,
        features: packageFeatures,
        deliveryTime: '1-2 semanas',
      },
    ];
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 🔍 MAPEAR PROJECT TYPE A SERVICE ID
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private mapProjectTypeToServiceId(projectType?: string, descripcion?: string): string {
    if (!projectType && !descripcion) return 'software-medida';

    const type = (projectType || '').toLowerCase();
    const desc = (descripcion || '').toLowerCase();
    const combined = `${type} ${desc}`;

    // Marketing y redes sociales
    if (combined.includes('marketing') || combined.includes('redes sociales') ||
        combined.includes('social media') || combined.includes('facebook') ||
        combined.includes('instagram')) {
      return 'seo-marketing';
    }

    // Chatbot
    if (combined.includes('chatbot') || combined.includes('bot') || combined.includes('whatsapp')) {
      return 'chatbot-ai';
    }

    // Apps móviles
    if (combined.includes('app') || combined.includes('movil') || combined.includes('mobile')) {
      return 'apps-moviles';
    }

    // Páginas web
    if (combined.includes('web') || combined.includes('pagina') || combined.includes('sitio') ||
        combined.includes('landing')) {
      return 'paginas-web';
    }

    // E-commerce
    if (combined.includes('tienda') || combined.includes('ecommerce') || combined.includes('e-commerce')) {
      return 'paginas-web'; // E-commerce usa pricing de web
    }

    // Campañas publicitarias
    if (combined.includes('campaña') || combined.includes('publicidad') || combined.includes('ads')) {
      return 'campañas-publicitarias';
    }

    // Analítica
    if (combined.includes('analitica') || combined.includes('analytics') || combined.includes('dashboard')) {
      return 'analitica-datos';
    }

    // Email marketing
    if (combined.includes('email') || combined.includes('newsletter')) {
      return 'email-marketing';
    }

    // Por defecto: software a medida
    return 'software-medida';
  }

  /**
   * ═══════════════════════════════════════════════════════════════════════════
   * 💡 GENERAR COTIZACIÓN PERSONALIZADA (CON PRICING LOGIC SERVICE)
   * ═══════════════════════════════════════════════════════════════════════════
   */
  private generateCustomQuotation(
    data: QuotationData,
    details: {
      projectName: string;
      industry: string;
      features: string[];
      platforms: string[];
      integrations: string[];
      specificRequirements: string[];
      complexity: string;
    }
  ): QuotationPackage[] {
    // ✅ USAR PRICING LOGIC SERVICE REAL
    const serviceId = this.mapProjectTypeToServiceId(data.projectType, data.descripcionProyecto);

    const complexity = (details.complexity || 'intermedia') as 'simple' | 'intermedia' | 'compleja' | 'enterprise';

    // Calcular precio usando el servicio real de pricing
    const pricingResult = pricingLogicService.calculatePrice({
      serviceId,
      complexity,
      features: details.features,
      integrations: details.integrations,
      platforms: details.platforms,
    });

    logger.info(`💰 [PRICING] Calculado con pricing-logic.service:`, {
      serviceId,
      complexity,
      precioMin: pricingResult.rango.minimo,
      precioMax: pricingResult.rango.maximo,
      precioFinal: pricingResult.precioFinal,
    });

    // Usar el precio calculado (mostrar rango)
    const precioMinFormatted = pricingResult.rango.minimo.toLocaleString('es-PE');
    const precioMaxFormatted = pricingResult.rango.maximo.toLocaleString('es-PE');
    const precioDisplay = pricingResult.rango.minimo === pricingResult.rango.maximo
      ? `S/ ${precioMinFormatted}`
      : `S/ ${precioMinFormatted} - S/ ${precioMaxFormatted}`;

    // Construir lista de features
    const packageFeatures: string[] = [
      '💻 Solución 100% personalizada',
      '🎨 Diseño profesional',
      '✅ Testing y QA completo',
      '📚 Documentación técnica',
    ];

    // Agregar recomendaciones del pricing service
    if (pricingResult.recomendaciones && pricingResult.recomendaciones.length > 0) {
      pricingResult.recomendaciones.forEach(rec => {
        packageFeatures.push(`💡 ${rec}`);
      });
    }

    // Agregar features específicos del cliente
    if (details.features.length > 0) {
      details.features.forEach(feature => {
        packageFeatures.push(`⭐ ${feature}`);
      });
    }

    // Agregar integraciones
    if (details.integrations.length > 0) {
      details.integrations.forEach(integration => {
        packageFeatures.push(`🔗 Integración con ${integration}`);
      });
    }

    // Requisitos específicos
    if (details.specificRequirements.length > 0) {
      details.specificRequirements.forEach(req => {
        packageFeatures.push(`💎 ${req}`);
      });
    }

    // Determinar tiempo de entrega según complejidad
    const deliveryWeeks = {
      'simple': '2-4',
      'intermedia': '4-8',
      'compleja': '8-12',
      'enterprise': '12-20',
    }[complexity] || '4-8';

    return [
      {
        name: pricingResult.servicio.nombre || details.projectName || `💡 ${data.clientName ? 'Tu' : 'Nuestra'} Solución Personalizada`,
        price: precioDisplay,
        features: packageFeatures,
        deliveryTime: `${deliveryWeeks} semanas`,
      },
    ];
  }


  /**
   * ═══════════════════════════════════════════════════════════════
   * 📧 ENVIAR PDF POR EMAIL
   * ═══════════════════════════════════════════════════════════════
   */
  private async sendEmail(data: QuotationData, pdfPath: string, pdfUrl: string): Promise<void> {
    try {
      // Configurar transporter de nodemailer
      const transporter = nodemailer.createTransport({
        host: process.env.SMTP_HOST || 'smtp.gmail.com',
        port: parseInt(process.env.SMTP_PORT || '587'),
        secure: false,
        auth: {
          user: process.env.SMTP_USER,
          pass: process.env.SMTP_PASS,
        },
      });

      const mailOptions = {
        from: `"NYNEL MKT" <${process.env.SMTP_USER}>`,
        to: data.clientEmail,
        subject: `Cotización de ${data.tipoProyecto || 'Proyecto'} - NYNEL MKT`,
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #0066CC; padding: 20px; text-align: center;">
              <h1 style="color: white; margin: 0;">NYNEL MKT</h1>
              <p style="color: #E0E0E0; margin: 5px 0;">Marketing Digital & Desarrollo de Software</p>
            </div>

            <div style="padding: 30px; background-color: #f9f9f9;">
              <h2 style="color: #333;">¡Hola ${data.clientName}! 👋</h2>

              <p style="color: #666; line-height: 1.6;">
                Gracias por tu interés en nuestros servicios. Adjunto encontrarás nuestra cotización
                personalizada para tu proyecto de <strong>${data.tipoProyecto || 'desarrollo'}</strong>.
              </p>

              <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: #0066CC; margin-top: 0;">📋 Detalles de tu Cotización</h3>
                <ul style="color: #666; line-height: 1.8;">
                  <li>3 paquetes disponibles para elegir</li>
                  <li>Precios competitivos del mercado</li>
                  <li>Soporte técnico incluido</li>
                  <li>Garantía de calidad</li>
                </ul>
              </div>

              <p style="color: #666; line-height: 1.6;">
                También puedes descargar tu cotización directamente desde este enlace:<br>
                <a href="${pdfUrl}" style="color: #0066CC; font-weight: bold;">📄 Ver Cotización Online</a>
              </p>

              <p style="color: #666; line-height: 1.6;">
                Si tienes alguna pregunta o deseas agendar una reunión, no dudes en contactarme.
                ¡Estoy aquí para ayudarte! 🚀
              </p>

              <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px;">
                <p>Cotización válida por 15 días</p>
                <p>NYNEL MKT - Transformando ideas en realidad digital</p>
              </div>
            </div>
          </div>
        `,
        attachments: [
          {
            filename: path.basename(pdfPath),
            path: pdfPath,
          },
        ],
      };

      await transporter.sendMail(mailOptions);
      logger.info(`✅ Email enviado a ${data.clientEmail}`);
    } catch (error: any) {
      logger.error('❌ Error enviando email:', error);
      // No lanzar error para no bloquear el flujo si el email falla
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📱 ENVIAR LINK POR WHATSAPP
   * ═══════════════════════════════════════════════════════════════
   */
  private async sendWhatsAppLink(
    subscriberId: string,
    pdfUrl: string,
    quotationCode: string
  ): Promise<void> {
    try {
      const message = `📄 *Tu Cotización Está Lista*

Hola! Te comparto tu cotización personalizada con 3 opciones para que elijas la que mejor se ajuste a tus necesidades:

🔗 *Ver cotización:*
${pdfUrl}

📋 *Código:* ${quotationCode}
⏰ *Válida hasta:* ${this.getExpirationDate()}

¿Tienes alguna pregunta sobre los paquetes? ¡Estoy aquí para ayudarte! 🚀`;

      await manyChatAPI.sendTextMessage(subscriberId, message);
      logger.info('✅ Link de cotización enviado por WhatsApp');
    } catch (error: any) {
      logger.error('❌ Error enviando link por WhatsApp:', error);
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 💾 GUARDAR COTIZACIÓN EN BASE DE DATOS
   * ═══════════════════════════════════════════════════════════════
   */
  private async saveQuotationToDB(params: {
    quotationCode: string;
    subscriberId: string;
    pdfUrl: string;
    data: QuotationData;
    packages: QuotationPackage[];
  }): Promise<void> {
    try {
      logger.info(`💾 Guardando cotización ${params.quotationCode} en base de datos...`);

      // ═══════════════════════════════════════════════════════════════
      // 1. Asegurar que existe servicio genérico (crear si no existe)
      // ═══════════════════════════════════════════════════════════════
      const service = await prisma.service.upsert({
        where: { slug: 'cotizacion-personalizada' },
        update: {},
        create: {
          name: 'Cotización Personalizada',
          slug: 'cotizacion-personalizada',
          description: 'Servicio de cotización personalizada generado automáticamente por el sistema de IA',
          category: 'SOFTWARE_DEVELOPMENT',
          priceMin: 500,
          priceMax: 50000,
          currency: 'PEN',
          billingType: 'ONE_TIME',
          features: [
            'Cotización personalizada según requisitos',
            'Asesoría inicial gratuita',
            'Propuesta detallada con 3 paquetes',
            'Soporte técnico incluido'
          ],
          keywords: ['cotizacion', 'personalizado', 'desarrollo', 'software'],
          isActive: true,
          displayOrder: 0
        }
      });

      // ═══════════════════════════════════════════════════════════════
      // 2. Mapear complejidad desde datos disponibles
      // ═══════════════════════════════════════════════════════════════
      const complexity = this.mapComplexity(params.data.tipoProyecto, params.data.descripcionProyecto);
      const urgency = this.mapUrgency(params.data.urgencia);
      const companySize = this.mapCompanySize(params.data.empresa);

      // ═══════════════════════════════════════════════════════════════
      // 3. Extraer precios de los paquetes
      // ═══════════════════════════════════════════════════════════════
      const prices = params.packages.map(pkg =>
        parseFloat(pkg.price.replace(/[^0-9.]/g, ''))
      );
      const priceMin = Math.min(...prices);
      const priceMax = Math.max(...prices);
      const priceAverage = prices.reduce((a, b) => a + b, 0) / prices.length;

      // ═══════════════════════════════════════════════════════════════
      // 4. Calcular fecha de validez (15 días)
      // ═══════════════════════════════════════════════════════════════
      const validUntil = new Date();
      validUntil.setDate(validUntil.getDate() + 15);

      // ═══════════════════════════════════════════════════════════════
      // 5. Guardar cotización en base de datos
      // ═══════════════════════════════════════════════════════════════
      await prisma.quotation.create({
        data: {
          quotationCode: params.quotationCode,
          subscriberId: params.subscriberId,
          serviceId: service.id,
          serviceName: params.data.tipoProyecto || 'Servicio Personalizado',
          priceMin,
          priceMax,
          priceAverage,
          currency: 'PEN',
          complexity,
          urgency,
          companySize,
          discount: null,
          packages: params.packages as any, // JSON de paquetes (cast para Prisma Json type)
          description: params.data.descripcionProyecto || 'Cotización personalizada',
          includes: params.packages[0]?.features || [],
          deliveryTime: params.packages[0]?.deliveryTime || 'Por definir',
          terms: 'Cotización válida por 15 días. Precios sujetos a confirmación de requisitos.',
          status: 'SENT',
          validUntil,
          pdfUrl: params.pdfUrl,
          pdfGeneratedAt: new Date()
        }
      });

      logger.info(`✅ Cotización ${params.quotationCode} guardada exitosamente en BD`);

      // ═══════════════════════════════════════════════════════════════
      // 📧 ENVIAR NOTIFICACIÓN AL EQUIPO POR EMAIL
      // ═══════════════════════════════════════════════════════════════
      try {
        // Obtener datos del subscriber para la notificación
        const subscriber = await prisma.subscriber.findUnique({
          where: { id: params.subscriberId },
          select: {
            firstName: true,
            lastName: true,
            phone: true,
            email: true,
          }
        });

        if (subscriber) {
          await emailNotificationService.notifyNewQuotation({
            quotationCode: params.quotationCode,
            clientName: `${subscriber.firstName} ${subscriber.lastName || ''}`.trim(),
            clientPhone: subscriber.phone || params.data.clientName,
            clientEmail: subscriber.email || params.data.clientEmail || '',
            serviceName: params.data.tipoProyecto || 'Servicio Personalizado',
            priceMin,
            priceMax,
            pdfUrl: params.pdfUrl,
          });
        }
      } catch (notificationError: any) {
        logger.error('❌ Error enviando notificación de cotización:', notificationError);
        // No interrumpir el flujo principal
      }

    } catch (error: any) {
      logger.error('❌ Error guardando cotización en BD:', error);
      // No lanzar error para no interrumpir el flujo - PDF ya se generó
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎯 MAPEAR COMPLEJIDAD DESDE DESCRIPCIÓN
   * ═══════════════════════════════════════════════════════════════
   */
  private mapComplexity(tipoProyecto?: string, descripcion?: string): 'LOW' | 'MEDIUM' | 'HIGH' | 'VERY_HIGH' | 'ENTERPRISE' {
    const text = `${tipoProyecto} ${descripcion}`.toLowerCase();

    if (text.includes('enterprise') || text.includes('gran empresa') || text.includes('corporativo')) {
      return 'ENTERPRISE';
    }
    if (text.includes('muy complejo') || text.includes('alta complejidad') || text.includes('avanzado')) {
      return 'VERY_HIGH';
    }
    if (text.includes('complejo') || text.includes('profesional') || text.includes('múltiples')) {
      return 'HIGH';
    }
    if (text.includes('simple') || text.includes('básico') || text.includes('sencillo')) {
      return 'LOW';
    }
    return 'MEDIUM'; // Default
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * ⚡ MAPEAR URGENCIA
   * ═══════════════════════════════════════════════════════════════
   */
  private mapUrgency(urgencia?: string): 'NORMAL' | 'PRIORITY' | 'URGENT' {
    if (!urgencia) return 'NORMAL';

    const text = urgencia.toLowerCase();
    if (text.includes('urgent') || text.includes('inmediato') || text.includes('ya')) {
      return 'URGENT';
    }
    if (text.includes('prioridad') || text.includes('rápido') || text.includes('pronto')) {
      return 'PRIORITY';
    }
    return 'NORMAL';
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🏢 MAPEAR TAMAÑO DE EMPRESA
   * ═══════════════════════════════════════════════════════════════
   */
  private mapCompanySize(empresa?: string): 'MICRO' | 'SMALL' | 'MEDIUM' | 'LARGE' {
    if (!empresa) return 'MICRO'; // Default para emprendedores

    const text = empresa.toLowerCase();
    if (text.includes('grande') || text.includes('corporación') || text.includes('multinacional')) {
      return 'LARGE';
    }
    if (text.includes('mediana') || text.includes('pyme')) {
      return 'MEDIUM';
    }
    if (text.includes('pequeña') || text.includes('startup')) {
      return 'SMALL';
    }
    return 'MICRO';
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📧 REENVIAR COTIZACIÓN EXISTENTE POR EMAIL
   * ═══════════════════════════════════════════════════════════════
   * Busca una cotización por su código y la reenvía al email especificado
   */
  async resendQuotation(quotationCode: string, recipientEmail: string): Promise<{ success: boolean; message: string }> {
    try {
      logger.info(`📧 Reenviando cotización ${quotationCode} a ${recipientEmail}...`);

      // Buscar cotización en base de datos
      const quotation = await prisma.quotation.findUnique({
        where: { quotationCode },
        include: {
          subscriber: {
            select: {
              firstName: true,
              lastName: true,
            }
          }
        }
      });

      if (!quotation) {
        logger.warn(`⚠️ Cotización ${quotationCode} no encontrada`);
        return {
          success: false,
          message: `Cotización ${quotationCode} no encontrada en el sistema`
        };
      }

      // Verificar que existe el archivo PDF
      const pdfFilename = quotation.quotationCode + '.pdf';
      const pdfPath = path.join(process.cwd(), 'public', 'quotations', pdfFilename);

      if (!fs.existsSync(pdfPath)) {
        logger.error(`❌ PDF no encontrado en: ${pdfPath}`);
        return {
          success: false,
          message: `El PDF de la cotización ${quotationCode} no está disponible`
        };
      }

      // Preparar datos del cliente
      const clientName = `${quotation.subscriber.firstName} ${quotation.subscriber.lastName || ''}`.trim();
      const pdfUrl = quotation.pdfUrl;

      // Crear transporter
      const transporter = nodemailer.createTransport({
        host: process.env.SMTP_HOST,
        port: parseInt(process.env.SMTP_PORT || '587'),
        secure: process.env.SMTP_SECURE === 'true',
        auth: {
          user: process.env.SMTP_USER,
          pass: process.env.SMTP_PASS,
        },
      });

      // Generar HTML del email
      const htmlContent = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
    .header h1 { margin: 0; font-size: 28px; }
    .content { background: #f9f9f9; padding: 30px 20px; }
    .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>💼 Tu Cotización Personalizada</h1>
      <p style="margin: 10px 0 0 0;">Código: ${quotation.quotationCode}</p>
    </div>
    <div class="content">
      <p>Hola <strong>${clientName}</strong>,</p>
      <p>Te reenviamos la cotización que solicitaste para <strong>${quotation.serviceName}</strong>.</p>
      <p style="text-align: center;">
        <a href="${pdfUrl}" class="button">📄 Ver Cotización Online</a>
      </p>
      <p style="font-size: 12px; color: #666;">También puedes descargar el PDF adjunto a este correo.</p>
    </div>
    <div class="footer">
      <p><strong>NYNEL MKT</strong></p>
      <p>Desarrollo de Software Profesional</p>
    </div>
  </div>
</body>
</html>
      `;

      // Enviar email
      await transporter.sendMail({
        from: `"${process.env.SMTP_FROM_NAME}" <${process.env.SMTP_FROM_EMAIL}>`,
        to: recipientEmail,
        subject: `Reenvío: Cotización ${quotation.quotationCode} - NYNEL MKT`,
        html: htmlContent,
        attachments: [
          {
            filename: pdfFilename,
            path: pdfPath,
            contentType: 'application/pdf',
          },
        ],
      });

      logger.info(`✅ Cotización ${quotationCode} reenviada exitosamente a ${recipientEmail}`);
      return {
        success: true,
        message: `Cotización ${quotationCode} reenviada exitosamente a ${recipientEmail}`
      };

    } catch (error: any) {
      logger.error('❌ Error reenviando cotización:', error);
      return {
        success: false,
        message: `Error al reenviar cotización: ${error.message}`
      };
    }
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🔢 GENERAR CÓDIGO ÚNICO DE COTIZACIÓN
   * ═══════════════════════════════════════════════════════════════
   */
  private generateQuotationCode(): string {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const random = Math.floor(Math.random() * 1000)
      .toString()
      .padStart(3, '0');

    return `COT-${year}${month}-${random}`;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📅 OBTENER FECHA DE EXPIRACIÓN (15 DÍAS)
   * ═══════════════════════════════════════════════════════════════
   */
  private getExpirationDate(): string {
    const date = new Date();
    date.setDate(date.getDate() + 15);
    return date.toLocaleDateString('es-PE', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    });
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// EXPORTAR INSTANCIA ÚNICA (SINGLETON)
// ═══════════════════════════════════════════════════════════════════════════
export const quotationService = new QuotationService();
