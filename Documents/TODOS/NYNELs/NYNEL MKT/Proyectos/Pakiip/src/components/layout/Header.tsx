

"use client";

import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter } from "next/navigation";
import { cn } from "@/lib/utils";
import { ShoppingCart, User, Store, UserCog, Truck, LogIn, Sparkles, LogOut, Package, LayoutDashboard, HandHeart, MapPin, Loader2, Search, Navigation, Coins, Moon, Sun, UserPlus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger, SheetDescription } from "@/components/ui/sheet";
import { Input } from "@/components/ui/input";
import { CartSheetContent } from "@/components/CartSheet";
import { Logo } from "@/components/icons/Logo";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { useState, useEffect } from "react";
import { useAppData } from "@/hooks/use-app-data";
import { useCart } from "@/hooks/use-cart";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { useSidebar } from "../ui/sidebar";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";
import { useToast } from "@/hooks/use-toast";
import { reverseGeocode, searchPlaces, GeocodeResult } from "@/lib/google-geocoding";
import { RoleSwitcher } from "@/components/RoleSwitcher";
import { AddressAutocomplete } from "@/components/AddressAutocomplete";


// Componente AppLogo eliminado - header sin logo para diseño más limpio

export function Header() {
  const pathname = usePathname();
  const router = useRouter();
  const { totalItems } = useCart();
  const { appSettings: settings, currentUser, logout, cities, selectedCity, setSelectedCity } = useAppData();
  const { toast } = useToast();

  // Estados para la ubicación actual
  const [currentAddress, setCurrentAddress] = useState<string | null>(null);
  const [isLoadingLocation, setIsLoadingLocation] = useState(false);

  // Estados para el modal de búsqueda de ubicación
  const [locationSheetOpen, setLocationSheetOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [suggestions, setSuggestions] = useState<GeocodeResult[]>([]);
  const [isSearching, setIsSearching] = useState(false);

  // Estado para el modo oscuro
  const [isDarkMode, setIsDarkMode] = useState(false);

  // Cargar preferencia de tema desde localStorage al montar
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldBeDark = savedTheme === 'dark' || (!savedTheme && prefersDark);
    setIsDarkMode(shouldBeDark);
    if (shouldBeDark) {
      document.documentElement.classList.add('dark');
    }
  }, []);

  // Alternar modo oscuro
  const toggleDarkMode = () => {
    const newDarkMode = !isDarkMode;
    setIsDarkMode(newDarkMode);
    if (newDarkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  };

  // 🐛 DEBUG: Verificar que el Header tiene max-width aplicado
  console.log('🎨 [HEADER] Renderizando con diseño limitado a max-w-7xl');

  // Obtener ubicación actual al montar el componente
  useEffect(() => {
    // Pequeño delay para asegurar que el componente esté montado
    const timer = setTimeout(() => {
      getCurrentLocation();
    }, 100);

    return () => clearTimeout(timer);
  }, []);

  // Efecto para búsqueda con debounce
  useEffect(() => {
    if (!searchQuery) {
      setSuggestions([]);
      return;
    }

    const timeoutId = setTimeout(() => {
      searchAddress(searchQuery);
    }, 500); // 500ms de debounce

    return () => clearTimeout(timeoutId);
  }, [searchQuery]);

  // Función para buscar direcciones con Google Maps Geocoding API
  const searchAddress = async (query: string) => {
    if (query.length < 3) {
      setSuggestions([]);
      return;
    }

    setIsSearching(true);
    try {
      const results = await searchPlaces(query);
      setSuggestions(results);
    } catch (error) {
      console.error("Error buscando direcciones:", error);
      setSuggestions([]);
    } finally {
      setIsSearching(false);
    }
  };

  // Función para seleccionar una dirección de las sugerencias
  const selectLocation = (place: GeocodeResult) => {
    setCurrentAddress(place.formattedAddress);
    setSearchQuery("");
    setSuggestions([]);
    setLocationSheetOpen(false);
  };

  const getCurrentLocation = () => {
    setIsLoadingLocation(true);

    // Verificar si geolocalización está disponible
    if (!navigator.geolocation) {
      setIsLoadingLocation(false);
      setCurrentAddress("Lima, Perú");
      console.log('⚠️ Navegador no soporta geolocalización, usando ubicación por defecto');
      return;
    }

    // Intentar obtener ubicación con manejo robusto de errores
    try {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          try {
            const { latitude, longitude, accuracy } = position.coords;

            // LOG CRÍTICO: Mostrar precisión del GPS
            const precisionKm = (accuracy / 1000).toFixed(2);
            console.log('📍 Ubicación obtenida:', { latitude, longitude });
            console.log(`🎯 Precisión del GPS: ${accuracy.toFixed(0)} metros (${precisionKm} km)`);

            if (accuracy > 1000) {
              console.warn(`⚠️ ¡GPS IMPRECISO! El margen de error es de ${precisionKm} km. Considera usar búsqueda manual para mayor precisión.`);
            } else if (accuracy > 100) {
              console.log(`ℹ️ GPS con precisión media (${accuracy.toFixed(0)}m). La dirección puede no ser exacta.`);
            } else {
              console.log(`✅ GPS con buena precisión (${accuracy.toFixed(0)}m).`);
            }

            // Usar Google Maps Geocoding API
            const result = await reverseGeocode(latitude, longitude);

            if (result) {
              setCurrentAddress(result.formattedAddress);
              setIsLoadingLocation(false);
              console.log('✅ Dirección obtenida con Google Maps:', result.formattedAddress);
            } else {
              // Si falla la API, mostrar coordenadas
              const coordsText = `${latitude.toFixed(4)}°, ${longitude.toFixed(4)}°`;
              setCurrentAddress(coordsText);
              setIsLoadingLocation(false);
              console.log('⚠️ Error en Google Maps API, mostrando coordenadas:', coordsText);
            }
          } catch (err) {
            console.log('⚠️ Error procesando ubicación:', err);
            setCurrentAddress("Lima, Perú");
            setIsLoadingLocation(false);
          }
        },
        (error) => {
          // Manejo silencioso de errores - solo usar ubicación por defecto
          setIsLoadingLocation(false);
          setCurrentAddress("Lima, Perú");

          // Solo mostrar logs si no es error de permisos
          if (error?.code !== 1) {
            console.log('ℹ️ No se pudo obtener ubicación, usando Lima, Perú por defecto');
          }
        },
        {
          enableHighAccuracy: true,
          timeout: 8000,
          maximumAge: 300000
        }
      );
    } catch (err) {
      // Si hay algún error al llamar a geolocation, usar ubicación por defecto
      console.log('⚠️ Error al acceder a geolocalización:', err);
      setIsLoadingLocation(false);
      setCurrentAddress("Lima, Perú");
    }
  };

  const handleLogout = () => {
    logout();
    router.push('/');
  }

  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
  }

  const handleLoginRedirect = () => {
      router.push('/login');
  }

  const renderUserMenu = () => {
    if (currentUser) {
        let dashboardUrl = '/my-orders';
        let dashboardLabel = 'Mis Pedidos';
        let dashboardIcon = <Package className="mr-2 h-4 w-4" />;
        
        switch (currentUser.role) {
            case 'admin':
                dashboardUrl = '/admin/dashboard';
                dashboardLabel = 'Panel de Admin';
                dashboardIcon = <LayoutDashboard className="mr-2 h-4 w-4" />;
                break;
            case 'vendor':
                dashboardUrl = `/vendor/dashboard?vendorId=${currentUser.id}`;
                dashboardLabel = 'Panel de Tienda';
                dashboardIcon = <Store className="mr-2 h-4 w-4" />;
                break;
            case 'driver':
                 dashboardUrl = '/driver/dashboard';
                 dashboardLabel = 'Panel de Repartidor';
                 dashboardIcon = <Truck className="mr-2 h-4 w-4" />;
                 break;
        }

        return (
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel>
                    <p className="font-bold truncate">{currentUser.name}</p>
                    <p className="text-xs text-muted-foreground font-normal">{currentUser.email}</p>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                   <Link href={dashboardUrl} className="flex items-center cursor-pointer">
                    {dashboardIcon}
                    <span>{dashboardLabel}</span>
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={handleLogout} className="text-destructive cursor-pointer">
                    <LogOut className="mr-2 h-4 w-4" />
                    <span>Cerrar Sesión</span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        )
    }

    return (
        <DropdownMenuContent align="end" className="w-56">
            <DropdownMenuItem onClick={handleLoginRedirect} className="cursor-pointer">
               <LogIn className="mr-2 h-4 w-4" />
               <span>Iniciar Sesión / Registrarse</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    );
  }

  return (
    <header className="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm" data-version="v17-location-left-with-logs">
      <div className="mx-auto flex h-14 sm:h-16 items-center justify-between gap-1 xs:gap-2 sm:gap-3 md:gap-4 px-2 xs:px-3 sm:px-4 md:px-6 lg:px-8" style={{ maxWidth: '1600px' }}>
      <div className="flex items-center gap-1 xs:gap-2 sm:gap-3 md:gap-4 min-w-0 flex-shrink">
        {/* Espacio para logo futuro - por ahora vacío para diseño limpio */}
        <Link href="/" className="flex items-center min-w-0 flex-shrink" aria-label="Inicio">
            {/* Sin contenido - header minimalista */}
        </Link>

        {/* Current Location Display - MOVIDO A LA IZQUIERDA - Clickable to open search modal */}
        <Sheet open={locationSheetOpen} onOpenChange={setLocationSheetOpen}>
          <SheetTrigger asChild>
            <Button
              variant="ghost"
              className="flex items-center gap-1 xs:gap-1.5 sm:gap-2 text-xs xs:text-sm sm:text-base hover:bg-muted px-1 xs:px-2 sm:px-3 h-8 sm:h-10 min-w-0"
            >
              {isLoadingLocation ? (
                <>
                  <Loader2 className="h-3 w-3 xs:h-4 xs:w-4 animate-spin flex-shrink-0" />
                  <span className="truncate max-w-[60px] xs:max-w-[80px] sm:max-w-[100px] md:max-w-[140px] lg:max-w-[180px] text-[10px] xs:text-xs sm:text-sm">Obteniendo...</span>
                </>
              ) : (
                <>
                  <MapPin className="h-3 w-3 xs:h-4 xs:w-4 flex-shrink-0 text-primary" />
                  {/* Marquee solo en móvil, truncate en desktop */}
                  <div className="max-w-[60px] xs:max-w-[80px] sm:max-w-[140px] md:max-w-[180px] lg:max-w-[220px] overflow-hidden">
                    <div className="sm:truncate text-[10px] xs:text-xs sm:text-sm animate-marquee-mobile sm:animate-none whitespace-nowrap">
                      {currentAddress || "Ubicación"}
                    </div>
                  </div>
                </>
              )}
            </Button>
          </SheetTrigger>

          <SheetContent side="bottom" className="h-[90vh] sm:h-[500px]">
            <SheetHeader>
              <SheetTitle>Buscar ubicación</SheetTitle>
              <SheetDescription>
                Busca y selecciona tu ubicación o usa el GPS
              </SheetDescription>
            </SheetHeader>

            <div className="mt-6 space-y-4">
              {/* Botón para obtener ubicación GPS */}
              <Button
                onClick={() => {
                  getCurrentLocation();
                  setLocationSheetOpen(false);
                }}
                disabled={isLoadingLocation}
                className="w-full gap-2"
                variant="outline"
              >
                {isLoadingLocation ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <Navigation className="h-4 w-4" />
                )}
                Usar mi ubicación actual
              </Button>

              {/* Campo de búsqueda con Google Maps Places */}
              <AddressAutocomplete
                label=""
                placeholder="Buscar dirección, ciudad..."
                value={searchQuery}
                onChange={setSearchQuery}
                onSelectAddress={(result: GeocodeResult) => {
                  selectLocation(result);
                }}
                id="header-address-search"
              />
            </div>
          </SheetContent>
        </Sheet>
      </div>

      <div className="flex items-center gap-1 xs:gap-2 sm:gap-3 md:gap-4 flex-shrink-0">
        {/* Botón de monedas Pakiip */}
        <Button
          variant="ghost"
          size="icon"
          className="relative h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0"
          asChild
        >
          <Link href={currentUser ? "/rewards" : "/login"}>
            <div className="relative">
              <Coins className="h-4 w-4 sm:h-5 sm:w-5 text-amber-500" />
              <span className="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 flex h-3 w-3 sm:h-4 sm:w-4 items-center justify-center rounded-full bg-amber-500 text-[8px] sm:text-[10px] font-bold text-white">
                P
              </span>
            </div>
            <span className="sr-only">Monedas Pakiip</span>
          </Link>
        </Button>

        {/* Botón Agregar Rol - Solo visible si está autenticado */}
        {currentUser && (
          <Button
            variant="ghost"
            size="icon"
            className="relative h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0"
            asChild
          >
            <Link href="/add-role">
              <UserPlus className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
              <span className="sr-only">Agregar nuevo rol</span>
            </Link>
          </Button>
        )}

        {/* Botón de modo oscuro */}
        <Button
          variant="ghost"
          size="icon"
          onClick={toggleDarkMode}
          className="relative h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0"
        >
          {isDarkMode ? (
            <Sun className="h-4 w-4 sm:h-5 sm:w-5" />
          ) : (
            <Moon className="h-4 w-4 sm:h-5 sm:w-5" />
          )}
          <span className="sr-only">Alternar modo oscuro</span>
        </Button>

        {/* Role Switcher - Cambiar entre roles */}
        <RoleSwitcher />

        {/* Cart */}
        <Sheet>
          <SheetTrigger asChild>
            <Button variant="outline" size="icon" className="relative h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0">
                {totalItems > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 flex h-3 w-3 sm:h-4 sm:w-4 items-center justify-center rounded-full bg-primary text-[8px] sm:text-xs text-primary-foreground">
                        {totalItems}
                    </span>
                )}
              <ShoppingCart className="h-4 w-4 sm:h-5 sm:w-5" />
              <span className="sr-only">Alternar carrito de compras</span>
            </Button>
          </SheetTrigger>
          <SheetContent>
            <SheetHeader>
              <SheetTitle>Tu Carrito</SheetTitle>
            </SheetHeader>
            <CartSheetContent />
          </SheetContent>
        </Sheet>

        {/* User Menu */}
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" size="icon" className="rounded-full h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0">
              {currentUser ? (
                <Avatar className="h-7 w-7 sm:h-8 sm:w-8">
                    {currentUser.profileImageUrl && <AvatarImage src={currentUser.profileImageUrl} alt={currentUser.name} />}
                    <AvatarFallback className="text-xs sm:text-sm">{getInitials(currentUser.name)}</AvatarFallback>
                </Avatar>
              ) : (
                <User className="h-4 w-4 sm:h-5 sm:w-5" />
              )}
              <span className="sr-only">Menú de usuario</span>
            </Button>
          </DropdownMenuTrigger>
          {renderUserMenu()}
        </DropdownMenu>

      </div>
      </div>
    </header>
  );
}
