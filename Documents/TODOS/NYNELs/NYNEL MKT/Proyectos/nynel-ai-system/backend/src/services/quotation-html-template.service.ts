// ═══════════════════════════════════════════════════════════════════════════
// 📄 SERVICIO DE PLANTILLA HTML PARA COTIZACIONES
// ═══════════════════════════════════════════════════════════════════════════
// Genera el HTML profesional para convertir a PDF con Puppeteer
// Basado en la plantilla de referencia 'cotizacion-app-todo-en-uno'
// ═══════════════════════════════════════════════════════════════════════════

import { logger } from '../utils/logger.js';
import fs from 'fs';
import path from 'path';

interface QuotationHtmlData {
  // ═══════════════════════════════════════════════════════════════
  // 📋 DATOS BÁSICOS DEL CLIENTE
  // ═══════════════════════════════════════════════════════════════
  quotationCode: string;
  clientName: string;
  empresa?: string;
  clientEmail?: string;
  clientPhone?: string;
  fecha: string;
  validez: string;

  // ═══════════════════════════════════════════════════════════════
  // 🎯 CONTEXTO DEL NEGOCIO Y PROYECTO
  // ═══════════════════════════════════════════════════════════════
  tipoProyecto?: string;
  descripcionProyecto?: string;
  presupuestoEstimado?: string;
  urgencia?: string;
  tipoNegocio?: string;
  tamañoEmpresa?: string;
  industria?: string;

  // ═══════════════════════════════════════════════════════════════
  // 💡 ANÁLISIS DE NECESIDADES (PERSONALIZADO)
  // ═══════════════════════════════════════════════════════════════
  problemasIdentificados?: string[]; // Pain points del cliente
  objetivosNegocio?: string[]; // Business goals
  beneficiosEsperados?: string[]; // Expected benefits
  riesgosActuales?: string[]; // Risks if not implementing
  situacionActual?: string; // Current situation description
  resultadoDeseado?: string; // Desired outcome

  // ═══════════════════════════════════════════════════════════════
  // 🔧 ESPECIFICACIONES TÉCNICAS
  // ═══════════════════════════════════════════════════════════════
  objetivosEspecificos?: string[]; // Specific objectives
  tecnologiasPreferidas?: string[]; // Preferred technologies
  plataformas?: string[]; // Target platforms
  integraciones?: string[]; // Required integrations
  requisitosEspeciales?: string[]; // Special requirements

  // ═══════════════════════════════════════════════════════════════
  // 📦 PROPUESTA Y PAQUETES
  // ═══════════════════════════════════════════════════════════════
  packages: Array<{
    name: string;
    price: string;
    features: string[];
    deliveryTime: string;
  }>;
  totalInversion: string;

  // ═══════════════════════════════════════════════════════════════
  // 📊 MÉTRICAS Y ROI (OPCIONALES)
  // ═══════════════════════════════════════════════════════════════
  kpisEsperados?: string[]; // Expected KPIs
  roiEstimado?: string; // Estimated ROI
  tiempoRecuperacion?: string; // Payback period
}

class QuotationHTMLTemplateService {
  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎨 GENERAR HTML COMPLETO PARA PDF CON ESTRUCTURA DE REFERENCIA
   * ═══════════════════════════════════════════════════════════════
   * Usa la plantilla completa de 18 slides profesionales
   */
  generateHTML(data: QuotationHtmlData): string {
    // Extraer features principales de los paquetes para los slides modulares
    const allFeatures = this.extractAllFeatures(data.packages);

    // Obtener logo en base64
    const logoBase64 = this.getLogoBase64();

    return `<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COTIZACIÓN ${data.quotationCode} | NYNEL MKT</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #fff;
        }

        /* Slide Container */
        .slide {
            width: 100vw;
            min-height: 100vh;
            padding: 30px 50px;
            background: #fff;
            page-break-after: always;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .slide:last-child {
            page-break-after: auto;
        }

        /* Slide Headers */
        .slide-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .slide-header h2 {
            font-size: 36px;
            color: #2196F3;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .slide-header p {
            font-size: 18px;
            color: #666;
        }

        /* Slide 1 - Portada */
        .slide-1 {
            background: linear-gradient(135deg, #2196F3 0%, #00BCD4 100%);
            color: white;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .slide-1 h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .slide-1 .subtitle {
            font-size: 24px;
            font-weight: 300;
            margin-bottom: 30px;
        }

        .client-box {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 30px 50px;
            border-radius: 20px;
            margin-top: 30px;
            color: #333;
        }

        .client-box p {
            font-size: 16px;
            margin: 8px 0;
        }

        /* Feature grid */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
            width: 100%;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #2196F3;
        }

        .feature-card h4 {
            color: #2196F3;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .feature-card ul {
            list-style: none;
            padding: 0;
        }

        .feature-card li {
            padding: 5px 0;
            font-size: 13px;
            padding-left: 25px;
            position: relative;
            line-height: 1.4;
        }

        .feature-card li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4caf50;
            font-weight: bold;
            top: 5px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .data-table th {
            background: #2196F3;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Price displays */
        .price-highlight {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 20px 40px;
            border-radius: 20px;
            display: inline-block;
            font-size: 32px;
            font-weight: 800;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin: 20px 0;
        }

        /* Payment boxes */
        .payment-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border: 2px solid #e0e0e0;
        }

        .payment-amount {
            font-size: 36px;
            font-weight: 800;
            color: #2196F3;
            margin: 10px 0;
        }

        /* Server box */
        .server-box {
            background: #E3F2FD;
            border: 2px solid #2196F3;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            text-align: center;
        }

        /* Module icons grid */
        .module-icon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
            width: 100%;
        }

        .module-icon-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .module-icon {
            font-size: 32px;
            margin-bottom: 10px;
            line-height: 1;
        }

        /* Module boxes */
        .module-box {
            background: linear-gradient(135deg, #2196F3 0%, #00BCD4 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .module-box h3 {
            font-size: 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .module-content {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Timeline */
        .timeline-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            width: 100%;
        }

        .timeline-item {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            flex: 1;
            margin: 0 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .timeline-number {
            background: #2196F3;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px;
        }

        /* Utility classes */
        .text-center { text-align: center; }
        .compact-content {
            font-size: 12px;
            line-height: 1.4;
        }

        /* Print styles */
        @media print {
            @page {
                size: A3 landscape; /* 📐 Formato A3 horizontal como solicitó el usuario */
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .slide {
                page-break-after: always;
                page-break-inside: avoid;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 🎯 ESTRUCTURA PROFESIONAL 2025 - BASADA EN MEJORES PRÁCTICAS -->
    <!-- ═══════════════════════════════════════════════════════════════ -->

    <!-- 1️⃣ PORTADA -->
    <div class="slide slide-1">
        ${logoBase64 ? `<img src="${logoBase64}" alt="NYNEL MKT Logo" style="max-width: 250px; height: auto; margin: 0 auto 30px auto; display: block;" />` : ''}
        <h1>📱 ${data.tipoProyecto || 'PROYECTO PERSONALIZADO'}</h1>
        <div class="client-box">
            <p><strong>Cliente:</strong> ${data.clientName}</p>
            ${data.empresa ? `<p><strong>Empresa:</strong> ${data.empresa}</p>` : ''}
            ${data.clientEmail ? `<p><strong>Email:</strong> ${data.clientEmail}</p>` : ''}
            ${data.clientPhone ? `<p><strong>Teléfono:</strong> ${data.clientPhone}</p>` : ''}
            <p><strong>Código:</strong> ${data.quotationCode}</p>
            <p><strong>Presentado por:</strong> NYNEL MKT</p>
            <p><strong>RUC:</strong> 20613930931</p>
            <p><strong>Fecha:</strong> ${data.fecha}</p>
            <p><strong>Validez:</strong> ${data.validez}</p>
            <p><strong>Inversión Total:</strong> ${data.totalInversion}</p>
        </div>
    </div>

    <!-- 2️⃣ RESUMEN EJECUTIVO (⭐ NUEVO - CRÍTICO) -->
    ${this.generateExecutiveSummary(data)}

    <!-- 3️⃣ DETALLES DEL PROYECTO -->
    ${this.generateProjectDetailsSlide(data)}

    <!-- 4️⃣ ANÁLISIS DE NECESIDADES (⭐ NUEVO - PAIN POINTS) -->
    ${this.generateNeedsAnalysis(data)}

    <!-- 5️⃣ OBJETIVOS Y REQUISITOS DEL CLIENTE -->
    ${this.generateObjectivesSlide(data)}

    <!-- 6️⃣ SOLUCIÓN PROPUESTA - ESPECIFICACIONES TÉCNICAS -->
    ${this.generateTechnicalSpecsSlide(data)}

    <!-- 7️⃣ ALCANCE Y ENTREGABLES - MÓDULOS PRINCIPALES -->
    ${this.generateModulesSlide(allFeatures)}

    <!-- 8️⃣ ALCANCE DETALLADO - PAQUETES -->
    ${this.generatePackageSlides(data.packages)}

    <!-- 9️⃣ METODOLOGÍA DE TRABAJO (⭐ NUEVO - PROCESO) -->
    ${this.generateMethodology(data)}

    <!-- 🔟 CRONOGRAMA DE DESARROLLO -->
    ${this.generateTimelineSlide(data.packages[0]?.deliveryTime || '4 semanas')}

    <!-- 1️⃣1️⃣ ROI Y BENEFICIOS MEDIBLES (⭐ NUEVO - KPIs) -->
    ${this.generateROIBenefits(data)}

    <!-- 1️⃣2️⃣ BENEFICIOS DEL SISTEMA -->
    ${this.generateBenefitsSlide()}

    <!-- 1️⃣3️⃣ INVERSIÓN TOTAL -->
    <div class="slide">
        <div class="slide-header">
            <h2>💎 INVERSIÓN TOTAL</h2>
            <p>${data.tipoProyecto || 'Tu Solución Digital'} Completa</p>
        </div>
        <div class="text-center">
            <div class="price-highlight">${data.totalInversion}</div>
            <p style="font-size: 20px; margin-bottom: 30px; color: #666;">Precio único de desarrollo</p>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Componente</th>
                    <th>Descripción</th>
                    <th>Incluido</th>
                </tr>
            </thead>
            <tbody>
                ${this.generateIncludedFeatures(data.packages)}
            </tbody>
        </table>
    </div>

    <!-- 1️⃣4️⃣ FORMA DE PAGO -->
    <div class="slide">
        <div class="slide-header">
            <h2>💳 FORMA DE PAGO</h2>
            <p>Plan de pagos flexible para su proyecto</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin: 40px 0; width: 100%;">
            <div class="payment-box">
                <h3>50% al inicio</h3>
                <div class="payment-amount">${this.calculate50Percent(data.totalInversion)}</div>
                <p>Para comenzar el desarrollo</p>
            </div>
            <div class="payment-box">
                <h3>50% a la entrega</h3>
                <div class="payment-amount">${this.calculate50Percent(data.totalInversion)}</div>
                <p>Al finalizar el proyecto</p>
            </div>
        </div>

        <div class="server-box">
            <h3 style="color: #2196F3;">☁️ INFORMACIÓN ADICIONAL</h3>
            <p style="margin: 10px 0;">Cotización personalizada según tus necesidades</p>
            <p style="font-size: 14px; color: #666;">Todos los precios están en Soles (PEN)</p>
        </div>
    </div>

    <!-- 1️⃣5️⃣ VENTAJAS COMPETITIVAS -->
    ${this.generateCompetitiveAdvantagesSlide()}

    <!-- 1️⃣6️⃣ CASOS DE ÉXITO RELEVANTES -->
    ${this.generateSuccessCasesSlide()}

    <!-- Slide 18: Contacto -->
    <div class="slide" style="background: linear-gradient(135deg, #2196F3 0%, #00BCD4 100%); color: white;">
        <div style="text-align: center;">
            <h2 style="color: white; font-size: 42px; margin-bottom: 20px;">🚀 TRANSFORMEMOS TU NEGOCIO</h2>
            <p style="font-size: 24px; margin-bottom: 40px;">${data.tipoProyecto || 'Solución Digital'} Profesional</p>

            <div style="background: rgba(255,255,255,0.95); color: #333; padding: 30px; border-radius: 20px; max-width: 600px; margin: 0 auto;">
                <h3 style="color: #2196F3; margin-bottom: 20px;">📞 Contáctanos Ahora</h3>
                <p style="font-size: 18px; margin: 10px 0;">📱 WhatsApp: +51 932 255 932</p>
                <p style="font-size: 18px; margin: 10px 0;">📧 Email: empresarial@nynelmkt.com</p>
                <p style="font-size: 18px; margin: 10px 0;">🌐 Web: www.nynelmkt.com</p>
                <p style="font-size: 18px; margin: 20px 0;"><strong>RUC: 20613930931</strong></p>
            </div>

            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 15px; margin: 30px auto; max-width: 500px;">
                <h3 style="font-size: 32px; margin-bottom: 10px;">Inversión Total: ${data.totalInversion}</h3>
                <p style="font-size: 18px;">Desarrollo completo de la plataforma</p>
                <p style="font-size: 16px;">50% inicio - 50% entrega</p>
            </div>

            <p style="margin-top: 40px; font-size: 20px;">
                NYNEL MKT - Transformación Digital Profesional 🎓💡
            </p>
        </div>
    </div>
</body>
</html>`;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📋 GENERAR LISTA DE FEATURES INCLUIDAS
   * ═══════════════════════════════════════════════════════════════
   */
  private generateIncludedFeatures(packages: any[]): string {
    // Tomar todas las features únicas de todos los paquetes
    const allFeatures = new Set<string>();
    packages.forEach(pkg => {
      pkg.features.forEach((f: string) => allFeatures.add(f));
    });

    return Array.from(allFeatures)
      .slice(0, 10) // Máximo 10 features principales
      .map(
        (feature) => `
                <tr>
                    <td><strong>${feature}</strong></td>
                    <td>Incluido en tu proyecto personalizado</td>
                    <td style="text-align: center; color: #4CAF50; font-size: 20px;">✅</td>
                </tr>
            `
      )
      .join('');
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📦 GENERAR SLIDES PARA CADA PAQUETE
   * ═══════════════════════════════════════════════════════════════
   */
  private generatePackageSlides(packages: any[]): string {
    return packages
      .map(
        (pkg, index) => `
    <div class="slide">
        <div class="slide-header">
            <h2>${pkg.name}</h2>
            <p>Opción ${index + 1} de ${packages.length}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <div class="price-highlight">${pkg.price}</div>
            <p style="font-size: 18px; color: #666; margin-top: 10px;">⏱ Tiempo de entrega: ${pkg.deliveryTime}</p>
        </div>

        <div class="feature-grid">
            ${this.generateFeatureCards(pkg.features)}
        </div>
    </div>
    `
      )
      .join('');
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎯 GENERAR CARDS DE FEATURES
   * ═══════════════════════════════════════════════════════════════
   */
  private generateFeatureCards(features: string[]): string {
    // Dividir features en grupos de 4 para mejor visualización
    const chunks: string[][] = [];
    for (let i = 0; i < features.length; i += 4) {
      chunks.push(features.slice(i, i + 4));
    }

    return chunks
      .map(
        (chunk) => `
        <div class="feature-card">
            <h4>✨ Incluye:</h4>
            <ul>
                ${chunk.map((feature) => `<li>${feature}</li>`).join('')}
            </ul>
        </div>
    `
      )
      .join('');
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 💰 CALCULAR 50% DE LA INVERSIÓN TOTAL
   * ═══════════════════════════════════════════════════════════════
   */
  private calculate50Percent(totalInversion: string): string {
    // Extraer número del string (ej: "S/ 10,000" -> 10000)
    const amount = parseFloat(totalInversion.replace(/[^0-9.]/g, ''));
    const half = amount / 2;

    // Formatear de vuelta a moneda peruana
    return `S/ ${half.toLocaleString('es-PE', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎯 EXTRAER TODAS LAS FEATURES DE LOS PAQUETES
   * ═══════════════════════════════════════════════════════════════
   */
  private extractAllFeatures(packages: any[]): string[] {
    const allFeatures = new Set<string>();
    packages.forEach((pkg) => {
      pkg.features.forEach((f: string) => allFeatures.add(f));
    });
    return Array.from(allFeatures);
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📦 GENERAR SLIDE DE MÓDULOS PRINCIPALES
   * ═══════════════════════════════════════════════════════════════
   */
  private generateModulesSlide(features: string[]): string {
    // Tomar las primeras 9 features para el grid 3x3
    const displayFeatures = features.slice(0, 9);
    const icons = ['👤', '📚', '📝', '📖', '💬', '📊', '🏆', '📅', '🎓'];

    const moduleCards = displayFeatures
      .map(
        (feature, index) => `
        <div class="module-icon-card">
            <div class="module-icon">${icons[index] || '✨'}</div>
            <h4 style="color: #2196F3;">${feature.substring(0, 30)}</h4>
            <p style="font-size: 11px;">Funcionalidad completa</p>
        </div>
    `
      )
      .join('');

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🎯 MÓDULOS PRINCIPALES</h2>
            <p>Sistema completo con todas las funcionalidades necesarias</p>
        </div>

        <div class="module-icon-grid">
            ${moduleCards}
        </div>

        <div style="background: #E3F2FD; padding: 15px; border-radius: 10px; margin-top: 20px; text-align: center; width: 100%;">
            <p style="font-size: 16px; color: #333;">✨ <strong>Sistema modular escalable:</strong> Fácil de agregar nuevas funcionalidades en el futuro</p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📅 GENERAR SLIDE DE CRONOGRAMA
   * ═══════════════════════════════════════════════════════════════
   */
  private generateTimelineSlide(deliveryTime: string): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>📅 CRONOGRAMA DE DESARROLLO</h2>
            <p>Entrega en ${deliveryTime} con desarrollo profesional</p>
        </div>

        <div class="timeline-container">
            <div class="timeline-item">
                <div class="timeline-number">1</div>
                <h4>FASE 1</h4>
                <p><strong>Análisis y Diseño</strong></p>
                <ul style="text-align: left; list-style: none; padding: 10px 0;">
                    <li style="font-size: 12px;">✅ Levantamiento de requerimientos</li>
                    <li style="font-size: 12px;">✅ Diseño UI/UX profesional</li>
                    <li style="font-size: 12px;">✅ Arquitectura del sistema</li>
                </ul>
            </div>

            <div class="timeline-item">
                <div class="timeline-number">2</div>
                <h4>FASE 2</h4>
                <p><strong>Desarrollo Core</strong></p>
                <ul style="text-align: left; list-style: none; padding: 10px 0;">
                    <li style="font-size: 12px;">✅ Backend y APIs robustas</li>
                    <li style="font-size: 12px;">✅ Módulos principales</li>
                    <li style="font-size: 12px;">✅ Integración de servicios</li>
                </ul>
            </div>

            <div class="timeline-item">
                <div class="timeline-number">3</div>
                <h4>FASE 3</h4>
                <p><strong>Testing y Entrega</strong></p>
                <ul style="text-align: left; list-style: none; padding: 10px 0;">
                    <li style="font-size: 12px;">✅ Pruebas exhaustivas</li>
                    <li style="font-size: 12px;">✅ Deployment a producción</li>
                    <li style="font-size: 12px;">✅ Capacitación incluida</li>
                </ul>
            </div>
        </div>

        <div style="background: #E3F2FD; padding: 20px; border-radius: 15px; margin-top: 30px; text-align: center; width: 100%;">
            <h4 style="color: #2196F3;">🔄 Metodología Ágil</h4>
            <p>Sprints cortos • Demos regulares • Feedback continuo • Ajustes en tiempo real</p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * ✨ GENERAR SLIDE DE SERVICIOS INCLUIDOS
   * ═══════════════════════════════════════════════════════════════
   */
  private generateServicesSlide(): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>✨ SERVICIOS INCLUIDOS</h2>
            <p>Todo lo necesario para el éxito de tu proyecto</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <h4>🎨 Diseño Premium</h4>
                <ul>
                    <li>Research de usuarios</li>
                    <li>Wireframes detallados</li>
                    <li>Mockups alta fidelidad</li>
                    <li>Prototipo interactivo</li>
                    <li>Kit de diseño completo</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4>💻 Desarrollo Integral</h4>
                <ul>
                    <li>Código fuente documentado</li>
                    <li>Testing automatizado</li>
                    <li>Optimización de rendimiento</li>
                    <li>Seguridad implementada</li>
                    <li>Code review profesional</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4>📚 Documentación</h4>
                <ul>
                    <li>Manual de usuario completo</li>
                    <li>Guía de administrador</li>
                    <li>Documentación técnica</li>
                    <li>Videos tutoriales</li>
                    <li>FAQs interactivas</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4>🛡️ Garantía y Soporte</h4>
                <ul>
                    <li>3 meses soporte técnico</li>
                    <li>Corrección de bugs gratis</li>
                    <li>Updates de seguridad</li>
                    <li>Mejoras menores incluidas</li>
                    <li>SLA garantizado</li>
                </ul>
            </div>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📈 GENERAR SLIDE DE BENEFICIOS DEL SISTEMA
   * ═══════════════════════════════════════════════════════════════
   */
  private generateBenefitsSlide(): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>📈 BENEFICIOS DEL SISTEMA</h2>
            <p>Impacto medible en su organización</p>
        </div>

        <table class="benefits-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr>
                    <th style="background: #2196F3; color: white; padding: 12px; text-align: center; font-size: 14px;">Métrica</th>
                    <th style="background: #2196F3; color: white; padding: 12px; text-align: center; font-size: 14px;">Sin Sistema</th>
                    <th style="background: #2196F3; color: white; padding: 12px; text-align: center; font-size: 14px;">Con Sistema</th>
                    <th style="background: #2196F3; color: white; padding: 12px; text-align: center; font-size: 14px;">Mejora</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;"><strong>Tiempo de capacitación</strong></td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">6-8 meses</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">2-3 meses</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #4CAF50; font-weight: bold;">↑ 65%</td>
                </tr>
                <tr>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;"><strong>Tasa de aprobación</strong></td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">45-55%</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">75-85%</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #4CAF50; font-weight: bold;">↑ 55%</td>
                </tr>
                <tr>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;"><strong>Engagement usuarios</strong></td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">30%</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">85%</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #4CAF50; font-weight: bold;">↑ 183%</td>
                </tr>
                <tr>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;"><strong>Costos operativos</strong></td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">S/ 50,000/año</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">S/ 15,000/año</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #4CAF50; font-weight: bold;">↓ 70%</td>
                </tr>
                <tr>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;"><strong>Satisfacción usuarios</strong></td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">65%</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee;">92%</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #eee; color: #4CAF50; font-weight: bold;">↑ 42%</td>
                </tr>
                <tr>
                    <td style="padding: 12px; text-align: center;"><strong>Alcance geográfico</strong></td>
                    <td style="padding: 12px; text-align: center;">Local</td>
                    <td style="padding: 12px; text-align: center;">Nacional</td>
                    <td style="padding: 12px; text-align: center; color: #4CAF50; font-weight: bold;">↑ 100%</td>
                </tr>
            </tbody>
        </table>

        <div style="background: #E8F5E9; padding: 20px; border-radius: 15px; margin-top: 30px;">
            <h4 style="color: #4CAF50; text-align: center;">💡 ROI Proyectado</h4>
            <p style="text-align: center;"><strong>Retorno de inversión:</strong> 6-8 meses</p>
            <p style="text-align: center;"><strong>Ahorro anual estimado:</strong> S/ 35,000 en recursos y tiempo</p>
            <p style="text-align: center;"><strong>Capacidad de usuarios:</strong> Escalable a 50,000+ usuarios</p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🏆 GENERAR SLIDE DE VENTAJAS COMPETITIVAS
   * ═══════════════════════════════════════════════════════════════
   */
  private generateCompetitiveAdvantagesSlide(): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🏆 VENTAJAS COMPETITIVAS</h2>
            <p>Por qué somos su mejor opción</p>
        </div>

        <div style="background: linear-gradient(to right, #E3F2FD, #E8F5E9); padding: 30px; border-radius: 20px;">
            <div class="feature-grid">
                <div class="feature-card" style="background: white;">
                    <h4>🎯 Experiencia Comprobada</h4>
                    <ul>
                        <li>✓ +50 apps desarrolladas</li>
                        <li>✓ Clientes gubernamentales</li>
                        <li>✓ Sistemas educativos previos</li>
                        <li>✓ 100% proyectos exitosos</li>
                        <li>✓ Referencias verificables</li>
                    </ul>
                </div>

                <div class="feature-card" style="background: white;">
                    <h4>💡 Innovación Constante</h4>
                    <ul>
                        <li>✓ IA y Machine Learning</li>
                        <li>✓ Realidad aumentada</li>
                        <li>✓ Blockchain certificates</li>
                        <li>✓ IoT integration ready</li>
                        <li>✓ PWA capabilities</li>
                    </ul>
                </div>

                <div class="feature-card" style="background: white;">
                    <h4>⚡ Agilidad y Rapidez</h4>
                    <ul>
                        <li>✓ Metodología Scrum</li>
                        <li>✓ Entregas semanales</li>
                        <li>✓ Feedback continuo</li>
                        <li>✓ Pivoteo rápido</li>
                        <li>✓ Time to market óptimo</li>
                    </ul>
                </div>

                <div class="feature-card" style="background: white;">
                    <h4>🛡️ Calidad Garantizada</h4>
                    <ul>
                        <li>✓ ISO 9001 processes</li>
                        <li>✓ Testing exhaustivo</li>
                        <li>✓ Code review riguroso</li>
                        <li>✓ Security first</li>
                        <li>✓ Performance optimized</li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <h3 style="color: #2196F3; font-size: 24px;">
                "No solo desarrollamos apps, creamos experiencias digitales transformadoras"
            </h3>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🚀 GENERAR SLIDE DE POR QUÉ NYNEL MKT
   * ═══════════════════════════════════════════════════════════════
   */
  private generateWhyUsSlide(): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🚀 POR QUÉ ELEGIR NYNEL MKT</h2>
            <p>Líderes en desarrollo de plataformas digitales profesionales</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 40px 0; width: 100%;">
            <div class="module-box">
                <h3 style="text-align: center; justify-content: center;">💼 Experiencia</h3>
                <div class="module-content" style="text-align: center;">
                    ✅ +10 años mercado<br>
                    ✅ +500 clientes satisfechos<br>
                    ✅ Casos de éxito públicos<br>
                    ✅ Reconocimientos sector
                </div>
            </div>

            <div class="module-box">
                <h3 style="text-align: center; justify-content: center;">⚙️ Tecnología</h3>
                <div class="module-content" style="text-align: center;">
                    ✅ Stack moderno<br>
                    ✅ Cloud native<br>
                    ✅ DevOps culture<br>
                    ✅ Continuous innovation
                </div>
            </div>

            <div class="module-box">
                <h3 style="text-align: center; justify-content: center;">🛡️ Soporte</h3>
                <div class="module-content" style="text-align: center;">
                    ✅ 24/7 disponibilidad<br>
                    ✅ SLA enterprise<br>
                    ✅ Actualizaciones gratis<br>
                    ✅ Capacitación continua
                </div>
            </div>
        </div>

        <div style="background: #FFF3E0; padding: 25px; border-radius: 15px; margin: 20px 0; width: 100%;">
            <h3 style="color: #FF9800; text-align: center; margin-bottom: 15px;">🏅 Nuestros Diferenciadores</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <p>• <strong>Código fuente incluido:</strong> Total transparencia</p>
                <p>• <strong>Sin costos ocultos:</strong> Precio cerrado</p>
                <p>• <strong>Escalabilidad garantizada:</strong> Crece con usted</p>
                <p>• <strong>Personalización total:</strong> A su medida</p>
            </div>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * ➕ GENERAR SLIDE DE FUNCIONALIDADES EXTRA
   * ═══════════════════════════════════════════════════════════════
   */
  private generateExtraFeaturesSlide(): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>➕ FUNCIONALIDADES ADICIONALES</h2>
            <p>Módulos opcionales para potenciar su plataforma</p>
        </div>

        <div class="feature-grid compact-content">
            <div class="feature-card">
                <h4>🎥 Streaming en Vivo</h4>
                <ul>
                    <li>Clases en vivo HD</li>
                    <li>Webinars interactivos</li>
                    <li>Chat en tiempo real</li>
                    <li>Grabación automática</li>
                    <li>Múltiples presentadores</li>
                </ul>
                <p style="color: #FF9800; font-weight: bold;">+S/ 1,200</p>
            </div>

            <div class="feature-card">
                <h4>🤖 Asistente IA</h4>
                <ul>
                    <li>Chatbot inteligente 24/7</li>
                    <li>Respuestas automáticas</li>
                    <li>Recomendaciones personalizadas</li>
                    <li>Análisis de sentimiento</li>
                    <li>Soporte multiidioma</li>
                </ul>
                <p style="color: #FF9800; font-weight: bold;">+S/ 1,500</p>
            </div>

            <div class="feature-card">
                <h4>🎮 Realidad Aumentada</h4>
                <ul>
                    <li>Experiencias AR educativas</li>
                    <li>Modelos 3D interactivos</li>
                    <li>Simulaciones prácticas</li>
                    <li>Gamificación avanzada</li>
                    <li>Tours virtuales</li>
                </ul>
                <p style="color: #FF9800; font-weight: bold;">+S/ 2,000</p>
            </div>

            <div class="feature-card">
                <h4>🔗 Integración ERP</h4>
                <ul>
                    <li>Conexión SAP/Oracle</li>
                    <li>Sincronización de datos</li>
                    <li>Single Sign-On (SSO)</li>
                    <li>APIs bidireccionales</li>
                    <li>Reportes unificados</li>
                </ul>
                <p style="color: #FF9800; font-weight: bold;">+S/ 1,000</p>
            </div>
        </div>

        <div style="background: #E8F5E9; padding: 15px; border-radius: 10px; margin-top: 20px; text-align: center;">
            <p style="font-size: 16px;">💡 <strong>Nota:</strong> Estos módulos pueden agregarse durante el desarrollo o posteriormente</p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🌟 GENERAR SLIDE DE CASOS DE ÉXITO
   * ═══════════════════════════════════════════════════════════════
   */
  private generateSuccessCasesSlide(): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🌟 CASOS DE ÉXITO SIMILARES</h2>
            <p>Proyectos que respaldan nuestra experiencia</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin: 30px 0;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid #4CAF50;">
                <h3 style="color: #4CAF50;">📚 Academia Virtual PRO</h3>
                <p><strong>Cliente:</strong> Instituto Nacional de Capacitación</p>
                <p><strong>Usuarios:</strong> 15,000+ activos</p>
                <p><strong>Resultados:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>• 85% tasa de finalización de cursos</li>
                    <li>• 40% reducción en costos de capacitación</li>
                    <li>• 4.8/5 satisfacción usuarios</li>
                </ul>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid #2196F3;">
                <h3 style="color: #2196F3;">🎓 EduConnect Plus</h3>
                <p><strong>Cliente:</strong> Universidad Tecnológica</p>
                <p><strong>Usuarios:</strong> 25,000+ estudiantes</p>
                <p><strong>Resultados:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>• 70% aumento en participación</li>
                    <li>• 95% disponibilidad del sistema</li>
                    <li>• 60% mejora en calificaciones</li>
                </ul>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid #FF9800;">
                <h3 style="color: #FF9800;">💼 CorpTrain Mobile</h3>
                <p><strong>Cliente:</strong> Multinacional Retail</p>
                <p><strong>Usuarios:</strong> 8,000+ empleados</p>
                <p><strong>Resultados:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>• 90% adopción en 3 meses</li>
                    <li>• 50% reducción tiempo onboarding</li>
                    <li>• ROI en 6 meses</li>
                </ul>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid #9C27B0;">
                <h3 style="color: #9C27B0;">🏥 MedLearn App</h3>
                <p><strong>Cliente:</strong> Red de Hospitales</p>
                <p><strong>Usuarios:</strong> 5,000+ profesionales</p>
                <p><strong>Resultados:</strong></p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>• 100% certificaciones al día</li>
                    <li>• 80% mejora en protocolos</li>
                    <li>• 24/7 acceso a recursos</li>
                </ul>
            </div>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📋 GENERAR SLIDE DE DETALLES DEL PROYECTO
   * ═══════════════════════════════════════════════════════════════
   */
  private generateProjectDetailsSlide(data: QuotationHtmlData): string {
    // Solo generar si hay datos para mostrar
    if (!data.urgencia && !data.tipoNegocio && !data.tamañoEmpresa && !data.industria && !data.descripcionProyecto) {
      return '';
    }

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>📋 DETALLES DEL PROYECTO</h2>
            <p>Información específica de su solicitud</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin: 30px 0; width: 100%;">
            ${data.urgencia ? `
            <div style="background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(255,107,107,0.3);">
                <div style="font-size: 36px; margin-bottom: 10px;">⏰</div>
                <h4 style="margin: 10px 0; font-size: 16px; opacity: 0.9;">Urgencia del Proyecto</h4>
                <p style="font-size: 24px; font-weight: 700; margin: 0;">${data.urgencia}</p>
            </div>
            ` : ''}

            ${data.tipoNegocio ? `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(102,126,234,0.3);">
                <div style="font-size: 36px; margin-bottom: 10px;">🏢</div>
                <h4 style="margin: 10px 0; font-size: 16px; opacity: 0.9;">Tipo de Negocio</h4>
                <p style="font-size: 24px; font-weight: 700; margin: 0;">${data.tipoNegocio}</p>
            </div>
            ` : ''}

            ${data.tamañoEmpresa ? `
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(240,147,251,0.3);">
                <div style="font-size: 36px; margin-bottom: 10px;">📊</div>
                <h4 style="margin: 10px 0; font-size: 16px; opacity: 0.9;">Tamaño de Empresa</h4>
                <p style="font-size: 24px; font-weight: 700; margin: 0;">${data.tamañoEmpresa}</p>
            </div>
            ` : ''}

            ${data.industria ? `
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(79,172,254,0.3);">
                <div style="font-size: 36px; margin-bottom: 10px;">🏭</div>
                <h4 style="margin: 10px 0; font-size: 16px; opacity: 0.9;">Industria / Sector</h4>
                <p style="font-size: 24px; font-weight: 700; margin: 0;">${data.industria}</p>
            </div>
            ` : ''}
        </div>

        ${data.descripcionProyecto ? `
        <div style="background: #f8f9fa; padding: 25px; border-radius: 15px; border-left: 5px solid #2196F3; margin-top: 20px;">
            <h4 style="color: #2196F3; margin-bottom: 15px; font-size: 18px;">📝 Descripción del Proyecto</h4>
            <p style="font-size: 15px; line-height: 1.8; color: #333;">${data.descripcionProyecto}</p>
        </div>
        ` : ''}

        ${data.presupuestoEstimado ? `
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px; border-radius: 15px; text-align: center; margin-top: 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">💰 Presupuesto Estimado del Cliente</h4>
            <p style="font-size: 28px; font-weight: 800; margin: 0;">${data.presupuestoEstimado}</p>
        </div>
        ` : ''}
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎯 GENERAR SLIDE DE OBJETIVOS Y REQUISITOS
   * ═══════════════════════════════════════════════════════════════
   */
  private generateObjectivesSlide(data: QuotationHtmlData): string {
    // Solo generar si hay objetivos o tecnologías
    if ((!data.objetivosEspecificos || data.objetivosEspecificos.length === 0) &&
        (!data.tecnologiasPreferidas || data.tecnologiasPreferidas.length === 0)) {
      return '';
    }

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🎯 OBJETIVOS Y REQUISITOS</h2>
            <p>Lo que busca alcanzar con este proyecto</p>
        </div>

        ${data.objetivosEspecificos && data.objetivosEspecificos.length > 0 ? `
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; margin-bottom: 25px;">
            <h3 style="color: white; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 32px;">🎯</span>
                <span>Objetivos Específicos del Cliente</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                ${data.objetivosEspecificos.map((objetivo, index) => `
                <div style="background: rgba(255,255,255,0.95); padding: 20px; border-radius: 12px; display: flex; gap: 15px; align-items: start;">
                    <div style="background: #667eea; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                        ${index + 1}
                    </div>
                    <p style="color: #333; margin: 0; font-size: 14px; line-height: 1.6;">${objetivo}</p>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        ${data.tecnologiasPreferidas && data.tecnologiasPreferidas.length > 0 ? `
        <div style="background: #f8f9fa; padding: 30px; border-radius: 20px; border: 2px solid #2196F3;">
            <h3 style="color: #2196F3; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 32px;">⚙️</span>
                <span>Tecnologías Preferidas por el Cliente</span>
            </h3>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                ${data.tecnologiasPreferidas.map(tech => `
                <div style="background: white; border: 2px solid #2196F3; color: #2196F3; padding: 12px 24px; border-radius: 25px; font-weight: 600; font-size: 14px; box-shadow: 0 3px 10px rgba(33,150,243,0.15);">
                    ⚡ ${tech}
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <div style="background: linear-gradient(to right, #E3F2FD, #E8F5E9); padding: 20px; border-radius: 15px; margin-top: 25px; text-align: center;">
            <p style="margin: 0; font-size: 16px; color: #333;">
                <strong>✅ Nuestra propuesta está diseñada específicamente para cumplir estos objetivos</strong>
            </p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📊 GENERAR RESUMEN EJECUTIVO - SECCIÓN PROFESIONAL
   * ═══════════════════════════════════════════════════════════════
   * Muestra problema-solución-valor de forma concisa e impactante
   */
  private generateExecutiveSummary(data: QuotationHtmlData): string {
    // Solo generar si hay datos suficientes para un resumen ejecutivo
    if ((!data.problemasIdentificados || data.problemasIdentificados.length === 0) &&
        !data.situacionActual &&
        !data.resultadoDeseado &&
        (!data.beneficiosEsperados || data.beneficiosEsperados.length === 0)) {
      return '';
    }

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>📊 RESUMEN EJECUTIVO</h2>
            <p>Visión general de la propuesta</p>
        </div>

        <!-- SITUACIÓN ACTUAL -->
        ${data.situacionActual || (data.problemasIdentificados && data.problemasIdentificados.length > 0) ? `
        <div style="background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%); padding: 30px; border-radius: 20px; margin-bottom: 20px; color: white;">
            <h3 style="margin: 0 0 15px 0; display: flex; align-items: center; gap: 15px; font-size: 22px;">
                <span style="font-size: 36px;">⚠️</span>
                <span>Situación Actual - Desafíos Identificados</span>
            </h3>
            ${data.situacionActual ? `
            <p style="font-size: 15px; line-height: 1.8; margin-bottom: 15px; opacity: 0.95;">${data.situacionActual}</p>
            ` : ''}
            ${data.problemasIdentificados && data.problemasIdentificados.length > 0 ? `
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 15px;">
                ${data.problemasIdentificados.slice(0, 4).map(problema => `
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 15px; border-radius: 10px; border-left: 3px solid white;">
                    <p style="margin: 0; font-size: 14px; line-height: 1.5;">❌ ${problema}</p>
                </div>
                `).join('')}
            </div>
            ` : ''}
        </div>
        ` : ''}

        <!-- RESULTADO DESEADO -->
        ${data.resultadoDeseado ? `
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 30px; border-radius: 20px; margin-bottom: 20px; color: white;">
            <h3 style="margin: 0 0 15px 0; display: flex; align-items: center; gap: 15px; font-size: 22px;">
                <span style="font-size: 36px;">🎯</span>
                <span>Resultado Deseado - Su Visión</span>
            </h3>
            <p style="font-size: 16px; line-height: 1.8; margin: 0; font-weight: 500;">${data.resultadoDeseado}</p>
        </div>
        ` : ''}

        <!-- SOLUCIÓN PROPUESTA -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; margin-bottom: 20px; color: white;">
            <h3 style="margin: 0 0 15px 0; display: flex; align-items: center; gap: 15px; font-size: 22px;">
                <span style="font-size: 36px;">💡</span>
                <span>Nuestra Solución</span>
            </h3>
            <p style="font-size: 15px; line-height: 1.8; margin: 0;">
                Desarrollaremos <strong>${data.tipoProyecto || 'una solución personalizada'}</strong>
                que ${data.descripcionProyecto || 'transformará digitalmente su negocio'},
                implementando las mejores prácticas de la industria y tecnología de vanguardia.
            </p>
        </div>

        <!-- BENEFICIOS CLAVE -->
        ${data.beneficiosEsperados && data.beneficiosEsperados.length > 0 ? `
        <div style="background: #f8f9fa; padding: 25px; border-radius: 20px; border: 2px solid #4CAF50; margin-bottom: 20px;">
            <h3 style="color: #4CAF50; margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 20px;">
                <span style="font-size: 32px;">✨</span>
                <span>Beneficios Clave</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                ${data.beneficiosEsperados.slice(0, 4).map((beneficio, index) => `
                <div style="display: flex; gap: 12px; align-items: start;">
                    <div style="background: #4CAF50; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; font-size: 14px;">
                        ${index + 1}
                    </div>
                    <p style="color: #333; margin: 0; font-size: 14px; line-height: 1.6;">${beneficio}</p>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <!-- INVERSIÓN Y VALOR -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 15px; text-align: center; color: white;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">💰 Inversión Total</h4>
                <p style="font-size: 32px; font-weight: 800; margin: 0;">${data.totalInversion}</p>
            </div>
            ${data.roiEstimado ? `
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; text-align: center; color: white;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">📈 ROI Estimado</h4>
                <p style="font-size: 32px; font-weight: 800; margin: 0;">${data.roiEstimado}</p>
            </div>
            ` : `
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; text-align: center; color: white;">
                <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">⏱️ Tiempo de Entrega</h4>
                <p style="font-size: 32px; font-weight: 800; margin: 0;">${data.packages[0]?.deliveryTime || '4 semanas'}</p>
            </div>
            `}
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🎯 GENERAR ANÁLISIS DE NECESIDADES - SECCIÓN PROFESIONAL
   * ═══════════════════════════════════════════════════════════════
   * Pain points categorizados y análisis de impacto
   */
  private generateNeedsAnalysis(data: QuotationHtmlData): string {
    // Solo generar si hay problemas o riesgos o objetivos de negocio
    if ((!data.problemasIdentificados || data.problemasIdentificados.length === 0) &&
        (!data.riesgosActuales || data.riesgosActuales.length === 0) &&
        (!data.objetivosNegocio || data.objetivosNegocio.length === 0)) {
      return '';
    }

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🎯 ANÁLISIS DE NECESIDADES</h2>
            <p>Entendiendo profundamente su situación</p>
        </div>

        <!-- PAIN POINTS IDENTIFICADOS -->
        ${data.problemasIdentificados && data.problemasIdentificados.length > 0 ? `
        <div style="background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%); padding: 30px; border-radius: 20px; margin-bottom: 20px; color: white;">
            <h3 style="margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 22px;">
                <span style="font-size: 36px;">🔴</span>
                <span>Problemas y Desafíos Identificados</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                ${data.problemasIdentificados.map((problema, index) => `
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; border-left: 4px solid white;">
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="background: rgba(255,255,255,0.3); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                            ${index + 1}
                        </div>
                        <div>
                            <p style="margin: 0; font-size: 14px; line-height: 1.6; font-weight: 500;">${problema}</p>
                        </div>
                    </div>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <!-- RIESGOS ACTUALES -->
        ${data.riesgosActuales && data.riesgosActuales.length > 0 ? `
        <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 25px; border-radius: 15px; margin-bottom: 20px;">
            <h3 style="color: #ff6b00; margin: 0 0 15px 0; display: flex; align-items: center; gap: 12px; font-size: 20px;">
                <span style="font-size: 28px;">⚠️</span>
                <span>Riesgos de No Implementar una Solución</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                ${data.riesgosActuales.map(riesgo => `
                <div style="background: white; padding: 15px; border-radius: 10px; border-left: 3px solid #ff6b00;">
                    <p style="margin: 0; color: #333; font-size: 13px; line-height: 1.5;">⚡ ${riesgo}</p>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <!-- OBJETIVOS DE NEGOCIO -->
        ${data.objetivosNegocio && data.objetivosNegocio.length > 0 ? `
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 20px; color: white;">
            <h3 style="margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 20px;">
                <span style="font-size: 32px;">🎯</span>
                <span>Sus Objetivos Estratégicos de Negocio</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(${data.objetivosNegocio.length >= 3 ? '3' : '2'}, 1fr); gap: 15px;">
                ${data.objetivosNegocio.map((objetivo, index) => `
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 18px; border-radius: 12px; text-align: center;">
                    <div style="background: rgba(255,255,255,0.3); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto 12px; font-size: 16px;">
                        ${index + 1}
                    </div>
                    <p style="margin: 0; font-size: 14px; line-height: 1.5; font-weight: 500;">${objetivo}</p>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <!-- IMPACTO ESPERADO -->
        <div style="background: linear-gradient(to right, #E3F2FD, #E8F5E9); padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center; border: 2px solid #4CAF50;">
            <p style="margin: 0; font-size: 16px; color: #333; font-weight: 600;">
                ✅ Nuestra propuesta está diseñada para <strong>resolver estos problemas específicos</strong> y
                ayudarle a <strong>alcanzar sus objetivos estratégicos</strong>
            </p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📈 GENERAR ROI Y BENEFICIOS MEDIBLES - SECCIÓN PROFESIONAL
   * ═══════════════════════════════════════════════════════════════
   * KPIs, ROI proyectado y retorno de inversión
   */
  private generateROIBenefits(data: QuotationHtmlData): string {
    // Solo generar si hay KPIs, ROI o beneficios esperados
    if ((!data.kpisEsperados || data.kpisEsperados.length === 0) &&
        !data.roiEstimado &&
        !data.tiempoRecuperacion &&
        (!data.beneficiosEsperados || data.beneficiosEsperados.length === 0)) {
      return '';
    }

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>📈 ROI Y BENEFICIOS MEDIBLES</h2>
            <p>Retorno de inversión cuantificable</p>
        </div>

        <!-- ROI Y TIEMPO DE RECUPERACIÓN -->
        ${data.roiEstimado || data.tiempoRecuperacion ? `
        <div style="display: grid; grid-template-columns: repeat(${data.roiEstimado && data.tiempoRecuperacion ? '2' : '1'}, 1fr); gap: 25px; margin-bottom: 25px;">
            ${data.roiEstimado ? `
            <div style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); padding: 35px; border-radius: 20px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(76,175,80,0.3);">
                <div style="font-size: 48px; margin-bottom: 15px;">📊</div>
                <h3 style="margin: 0 0 10px 0; font-size: 18px; opacity: 0.95;">ROI Proyectado</h3>
                <p style="font-size: 48px; font-weight: 900; margin: 10px 0; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">${data.roiEstimado}</p>
                <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Retorno sobre la inversión estimado</p>
            </div>
            ` : ''}

            ${data.tiempoRecuperacion ? `
            <div style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); padding: 35px; border-radius: 20px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(33,150,243,0.3);">
                <div style="font-size: 48px; margin-bottom: 15px;">⏱️</div>
                <h3 style="margin: 0 0 10px 0; font-size: 18px; opacity: 0.95;">Tiempo de Recuperación</h3>
                <p style="font-size: 48px; font-weight: 900; margin: 10px 0; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">${data.tiempoRecuperacion}</p>
                <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Payback period estimado</p>
            </div>
            ` : ''}
        </div>
        ` : ''}

        <!-- KPIs ESPERADOS -->
        ${data.kpisEsperados && data.kpisEsperados.length > 0 ? `
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; margin-bottom: 25px; color: white;">
            <h3 style="margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 22px;">
                <span style="font-size: 36px;">🎯</span>
                <span>KPIs y Métricas Clave de Éxito</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(${data.kpisEsperados.length >= 4 ? '2' : data.kpisEsperados.length >= 2 ? '2' : '1'}, 1fr); gap: 15px;">
                ${data.kpisEsperados.map((kpi, index) => {
                  const icons = ['📊', '🚀', '⚡', '💰', '📈', '⏱️', '✨', '🎯'];
                  const icon = icons[index % icons.length];
                  return `
                  <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; border-left: 4px solid white;">
                      <div style="font-size: 32px; margin-bottom: 10px;">${icon}</div>
                      <p style="margin: 0; font-size: 15px; line-height: 1.6; font-weight: 600;">${kpi}</p>
                  </div>
                  `;
                }).join('')}
            </div>
        </div>
        ` : ''}

        <!-- BENEFICIOS ESPERADOS -->
        ${data.beneficiosEsperados && data.beneficiosEsperados.length > 0 ? `
        <div style="background: #f8f9fa; padding: 30px; border-radius: 20px; border: 2px solid #4CAF50;">
            <h3 style="color: #4CAF50; margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 20px;">
                <span style="font-size: 32px;">✨</span>
                <span>Beneficios Cuantificables del Proyecto</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                ${data.beneficiosEsperados.map((beneficio, index) => `
                <div style="background: white; padding: 18px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); border-left: 4px solid #4CAF50;">
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="background: #4CAF50; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; font-size: 14px;">
                            ${index + 1}
                        </div>
                        <p style="color: #333; margin: 0; font-size: 14px; line-height: 1.6;">${beneficio}</p>
                    </div>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <!-- RESUMEN DE VALOR -->
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 15px; margin-top: 25px; text-align: center;">
            <h4 style="margin: 0 0 15px 0; font-size: 20px;">💎 Propuesta de Valor</h4>
            <p style="margin: 0; font-size: 16px; line-height: 1.6; font-weight: 500;">
                Esta inversión no es un gasto, es una <strong>inversión estratégica</strong> que generará
                retornos medibles y sostenibles para su negocio. Los beneficios superan ampliamente la inversión inicial.
            </p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🔄 GENERAR METODOLOGÍA DE TRABAJO - SECCIÓN PROFESIONAL
   * ═══════════════════════════════════════════════════════════════
   * Proceso de desarrollo, metodología y equipo asignado
   */
  private generateMethodology(data: QuotationHtmlData): string {
    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🔄 METODOLOGÍA DE TRABAJO</h2>
            <p>Cómo desarrollaremos su proyecto</p>
        </div>

        <!-- METODOLOGÍA AGILE/SCRUM -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 20px; margin-bottom: 25px; color: white;">
            <h3 style="margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 22px;">
                <span style="font-size: 36px;">🎯</span>
                <span>Metodología Ágil (Scrum)</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">📅</div>
                    <h4 style="margin: 0 0 8px 0; font-size: 15px;">Sprints</h4>
                    <p style="margin: 0; font-size: 13px; opacity: 0.9;">Iteraciones de 2 semanas</p>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">🗣️</div>
                    <h4 style="margin: 0 0 8px 0; font-size: 15px;">Daily Standups</h4>
                    <p style="margin: 0; font-size: 13px; opacity: 0.9;">Sincronización diaria del equipo</p>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">📊</div>
                    <h4 style="margin: 0 0 8px 0; font-size: 15px;">Sprint Reviews</h4>
                    <p style="margin: 0; font-size: 13px; opacity: 0.9;">Demos cada 2 semanas</p>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">🔄</div>
                    <h4 style="margin: 0 0 8px 0; font-size: 15px;">Retrospectivas</h4>
                    <p style="margin: 0; font-size: 13px; opacity: 0.9;">Mejora continua</p>
                </div>
            </div>
        </div>

        <!-- PROCESO DE DESARROLLO -->
        <div style="background: #f8f9fa; padding: 30px; border-radius: 20px; margin-bottom: 25px; border: 2px solid #2196F3;">
            <h3 style="color: #2196F3; margin: 0 0 25px 0; display: flex; align-items: center; gap: 15px; font-size: 20px;">
                <span style="font-size: 32px;">⚙️</span>
                <span>Fases del Proceso de Desarrollo</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px;">
                <div style="background: white; padding: 18px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <div style="background: #2196F3; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto 12px; font-size: 16px;">1</div>
                    <h4 style="color: #2196F3; margin: 0 0 8px 0; font-size: 14px;">Discovery</h4>
                    <p style="margin: 0; font-size: 12px; color: #666;">Requisitos y alcance</p>
                </div>
                <div style="background: white; padding: 18px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <div style="background: #4CAF50; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto 12px; font-size: 16px;">2</div>
                    <h4 style="color: #4CAF50; margin: 0 0 8px 0; font-size: 14px;">Diseño</h4>
                    <p style="margin: 0; font-size: 12px; color: #666;">UX/UI y arquitectura</p>
                </div>
                <div style="background: white; padding: 18px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <div style="background: #FF9800; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto 12px; font-size: 16px;">3</div>
                    <h4 style="color: #FF9800; margin: 0 0 8px 0; font-size: 14px;">Desarrollo</h4>
                    <p style="margin: 0; font-size: 12px; color: #666;">Codificación y sprints</p>
                </div>
                <div style="background: white; padding: 18px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <div style="background: #9C27B0; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto 12px; font-size: 16px;">4</div>
                    <h4 style="color: #9C27B0; margin: 0 0 8px 0; font-size: 14px;">Testing</h4>
                    <p style="margin: 0; font-size: 12px; color: #666;">QA y pruebas</p>
                </div>
                <div style="background: white; padding: 18px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                    <div style="background: #00BCD4; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto 12px; font-size: 16px;">5</div>
                    <h4 style="color: #00BCD4; margin: 0 0 8px 0; font-size: 14px;">Deploy</h4>
                    <p style="margin: 0; font-size: 12px; color: #666;">Lanzamiento y soporte</p>
                </div>
            </div>
        </div>

        <!-- EQUIPO ASIGNADO -->
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 30px; border-radius: 20px; color: white;">
            <h3 style="margin: 0 0 20px 0; display: flex; align-items: center; gap: 15px; font-size: 20px;">
                <span style="font-size: 32px;">👥</span>
                <span>Equipo Profesional Asignado</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">👨‍💼</div>
                    <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600;">Project Manager</h4>
                    <p style="margin: 0; font-size: 12px; opacity: 0.9;">Gestión y coordinación</p>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">🎨</div>
                    <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600;">UX/UI Designer</h4>
                    <p style="margin: 0; font-size: 12px; opacity: 0.9;">Diseño de interfaz</p>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">👨‍💻</div>
                    <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600;">Developers</h4>
                    <p style="margin: 0; font-size: 12px; opacity: 0.9;">Frontend y Backend</p>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 36px; margin-bottom: 10px;">🔍</div>
                    <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600;">QA Engineer</h4>
                    <p style="margin: 0; font-size: 12px; opacity: 0.9;">Control de calidad</p>
                </div>
            </div>
        </div>

        <!-- GARANTÍAS -->
        <div style="background: #E8F5E9; border: 2px solid #4CAF50; padding: 20px; border-radius: 15px; margin-top: 20px; text-align: center;">
            <p style="margin: 0; font-size: 15px; color: #333; font-weight: 600;">
                ✅ <strong>Comunicación Transparente</strong> • Reportes semanales de progreso •
                <strong>Control Total</strong> • Acceso a herramientas de gestión (Jira/Trello) •
                <strong>Participación Activa</strong> en cada fase
            </p>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🔧 GENERAR SLIDE DE ESPECIFICACIONES TÉCNICAS
   * ═══════════════════════════════════════════════════════════════
   */
  private generateTechnicalSpecsSlide(data: QuotationHtmlData): string {
    // Solo generar si hay plataformas o integraciones
    if ((!data.plataformas || data.plataformas.length === 0) &&
        (!data.integraciones || data.integraciones.length === 0)) {
      return '';
    }

    return `
    <div class="slide">
        <div class="slide-header">
            <h2>🔧 ESPECIFICACIONES TÉCNICAS</h2>
            <p>Plataformas e integraciones requeridas</p>
        </div>

        ${data.plataformas && data.plataformas.length > 0 ? `
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 30px; border-radius: 20px; margin-bottom: 25px;">
            <h3 style="color: white; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 32px;">📱</span>
                <span>Plataformas de Destino</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                ${data.plataformas.map(plataforma => {
                  const icons: any = {
                    'iOS': '🍎',
                    'Android': '🤖',
                    'Web': '🌐',
                    'Windows': '🪟',
                    'macOS': '💻',
                    'Linux': '🐧'
                  };
                  const icon = icons[plataforma] || '📱';
                  return `
                  <div style="background: rgba(255,255,255,0.95); padding: 20px; border-radius: 12px; text-align: center;">
                      <div style="font-size: 40px; margin-bottom: 10px;">${icon}</div>
                      <p style="color: #333; margin: 0; font-weight: 600; font-size: 16px;">${plataforma}</p>
                  </div>
                  `;
                }).join('')}
            </div>
        </div>
        ` : ''}

        ${data.integraciones && data.integraciones.length > 0 ? `
        <div style="background: #f8f9fa; padding: 30px; border-radius: 20px; border: 2px solid #FF9800;">
            <h3 style="color: #FF9800; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <span style="font-size: 32px;">🔗</span>
                <span>Integraciones Requeridas</span>
            </h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                ${data.integraciones.map(integracion => `
                <div style="background: white; padding: 18px; border-radius: 12px; border-left: 4px solid #FF9800; box-shadow: 0 3px 10px rgba(255,152,0,0.1);">
                    <p style="color: #333; margin: 0; font-size: 15px; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 20px;">🔌</span>
                        <strong>${integracion}</strong>
                    </p>
                </div>
                `).join('')}
            </div>
        </div>
        ` : ''}

        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; margin-top: 25px;">
            <h4 style="margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                <span>✨</span>
                <span>Garantías Técnicas</span>
            </h4>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 14px;">
                <div style="text-align: center;">
                    <div style="font-size: 24px; margin-bottom: 5px;">🚀</div>
                    <strong>Alto Rendimiento</strong>
                    <p style="margin: 5px 0; opacity: 0.9; font-size: 12px;">Optimizado para todas las plataformas</p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 24px; margin-bottom: 5px;">🔒</div>
                    <strong>Seguridad Total</strong>
                    <p style="margin: 5px 0; opacity: 0.9; font-size: 12px;">Encriptación y mejores prácticas</p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 24px; margin-bottom: 5px;">📈</div>
                    <strong>Escalabilidad</strong>
                    <p style="margin: 5px 0; opacity: 0.9; font-size: 12px;">Crece con tu negocio</p>
                </div>
            </div>
        </div>
    </div>
    `;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🖼️ OBTENER LOGO NYNEL MKT EN BASE64
   * ═══════════════════════════════════════════════════════════════
   * Convierte el logo a base64 para embederlo directamente en el PDF
   */
  private getLogoBase64(): string {
    try {
      const logoPath = path.join(process.cwd(), 'public', 'assets', 'logo-nynel-mkt.png');

      if (!fs.existsSync(logoPath)) {
        logger.warn(`⚠️ Logo no encontrado en: ${logoPath}`);
        return ''; // Retornar vacío si no existe
      }

      const logoBuffer = fs.readFileSync(logoPath);
      const base64Logo = logoBuffer.toString('base64');

      logger.info('✅ Logo Nynel Mkt cargado exitosamente en base64');

      return `data:image/png;base64,${base64Logo}`;
    } catch (error) {
      logger.error('❌ Error cargando logo:', error);
      return '';
    }
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// EXPORTAR INSTANCIA ÚNICA (SINGLETON)
// ═══════════════════════════════════════════════════════════════════════════
export const quotationHTMLTemplateService = new QuotationHTMLTemplateService();
