/**
 * API de Upload de Imágenes
 * Usa almacenamiento local en servidor
 */

import { NextRequest, NextResponse } from "next/server";
import { writeFile, mkdir } from "fs/promises";
import { join } from "path";
import sharp from "sharp";
import { auth } from "@/lib/auth";

// Configuración
const UPLOAD_DIR = join(process.cwd(), "public", "uploads");
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/webp", "image/gif"];

export async function POST(request: NextRequest) {
  try {
    // Verificar autenticación
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const formData = await request.formData();
    const file = formData.get("file") as File | null;
    const path = formData.get("path") as string | null;

    if (!file) {
      return NextResponse.json(
        { success: false, error: "No se recibió ningún archivo" },
        { status: 400 }
      );
    }

    // Validar tipo de archivo
    if (!ALLOWED_TYPES.includes(file.type)) {
      return NextResponse.json(
        { success: false, error: "Tipo de archivo no permitido" },
        { status: 400 }
      );
    }

    // Validar tamaño
    if (file.size > MAX_FILE_SIZE) {
      return NextResponse.json(
        { success: false, error: "El archivo es demasiado grande (máx 10MB)" },
        { status: 400 }
      );
    }

    // Leer archivo
    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    // Optimizar imagen con sharp
    const optimized = await sharp(buffer)
      .resize(1200, 1200, {
        fit: "inside",
        withoutEnlargement: true
      })
      .webp({ quality: 80 })
      .toBuffer();

    // Crear directorio si no existe
    const uploadPath = path || "misc";
    const fullDir = join(UPLOAD_DIR, uploadPath);
    await mkdir(fullDir, { recursive: true });

    // Generar nombre único
    const filename = `${Date.now()}-${Math.random().toString(36).substring(7)}.webp`;
    const filepath = join(fullDir, filename);

    // Guardar archivo
    await writeFile(filepath, optimized);

    // Retornar URL pública
    const url = `/uploads/${uploadPath}/${filename}`;

    return NextResponse.json({
      success: true,
      url,
      originalSize: buffer.length,
      optimizedSize: optimized.length,
      compression: Math.round((1 - optimized.length / buffer.length) * 100),
    });
  } catch (error: any) {
    console.error("Error en upload:", error);
    return NextResponse.json(
      { success: false, error: error.message || "Error al subir archivo" },
      { status: 500 }
    );
  }
}

/**
 * Subir imagen desde base64 (para migración)
 */
export async function PUT(request: NextRequest) {
  try {
    const { base64, path } = await request.json();

    if (!base64 || !path) {
      return NextResponse.json(
        { success: false, error: "Faltan parámetros" },
        { status: 400 }
      );
    }

    // Extraer datos del base64
    const matches = base64.match(/^data:image\/(\w+);base64,(.+)$/);
    if (!matches) {
      return NextResponse.json(
        { success: false, error: "Formato base64 inválido" },
        { status: 400 }
      );
    }

    const buffer = Buffer.from(matches[2], "base64");

    // Optimizar
    const optimized = await sharp(buffer)
      .resize(1200, 1200, {
        fit: "inside",
        withoutEnlargement: true
      })
      .webp({ quality: 80 })
      .toBuffer();

    // Crear directorio
    const fullDir = join(UPLOAD_DIR, path);
    await mkdir(fullDir, { recursive: true });

    // Guardar
    const filename = `${Date.now()}.webp`;
    const filepath = join(fullDir, filename);
    await writeFile(filepath, optimized);

    const url = `/uploads/${path}/${filename}`;

    return NextResponse.json({ success: true, url });
  } catch (error: any) {
    console.error("Error en upload base64:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
