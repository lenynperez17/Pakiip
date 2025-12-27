/**
 * API para cambiar rol de usuario
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { switchUserRole } from "@/lib/auth";
import type { UserRole } from "@/generated/prisma";

export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json(
        { success: false, error: "No autorizado" },
        { status: 401 }
      );
    }

    const { role } = await request.json();

    if (!role) {
      return NextResponse.json(
        { success: false, error: "Rol requerido" },
        { status: 400 }
      );
    }

    const result = await switchUserRole(session.user.id, role as UserRole);
    return NextResponse.json({ success: true, data: result });
  } catch (error: any) {
    console.error("Error cambiando rol:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
