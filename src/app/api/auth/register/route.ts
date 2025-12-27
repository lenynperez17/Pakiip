/**
 * API de Registro de Usuarios
 */

import { NextRequest, NextResponse } from "next/server";
import { registerUser } from "@/lib/auth";

export async function POST(request: NextRequest) {
  try {
    const { email, password, name, phone } = await request.json();

    // Validaciones
    if (!email || !password || !name) {
      return NextResponse.json(
        { success: false, error: "Email, contraseña y nombre son requeridos" },
        { status: 400 }
      );
    }

    if (password.length < 6) {
      return NextResponse.json(
        { success: false, error: "La contraseña debe tener al menos 6 caracteres" },
        { status: 400 }
      );
    }

    // Registrar usuario
    const user = await registerUser({ email, password, name, phone });

    return NextResponse.json({
      success: true,
      data: {
        id: user.id,
        email: user.email,
        name: user.name,
      },
    });
  } catch (error: any) {
    console.error("Error en registro:", error);
    return NextResponse.json(
      { success: false, error: error.message || "Error al registrar usuario" },
      { status: 500 }
    );
  }
}
