/**
 * API de Configuración de Usuario
 * Permite guardar y recuperar preferencias del usuario (rol seleccionado, etc.)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

// GET: Obtener configuración del usuario
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

    const settings = await prisma.userSettings.findUnique({
      where: { userId },
    });

    if (!settings) {
      // Retornar configuración por defecto si no existe
      return NextResponse.json({
        success: true,
        data: {
          userId,
          selectedRole: "customer",
        },
      });
    }

    return NextResponse.json({
      success: true,
      data: {
        userId: settings.userId,
        selectedRole: settings.selectedRole,
      },
    });
  } catch (error: any) {
    console.error("Error obteniendo configuración de usuario:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Guardar configuración del usuario
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
    const { userId, selectedRole } = data;

    // Solo el propio usuario o admin puede modificar la configuración
    if (session.user.role !== "admin" && session.user.id !== userId) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para esta operación" },
        { status: 403 }
      );
    }

    const settings = await prisma.userSettings.upsert({
      where: { userId },
      update: { selectedRole },
      create: {
        userId,
        selectedRole: selectedRole || "customer",
      },
    });

    return NextResponse.json({
      success: true,
      data: {
        userId: settings.userId,
        selectedRole: settings.selectedRole,
      },
    });
  } catch (error: any) {
    console.error("Error guardando configuración de usuario:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
