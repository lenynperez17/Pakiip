
import type { QuoteFavorOutput } from "@/ai/flows/quote-favor-flow";

// --- Type Definitions ---
export type DrinkOption = {
  name: string;
  price: number;
  costPrice: number;
}

export type ProductOption = {
  packagingFee?: number;
  cutleryPrice?: number;
  cutleryCostPrice?: number;
  drinks?: DrinkOption[];
}

export type VendorProductCategory = {
    id: string;
    name: string;
};

export type Product = {
  id: string;
  name: string;
  description: string;
  price: number;
  costPrice: number;
  offerPrice?: number;
  isOffer?: boolean;
  isFeatured?: boolean;
  imageUrl: string;
  vendorId: string;
  vendorCategoryId?: string;
  stock: number;
  options?: ProductOption;
};

export type Coordinate = {
  lat: number;
  lng: number;
};

export type VendorBankAccount = {
  bankName: string;
  accountNumber: string;
  accountHolder: string;
};

export type Vendor = {
  id: string;
  name: string;
  email: string;
  phone: string;
  dni: string;
  bankAccount?: VendorBankAccount; // Opcional - solo si paymentMethod === 'bank'
  paymentMethod?: 'bank' | 'qr'; // Método de cobro del vendor
  qrImageUrl?: string; // URL de Firebase Storage - solo si paymentMethod === 'qr'
  qrPaymentName?: string; // Nombre del método QR (Yape, Plin, etc.)
  description: string;
  category: string;
  imageUrl: string; // Logo
  bannerUrl?: string; // Banner
  address: string; // Full address of the vendor
  location: string; // City name
  coordinates: Coordinate;
  products: Product[];
  productCategories: VendorProductCategory[];
  isFeatured?: boolean;
  commissionRate: number;
  status: 'Activo' | 'Inactivo' | 'Pendiente' | 'Rechazado';
  additionalFee?: number; // Fee for packaging, etc.
};

export type Category = {
    id: string;
    name: string;
    imageUrl: string;
    imageHint: string;
};

export type DeliveryZone = {
  id: string;
  name: string;
  cityId: string;
  path: Coordinate[]; // Array of coordinates forming the polygon
  shippingFee: number;
};

export type CitySector = {
  name: string;
  fee: number;
};

export type City = {
    id: string;
    name: string;
    coordinates: Coordinate;
    sectors?: CitySector[];
};

export type CoinTransaction = {
    id: string;
    userId: string;
    type: 'earned' | 'spent' | 'bonus';
    amount: number;
    reason: string;
    description: string;
    timestamp: string;
    relatedOrderId?: string;
};

export type Reward = {
    id: string;
    name: string;
    description: string;
    coinsCost: number;
    imageUrl: string;
    type: 'discount' | 'freeDelivery' | 'product' | 'voucher';
    value: number; // Valor en soles o porcentaje
    stock: number;
    isActive: boolean;
};

export type User = {
    id:string;
    name: string;
    email: string;
    phone?: string;
    dni?: string;
    city?: string;
    sector?: string;
    coordinates?: Coordinate;
    profileImageUrl?: string;
    coins?: number; // Monedas Pakiip del usuario
    coinTransactions?: CoinTransaction[]; // Historial de transacciones
};

export type DeliveryDriver = {
    id: string;
    name: string;
    email: string;
    dni: string;
    phone: string;
    bankAccount?: string; // Opcional - solo si paymentMethod === 'bank'
    paymentMethod?: 'bank' | 'qr'; // Método de cobro del driver
    qrImageUrl?: string; // URL de Firebase Storage - solo si paymentMethod === 'qr'
    qrPaymentName?: string; // Nombre del método QR (Yape, Plin, etc.)
    vehicle: 'Moto' | 'Coche' | 'Bicicleta';
    status: 'Activo' | 'Inactivo' | 'Pendiente' | 'Rechazado';
    commissionRate: number;
    debt: number;
    debtTransactions?: DebtTransaction[];
    coordinates?: Coordinate;
    documentImageUrl?: string;
    profileImageUrl?: string;
};

export type DebtTransaction = {
    id: string;
    date: string;
    type: 'debit' | 'credit';
    amount: number;
    description: string;
}

export type Message = {
    id: string;
    orderId: string;
    senderType: 'customer' | 'driver';
    senderName: string;
    text: string;
    timestamp: string;
};

export type BankAccount = {
  id: string;
  bankName: string;
  accountNumber: string;
  accountHolder: string;
  country: string;
};

export type QRPayment = {
  id: string;
  name: string;
  qrImageUrl: string;
  instructions: string;
};

export type PaymentGatewaySettings = {
  provider: 'stripe' | 'mercadopago' | 'custom' | 'none';
  publicKey: string;
  secretKey: string;
  enabled: boolean;
};

export type VerificationMethods = {
    email: boolean;
    sms: boolean;
    whatsapp: boolean;
}

export type ShippingSettings = {
    baseRadiusKm: number;
    baseFee: number;
    feePerKm: number;
}

export type Admin = {
    id: string;
    name: string;
    email: string;
    phone: string;
    permissions: string[]; // e.g., ['manage_orders', 'view_reports', 'manage_settings']
};

export type PromotionalBanner = {
  id: string;
  imageUrl: string;
  title: string;
  description: string;
  link: string;
  imageHint: string;
  locations?: string[]; // Array of city names. undefined or empty means global.
};

export type AppSettings = {
    appName: string;
    logoUrl: string;
    heroImageUrl: string;
    welcomeImageUrl: string;
    driverWelcomeImageUrl: string;
    loginBackgroundImageUrl?: string;
    hideFooter?: boolean;
    googleClientId?: string;
    firebaseConfig?: {
      apiKey: string;
      authDomain: string;
      projectId: string;
      storageBucket?: string;
    };
    taxType: 'gravada' | 'exonerada' | 'inafecta';
    taxRate: number; // e.g., 18 for 18%
    taxExemptRegions: string[]; // List of regions exempt from tax
    currencySymbol: string;
    verificationMethods: VerificationMethods;
    enablePasswordRecovery: boolean;
    featuredStoreCost: number;
    customDomain: string;
    paymentMethods: {
      bankAccounts: BankAccount[];
      qrPayments: QRPayment[];
      gateway?: PaymentGatewaySettings;
      cashOnDeliveryEnabled: boolean;
    },
    shipping: ShippingSettings;
    promotionalBanners: PromotionalBanner[];
    announcementBanners: PromotionalBanner[];
};

export type OrderItem = {
    productId: string;
    productName: string;
    vendor: string;
    quantity: number;
    price: number; // This is the base price (regular or offer) at the time of order
    costPrice: number;
    payoutStatus: 'pending' | 'paid'; // New field for payout tracking
    options?: {
        cutlery?: boolean;
        drink?: string;
        cutleryPrice?: number;
        cutleryCostPrice?: number;
        drinkPrice?: number;
        drinkCostPrice?: number;
    }
};
  
export type Order = {
    id: string;
    date: string;
    customerName: string;
    customerPhone: string; // Teléfono del cliente para contacto
    customerAddress: string;
    customerCoordinates: Coordinate;
    status: 'Procesando' | 'Listo para Recoger' | 'Esperando Aceptación' | 'Enviado' | 'Entregado' | 'Cancelado';
    total: number;
    shippingFee: number;
    items: OrderItem[];
    driverId?: string;
    paymentMethod: string;
    driverPayoutStatus?: 'pending' | 'paid';
    verificationCode: string;
};

export type Favor = {
    id: string;
    date: string;
    userName: string;
    description: string;
    pickupAddress: string;
    deliveryAddress: string;
    pickupLocation: Coordinate;
    deliveryLocation: Coordinate;
    estimatedProductCost: number;
    photoDataUri?: string;
    quote: QuoteFavorOutput;
    status: 'Pendiente' | 'Aceptado' | 'En Camino' | 'Completado' | 'Cancelado';
    driverId?: string;
};


// --- Centralized Data Store Type ---
export type AppData = {
  categories: Category[];
  vendors: Vendor[];
  users: User[];
  drivers: DeliveryDriver[];
  messages: Message[];
  appSettings: AppSettings;
  orders: Order[];
  favors: Favor[];
  cities: City[];
  deliveryZones: DeliveryZone[];
  admins: Admin[];
  currentUser?: any; // Intentionally left flexible for runtime session management
};

// This is the master, read-only data for initialization.
export const initialData: AppData = {
  cities: [
    { id: 'city1', name: 'Lima', coordinates: { lat: -12.046374, lng: -77.042793 }, sectors: [{ name: 'Zona A', fee: 5 }, { name: 'Zona B', fee: 7.5 }] },
    { id: 'city2', name: 'Arequipa', coordinates: { lat: -16.409047, lng: -71.537451 }, sectors: [{ name: 'Centro', fee: 4 }, { name: 'Yanahuara', fee: 6 }] },
    { id: 'city3', name: 'Cusco', coordinates: { lat: -13.518333, lng: -71.978058 }, sectors: [{ name: 'Centro Histórico', fee: 5 }, { name: 'Wanchaq', fee: 4.5 }] },
  ],
  deliveryZones: [
      { id: 'zone1', name: 'Miraflores', cityId: 'city1', path: [
          {lat: -12.115, lng: -77.035},
          {lat: -12.135, lng: -77.035},
          {lat: -12.135, lng: -77.015},
          {lat: -12.115, lng: -77.015},
      ], shippingFee: 5.00 },
  ],
  categories: [],
  vendors: [
    { id: '1', name: 'Farmacia Universal', email: 'vendor@mercadolisto.com', phone: '987654321', dni: '20556677881', bankAccount: { bankName: 'BCP', accountNumber: '123-456-789', accountHolder: 'Farmacia Universal SAC' }, paymentMethod: 'bank', description: 'La mejor pizza italiana de la ciudad.', category: 'Farmacia', imageUrl: 'https://placehold.co/400x300/4CAF50/FFFFFF?text=FU', bannerUrl: '/placeholder-images/pharmacy_banner_1.png', address: 'Av. Larco 743, Miraflores', location: 'Lima', coordinates: { lat: -12.1214, lng: -77.0282 }, status: 'Activo',
      productCategories: [],
      products: [], isFeatured: false, commissionRate: 15 },
    { id: '2', name: 'Plaza Vea', email: 'contact@burgerpalace.com', phone: '912345678', dni: '20556677992', bankAccount: { bankName: 'Interbank', accountNumber: '987-654-321', accountHolder: 'Supermercados Peruanos S.A.' }, paymentMethod: 'bank', description: 'Hamburguesas jugosas y batidos cremosos.', category: 'Supermercado', imageUrl: 'https://placehold.co/400x300/F44336/FFFFFF?text=PV', bannerUrl: '/placeholder-images/supermarket_banner_1.png', address: 'Calle Las Begonias 450, San Isidro', location: 'Lima', coordinates: { lat: -12.0889, lng: -77.0431 }, status: 'Activo',
      productCategories: [],
      products: [], isFeatured: false, commissionRate: 12.5 },
  ],
  users: [
    { id: 'u1', name: 'Juan Pérez', email: 'juan.perez@example.com', phone: '987654321', dni: '12345678', city: 'Lima', sector: 'Zona A', coordinates: { lat: -12.122, lng: -77.03 }, profileImageUrl: '' },
    { id: 'u2', name: 'Maria Garcia', email: 'maria.garcia@example.com', phone: '912345678', dni: '87654321', city: 'Arequipa', sector: 'Centro', profileImageUrl: '' },
  ],
  drivers: [
    { id: 'd1', name: 'Carlos Ruiz', email: 'driver@mercadolisto.com', dni: '12345678', phone: '987654321', bankAccount: '123-456-789012', paymentMethod: 'bank', vehicle: 'Moto', status: 'Activo', commissionRate: 80, debt: 0, debtTransactions: [], coordinates: { lat: -12.1, lng: -77.0 }, documentImageUrl: '', profileImageUrl: '' },
    { id: 'd2', name: 'Ana Gomez', email: 'ana.g@example.com', dni: '87654321', phone: '912345678', bankAccount: '987-654-321098', paymentMethod: 'bank', vehicle: 'Bicicleta', status: 'Activo', commissionRate: 85, debt: 0, debtTransactions: [], coordinates: { lat: -12.2, lng: -77.1 }, documentImageUrl: '', profileImageUrl: '' },
    { id: 'd3', name: 'Luis Fernandez', email: 'luis.f@example.com', dni: '11223344', phone: '999888777', bankAccount: '456-789-012345', paymentMethod: 'bank', vehicle: 'Coche', status: 'Inactivo', commissionRate: 75, debt: 0, debtTransactions: [], coordinates: { lat: -12.3, lng: -77.2 }, documentImageUrl: '', profileImageUrl: '' },
  ],
  messages: [],
  appSettings: {
    appName: 'PakiiP',
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
    taxRate: 18, // IGV Peru
    taxExemptRegions: [],
    currencySymbol: 'S/.',
    verificationMethods: {
      email: true,
      sms: false,
      whatsapp: false,
    },
    enablePasswordRecovery: true,
    featuredStoreCost: 25.00,
    customDomain: '',
    paymentMethods: {
      bankAccounts: [
        { id: 'bcp1', bankName: 'BCP', accountHolder: 'MercadoListo SAC', accountNumber: '123-4567890-1-23', country: 'Perú' }
      ],
      qrPayments: [
        // Los administradores deben agregar métodos de pago QR (Yape, Plin, etc.) desde Admin > Configuración
        // El sistema permite subir imágenes QR reales, no usar placeholders
      ],
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
    promotionalBanners: [
      {
        id: 'promo1',
        imageUrl: '/placeholder-images/offers_banner.png',
        title: 'Ofertas',
        description: 'Aprovecha descuentos exclusivos.',
        link: '/offers',
        imageHint: 'special offer'
      },
      {
        id: 'promo2',
        imageUrl: '/placeholder-images/favors_banner.png',
        title: 'Pide un Favor',
        description: 'Lo conseguimos y te lo llevamos.',
        link: '/ask-a-favor',
        imageHint: 'delivery person package'
      }
    ],
    announcementBanners: [
        {
          id: 'anno1',
          imageUrl: 'https://placehold.co/600x600/3498db/ffffff?text=Nuevos+Horarios',
          title: 'Nuevos Horarios de Atención',
          description: 'Ahora hasta las 11 PM.',
          link: '#',
          imageHint: 'clock announcement',
          locations: [], // Global
        },
        {
          id: 'anno2',
          imageUrl: 'https://placehold.co/600x600/e74c3c/ffffff?text=Llegamos+a+Arequipa',
          title: '¡Llegamos a Arequipa!',
          description: 'Encuentra tus tiendas locales.',
          link: '#',
          imageHint: 'city skyline',
          locations: ['Arequipa'], // Specific to Arequipa
        },
        {
          id: 'anno3',
          imageUrl: 'https://placehold.co/600x600/2ecc71/ffffff?text=Promos+del+Mes',
          title: 'Promociones del Mes',
          description: 'Hasta 50% de descuento.',
          link: '#',
          imageHint: 'sale discount',
          locations: [], // Global
        }
      ]
  },
  orders: [],
  favors: [],
  admins: [],
};

    
