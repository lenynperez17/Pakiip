/**
 * Adaptador de backend - USA SOLO FIREBASE
 * TODAS las funciones guardan REALMENTE en Firestore
 */

import type { AppData, Vendor, User, DeliveryDriver, Order, Category, City, Admin } from '@/types';
import {
  loadAppDataFromFirestore,
  subscribeToAppData as firebaseSubscribe,
  isFirestoreAvailable,
  COLLECTIONS
} from './firestore-service';
import { saveDocument, deleteDocument } from './firebase';

console.log('🔧 [BACKEND] Usando backend: firebase');

/**
 * Cargar datos de la aplicación
 */
export async function loadAppData(defaultData: AppData): Promise<{
  success: boolean;
  data?: Omit<AppData, 'currentUser'>;
  error?: string;
}> {
  return loadAppDataFromFirestore(defaultData);
}

/**
 * Guardar vendor - GUARDA REALMENTE EN FIREBASE
 */
export async function saveVendorToBackend(vendor: Vendor): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.VENDORS, vendor.id, vendor);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar customer/user - GUARDA REALMENTE EN FIREBASE
 */
export async function saveCustomerToBackend(user: User): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.USERS, user.id, user);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar driver - GUARDA REALMENTE EN FIREBASE
 */
export async function saveDriverToBackend(driver: DeliveryDriver): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.DRIVERS, driver.id, driver);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar order - GUARDA REALMENTE EN FIREBASE
 */
export async function saveOrderToBackend(order: Order): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.ORDERS, order.id, order);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar category - GUARDA REALMENTE EN FIREBASE
 */
export async function saveCategoryToBackend(category: Category): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.CATEGORIES, category.id, category);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar city - GUARDA REALMENTE EN FIREBASE
 */
export async function saveCityToBackend(city: City): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.CITIES, city.id, city);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar settings - GUARDA REALMENTE EN FIREBASE
 */
export async function saveSettingsToBackend(settings: any): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.APP_DATA, 'main', { appSettings: settings });
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar admin - GUARDA REALMENTE EN FIREBASE
 */
export async function saveAdminToBackend(admin: Admin): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.ADMINS, admin.id, admin);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar vendor - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteVendorFromBackend(vendorId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.VENDORS, vendorId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar driver - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteDriverFromBackend(driverId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.DRIVERS, driverId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar customer - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteCustomerFromBackend(userId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.USERS, userId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar order - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteOrderFromBackend(orderId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.ORDERS, orderId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar category - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteCategoryFromBackend(categoryId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.CATEGORIES, categoryId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar city - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteCityFromBackend(cityId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.CITIES, cityId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar admin - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteAdminFromBackend(adminId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.ADMINS, adminId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar delivery zone - GUARDA REALMENTE EN FIREBASE
 */
export async function saveDeliveryZoneToBackend(zone: any): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.DELIVERY_ZONES, zone.id, zone);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar delivery zone - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteDeliveryZoneFromBackend(zoneId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.DELIVERY_ZONES, zoneId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar mensaje - GUARDA REALMENTE EN FIREBASE
 */
export async function saveMessageToBackend(message: any): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.MESSAGES, message.id, message);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar favor - GUARDA REALMENTE EN FIREBASE
 */
export async function saveFavorToBackend(favor: any): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.FAVORS, favor.id, favor);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar carrito - GUARDA REALMENTE EN FIREBASE
 */
export async function saveCartToBackend(userId: string, cart: any): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const cartData = {
      userId,
      items: cart.items,
      lastUpdated: new Date().toISOString()
    };
    const result = await saveDocument(COLLECTIONS.CARTS, userId, cartData);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Cargar carrito - CARGA REALMENTE DE FIREBASE
 */
export async function loadCartFromBackend(userId: string): Promise<{
  success: boolean;
  data?: any;
  error?: string;
}> {
  try {
    const { getDocument } = await import('./firebase');
    const result = await getDocument(COLLECTIONS.CARTS, userId);

    if (result.success && result.data) {
      return { success: true, data: result.data };
    }

    return { success: true, data: { items: [] } };
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Guardar configuración de usuario - GUARDA REALMENTE EN FIREBASE
 */
export async function saveUserSettingsToBackend(userId: string, settings: any): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await saveDocument(COLLECTIONS.USER_SETTINGS, userId, {
      ...settings,
      lastUpdated: new Date().toISOString()
    });
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Cargar configuración de usuario - CARGA REALMENTE DE FIREBASE
 */
export async function loadUserSettingsFromBackend(userId: string): Promise<{
  success: boolean;
  data?: any;
  error?: string;
}> {
  try {
    const { getDocument } = await import('./firebase');
    const result = await getDocument(COLLECTIONS.USER_SETTINGS, userId);

    if (result.success && result.data) {
      return { success: true, data: result.data };
    }

    return { success: true, data: {} };
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Eliminar favor - ELIMINA REALMENTE DE FIREBASE
 */
export async function deleteFavorFromBackend(favorId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  try {
    const result = await deleteDocument(COLLECTIONS.FAVORS, favorId);
    return result;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

/**
 * Suscribirse a cambios en tiempo real usando Firebase
 */
export function subscribeToAppData(
  callback: (data: Omit<AppData, 'currentUser'>) => void
): () => void {
  console.log('🔔 [BACKEND] Estableciendo suscripción a Firebase...');
  const unsubscribe = firebaseSubscribe(callback);
  console.log('✅ [BACKEND] Suscripción establecida correctamente');

  return () => {
    console.log('🔕 [BACKEND] Desuscribiendo de Firebase...');
    unsubscribe();
  };
}

/**
 * Verificar si Firebase está disponible
 */
export async function isBackendAvailable(): Promise<boolean> {
  return isFirestoreAvailable();
}

// Exportar constante para compatibilidad
export const BACKEND = 'firebase';
