

"use client";

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { AppData, Vendor, Category, DeliveryDriver, User, AppSettings, Message, Product, Order, City, OrderItem, DrinkOption, Admin, DebtTransaction, Coordinate, DeliveryZone, Favor, UserSettings } from '@/lib/placeholder-data';
import { initializeFirebase } from '@/lib/firebase';
import { saveAppDataToFirestore, isFirestoreAvailable, COLLECTIONS } from '@/lib/firestore-service';
import { loadAppData, saveVendorToBackend, saveCustomerToBackend, saveDriverToBackend, saveOrderToBackend, saveCategoryToBackend, saveCityToBackend, saveSettingsToBackend, saveAdminToBackend, deleteVendorFromBackend, deleteDriverFromBackend, deleteCustomerFromBackend, deleteOrderFromBackend, deleteCategoryFromBackend, deleteCityFromBackend, deleteAdminFromBackend, saveDeliveryZoneToBackend, deleteDeliveryZoneFromBackend, saveMessageToBackend, saveFavorToBackend, deleteFavorFromBackend, subscribeToAppData, isBackendAvailable, BACKEND } from '@/lib/backend-adapter';
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

    const setSelectedCity = async (cityName: string | null) => {
        _setSelectedCity(cityName);

        // 🔥 GUARDAR EN FIREBASE si hay usuario autenticado
        if (currentUser) {
            const { saveUserSettingsToBackend } = await import('@/lib/backend-adapter');
            const result = await saveUserSettingsToBackend(currentUser.id, { selectedCity: cityName });

            if (result.success) {
            } else {
                console.error('❌ Error guardando ciudad:', result.error);
            }
        }
    };

    // 🔥 CARGAR selectedCity de Firebase cuando cambia currentUser
    useEffect(() => {
        async function loadUserSettings() {
            if (!currentUser) {
                _setSelectedCity(null);
                return;
            }

            const { loadUserSettingsFromBackend } = await import('@/lib/backend-adapter');
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

    // 🔥 SINCRONIZACIÓN EN TIEMPO REAL con Backend (Firebase o PocketBase)
    useEffect(() => {
        const initializeData = async () => {

            // Inicializar Firebase si es necesario (para autenticación)
            const firebaseInitialized = tryInitializeFirebase(emptyAppData.appSettings);

            if (!firebaseInitialized && BACKEND === 'firebase') {
                console.error('❌ Error: No se pudo inicializar Firebase');
                setIsInitialized(true);
                return;
            }

            // Verificar disponibilidad del backend
            const backendAvailable = await isBackendAvailable();
            if (!backendAvailable) {
                console.error(`❌ Error: Backend ${BACKEND} no está disponible`);
                setIsInitialized(true);
                return;
            }

            // ✅ CARGAR DATOS INICIALES (una sola vez)
            const result = await loadAppData(emptyAppData);

            if (result.success && result.data) {
                const loadedData = { ...result.data, currentUser: undefined } as AppData;
                setData(loadedData);

                // Detectar ciudad automáticamente
                if (loadedData.cities.length > 0) {
                    _setSelectedCity(loadedData.cities[0].name);
                }

                setIsInitialized(true);
            } else {
                console.error(`❌ Error al cargar datos desde ${BACKEND}:`, result.error);
                console.error('⚠️ La aplicación requiere datos en el backend para funcionar');
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
                // Usar el adapter que maneja tanto Firebase como PocketBase
                unsubscribe = subscribeToAppData(
                    (updatedData) => {
                        // ✅ VALIDAR datos antes de actualizar para evitar undefined
                        // Merge solo propiedades que existan en updatedData y tengan valores válidos
                        setData(prevData => {
                          // ✅ FILTRAR admins inválidos ANTES de actualizar estado
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
                            ...prevData, // Mantener datos actuales
                            // Solo sobrescribir si existen en updatedData y no son undefined
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
        const lowerEmail = email?.toLowerCase();

        if (!lowerEmail) return roles;

        // 🔒 PRIORIDAD MÁXIMA: Buscar en admins PRIMERO (admins no tienen status, siempre activos)
        const admin = data.admins.find(c => c.email?.toLowerCase() === lowerEmail);
        if (admin) {
            roles.push({ role: 'admin', data: admin });
        }

        // 🔒 SEGURIDAD: Buscar en vendors SOLO si status='Activo'
        const vendor = data.vendors.find(v =>
            v.email?.toLowerCase() === lowerEmail &&
            v.status === 'Activo'
        );
        if (vendor) {
            roles.push({ role: 'vendor', data: vendor });
        }

        // 🔒 SEGURIDAD: Buscar en drivers SOLO si status='Activo'
        const driver = data.drivers.find(d =>
            d.email?.toLowerCase() === lowerEmail &&
            d.status === 'Activo'
        );
        if (driver) {
            roles.push({ role: 'driver', data: driver });
        }

        // Buscar en customers (última prioridad)
        const customer = data.users.find(u => u.email?.toLowerCase() === lowerEmail);
        if (customer) {
            roles.push({ role: 'customer', data: customer });
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

        // 🔥 4. Si NO hay rol guardado PERO tiene vendor/driver APROBADO, auto-activar ese rol
        if (!savedRole && email) {
            // Buscar vendor/driver aprobado (status='Activo')
            const approvedVendor = allRoles.find(r => r.role === 'vendor' && r.data.status === 'Activo');
            const approvedDriver = allRoles.find(r => r.role === 'driver' && r.data.status === 'Activo');

            // Prioridad: vendor > driver (si ambos están aprobados)
            const approvedRole = approvedVendor || approvedDriver;

            if (approvedRole) {
                // Auto-guardar el rol aprobado en Firebase para persistencia
                updateUserSettings(email, approvedRole.role as 'customer' | 'vendor' | 'driver' | 'admin').catch(err => {
                    console.error('❌ Error al auto-guardar rol aprobado:', err);
                });
                return { ...approvedRole.data, role: approvedRole.role };
            }
        }

        // 5. Si no hay rol guardado ni aprobado, usar el primer rol disponible
        // Esto usualmente será 'customer' para usuarios nuevos
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
    // Y también detecta cuando un admin aprueba un vendor/driver en tiempo real
    useEffect(() => {
        if (!useFirestore) return;

        const auth = getAuth();
        const firebaseUser = auth.currentUser;

        // 🔥 CORRECCIÓN: función async interna para manejar await de findUserInData
        const checkAndSetUser = async () => {
            // ✅ SIEMPRE recalcular si hay usuario autenticado (eliminar condición !currentUser)
            // Esto permite detectar cuando un vendor/driver es aprobado por el admin
            if (firebaseUser) {
                const email = firebaseUser.email?.toLowerCase();
                const phoneNumber = firebaseUser.phoneNumber;

                const foundUser = await findUserInData(email || null, phoneNumber);

                if (foundUser) {
                    // ✅ Actualizar currentUser si cambió el rol o los datos
                    const hasChanged = !currentUser ||
                                     currentUser.role !== foundUser.role ||
                                     JSON.stringify(currentUser) !== JSON.stringify(foundUser);

                    if (hasChanged) {
                        setCurrentUser(foundUser);
                    }
                }
            }
        };

        checkAndSetUser();
    }, [useFirestore, data.admins, data.vendors, data.drivers, data.users]);

    // --- Auth Functions ---
    const login = async (email: string): Promise<LoggedInUser | null> => {
        // Reutilizar findUserInData() para eliminar duplicación
        const user = await findUserInData(email?.toLowerCase() || null, null);
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

    // 🔥 SINCRONIZACIÓN AUTOMÁTICA CON FIREBASE (GUARDAR INMEDIATAMENTE)
    // Guarda TODO el documento después de CADA cambio
    // ⚠️ SOLO SE EJECUTA PARA ADMINS (para evitar errores de permisos en otros usuarios)
    useEffect(() => {
        // No sincronizar si no está inicializado o no usa Firestore
        if (!isInitialized || !useFirestore) return;

        // ✅ SOLO admins pueden ejecutar el auto-sync (tienen permisos de escritura)
        if (currentUser?.role !== 'admin') return;

        // Debounce: esperar 2 segundos después del último cambio antes de guardar
        // REDUCIDO de 30s a 2s para persistencia inmediata
        const timeoutId = setTimeout(async () => {
            const { currentUser, ...dataWithoutUser } = data as any;
            const result = await saveAppDataToFirestore(dataWithoutUser);

            if (!result.success) {
                console.error('❌ [AUTO-SYNC] Error al sincronizar con Firebase:', result.error);
            } else {
            }
        }, 2000); // Esperar 2 segundos de inactividad para persistencia rápida

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

        // 2. 🔥 GUARDAR AL BACKEND (adaptador híbrido)
        if (isInitialized) {
            // Guardar orden usando el adaptador
            const orderResult = await saveOrderToBackend(order);
            if (!orderResult.success) {
                console.error(`❌ Error al guardar orden ${order.id}:`, orderResult.error);
            }

            // Para Firebase, también guardar el documento completo (por compatibilidad, solo admins)
            if (BACKEND === 'firebase' && useFirestore && currentUser?.role === 'admin') {
                try {
                    const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                    const { currentUser, ...dataWithoutUser } = data as any;

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

        // 2. 🔥 GUARDAR AL BACKEND (adaptador híbrido)
        if (isInitialized) {
            // Guardar nueva orden usando el adaptador
            const orderResult = await saveOrderToBackend(newOrder);
            if (!orderResult.success) {
                console.error(`❌ Error al guardar orden ${newOrder.id}:`, orderResult.error);
            }

            // Para Firebase, también guardar el documento completo (por compatibilidad)
            if (BACKEND === 'firebase' && useFirestore) {
                try {
                    const { saveAppDataToFirestore } = await import('@/lib/firestore-service');
                    const { currentUser, ...dataWithoutUser } = data as any;

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
        }

        return newOrder;
    };

    const deleteOrder = async (orderId: string) => {
        // 0. Buscar orden para obtener userId y orderDate
        const order = data.orders.find(o => o.id === orderId);
        if (!order) {
            console.error(`❌ Orden ${orderId} no encontrada`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            orders: prevData.orders.filter(o => o.id !== orderId)
        }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteOrderFromBackend(order.userId, order.orderDate);
            if (!result.success) {
                console.error(`❌ Error al eliminar orden ${orderId}:`, result.error);
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
            const userExists = newUsers.find(u => u.email?.toLowerCase() === vendor.email?.toLowerCase());

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

        // 2. 🔥 GUARDAR AL BACKEND (adaptador híbrido)
        if (isInitialized) {
            // Guardar vendor usando el adaptador
            const vendorResult = await saveVendorToBackend(vendor);
            if (!vendorResult.success) {
                console.error('❌ [SAVE VENDOR] Error al guardar vendor:', vendorResult.error);
                return vendorResult; // Retornar error
            }

            // Si se creó un usuario automáticamente, guardarlo también
            const userExists = data.users.find(u => u.email?.toLowerCase() === vendor.email?.toLowerCase());
            if (!userExists) {
                const newCustomer: User = {
                    id: vendor.id,
                    name: vendor.name,
                    email: vendor.email,
                    phone: vendor.phone,
                    address: vendor.address || '',
                    profileImageUrl: vendor.imageUrl || undefined,
                };
                const customerResult = await saveCustomerToBackend(newCustomer);
                if (!customerResult.success) {
                    console.error('❌ [SAVE VENDOR] Error al guardar customer auto-creado:', customerResult.error);
                }
            }

            // Para Firebase, también guardar el documento completo (por compatibilidad)
            if (BACKEND === 'firebase' && useFirestore && currentUser?.role === 'admin') {
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
                    console.error('❌ [SAVE VENDOR] Error al guardar en Firebase:', result.error);
                    return result; // Retornar error
                }
            }

            return vendorResult; // Retornar éxito
        }

        return { success: true }; // Si no está inicializado, retornar éxito (solo se actualizó estado local)
    };

    const deleteVendor = async (vendorId: string) => {
        // 0. Buscar vendor primero
        const vendor = data.vendors.find(v => v.id === vendorId);
        if (!vendor) {
            console.error(`❌ Vendor ${vendorId} no encontrado`);
            return;
        }

        // Verificar que vendor.id existe
        if (!vendor.id) {
            console.error(`❌ Vendor sin ID válido:`, vendor);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            vendors: prevData.vendors.filter(v => v.id !== vendorId)
        }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteVendorFromBackend(vendor.id);
            if (!result.success) {
                console.error(`❌ Error al eliminar vendor ${vendor.id}:`, result.error);
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

      // 2. 🔥 GUARDAR EN BACKEND (solo si es admin)
      if (currentUser?.role === 'admin') {
        if (useFirestore && isInitialized) {
          // Guardar en Firebase
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
      }
    };

    const deleteCategory = async (categoryId: string) => {
        // 0. Buscar categoría para obtener su nombre
        const category = data.categories.find(c => c.id === categoryId);
        if (!category) {
            console.error(`❌ Categoría ${categoryId} no encontrada`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, categories: prevData.categories.filter(c => c.id !== categoryId) }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteCategoryFromBackend(category.name);
            if (!result.success) {
                console.error(`❌ Error al eliminar categoría ${category.name}:`, result.error);
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

      // 2. 🔥 GUARDAR EN BACKEND (solo si es admin)
      if (currentUser?.role === 'admin') {
        if (useFirestore && isInitialized) {
          // Guardar en Firebase
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
      }
    };

    const deleteCity = async (cityId: string) => {
        // 0. Buscar ciudad para obtener su nombre
        const city = data.cities.find(c => c.id === cityId);
        if (!city) {
            console.error(`❌ Ciudad ${cityId} no encontrada`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, cities: prevData.cities.filter(c => c.id !== cityId) }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteCityFromBackend(city.name);
            if (!result.success) {
                console.error(`❌ Error al eliminar ciudad ${city.name}:`, result.error);
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

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await saveDeliveryZoneToBackend(zone);
            if (!result.success) {
                console.error('❌ Error al guardar zona de entrega:', result.error);
            }
        }
    };

    const deleteDeliveryZone = async (zoneId: string) => {
        // 0. Buscar zona para obtener su nombre
        const zone = data.deliveryZones.find(z => z.id === zoneId);
        if (!zone) {
            console.error(`❌ Zona de entrega ${zoneId} no encontrada`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, deliveryZones: prevData.deliveryZones.filter(z => z.id !== zoneId)}));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteDeliveryZoneFromBackend(zone.name);
            if (!result.success) {
                console.error(`❌ Error al eliminar zona de entrega ${zone.name}:`, result.error);
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
        const userExists = newUsers.find(u => u.email?.toLowerCase() === driver.email?.toLowerCase());

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

      // 2. 🔥 GUARDAR AL BACKEND (adaptador híbrido)
      if (isInitialized) {
          // Guardar driver usando el adaptador
          const driverResult = await saveDriverToBackend(driver);
          if (!driverResult.success) {
              console.error('❌ [SAVE DRIVER] Error al guardar driver:', driverResult.error);
              return driverResult; // Retornar error
          }

          // Si se creó un usuario automáticamente, guardarlo también
          const userExists = data.users.find(u => u.email?.toLowerCase() === driver.email?.toLowerCase());
          if (!userExists) {
              const newCustomer: User = {
                  id: driver.id,
                  name: driver.name,
                  email: driver.email,
                  phone: driver.phone,
                  address: '',
                  profileImageUrl: driver.profileImageUrl || undefined,
              };
              const customerResult = await saveCustomerToBackend(newCustomer);
              if (!customerResult.success) {
                  console.error('❌ [SAVE DRIVER] Error al guardar customer auto-creado:', customerResult.error);
              }
          }

          // Para Firebase, también guardar el documento completo (por compatibilidad)
          if (BACKEND === 'firebase' && useFirestore && currentUser?.role === 'admin') {
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
                  console.error('❌ [SAVE DRIVER] Error al guardar en Firebase:', result.error);
                  return result; // Retornar error
              }
          }

          return driverResult; // Retornar éxito
      }

      return { success: true }; // Si no está inicializado, retornar éxito (solo se actualizó estado local)
    };

    const deleteDriver = async (driverId: string) => {
        // 0. Buscar driver para obtener su email
        const driver = data.drivers.find(d => d.id === driverId);
        if (!driver) {
            console.error(`❌ Driver ${driverId} no encontrado`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, drivers: prevData.drivers.filter(d => d.id !== driverId)}));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteDriverFromBackend(driver.email);
            if (!result.success) {
                console.error(`❌ Error al eliminar driver ${driver.email}:`, result.error);
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

       // 2. 🔥 GUARDAR AL BACKEND (adaptador híbrido)
       // Permitir guardar si:
       // - Es admin (puede guardar cualquier usuario)
       // - O es auto-creación (usuario se está registrando por primera vez)
       const auth = getAuth();
       const isAutoCreation = !currentUser && user.id === auth.currentUser?.uid;

       if (isInitialized && (currentUser?.role === 'admin' || isAutoCreation)) {
          // Guardar usuario usando el adaptador
          const userResult = await saveCustomerToBackend(user);
          if (!userResult.success) {
              console.error('❌ Error al guardar usuario:', userResult.error);
          }

          // Para Firebase, también guardar el documento completo (por compatibilidad)
          if (BACKEND === 'firebase' && useFirestore) {
              try {
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
      }
    };
    
    const deleteUser = async (userId: string) => {
        // 0. Buscar usuario para obtener su email
        const user = data.users.find(u => u.id === userId);
        if (!user) {
            console.error(`❌ Usuario ${userId} no encontrado`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({ ...prevData, users: prevData.users.filter(u => u.id !== userId) }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteCustomerFromBackend(user.email);
            if (!result.success) {
                console.error(`❌ Error al eliminar usuario ${user.email}:`, result.error);
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

       // 2. 🔥 GUARDAR EN BACKEND (solo si es admin)
       if (currentUser?.role === 'admin') {
          if (useFirestore && isInitialized) {
              // Guardar en Firebase
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
                  console.error('❌ Error al guardar admin en Firebase:', result.error);
              }
          }
      }
    };
    
    const deleteAdmin = async (adminId: string) => {
        // 0. Buscar admin para obtener su email
        const admin = data.admins.find(a => a.id === adminId);
        if (!admin) {
            console.error(`❌ Admin ${adminId} no encontrado`);
            return;
        }

        // 1. Actualizar estado local primero
        setData(prevData => ({ ...prevData, admins: prevData.admins.filter(c => c.id !== adminId) }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteAdminFromBackend(admin.email);
            if (!result.success) {
                console.error(`❌ Error al eliminar admin ${admin.email}:`, result.error);
            }
        }
    };

    const saveSettings = async (settings: AppSettings) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, appSettings: settings }));

        // 2. 🔥 GUARDAR EN BACKEND
        if (currentUser?.role === 'admin') {
            if (useFirestore && isInitialized) {
                // Guardar en Firebase
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
        }
    };

    const addMessage = async (newMessage: Omit<Message, 'id' | 'timestamp'>) => {
        const message: Message = { ...newMessage, id: `msg${Date.now()}`, timestamp: new Date().toISOString() };

        // 1. Actualizar estado local primero
        setData(prevData => ({...prevData, messages: [...prevData.messages, message]}));

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await saveMessageToBackend(message);
            if (!result.success) {
                console.error(`❌ Error al guardar mensaje ${message.id}:`, result.error);
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

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            // Encontrar pedidos que tienen items de este vendor con payoutStatus pending
            const ordersToUpdate = data.orders.filter(order =>
                order.items.some(item => item.vendor === vendor.name && item.payoutStatus === 'pending')
            );

            // Actualizar cada pedido individualmente
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

                const result = await saveOrderToBackend(updatedOrder);
                if (!result.success) {
                    console.error(`❌ Error al actualizar pedido ${order.id} en liquidación de vendor:`, result.error);
                }
            }
        }
    };
    
    const updateDriverDebt = async (driverId: string, type: 'credit' | 'debit', amount: number, description: string) => {
        // 0. Buscar driver actual
        const driver = data.drivers.find(d => d.id === driverId);
        if (!driver) {
            console.error(`❌ Driver ${driverId} no encontrado`);
            return;
        }

        // Calcular nueva deuda y crear transacción
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
            debt: Math.max(0, newDebt), // Prevent negative debt
            debtTransactions: [...(driver.debtTransactions || []), newTransaction]
        };

        // 1. Actualizar estado local primero
        setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex > -1) {
                newDrivers[driverIndex] = updatedDriver;
                return { ...prevData, drivers: newDrivers };
            }
            return prevData;
        });

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await saveDriverToBackend(updatedDriver);
            if (!result.success) {
                console.error(`❌ Error al actualizar deuda del driver ${driver.email}:`, result.error);
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
        // 0. Buscar driver actual
        const driver = data.drivers.find(d => d.id === driverId);
        if (!driver) {
            console.error(`❌ Driver ${driverId} no encontrado`);
            return;
        }

        const updatedDriver = { ...driver, debt: 0 };

        // 1. Actualizar estado local primero
        setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex > -1) {
                newDrivers[driverIndex] = updatedDriver;
                return { ...prevData, drivers: newDrivers };
            }
            return prevData;
        });

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await saveDriverToBackend(updatedDriver);
            if (!result.success) {
                console.error(`❌ Error al limpiar deuda del driver ${driver.email}:`, result.error);
            }
        }
    };
    
    const markDriverPayoutAsPaid = async (driverId: string, netBalance: number, ordersToMarkAsPaid: Order[]) => {
        // 0. Buscar driver actual
        const driver = data.drivers.find(d => d.id === driverId);
        if (!driver) {
            console.error(`❌ Driver ${driverId} no encontrado`);
            return;
        }

        // Crear transacción de crédito
        const transactionAmount = Math.abs(netBalance);
        const transactionDescription = netBalance >= 0
            ? `Pago de liquidación por ${data.appSettings.currencySymbol}${transactionAmount.toFixed(2)}`
            : `Recepción de pago de deuda por ${data.appSettings.currencySymbol}${transactionAmount.toFixed(2)}`;

        const newTransaction: DebtTransaction = {
            id: `tx${Date.now()}`,
            date: new Date().toISOString(),
            type: 'credit',
            amount: transactionAmount,
            description: transactionDescription,
        };

        const updatedDriver = {
            ...driver,
            debt: 0, // The debt is now settled
            debtTransactions: [...(driver.debtTransactions || []), newTransaction]
        };

        // 1. Actualizar estado local primero
        setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex > -1) {
                newDrivers[driverIndex] = updatedDriver;
            }

            // Marcar pedidos como pagados
            const orderIdsToMark = new Set(ordersToMarkAsPaid.map(o => o.id));
            const newOrders = prevData.orders.map(order => {
                if (orderIdsToMark.has(order.id)) {
                    return { ...order, driverPayoutStatus: 'paid' as const };
                }
                return order;
            });

            return { ...prevData, drivers: newDrivers, orders: newOrders };
        });

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            // Guardar driver actualizado
            const driverResult = await saveDriverToBackend(updatedDriver);
            if (!driverResult.success) {
                console.error(`❌ Error al actualizar driver ${driver.email} en liquidación:`, driverResult.error);
            }

            // Marcar cada pedido como pagado individualmente
            for (const order of ordersToMarkAsPaid) {
                const updatedOrder = { ...order, driverPayoutStatus: 'paid' as const };
                const orderResult = await saveOrderToBackend(updatedOrder);
                if (!orderResult.success) {
                    console.error(`❌ Error al marcar pedido ${order.id} como pagado:`, orderResult.error);
                }
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

        // 2. 🔥 GUARDAR EN BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await saveFavorToBackend(favor);
            if (!result.success) {
                console.error(`❌ Error al guardar favor ${favor.id}:`, result.error);
            }
        }
    };

    const deleteFavor = async (favorId: string) => {
        // 1. Actualizar estado local primero
        setData(prevData => ({
            ...prevData,
            favors: prevData.favors.filter(f => f.id !== favorId)
        }));

        // 2. 🔥 ELIMINAR DEL BACKEND (adaptador híbrido)
        if (isInitialized) {
            const result = await deleteFavorFromBackend(favorId);
            if (!result.success) {
                console.error(`❌ Error al eliminar favor ${favorId}:`, result.error);
            }
        }
    };


    // --- Data Getter Functions ---
    const getAllProducts = () => data.vendors.flatMap(v => v.products).filter(p => p != null);
    const getVendorById = (id: string) => data.vendors.find(v => v.id === id);
    const getProductById = (id: string) => getAllProducts().find(p => p?.id === id);
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
