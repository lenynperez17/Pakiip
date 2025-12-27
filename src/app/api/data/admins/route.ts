/**
 * API de Admins (Administradores)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener todos los admins
export async function GET() {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden ver esta información" },
        { status: 403 }
      );
    }

    const admins = await db.getAdmins();
    return NextResponse.json({ success: true, data: admins });
  } catch (error: any) {
    console.error("Error obteniendo admins:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar admin
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden gestionar admins" },
        { status: 403 }
      );
    }

    const data = await request.json();
    const { id, ...adminData } = data;

    let admin;
    if (id) {
      admin = await prisma.admin.update({
        where: { id },
        data: {
          name: adminData.name,
          email: adminData.email,
          phone: adminData.phone,
          permissions: adminData.permissions,
        },
      });
    } else {
      // Crear admin y vincularlo a usuario si existe
      const user = await prisma.user.findUnique({
        where: { email: adminData.email },
      });

      admin = await prisma.admin.create({
        data: {
          name: adminData.name,
          email: adminData.email,
          phone: adminData.phone,
          permissions: adminData.permissions || [],
          userId: user?.id,
        },
      });

      // Agregar rol admin al usuario
      if (user) {
        await prisma.userRoleAssignment.upsert({
          where: {
            userId_role: { userId: user.id, role: "admin" },
          },
          update: { isActive: true },
          create: { userId: user.id, role: "admin", isActive: true },
        });
      }
    }

    return NextResponse.json({ success: true, data: admin });
  } catch (error: any) {
    console.error("Error guardando admin:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar admin
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar admins" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de admin requerido" },
        { status: 400 }
      );
    }

    // Obtener admin antes de eliminar para quitar rol
    const admin = await prisma.admin.findUnique({ where: { id } });
    if (admin?.userId) {
      await prisma.userRoleAssignment.deleteMany({
        where: { userId: admin.userId, role: "admin" },
      });
    }

    await prisma.admin.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando admin:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
