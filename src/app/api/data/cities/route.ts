/**
 * API de Cities (Ciudades)
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";
import { prisma } from "@/lib/prisma";

// GET: Obtener todas las ciudades
export async function GET() {
  try {
    const cities = await db.getCities();
    return NextResponse.json({ success: true, data: cities });
  } catch (error: any) {
    console.error("Error obteniendo ciudades:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// POST: Crear o actualizar ciudad
export async function POST(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden gestionar ciudades" },
        { status: 403 }
      );
    }

    const data = await request.json();
    const { id, sectors, ...cityData } = data;

    let city;

    // Verificar si es un ID temporal (generado en el frontend) o un ID real de la DB
    const isTemporaryId = id && (id.startsWith('city') || !id.startsWith('cm'));

    if (id && !isTemporaryId) {
      // Verificar si el registro existe antes de actualizar
      const existingCity = await prisma.city.findUnique({ where: { id } });

      if (existingCity) {
        // Actualizar ciudad existente
        city = await prisma.city.update({
          where: { id },
          data: {
            name: cityData.name,
            lat: cityData.coordinates?.lat,
            lng: cityData.coordinates?.lng,
          },
        });

        // Actualizar sectores
        if (sectors) {
          // Eliminar sectores existentes
          await prisma.citySector.deleteMany({ where: { cityId: id } });
          // Crear nuevos
          for (const sector of sectors) {
            await prisma.citySector.create({
              data: {
                cityId: id,
                name: sector.name,
                fee: sector.fee,
              },
            });
          }
        }
      } else {
        // ID no existe, crear nueva ciudad
        city = await prisma.city.create({
          data: {
            name: cityData.name,
            lat: cityData.coordinates?.lat,
            lng: cityData.coordinates?.lng,
            sectors: sectors ? {
              create: sectors.map((s: any) => ({
                name: s.name,
                fee: s.fee,
              })),
            } : undefined,
          },
        });
      }
    } else {
      // Sin ID o ID temporal: crear nueva ciudad
      city = await prisma.city.create({
        data: {
          name: cityData.name,
          lat: cityData.coordinates?.lat,
          lng: cityData.coordinates?.lng,
          sectors: sectors ? {
            create: sectors.map((s: any) => ({
              name: s.name,
              fee: s.fee,
            })),
          } : undefined,
        },
      });
    }

    return NextResponse.json({ success: true, data: city });
  } catch (error: any) {
    console.error("Error guardando ciudad:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}

// DELETE: Eliminar ciudad
export async function DELETE(request: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user || session.user.role !== "admin") {
      return NextResponse.json(
        { success: false, error: "Solo administradores pueden eliminar ciudades" },
        { status: 403 }
      );
    }

    const { searchParams } = new URL(request.url);
    const id = searchParams.get("id");

    if (!id) {
      return NextResponse.json(
        { success: false, error: "ID de ciudad requerido" },
        { status: 400 }
      );
    }

    await prisma.city.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error: any) {
    console.error("Error eliminando ciudad:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
