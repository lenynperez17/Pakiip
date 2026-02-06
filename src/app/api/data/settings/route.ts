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
    console.log("[API/SETTINGS] POST recibido");
    console.log("[API/SETTINGS] Session:", session?.user?.email, "Role:", session?.user?.role);

    if (!session?.user || session.user.role !== "admin") {
      console.log("[API/SETTINGS] ERROR: Usuario no autorizado");
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden modificar la configuración" },
        { status: 403 }
      );
    }

    const data = await request.json();
    console.log("[API/SETTINGS] Datos recibidos - shipping:", JSON.stringify(data.shipping));

    const settings = await db.saveAppSettings(data);
    console.log("[API/SETTINGS] Guardado exitoso");

    return NextResponse.json({ success: true, data: settings });
  } catch (error: any) {
    console.error("[API/SETTINGS] Error guardando configuración:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
