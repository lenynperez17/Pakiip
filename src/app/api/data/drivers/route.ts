/**
 * API de Drivers (Repartidores)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener todos los drivers
export async function GET() {
  try {
    const drivers = await db.getDrivers();
    return NextResponse.json({ success: true, data: drivers });
  } catch (error: any) {
    console.error("Error obteniendo drivers:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar driver
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const userRole = session.user.role;
    if (userRole !== "admin" && userRole !== "driver") {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    const data = await request.json();
    const driver = await db.saveDriver(data);
    return NextResponse.json({ success: true, data: driver });
  } catch (error: any) {
    console.error("Error guardando driver:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar driver
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar drivers" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de driver requerido" },
        { status: 400 }
      );
    }

    await prisma.driver.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando driver:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
