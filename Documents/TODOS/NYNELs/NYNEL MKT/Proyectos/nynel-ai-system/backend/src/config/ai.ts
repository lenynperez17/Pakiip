// ═══════════════════════════════════════════════════════════════════════════
// 🤖 CONFIGURACIÓN DE IA - SOLO OPENROUTER + DEEPSEEK V3
// ═══════════════════════════════════════════════════════════════════════════

import OpenAI from 'openai';
import { logger } from '#/utils/logger.js';

// ─────────────────────────────────────────────────────────────────────────────
// Validación de API Key
// ─────────────────────────────────────────────────────────────────────────────

if (!process.env.OPENROUTER_API_KEY) {
  throw new Error('❌ OPENROUTER_API_KEY no configurado - Sistema no puede iniciar');
}

logger.info('✅ OpenRouter API Key configurado correctamente');

// ─────────────────────────────────────────────────────────────────────────────
// OPENROUTER - DeepSeek v3 (ÚNICO PROVEEDOR DE IA)
// ─────────────────────────────────────────────────────────────────────────────

export const openai = new OpenAI({
  apiKey: process.env.OPENROUTER_API_KEY,
  baseURL: 'https://openrouter.ai/api/v1',
  defaultHeaders: {
    'HTTP-Referer': 'https://api.nyneldigital.com',
    'X-Title': 'Nynel AI System',
  },
});

logger.info('✅ Cliente OpenAI configurado para usar OpenRouter + DeepSeek v3');

// ─────────────────────────────────────────────────────────────────────────────
// Configuración del Modelo
// ─────────────────────────────────────────────────────────────────────────────

export const AI_CONFIG = {
  // DeepSeek v3 via OpenRouter (ÚNICO MODELO)
  model: 'deepseek/deepseek-chat-v3.1',

  // Configuración de agentes
  agents: {
    coordinator: {
      temperature: 0.5,
      maxTokens: 1024,
    },
    sales: {
      temperature: 0.7,
      maxTokens: 2048,
    },
    technical: {
      temperature: 0.3,
      maxTokens: 1536,
    },
    support: {
      temperature: 0.6,
      maxTokens: 1536,
    },
    content: {
      temperature: 0.8,
      maxTokens: 2048,
    },
  },

  // Límites de procesamiento
  limits: {
    maxTextLength: 8000, // Caracteres
    maxConversationHistory: 10, // Mensajes a incluir en contexto
    maxAudioSizeMB: 25, // Tamaño máximo de audio en MB
  },

  // Timeouts (en ms)
  timeouts: {
    chat: 30000, // 30 segundos
  },

  // Configuración para procesamiento de audio (DeepSeek v3)
  whisper: {
    model: 'deepseek/deepseek-chat-v3.1',
    language: 'es',
    temperature: 0.3,
  },

  // Configuración para procesamiento de imágenes (DeepSeek v3)
  vision: {
    model: 'deepseek/deepseek-chat-v3.1',
    maxTokens: 1500,
    temperature: 0.5,
  },
};

// ─────────────────────────────────────────────────────────────────────────────
// System Prompts Base
// ─────────────────────────────────────────────────────────────────────────────

export const SYSTEM_PROMPTS = {
  coordinator: `Eres el Coordinador Principal de Nynel Mkt, una agencia de desarrollo de software y marketing digital en Perú.

Tu rol es analizar cada consulta del cliente y decidir qué agente especializado debe atenderla:

- **Sales Agent**: Para cotizaciones, precios, paquetes, negociación
- **Technical Agent**: Para consultas técnicas, especificaciones, tecnologías
- **Support Agent**: Para soporte, problemas, dudas post-venta
- **Content Agent**: Para estrategias de marketing, contenido, campañas

Debes responder SOLO con el nombre del agente apropiado: "sales", "technical", "support" o "content"

Si la consulta es ambigua, elige el agente más apropiado basándote en el contexto.`,

  sales: `Eres el Sales Agent de Nynel Mkt, experto en ventas consultivas de servicios digitales.

Servicios principales:
1. Software a Medida (S/ 8,000 - S/ 150,000)
2. SEO y Marketing (S/ 900 - S/ 5,000/mes)
3. Email Marketing (S/ 500 - S/ 3,000/mes)
4. Páginas Web (S/ 1,500 - S/ 25,000)
5. Automatización (S/ 800 - S/ 8,000)
6. Apps Móviles (S/ 15,000 - S/ 120,000)
7. Analítica (S/ 2,000 - S/ 15,000)
8. Campañas Publicitarias (S/ 1,200 - S/ 10,000/mes)

Tu objetivo: Entender las necesidades del cliente, hacer preguntas consultivas, y ofrecer soluciones personalizadas.

Estilo: Profesional pero cercano, consultivo, enfocado en valor.`,

  technical: `Eres el Technical Agent de Nynel Mkt, ingeniero de software senior con expertise en:

- Desarrollo web (React, Vue, Angular, Next.js)
- Backend (Node.js, Python, PHP)
- Apps móviles (Flutter, React Native, iOS, Android)
- Bases de datos (PostgreSQL, MySQL, MongoDB)
- Cloud (AWS, Google Cloud, Azure)
- DevOps y CI/CD

Tu rol: Responder consultas técnicas, explicar arquitecturas, recomendar soluciones tecnológicas.

Estilo: Técnico pero accesible, educativo, preciso.`,

  support: `Eres el Support Agent de Nynel Mkt, especialista en atención al cliente y resolución de problemas.

Tu rol:
- Resolver dudas post-venta
- Ayudar con el uso de servicios
- Escalar problemas técnicos complejos
- Mantener al cliente satisfecho

Estilo: Empático, paciente, resolutivo, proactivo.`,

  content: `Eres el Content Agent de Nynel Mkt, estratega de marketing digital y creación de contenido.

Tu expertise:
- Estrategias de contenido
- SEO y SEM
- Redes sociales
- Email marketing
- Copywriting persuasivo

Tu rol: Crear estrategias de marketing, sugerir campañas, optimizar contenido.

Estilo: Creativo, estratégico, orientado a resultados.`,
};

// ─────────────────────────────────────────────────────────────────────────────
// Utilidades de IA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Trunca texto si excede el límite
 */
export function truncateText(text: string, maxLength: number = AI_CONFIG.limits.maxTextLength): string {
  if (text.length <= maxLength) {
    return text;
  }

  return text.substring(0, maxLength) + '... [truncado]';
}

/**
 * Valida que un archivo no exceda el tamaño máximo permitido
 */
export function validateFileSize(fileSizeBytes: number, maxSizeMB: number): boolean {
  const fileSizeMB = fileSizeBytes / (1024 * 1024);
  return fileSizeMB <= maxSizeMB;
}
