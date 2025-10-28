// ═══════════════════════════════════════════════════════════════════════════
// 🤖 CONFIGURACIÓN DE MODELOS DE IA
// ═══════════════════════════════════════════════════════════════════════════

// TEMPORALMENTE COMENTADO - El import de LangChain bloquea el startup
// import { ChatGroq } from '@langchain/groq';
import OpenAI from 'openai';
import { logger } from '@/utils/logger';

// ─────────────────────────────────────────────────────────────────────────────
// Validación de API Keys
// ─────────────────────────────────────────────────────────────────────────────

if (!process.env.GROQ_API_KEY) {
  logger.warn('⚠️  GROQ_API_KEY no configurado - Groq/LLaMA deshabilitado (sistema usa GPT-4o por defecto)');
}

if (!process.env.OPENAI_API_KEY) {
  logger.error('❌ OPENAI_API_KEY no está configurado en .env');
  throw new Error('OPENAI_API_KEY es requerido para GPT-4o, Whisper y Vision');
}

// ─────────────────────────────────────────────────────────────────────────────
// GROQ - LLaMA 3.3 70B (para conversación principal) - LAZY LOADING
// ─────────────────────────────────────────────────────────────────────────────

// TEMPORALMENTE DESHABILITADO - Groq se cargará bajo demanda
// export const groqModel = new ChatGroq({
//   apiKey: process.env.GROQ_API_KEY,
//   model: process.env.GROQ_MODEL || 'llama-3.3-70b-versatile',
//   temperature: 0.7,
//   maxTokens: 2048,
//   streaming: false,
// });

logger.info(`✅ Groq configurado (lazy loading): ${process.env.GROQ_MODEL || 'llama-3.3-70b-versatile'}`);

// ─────────────────────────────────────────────────────────────────────────────
// OPENAI - Whisper (audio) + GPT-4 Vision (imágenes)
// ─────────────────────────────────────────────────────────────────────────────

export const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

// ─────────────────────────────────────────────────────────────────────────────
// Configuración de Modelos
// ─────────────────────────────────────────────────────────────────────────────

export const AI_CONFIG = {
  // Groq LLaMA
  groq: {
    model: process.env.GROQ_MODEL || 'llama-3.3-70b-versatile',
    temperature: 0.7,
    maxTokens: 2048,
    topP: 1,
  },

  // OpenAI Whisper (transcripción de audio)
  whisper: {
    model: process.env.OPENAI_WHISPER_MODEL || 'whisper-1',
    language: 'es', // Español por defecto
    temperature: 0,
  },

  // OpenAI GPT-4 Vision (análisis de imágenes)
  vision: {
    model: process.env.OPENAI_VISION_MODEL || 'gpt-4-vision-preview',
    maxTokens: 1024,
    temperature: 0.3,
  },

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
    maxAudioSizeMB: 25, // Whisper soporta hasta 25MB
    maxImageSizeMB: 20,
    maxTextLength: 8000, // Caracteres
    maxConversationHistory: 10, // Mensajes a incluir en contexto
  },

  // Timeouts (en ms)
  timeouts: {
    groq: 30000, // 30 segundos
    whisper: 60000, // 60 segundos
    vision: 45000, // 45 segundos
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
 * Crea un modelo Groq con configuración personalizada
 * TEMPORALMENTE DESHABILITADO - lazy loading
 */
// export function createGroqModel(config?: {
//   temperature?: number;
//   maxTokens?: number;
//   model?: string;
// }): ChatGroq {
//   return new ChatGroq({
//     apiKey: process.env.GROQ_API_KEY,
//     model: config?.model || AI_CONFIG.groq.model,
//     temperature: config?.temperature ?? AI_CONFIG.groq.temperature,
//     maxTokens: config?.maxTokens ?? AI_CONFIG.groq.maxTokens,
//   });
// }

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
 * Valida el tamaño de un archivo
 */
export function validateFileSize(sizeInBytes: number, type: 'audio' | 'image'): boolean {
  const sizeMB = sizeInBytes / (1024 * 1024);
  const maxSize = type === 'audio' ? AI_CONFIG.limits.maxAudioSizeMB : AI_CONFIG.limits.maxImageSizeMB;

  return sizeMB <= maxSize;
}
