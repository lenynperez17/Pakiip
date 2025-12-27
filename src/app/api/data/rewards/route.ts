/**
 * API para gestionar recompensas
 * Reemplaza las funciones de firestore-coins.ts
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

// GET /api/data/rewards - Obtener todas las recompensas activas
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const activeOnly = searchParams.get("active") !== "false";

    const whereClause = activeOnly ? { isActive: true } : {};

    const rewards = await prisma.reward.findMany({
      where: whereClause,
      orderBy: { coinsCost: "asc" },
    });

    // Formatear para compatibilidad con el frontend
    const formattedRewards = rewards.map((reward) => ({
      id: reward.id,
      name: reward.name,
      description: reward.description,
      coinsCost: reward.coinsCost,
      imageUrl: reward.imageUrl,
      type: reward.type,
      value: reward.value ? Number(reward.value) : null,
      stock: reward.stock,
      isActive: reward.isActive,
    }));

    return NextResponse.json({
      success: true,
      data: formattedRewards,
    });
  } catch (error: any) {
    console.error("Error obteniendo recompensas:", error);
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}

// POST /api/data/rewards - Crear nueva recompensa (solo admin)
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const body = await request.json();
    const { name, description, coinsCost, imageUrl, type, value, stock, isActive } = body;

    if (!name || !coinsCost || !type) {
      return NextResponse.json(
        { success: false, error: "name, coinsCost y type son requeridos" },
        { status: 400 }
      );
    }

    const reward = await prisma.reward.create({
      data: {
        name,
        description,
        coinsCost,
        imageUrl,
        type,
        value: value || null,
        stock: stock || 0,
        isActive: isActive !== false,
      },
    });

    return NextResponse.json({
      success: true,
      data: {
        id: reward.id,
        name: reward.name,
        description: reward.description,
        coinsCost: reward.coinsCost,
        imageUrl: reward.imageUrl,
        type: reward.type,
        value: reward.value ? Number(reward.value) : null,
        stock: reward.stock,
        isActive: reward.isActive,
      },
    });
  } catch (error: any) {
    console.error("Error creando recompensa:", error);
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}

// PUT /api/data/rewards - Actualizar recompensa (solo admin)
export async function PUT(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const body = await request.json();
    const { id, ...updateData } = body;

    if (!id) {
      return NextResponse.json(
        { success: false, error: "id es requerido" },
        { status: 400 }
      );
    }

    const reward = await prisma.reward.update({
      where: { id },
      data: updateData,
    });

    return NextResponse.json({
      success: true,
      data: {
        id: reward.id,
        name: reward.name,
        description: reward.description,
        coinsCost: reward.coinsCost,
        imageUrl: reward.imageUrl,
        type: reward.type,
        value: reward.value ? Number(reward.value) : null,
        stock: reward.stock,
        isActive: reward.isActive,
      },
    });
  } catch (error: any) {
    console.error("Error actualizando recompensa:", error);
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}

// DELETE /api/data/rewards?id=xxx - Eliminar recompensa (solo admin)
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "id es requerido" },
        { status: 400 }
      );
    }

    await prisma.reward.delete({
      where: { id },
    });

    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando recompensa:", error);
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}
