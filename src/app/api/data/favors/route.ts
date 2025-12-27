/**
 * API de Favors (Encargos)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener encargos
export async function GET(request: NextRequest) {
  try {
    const session = await auth();
    const userId = session?.user?.id;
    const userRole = session?.user?.role;

    // Admin ve todos, otros usuarios solo los suyos
    const favors = await db.getFavors(
      userRole === "admin" ? undefined : userId
    );

    return NextResponse.json({ success: true, data: favors });
  } catch (error: any) {
    console.error("Error obteniendo encargos:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar encargo
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
    const { id, ...favorData } = data;

    // Asignar usuario actual si es nuevo
    if (!id && !favorData.userId) {
      favorData.userId = session.user.id;
    }

    let favor;
    if (id) {
      favor = await prisma.favor.update({
        where: { id },
        data: {
          description: favorData.description,
          pickupAddress: favorData.pickupAddress,
          deliveryAddress: favorData.deliveryAddress,
          pickupLat: favorData.pickupLocation?.lat,
          pickupLng: favorData.pickupLocation?.lng,
          deliveryLat: favorData.deliveryLocation?.lat,
          deliveryLng: favorData.deliveryLocation?.lng,
          estimatedProductCost: favorData.estimatedProductCost,
          photoUrl: favorData.photoDataUri,
          quoteData: favorData.quote,
          status: favorData.status,
          driverId: favorData.driverId,
        },
      });
    } else {
      favor = await prisma.favor.create({
        data: {
          userId: favorData.userId,
          userName: favorData.userName,
          description: favorData.description,
          pickupAddress: favorData.pickupAddress,
          deliveryAddress: favorData.deliveryAddress,
          pickupLat: favorData.pickupLocation?.lat,
          pickupLng: favorData.pickupLocation?.lng,
          deliveryLat: favorData.deliveryLocation?.lat,
          deliveryLng: favorData.deliveryLocation?.lng,
          estimatedProductCost: favorData.estimatedProductCost,
          photoUrl: favorData.photoDataUri,
          quoteData: favorData.quote,
          status: favorData.status || "pending",
          driverId: favorData.driverId,
        },
      });
    }

    return NextResponse.json({ success: true, data: favor });
  } catch (error: any) {
    console.error("Error guardando encargo:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar encargo
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
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de encargo requerido" },
        { status: 400 }
      );
    }

    // Verificar que el usuario puede eliminar
    const favor = await prisma.favor.findUnique({ where: { id } });
    if (!favor) {
      return NextResponse.json(
        { success: false, error: "Encargo no encontrado" },
        { status: 404 }
      );
    }

    if (session.user.role !== "admin" && favor.userId !== session.user.id) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para eliminar este encargo" },
        { status: 403 }
      );
    }

    await prisma.favor.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando encargo:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
