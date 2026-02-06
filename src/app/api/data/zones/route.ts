/**
 * API de DeliveryZones (Zonas de Entrega)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener todas las zonas
export async function GET() {
  try {
    const zones = await db.getDeliveryZones();
    return NextResponse.json({ success: true, data: zones });
  } catch (error: any) {
    console.error("Error obteniendo zonas:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar zona
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden gestionar zonas" },
        { status: 403 }
      );
    }

    const data = await request.json();
    const { id, ...zoneData } = data;

    const zonePayload = {
      name: zoneData.name,
      cityId: zoneData.cityId,
      path: zoneData.path,
      shippingFee: zoneData.shippingFee,
    };

    let zone;
    if (id) {
      // Verificar si el registro existe antes de actualizar
      const existing = await prisma.deliveryZone.findUnique({ where: { id } });

      if (existing) {
        zone = await prisma.deliveryZone.update({
          where: { id },
          data: zonePayload,
        });
      } else {
        // Si el ID no existe en la DB, crear nuevo registro
        zone = await prisma.deliveryZone.create({
          data: zonePayload,
        });
      }
    } else {
      zone = await prisma.deliveryZone.create({
        data: zonePayload,
      });
    }

    return NextResponse.json({ success: true, data: zone });
  } catch (error: any) {
    console.error("Error guardando zona:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar zona
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar zonas" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de zona requerido" },
        { status: 400 }
      );
    }

    await prisma.deliveryZone.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando zona:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
