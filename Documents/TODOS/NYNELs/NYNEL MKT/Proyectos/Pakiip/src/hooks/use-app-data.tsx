

"use client";

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { AppData, Vendor, Category, DeliveryDriver, User, AppSettings, Message, Product, Order, City, OrderItem, DrinkOption, Admin, DebtTransaction, Coordinate, DeliveryZone, Favor } from '@/lib/placeholder-data';
import { initializeFirebase } from '@/lib/firebase';
import { saveAppDataToFirestore, loadAppDataFromFirestore, isFirestoreAvailable } from '@/lib/firestore-service';
import { getAuth, onAuthStateChanged, User as FirebaseUser } from 'firebase/auth';

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

    const setSelectedCity = (cityName: string | null) => {
        _setSelectedCity(cityName);
    };

    // Cargar datos al iniciar SOLO desde Firestore
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

            // Cargar TODOS los datos exclusivamente desde Firestore
            const result = await loadAppDataFromFirestore(emptyAppData);

            if (result.success && result.data) {
                const loadedData = { ...result.data, currentUser: undefined } as AppData;
                setData(loadedData);

                // 🔍 DEBUG: Ver si los datos se cargaron
                console.log('✅ Datos cargados desde Firestore:', {
                    admins: loadedData.admins.length,
                    vendors: loadedData.vendors.length,
                    drivers: loadedData.drivers.length,
                    users: loadedData.users.length,
                    adminEmails: loadedData.admins.map(c => c.email)
                });

                // Detectar ciudad automáticamente
                if (loadedData.cities.length > 0) {
                    // La detección de ciudad ahora se maneja en los componentes
                    // individuales usando el hook useLocation()
                    _setSelectedCity(loadedData.cities[0].name);
                    setIsInitialized(true);
                } else {
                    setIsInitialized(true);
                }
            } else {
                console.error('❌ Error al cargar datos desde Firestore:', result.error);
                console.error('⚠️ La aplicación requiere datos en Firebase para funcionar');
                setIsInitialized(true);
            }
        };

        initializeData();
    }, []);

    // Función helper para buscar usuario en los datos locales
    const findUserInData = (email: string | null, phoneNumber: string | null): LoggedInUser | null => {
        // 🔍 DEBUG: Ver qué email/teléfono se está buscando
        console.log('🔍 Buscando usuario:', { email, phoneNumber,
            dataAvailable: {
                admins: data.admins.length,
                vendors: data.vendors.length,
                drivers: data.drivers.length,
                users: data.users.length
            }
        });

        if (!email && !phoneNumber) return null;

        let foundUser: LoggedInUser | null = null;

        // ORDEN CORRECTO: Buscar PRIMERO en users (customers) - todos empiezan como clientes
        // Buscar por EMAIL o TELÉFONO para vincular cuentas correctamente
        if (email || phoneNumber) {
            const customer = data.users.find(u =>
                (email && u.email.toLowerCase() === email) ||
                (phoneNumber && u.phone === phoneNumber)
            );
            if (customer) {
                foundUser = { ...customer, role: 'customer' };
            }
        }

        // Buscar en drivers (solo si ya es driver registrado)
        if (!foundUser && (email || phoneNumber)) {
            const driver = data.drivers.find(d =>
                (email && d.email.toLowerCase() === email) ||
                (phoneNumber && d.phone === phoneNumber)
            );
            if (driver) {
                foundUser = { ...driver, role: 'driver' };
            }
        }

        // Buscar en vendors (solo si ya es vendedor registrado)
        if (!foundUser && (email || phoneNumber)) {
            const vendor = data.vendors.find(v =>
                (email && v.email.toLowerCase() === email) ||
                (phoneNumber && v.phone === phoneNumber)
            );
            if (vendor) {
                foundUser = { ...vendor, role: 'vendor' };
            }
        }

        // Buscar en admins - última prioridad
        if (!foundUser && (email || phoneNumber)) {
            const admin = data.admins.find(c =>
                (email && c.email.toLowerCase() === email) ||
                (phoneNumber && c.phone === phoneNumber)
            );
            if (admin) {
                foundUser = { ...admin, role: 'admin' };
            }
        }

        // 🔍 DEBUG: Ver resultado de la búsqueda
        console.log('🔍 Resultado búsqueda:', foundUser ? `✅ Encontrado: ${foundUser.role}` : '❌ NO encontrado');

        return foundUser;
    };

    // Sincronizar usuario autenticado de Firebase Auth con currentUser
    useEffect(() => {
        if (!useFirestore) return;

        const auth = getAuth();
        const unsubscribe = onAuthStateChanged(auth, (firebaseUser: FirebaseUser | null) => {
            if (firebaseUser) {
                // Usuario autenticado en Firebase
                // Buscar usuario en la base de datos local por email O teléfono
                const email = firebaseUser.email?.toLowerCase();
                const phoneNumber = firebaseUser.phoneNumber;

                const foundUser = findUserInData(email || null, phoneNumber);

                // Si no se encuentra el usuario en ninguna tabla, dejarlo sin rol
                // El flujo de login redirigirá a /select-role para que elija
                if (!foundUser) {
                    // No establecer currentUser para que el login detecte que necesita seleccionar rol
                    console.log('⚠️ Usuario autenticado en Firebase pero NO encontrado en Firestore:', email || phoneNumber);
                    setCurrentUser(null);
                    return; // Salir temprano
                }

                console.log('✅ Usuario encontrado en Firestore:', foundUser.role, email || phoneNumber);
                setCurrentUser(foundUser);
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

        // 🔍 DEBUG: Ver estado del segundo useEffect
        console.log('🔄 Segundo useEffect ejecutándose:', {
            firebaseUser: firebaseUser?.email || firebaseUser?.phoneNumber || null,
            currentUser: currentUser ? currentUser.role : null,
            shouldReEvaluate: !!(firebaseUser && !currentUser)
        });

        // Si hay un usuario autenticado en Firebase pero currentUser no tiene role
        if (firebaseUser && !currentUser) {
            const email = firebaseUser.email?.toLowerCase();
            const phoneNumber = firebaseUser.phoneNumber;

            console.log('🔄 Re-evaluando usuario ahora con email:', email, 'phone:', phoneNumber);

            const foundUser = findUserInData(email || null, phoneNumber);

            if (foundUser) {
                console.log('✅ Re-evaluando usuario después de cargar datos:', foundUser.role, email || phoneNumber);
                setCurrentUser(foundUser);
            } else {
                console.log('❌ Re-evaluación: Usuario NO encontrado en datos');
            }
        }
    }, [useFirestore, data.admins, data.vendors, data.drivers, data.users, currentUser]);

    // --- Auth Functions ---
    const login = (email: string): LoggedInUser | null => {
        // ORDEN CORRECTO: Buscar PRIMERO en customers, luego otros roles
        const customer = data.users.find(u => u.email.toLowerCase() === email.toLowerCase());
        if (customer) {
            const user: LoggedInUser = { ...customer, role: 'customer' };
            setCurrentUser(user);
            return user;
        }

        const driver = data.drivers.find(d => d.email.toLowerCase() === email.toLowerCase());
        if (driver) {
            const user: LoggedInUser = { ...driver, role: 'driver' };
            setCurrentUser(user);
            return user;
        }

        const vendor = data.vendors.find(v => v.email.toLowerCase() === email.toLowerCase());
        if (vendor) {
            const user: LoggedInUser = { ...vendor, role: 'vendor' };
            setCurrentUser(user);
            return user;
        }

        const admin = data.admins.find(c => c.email.toLowerCase() === email.toLowerCase());
        if (admin) {
            const user: LoggedInUser = { ...admin, role: 'admin' };
            setCurrentUser(user);
            return user;
        }

        return null;
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

    // Función para cambiar de rol (solo si el usuario tiene ese rol)
    const switchRole = (role: 'customer' | 'vendor' | 'driver' | 'admin'): boolean => {
        if (!currentUser) return false;

        const userRoles = getUserRoles(currentUser.email);
        const targetRole = userRoles.find(r => r.role === role);

        if (targetRole) {
            const newUser: LoggedInUser = { ...targetRole.data, role: targetRole.role };
            setCurrentUser(newUser);

            // Guardar rol seleccionado en localStorage para persistencia
            if (typeof window !== 'undefined') {
                localStorage.setItem('selectedRole', role);
            }

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
    useEffect(() => {
        // No sincronizar si no está inicializado o no usa Firestore
        if (!isInitialized || !useFirestore) return;

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
    }, [data, isInitialized, useFirestore]);

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

        // 2. Guardar INMEDIATAMENTE en Firebase
        if (useFirestore) {
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

        // 2. Guardar INMEDIATAMENTE en Firebase
        if (useFirestore) {
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

        // 2. Guardar INMEDIATAMENTE en Firebase
        if (useFirestore) {
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

        // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (no esperar el auto-sync)
        if (useFirestore && isInitialized) {
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

    const deleteVendor = (vendorId: string) => {
        setData(prevData => ({
            ...prevData,
            vendors: prevData.vendors.filter(v => v.id !== vendorId)
        }));
    };
    
    const saveCategory = (category: Category) => {
      setData(prevData => {
        const index = prevData.categories.findIndex(c => c.id === category.id);
        const newCategories = [...prevData.categories];
        if (index > -1) { newCategories[index] = category; } else { newCategories.push(category); }
        return { ...prevData, categories: newCategories };
      });
    };

    const deleteCategory = (categoryId: string) => {
        setData(prevData => ({...prevData, categories: prevData.categories.filter(c => c.id !== categoryId) }));
    };

    const saveCity = (city: City) => {
      setData(prevData => {
        const index = prevData.cities.findIndex(c => c.id === city.id);
        const newCities = [...prevData.cities];
        if (index > -1) { newCities[index] = city; } else { newCities.push(city); }
        return { ...prevData, cities: newCities };
      });
    };

    const deleteCity = (cityId: string) => {
        setData(prevData => ({...prevData, cities: prevData.cities.filter(c => c.id !== cityId) }));
    };
    
    const saveDeliveryZone = (zone: DeliveryZone) => {
        setData(prevData => {
            const index = prevData.deliveryZones.findIndex(z => z.id === zone.id);
            const newZones = [...prevData.deliveryZones];
            if (index > -1) { newZones[index] = zone; } else { newZones.push(zone); }
            return { ...prevData, deliveryZones: newZones };
        });
    };

    const deleteDeliveryZone = (zoneId: string) => {
        setData(prevData => ({...prevData, deliveryZones: prevData.deliveryZones.filter(z => z.id !== zoneId)}));
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

      // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (no esperar el auto-sync)
      if (useFirestore && isInitialized) {
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

    const deleteDriver = (driverId: string) => {
        setData(prevData => ({...prevData, drivers: prevData.drivers.filter(d => d.id !== driverId)}));
    };

    const saveUser = async (user: User) => {
       // 1. Actualizar estado local primero
       setData(prevData => {
        const index = prevData.users.findIndex(u => u.id === user.id);
        const newUsers = [...prevData.users];
        if (index > -1) { newUsers[index] = user; } else { newUsers.push(user); }
        return { ...prevData, users: newUsers };
      });

       // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (no esperar el auto-sync)
       if (useFirestore && isInitialized) {
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
              console.error('❌ [SAVE USER] Error al guardar user en Firebase:', result.error);
          }
      }
    };
    
    const deleteUser = (userId: string) => {
        setData(prevData => ({ ...prevData, users: prevData.users.filter(u => u.id !== userId) }));
    };
    
    const saveAdmin = async (admin: Admin) => {
       // 1. Actualizar estado local primero
       setData(prevData => {
        const index = prevData.admins.findIndex(c => c.id === admin.id);
        const newAdmins = [...prevData.admins];
        if (index > -1) { newAdmins[index] = admin; } else { newAdmins.push(admin); }
        return { ...prevData, admins: newAdmins };
      });

       // 2. 🔥 GUARDAR INMEDIATAMENTE A FIREBASE (no esperar el auto-sync)
       if (useFirestore && isInitialized) {
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
    
    const deleteAdmin = (adminId: string) => {
        setData(prevData => ({ ...prevData, admins: prevData.admins.filter(c => c.id !== adminId) }));
    };

    const saveSettings = (settings: AppSettings) => {
        setData(prevData => ({...prevData, appSettings: settings }));
    };

    const addMessage = (newMessage: Omit<Message, 'id' | 'timestamp'>) => {
        const message: Message = { ...newMessage, id: `msg${Date.now()}`, timestamp: new Date().toISOString() };
        setData(prevData => ({...prevData, messages: [...prevData.messages, message]}));
    };
    
    const markVendorPayoutAsPaid = (vendorId: string) => {
        const vendor = data.vendors.find(v => v.id === vendorId);
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
    };
    
    const updateDriverDebt = (driverId: string, type: 'credit' | 'debit', amount: number, description: string) => {
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
    };

    const addDebtTransaction = (driverId: string, amount: number, description: string) => {
        updateDriverDebt(driverId, 'debit', amount, description);
    };

    const addCreditTransaction = (driverId: string, amount: number, description: string) => {
        updateDriverDebt(driverId, 'credit', amount, description);
    };


    const clearDriverDebt = (driverId: string) => {
         setData(prevData => {
            const newDrivers = [...prevData.drivers];
            const driverIndex = newDrivers.findIndex(d => d.id === driverId);
            if (driverIndex > -1) {
                newDrivers[driverIndex] = { ...newDrivers[driverIndex], debt: 0 };
                return { ...prevData, drivers: newDrivers };
            }
            return prevData;
        });
    };
    
    const markDriverPayoutAsPaid = (driverId: string, netBalance: number, ordersToMarkAsPaid: Order[]) => {
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
    };

    const addFavor = (favorData: Omit<Favor, 'id' | 'date'>): Favor => {
        const newFavor: Favor = {
            ...favorData,
            id: `FAV-${Date.now()}`,
            date: new Date().toISOString(),
        };
        setData(prevData => ({
            ...prevData,
            favors: [newFavor, ...prevData.favors]
        }));
        return newFavor;
    };

    const saveFavor = (favor: Favor) => {
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
    };

    const deleteFavor = (favorId: string) => {
        setData(prevData => ({
            ...prevData,
            favors: prevData.favors.filter(f => f.id !== favorId)
        }));
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
