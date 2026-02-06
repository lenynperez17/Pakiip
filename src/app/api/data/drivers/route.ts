/**
 * API de Drivers (Repartidores)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener todos los drivers (solo admin)
export async function GET() {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden ver drivers" },
        { status: 401 }
      );
    }

    const drivers = await db.getDrivers();
    return NextResponse.json({ success: true, data: drivers });
  } catch (error: any) {
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
    const userId = session.user.id;

    if (userRole !== "admin" && userRole !== "driver") {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    const data = await request.json();

    // Si es driver (no admin), solo puede modificar su propio perfil
    // y NO puede modificar campos sensibles como deuda o comisión
    if (userRole === "driver") {
      const existingDriver = await db.getDriverById(data.id);
      if (!existingDriver || existingDriver.userId !== userId) {
        return NextResponse.json(
          { success: false, error: "Solo puedes modificar tu propio perfil" },
          { status: 403 }
        );
      }
      // Proteger campos sensibles - mantener valores originales
      data.debt = existingDriver.debt;
      data.commission = existingDriver.commission;
      data.isActive = existingDriver.isActive;
    }

    const driver = await db.saveDriver(data);
    return NextResponse.json({ success: true, data: driver });
  } catch (error: any) {
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
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
