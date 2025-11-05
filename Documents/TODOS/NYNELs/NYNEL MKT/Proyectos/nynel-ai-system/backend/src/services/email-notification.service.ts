// ═══════════════════════════════════════════════════════════════════════════
// 📧 EMAIL NOTIFICATION SERVICE - NOTIFICACIONES AL EQUIPO
// ═══════════════════════════════════════════════════════════════════════════
// Servicio para enviar notificaciones por email al equipo interno de NYNEL
// cuando ocurren eventos importantes (reuniones agendadas, cotizaciones, etc.)

import nodemailer from 'nodemailer';
import { logger } from '../utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// Interfaces
// ─────────────────────────────────────────────────────────────────────────────

interface MeetingNotificationData {
  meetingCode: string;
  clientName: string;
  clientPhone: string;
  topic: string;
  scheduledAt: Date;
  duration: number;
  meetUrl: string;
  description?: string;
}

interface QuotationNotificationData {
  quotationCode: string;
  clientName: string;
  clientPhone: string;
  clientEmail?: string;
  serviceName: string;
  priceMin: number;
  priceMax: number;
  pdfUrl: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Configuración
// ─────────────────────────────────────────────────────────────────────────────

const TEAM_EMAIL = process.env.SMTP_FROM_EMAIL || 'empresarial@nynelmkt.com';
const NOTIFICATION_EMAILS = [
  TEAM_EMAIL, // ✅ ÚNICO EMAIL: empresarial@nynelmkt.com
];

// ─────────────────────────────────────────────────────────────────────────────
// Servicio de Notificaciones
// ─────────────────────────────────────────────────────────────────────────────

class EmailNotificationService {
  private transporter: nodemailer.Transporter | null = null;

  /**
   * Inicializa el transporter de nodemailer
   */
  private initializeTransporter(): void {
    if (this.transporter) return;

    try {
      this.transporter = nodemailer.createTransport({
        host: process.env.SMTP_HOST,
        port: parseInt(process.env.SMTP_PORT || '587'),
        secure: process.env.SMTP_SECURE === 'true',
        auth: {
          user: process.env.SMTP_USER,
          pass: process.env.SMTP_PASS,
        },
      });

      logger.info('✅ Email Notification Service: Transporter inicializado');
    } catch (error: any) {
      logger.error('❌ Error inicializando transporter de notificaciones:', error);
    }
  }

  /**
   * Notifica al equipo sobre una nueva reunión agendada
   */
  async notifyNewMeeting(data: MeetingNotificationData): Promise<void> {
    try {
      this.initializeTransporter();
      if (!this.transporter) {
        throw new Error('Transporter no inicializado');
      }

      const formattedDate = new Date(data.scheduledAt).toLocaleString('es-PE', {
        dateStyle: 'full',
        timeStyle: 'short',
        timeZone: 'America/Lima',
      });

      const htmlContent = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
    .content { background: #f9f9f9; padding: 30px; border: 1px solid #e0e0e0; }
    .info-box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .label { font-weight: bold; color: #667eea; margin-bottom: 5px; }
    .value { color: #333; font-size: 16px; }
    .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold; }
    .button:hover { background: #5568d3; }
    .urgent { background: #ff6b6b; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; }
    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🗓️ Nueva Reunión Agendada</h1>
      <p style="margin: 0; font-size: 18px;">Un cliente ha agendado una reunión</p>
    </div>

    <div class="content">
      <div class="urgent">
        ⚠️ <strong>ACCIÓN REQUERIDA:</strong> Un miembro del equipo debe unirse a esta reunión
      </div>

      <div class="info-box">
        <div class="label">📋 Código de Reunión:</div>
        <div class="value" style="font-size: 20px; font-weight: bold; color: #667eea;">${data.meetingCode}</div>
      </div>

      <div class="info-box">
        <div class="label">👤 Cliente:</div>
        <div class="value">${data.clientName}</div>
      </div>

      <div class="info-box">
        <div class="label">📱 Teléfono:</div>
        <div class="value">${data.clientPhone}</div>
      </div>

      <div class="info-box">
        <div class="label">📅 Fecha y Hora:</div>
        <div class="value" style="font-size: 18px; color: #ff6b6b; font-weight: bold;">${formattedDate}</div>
      </div>

      <div class="info-box">
        <div class="label">⏱️ Duración:</div>
        <div class="value">${data.duration} minutos</div>
      </div>

      <div class="info-box">
        <div class="label">💬 Tema:</div>
        <div class="value">${data.topic}</div>
      </div>

      ${data.description ? `
      <div class="info-box">
        <div class="label">📝 Descripción:</div>
        <div class="value">${data.description}</div>
      </div>
      ` : ''}

      <div style="text-align: center; margin: 30px 0;">
        <a href="${data.meetUrl}" class="button">🎥 Unirse a la Reunión</a>
      </div>

      <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <strong>💡 Recordatorio:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
          <li>Revisa el tema antes de la reunión</li>
          <li>Prepara materiales o demos si es necesario</li>
          <li>Únete 5 minutos antes para verificar audio/video</li>
        </ul>
      </div>
    </div>

    <div class="footer">
      <p>Este es un email automático del sistema NYNEL AI</p>
      <p>Para más información, revisa el panel de administración</p>
    </div>
  </div>
</body>
</html>
      `;

      await this.transporter.sendMail({
        from: `"NYNEL AI - Notificaciones" <${TEAM_EMAIL}>`,
        to: NOTIFICATION_EMAILS.join(', '),
        subject: `🗓️ Nueva Reunión: ${data.clientName} - ${data.meetingCode}`,
        html: htmlContent,
      });

      logger.info(`✅ Notificación de reunión enviada: ${data.meetingCode}`);
    } catch (error: any) {
      logger.error('❌ Error enviando notificación de reunión:', error);
      // No lanzar error para no interrumpir el flujo principal
    }
  }

  /**
   * Notifica al equipo sobre una cotización generada
   */
  async notifyNewQuotation(data: QuotationNotificationData): Promise<void> {
    try {
      this.initializeTransporter();
      if (!this.transporter) {
        throw new Error('Transporter no inicializado');
      }

      const priceRange = `S/ ${data.priceMin.toFixed(2)} - S/ ${data.priceMax.toFixed(2)}`;

      const htmlContent = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
    .content { background: #f9f9f9; padding: 30px; border: 1px solid #e0e0e0; }
    .info-box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .label { font-weight: bold; color: #00b894; margin-bottom: 5px; }
    .value { color: #333; font-size: 16px; }
    .button { display: inline-block; padding: 15px 30px; background: #00b894; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold; }
    .button:hover { background: #00a383; }
    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>💰 Nueva Cotización Generada</h1>
      <p style="margin: 0; font-size: 18px;">El bot ha generado una cotización automática</p>
    </div>

    <div class="content">
      <div class="info-box">
        <div class="label">📋 Código de Cotización:</div>
        <div class="value" style="font-size: 20px; font-weight: bold; color: #00b894;">${data.quotationCode}</div>
      </div>

      <div class="info-box">
        <div class="label">👤 Cliente:</div>
        <div class="value">${data.clientName}</div>
      </div>

      <div class="info-box">
        <div class="label">📱 Teléfono:</div>
        <div class="value">${data.clientPhone}</div>
      </div>

      ${data.clientEmail ? `
      <div class="info-box">
        <div class="label">📧 Email:</div>
        <div class="value">${data.clientEmail}</div>
      </div>
      ` : ''}

      <div class="info-box">
        <div class="label">🛠️ Servicio:</div>
        <div class="value">${data.serviceName}</div>
      </div>

      <div class="info-box">
        <div class="label">💵 Rango de Precios:</div>
        <div class="value" style="font-size: 18px; font-weight: bold; color: #00b894;">${priceRange}</div>
      </div>

      <div style="text-align: center; margin: 30px 0;">
        <a href="${data.pdfUrl}" class="button">📄 Ver Cotización PDF</a>
      </div>

      <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <strong>💡 Próximos pasos sugeridos:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
          <li>Revisa la cotización para validar precios y alcance</li>
          <li>Prepárate para dar seguimiento al cliente</li>
          <li>Ajusta si es necesario y reenvía manualmente</li>
        </ul>
      </div>
    </div>

    <div class="footer">
      <p>Este es un email automático del sistema NYNEL AI</p>
      <p>Para más información, revisa el panel de administración</p>
    </div>
  </div>
</body>
</html>
      `;

      await this.transporter.sendMail({
        from: `"NYNEL AI - Notificaciones" <${TEAM_EMAIL}>`,
        to: NOTIFICATION_EMAILS.join(', '),
        subject: `💰 Nueva Cotización: ${data.clientName} - ${data.quotationCode}`,
        html: htmlContent,
      });

      logger.info(`✅ Notificación de cotización enviada: ${data.quotationCode}`);
    } catch (error: any) {
      logger.error('❌ Error enviando notificación de cotización:', error);
      // No lanzar error para no interrumpir el flujo principal
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Exportar instancia singleton
// ─────────────────────────────────────────────────────────────────────────────

export const emailNotificationService = new EmailNotificationService();
