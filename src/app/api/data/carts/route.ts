/**
 * API de Carritos
 * Permite guardar y recuperar el carrito de compras de un usuario
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";

// GET: Obtener carrito del usuario
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const userId = searchParams.get("userId");

    if (!userId) {
      return NextResponse.json(
        { success: false, error: "userId es requerido" },
        { status: 400 }
      );
    }

    const cart = await db.getCart(userId);
    return NextResponse.json({ success: true, data: cart });
  } catch (error: any) {
    console.error("Error obteniendo carrito:", error);
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
    console.error("Error guardando carrito:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
