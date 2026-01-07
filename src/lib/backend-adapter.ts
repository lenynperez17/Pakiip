/**
 * Adaptador de backend - USA POSTGRESQL via APIs REST
 * Todas las funciones llaman a las APIs en /api/data/*
 */

import type { AppData, Vendor, User, DeliveryDriver, Order, Category, City, Admin, DeliveryZone, Message, Favor } from '@/lib/placeholder-data';

// Variable para controlar el intervalo de polling
let pollingInterval: NodeJS.Timeout | null = null;
let lastDataHash: string = '';

/**
 * Función auxiliar para hacer peticiones a la API
 */
async function apiRequest<T>(
  endpoint: string,
  options?: RequestInit
): Promise<{ success: boolean; data?: T; error?: string }> {
  try {
    const res = await fetch(`/api/data${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        ...options?.headers,
      },
      ...options,
    });

    const json = await res.json();

    if (!res.ok) {
      return { success: false, error: json.error || `Error ${res.status}` };
    }

    return { success: true, data: json.data || json };
  } catch (error: any) {
    console.error(`❌ [API] Error en ${endpoint}:`, error);
    return { success: false, error: error.message };
  }
}

/**
 * Cargar datos de la aplicación
 */
export async function loadAppData(defaultData: AppData): Promise<{
  success: boolean;
  data?: Omit<AppData, 'currentUser'>;
  error?: string;
}> {
  const result = await apiRequest<any>('');

  if (result.success && result.data) {
    return {
      success: true,
      data: {
        cities: result.data.cities || [],
        deliveryZones: result.data.deliveryZones || [],
        categories: result.data.categories || [],
        vendors: result.data.vendors || [],
        users: result.data.users || [],
        drivers: result.data.drivers || [],
        messages: result.data.messages || [],
        appSettings: result.data.appSettings || defaultData.appSettings,
        orders: result.data.orders || [],
        favors: result.data.favors || [],
        admins: result.data.admins || [],
        promotionalBanners: result.data.promotionalBanners || [],
        announcementBanners: result.data.announcementBanners || [],
        bankAccounts: result.data.bankAccounts || [],
        qrPayments: result.data.qrPayments || [],
      },
    };
  }

  return { success: false, error: result.error };
}

/**
 * Guardar vendor
 */
export async function saveVendorToBackend(vendor: Vendor): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/vendors', {
    method: 'POST',
    body: JSON.stringify(vendor),
  });
}

/**
 * Guardar customer/user
 */
export async function saveCustomerToBackend(user: User): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/users', {
    method: 'POST',
    body: JSON.stringify(user),
  });
}

/**
 * Guardar driver
 */
export async function saveDriverToBackend(driver: DeliveryDriver): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/drivers', {
    method: 'POST',
    body: JSON.stringify(driver),
  });
}

/**
 * Guardar order
 */
export async function saveOrderToBackend(order: Order): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/orders', {
    method: 'POST',
    body: JSON.stringify(order),
  });
}

/**
 * Guardar category
 */
export async function saveCategoryToBackend(category: Category): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/categories', {
    method: 'POST',
    body: JSON.stringify(category),
  });
}

/**
 * Guardar city
 */
export async function saveCityToBackend(city: City): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/cities', {
    method: 'POST',
    body: JSON.stringify(city),
  });
}

/**
 * Guardar settings
 */
export async function saveSettingsToBackend(settings: any): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/settings', {
    method: 'POST',
    body: JSON.stringify(settings),
  });
}

/**
 * Guardar admin
 */
export async function saveAdminToBackend(admin: Admin): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/admins', {
    method: 'POST',
    body: JSON.stringify(admin),
  });
}

/**
 * Eliminar vendor
 */
export async function deleteVendorFromBackend(vendorId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/vendors?id=${vendorId}`, {
    method: 'DELETE',
  });
}

/**
 * Eliminar driver
 */
export async function deleteDriverFromBackend(driverId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/drivers?id=${driverId}`, {
    method: 'DELETE',
  });
}

/**
 * Eliminar customer
 */
export async function deleteCustomerFromBackend(userId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/users?id=${userId}`, {
    method: 'DELETE',
  });
}

/**
 * Eliminar order
 */
export async function deleteOrderFromBackend(orderId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/orders?id=${orderId}`, {
    method: 'DELETE',
  });
}

/**
 * Eliminar category
 */
export async function deleteCategoryFromBackend(categoryId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/categories?id=${categoryId}`, {
    method: 'DELETE',
  });
}

/**
 * Eliminar city
 */
export async function deleteCityFromBackend(cityId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/cities?id=${cityId}`, {
    method: 'DELETE',
  });
}

/**
 * Eliminar admin
 */
export async function deleteAdminFromBackend(adminId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/admins?id=${adminId}`, {
    method: 'DELETE',
  });
}

/**
 * Guardar delivery zone
 */
export async function saveDeliveryZoneToBackend(zone: DeliveryZone): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/zones', {
    method: 'POST',
    body: JSON.stringify(zone),
  });
}

/**
 * Eliminar delivery zone
 */
export async function deleteDeliveryZoneFromBackend(zoneId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/zones?id=${zoneId}`, {
    method: 'DELETE',
  });
}

/**
 * Guardar mensaje
 */
export async function saveMessageToBackend(message: Message): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/messages', {
    method: 'POST',
    body: JSON.stringify(message),
  });
}

/**
 * Guardar favor
 */
export async function saveFavorToBackend(favor: Favor): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/favors', {
    method: 'POST',
    body: JSON.stringify(favor),
  });
}

/**
 * Guardar carrito
 */
export async function saveCartToBackend(userId: string, cart: any): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/carts', {
    method: 'POST',
    body: JSON.stringify({ userId, items: cart.items }),
  });
}

/**
 * Cargar carrito
 */
export async function loadCartFromBackend(userId: string): Promise<{
  success: boolean;
  data?: any;
  error?: string;
}> {
  return apiRequest(`/carts?userId=${userId}`);
}

/**
 * Guardar configuración de usuario
 */
export async function saveUserSettingsToBackend(userId: string, settings: any): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest('/users/settings', {
    method: 'POST',
    body: JSON.stringify({ userId, ...settings }),
  });
}

/**
 * Cargar configuración de usuario
 */
export async function loadUserSettingsFromBackend(userId: string): Promise<{
  success: boolean;
  data?: any;
  error?: string;
}> {
  return apiRequest(`/users/settings?userId=${userId}`);
}

/**
 * Eliminar favor
 */
export async function deleteFavorFromBackend(favorId: string): Promise<{
  success: boolean;
  error?: string;
}> {
  return apiRequest(`/favors?id=${favorId}`, {
    method: 'DELETE',
  });
}

/**
 * Suscribirse a cambios en tiempo real usando POLLING
 * Consulta la API cada 30 segundos para detectar cambios
 * IMPORTANTE: Intervalo aumentado para evitar sobrescribir cambios del admin
 */
export function subscribeToAppData(
  callback: (data: Omit<AppData, 'currentUser'>) => void
): () => void {
  // Función para cargar datos
  const fetchData = async () => {
    try {
      const res = await fetch('/api/data');
      const json = await res.json();

      if (json.success && json.data) {
        // Crear hash basado en el contenido real del JSON
        const newHash = JSON.stringify(json.data);

        if (newHash !== lastDataHash) {
          lastDataHash = newHash;
          callback(json.data);
        }
      }
    } catch {
      // Silenciar errores de polling
    }
  };

  // Ejecutar inmediatamente
  fetchData();

  // Iniciar intervalo (30 segundos para dar tiempo a que los cambios persistan)
  pollingInterval = setInterval(fetchData, 30000);

  // Retornar función para detener el polling
  return () => {
    if (pollingInterval) {
      clearInterval(pollingInterval);
      pollingInterval = null;
    }
  };
}

/**
 * Verificar si el backend está disponible
 */
export async function isBackendAvailable(): Promise<boolean> {
  try {
    const res = await fetch('/api/data');
    return res.ok;
  } catch {
    return false;
  }
}

// Exportar constante para compatibilidad
export const BACKEND = 'postgresql';
