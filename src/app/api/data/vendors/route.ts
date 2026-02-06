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

    const data = await request.json();
    const userRole = session.user.role || "customer"; // Sin rol = customer por defecto
    const userId = session.user.id;

    // Verificar si el vendor ya existe en la base de datos
    let existingVendor = null;
    if (data.id) {
      existingVendor = await db.getVendorById(data.id);
    }

    // Permisos:
    // - Admin: puede crear/editar cualquier vendor
    // - Vendor: puede editar su propio vendor
    // - Customer: puede crear su propio vendor (registro inicial)
    const isAdmin = userRole === "admin";
    // Customer puede crear vendor si no existe en DB (aunque envíe un ID)
    const isCustomerCreating = (userRole === "customer" || !userRole) && !existingVendor;
    const isEditingOwnVendor = existingVendor && userRole === "vendor" && existingVendor.ownerId === userId;


    if (!isAdmin && !isCustomerCreating && !isEditingOwnVendor) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    // Si es customer creando su vendor, asegurar que el ownerId sea su propio ID
    if (isCustomerCreating) {
      data.ownerId = userId;
    }

    const vendor = await db.saveVendor(data);
    return NextResponse.json({ success: true, data: vendor });
  } catch (error: any) {
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
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
