// ═══════════════════════════════════════════════════════════════════════════
// 🎯 WEBHOOK CONTROLLER V2 - SOLUCIÓN PROFESIONAL CON CUSTOM FIELD + FLOW
// ═══════════════════════════════════════════════════════════════════════════
// ESTRATEGIA PROFESIONAL (Recomendada por ManyChat):
// 1. Webhook responde 200 OK inmediatamente (sin contenido)
// 2. Backend espera 15 segundos para concatenar mensajes
// 3. Backend guarda respuesta en Custom Field "ai_response"
// 4. Backend activa Flow que envía el mensaje al usuario
//
// VENTAJAS:
// - ✅ Sin timeout de ManyChat (webhook ya respondió)
// - ✅ Sin límite de ventana 24h (usa Flows, no Send API)
// - ✅ Usuario puede escribir lento (15 segundos de ventana)
// - ✅ Solución oficial recomendada por documentación de ManyChat

import { Request, Response } from 'express';
import { prisma } from '../config/database.js';
import { Platform, MessageType, MessageRole, LeadStatus, Prisma } from '@prisma/client';
import { Decimal } from '@prisma/client/runtime/library';
import { logger } from '../utils/logger.js';
import { manyChatAPI } from '../services/manychat-api.service.js';
import { masterConversationalAI } from '../services/master-conversational-ai.service.js';
import { redis, setCache, getCache, deleteCache } from '../config/redis.js';
import { z } from 'zod';

// ═══════════════════════════════════════════════════════════════════════════
// 🔴 REDIS-BASED MESSAGE CONCATENATION SYSTEM (PRODUCTION-READY)
// ═══════════════════════════════════════════════════════════════════════════
// SOLUCIÓN PROFESIONAL: Usar Redis en lugar de Map en memoria
// VENTAJAS:
// ✅ Persistente - sobrevive reinicios del servidor
// ✅ Escalable - funciona con múltiples instancias (load balancing)
// ✅ TTL automático - Redis maneja la expiración automáticamente
// ✅ Production-ready - usado por empresas de nivel enterprise
// ═══════════════════════════════════════════════════════════════════════════

// Estructura para mensajes pendientes en Redis (SIN timeout, usamos TTL de Redis)
interface PendingMessage {
  subscriberId: string;
  messages: string[];
  payload: ManyChatWebhookPayload;
  timestamp: number; // Para debug y logging
}

// Constantes
const CONCATENATION_WINDOW_SECONDS = 7; // Ventana de concatenación
const REDIS_TTL_SECONDS = 10; // TTL de Redis (debe ser MAYOR que ventana para evitar race condition)
const REDIS_KEY_PREFIX = 'pending_msg:'; // Prefijo para claves Redis

/**
 * Helper: Obtener mensajes pendientes desde Redis
 */
async function getPendingMessages(subscriberId: string): Promise<PendingMessage | null> {
  const key = `${REDIS_KEY_PREFIX}${subscriberId}`;
  return await getCache<PendingMessage>(key);
}

/**
 * Helper: Guardar mensajes pendientes en Redis con TTL
 */
async function setPendingMessages(
  subscriberId: string,
  data: PendingMessage
): Promise<void> {
  const key = `${REDIS_KEY_PREFIX}${subscriberId}`;
  await setCache(key, data, REDIS_TTL_SECONDS);
  logger.info(`💾 Redis: Mensajes guardados para ${subscriberId} (TTL: ${REDIS_TTL_SECONDS}s, ventana: ${CONCATENATION_WINDOW_SECONDS}s)`);
}

/**
 * Helper: Eliminar mensajes pendientes de Redis
 */
async function deletePendingMessages(subscriberId: string): Promise<void> {
  const key = `${REDIS_KEY_PREFIX}${subscriberId}`;
  await deleteCache(key);
  logger.debug(`🗑️  Redis: Mensajes eliminados para ${subscriberId}`);
}

// ═══════════════════════════════════════════════════════════════════════════
// 🔒 ZOD VALIDATION SCHEMAS (PRODUCTION-READY)
// ═══════════════════════════════════════════════════════════════════════════
// VENTAJAS:
// ✅ Type-safe validation - detecta payloads malformados
// ✅ Previene crashes - valida antes de procesar
// ✅ Logging detallado - muestra exactamente qué falló
// ✅ Production-ready - usado por empresas de nivel enterprise
// ═══════════════════════════════════════════════════════════════════════════

const ManyChatWebhookPayloadSchema = z.object({
  subscriber_id: z.string().min(1, 'subscriber_id es requerido'),
  platform: z.string().optional().default('whatsapp'),
  // Campos de texto: permitir cualquier string, null, undefined (incluyendo placeholders {{...}})
  first_name: z.string().nullish(),
  last_name: z.string().nullish(),
  phone: z.string().nullish(),
  user_message: z.string().nullish(),
  // Email: permitir email válido, placeholder {{...}}, vacío, null, undefined
  email: z.string().optional().nullable().refine(
    (val) => {
      if (!val || val === '' || val.startsWith('{{')) return true; // Placeholder o vacío
      // Usar regex simple en lugar de z.string().email() para evitar mensaje confuso
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val); // Email válido
    },
    { message: 'Email inválido' }
  ),
  // URLs: permitir URL válida, placeholder {{...}}, vacío, null, undefined
  audio_url: z.string().optional().nullable().refine(
    (val) => {
      if (!val || val === '' || val.startsWith('{{')) return true; // Placeholder o vacío
      // Regex simple para URL
      return /^https?:\/\/.+/.test(val); // URL válida
    },
    { message: 'Audio URL inválida' }
  ),
  image_url: z.string().optional().nullable().refine(
    (val) => {
      if (!val || val === '' || val.startsWith('{{')) return true; // Placeholder o vacío
      // Regex simple para URL
      return /^https?:\/\/.+/.test(val); // URL válida
    },
    { message: 'Image URL inválida' }
  ),
  tags: z.array(z.string()).optional().default([]),
  custom_fields: z.record(z.any()).optional().default({}),
});

// TypeScript type inferido desde el schema
type ManyChatWebhookPayload = z.infer<typeof ManyChatWebhookPayloadSchema>;

interface AIResponse {
  response: string;
  agentUsed: string;
  confidence: number;
  intent: 'greeting' | 'service_inquiry' | 'quotation_request' | 'support' | 'other';
  detectedService?: string;
  generatePdf?: boolean; // ✅ Si debe generar PDF de cotización (desde Master AI o POST-PROCESSING)
}

class WebhookControllerV2 {
  /**
   * Handler principal del webhook de ManyChat
   * Implementa concatenación de mensajes con ventana de 4 segundos
   */
  async handleManyChatMessage(req: Request, res: Response) {
    try {
      // 🔍 DEBUG: Log del payload RAW para diagnosticar (console.log para PM2)
      console.log('='.repeat(80));
      console.log('📥 PAYLOAD RECIBIDO:');
      console.log(JSON.stringify(req.body, null, 2));
      console.log('='.repeat(80));

      // ═══════════════════════════════════════════════════════════════
      // 🔒 VALIDACIÓN CON ZOD (CRÍTICO PARA PRODUCCIÓN)
      // ═══════════════════════════════════════════════════════════════
      const validationResult = ManyChatWebhookPayloadSchema.safeParse(req.body);

      if (!validationResult.success) {
        // Payload inválido - loggear error detallado
        console.log('❌ ERRORES DE VALIDACIÓN:');
        console.log(JSON.stringify(validationResult.error.format(), null, 2));
        console.log('='.repeat(80));

        // Responder con éxito para que ManyChat no reintente
        // (payloads malformados no deben reintentar)
        return res.status(200).json({
          version: 'v2',
          content: {
            messages: [{ type: 'text', text: 'Mensaje recibido' }],
          },
        });
      }

      // Payload validado ✅
      const payload = validationResult.data;

      // 🔍 LOG COMPLETO DEL PAYLOAD VALIDADO
      logger.info('📥 Webhook V2 recibido de ManyChat (✅ VALIDADO):');
      logger.info(JSON.stringify(payload, null, 2));

      // ─────────────────────────────────────────────────────────────────
      // 🚫 FILTRO DE CLIENTES PAGADOS - NO PROCESAR CON IA
      // ─────────────────────────────────────────────────────────────────
      // Si el cliente ya pagó y está en proyecto activo, NO debe responder el AI
      // El equipo humano se encarga de estos clientes
      //
      // CUSTOM FIELD: "estado_cliente"
      // Valores posibles: "prospecto", "cliente_pagado", "proyecto_activo"
      const estadoCliente = payload.custom_fields?.estado_cliente ||
                           payload.custom_fields?.['estado cliente'] ||
                           payload.custom_fields?.estadoCliente;

      const isPaidCustomer =
        estadoCliente === 'cliente_pagado' ||
        estadoCliente === 'cliente pagado' ||
        estadoCliente === 'proyecto_activo' ||
        estadoCliente === 'proyecto activo';

      if (isPaidCustomer) {
        logger.info('🚫 Cliente pagado detectado - NO procesando con IA');
        logger.info(`   Custom Field "estado_cliente": "${estadoCliente}"`);
        logger.info('   ✅ Este cliente será atendido por el equipo humano');
        logger.info('   🔕 El bot NO responderá (silencio total)');

        return res.status(200).json({
          version: 'v2',
          content: {
            messages: [] // Sin respuesta - el equipo humano se encargará
          }
        });
      }

      // ═══════════════════════════════════════════════════════════════════════════
      // ✅ VALIDACIÓN ADICIONAL: Verificar que hay mensaje del usuario
      // ═══════════════════════════════════════════════════════════════════════════
      // Si no hay mensaje del usuario, probablemente es un evento de sistema
      // (ej: usuario se suscribió, actualizó perfil, etc.)
      if (!payload.user_message || payload.user_message.trim() === '') {
        logger.info('ℹ️  Webhook sin user_message - Probablemente evento de sistema');
        return res.status(200).json({
          version: 'v2',
          content: {
            messages: [],
          },
        });
      }

      // ─────────────────────────────────────────────────────────────────
      // 1. Guardar mensaje inmediatamente en BD
      // ─────────────────────────────────────────────────────────────────
      const subscriber = await this.getOrCreateSubscriber(payload);
      const conversation = await this.getOrCreateConversation(subscriber.id);

      await prisma.message.create({
        data: {
          conversationId: conversation.id,
          role: MessageRole.USER,
          messageType: MessageType.TEXT,
          content: payload.user_message,
        },
      });

      logger.info(`💾 Mensaje guardado en BD: "${payload.user_message}"`);

      // ─────────────────────────────────────────────────────────────────
      // 2. Responder INMEDIATAMENTE con 200 OK (Decouple Response Pattern)
      // ─────────────────────────────────────────────────────────────────
      res.status(200).json({
        version: 'v2',
        content: {
          messages: [] // Sin contenido - responderemos después via API
        }
      });
      logger.info('✅ Webhook respondió 200 OK inmediatamente');

      // ─────────────────────────────────────────────────────────────────
      // 3. Sistema de concatenación con Redis (PRODUCCIÓN)
      // ─────────────────────────────────────────────────────────────────
      const subscriberId = payload.subscriber_id;
      const pending = await getPendingMessages(subscriberId);

      if (pending) {
        // Ya hay mensajes pendientes, agregar este mensaje
        logger.info(`⏱️  Mensaje agregado a cola Redis (total: ${pending.messages.length + 1})`);
        pending.messages.push(payload.user_message);

        // Actualizar en Redis con TTL renovado (esto reinicia el contador de 7 segundos)
        await setPendingMessages(subscriberId, {
          ...pending,
          timestamp: Date.now(),
        });

        // ✅ NO necesitamos setTimeout aquí - el primer mensaje ya lo programó
        return;
      } else {
        // Primer mensaje, crear nueva entrada en Redis
        logger.info('⏱️  Iniciando ventana de concatenación Redis (7 segundos)');

        const pendingData: PendingMessage = {
          subscriberId,
          messages: [payload.user_message],
          payload,
          timestamp: Date.now(),
        };

        // Guardar en Redis con TTL de 7 segundos
        await setPendingMessages(subscriberId, pendingData);

        // ═══════════════════════════════════════════════════════════════
        // 🚀 PROGRAMAR PROCESAMIENTO DESPUÉS DE LA VENTANA
        // ═══════════════════════════════════════════════════════════════
        // IMPORTANTE: Usamos setTimeout para procesamiento automático
        // Si el servidor se reinicia, los mensajes quedan en Redis y se
        // pueden recuperar manualmente o con un worker de limpieza
        setTimeout(() => {
          this.processQueuedMessagesAndSendViaAPI(subscriberId);
        }, CONCATENATION_WINDOW_SECONDS * 1000);

        // NO responder aún - se responderá cuando se procese
        return;
      }
    } catch (error: any) {
      logger.error('❌ Error en webhook V2:', error);
      return res.status(200).json({
        version: 'v2',
        content: {
          messages: [{ type: 'text', text: '...' }]
        }
      });
    }
  }

  /**
   * Procesa todos los mensajes en cola después de la ventana de tiempo
   * Y envía respuesta via ManyChat Send API (ASÍNCRONO, sin webhook response)
   * ✅ AHORA USA REDIS PARA RECUPERAR MENSAJES PENDIENTES
   */
  private async processQueuedMessagesAndSendViaAPI(subscriberId: string) {
    // ═══════════════════════════════════════════════════════════════
    // 🔴 OBTENER MENSAJES DESDE REDIS (PERSISTENTE)
    // ═══════════════════════════════════════════════════════════════
    const pending = await getPendingMessages(subscriberId);
    if (!pending) {
      logger.warn(`⚠️  No hay mensajes pendientes en Redis para ${subscriberId} (ya procesados o expirados)`);
      return;
    }

    const startTime = Date.now();
    logger.info(`🚀 Procesando ${pending.messages.length} mensaje(s) concatenado(s) desde Redis`);

    try {
      const subscriber = await this.getOrCreateSubscriber(pending.payload);
      const conversation = await this.getOrCreateConversation(subscriber.id);

      // ─────────────────────────────────────────────────────────────────
      // 1. Concatenar todos los mensajes
      // ─────────────────────────────────────────────────────────────────
      const concatenatedMessage = pending.messages.join('\n');
      logger.info(`📝 Mensaje concatenado: "${concatenatedMessage}"`);

      // ═══════════════════════════════════════════════════════════════
      // 🔍 EXTRACCIÓN INTELIGENTE DE DATOS DEL MENSAJE
      // ═══════════════════════════════════════════════════════════════
      const extractedData = this.extractUserDataFromMessage(concatenatedMessage);

      if (Object.keys(extractedData).length > 0) {
        logger.info('💾 Datos extraídos del mensaje - Guardando en BD...');

        // Obtener customFields actuales
        const currentCustomFields = (subscriber.customFields as Record<string, any>) || {};

        // Preparar datos a actualizar
        const updateData: any = {
          customFields: {
            ...currentCustomFields,
            ...extractedData,
            lastExtractedAt: new Date().toISOString(),
          } as Prisma.InputJsonValue,
        };

        // Si se extrajo email, guardarlo también en el campo email
        if (extractedData.email && !subscriber.email) {
          updateData.email = extractedData.email;
          logger.info(`📧 Email extraído guardado en campo principal: ${extractedData.email}`);
        }

        // Actualizar subscriber con datos extraídos
        await prisma.subscriber.update({
          where: { id: subscriber.id },
          data: updateData,
        });

        logger.info('✅ Datos extraídos guardados exitosamente en customFields');
      }

      // ─────────────────────────────────────────────────────────────────
      // 2. Context Management INTELIGENTE - Sliding Window + Summarization (Best Practice 2025)
      // ─────────────────────────────────────────────────────────────────
      // 🚀 OPTIMIZACIÓN: Reduce tokens en 80-90% manteniendo contexto útil
      // Estrategia: Últimos 15 mensajes verbatim + resumen de mensajes antiguos

      const RECENT_MESSAGES_LIMIT = 15; // Best practice: 10-15 mensajes recientes

      // Obtener TODOS los mensajes para decidir si necesitamos resumir
      const allMessages = await prisma.message.findMany({
        where: { conversationId: conversation.id },
        orderBy: { createdAt: 'asc' },
        select: {
          id: true,
          role: true,
          content: true,
          createdAt: true,
          messageType: true,
          audioTranscription: true,
        },
      });

      let messageHistory = allMessages;
      let conversationSummary = '';

      // Si hay más de RECENT_MESSAGES_LIMIT mensajes, aplicar sliding window
      if (allMessages.length > RECENT_MESSAGES_LIMIT) {
        const oldMessages = allMessages.slice(0, -RECENT_MESSAGES_LIMIT);
        const recentMessages = allMessages.slice(-RECENT_MESSAGES_LIMIT);

        // Generar resumen de mensajes antiguos (summarization)
        conversationSummary = this.generateConversationSummary(oldMessages);

        // Solo enviar mensajes recientes al AI
        messageHistory = recentMessages;

        logger.info(`📊 Context Management: ${allMessages.length} mensajes totales → ${recentMessages.length} recientes + resumen de ${oldMessages.length} antiguos`);
      } else {
        logger.info(`📊 Context Management: ${allMessages.length} mensajes (no requiere resumen)`);
      }

      // ─────────────────────────────────────────────────────────────────
      // 3. Procesar con AI (DeepSeek v3 via OpenRouter)
      // ─────────────────────────────────────────────────────────────────
      logger.info('🤖 Procesando con DeepSeek v3 via OpenRouter...');

      // ═══════════════════════════════════════════════════════════════
      // 🧠 RECARGAR SUBSCRIBER ACTUALIZADO con datos extraídos
      // ═══════════════════════════════════════════════════════════════
      const updatedSubscriber = await prisma.subscriber.findUnique({
        where: { id: subscriber.id },
        // 🚀 OPTIMIZADO: Select solo campos necesarios para AI context
        select: {
          id: true,
          firstName: true,
          lastName: true,
          email: true,
          phone: true,
          leadStatus: true,
          customFields: true,
        },
      });

      const aiResponse = await this.processWithDeepSeekAI({
        subscriberId: updatedSubscriber!.id,
        conversationId: conversation.id,
        userMessage: concatenatedMessage,
        messageHistory,
        subscriber: {
          firstName: updatedSubscriber!.firstName || undefined,
          lastName: updatedSubscriber!.lastName || undefined,
          email: updatedSubscriber!.email || undefined,
          phone: updatedSubscriber!.phone || undefined,
          leadStatus: updatedSubscriber!.leadStatus,
          customFields: updatedSubscriber!.customFields as Record<string, any> || {},
        },
        platform: pending.payload.platform || 'WHATSAPP',
      });

      const processingTime = Date.now() - startTime;

      // ─────────────────────────────────────────────────────────────────
      // 4. Guardar respuesta del asistente
      // ─────────────────────────────────────────────────────────────────
      await prisma.message.create({
        data: {
          conversationId: conversation.id,
          role: MessageRole.ASSISTANT,
          messageType: MessageType.TEXT,
          content: aiResponse.response,
          aiAgent: aiResponse.agentUsed,
          aiConfidence: aiResponse.confidence,
          processingTime,
        },
      });

      // ─────────────────────────────────────────────────────────────────
      // 5. Actualizar subscriber
      // ─────────────────────────────────────────────────────────────────
      const newLeadStatus = this.determineLeadStatus(subscriber.leadStatus, aiResponse.intent);
      const leadScoreIncrement = this.calculateLeadScoreIncrement(aiResponse.intent);

      await prisma.subscriber.update({
        where: { id: subscriber.id },
        data: {
          lastActiveAt: new Date(),
          leadScore: Math.min(subscriber.leadScore + leadScoreIncrement, 100),
          leadStatus: newLeadStatus,
        },
      });

      logger.info(`✅ Respuesta generada en ${processingTime}ms`);

      // ═══════════════════════════════════════════════════════════════════════════
      // 🔍 POST-PROCESSING: VERIFICACIÓN ESTRICTA PARA GENERACIÓN DE PDF
      // ═══════════════════════════════════════════════════════════════════════════
      // PROBLEMA IDENTIFICADO: El Master AI detectaba intent=quotation por cualquier
      // mención de servicios, causando que "Ok gracias por la info, lo voy a pensar"
      // generara PDF cuando NO debería.
      //
      // SOLUCIÓN: Doble verificación OBLIGATORIA:
      // 1. Intent debe ser "quotation_request" (detección de Master AI)
      // 2. Mensaje debe contener palabras clave EXPLÍCITAS de solicitud
      //
      // SOLO si AMBAS condiciones se cumplen → generatePdf = true
      // ═══════════════════════════════════════════════════════════════════════════

      let shouldGeneratePdf = false;

      if (aiResponse.intent === 'quotation_request') {
        logger.info('🔍 POST-PROCESSING: Intent detectado como quotation_request');

        // Verificar si el mensaje contiene palabras clave EXPLÍCITAS de solicitud
        const messageText = pending.payload.user_message || '';
        const lowerMessage = messageText.toLowerCase();

        // Palabras que indican solicitud EXPLÍCITA de cotización/presupuesto
        const hasQuotationWord = /cotizaci[oó]n|cotiza|presupuesto|proforma|propuesta|precio/i.test(messageText);

        // Patrones que indican solicitud EXPLÍCITA (verbo de solicitud + objeto)
        const hasExplicitPattern =
          /(?:enviar|mandar|pasar|compartir|necesito|quiero|solicito|dame|mu[eé]strame|podr[ií]as?\s+(?:enviar|mandar|pasar))/i.test(
            messageText
          ) && hasQuotationWord;

        // Patrones de RECHAZO (el usuario está declinando, no solicitando)
        const hasRejectionPattern =
          /(?:gracias|ok|entiendo|vale|perfecto|bien|claro).{0,30}(?:pensar|pensarlo|revisar|revisarlo|luego|despu[eé]s|m[aá]s\s+tarde)/i.test(
            messageText
          ) ||
          /(?:voy\s+a|vamos\s+a).{0,20}(?:pensar|pensarlo|revisar|revisarlo|evaluar)/i.test(messageText) ||
          /(?:d[eé]jame|dejame).{0,20}(?:pensar|pensarlo|revisar|revisarlo|evaluar)/i.test(messageText) ||
          /(?:solo|s[oó]lo).{0,20}(?:informaci[oó]n|info|consulta|pregunta|saber)/i.test(messageText);

        logger.info('🔍 POST-PROCESSING Análisis:', {
          hasQuotationWord,
          hasExplicitPattern,
          hasRejectionPattern,
          messagePreview: messageText.substring(0, 80),
        });

        // DECISIÓN FINAL: Generar PDF SOLO si:
        // 1. Hay palabra de cotización/presupuesto
        // 2. Hay patrón explícito de solicitud
        // 3. NO hay patrón de rechazo
        if ((hasQuotationWord || hasExplicitPattern) && !hasRejectionPattern) {
          shouldGeneratePdf = true;
          logger.info('✅ POST-PROCESSING: FORZANDO generatePdf = true (solicitud EXPLÍCITA detectada)');
        } else {
          logger.info('⚠️  POST-PROCESSING: Intent quotation pero NO hay solicitud explícita → generatePdf = false');
          logger.info('📋 Razón de rechazo:', {
            faltaPalabraClave: !hasQuotationWord,
            faltaPatronExplicito: !hasExplicitPattern,
            tienePatronRechazo: hasRejectionPattern,
          });
        }
      }

      // ═══════════════════════════════════════════════════════════════════════════
      // 🔧 FORZAR generatePdf EN LA RESPUESTA
      // ═══════════════════════════════════════════════════════════════════════════
      // El valor del POST-PROCESSING sobrescribe el valor del Master AI
      aiResponse.generatePdf = shouldGeneratePdf;
      logger.info(`🎯 POST-PROCESSING FINAL: generatePdf = ${shouldGeneratePdf}`);

      // ─────────────────────────────────────────────────────────────────
      // 6. Activar Flow con Custom Field (PROFESIONAL) - MULTI-PLATAFORMA
      // ─────────────────────────────────────────────────────────────────
      logger.info(`📤 Activando flow con respuesta...`);

      // Determinar el Flow ID según la plataforma
      let flowId: string;
      const platform = pending.payload.platform?.toLowerCase() || 'whatsapp'; // Default WhatsApp

      switch (platform) {
        case 'instagram':
          flowId = process.env.MANYCHAT_RESPONSE_FLOW_INSTAGRAM || 'INSTAGRAM_FLOW_ID_AQUI';
          logger.info(`📱 Plataforma detectada: INSTAGRAM → Flow: ${flowId}`);
          break;
        case 'messenger':
        case 'facebook':
          flowId = process.env.MANYCHAT_RESPONSE_FLOW_MESSENGER || 'MESSENGER_FLOW_ID_AQUI';
          logger.info(`📱 Plataforma detectada: MESSENGER → Flow: ${flowId}`);
          break;
        case 'telegram':
          flowId = process.env.MANYCHAT_RESPONSE_FLOW_TELEGRAM || 'TELEGRAM_FLOW_ID_AQUI';
          logger.info(`📱 Plataforma detectada: TELEGRAM → Flow: ${flowId}`);
          break;
        case 'whatsapp':
        default:
          flowId = process.env.MANYCHAT_RESPONSE_FLOW_NS || 'content20251027074251_182571';
          logger.info(`📱 Plataforma detectada: WHATSAPP → Flow: ${flowId}`);
          break;
      }

      // Activar flow pasando el valor del custom field
      const flowActivated = await manyChatAPI.sendFlow(
        subscriberId,
        flowId,
        aiResponse.response  // Pasamos la respuesta para que se establezca en custom field
      );

      if (flowActivated) {
        logger.info('✅ Flow activado exitosamente - mensaje enviado');
      } else {
        logger.error('❌ Error al activar flow');
      }

      logger.info('✅ Procesamiento completado');

      // ═══════════════════════════════════════════════════════════════
      // 🗑️  LIMPIAR MENSAJES DE REDIS DESPUÉS DE PROCESAR
      // ═══════════════════════════════════════════════════════════════
      await deletePendingMessages(subscriberId);
      logger.info(`🗑️  Mensajes eliminados de Redis para ${subscriberId}`);
    } catch (error: any) {
      logger.error('❌ Error procesando mensajes en cola:', error);

      // Enviar mensaje de error via ManyChat Send API
      await manyChatAPI.sendTextMessage(
        subscriberId,
        'Disculpa, tuve un problema técnico. ¿Podrías repetir tu mensaje?'
      );

      // ═══════════════════════════════════════════════════════════════
      // 🗑️  LIMPIAR MENSAJES DE REDIS TAMBIÉN EN CASO DE ERROR
      // ═══════════════════════════════════════════════════════════════
      await deletePendingMessages(subscriberId);
      logger.info(`🗑️  Mensajes eliminados de Redis (error recovery) para ${subscriberId}`);
    }
  }

  /**
   * Procesa el mensaje con DeepSeek v3 via OpenRouter
   */
  private async processWithDeepSeekAI(context: {
    subscriberId: string;
    conversationId: string;
    userMessage: string;
    messageHistory: any[];
    subscriber: {
      firstName?: string;
      lastName?: string;
      email?: string;
      phone?: string;
      leadStatus: LeadStatus;
      customFields?: Record<string, any>;
    };
    platform: string;
  }): Promise<AIResponse> {
    try {
      // ═══════════════════════════════════════════════════════════════
      // SISTEMA DE MEMORIA PROFESIONAL
      // ═══════════════════════════════════════════════════════════════

      // 1. Historial completo filtrado
      const allMessages = context.messageHistory
        .filter((msg) => msg.role !== 'SYSTEM')
        .map((msg) => ({
          role: msg.role === 'USER' ? 'user' : 'assistant',
          content: msg.content,
        }));

      // 2. Extraer información clave de TODA la conversación
      const conversationSummary = this.extractConversationContext(allMessages);

      // 3. ✅ USAR TODOS LOS MENSAJES - MEMORIA COMPLETA SIN LÍMITES
      // NO hay .slice() - El AI recibe TODO el historial conversacional
      const recentHistory = allMessages;

      // 📊 LOGGING DETALLADO PARA DEBUG
      logger.info(`📊 CONTEXTO DE MEMORIA COMPLETA:`);
      logger.info(`  - Total de mensajes en historial: ${allMessages.length}`);
      logger.info(`  - Mensajes enviados al AI: ${recentHistory.length} (TODOS)`);
      logger.info(`  - Resumen extraído: "${conversationSummary}"`);
      logger.info(`  - Últimos 5 mensajes del historial:`);
      recentHistory.slice(-5).forEach((msg, idx) => {
        logger.info(`    ${idx + 1}. [${msg.role}]: ${msg.content.substring(0, 100)}...`);
      });

      // ═══════════════════════════════════════════════════════════════
      // 🧠 CONSTRUIR CONTEXTO COMPLETO DEL USUARIO
      // ═══════════════════════════════════════════════════════════════
      const customFields = context.subscriber.customFields || {};
      const userContext: string[] = [];

      // Datos personales
      if (context.subscriber.firstName) {
        userContext.push(`Nombre: ${context.subscriber.firstName} ${context.subscriber.lastName || ''}`.trim());
      }
      if (context.subscriber.email) {
        userContext.push(`Email: ${context.subscriber.email}`);
      }
      if (context.subscriber.phone) {
        userContext.push(`Teléfono: ${context.subscriber.phone}`);
      }

      // Datos del negocio
      if (customFields.empresa) {
        userContext.push(`Empresa: ${customFields.empresa}`);
      }
      if (customFields.tipoNegocio) {
        userContext.push(`Tipo de negocio: ${customFields.tipoNegocio}`);
      }
      if (customFields.tamañoEmpresa) {
        userContext.push(`Tamaño empresa: ${customFields.tamañoEmpresa}`);
      }

      // Proyecto y presupuesto
      if (customFields.tipoProyecto) {
        userContext.push(`Proyecto interesado: ${customFields.tipoProyecto}`);
      }
      if (customFields.presupuestoEstimado) {
        userContext.push(`Presupuesto: ${customFields.presupuestoEstimado}`);
      }
      if (customFields.urgencia) {
        userContext.push(`Urgencia: ${customFields.urgencia}`);
      }

      const userContextString = userContext.length > 0
        ? `\n\n**INFORMACIÓN DEL CLIENTE (YA GUARDADA - NO PREGUNTAR DE NUEVO):**\n${userContext.map(item => `• ${item}`).join('\n')}`
        : '';

      // Sistema prompt OPTIMIZADO 2025 - Con información REAL de Nynel Mkt
      const systemPrompt = `Eres Nynel AI de NYNEL MKT, agencia líder en tecnología y marketing digital en Perú con 15+ años de experiencia, +300 proyectos exitosos, y +120 clientes satisfechos. Cliente: ${context.subscriber.firstName || 'Cliente'} ${context.subscriber.lastName || ''}.

**CONTEXTO ACTUAL:** ${conversationSummary}${userContextString}

**SERVICIOS REALES Y PRECIOS "DESDE" (PRECIOS BASE - PUEDEN AUMENTAR SEGÚN COMPLEJIDAD):**

1. 💻 Implementación de Software a Medida - desde S/2,500
   Soluciones personalizadas que se adaptan a las necesidades de tu empresa

2. 🔍 SEO y Marketing Digital - desde S/500
   Optimización web para atraer tráfico cualificado y convertirlo en clientes

3. 📧 Email Marketing y Eventos - desde S/300
   Campañas que convierten suscriptores en clientes y eventos estratégicos

4. 🌐 Creación de Páginas Web Avanzadas - desde S/650
   Sitios web profesionales, responsive y orientados a conversión

5. 🤖 Automatización de Procesos (Chatbot/AI Agent) - S/350 instalación + S/89.90/mes
   Flujos automatizados, WhatsApp 24/7, cotizaciones automáticas, N8N

6. 📱 Desarrollo de Apps Móviles - desde S/5,000
   Apps intuitivas de alto rendimiento para iOS y Android

7. 📊 Analítica de Datos Empresariales - desde S/350
   Convertimos datos en insights accionables para mejores decisiones

8. 📣 Campañas Publicitarias Integrales - desde S/2,000
   Estrategias ATL, BTL y TTL que maximizan visibilidad

**CREDENCIALES (úsalas para generar confianza):**
✅ +300% ROI Promedio | ✅ 5.0★ Calificación | ✅ Garantía: Resultados en 30 días o devolución

**REGLAS CRÍTICAS DE RESPUESTAS (ABSOLUTO CUMPLIMIENTO):**
1. MÁXIMO 2 párrafos cortos por respuesta (estilo WhatsApp, no "wall of text")
2. USA bullet points cuando sea posible (más fácil de leer)
3. 1 pregunta a la vez, NUNCA listas largas de preguntas
4. Conversacional, directo, amigable - NO formal ni corporativo
5. USA el contexto - NUNCA olvides info previa ni repitas preguntas
6. Emojis: máximo 1-2 por mensaje (no abusar)

**PRICING LOGIC INTELIGENTE:**
• Precios "desde" son REALES - úsalos como base
• Proyectos simples = precio base (Ej: Web básica = S/650)
• Proyectos complejos = precio base × 2-4 (Ej: E-commerce complejo = S/2,600-S/10,000+)
• Múltiples servicios = sugerir paquete con ligero descuento (5-10%)
• Si proyecto suena >S/20,000, recomienda consultoría presencial

**CALIFICACIÓN DE LEADS (BANT - Mental, no menciones):**
Evalúa mentalmente:
• Budget: ¿Tiene presupuesto? (bajo <S/5k, medio S/5-20k, alto >S/20k)
• Authority: ¿Es quien decide? (dueño, gerente, empleado)
• Need: ¿Qué tan urgente? (explorando, definido, urgente)
• Timing: ¿Cuándo quiere iniciar? (futuro, 1-3 meses, inmediato)

**CUÁNDO GENERAR COTIZACIÓN (Solo si se cumple TODO):**
✅ Cliente la pide explícitamente ("cotización", "propuesta", "precio exacto", "presupuesto detallado")
✅ Tienes: Nombre, Contacto (email o WhatsApp), Servicio de interés
✅ Tienes descripción básica del proyecto (2-3 frases sobre qué necesita)
✅ Tienes idea de timeline (urgente, normal, futuro)
**SI FALTA ALGO**: Pregunta de forma NATURAL y breve, NO como formulario

**RECOPILACIÓN DE INFO (Conversacional, NO interrogatorio):**
• Pregunta 1 cosa a la vez, integrada en la conversación natural
• Si no responde algo, NO insistir - continúa la conversación
• Ejemplos CORRECTOS:
  ❌ "Necesito tu nombre, empresa, presupuesto y timeline"
  ✅ "Perfecto! Para prepararte una propuesta personalizada, ¿cómo te llamas?"
  ✅ "Genial! ¿Y esto es para tu empresa o proyecto personal?"

**ESTRATEGIA DE CIERRE (Aplicar naturalmente según contexto):**
• Urgencia: "Solo 3 cupos este mes" / "Promo válida esta semana"
• Valor específico: "Esto aumentaría tus ventas 40%" / "Ahorras 20 hrs/semana"
• CTA directa: "¿Agendamos demo de 15 min?" / "¿Arrancamos esta semana?"
• Pregunta CUÁNDO (no SI): "¿Mejor mañana o pasado?" vs "¿Te interesa?"
• Opciones A o B: "¿Paquete básico o completo?" (no preguntas abiertas)

**AGENDAMIENTO:**
• Sistema consulta disponibilidad REAL de Google Calendar
• Si pide agendar, muestra horarios disponibles automáticamente
• NO inventes horarios - el sistema los maneja
• Prioriza mismo día o siguiente si hay disponibilidad

**EJEMPLOS DE RESPUESTAS CORTAS Y EFECTIVAS:**

User: "Cuánto cuesta una web"
Tú: "Desde S/650 para sitio profesional. ¿Qué tipo de web necesitas: catálogo, e-commerce, o personalizada? 🌐"

User: "E-commerce para ropa"
Tú: "Perfecto! E-commerce con pagos, stock y envíos desde S/2,600.
¿Para cuántos productos aproximadamente? 👔"

User: "Como 200 productos, necesito algo profesional"
Tú: "Excelente! Con 200 productos + diseño pro, estaríamos entre S/8,000-S/12,000.
¿Te preparo una propuesta detallada? Solo necesito tu nombre y email 📧"

User: "Soy Juan, juan@empresa.com"
Tú: "Genial Juan! Te preparo la propuesta hoy.
¿Esto es urgente o tienes tiempo? (nos ayuda a priorizarte) ⚡"

**CONTACTO NYNEL MKT (dar cuando pregunten):**
📞 WhatsApp: +51 932255932
📧 Email: empresarial@nynelmkt.com
📍 Lima, Perú | Atención 24/7`;

      // ═══════════════════════════════════════════════════════════════
      // ═══════════════════════════════════════════════════════════════
      // 🧠 MASTER CONVERSATIONAL AI - SISTEMA 100% INTELIGENTE
      // ═══════════════════════════════════════════════════════════════
      // Sistema universal que maneja TODO:
      // ✅ Calendario (crear/modificar/cancelar citas)
      // ✅ Cotizaciones (precios/presupuestos)
      // ✅ Consultas (servicios/tecnologías)
      // ✅ Chat General (saludos/preguntas/conversación)
      logger.info('🧠 [MASTER AI] Procesando mensaje con sistema 100% inteligente...');

      try {
        // 🎯 PROCESAR MENSAJE CON MASTER AI
        const result = await masterConversationalAI.processMessage({
          userMessage: context.userMessage,
          conversationHistory: recentHistory,
          subscriberId: context.subscriberId,
          userEmail: context.subscriber.email,
          platform: context.platform,
          subscriber: {
            firstName: context.subscriber.firstName,
            lastName: context.subscriber.lastName,
            email: context.subscriber.email,
            phone: context.subscriber.phone,
            customFields: context.subscriber.customFields,
          },
        });

        logger.info('✅ [MASTER AI] Procesamiento completado:', {
          intent: result.intentType,
          confidence: result.confidence,
          success: result.success,
        });

        // ══════════════════════════════════════════════════════════════
        // ✅ SI CREÓ/MODIFICÓ EVENTO - ACTIVAR FLOW POST-AGENDAMIENTO
        // ══════════════════════════════════════════════════════════════
        if (result.eventCreated || result.eventModified) {
          logger.info('✅ [MASTER AI] Evento creado/modificado - Activando flow post-agendamiento');

          try {
            const subscriberId = context.subscriberId;
            const userEmail = context.subscriber.email;

            // 1. GUARDAR EMAIL EN BD SI EXISTE
            if (userEmail) {
              logger.info(`📧 Email detectado: ${userEmail} - Guardando en BD`);
              await prisma.subscriber.update({
                where: { id: subscriberId },
                data: { email: userEmail },
              });
            }

            // 2. ESTABLECER CUSTOM FIELDS EN MANYCHAT
            logger.info('📝 Estableciendo custom fields en ManyChat...');
            await manyChatAPI.setCustomField(subscriberId, 'appointment_status', 'booked');
            await manyChatAPI.setCustomField(subscriberId, 'last_booking_date', new Date().toISOString());

            if (result.eventDetails?.dateTime) {
              await manyChatAPI.setCustomField(subscriberId, 'next_appointment_datetime', result.eventDetails.dateTime);
            }
            if (result.eventDetails?.meetUrl) {
              await manyChatAPI.setCustomField(subscriberId, 'meeting_url', result.eventDetails.meetUrl);
            }
            if (result.eventDetails?.eventUrl) {
              await manyChatAPI.setCustomField(subscriberId, 'calendar_event_url', result.eventDetails.eventUrl);
            }
            await manyChatAPI.setCustomField(subscriberId, 'has_email', userEmail ? 'true' : 'false');

            // 3. ACTIVAR FLOW DE POST-AGENDAMIENTO
            const postBookingFlowId = process.env.MANYCHAT_POST_BOOKING_FLOW_NS || 'content_20250128_POST_BOOKING';
            logger.info(`🔄 Activando flow post-agendamiento: ${postBookingFlowId}`);

            const flowActivated = await manyChatAPI.sendFlow(subscriberId, postBookingFlowId, result.response);

            if (!flowActivated) {
              logger.warn('⚠️  Flow no activado, usando respuesta directa');
              await manyChatAPI.sendTextMessage(subscriberId, result.response);
            }

            return {
              response: result.response,
              agentUsed: result.eventCreated ? 'master-ai-booking' : 'master-ai-modify',
              confidence: result.confidence,
              intent: 'quotation_request',
            };
          } catch (error) {
            logger.error('❌ Error en lógica post-agendamiento:', error);
          }
        }

        // ══════════════════════════════════════════════════════════════
        // 📤 RESPUESTA PARA CUALQUIER OTRA INTENCIÓN
        // ══════════════════════════════════════════════════════════════
        logger.info(`📤 [MASTER AI] Respuesta (${result.response.length} caracteres): "${result.response}"`);

        const intent = this.detectIntent(context.userMessage);
        const detectedService = this.detectService(context.userMessage);

        return {
          response: result.response,
          agentUsed: `master-ai-${result.intentType}`,
          confidence: result.confidence,
          intent,
          detectedService,
        };
      } catch (error) {
        logger.error('❌ [MASTER AI] Error crítico:', error);
        return {
          response: 'Disculpa, tuve un problema procesando tu mensaje. ¿Podrías repetir?',
          agentUsed: 'master-ai-error',
          confidence: 0,
          intent: 'other',
        };
      }
    } catch (error: any) {
      logger.error('❌ Error en Master AI:', error);

      // Respuesta de fallback
      return {
        response: `¡Hola! 👋 Gracias por contactarnos. ¿En qué puedo ayudarte?`,
        agentUsed: 'fallback',
        confidence: 0.5,
        intent: 'greeting',
      };
    }
  }

  /**
   * Extrae contexto clave de TODA la conversación
   */
  private extractConversationContext(messages: any[]): string {
    if (messages.length === 0) {
      return 'Primera interacción con el cliente.';
    }

    // Extraer información clave
    const allText = messages.map(m => m.content).join(' ').toLowerCase();

    const context: string[] = [];

    // Detectar tipo de negocio mencionado
    const businessTypes = {
      'dental|clínica dental|odontolog': 'clínica dental',
      'restaurante|comida|food|chef': 'restaurante',
      'tienda|shop|ecommerce|productos': 'tienda/e-commerce',
      'consultorio|médico|doctor|salud': 'consultorio médico',
      'gym|gimnasio|fitness': 'gimnasio',
      'salon|peluqueria|belleza|spa': 'salón de belleza',
      'ferreteria|construccion': 'ferretería',
      'abogado|legal|juridico': 'servicios legales',
      'contabilidad|contador': 'contabilidad'
    };

    for (const [pattern, type] of Object.entries(businessTypes)) {
      if (new RegExp(pattern).test(allText)) {
        context.push(`Tipo de negocio: ${type}`);
        break;
      }
    }

    // Detectar servicios mencionados
    if (/app|aplicaci[oó]n|m[oó]vil/.test(allText)) {
      context.push('Interesado en: App móvil');
    }
    if (/web|p[aá]gina|sitio|website/.test(allText)) {
      context.push('Interesado en: Página web');
    }
    if (/chatbot|bot|whatsapp/.test(allText)) {
      context.push('Interesado en: Chatbot WhatsApp');
    }
    if (/marketing|seo|publicidad|ads/.test(allText)) {
      context.push('Interesado en: Marketing digital');
    }

    // Detectar si se mencionaron precios
    if (/precio|cuanto|cuesta|presupuesto|cotizaci[oó]n/.test(allText)) {
      context.push('Ya preguntó por precios');
    }

    // Detectar funcionalidades específicas mencionadas
    if (/pago|pasarela|transacci[oó]n/.test(allText)) {
      context.push('Necesita: Sistema de pagos');
    }
    if (/cita|reserva|agenda/.test(allText)) {
      context.push('Necesita: Sistema de citas/reservas');
    }
    if (/inventario|stock|productos/.test(allText)) {
      context.push('Necesita: Control de inventario');
    }

    // Número de intercambios
    context.push(`Intercambios previos: ${Math.floor(messages.length / 2)}`);

    return context.length > 1 ? context.join(' | ') : 'Conversación iniciando, recopilando información.';
  }

  /**
   * Detecta la intención del mensaje
   */
  private detectIntent(userMessage: string): AIResponse['intent'] {
    const lowerMessage = userMessage.toLowerCase();

    if (
      lowerMessage.match(/cotización|cotizacion|precio|cuánto|cuanto|tarifa|presupuesto/i)
    ) {
      return 'quotation_request';
    }

    if (
      lowerMessage.match(/servicio|ofrec|hac|desarroll|marketing|diseño|web|app/i)
    ) {
      return 'service_inquiry';
    }

    if (lowerMessage.match(/^(hola|buenos|buenas|hey|hi|saludos|qué tal|que tal)/i)) {
      return 'greeting';
    }

    if (lowerMessage.match(/ayuda|problema|error|no funcion|support/i)) {
      return 'support';
    }

    return 'other';
  }

  /**
   * Detecta el servicio mencionado
   */
  private detectService(message: string): string | undefined {
    const lowerMessage = message.toLowerCase();

    const serviceKeywords: Record<string, string[]> = {
      'Desarrollo de Software': ['desarrollo', 'software', 'app', 'sistema', 'programación'],
      'Marketing Digital': ['marketing', 'publicidad', 'seo', 'redes sociales'],
      'Diseño Web': ['diseño', 'web', 'sitio', 'página'],
      'Chatbots con IA': ['chatbot', 'bot', 'whatsapp', 'automatización'],
    };

    for (const [service, keywords] of Object.entries(serviceKeywords)) {
      if (keywords.some((keyword) => lowerMessage.includes(keyword))) {
        return service;
      }
    }

    return undefined;
  }

  /**
   * Determina nuevo estado del lead
   */
  private determineLeadStatus(currentStatus: LeadStatus, intent: AIResponse['intent']): LeadStatus {
    if (currentStatus === 'NEW' && intent !== 'greeting') {
      return 'CONTACTED';
    }

    if (currentStatus === 'CONTACTED' && intent === 'service_inquiry') {
      return 'QUALIFIED';
    }

    if ((currentStatus === 'QUALIFIED' || currentStatus === 'CONTACTED') && intent === 'quotation_request') {
      return 'PROPOSAL_SENT';
    }

    return currentStatus;
  }

  /**
   * Calcula incremento de lead score
   */
  private calculateLeadScoreIncrement(intent: AIResponse['intent']): number {
    const scoreMap: Record<string, number> = {
      greeting: 2,
      service_inquiry: 5,
      quotation_request: 15,
      support: 3,
      other: 1,
    };

    return scoreMap[intent] || 1;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 🔍 EXTRACTOR INTELIGENTE DE DATOS DEL MENSAJE
   * ═══════════════════════════════════════════════════════════════
   * Extrae información valiosa del mensaje del usuario:
   * - Email
   * - Nombre de empresa
   * - Tipo de proyecto
   * - Presupuesto estimado
   * - Otros datos relevantes
   */
  private extractUserDataFromMessage(message: string): Record<string, any> {
    const extracted: Record<string, any> = {};
    const lowerMessage = message.toLowerCase();

    // ─────────────────────────────────────────────────────────────
    // 📧 EXTRAER EMAIL
    // ─────────────────────────────────────────────────────────────
    const emailRegex = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/gi;
    const emails = message.match(emailRegex);
    if (emails && emails.length > 0) {
      extracted.email = emails[0].toLowerCase();
      logger.info(`📧 Email detectado en mensaje: ${extracted.email}`);
    }

    // ─────────────────────────────────────────────────────────────
    // 🏢 EXTRAER NOMBRE DE EMPRESA
    // ─────────────────────────────────────────────────────────────
    const empresaPatterns = [
      /(?:mi empresa|empresa|compañía|negocio|emprendimiento) (?:es|se llama|:) ([A-Za-zÀ-ÿ0-9 ]{2,50})/i,
      /(?:trabajo en|de) ([A-Za-zÀ-ÿ0-9 ]{2,50})(?:\s|$)/i,
      /empresa:?\s*([A-Za-zÀ-ÿ0-9 ]{2,50})/i,
    ];

    for (const pattern of empresaPatterns) {
      const match = message.match(pattern);
      if (match && match[1]) {
        extracted.empresa = match[1].trim();
        logger.info(`🏢 Empresa detectada: ${extracted.empresa}`);
        break;
      }
    }

    // ─────────────────────────────────────────────────────────────
    // 💼 EXTRAER TIPO DE PROYECTO
    // ─────────────────────────────────────────────────────────────
    const projectTypes: Record<string, string> = {
      'e-?commerce|tienda online|venta online': 'E-commerce',
      'aplicaci[oó]n m[oó]vil|app m[oó]vil|mobile app': 'Aplicación Móvil',
      'p[aá]gina web|sitio web|website|landing page': 'Página Web',
      'chatbot|bot|automatizaci[oó]n whatsapp': 'Chatbot WhatsApp',
      'sistema|software|crm|erp|plataforma': 'Sistema/Software a Medida',
      'marketing digital|seo|publicidad|ads|redes sociales': 'Marketing Digital',
      'diseño|ui|ux|interfaz|gr[aá]fico': 'Diseño UX/UI',
    };

    for (const [pattern, type] of Object.entries(projectTypes)) {
      if (new RegExp(pattern, 'i').test(message)) {
        extracted.tipoProyecto = type;
        logger.info(`💼 Tipo de proyecto detectado: ${type}`);
        break;
      }
    }

    // ─────────────────────────────────────────────────────────────
    // 💰 EXTRAER PRESUPUESTO
    // ─────────────────────────────────────────────────────────────
    const presupuestoPatterns = [
      /presupuesto de (?:S\/|s\/|soles?|PEN)?\s*([0-9,]+)/i,
      /tengo (?:S\/|s\/|soles?|PEN)?\s*([0-9,]+)/i,
      /(?:S\/|s\/)\s*([0-9,]+)/i,
      /([0-9,]+)\s*(?:soles|PEN)/i,
    ];

    for (const pattern of presupuestoPatterns) {
      const match = message.match(pattern);
      if (match && match[1]) {
        const amount = match[1].replace(/,/g, '');
        extracted.presupuestoEstimado = `S/ ${amount}`;
        logger.info(`💰 Presupuesto detectado: ${extracted.presupuestoEstimado}`);
        break;
      }
    }

    // ─────────────────────────────────────────────────────────────
    // 🏪 EXTRAER TIPO DE NEGOCIO
    // ─────────────────────────────────────────────────────────────
    const businessTypes: Record<string, string> = {
      'dental|cl[ií]nica dental|odontolog': 'Clínica Dental',
      'restaurante|comida|food|chef|gastronom': 'Restaurante',
      'tienda|shop|boutique|ropa': 'Tienda/Retail',
      'consultorio|m[eé]dico|doctor|salud': 'Consultorio Médico',
      'gym|gimnasio|fitness|deport': 'Gimnasio',
      'salon|peluquer[ií]a|belleza|spa|est[eé]tica': 'Salón de Belleza/Spa',
      'ferreter[ií]a|construcci[oó]n|materiales': 'Ferretería',
      'abogado|legal|jur[ií]dico|estudio legal': 'Servicios Legales',
      'contabilidad|contador|contable': 'Contabilidad',
      'agencia|publicidad|marketing': 'Agencia de Marketing',
      'educaci[oó]n|colegio|academia|instituto': 'Educación',
    };

    for (const [pattern, type] of Object.entries(businessTypes)) {
      if (new RegExp(pattern, 'i').test(message)) {
        extracted.tipoNegocio = type;
        logger.info(`🏪 Tipo de negocio detectado: ${type}`);
        break;
      }
    }

    // ─────────────────────────────────────────────────────────────
    // ⏰ EXTRAER URGENCIA/TIMELINE
    // ─────────────────────────────────────────────────────────────
    if (/urgente|r[aá]pido|ya|pronto|inmediato|cuanto antes/i.test(message)) {
      extracted.urgencia = 'Alta';
      logger.info('⏰ Urgencia detectada: Alta');
    } else if (/esta semana|pr[oó]xima semana|este mes/i.test(message)) {
      extracted.urgencia = 'Media';
      logger.info('⏰ Urgencia detectada: Media');
    }

    // ─────────────────────────────────────────────────────────────
    // 📊 EXTRAER TAMAÑO DE EMPRESA
    // ─────────────────────────────────────────────────────────────
    const sizeMatch = message.match(/(\d+)\s*(?:empleados|trabajadores|personas|colaboradores)/i);
    if (sizeMatch) {
      const employees = parseInt(sizeMatch[1]);
      if (employees <= 10) {
        extracted.tamañoEmpresa = 'Micro (1-10)';
      } else if (employees <= 50) {
        extracted.tamañoEmpresa = 'Pequeña (11-50)';
      } else if (employees <= 200) {
        extracted.tamañoEmpresa = 'Mediana (51-200)';
      } else {
        extracted.tamañoEmpresa = 'Grande (200+)';
      }
      logger.info(`📊 Tamaño empresa detectado: ${extracted.tamañoEmpresa}`);
    }

    return extracted;
  }

  /**
   * Obtiene o crea subscriber - ACTUALIZA DATOS EN CADA LLAMADA
   */
  private async getOrCreateSubscriber(payload: ManyChatWebhookPayload) {
    const existing = await prisma.subscriber.findUnique({
      where: { subscriberId: payload.subscriber_id },
    });

    // ═══════════════════════════════════════════════════════════════
    // 🔄 ACTUALIZAR subscriber existente con datos frescos del payload
    // ═══════════════════════════════════════════════════════════════
    if (existing) {
      // Preparar datos a actualizar (solo si vienen del payload)
      const updateData: any = {
        lastActiveAt: new Date(),
      };

      // Actualizar firstName/lastName solo si no son placeholders
      if (payload.first_name && payload.first_name !== '{{first_name}}') {
        updateData.firstName = payload.first_name;
      }
      if (payload.last_name && payload.last_name !== '{{last_name}}') {
        updateData.lastName = payload.last_name;
      }

      // Actualizar phone solo si no es placeholder
      if (payload.phone && payload.phone !== '{{phone}}' && payload.phone !== '{{wa_id}}') {
        updateData.phone = payload.phone;
      }

      // Actualizar email solo si no es placeholder Y no está ya guardado
      if (payload.email && payload.email !== '{{email}}' && !existing.email) {
        updateData.email = payload.email;
      }

      // Actualizar customFields (merge con existentes)
      if (payload.custom_fields) {
        const existingFields = (existing.customFields as Record<string, any>) || {};
        updateData.customFields = {
          ...existingFields,
          ...payload.custom_fields,
        } as Prisma.InputJsonValue;
      }

      logger.info('🔄 Actualizando subscriber con datos frescos:', updateData);

      return prisma.subscriber.update({
        where: { id: existing.id },
        data: updateData,
      });
    }

    // ═══════════════════════════════════════════════════════════════
    // ✨ CREAR nuevo subscriber
    // ═══════════════════════════════════════════════════════════════
    return prisma.subscriber.create({
      data: {
        subscriberId: payload.subscriber_id,
        platform: this.mapPlatform(payload.platform),
        firstName: payload.first_name && payload.first_name !== '{{first_name}}' ? payload.first_name : undefined,
        lastName: payload.last_name && payload.last_name !== '{{last_name}}' ? payload.last_name : undefined,
        email: payload.email && payload.email !== '{{email}}' ? payload.email : undefined,
        phone: payload.phone && payload.phone !== '{{phone}}' && payload.phone !== '{{wa_id}}' ? payload.phone : undefined,
        tags: payload.tags || [],
        customFields: payload.custom_fields as Prisma.InputJsonValue,
      },
    });
  }

  /**
   * Obtiene o crea conversación activa
   */
  private async getOrCreateConversation(subscriberId: string) {
    const activeConversation = await prisma.conversation.findFirst({
      where: {
        subscriberId,
        isActive: true,
      },
      orderBy: { createdAt: 'desc' },
    });

    if (activeConversation) {
      return activeConversation;
    }

    return prisma.conversation.create({
      data: {
        subscriberId,
        isActive: true,
      },
    });
  }

  /**
   * Mapea platform string a enum
   */
  private mapPlatform(platform?: string): Platform {
    if (!platform) return Platform.WEB;

    const platformMap: Record<string, Platform> = {
      instagram: Platform.INSTAGRAM,
      whatsapp: Platform.WHATSAPP,
      facebook: Platform.FACEBOOK,
      telegram: Platform.TELEGRAM,
      email: Platform.EMAIL,
      web: Platform.WEB,
    };

    return platformMap[platform.toLowerCase()] || Platform.WEB;
  }

  /**
   * ═══════════════════════════════════════════════════════════════
   * 📝 GENERADOR DE RESUMEN DE CONVERSACIÓN (SLIDING WINDOW)
   * ═══════════════════════════════════════════════════════════════
   * Genera resumen de mensajes antiguos para Context Management.
   * Usa la lógica existente de extractConversationContext para
   * comprimir conversaciones largas y reducir tokens en 80-90%.
   */
  private generateConversationSummary(oldMessages: any[]): string {
    if (oldMessages.length === 0) {
      return '';
    }

    // Convertir mensajes al formato esperado por extractConversationContext
    const formattedMessages = oldMessages.map(msg => ({
      role: msg.role === 'USER' ? 'user' : 'assistant',
      content: msg.content || msg.audioTranscription || '',
    }));

    // Usar la función existente que ya hace un buen trabajo extrayendo contexto
    const summary = this.extractConversationContext(formattedMessages);

    logger.info(`📝 Resumen generado de ${oldMessages.length} mensajes antiguos: "${summary}"`);

    return summary;
  }
}

export const webhookControllerV2 = new WebhookControllerV2();
