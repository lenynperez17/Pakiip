// ═══════════════════════════════════════════════════════════════════════════
// 🖼️ PROCESADOR DE IMÁGENES - DEEPSEEK V3 VIA OPENROUTER
// ═══════════════════════════════════════════════════════════════════════════

import { openai, AI_CONFIG } from '#/config/ai.js';
import { logger } from '#/utils/logger.js';

export interface ImageAnalysisResult {
  description: string;
  objects: string[];
  text?: string;
  context?: string;
  suggestedServices?: string[];
}

/**
 * Analiza una imagen usando DeepSeek v3 via OpenRouter
 */
export async function processImage(imageUrl: string, userMessage?: string): Promise<ImageAnalysisResult> {
  try {
    logger.info(`🖼️ Analizando imagen: ${imageUrl}`);

    // Construir el prompt según el contexto
    const analysisPrompt = userMessage
      ? `El usuario envió esta imagen con el mensaje: "${userMessage}"\n\nAnaliza la imagen y proporciona:\n1. Descripción detallada\n2. Objetos/elementos identificados\n3. Texto visible (si hay)\n4. Contexto o propósito de la imagen\n5. Servicios de Nynel Mkt que podrían ser relevantes`
      : `Analiza esta imagen y proporciona:\n1. Descripción detallada\n2. Objetos/elementos principales\n3. Texto visible\n4. Posible contexto de negocio`;

    // Llamar a DeepSeek v3 via OpenRouter
    const response = await openai.chat.completions.create({
      model: AI_CONFIG.vision.model,
      messages: [
        {
          role: 'user',
          content: [
            {
              type: 'text',
              text: analysisPrompt,
            },
            {
              type: 'image_url',
              image_url: {
                url: imageUrl,
                detail: 'auto',
              },
            },
          ],
        },
      ],
      max_tokens: AI_CONFIG.vision.maxTokens,
      temperature: AI_CONFIG.vision.temperature,
    });

    const analysis = response.choices[0]?.message?.content || 'No se pudo analizar la imagen';

    logger.info(`✅ Imagen analizada: "${analysis.substring(0, 100)}..."`);

    // Parsear el análisis (simple)
    const result: ImageAnalysisResult = {
      description: analysis,
      objects: extractObjectsFromAnalysis(analysis),
      text: extractTextFromAnalysis(analysis),
      context: extractContextFromAnalysis(analysis),
      suggestedServices: extractServicesFromAnalysis(analysis),
    };

    return result;

  } catch (error: any) {
    logger.error('❌ Error analizando imagen:', error);
    throw new Error(`Error al analizar imagen: ${error.message}`);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Utilidades de parseo
// ─────────────────────────────────────────────────────────────────────────────

function extractObjectsFromAnalysis(analysis: string): string[] {
  // Buscar sección de objetos
  const objectsMatch = analysis.match(/objetos?[:\s]+([^\n]+)/i);
  if (!objectsMatch) return [];

  return objectsMatch[1]
    .split(/[,;]/)
    .map(obj => obj.trim())
    .filter(Boolean);
}

function extractTextFromAnalysis(analysis: string): string | undefined {
  const textMatch = analysis.match(/texto[:\s]+["']?([^"'\n]+)["']?/i);
  return textMatch?.[1]?.trim();
}

function extractContextFromAnalysis(analysis: string): string | undefined {
  const contextMatch = analysis.match(/contexto[:\s]+([^\n]+)/i);
  return contextMatch?.[1]?.trim();
}

function extractServicesFromAnalysis(analysis: string): string[] {
  const servicesKeywords = [
    'web',
    'app',
    'móvil',
    'software',
    'marketing',
    'seo',
    'diseño',
    'automatización',
    'analítica',
  ];

  return servicesKeywords.filter(keyword =>
    analysis.toLowerCase().includes(keyword)
  );
}
