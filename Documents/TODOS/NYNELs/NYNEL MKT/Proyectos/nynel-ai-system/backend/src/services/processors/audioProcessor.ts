// ═══════════════════════════════════════════════════════════════════════════
// 🎙️ PROCESADOR DE AUDIO - WHISPER API
// ═══════════════════════════════════════════════════════════════════════════

import { openai, AI_CONFIG, validateFileSize } from '#/config/ai.js';
import { logger } from '#/utils/logger.js';
import axios from 'axios';
import fs from 'fs';
import path from 'path';
import { v4 as uuidv4 } from 'uuid';

export interface AudioProcessResult {
  transcription: string;
  duration?: number;
  language: string;
  confidence?: number;
}

/**
 * Procesa audio usando Whisper API
 */
export async function processAudio(audioUrl: string): Promise<AudioProcessResult> {
  let tempFilePath: string | null = null;

  try {
    logger.info(`🎙️ Procesando audio: ${audioUrl}`);

    // ───────────────────────────────────────────────────────────────────────
    // 1. Descargar el audio
    // ───────────────────────────────────────────────────────────────────────
    tempFilePath = await downloadAudio(audioUrl);

    // ───────────────────────────────────────────────────────────────────────
    // 2. Validar tamaño
    // ───────────────────────────────────────────────────────────────────────
    const stats = fs.statSync(tempFilePath);
    if (!validateFileSize(stats.size, AI_CONFIG.limits.maxAudioSizeMB)) {
      throw new Error(`Audio demasiado grande. Máximo: ${AI_CONFIG.limits.maxAudioSizeMB}MB`);
    }

    // ───────────────────────────────────────────────────────────────────────
    // 3. Transcribir con Whisper
    // ───────────────────────────────────────────────────────────────────────
    const fileStream = fs.createReadStream(tempFilePath);

    const transcription = await openai.audio.transcriptions.create({
      file: fileStream,
      model: AI_CONFIG.whisper.model,
      language: AI_CONFIG.whisper.language,
      response_format: 'json',
      temperature: AI_CONFIG.whisper.temperature,
    });

    logger.info(`✅ Audio transcrito: "${transcription.text.substring(0, 100)}..."`);

    return {
      transcription: transcription.text,
      language: AI_CONFIG.whisper.language,
    };

  } catch (error: any) {
    logger.error('❌ Error procesando audio:', error);
    throw new Error(`Error al transcribir audio: ${error.message}`);
  } finally {
    // Limpiar archivo temporal
    if (tempFilePath && fs.existsSync(tempFilePath)) {
      fs.unlinkSync(tempFilePath);
    }
  }
}

/**
 * Descarga un archivo de audio desde una URL
 */
async function downloadAudio(url: string): Promise<string> {
  try {
    const response = await axios.get(url, {
      responseType: 'arraybuffer',
      timeout: 30000,
    });

    // Detectar extensión del archivo
    const contentType = response.headers['content-type'] || '';
    let extension = 'mp3';

    if (contentType.includes('ogg')) extension = 'ogg';
    if (contentType.includes('wav')) extension = 'wav';
    if (contentType.includes('m4a')) extension = 'm4a';

    // Crear archivo temporal
    const tempDir = path.join(process.cwd(), 'temp');
    if (!fs.existsSync(tempDir)) {
      fs.mkdirSync(tempDir, { recursive: true });
    }

    const tempFilePath = path.join(tempDir, `audio-${uuidv4()}.${extension}`);

    fs.writeFileSync(tempFilePath, Buffer.from(response.data));

    logger.debug(`✅ Audio descargado: ${tempFilePath}`);

    return tempFilePath;

  } catch (error: any) {
    logger.error('❌ Error descargando audio:', error);
    throw new Error(`Error al descargar audio: ${error.message}`);
  }
}
