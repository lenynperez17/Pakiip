/**
 * API de Carritos
 * Permite guardar y recuperar el carrito de compras de un usuario
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";

// GET: Obtener carrito del usuario (requiere auth)
export async function GET(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const { searchParams } = new URL(request.url);
    const userId = searchParams.get("userId");

    if (!userId) {
      return NextResponse.json(
        { success: false, error: "userId es requerido" },
        { status: 400 }
      );
    }

    // Solo admin o el propio usuario pueden ver su carrito
    if (session.user.role !== "admin" && session.user.id !== userId) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para ver este carrito" },
        { status: 403 }
      );
    }

    const cart = await db.getCart(userId);
    return NextResponse.json({ success: true, data: cart });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Guardar carrito del usuario
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
    const { userId, items } = data;

    // Solo el propio usuario o admin puede modificar el carrito
    if (session.user.role !== "admin" && session.user.id !== userId) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    const cart = await db.saveCart(userId, items || []);
    return NextResponse.json({ success: true, data: cart });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar carrito del usuario
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const { searchParams } = new URL(request.url);
    const userId = searchParams.get("userId");

    if (!userId) {
      return NextResponse.json(
        { success: false, error: "userId es requerido" },
        { status: 400 }
      );
    }

    // Solo admin o el propio usuario pueden eliminar su carrito
    if (session.user.role !== "admin" && session.user.id !== userId) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    await db.deleteCart(userId);
    return NextResponse.json({ success: true });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
