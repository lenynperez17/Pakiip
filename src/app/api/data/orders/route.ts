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

    // Validar campos requeridos para nuevas órdenes
    if (!data.id || data.id.startsWith('TEMP_') || data.id.startsWith('order_')) {
      // Es una nueva orden, validar campos obligatorios
      if (!data.customerName || !data.customerPhone || !data.customerAddress) {
        return NextResponse.json(
          { success: false, error: "Nombre, teléfono y dirección del cliente son requeridos" },
          { status: 400 }
        );
      }
      if (!data.items || data.items.length === 0) {
        return NextResponse.json(
          { success: false, error: "La orden debe tener al menos un producto" },
          { status: 400 }
        );
      }
      if (!data.vendorId) {
        return NextResponse.json(
          { success: false, error: "La orden debe estar asociada a una tienda" },
          { status: 400 }
        );
      }
    }

    const order = await db.saveOrder(data);
    return NextResponse.json({ success: true, data: order });
  } catch (error: any) {
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

    // Verificar que el pedido existe antes de eliminar
    const existingOrder = await prisma.order.findUnique({ where: { id } });
    if (!existingOrder) {
      return NextResponse.json(
        { success: false, error: "Pedido no encontrado" },
        { status: 404 }
      );
    }

    await prisma.order.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
