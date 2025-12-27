/**
 * API de Vendors (Tiendas)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";

// GET: Obtener todos los vendors
export async function GET() {
  try {
    const vendors = await db.getVendors();
    return NextResponse.json({ success: true, data: vendors });
  } catch (error: any) {
    console.error("Error obteniendo vendors:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar vendor
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    // Solo admin o el propio vendor puede modificar
    const data = await request.json();
    const userRole = session.user.role;

    if (userRole !== "admin" && userRole !== "vendor") {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    const vendor = await db.saveVendor(data);
    return NextResponse.json({ success: true, data: vendor });
  } catch (error: any) {
    console.error("Error guardando vendor:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar vendor
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar vendors" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de vendor requerido" },
        { status: 400 }
      );
    }

    await db.deleteVendor(id);
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando vendor:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
