

"use client";

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { AppData, Vendor, Category, DeliveryDriver, User, AppSettings, Message, Product, Order, City, OrderItem, DrinkOption, Admin, DebtTransaction, Coordinate, DeliveryZone, Favor, UserSettings } from '@/lib/placeholder-data';
import { initializeFirebase } from '@/lib/firebase';
import { saveAppDataToFirestore, loadAppDataFromFirestore, isFirestoreAvailable, COLLECTIONS } from '@/lib/firestore-service';
import { getAuth, onAuthStateChanged, User as FirebaseUser } from 'firebase/auth';
import { doc, getDoc, setDoc, getFirestore } from 'firebase/firestore';

type LoggedInUser = (User | Vendor | DeliveryDriver | Admin) & { role: 'customer' | 'vendor' | 'driver' | 'admin' };

// Estado inicial vacío - TODO se carga desde Firebase
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
    firebaseConfig: {
      apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY || "",
      authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN || "",
      projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID || "",
      storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET || "",
    },
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

// Función para inicializar Firebase si está configurado
function tryInitializeFirebase(settings: AppSettings): boolean {
    if (settings.firebaseConfig?.apiKey && settings.firebaseConfig?.projectId) {
        try {
            initializeFirebase({
                apiKey: settings.firebaseConfig.apiKey,
                authDomain: settings.firebaseConfig.authDomain || `${settings.firebaseConfig.projectId}.firebaseapp.com`,
                projectId: settings.firebaseConfig.projectId,
                storageBucket: settings.firebaseConfig.storageBucket || `${settings.firebaseConfig.projectId}.appspot.com`
            });
            return true;
        } catch (error) {
            console.error('Error al inicializar Firebase:', error);
            return false;
        }
    }
    return false;
}

// 🔥 FIREBASE HELPER: Leer configuración de usuario desde Firestore
async function getUserSettings(email: string): Promise<string | null> {
  try {
    const db = getFirestore();
    const docRef = doc(db, COLLECTIONS.USER_SETTINGS, email);
    const docSnap = await getDoc(docRef);

    if (docSnap.exists()) {
      const data = docSnap.data() as UserSettings;
      return data.selectedRole;
    }
    return null; // Usuario nuevo, no tiene configuración guardada
  } catch (error) {
    console.error('Error al leer userSettings desde Firebase:', error);
    return null;
  }
}

// 🔥 FIREBASE HELPER: Guardar configuración de usuario en Firestore
async function updateUserSettings(email: string, role: 'customer' | 'vendor' | 'driver' | 'admin'): Promise<void> {
  try {
    const db = getFirestore();
    const docRef = doc(db, COLLECTIONS.USER_SETTINGS, email);

    const settings: UserSettings = {
      email,
      selectedRole: role,
      lastUpdated: new Date().toISOString()
    };

    await setDoc(docRef, settings);
    console.log(`✅ Rol '${role}' guardado en Firebase para ${email}`);
  } catch (error) {
    console.error('Error al guardar userSettings en Firebase:', error);
    // No lanzar error para no bloquear la aplicación
  }
}

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
  addOrder: (newOrderData: Omit<Order, 'id' | 'date' | 'status' | 'verificationCode'>) => Order;
  deleteOrder: (orderId: string) => void;
  getAllProducts: () => Product[];
  getVendorById: (id: string) => Vendor | undefined;
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
  addFavor: (favorData: Omit<Favor, 'id' | 'date'>) => Favor;
  saveFavor: (favor: Favor) => void;
  deleteFavor: (favorId: string) => void;
}

const AppDataContext = createContext<AppDataContextValue | undefined>(undefined);

// --- Provider Component ---
export const AppDataProvider = ({ children }: { children: ReactNode }) => {
    const [data, setData] = useState<AppData>(emptyAppData);
    const [isInitialized, setIsInitialized] = useState(false);
    const [currentUser, setCurrentUser] = useState<LoggedInUser | null>(null);
    const [availableRoles, setAvailableRoles] = useState<string[]>([]);
    const [selectedCity, _setSelectedCity] = useState<string | null>(null);
    const [useFirestore, setUseFirestore] = useState(true); // Siempre usar Firestore
    const [syncStatus, setSyncStatus] = useState<'connected' | 'disconnected' | 'error' | 'reconnecting'>('disconnected');
    const [lastSyncError, setLastSyncError] = useState<string | null>(null);

    const setSelectedCity = (cityName: string | null) => {
        _setSelectedCity(cityName);
    };

    // 🔥 SINCRONIZACIÓN EN TIEMPO REAL con Firebase Firestore
    useEffect(() => {
        const initializeData = async () => {
            // Inicializar Firebase con la configuración del estado inicial
            const firebaseInitialized = tryInitializeFirebase(emptyAppData.appSettings);

            if (!firebaseInitialized) {
                console.error('❌ Error: No se pudo inicializar Firebase');
                setIsInitialized(true);
                return;
            }

            if (!isFirestoreAvailable()) {
                console.error('❌ Error: Firestore no está disponible');
                setIsInitialized(true);
                return;
            }

            // ✅ CARGAR DATOS INICIALES (una sola vez)
            const result = await loadAppDataFromFirestore(emptyAppData);

            if (result.success && result.data) {
                const loadedData = { ...result.data, currentUser: undefined } as AppData;
                setData(loadedData);

                // Detectar ciudad automáticamente
                if (loadedData.cities.length > 0) {
                    _setSelectedCity(loadedData.cities[0].name);
                }

                setIsInitialized(true);
            } else {
                console.error('❌ Error al cargar datos desde Firestore:', result.error);
                console.error('⚠️ La aplicación requiere datos en Firebase para funcionar');
                setIsInitialized(true);
            }
        };

        initializeData();
    }, []);

    // 🔥 LISTENER EN TIEMPO REAL con manejo de errores y reintentos automáticos
    useEffect(() => {
        // No iniciar listener hasta que los datos iniciales estén cargados
        if (!isInitialized || !useFirestore) return;

        setSyncStatus('reconnecting');

        // Importar la función de suscripción de forma dinámica
        let unsubscribe: (() => void) | null = null;
        let retryTimeout: NodeJS.Timeout | null = null;

        const MAX_RETRIES = 5;
        const RETRY_DELAYS = [1000, 2000, 5000, 10000, 30000]; // Backoff exponencial

        const setupRealtimeSync = async (attemptNumber: number = 0) => {
            try {
                const { subscribeToAppData } = await import('@/lib/firestore-service');

                // Establecer listener en tiempo real con manejo de errores
                unsubscribe = subscribeToAppData(
                    (updatedData) => {
                        // ✅ VALIDAR datos antes de actualizar para evitar undefined
                        // Merge solo propiedades que existan en updatedData y tengan valores válidos
                        setData(prevData => {
                          const mergedData = {
                            ...prevData, // Mantener datos actuales
                            // Solo sobrescribir si existen en updatedData y no son undefined
                            ...(updatedData.vendors !== undefined && { vendors: updatedData.vendors }),
                            ...(updatedData.users !== undefined && { users: updatedData.users }),
                            ...(updatedData.drivers !== undefined && { drivers: updatedData.drivers }),
                            ...(updatedData.orders !== undefined && { orders: updatedData.orders }),
                            ...(updatedData.admins !== undefined && { admins: updatedData.admins }),
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
                            ...(updatedData.adminEmails !== undefined && { adminEmails: updatedData.adminEmails }),
                            currentUser: undefined // currentUser se maneja separadamente
                          } as AppData;

                          return mergedData;
                        });

                        // Actualizar ciudad seleccionada si cambió
                        if (updatedData.cities?.length > 0 && !selectedCity) {
                            _setSelectedCity(updatedData.cities[0].name);
                        }

                        // Marcar como conectado y limpiar errores
                        setSyncStatus('connected');
                        setLastSyncError(null);
                    }
                );

                setSyncStatus('connected');
                setLastSyncError(null);

            } catch (error) {
                const errorMessage = error instanceof Error ? error.message : 'Error desconocido';
                console.error(`❌ Error al establecer listener (intento ${attemptNumber + 1}/${MAX_RETRIES + 1}):`, errorMessage);

                setSyncStatus('error');
                setLastSyncError(errorMessage);

                // Reintentar con backoff exponencial
                if (attemptNumber < MAX_RETRIES) {
                    const delay = RETRY_DELAYS[attemptNumber] || 30000;
                    setSyncStatus('reconnecting');

                    retryTimeout = setTimeout(() => {
                        setupRealtimeSync(attemptNumber + 1);
                    }, delay);
                } else {
                    console.error('❌ Máximo de reintentos alcanzado. La sincronización en tiempo real no está disponible.');
                    setSyncStatus('error');
                }
            }
        };

        setupRealtimeSync(0);

        // 🧹 CLEANUP: Desuscribirse cuando el componente se desmonte
        return () => {
            if (unsubscribe) {
                unsubscribe();
            }
            if (retryTimeout) {
                clearTimeout(retryTimeout);
            }
        };
    }, [isInitialized, useFirestore, selectedCity]);

    // Función para obtener todos los roles de un email
    const getUserRoles = (email: string) => {
        const roles: Array<{role: 'customer' | 'vendor' | 'driver' | 'admin', data: User | Vendor | DeliveryDriver | Admin}> = [];
        const lowerEmail = email.toLowerCase();

        // Buscar en customers
        const customer = data.users.find(u => u.email.toLowerCase() === lowerEmail);
        if (customer) {
            roles.push({ role: 'customer', data: customer });
        }

        // Buscar en vendors
        const vendor = data.vendors.find(v => v.email.toLowerCase() === lowerEmail);
        if (vendor) {
            roles.push({ role: 'vendor', data: vendor });
        }

        // Buscar en drivers
        const driver = data.drivers.find(d => d.email.toLowerCase() === lowerEmail);
        if (driver) {
            roles.push({ role: 'driver', data: driver });
        }

        // Buscar en admins
        const admin = data.admins.find(c => c.email.toLowerCase() === lowerEmail);
        if (admin) {
            roles.push({ role: 'admin', data: admin });
        }

        return roles;
    };

    // Función helper para buscar usuario en los datos locales
    // 🔥 CORRECCIÓN: Ahora lee el rol guardado desde FIREBASE (no localStorage)
    const findUserInData = async (email: string | null, phoneNumber: string | null): Promise<LoggedInUser | null> => {
        if (!email && !phoneNumber) return null;

        // 1. Obtener TODOS los roles del usuario
        const identifier = email || phoneNumber || '';
        const allRoles = getUserRoles(identifier);

        // Si no tiene ningún rol registrado, retornar null
        if (allRoles.length === 0) return null;

        // 2. Leer rol guardado desde FIREBASE (persistencia cloud)
        const savedRole = email ? await getUserSettings(email) : null;

        // 3. Si hay rol guardado Y es válido para este usuario, usarlo
        if (savedRole && allRoles.find(r => r.role === savedRole)) {
            const roleData = allRoles.find(r => r.role === savedRole);
            if (roleData) {
                return { ...roleData.data, role: roleData.role };
            }
        }

        // 4. Si no hay rol guardado (nuevo usuario o rol inválido), usar el primer rol disponible
        // Esto usualmente será 'customer' para usuarios nuevos, pero respetará otros roles después
        return { ...allRoles[0].data, role: allRoles[0].role };
    };

    // Sincronizar usuario autenticado de Firebase Auth con currentUser
    useEffect(() => {
        if (!useFirestore) return;

        const auth = getAuth();
        const unsubscribe = onAuthStateChanged(auth, async (firebaseUser: FirebaseUser | null) => {
            if (firebaseUser) {
                // Usuario autenticado en Firebase
                // Buscar usuario en la base de datos local por email O teléfono
                const email = firebaseUser.email?.toLowerCase();
                const phoneNumber = firebaseUser.phoneNumber;

                // 🔥 CORRECCIÓN: await findUserInData porque ahora lee desde Firebase
                const foundUser = await findUserInData(email || null, phoneNumber);

                // 🔥 AUTO-CREACIÓN AUTOMÁTICA: Si no se encuentra el usuario, crearlo como customer por defecto
                if (!foundUser && (email || phoneNumber)) {
                    try {
                        // Crear nuevo usuario customer automáticamente
                        const newUser: User = {
                            id: firebaseUser.uid, // Usar UID de Firebase como ID
                            name: firebaseUser.displayName || email || phoneNumber || 'Usuario',
                            email: email || '',
                            phone: phoneNumber || '',
                            address: '',
                            profileImageUrl: firebaseUser.photoURL || undefined,
                        };

                        // Guardar inmediatamente en Firestore
                        await saveUser(newUser);

                        // Establecer como usuario actual
                        setCurrentUser({ ...newUser, role: 'customer' });

                        return;
                    } catch (error) {
                        console.error('❌ Error al crear usuario automáticamente:', error);
                        // Si falla la creación automática, no establecer currentUser
                        // El flujo redirigirá a /select-role
                        setCurrentUser(null);
                        return;
                    }
                }

                // 🔥 CORRECCIÓN: findUserInData() YA lee el rol desde Firebase internamente
                // No es necesario código adicional de localStorage aquí
                if (foundUser) {
                    setCurrentUser(foundUser);
                } else {
                    setCurrentUser(null);
                }
            } else {
                // Usuario no autenticado
                setCurrentUser(null);
            }
        });

        return () => unsubscribe();
    }, [useFirestore, data.admins, data.vendors, data.drivers, data.users]);

    // SOLUCIÓN CRÍTICA: Re-evaluar currentUser cuando los datos cambien
    // Esto resuelve el problema de timing donde onAuthStateChanged dispara antes de que los datos se carguen
    useEffect(() => {
        if (!useFirestore) return;

        const auth = getAuth();
        const firebaseUser = auth.currentUser;

        // 🔥 CORRECCIÓN: función async interna para manejar await de findUserInData
        const checkAndSetUser = async () => {
            // Si hay un usuario autenticado en Firebase pero currentUser no tiene role
            if (firebaseUser && !currentUser) {
                const email = firebaseUser.email?.toLowerCase();
                const phoneNumber = firebaseUser.phoneNumber;

                const foundUser = await findUserInData(email || null, phoneNumber);

                if (foundUser) {
                    setCurrentUser(foundUser);
                }
            }
        };

        checkAndSetUser();
    }, [useFirestore, data.admins, data.vendors, data.drivers, data.users, currentUser]);

    // --- Auth Functions ---
    const login = async (email: string): Promise<LoggedInUser | null> => {
        // Reutilizar findUserInData() para eliminar duplicación
        const user = await findUserInData(email.toLowerCase(), null);
        if (user) {
            setCurrentUser(user);
        }
        return user;
    };

    const logout = async () => {
        try {
            // Cerrar sesión en Firebase Authentication
            const { signOutUser } = await import('@/lib/firebase');
            await signOutUser();

            // Limpiar estado local
            setCurrentUser(null);
            setAvailableRoles([]);
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
            // Aunque falle Firebase, igual limpiamos el estado local
            setCurrentUser(null);
            setAvailableRoles([]);
        }
    };

    // Función para cambiar de rol (solo si el usuario tiene ese rol)
    const switchRole = (role: 'customer' | 'vendor' | 'driver' | 'admin'): boolean => {
        if (!currentUser) return false;

        const userRoles = getUserRoles(currentUser.email);
        const targetRole = userRoles.find(r => r.role === role);

        if (targetRole) {
            const newUser: LoggedInUser = { ...targetRole.data, role: targetRole.role };
            setCurrentUser(newUser);

            // 🔥 CORRECCIÓN: Guardar rol seleccionado en FIREBASE (no localStorage) para persistencia cloud
            updateUserSettings(currentUser.email, role).catch(err => {
                console.error('Error al guardar rol en Firebase:', err);
            });

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

    // 🔥 SINCRONIZACIÓN AUTOMÁTICA CON FIREBASE (BACKUP)
    // Las operaciones críticas (saveVendor, saveDriver, saveUser) guardan INMEDIATAMENTE
    // Este auto-sync sirve como BACKUP para otros cambios menores
    // ⚠️ SOLO SE EJECUTA PARA ADMINS (para evitar errores de permisos en otros usuarios)
    useEffect(() => {
        // No sincronizar si no está inicializado o no usa Firestore
        if (!isInitialized || !useFirestore) return;

        // ✅ SOLO admins pueden ejecutar el auto-sync (tienen permisos de escritura)
        if (currentUser?.role !== 'admin') return;

        // Debounce: esperar 30 segundos después del último cambio antes de guardar
        // Aumentado de 2s a 30s porque ahora las operaciones críticas guardan inmediatamente
        const timeoutId = setTimeout(async () => {
            const { currentUser, ...dataWithoutUser } = data as any;
            const result = await saveAppDataToFirestore(dataWithoutUser);

            if (!result.success) {
                console.error('❌ [AUTO-SYNC BACKUP] Error al sincronizar con Firebase:', result.error);
            }
        }, 30000); // Esperar 30 segundos de inactividad

        return () => clearTimeout(timeoutId);
    }, [data, isInitialized, useFirestore, currentUser]);

    // --- Data Manipulation Functions ---

    const saveOrder = async (order: Order) => {
        // 1. Actualizar estado local primero
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

        // 2. Guardar INMEDIATAMENTE en Firebase (solo si es admin)
        if (useFirestore && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Actualizar la orden en el objeto de datos
                const updatedData = { ...dataWithoutUser };
                const orderIndex = updatedData.orders.findIndex((o: Order) => o.id === order.id);
                if (orderIndex > -1) {
                    updatedData.orders[orderIndex] = order;
                } else {
                    updatedData.orders.push(order);
                }

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al guardar orden ${order.id} en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al guardar orden ${order.id} en Firebase:`, error);
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
         // Set payoutStatus for each item
        newOrder.items.forEach(item => item.payoutStatus = 'pending');

        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            orders: [newOrder, ...prevData.orders]
        }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized) {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Agregar la nueva orden al objeto de datos
                const updatedData = {
                    ...dataWithoutUser,
                    orders: [newOrder, ...dataWithoutUser.orders]
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al guardar orden ${newOrder.id} en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al guardar orden ${newOrder.id} en Firebase:`, error);
            }
        }

        return newOrder;
    };

    const deleteOrder = async (orderId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            orders: prevData.orders.filter(o => o.id !== orderId)
        }));

        // 2. Guardar INMEDIATAMENTE en Firebase (solo si es admin)
        if (useFirestore && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Eliminar la orden del objeto de datos
                const updatedData = {
                    ...dataWithoutUser,
                    orders: dataWithoutUser.orders.filter((o: Order) => o.id !== orderId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar orden ${orderId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar orden ${orderId} de Firebase:`, error);
            }
        }
    };

    const saveVendor = async (vendor: Vendor) => {
        // 1. Actualizar estado local primero
        setData(prevData => {
            const newVendors = [...prevData.vendors];
            const index = newVendors.findIndex(v => v.id === vendor.id);
            if (index > -1) {
                newVendors[index] = vendor;
            } else {
                newVendors.push(vendor);
            }

            // 🎯 AUTO-CREAR ROL DE CUSTOMER: Si el vendor no existe como usuario, crearlo automáticamente
            const newUsers = [...prevData.users];
            const userExists = newUsers.find(u => u.email.toLowerCase() === vendor.email.toLowerCase());

            if (!userExists) {
                const newCustomer: User = {
                    id: vendor.id, // Usar mismo ID para vincular
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

        // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            // Obtener los datos actualizados
            const updatedData = { ...data };
            const vendorIndex = updatedData.vendors.findIndex(v => v.id === vendor.id);
            if (vendorIndex > -1) {
                updatedData.vendors[vendorIndex] = vendor;
            } else {
                updatedData.vendors.push(vendor);
            }

            const { currentUser, ...dataWithoutUser } = updatedData as any;
            const result = await saveAppDataToFirestore(dataWithoutUser);

            if (!result.success) {
                console.error('❌ [SAVE VENDOR] Error al guardar vendor en Firebase:', result.error);
            }
        }
    };

    const deleteVendor = async (vendorId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            vendors: prevData.vendors.filter(v => v.id !== vendorId)
        }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    vendors: dataWithoutUser.vendors.filter((v: Vendor) => v.id !== vendorId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar vendor ${vendorId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar vendor ${vendorId} de Firebase:`, error);
            }
        }
    };
    
    const saveCategory = async (category: Category) => {
      // 1. Actualizar estado local primero
      setData(prevData => {
        const index = prevData.categories.findIndex(c => c.id === category.id);
        const newCategories = [...prevData.categories];
        if (index > -1) { newCategories[index] = category; } else { newCategories.push(category); }
        return { ...prevData, categories: newCategories };
      });

      // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
      if (useFirestore && isInitialized && currentUser?.role === 'admin') {
        try {
          const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
          const { currentUser, ...dataWithoutUser } = data as any;

          const categoryIndex = dataWithoutUser.categories.findIndex((c: Category) => c.id === category.id);
          const newCategories = [...dataWithoutUser.categories];
          if (categoryIndex > -1) {
            newCategories[categoryIndex] = category;
          } else {
            newCategories.push(category);
          }

          const updatedData = {
            ...dataWithoutUser,
            categories: newCategories
          };

          const result = await saveAppDataToFirestore(updatedData);

          if (!result.success) {
            console.error('❌ Error al guardar categoría en Firebase:', result.error);
          }
        } catch (error) {
          console.error('❌ Error al guardar categoría en Firebase:', error);
        }
      }
    };

    const deleteCategory = async (categoryId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, categories: prevData.categories.filter(c => c.id !== categoryId) }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    categories: dataWithoutUser.categories.filter((c: Category) => c.id !== categoryId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar categoría ${categoryId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar categoría ${categoryId} de Firebase:`, error);
            }
        }
    };

    const saveCity = async (city: City) => {
      // 1. Actualizar estado local primero
      setData(prevData => {
        const index = prevData.cities.findIndex(c => c.id === city.id);
        const newCities = [...prevData.cities];
        if (index > -1) { newCities[index] = city; } else { newCities.push(city); }
        return { ...prevData, cities: newCities };
      });

      // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
      if (useFirestore && isInitialized && currentUser?.role === 'admin') {
        try {
          const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
          const { currentUser, ...dataWithoutUser } = data as any;

          const cityIndex = dataWithoutUser.cities.findIndex((c: City) => c.id === city.id);
          const newCities = [...dataWithoutUser.cities];
          if (cityIndex > -1) {
            newCities[cityIndex] = city;
          } else {
            newCities.push(city);
          }

          const updatedData = {
            ...dataWithoutUser,
            cities: newCities
          };

          const result = await saveAppDataToFirestore(updatedData);

          if (!result.success) {
            console.error('❌ Error al guardar ciudad en Firebase:', result.error);
          }
        } catch (error) {
          console.error('❌ Error al guardar ciudad en Firebase:', error);
        }
      }
    };

    const deleteCity = async (cityId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, cities: prevData.cities.filter(c => c.id !== cityId) }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    cities: dataWithoutUser.cities.filter((c: City) => c.id !== cityId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar ciudad ${cityId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar ciudad ${cityId} de Firebase:`, error);
            }
        }
    };
    
    const saveDeliveryZone = async (zone: DeliveryZone) => {
        // 1. Actualizar estado local primero
        setData(prevData => {
            const index = prevData.deliveryZones.findIndex(z => z.id === zone.id);
            const newZones = [...prevData.deliveryZones];
            if (index > -1) { newZones[index] = zone; } else { newZones.push(zone); }
            return { ...prevData, deliveryZones: newZones };
        });

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const zoneIndex = dataWithoutUser.deliveryZones.findIndex((z: DeliveryZone) => z.id === zone.id);
                const newZones = [...dataWithoutUser.deliveryZones];
                if (zoneIndex > -1) {
                    newZones[zoneIndex] = zone;
                } else {
                    newZones.push(zone);
                }

                const updatedData = {
                    ...dataWithoutUser,
                    deliveryZones: newZones
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error('❌ Error al guardar zona de entrega en Firebase:', result.error);
                }
            } catch (error) {
                console.error('❌ Error al guardar zona de entrega en Firebase:', error);
            }
        }
    };

    const deleteDeliveryZone = async (zoneId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, deliveryZones: prevData.deliveryZones.filter(z => z.id !== zoneId)}));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    deliveryZones: dataWithoutUser.deliveryZones.filter((z: DeliveryZone) => z.id !== zoneId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar zona de entrega ${zoneId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar zona de entrega ${zoneId} de Firebase:`, error);
            }
        }
    };

    const saveDriver = async (driver: DeliveryDriver) => {
      // 1. Actualizar estado local primero
      setData(prevData => {
        const index = prevData.drivers.findIndex(d => d.id === driver.id);
        const newDrivers = [...prevData.drivers];
        if (index > -1) { newDrivers[index] = driver; } else { newDrivers.push(driver); }

        // 🎯 AUTO-CREAR ROL DE CUSTOMER: Si el driver no existe como usuario, crearlo automáticamente
        const newUsers = [...prevData.users];
        const userExists = newUsers.find(u => u.email.toLowerCase() === driver.email.toLowerCase());

        if (!userExists) {
          const newCustomer: User = {
            id: driver.id, // Usar mismo ID para vincular
            name: driver.name,
            email: driver.email,
            phone: driver.phone,
            address: '', // Los drivers no tienen address en su registro
            profileImageUrl: driver.profileImageUrl || undefined,
          };
          newUsers.push(newCustomer);
        }

        return { ...prevData, drivers: newDrivers, users: newUsers };
      });

      // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (solo si es admin)
      if (useFirestore && isInitialized && currentUser?.role === 'admin') {
          // Obtener los datos actualizados
          const updatedData = { ...data };
          const driverIndex = updatedData.drivers.findIndex(d => d.id === driver.id);
          if (driverIndex > -1) {
              updatedData.drivers[driverIndex] = driver;
          } else {
              updatedData.drivers.push(driver);
          }

          const { currentUser, ...dataWithoutUser } = updatedData as any;
          const result = await saveAppDataToFirestore(dataWithoutUser);

          if (!result.success) {
              console.error('❌ [SAVE DRIVER] Error al guardar driver en Firebase:', result.error);
          }
      }
    };

    const deleteDriver = async (driverId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, drivers: prevData.drivers.filter(d => d.id !== driverId)}));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    drivers: dataWithoutUser.drivers.filter((d: Driver) => d.id !== driverId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar driver ${driverId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar driver ${driverId} de Firebase:`, error);
            }
        }
    };

    const saveUser = async (user: User) => {
       // 1. Actualizar estado local primero
       setData(prevData => {
        const index = prevData.users.findIndex(u => u.id === user.id);
        const newUsers = [...prevData.users];
        if (index > -1) { newUsers[index] = user; } else { newUsers.push(user); }
        return { ...prevData, users: newUsers };
      });

       // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE
       // Permitir guardar si:
       // - Es admin (puede guardar cualquier usuario)
       // - O es auto-creación (usuario se está registrando por primera vez)
       const auth = getAuth();
       const isAutoCreation = !currentUser && user.id === auth.currentUser?.uid;

       if (useFirestore && isInitialized && (currentUser?.role === 'admin' || isAutoCreation)) {
          try {
            // Obtener los datos actualizados
            const updatedData = { ...data };
            const userIndex = updatedData.users.findIndex(u => u.id === user.id);
            if (userIndex > -1) {
                updatedData.users[userIndex] = user;
            } else {
                updatedData.users.push(user);
            }

            const { currentUser, ...dataWithoutUser } = updatedData as any;

            const result = await saveAppDataToFirestore(dataWithoutUser);

            if (!result.success) {
                console.error('❌ Error al guardar usuario en Firebase:', result.error);
            }
          } catch (error) {
            console.error('❌ Error al guardar usuario en Firebase:', error);
          }
      }
    };
    
    const deleteUser = async (userId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({ ...prevData, users: prevData.users.filter(u => u.id !== userId) }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    users: dataWithoutUser.users.filter((u: User) => u.id !== userId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar usuario ${userId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar usuario ${userId} de Firebase:`, error);
            }
        }
    };
    
    const saveAdmin = async (admin: Admin) => {
       // 1. Actualizar estado local primero
       setData(prevData => {
        const index = prevData.admins.findIndex(c => c.id === admin.id);
        const newAdmins = [...prevData.admins];
        if (index > -1) { newAdmins[index] = admin; } else { newAdmins.push(admin); }
        return { ...prevData, admins: newAdmins };
      });

       // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (solo si es admin)
       if (useFirestore && isInitialized && currentUser?.role === 'admin') {
          // Obtener los datos actualizados
          const updatedData = { ...data };
          const adminIndex = updatedData.admins.findIndex(c => c.id === admin.id);
          if (adminIndex > -1) {
              updatedData.admins[adminIndex] = admin;
          } else {
              updatedData.admins.push(admin);
          }

          const { currentUser, ...dataWithoutUser } = updatedData as any;
          const result = await saveAppDataToFirestore(dataWithoutUser);

          if (!result.success) {
              console.error('❌ [SAVE ADMIN] Error al guardar admin en Firebase:', result.error);
          }
      }
    };
    
    const deleteAdmin = async (adminId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({ ...prevData, admins: prevData.admins.filter(c => c.id !== adminId) }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE (solo si es admin)
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    admins: dataWithoutUser.admins.filter((a: Admin) => a.id !== adminId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar admin ${adminId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar admin ${adminId} de Firebase:`, error);
            }
        }
    };

    const saveSettings = async (settings: AppSettings) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, appSettings: settings }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Actualizar settings en el objeto de datos
                const updatedData = {
                    ...dataWithoutUser,
                    appSettings: settings
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error('❌ Error al guardar configuración en Firebase:', result.error);
                }
            } catch (error) {
                console.error('❌ Error al guardar configuración en Firebase:', error);
            }
        }
    };

    const addMessage = async (newMessage: Omit<Message, 'id' | 'timestamp'>) => {
        const message: Message = { ...newMessage, id: `msg${Date.now()}`, timestamp: new Date().toISOString() };

        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, messages: [...prevData.messages, message]}));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized) {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    messages: [...dataWithoutUser.messages, message]
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al guardar mensaje ${message.id} en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al guardar mensaje ${message.id} en Firebase:`, error);
            }
        }
    };
    
    const markVendorPayoutAsPaid = async (vendorId: string) => {
        const vendor = data.vendors.find(v => v.id === vendorId);
        if (!vendor) return;

        // 1. Actualizar estado local primero
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

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Marcar items del vendor como pagados
                const newOrders = dataWithoutUser.orders.map((order: any) => {
                    const newItems = order.items.map((item: any) => {
                        if (item.vendor === vendor.name && item.payoutStatus === 'pending') {
                            return { ...item, payoutStatus: 'paid' as const };
                        }
                        return item;
                    });
                    return { ...order, items: newItems };
                });

                const updatedData = {
                    ...dataWithoutUser,
                    orders: newOrders
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al marcar liquidación de vendor en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al marcar liquidación de vendor en Firebase:`, error);
            }
        }
    };
    
    const updateDriverDebt = async (driverId: string, type: 'credit' | 'debit', amount: number, description: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex > -1) {
                const driver = { ...newDrivers[driverIndex] };
                const newDebt = type === 'debit' ? driver.debt + amount : driver.debt - amount;

                const newTransaction: DebtTransaction = {
                    id: `tx${Date.now()}`,
                    date: new Date().toISOString(),
                    type,
                    amount,
                    description,
                };

                driver.debt = Math.max(0, newDebt); // Prevent negative debt
                driver.debtTransactions = [...(driver.debtTransactions || []), newTransaction];
                newDrivers[driverIndex] = driver;

                return { ...prevData, drivers: newDrivers };
            }
            return prevData;
        });

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized) {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Actualizar driver en el objeto de datos
                const driverIndex = dataWithoutUser.drivers.findIndex((d: any) => d.id === driverId);
                if (driverIndex > -1) {
                    const driver = { ...dataWithoutUser.drivers[driverIndex] };
                    const newDebt = type === 'debit' ? driver.debt + amount : driver.debt - amount;

                    const newTransaction: DebtTransaction = {
                        id: `tx${Date.now()}`,
                        date: new Date().toISOString(),
                        type,
                        amount,
                        description,
                    };

                    driver.debt = Math.max(0, newDebt);
                    driver.debtTransactions = [...(driver.debtTransactions || []), newTransaction];

                    const newDrivers = [...dataWithoutUser.drivers];
                    newDrivers[driverIndex] = driver;

                    const updatedData = {
                        ...dataWithoutUser,
                        drivers: newDrivers
                    };

                    const result = await saveAppDataToFirestore(updatedData);

                    if (!result.success) {
                        console.error(`❌ Error al guardar transacción de deuda en Firebase:`, result.error);
                    }
                }
            } catch (error) {
                console.error(`❌ Error al guardar transacción de deuda en Firebase:`, error);
            }
        }
    };

    const addDebtTransaction = (driverId: string, amount: number, description: string) => {
        updateDriverDebt(driverId, 'debit', amount, description);
    };

    const addCreditTransaction = (driverId: string, amount: number, description: string) => {
        updateDriverDebt(driverId, 'credit', amount, description);
    };


    const clearDriverDebt = async (driverId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex > -1) {
                newDrivers[driverIndex] = { ...newDrivers[driverIndex], debt: 0 };
                return { ...prevData, drivers: newDrivers };
            }
            return prevData;
        });

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Actualizar driver en el objeto de datos
                const driverIndex = dataWithoutUser.drivers.findIndex((d: any) => d.id === driverId);
                if (driverIndex > -1) {
                    const newDrivers = [...dataWithoutUser.drivers];
                    newDrivers[driverIndex] = { ...newDrivers[driverIndex], debt: 0 };

                    const updatedData = {
                        ...dataWithoutUser,
                        drivers: newDrivers
                    };

                    const result = await saveAppDataToFirestore(updatedData);

                    if (!result.success) {
                        console.error(`❌ Error al limpiar deuda del driver en Firebase:`, result.error);
                    }
                }
            } catch (error) {
                console.error(`❌ Error al limpiar deuda del driver en Firebase:`, error);
            }
        }
    };
    
    const markDriverPayoutAsPaid = async (driverId: string, netBalance: number, ordersToMarkAsPaid: Order[]) => {
        // 1. Actualizar estado local primero
        setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex === -1) return prevData;

            const driver = { ...newDrivers[driverIndex] };

            // Step 1: Add a single credit transaction for the net balance paid to the driver.
            // If the balance is negative, it means the driver paid the platform, so it's also a credit.
            const transactionAmount = Math.abs(netBalance);
            const transactionDescription = netBalance >= 0 ? `Pago de liquidación por ${data.appSettings.currencySymbol}${transactionAmount.toFixed(2)}` : `Recepción de pago de deuda por ${data.appSettings.currencySymbol}${transactionAmount.toFixed(2)}`;

            const newTransaction: DebtTransaction = {
                id: `tx${Date.now()}`,
                date: new Date().toISOString(),
                type: 'credit',
                amount: transactionAmount,
                description: transactionDescription,
            };

            driver.debt = 0; // The debt is now settled.
            driver.debtTransactions = [...(driver.debtTransactions || []), newTransaction];
            newDrivers[driverIndex] = driver;

            // Step 2: Mark all settled orders as paid for the driver.
            const orderIdsToMark = new Set(ordersToMarkAsPaid.map(o => o.id));
            const newOrders = prevData.orders.map(order => {
                if (orderIdsToMark.has(order.id)) {
                    return { ...order, driverPayoutStatus: 'paid' as const };
                }
                return order;
            });

            return { ...prevData, drivers: newDrivers, orders: newOrders };
        });

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized && currentUser?.role === 'admin') {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                // Actualizar driver
                const driverIndex = dataWithoutUser.drivers.findIndex((d: any) => d.id === driverId);
                if (driverIndex === -1) return;

                const driver = { ...dataWithoutUser.drivers[driverIndex] };
                const transactionAmount = Math.abs(netBalance);
                const transactionDescription = netBalance >= 0 ? `Pago de liquidación por ${dataWithoutUser.appSettings.currencySymbol}${transactionAmount.toFixed(2)}` : `Recepción de pago de deuda por ${dataWithoutUser.appSettings.currencySymbol}${transactionAmount.toFixed(2)}`;

                const newTransaction: DebtTransaction = {
                    id: `tx${Date.now()}`,
                    date: new Date().toISOString(),
                    type: 'credit',
                    amount: transactionAmount,
                    description: transactionDescription,
                };

                driver.debt = 0;
                driver.debtTransactions = [...(driver.debtTransactions || []), newTransaction];

                const newDrivers = [...dataWithoutUser.drivers];
                newDrivers[driverIndex] = driver;

                // Marcar pedidos como pagados
                const orderIdsToMark = new Set(ordersToMarkAsPaid.map(o => o.id));
                const newOrders = dataWithoutUser.orders.map((order: any) => {
                    if (orderIdsToMark.has(order.id)) {
                        return { ...order, driverPayoutStatus: 'paid' as const };
                    }
                    return order;
                });

                const updatedData = {
                    ...dataWithoutUser,
                    drivers: newDrivers,
                    orders: newOrders
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al marcar liquidación de driver en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al marcar liquidación de driver en Firebase:`, error);
            }
        }
    };

    const addFavor = async (favorData: Omit<Favor, 'id' | 'date'>): Promise<Favor> => {
        const newFavor: Favor = {
            ...favorData,
            id: `FAV-${Date.now()}`,
            date: new Date().toISOString(),
        };

        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            favors: [newFavor, ...prevData.favors]
        }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized) {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    favors: [newFavor, ...dataWithoutUser.favors]
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al guardar favor ${newFavor.id} en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al guardar favor ${newFavor.id} en Firebase:`, error);
            }
        }

        return newFavor;
    };

    const saveFavor = async (favor: Favor) => {
        // 1. Actualizar estado local primero
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

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized) {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const favorIndex = dataWithoutUser.favors.findIndex((f: Favor) => f.id === favor.id);
                const newFavors = [...dataWithoutUser.favors];
                if (favorIndex > -1) {
                    newFavors[favorIndex] = favor;
                } else {
                    newFavors.push(favor);
                }

                const updatedData = {
                    ...dataWithoutUser,
                    favors: newFavors
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al actualizar favor ${favor.id} en Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al actualizar favor ${favor.id} en Firebase:`, error);
            }
        }
    };

    const deleteFavor = async (favorId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            favors: prevData.favors.filter(f => f.id !== favorId)
        }));

        // 2. 🔥 GUARDAR INMEDIATAMENTE EN FIREBASE
        if (useFirestore && isInitialized) {
            try {
                const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                const { currentUser, ...dataWithoutUser } = data as any;

                const updatedData = {
                    ...dataWithoutUser,
                    favors: dataWithoutUser.favors.filter((f: Favor) => f.id !== favorId)
                };

                const result = await saveAppDataToFirestore(updatedData);

                if (!result.success) {
                    console.error(`❌ Error al eliminar favor ${favorId} de Firebase:`, result.error);
                }
            } catch (error) {
                console.error(`❌ Error al eliminar favor ${favorId} de Firebase:`, error);
            }
        }
    };


    // --- Data Getter Functions ---
    const getAllProducts = () => data.vendors.flatMap(v => v.products);
    const getVendorById = (id: string) => data.vendors.find(v => v.id === id);
    const getProductById = (id: string) => getAllProducts().find(p => p.id === id);
    const getMessagesForOrder = (orderId: string) => data.messages.filter(m => m.orderId === orderId).sort((a,b) => new Date(a.timestamp).getTime() - new Date(b.timestamp).getTime());


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

    // Render children only after initialization to prevent hydration mismatch issues
    if (!isInitialized) {
        return null; // Or a loading spinner
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
