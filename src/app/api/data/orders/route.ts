/**
 * API de Orders (Pedidos)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener pedidos
export async function GET(request: NextRequest) {
  try {
    const session = await auth();
    const userId = session?.user?.id;
    const userRole = session?.user?.role;

    // Filtrar según rol
    const filters: any = {};
    if (userRole === "customer" && userId) {
      filters.userId = userId;
    } else if (userRole === "driver" && userId) {
      filters.driverId = userId;
    }
    // Admin ve todos los pedidos

    const orders = await db.getOrders(filters);
    return NextResponse.json({ success: true, data: orders });
  } catch (error: any) {
    console.error("Error obteniendo pedidos:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar pedido
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const data = await request.json();
    const order = await db.saveOrder(data);
    return NextResponse.json({ success: true, data: order });
  } catch (error: any) {
    console.error("Error guardando pedido:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar pedido
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar pedidos" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de pedido requerido" },
        { status: 400 }
      );
    }

    await prisma.order.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando pedido:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
