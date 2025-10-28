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
import { prisma } from '../config/database';
import { Platform, MessageType, MessageRole, LeadStatus, Prisma } from '@prisma/client';
import { Decimal } from '@prisma/client/runtime/library';
import { logger } from '../utils/logger';
import { manyChatAPI } from '../services/manychat-api.service';
import calendarIntentService from '../services/calendar-intent.service';
// Lazy import para evitar bloquear el startup
// import { LangChainRAGService } from '../services/langchain-rag.service';

// Estructura para mensajes pendientes (SIN responses, porque respondemos inmediatamente)
interface PendingMessage {
  subscriberId: string;
  messages: string[];
  payload: ManyChatWebhookPayload;
  timeout: NodeJS.Timeout;
}

// Map para almacenar mensajes pendientes por subscriber
const pendingMessages = new Map<string, PendingMessage>();

interface ManyChatWebhookPayload {
  subscriber_id: string;
  platform: string;
  first_name?: string;
  last_name?: string;
  email?: string;
  phone?: string;
  user_message?: string;
  audio_url?: string;
  image_url?: string;
  tags?: string[];
  custom_fields?: Record<string, any>;
}

interface AIResponse {
  response: string;
  agentUsed: string;
  confidence: number;
  intent: 'greeting' | 'service_inquiry' | 'quotation_request' | 'support' | 'other';
  detectedService?: string;
}

class WebhookControllerV2 {
  /**
   * Handler principal del webhook de ManyChat
   * Implementa concatenación de mensajes con ventana de 4 segundos
   */
  async handleManyChatMessage(req: Request, res: Response) {
    try {
      const payload: ManyChatWebhookPayload = req.body;

      // 🔍 LOG COMPLETO DEL PAYLOAD
      logger.info('📥 Webhook V2 recibido de ManyChat:');
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

      // Validar payload
      if (!payload.subscriber_id || !payload.user_message) {
        return res.status(200).json({
          version: 'v2',
          content: {
            messages: [{ type: 'text', text: 'Mensaje recibido' }]
          }
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
      // 3. Sistema de concatenación con ventana de tiempo (ASÍNCRONO)
      // ─────────────────────────────────────────────────────────────────
      const subscriberId = payload.subscriber_id;
      const pending = pendingMessages.get(subscriberId);

      if (pending) {
        // Ya hay mensajes pendientes, agregar este y reiniciar timer
        logger.info(`⏱️  Mensaje agregado a cola (total: ${pending.messages.length + 1})`);
        pending.messages.push(payload.user_message);
        clearTimeout(pending.timeout);

        // Crear nuevo timeout con 15 segundos (ahora NO hay límite de ManyChat)
        pending.timeout = setTimeout(() => {
          this.processQueuedMessagesAndSendViaAPI(subscriberId);
        }, 15000); // 15 segundos - ventana generosa para usuario real

        return;
      } else {
        // Primer mensaje, crear nueva entrada en cola
        logger.info('⏱️  Iniciando ventana de concatenación (15 segundos)');
        const timeout = setTimeout(() => {
          this.processQueuedMessagesAndSendViaAPI(subscriberId);
        }, 15000);

        pendingMessages.set(subscriberId, {
          subscriberId,
          messages: [payload.user_message],
          payload,
          timeout,
        });

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
   */
  private async processQueuedMessagesAndSendViaAPI(subscriberId: string) {
    const pending = pendingMessages.get(subscriberId);
    if (!pending) return;

    const startTime = Date.now();
    logger.info(`🚀 Procesando ${pending.messages.length} mensaje(s) concatenado(s)`);

    try {
      const subscriber = await this.getOrCreateSubscriber(pending.payload);
      const conversation = await this.getOrCreateConversation(subscriber.id);

      // ─────────────────────────────────────────────────────────────────
      // 1. Concatenar todos los mensajes
      // ─────────────────────────────────────────────────────────────────
      const concatenatedMessage = pending.messages.join('\n');
      logger.info(`📝 Mensaje concatenado: "${concatenatedMessage}"`);

      // ─────────────────────────────────────────────────────────────────
      // 2. Obtener historial COMPLETO de conversación (últimos 200 mensajes)
      // ─────────────────────────────────────────────────────────────────
      const messageHistory = await prisma.message.findMany({
        where: { conversationId: conversation.id },
        orderBy: { createdAt: 'asc' },
        take: 200, // ✅ Aumentado a 200 para memoria conversacional extendida
      });

      // ─────────────────────────────────────────────────────────────────
      // 3. Procesar con AI (Groq)
      // ─────────────────────────────────────────────────────────────────
      logger.info('🤖 Procesando con Groq AI...');

      const aiResponse = await this.processWithGroqAI({
        subscriberId: subscriber.id,
        conversationId: conversation.id,
        userMessage: concatenatedMessage,
        messageHistory,
        subscriber: {
          firstName: subscriber.firstName || undefined,
          lastName: subscriber.lastName || undefined,
          leadStatus: subscriber.leadStatus,
        },
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

      // Limpiar de la cola DESPUÉS de enviar
      pendingMessages.delete(subscriberId);
    } catch (error: any) {
      logger.error('❌ Error procesando mensajes en cola:', error);

      // Enviar mensaje de error via ManyChat Send API
      await manyChatAPI.sendTextMessage(
        subscriberId,
        'Disculpa, tuve un problema técnico. ¿Podrías repetir tu mensaje?'
      );

      // Limpiar de la cola también en caso de error
      pendingMessages.delete(subscriberId);
    }
  }

  /**
   * Procesa el mensaje con Groq AI
   */
  private async processWithGroqAI(context: {
    subscriberId: string;
    conversationId: string;
    userMessage: string;
    messageHistory: any[];
    subscriber: {
      firstName?: string;
      lastName?: string;
      leadStatus: LeadStatus;
    };
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

      // 3. Últimos 100 mensajes para contexto COMPLETO (50 intercambios = horas/días de conversación)
      // ✅ Esto permite recordar conversaciones largas que duran minutos, horas o incluso días
      const recentHistory = allMessages.slice(-100);

      // 📊 LOGGING DETALLADO PARA DEBUG
      logger.info(`📊 CONTEXTO DE MEMORIA:`);
      logger.info(`  - Total de mensajes en historial: ${allMessages.length}`);
      logger.info(`  - Mensajes enviados a GPT-4o: ${recentHistory.length}`);
      logger.info(`  - Resumen extraído: "${conversationSummary}"`);
      logger.info(`  - Últimos 5 mensajes del historial:`);
      recentHistory.slice(-5).forEach((msg, idx) => {
        logger.info(`    ${idx + 1}. [${msg.role}]: ${msg.content.substring(0, 100)}...`);
      });

      // Sistema prompt OPTIMIZADO - Inteligente y contextual
      const systemPrompt = `Eres Nynel AI de NYNEL MKT (marketing digital y desarrollo en Perú). Cliente: ${context.subscriber.firstName || 'Cliente'} ${context.subscriber.lastName || ''}.

**CONTEXTO ACTUAL:** ${conversationSummary}

**SERVICIOS Y PRECIOS:**
• Chatbot IA WhatsApp 24/7 - PROMO: S/350 instalación + S/79.90/mes
• Desarrollo Web/Apps/Software - Cotización personalizada
• Marketing Digital/SEO/Ads - Cotización personalizada
• Diseño Web/UX/UI - Cotización personalizada

**REGLAS DE CONVERSACIÓN:**
1. USA el contexto arriba - NUNCA olvides información previa
2. Responde en 2-3 líneas MAX (estilo WhatsApp conversacional)
3. Si ya preguntaste algo, NO vuelvas a preguntar
4. Cada respuesta debe ser ÚNICA según el contexto - NO uses respuestas genéricas
5. Solo Chatbot IA tiene precio fijo - otros servicios: "cotización personalizada según tus necesidades"
6. Usa 1-2 emojis ocasionalmente (no en cada mensaje)

**AGENDAMIENTO DE CITAS:**
• El sistema consulta disponibilidad REAL del Google Calendar
• Si el cliente pide agendar, el sistema automáticamente muestra horarios disponibles
• NO inventes horarios - el sistema ya maneja esto automáticamente
• Prioriza agendar mismo día o día siguiente si hay disponibilidad

**CIERRE DE VENTAS (APLICA DE FORMA CONTEXTUAL):**
• Genera urgencia natural: "Solo quedan 3 cupos este mes" / "Promoción válida esta semana"
• Muestra valor específico: "Esto aumentaría tus ventas en 40%" / "Ahorrarías 20 horas/semana"
• Llama a acción directa: "¿Agendamos una demo de 15 min?" / "¿Arrancamos esta semana?"
• Pregunta CUÁNDO (no SI): Ofrece opciones concretas según contexto
• Ofrece alternativas (A o B): "¿Paquete básico o completo?" vs preguntas abiertas
• Cierre asumido: "Te preparo la propuesta, ¿tu email es...?"

**EJEMPLOS DE RESPUESTAS CONTEXTUALES:**
User: "Cuánto cuesta web"
Tú: "Depende del tipo de web. ¿Qué necesitas: catálogo, e-commerce, o algo personalizado? 🌐"

User: "E-commerce para ropa"
Tú: "Perfecto! E-commerce con pasarela de pagos, gestión de stock y envíos. Inversión desde S/8,500. ¿Agendamos una demo? 👔"`;

      // ═══════════════════════════════════════════════════════════════
      // 📅 PRIORIDAD 1: GESTIÓN COMPLETA DE CALENDARIO
      // ═══════════════════════════════════════════════════════════════
      // Soporta: CREATE, MODIFY, CANCEL, LIST acciones de calendario
      logger.info('📅 Verificando intenciones de gestión de calendario...');

      // 🔍 PASO 1: Detectar tipo de acción (CREATE/MODIFY/CANCEL/LIST/NONE)
      const calendarAction = await calendarIntentService.detectCalendarAction(
        context.userMessage,
        recentHistory
      );

      logger.info(`📅 Acción detectada: ${calendarAction.actionType}`);

      // 🔀 ENRUTAMIENTO POR TIPO DE ACCIÓN
      if (calendarAction.actionType === 'LIST') {
        // ══════════════════════════════════════════════════════════════
        // 📋 LIST: Mostrar agenda del usuario
        // ══════════════════════════════════════════════════════════════
        logger.info('📋 Usuario quiere ver su agenda');

        const timeRange = calendarAction.searchCriteria?.timeRange || 'semana';
        const agendaText = await calendarIntentService.listUserAgenda(
          timeRange as 'hoy' | 'mañana' | 'semana' | 'mes'
        );

        return {
          response: agendaText,
          agentUsed: 'calendar-list',
          confidence: 0.95,
          intent: 'quotation_request',
        };
      }

      if (calendarAction.actionType === 'MODIFY' || calendarAction.actionType === 'CANCEL') {
        // ══════════════════════════════════════════════════════════════
        // ✏️ MODIFY / 🗑️ CANCEL: Buscar eventos existentes
        // ══════════════════════════════════════════════════════════════
        logger.info(`${calendarAction.actionType === 'MODIFY' ? '✏️' : '🗑️'} Buscando eventos para ${calendarAction.actionType.toLowerCase()}...`);

        const foundEvents = await calendarIntentService.searchExistingEvents(
          calendarAction.searchCriteria!
        );

        if (foundEvents.length === 0) {
          return {
            response: 'No encontré esa cita. ¿Me das más detalles? Por ejemplo: "la de mañana" o "la del viernes"',
            agentUsed: 'calendar-search',
            confidence: 0.9,
            intent: 'other',
          };
        }

        if (foundEvents.length === 1) {
          // ══════════════════════════════════════════════════════════════
          // ✅ Solo 1 evento encontrado - Procesar directamente
          // ══════════════════════════════════════════════════════════════
          const event = foundEvents[0];

          if (calendarAction.actionType === 'CANCEL') {
            // 🗑️ CANCELAR EVENTO
            const cancelResult = await calendarIntentService.cancelCalendarEvent(event.id);

            const eventDate = new Date(event.start);
            const formattedDate = eventDate.toLocaleDateString('es-PE', {
              weekday: 'long',
              day: 'numeric',
              month: 'long',
              hour: '2-digit',
              minute: '2-digit',
            });

            return {
              response: `Listo, cancelé tu cita:\n\n*${event.summary}*\n📅 ${formattedDate}`,
              agentUsed: 'calendar-cancel',
              confidence: 0.98,
              intent: 'quotation_request',
            };
          } else {
            // ✏️ MODIFICAR EVENTO
            const modifyResult = await calendarIntentService.modifyCalendarEvent(
              event.id,
              calendarAction.modifications!,
              context.subscriber.email
            );

            return {
              response: `Listo, cambié tu cita:\n\n*${modifyResult.summary}*\n📅 ${new Date(modifyResult.start).toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' })}${modifyResult.meetUrl ? `\n\n📹 Link: ${modifyResult.meetUrl}` : ''}`,
              agentUsed: 'calendar-modify',
              confidence: 0.98,
              intent: 'quotation_request',
            };
          }
        }

        if (foundEvents.length > 1) {
          // ══════════════════════════════════════════════════════════════
          // ⚠️ Múltiples eventos encontrados - Pedir selección al usuario
          // ══════════════════════════════════════════════════════════════
          let selectionMessage = `Encontré ${foundEvents.length} citas que coinciden. ¿Cuál quieres ${calendarAction.actionType === 'MODIFY' ? 'modificar' : 'cancelar'}?\n\n`;

          foundEvents.forEach((event, index) => {
            const eventDate = new Date(event.start);
            const formattedDate = eventDate.toLocaleDateString('es-PE', {
              weekday: 'short',
              day: 'numeric',
              month: 'short',
              hour: '2-digit',
              minute: '2-digit',
            });

            selectionMessage += `${index + 1}. *${event.summary}*\n   📅 ${formattedDate}\n\n`;
          });

          selectionMessage += `Responde con el número (1-${foundEvents.length})`;

          // TODO: Guardar estado en Custom Fields para el siguiente mensaje
          // await manyChatAPI.setCustomField(subscriberId, 'pending_calendar_action', calendarAction.actionType);
          // await manyChatAPI.setCustomField(subscriberId, 'found_events', JSON.stringify(foundEvents));

          return {
            response: selectionMessage,
            agentUsed: 'calendar-search',
            confidence: 0.95,
            intent: 'other',
          };
        }
      }

      if (calendarAction.actionType === 'CREATE' || calendarAction.actionType === 'NONE') {
        // ══════════════════════════════════════════════════════════════
        // 🆕 CREATE: Usar el flujo original de creación
        // ══════════════════════════════════════════════════════════════
        const calendarResult = await calendarIntentService.processCalendarIntent(
          context.userMessage,
          recentHistory,
          context.subscriber.email
        );

        if (calendarResult.hasIntent && calendarResult.eventCreated) {
          // ✅ Cita agendada exitosamente
          logger.info('✅ Cita agendada exitosamente en Google Calendar');
          return {
            response: calendarResult.suggestedResponse!,
            agentUsed: 'calendar-booking',
            confidence: 0.98,
            intent: 'quotation_request',
          };
        } else if (calendarResult.hasIntent && !calendarResult.eventCreated) {
          // ⚠️ Detectó intención pero falta información (fecha/hora)
          logger.info('⚠️ Intención de calendario detectada pero faltan datos');
          // El RAG manejará la solicitud de información adicional
        }
        // Si no hay intención de calendario, continuar con RAG normal
      }

      // ═══════════════════════════════════════════════════════════════
      // 🧠 SISTEMA RAG + GPT-4o (PROFESIONAL CON VECTOR STORE)
      // ═══════════════════════════════════════════════════════════════
      logger.info('🧠 Usando RAG + GPT-4o (OpenAI)');

      // Import dinámico para evitar bloquear el startup
      const { LangChainRAGService } = await import('../services/langchain-rag.service');

      // Inicializar RAG si aún no está inicializado (lazy initialization)
      await LangChainRAGService.initialize();

      // Preparar mensajes para RAG (incluye system prompt + historial reciente)
      const ragMessages = [
        {
          role: 'system' as const,
          content: systemPrompt,
        },
        ...recentHistory.map(msg => ({
          role: msg.role as 'user' | 'assistant',
          content: msg.content
        })),
        {
          role: 'user' as const,
          content: context.userMessage,
        },
      ];

      // Procesar con RAG + GPT-4o
      const aiResponseText = await LangChainRAGService.processMessage(
        ragMessages,
        {
          temperature: 0.7,     // Coherente pero conversacional
          maxTokens: 500,       // Respuestas completas pero concisas
        }
      );

      // 📤 LOGGING DETALLADO DE LA RESPUESTA
      logger.info(`📤 RESPUESTA COMPLETA DE GPT-4o (${aiResponseText.length} caracteres):`);
      logger.info(`   "${aiResponseText}"`);
      logger.info(`   ✅ Esta respuesta se enviará a ManyChat Custom Field`);

      const intent = this.detectIntent(context.userMessage);
      const detectedService = this.detectService(context.userMessage);

      return {
        response: aiResponseText,
        agentUsed: 'rag-gpt-4o',  // Indicador del nuevo sistema
        confidence: 0.95,         // Mayor confianza con GPT-4o
        intent,
        detectedService,
      };
    } catch (error: any) {
      logger.error('❌ Error en RAG + GPT-4o:', error);

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
   * Obtiene o crea subscriber
   */
  private async getOrCreateSubscriber(payload: ManyChatWebhookPayload) {
    const existing = await prisma.subscriber.findUnique({
      where: { subscriberId: payload.subscriber_id },
    });

    if (existing) {
      return existing;
    }

    return prisma.subscriber.create({
      data: {
        subscriberId: payload.subscriber_id,
        platform: this.mapPlatform(payload.platform),
        firstName: payload.first_name,
        lastName: payload.last_name,
        email: payload.email,
        phone: payload.phone,
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
}

export const webhookControllerV2 = new WebhookControllerV2();
