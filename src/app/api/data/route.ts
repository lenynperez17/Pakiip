/**
 * API Unificada de Datos
 * Expone todas las operaciones de db-service.ts al cliente
 */

import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import * as db from "@/lib/db-service";

// GET: Cargar todos los datos de la aplicación
export async function GET(request: NextRequest) {
  try {
    const session = await auth();
    const userId = session?.user?.id;
    const userRole = session?.user?.role;

    // Cargar todos los datos en paralelo
    const [
      users,
      vendors,
      orders,
      drivers,
      categories,
      cities,
      appSettings,
      promotionalBanners,
      announcementBanners,
      bankAccounts,
      qrPayments,
      favors,
      admins,
      deliveryZones,
    ] = await Promise.all([
      db.getUsers(),
      db.getVendors(),
      db.getOrders(userRole === "admin" ? undefined : { userId }),
      db.getDrivers(),
      db.getCategories(),
      db.getCities(),
      db.getAppSettings(),
      db.getPromotionalBanners(),
      db.getAnnouncementBanners(),
      db.getBankAccounts(),
      db.getQrPayments(),
      db.getFavors(userRole === "admin" ? undefined : userId),
      db.getAdmins(),
      db.getDeliveryZones(),
    ]);

    return NextResponse.json({
      success: true,
      data: {
        users,
        vendors,
        orders,
        drivers,
        categories,
        cities,
        appSettings,
        promotionalBanners,
        announcementBanners,
        bankAccounts,
        qrPayments,
        favors,
        admins,
        deliveryZones,
        messages: [], // Los mensajes se cargan por orden
      },
    });
  } catch (error: any) {
    console.error("Error cargando datos:", error);
    return NextResponse.json(
      { success: false, error: error.message },
      { status: 500 }
    );
  }
}
