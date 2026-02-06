/**
 * API de Messages (Mensajes de Chat)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

// GET: Obtener mensajes de un pedido
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const orderId = searchParams.get("orderId");

    if (!orderId) {
      return NextResponse.json(
        { success: false, error: "orderId requerido" },
        { status: 400 }
      );
    }

    const messages = await prisma.message.findMany({
      where: { orderId },
      orderBy: { createdAt: "asc" },
    });

    const formattedMessages = messages.map((m) => ({
      id: m.id,
      orderId: m.orderId,
      senderId: m.senderId,
      senderName: m.senderName,
      senderRole: m.senderType,
      content: m.text,
      timestamp: m.createdAt.toISOString(),
    }));

    return NextResponse.json({ success: true, data: formattedMessages });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Enviar mensaje
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

    const message = await prisma.message.create({
      data: {
        orderId: data.orderId,
        senderId: session.user.id,
        senderName: session.user.name || "Usuario",
        senderType: session.user.role || "customer",
        text: data.content,
      },
    });

    return NextResponse.json({
      success: true,
      data: {
        id: message.id,
        orderId: message.orderId,
        senderId: message.senderId,
        senderName: message.senderName,
        senderRole: message.senderType,
        content: message.text,
        timestamp: message.createdAt.toISOString(),
      },
    });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar mensaje (solo admin o autor)
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
        { success: false, error: "ID de mensaje requerido" },
        { status: 400 }
      );
    }

    const message = await prisma.message.findUnique({ where: { id } });
    if (!message) {
      return NextResponse.json(
        { success: false, error: "Mensaje no encontrado" },
        { status: 404 }
      );
    }

    // Solo admin o el autor pueden eliminar
    if (session.user.role !== "admin" && session.user.id !== message.senderId) {
      return NextResponse.json(
        { success: false, error: "No tienes permisos para eliminar este mensaje" },
        { status: 403 }
      );
    }

    await prisma.message.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
