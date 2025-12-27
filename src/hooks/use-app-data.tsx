"use client";

/**
 * Hook principal de datos de la aplicación
 * Migrado a PostgreSQL + NextAuth (sin Firebase)
 */

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useSession, signOut as nextAuthSignOut } from 'next-auth/react';
import { AppData, Vendor, Category, DeliveryDriver, User, AppSettings, Message, Product, Order, City, Admin, DebtTransaction, DeliveryZone, Favor } from '@/lib/placeholder-data';
import {
  loadAppData,
  saveVendorToBackend,
  saveCustomerToBackend,
  saveDriverToBackend,
  saveOrderToBackend,
  saveCategoryToBackend,
  saveCityToBackend,
  saveSettingsToBackend,
  saveAdminToBackend,
  deleteVendorFromBackend,
  deleteDriverFromBackend,
  deleteCustomerFromBackend,
  deleteOrderFromBackend,
  deleteCategoryFromBackend,
  deleteCityFromBackend,
  deleteAdminFromBackend,
  saveDeliveryZoneToBackend,
  deleteDeliveryZoneFromBackend,
  saveMessageToBackend,
  saveFavorToBackend,
  deleteFavorFromBackend,
  subscribeToAppData,
  isBackendAvailable,
  saveUserSettingsToBackend,
  loadUserSettingsFromBackend,
} from '@/lib/backend-adapter';

type LoggedInUser = (User | Vendor | DeliveryDriver | Admin) & { role: 'customer' | 'vendor' | 'driver' | 'admin' };

// Estado inicial vacio - TODO se carga desde PostgreSQL
const emptyAppData: AppData = {
  cities: [],
  deliveryZones: [],
  categories: [],
  vendors: [],
  users: [],
  drivers: [],
  messages: [],
  appSettings: {
    appName: 'Pakiip',
    logoUrl: '',
    heroImageUrl: '',
    welcomeImageUrl: '',
    driverWelcomeImageUrl: '',
    taxType: 'gravada',
    taxRate: 18,
    taxExemptRegions: [],
    currencySymbol: 'S/.',
    verificationMethods: {
      email: true,
      sms: false,
      whatsapp: false,
    },
    enablePasswordRecovery: true,
    featuredStoreCost: 25.00,
    customDomain: 'pakiip.com',
    paymentMethods: {
      bankAccounts: [],
      qrPayments: [],
      gateway: {
        provider: 'none',
        publicKey: '',
        secretKey: '',
        enabled: false,
      },
      cashOnDeliveryEnabled: true,
    },
    shipping: {
      baseRadiusKm: 3,
      baseFee: 5.00,
      feePerKm: 1.50,
    },
    promotionalBanners: [],
    announcementBanners: [],
  },
  orders: [],
  favors: [],
  admins: [],
};

// --- Context Definition ---
interface AppDataContextValue extends Omit<AppData, 'currentUser'> {
  currentUser: LoggedInUser | null;
  availableRoles: string[];
  selectedCity: string | null;
  setSelectedCity: (city: string | null) => void;
  login: (email: string) => LoggedInUser | null;
  logout: () => void;
  getUserRoles: (email: string) => Array<{role: 'customer' | 'vendor' | 'driver' | 'admin', data: User | Vendor | DeliveryDriver | Admin}>;
  switchRole: (role: 'customer' | 'vendor' | 'driver' | 'admin') => boolean;
  syncStatus: 'connected' | 'disconnected' | 'error' | 'reconnecting';
  lastSyncError: string | null;
  saveVendor: (vendor: Vendor) => void;
  deleteVendor: (vendorId: string) => void;
  saveCategory: (category: Category) => void;
  deleteCategory: (categoryId: string) => void;
  saveDriver: (driver: DeliveryDriver) => void;
  deleteDriver: (driverId: string) => void;
  saveUser: (user: User) => void;
  deleteUser: (userId: string) => void;
  saveSettings: (settings: AppSettings) => void;
  addMessage: (newMessage: Omit<Message, 'id' | 'timestamp'>) => void;
  saveOrder: (order: Order) => void;
  addOrder: (newOrderData: Omit<Order, 'id' | 'date' | 'status' | 'verificationCode'>) => Promise<Order>;
  deleteOrder: (orderId: string) => void;
  getAllProducts: () => Product[];
  getVendorById: (id: string) => Vendor | undefined;
  getVendorByOwnerId: (ownerId: string) => Vendor | undefined;
  getDriverById: (id: string) => DeliveryDriver | undefined;
  getDriverByUserId: (userId: string) => DeliveryDriver | undefined;
  getProductById: (id: string) => Product | undefined;
  getMessagesForOrder: (orderId: string) => Message[];
  saveCity: (city: City) => void;
  deleteCity: (cityId: string) => void;
  saveDeliveryZone: (zone: DeliveryZone) => void;
  deleteDeliveryZone: (zoneId: string) => void;
  markVendorPayoutAsPaid: (vendorId: string) => void;
  addCreditTransaction: (driverId: string, amount: number, description: string) => void;
  addDebtTransaction: (driverId: string, amount: number, description: string) => void;
  clearDriverDebt: (driverId: string) => void;
  saveAdmin: (admin: Admin) => void;
  deleteAdmin: (adminId: string) => void;
  markDriverPayoutAsPaid: (driverId: string, netBalance: number, ordersToMarkAsPaid: Order[]) => void;
  addFavor: (favorData: Omit<Favor, 'id' | 'date'>) => Promise<Favor>;
  saveFavor: (favor: Favor) => void;
  deleteFavor: (favorId: string) => void;
}

const AppDataContext = createContext<AppDataContextValue | undefined>(undefined);

// --- Provider Component ---
export const AppDataProvider = ({ children }: { children: ReactNode }) => {
  const { data: session, status, update: updateSession } = useSession();
  const [data, setData] = useState<AppData>(emptyAppData);
  const [isInitialized, setIsInitialized] = useState(false);
  const [currentUser, setCurrentUser] = useState<LoggedInUser | null>(null);
  const [availableRoles, setAvailableRoles] = useState<string[]>([]);
  const [selectedCity, _setSelectedCity] = useState<string | null>(null);
  const [syncStatus, setSyncStatus] = useState<'connected' | 'disconnected' | 'error' | 'reconnecting'>('disconnected');
  const [lastSyncError, setLastSyncError] = useState<string | null>(null);

  const setSelectedCity = async (cityName: string | null) => {
    _setSelectedCity(cityName);

    // Guardar en backend si hay usuario autenticado
    if (currentUser) {
      const result = await saveUserSettingsToBackend(currentUser.id, { selectedCity: cityName });
      if (!result.success) {
        console.error('Error guardando ciudad:', result.error);
      }
    }
  };

  // Cargar selectedCity cuando cambia currentUser
  useEffect(() => {
    async function loadUserSettings() {
      if (!currentUser) {
        _setSelectedCity(null);
        return;
      }

      const result = await loadUserSettingsFromBackend(currentUser.id);

      if (result.success && result.data?.selectedCity) {
        _setSelectedCity(result.data.selectedCity);
      } else {
        // Si no hay ciudad guardada, usar la primera disponible
        if (data.cities?.length > 0) {
          _setSelectedCity(data.cities[0].name);
        }
      }
    }

    loadUserSettings();
  }, [currentUser, data.cities]);

  // Cargar datos iniciales desde PostgreSQL
  useEffect(() => {
    const initializeData = async () => {
      // Verificar disponibilidad del backend
      const backendAvailable = await isBackendAvailable();
      if (!backendAvailable) {
        setIsInitialized(true);
        return;
      }

      // Cargar datos iniciales
      console.log('[AppData] Cargando datos iniciales...');
      const result = await loadAppData(emptyAppData);

      if (result.success && result.data) {
        const loadedData = { ...result.data, currentUser: undefined } as AppData;
        console.log('[AppData] Datos cargados exitosamente:');
        console.log('[AppData] - Vendors:', loadedData.vendors?.length || 0);
        console.log('[AppData] - Users:', loadedData.users?.length || 0);
        console.log('[AppData] - Orders:', loadedData.orders?.length || 0);
        console.log('[AppData] - Categories:', loadedData.categories?.length || 0);

        if (loadedData.vendors?.length > 0) {
          console.log('[AppData] - Vendor IDs:', loadedData.vendors.map(v => v.id));
        }

        setData(loadedData);

        // Detectar ciudad automaticamente
        if (loadedData.cities.length > 0) {
          _setSelectedCity(loadedData.cities[0].name);
        }

        setIsInitialized(true);
      } else {
        console.error('[AppData] Error al cargar datos:', result.error);
        setIsInitialized(true);
      }
    };

    initializeData();
  }, []);

  // Listener en tiempo real con polling
  useEffect(() => {
    if (!isInitialized) return;

    setSyncStatus('reconnecting');

    let unsubscribe: (() => void) | null = null;

    const setupRealtimeSync = async () => {
      try {
        unsubscribe = subscribeToAppData((updatedData) => {
          // Validar datos antes de actualizar
          setData(prevData => {
            // Filtrar admins invalidos
            const validAdmins = Array.isArray(updatedData.admins)
              ? updatedData.admins.filter(admin =>
                  Boolean(
                    admin &&
                    typeof admin === 'object' &&
                    admin.id &&
                    typeof admin.id === 'string' &&
                    admin.email &&
                    typeof admin.email === 'string' &&
                    admin.name &&
                    typeof admin.name === 'string'
                  )
                )
              : prevData.admins;

            const mergedData = {
              ...prevData,
              ...(updatedData.vendors !== undefined && { vendors: updatedData.vendors }),
              ...(updatedData.users !== undefined && { users: updatedData.users }),
              ...(updatedData.drivers !== undefined && { drivers: updatedData.drivers }),
              ...(updatedData.orders !== undefined && { orders: updatedData.orders }),
              ...(updatedData.admins !== undefined && { admins: validAdmins }),
              ...(updatedData.categories !== undefined && { categories: updatedData.categories }),
              ...(updatedData.cities !== undefined && { cities: updatedData.cities }),
              ...(updatedData.deliveryZones !== undefined && { deliveryZones: updatedData.deliveryZones }),
              ...(updatedData.messages !== undefined && { messages: updatedData.messages }),
              ...(updatedData.favors !== undefined && { favors: updatedData.favors }),
              ...(updatedData.promotionalBanners !== undefined && { promotionalBanners: updatedData.promotionalBanners }),
              ...(updatedData.announcementBanners !== undefined && { announcementBanners: updatedData.announcementBanners }),
              ...(updatedData.bankAccounts !== undefined && { bankAccounts: updatedData.bankAccounts }),
              ...(updatedData.qrPayments !== undefined && { qrPayments: updatedData.qrPayments }),
              ...(updatedData.appSettings !== undefined && { appSettings: updatedData.appSettings }),
              currentUser: undefined
            } as AppData;

            return mergedData;
          });

          // Actualizar ciudad seleccionada si cambio
          if (updatedData.cities?.length > 0 && !selectedCity) {
            _setSelectedCity(updatedData.cities[0].name);
          }

          setSyncStatus('connected');
          setLastSyncError(null);
        });

        setSyncStatus('connected');
        setLastSyncError(null);
      } catch (error) {
        const errorMessage = error instanceof Error ? error.message : 'Error desconocido';
        console.error('Error al establecer sincronizacion:', errorMessage);
        setSyncStatus('error');
        setLastSyncError(errorMessage);
      }
    };

    setupRealtimeSync();

    return () => {
      if (unsubscribe) {
        unsubscribe();
      }
    };
  }, [isInitialized, selectedCity]);

  // Funcion para obtener todos los roles de un email
  const getUserRoles = (email: string) => {
    const roles: Array<{role: 'customer' | 'vendor' | 'driver' | 'admin', data: User | Vendor | DeliveryDriver | Admin}> = [];
    const lowerEmail = email?.toLowerCase();

    if (!lowerEmail) return roles;

    // Buscar en admins primero
    const admin = (data.admins || []).find(c => c.email?.toLowerCase() === lowerEmail);
    if (admin) {
      roles.push({ role: 'admin', data: admin });
      // Si es admin, retornar solo ese rol (admins no tienen otros roles)
      return roles;
    }

    // Buscar en vendors (solo activos)
    const vendor = (data.vendors || []).find(v =>
      v.email?.toLowerCase() === lowerEmail &&
      v.status === 'Activo'
    );
    if (vendor) {
      roles.push({ role: 'vendor', data: vendor });
    }

    // Buscar en drivers (solo activos)
    const driver = (data.drivers || []).find(d =>
      d.email?.toLowerCase() === lowerEmail &&
      d.status === 'Activo'
    );
    if (driver) {
      roles.push({ role: 'driver', data: driver });
    }

    // Buscar en customers - siempre agregar si existe en la tabla users
    // Todos los usuarios registrados pueden actuar como clientes
    const customer = (data.users || []).find(u => u.email?.toLowerCase() === lowerEmail);
    if (customer) {
      roles.push({ role: 'customer', data: customer });
    }

    return roles;
  };

  // Sincronizar usuario de NextAuth con currentUser
  useEffect(() => {
    if (status === 'loading') return;

    if (session?.user) {
      const email = session.user.email?.toLowerCase();
      const role = session.user.role || 'customer';

      // Buscar datos del usuario segun su rol actual
      const userRoles = getUserRoles(email || '');
      const roleData = userRoles.find(r => r.role === role);

      if (roleData) {
        setCurrentUser({ ...roleData.data, role: roleData.role });
      } else if (userRoles.length > 0) {
        // Si no tiene el rol de la sesion, usar el primero disponible
        setCurrentUser({ ...userRoles[0].data, role: userRoles[0].role });
      } else {
        // Usuario autenticado pero sin datos en la BD local
        // Crear un usuario temporal basado en la sesion
        const tempUser: User = {
          id: session.user.id,
          name: session.user.name || 'Usuario',
          email: session.user.email || '',
          phone: '',
          address: '',
          profileImageUrl: session.user.image,
        };
        setCurrentUser({ ...tempUser, role: 'customer' });
      }
    } else {
      setCurrentUser(null);
    }
  }, [session, status, data.admins, data.vendors, data.drivers, data.users]);

  // --- Auth Functions ---
  const login = (email: string): LoggedInUser | null => {
    const userRoles = getUserRoles(email?.toLowerCase() || '');
    if (userRoles.length > 0) {
      const user = { ...userRoles[0].data, role: userRoles[0].role };
      setCurrentUser(user);
      return user;
    }
    return null;
  };

  const logout = async () => {
    try {
      await nextAuthSignOut({ redirect: false });
      setCurrentUser(null);
      setAvailableRoles([]);
    } catch (error) {
      console.error('Error al cerrar sesion:', error);
      setCurrentUser(null);
      setAvailableRoles([]);
    }
  };

  // Funcion para cambiar de rol
  const switchRole = (role: 'customer' | 'vendor' | 'driver' | 'admin'): boolean => {
    if (!currentUser) return false;

    const userRoles = getUserRoles(currentUser.email);
    const targetRole = userRoles.find(r => r.role === role);

    if (targetRole) {
      const newUser: LoggedInUser = { ...targetRole.data, role: targetRole.role };
      setCurrentUser(newUser);

      // Actualizar sesion de NextAuth con nuevo rol
      updateSession({ role });

      // Guardar en backend
      fetch('/api/auth/switch-role', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ role }),
      }).catch(err => console.error('Error guardando rol:', err));

      return true;
    }

    return false;
  };

  // Actualizar roles disponibles cuando cambia el usuario
  useEffect(() => {
    if (currentUser) {
      const roles = getUserRoles(currentUser.email);
      setAvailableRoles(roles.map(r => r.role));
    } else {
      setAvailableRoles([]);
    }
  }, [currentUser, data.users, data.vendors, data.drivers, data.admins]);

  // --- Data Manipulation Functions ---

  const saveOrder = async (order: Order) => {
    setData(prevData => {
      const index = prevData.orders.findIndex(o => o.id === order.id);
      const newOrders = [...prevData.orders];
      if (index > -1) {
        newOrders[index] = order;
      } else {
        newOrders.push(order);
      }
      return { ...prevData, orders: newOrders };
    });

    if (isInitialized) {
      const result = await saveOrderToBackend(order);
      if (!result.success) {
        console.error(`Error al guardar orden ${order.id}:`, result.error);
      }
    }
  };

  const addOrder = async (newOrderData: Omit<Order, 'id' | 'date' | 'status' | 'verificationCode'>): Promise<Order> => {
    const verificationCode = Math.floor(1000 + Math.random() * 9000).toString();
    const newOrder: Order = {
      ...newOrderData,
      id: `ORD-${Date.now()}`,
      date: new Date().toISOString(),
      status: 'Procesando',
      verificationCode,
    };
    newOrder.items.forEach(item => item.payoutStatus = 'pending');

    setData(prevData => ({
      ...prevData,
      orders: [newOrder, ...prevData.orders]
    }));

    if (isInitialized) {
      const result = await saveOrderToBackend(newOrder);
      if (!result.success) {
        console.error(`Error al guardar orden ${newOrder.id}:`, result.error);
      }
    }

    return newOrder;
  };

  const deleteOrder = async (orderId: string) => {
    const order = (data.orders || []).find(o => o.id === orderId);
    if (!order) return;

    setData(prevData => ({
      ...prevData,
      orders: prevData.orders.filter(o => o.id !== orderId)
    }));

    if (isInitialized) {
      const result = await deleteOrderFromBackend(orderId);
      if (!result.success) {
        console.error(`Error al eliminar orden ${orderId}:`, result.error);
      }
    }
  };

  const saveVendor = async (vendor: Vendor) => {
    setData(prevData => {
      const newVendors = [...prevData.vendors];
      const index = newVendors.findIndex(v => v.id === vendor.id);
      if (index > -1) {
        newVendors[index] = vendor;
      } else {
        newVendors.push(vendor);
      }

      // Auto-crear customer si no existe
      const newUsers = [...prevData.users];
      const userExists = (newUsers || []).find(u => u.email?.toLowerCase() === vendor.email?.toLowerCase());

      if (!userExists) {
        const newCustomer: User = {
          id: vendor.id,
          name: vendor.name,
          email: vendor.email,
          phone: vendor.phone,
          address: vendor.address || '',
          profileImageUrl: vendor.imageUrl || undefined,
        };
        newUsers.push(newCustomer);
      }

      return { ...prevData, vendors: newVendors, users: newUsers };
    });

    if (isInitialized) {
      const result = await saveVendorToBackend(vendor);
      if (!result.success) {
        console.error('Error al guardar vendor:', result.error);
      }

      // Si se creo usuario automaticamente, guardarlo
      const userExists = (data.users || []).find(u => u.email?.toLowerCase() === vendor.email?.toLowerCase());
      if (!userExists) {
        const newCustomer: User = {
          id: vendor.id,
          name: vendor.name,
          email: vendor.email,
          phone: vendor.phone,
          address: vendor.address || '',
          profileImageUrl: vendor.imageUrl || undefined,
        };
        await saveCustomerToBackend(newCustomer);
      }
    }
  };

  const deleteVendor = async (vendorId: string) => {
    const vendor = (data.vendors || []).find(v => v.id === vendorId);
    if (!vendor) return;

    setData(prevData => ({
      ...prevData,
      vendors: prevData.vendors.filter(v => v.id !== vendorId)
    }));

    if (isInitialized) {
      const result = await deleteVendorFromBackend(vendorId);
      if (!result.success) {
        console.error(`Error al eliminar vendor ${vendorId}:`, result.error);
      }
    }
  };

  const saveCategory = async (category: Category) => {
    setData(prevData => {
      const index = prevData.categories.findIndex(c => c.id === category.id);
      const newCategories = [...prevData.categories];
      if (index > -1) {
        newCategories[index] = category;
      } else {
        newCategories.push(category);
      }
      return { ...prevData, categories: newCategories };
    });

    if (isInitialized) {
      const result = await saveCategoryToBackend(category);
      if (!result.success) {
        console.error('Error al guardar categoria:', result.error);
      }
    }
  };

  const deleteCategory = async (categoryId: string) => {
    setData(prevData => ({
      ...prevData,
      categories: prevData.categories.filter(c => c.id !== categoryId)
    }));

    if (isInitialized) {
      const result = await deleteCategoryFromBackend(categoryId);
      if (!result.success) {
        console.error(`Error al eliminar categoria ${categoryId}:`, result.error);
      }
    }
  };

  const saveCity = async (city: City) => {
    setData(prevData => {
      const index = prevData.cities.findIndex(c => c.id === city.id);
      const newCities = [...prevData.cities];
      if (index > -1) {
        newCities[index] = city;
      } else {
        newCities.push(city);
      }
      return { ...prevData, cities: newCities };
    });

    if (isInitialized) {
      const result = await saveCityToBackend(city);
      if (!result.success) {
        console.error('Error al guardar ciudad:', result.error);
      }
    }
  };

  const deleteCity = async (cityId: string) => {
    setData(prevData => ({
      ...prevData,
      cities: prevData.cities.filter(c => c.id !== cityId)
    }));

    if (isInitialized) {
      const result = await deleteCityFromBackend(cityId);
      if (!result.success) {
        console.error(`Error al eliminar ciudad ${cityId}:`, result.error);
      }
    }
  };

  const saveDeliveryZone = async (zone: DeliveryZone) => {
    setData(prevData => {
      const index = prevData.deliveryZones.findIndex(z => z.id === zone.id);
      const newZones = [...prevData.deliveryZones];
      if (index > -1) {
        newZones[index] = zone;
      } else {
        newZones.push(zone);
      }
      return { ...prevData, deliveryZones: newZones };
    });

    if (isInitialized) {
      const result = await saveDeliveryZoneToBackend(zone);
      if (!result.success) {
        console.error('Error al guardar zona de entrega:', result.error);
      }
    }
  };

  const deleteDeliveryZone = async (zoneId: string) => {
    setData(prevData => ({
      ...prevData,
      deliveryZones: prevData.deliveryZones.filter(z => z.id !== zoneId)
    }));

    if (isInitialized) {
      const result = await deleteDeliveryZoneFromBackend(zoneId);
      if (!result.success) {
        console.error(`Error al eliminar zona de entrega ${zoneId}:`, result.error);
      }
    }
  };

  const saveDriver = async (driver: DeliveryDriver) => {
    setData(prevData => {
      const index = prevData.drivers.findIndex(d => d.id === driver.id);
      const newDrivers = [...prevData.drivers];
      if (index > -1) {
        newDrivers[index] = driver;
      } else {
        newDrivers.push(driver);
      }

      // Auto-crear customer si no existe
      const newUsers = [...prevData.users];
      const userExists = newUsers.find(u => u.email?.toLowerCase() === driver.email?.toLowerCase());

      if (!userExists) {
        const newCustomer: User = {
          id: driver.id,
          name: driver.name,
          email: driver.email,
          phone: driver.phone,
          address: '',
          profileImageUrl: driver.profileImageUrl || undefined,
        };
        newUsers.push(newCustomer);
      }

      return { ...prevData, drivers: newDrivers, users: newUsers };
    });

    if (isInitialized) {
      const result = await saveDriverToBackend(driver);
      if (!result.success) {
        console.error('Error al guardar driver:', result.error);
      }

      // Si se creo usuario automaticamente, guardarlo
      const userExists = (data.users || []).find(u => u.email?.toLowerCase() === driver.email?.toLowerCase());
      if (!userExists) {
        const newCustomer: User = {
          id: driver.id,
          name: driver.name,
          email: driver.email,
          phone: driver.phone,
          address: '',
          profileImageUrl: driver.profileImageUrl || undefined,
        };
        await saveCustomerToBackend(newCustomer);
      }
    }
  };

  const deleteDriver = async (driverId: string) => {
    setData(prevData => ({
      ...prevData,
      drivers: prevData.drivers.filter(d => d.id !== driverId)
    }));

    if (isInitialized) {
      const result = await deleteDriverFromBackend(driverId);
      if (!result.success) {
        console.error(`Error al eliminar driver ${driverId}:`, result.error);
      }
    }
  };

  const saveUser = async (user: User) => {
    setData(prevData => {
      const index = prevData.users.findIndex(u => u.id === user.id);
      const newUsers = [...prevData.users];
      if (index > -1) {
        newUsers[index] = user;
      } else {
        newUsers.push(user);
      }
      return { ...prevData, users: newUsers };
    });

    if (isInitialized) {
      const result = await saveCustomerToBackend(user);
      if (!result.success) {
        console.error('Error al guardar usuario:', result.error);
      }
    }
  };

  const deleteUser = async (userId: string) => {
    setData(prevData => ({
      ...prevData,
      users: prevData.users.filter(u => u.id !== userId)
    }));

    if (isInitialized) {
      const result = await deleteCustomerFromBackend(userId);
      if (!result.success) {
        console.error(`Error al eliminar usuario ${userId}:`, result.error);
      }
    }
  };

  const saveAdmin = async (admin: Admin) => {
    setData(prevData => {
      const index = prevData.admins.findIndex(c => c.id === admin.id);
      const newAdmins = [...prevData.admins];
      if (index > -1) {
        newAdmins[index] = admin;
      } else {
        newAdmins.push(admin);
      }
      return { ...prevData, admins: newAdmins };
    });

    if (isInitialized) {
      const result = await saveAdminToBackend(admin);
      if (!result.success) {
        console.error('Error al guardar admin:', result.error);
      }
    }
  };

  const deleteAdmin = async (adminId: string) => {
    setData(prevData => ({
      ...prevData,
      admins: prevData.admins.filter(c => c.id !== adminId)
    }));

    if (isInitialized) {
      const result = await deleteAdminFromBackend(adminId);
      if (!result.success) {
        console.error(`Error al eliminar admin ${adminId}:`, result.error);
      }
    }
  };

  const saveSettings = async (settings: AppSettings) => {
    setData(prevData => ({ ...prevData, appSettings: settings }));

    if (isInitialized) {
      const result = await saveSettingsToBackend(settings);
      if (!result.success) {
        console.error('Error al guardar configuracion:', result.error);
      }
    }
  };

  const addMessage = async (newMessage: Omit<Message, 'id' | 'timestamp'>) => {
    const message: Message = {
      ...newMessage,
      id: `msg${Date.now()}`,
      timestamp: new Date().toISOString()
    };

    setData(prevData => ({
      ...prevData,
      messages: [...prevData.messages, message]
    }));

    if (isInitialized) {
      const result = await saveMessageToBackend(message);
      if (!result.success) {
        console.error(`Error al guardar mensaje ${message.id}:`, result.error);
      }
    }
  };

  const markVendorPayoutAsPaid = async (vendorId: string) => {
    const vendor = (data.vendors || []).find(v => v.id === vendorId);
    if (!vendor) return;

    setData(prevData => {
      const newOrders = prevData.orders.map(order => {
        const newItems = order.items.map(item => {
          if (item.vendor === vendor.name && item.payoutStatus === 'pending') {
            return { ...item, payoutStatus: 'paid' as const };
          }
          return item;
        });
        return { ...order, items: newItems };
      });
      return { ...prevData, orders: newOrders };
    });

    if (isInitialized) {
      const ordersToUpdate = data.orders.filter(order =>
        order.items.some(item => item.vendor === vendor.name && item.payoutStatus === 'pending')
      );

      for (const order of ordersToUpdate) {
        const updatedOrder = {
          ...order,
          items: order.items.map(item => {
            if (item.vendor === vendor.name && item.payoutStatus === 'pending') {
              return { ...item, payoutStatus: 'paid' as const };
            }
            return item;
          })
        };
        await saveOrderToBackend(updatedOrder);
      }
    }
  };

  const updateDriverDebt = async (driverId: string, type: 'credit' | 'debit', amount: number, description: string) => {
    const driver = (data.drivers || []).find(d => d.id === driverId);
    if (!driver) return;

    const newDebt = type === 'debit' ? driver.debt + amount : driver.debt - amount;
    const newTransaction: DebtTransaction = {
      id: `tx${Date.now()}`,
      date: new Date().toISOString(),
      type,
      amount,
      description,
    };

    const updatedDriver = {
      ...driver,
      debt: Math.max(0, newDebt),
      debtTransactions: [...(driver.debtTransactions || []), newTransaction]
    };

    setData(prevData => {
      const newDrivers = [...prevData.drivers];
      const driverIndex = newDrivers.findIndex(d => d.id === driverId);
      if (driverIndex > -1) {
        newDrivers[driverIndex] = updatedDriver;
        return { ...prevData, drivers: newDrivers };
      }
      return prevData;
    });

    if (isInitialized) {
      await saveDriverToBackend(updatedDriver);
    }
  };

  const addDebtTransaction = (driverId: string, amount: number, description: string) => {
    updateDriverDebt(driverId, 'debit', amount, description);
  };

  const addCreditTransaction = (driverId: string, amount: number, description: string) => {
    updateDriverDebt(driverId, 'credit', amount, description);
  };

  const clearDriverDebt = async (driverId: string) => {
    const driver = (data.drivers || []).find(d => d.id === driverId);
    if (!driver) return;

    const updatedDriver = { ...driver, debt: 0 };

    setData(prevData => {
      const newDrivers = [...prevData.drivers];
      const driverIndex = newDrivers.findIndex(d => d.id === driverId);
      if (driverIndex > -1) {
        newDrivers[driverIndex] = updatedDriver;
        return { ...prevData, drivers: newDrivers };
      }
      return prevData;
    });

    if (isInitialized) {
      await saveDriverToBackend(updatedDriver);
    }
  };

  const markDriverPayoutAsPaid = async (driverId: string, netBalance: number, ordersToMarkAsPaid: Order[]) => {
    const driver = (data.drivers || []).find(d => d.id === driverId);
    if (!driver) return;

    const transactionAmount = Math.abs(netBalance);
    const transactionDescription = netBalance >= 0
      ? `Pago de liquidacion por ${data.appSettings.currencySymbol}${transactionAmount.toFixed(2)}`
      : `Recepcion de pago de deuda por ${data.appSettings.currencySymbol}${transactionAmount.toFixed(2)}`;

    const newTransaction: DebtTransaction = {
      id: `tx${Date.now()}`,
      date: new Date().toISOString(),
      type: 'credit',
      amount: transactionAmount,
      description: transactionDescription,
    };

    const updatedDriver = {
      ...driver,
      debt: 0,
      debtTransactions: [...(driver.debtTransactions || []), newTransaction]
    };

    setData(prevData => {
      const newDrivers = [...prevData.drivers];
      const driverIndex = newDrivers.findIndex(d => d.id === driverId);
      if (driverIndex > -1) {
        newDrivers[driverIndex] = updatedDriver;
      }

      const orderIdsToMark = new Set(ordersToMarkAsPaid.map(o => o.id));
      const newOrders = prevData.orders.map(order => {
        if (orderIdsToMark.has(order.id)) {
          return { ...order, driverPayoutStatus: 'paid' as const };
        }
        return order;
      });

      return { ...prevData, drivers: newDrivers, orders: newOrders };
    });

    if (isInitialized) {
      await saveDriverToBackend(updatedDriver);

      for (const order of ordersToMarkAsPaid) {
        const updatedOrder = { ...order, driverPayoutStatus: 'paid' as const };
        await saveOrderToBackend(updatedOrder);
      }
    }
  };

  const addFavor = async (favorData: Omit<Favor, 'id' | 'date'>): Promise<Favor> => {
    const newFavor: Favor = {
      ...favorData,
      id: `FAV-${Date.now()}`,
      date: new Date().toISOString(),
    };

    setData(prevData => ({
      ...prevData,
      favors: [newFavor, ...prevData.favors]
    }));

    if (isInitialized) {
      await saveFavorToBackend(newFavor);
    }

    return newFavor;
  };

  const saveFavor = async (favor: Favor) => {
    setData(prevData => {
      const index = prevData.favors.findIndex(f => f.id === favor.id);
      const newFavors = [...prevData.favors];
      if (index > -1) {
        newFavors[index] = favor;
      } else {
        newFavors.push(favor);
      }
      return { ...prevData, favors: newFavors };
    });

    if (isInitialized) {
      await saveFavorToBackend(favor);
    }
  };

  const deleteFavor = async (favorId: string) => {
    setData(prevData => ({
      ...prevData,
      favors: prevData.favors.filter(f => f.id !== favorId)
    }));

    if (isInitialized) {
      await deleteFavorFromBackend(favorId);
    }
  };

  // --- Data Getter Functions ---
  const getAllProducts = () => (data.vendors || []).flatMap(v => v.products || []).filter(p => p != null);
  const getVendorById = (id: string) => (data.vendors || []).find(v => v.id === id);
  const getVendorByOwnerId = (ownerId: string) => (data.vendors || []).find(v => v.ownerId === ownerId);
  const getDriverById = (id: string) => (data.drivers || []).find(d => d.id === id);
  const getDriverByUserId = (userId: string) => (data.drivers || []).find(d => d.userId === userId);
  const getProductById = (id: string) => getAllProducts().find(p => p?.id === id);
  const getMessagesForOrder = (orderId: string) => (data.messages || []).filter(m => m.orderId === orderId).sort((a, b) => new Date(a.timestamp).getTime() - new Date(b.timestamp).getTime());

  const value: AppDataContextValue = {
    ...data,
    currentUser,
    availableRoles,
    selectedCity,
    setSelectedCity,
    login,
    logout,
    getUserRoles,
    switchRole,
    syncStatus,
    lastSyncError,
    saveVendor,
    deleteVendor,
    saveCategory,
    deleteCategory,
    saveDriver,
    deleteDriver,
    saveUser,
    deleteUser,
    saveSettings,
    addMessage,
    saveOrder,
    addOrder,
    deleteOrder,
    getAllProducts,
    getVendorById,
    getVendorByOwnerId,
    getDriverById,
    getDriverByUserId,
    getProductById,
    getMessagesForOrder,
    saveCity,
    deleteCity,
    saveDeliveryZone,
    deleteDeliveryZone,
    markVendorPayoutAsPaid,
    addCreditTransaction,
    addDebtTransaction,
    clearDriverDebt,
    saveAdmin,
    deleteAdmin,
    markDriverPayoutAsPaid,
    addFavor,
    saveFavor,
    deleteFavor,
  };

  // Render children solo despues de inicializacion
  if (!isInitialized) {
    return null;
  }

  return (
    <AppDataContext.Provider value={value}>
      {children}
    </AppDataContext.Provider>
  );
};

// --- Custom Hook ---
export const useAppData = () => {
  const context = useContext(AppDataContext);
  if (context === undefined) {
    throw new Error('useAppData must be used within an AppDataProvider');
  }
  return context;
};
