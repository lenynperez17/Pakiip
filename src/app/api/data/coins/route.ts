/**
 * API para gestionar las monedas de usuario
 * Reemplaza las funciones de firestore-coins.ts
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { CoinTransactionType } from "@/generated/prisma";

// GET /api/data/coins?userId=xxx - Obtener monedas y transacciones de un usuario
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

    // Obtener usuario con sus monedas
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: { coins: true },
    });

    // Obtener transacciones de monedas
    const transactions = await prisma.coinTransaction.findMany({
      where: { userId },
      orderBy: { createdAt: "desc" },
      take: 50, // Limitar a las últimas 50 transacciones
    });

    // Formatear transacciones para compatibilidad con el frontend
    const formattedTransactions = transactions.map((tx) => ({
      id: tx.id,
      userId: tx.userId,
      type: tx.type.toLowerCase(),
      amount: tx.amount,
      reason: tx.reason,
      description: tx.description,
      timestamp: tx.createdAt.toISOString(),
    }));

    return NextResponse.json({
      success: true,
      data: {
        coins: user?.coins || 0,
        transactions: formattedTransactions,
      },
    });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}

// POST /api/data/coins - Agregar transacción de monedas
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const body = await request.json();
    const { userId, type, amount, reason, description, relatedOrderId } = body;

    if (!userId || !type || amount === undefined) {
      return NextResponse.json(
        { success: false, error: "userId, type y amount son requeridos" },
        { status: 400 }
      );
    }

    // Validar que el monto sea un número válido y no sea negativo para EARNED/BONUS
    if (typeof amount !== 'number' || isNaN(amount)) {
      return NextResponse.json(
        { success: false, error: "El monto debe ser un número válido" },
        { status: 400 }
      );
    }

    // Para SPENT el monto debe ser negativo, para EARNED/BONUS positivo
    const upperType = type.toUpperCase();
    if ((upperType === 'EARNED' || upperType === 'BONUS') && amount <= 0) {
      return NextResponse.json(
        { success: false, error: "El monto para EARNED/BONUS debe ser positivo" },
        { status: 400 }
      );
    }
    if (upperType === 'SPENT' && amount >= 0) {
      return NextResponse.json(
        { success: false, error: "El monto para SPENT debe ser negativo" },
        { status: 400 }
      );
    }

    // Validar tipo de transacción
    const transactionType = type.toUpperCase() as CoinTransactionType;
    if (!["EARNED", "SPENT", "BONUS"].includes(transactionType)) {
      return NextResponse.json(
        { success: false, error: "Tipo de transacción inválido" },
        { status: 400 }
      );
    }

    // Crear transacción y actualizar balance en una transacción de DB
    const result = await prisma.$transaction(async (tx) => {
      // Crear la transacción de monedas
      const transaction = await tx.coinTransaction.create({
        data: {
          userId,
          type: transactionType,
          amount,
          reason,
          description,
          relatedOrderId,
        },
      });

      // Actualizar balance del usuario
      const updatedUser = await tx.user.update({
        where: { id: userId },
        data: {
          coins: { increment: amount },
        },
        select: { coins: true },
      });

      return { transaction, newBalance: updatedUser.coins };
    });

    return NextResponse.json({
      success: true,
      data: {
        transaction: {
          id: result.transaction.id,
          userId: result.transaction.userId,
          type: result.transaction.type.toLowerCase(),
          amount: result.transaction.amount,
          reason: result.transaction.reason,
          description: result.transaction.description,
          timestamp: result.transaction.createdAt.toISOString(),
        },
        newBalance: result.newBalance,
      },
    });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}

// PUT /api/data/coins - Actualizar balance de monedas directamente
export async function PUT(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const body = await request.json();
    const { userId, coins } = body;

    if (!userId || coins === undefined) {
      return NextResponse.json(
        { success: false, error: "userId y coins son requeridos" },
        { status: 400 }
      );
    }

    const updatedUser = await prisma.user.update({
      where: { id: userId },
      data: { coins },
      select: { coins: true },
    });

    return NextResponse.json({
      success: true,
      data: { coins: updatedUser.coins },
    });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message || "Error interno" },
      { status: 500 }
    );
  }
}
