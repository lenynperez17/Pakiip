/**
 * API de Categories (Categorías)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener todas las categorías
export async function GET() {
  try {
    const categories = await db.getCategories();
    return NextResponse.json({ success: true, data: categories });
  } catch (error: any) {
    console.error("Error obteniendo categorías:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar categoría
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden gestionar categorías" },
        { status: 403 }
      );
    }

    const data = await request.json();
    const category = await db.saveCategory(data);
    return NextResponse.json({ success: true, data: category });
  } catch (error: any) {
    console.error("Error guardando categoría:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar categoría
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar categorías" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de categoría requerido" },
        { status: 400 }
      );
    }

    await prisma.category.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando categoría:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
