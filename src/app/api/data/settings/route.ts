/**
 * API de AppSettings (Configuración)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";

// GET: Obtener configuración
export async function GET() {
  try {
    const settings = await db.getAppSettings();
    return NextResponse.json({ success: true, data: settings });
  } catch (error: any) {
    console.error("Error obteniendo configuración:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Actualizar configuración
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden modificar la configuración" },
        { status: 403 }
      );
    }

    const data = await request.json();
    const settings = await db.saveAppSettings(data);
    return NextResponse.json({ success: true, data: settings });
  } catch (error: any) {
    console.error("Error guardando configuración:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
