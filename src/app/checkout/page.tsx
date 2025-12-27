
"use client";

import { AuthGuard } from "@/components/AuthGuard";
import { useCart } from "@/hooks/use-cart";
import { useAppData } from "@/hooks/use-app-data";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import Image from "next/image";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage, FormDescription } from "@/components/ui/form";
import { CreditCard, Lock, Store, AlertTriangle, Wallet, LocateFixed, CheckCircle } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { cn, formatCurrency } from "@/lib/utils";
import React, { useState, useEffect, useRef } from "react";
import { calculateCartTotals } from "@/lib/business-logic";
import { Checkbox } from "@/components/ui/checkbox";
import { Coordinate } from "@/lib/placeholder-data";
import { AddressAutocomplete } from "@/components/AddressAutocomplete";
import { GeocodeResult, reverseGeocode } from "@/lib/google-geocoding";
import { useLocation } from "@/hooks/use-location";

const checkoutSchema = z.object({
  name: z.string().min(2, "El nombre es obligatorio"),
  phone: z.string().min(9, "El teléfono es obligatorio"),
  address: z.string().min(5, "La dirección es obligatoria"),
  location: z.object({
    lat: z.number(),
    lng: z.number(),
  }, { required_error: "Por favor, permite el acceso a tu ubicación para calcular el envío."}),
  paymentMethod: z.string({
    required_error: "Debes seleccionar un método de pago.",
  }),
  cardNumber: z.string().optional(),
  expiryDate: z.string().optional(),
  cvc: z.string().optional(),
  qrPaymentConfirmed: z.boolean().optional(),
}).superRefine((data, ctx) => {
    if (data.paymentMethod === 'card') {
        if (!data.cardNumber || !/^\d{16}$/.test(data.cardNumber)) {
            ctx.addIssue({ code: "custom", message: "El número de tarjeta debe tener 16 dígitos", path: ['cardNumber'] });
        }
        if (!data.expiryDate || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(data.expiryDate)) {
            ctx.addIssue({ code: "custom", message: "Fecha de vencimiento no válida (MM/AA)", path: ['expiryDate'] });
        }
        if (!data.cvc || !/^\d{3}$/.test(data.cvc)) {
            ctx.addIssue({ code: "custom", message: "El CVC debe tener 3 dígitos", path: ['cvc'] });
        }
    }
    if (data.paymentMethod.startsWith('qr_') && !data.qrPaymentConfirmed) {
        ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: "Por favor, confirma que has realizado el pago para continuar.",
            path: ["qrPaymentConfirmed"],
        });
    }
});


type CheckoutFormValues = z.infer<typeof checkoutSchema>;

// Helper to get address from coordinates using our improved reverse geocoding system
const getAddressFromCoordinates = async (coords: Coordinate, callback: (address: string) => void) => {
    try {
        // Usar sistema mejorado con 10 componentes de dirección y búsqueda de calles cercanas
        const result = await reverseGeocode(coords.lat, coords.lng);
        if (result) {
            // Usar formattedAddress (dirección completa con números, postal, etc.)
            callback(result.formattedAddress);
        } else {
            callback('No se pudo encontrar la dirección.');
        }
    } catch (error) {
        console.error('Error al obtener dirección:', error);
        callback('Error al obtener la dirección.');
    }
};

function CheckoutPageContent() {
    const { items: cartItems, dispatch: cartDispatch } = useCart();
    const { appSettings, addOrder, currentUser, cities, vendors, deliveryZones, getVendorById } = useAppData();
    const router = useRouter();
    const { toast } = useToast();

    // Usar el hook de ubicación para obtener la ubicación ya detectada en el header
    const { location: userLocation, isLoading: isLoadingLocation } = useLocation(cities, {
        autoRequest: false, // No solicitar automáticamente, ya debería estar cargada
        useIPFallback: true,
        enableCache: true,
    });

    const loggedInUser = currentUser;

    // Determinar el método de pago por defecto: primer QR disponible, sino efectivo, sino tarjeta
    // Los QR payments válidos son los que tienen nombre y imagen
    const qrPayments = (appSettings?.paymentMethods?.qrPayments || []).filter(qr => qr.name && qr.qrImageUrl);
    const hasGateway = appSettings?.paymentMethods?.gateway?.enabled;
    // cashOnDeliveryEnabled está en el root de appSettings, no dentro de paymentMethods
    const hasCash = appSettings?.cashOnDeliveryEnabled ?? appSettings?.paymentMethods?.cashOnDeliveryEnabled ?? true;

    const getDefaultPaymentMethod = () => {
        if (qrPayments.length > 0) return `qr_${qrPayments[0].id}`;
        if (hasCash) return 'cash';
        if (hasGateway) return 'card';
        return 'cash'; // Por defecto efectivo
    };

    const form = useForm<CheckoutFormValues>({
        resolver: zodResolver(checkoutSchema),
        defaultValues: {
            name: loggedInUser?.name || "",
            phone: (loggedInUser as any)?.phone || "",
            address: (loggedInUser as any)?.address || "",
            location: (loggedInUser as any)?.coordinates,
            paymentMethod: getDefaultPaymentMethod(),
            cardNumber: "",
            expiryDate: "",
            cvc: "",
            qrPaymentConfirmed: false,
        },
    });

    const paymentMethod = form.watch("paymentMethod");
    const selectedLocation = form.watch("location");
    // Detectar si ya tiene dirección pre-llenada
    const [addressDetected, setAddressDetected] = useState(!!(loggedInUser as any)?.address);
    // Ref para evitar re-llenar si el usuario ya editó manualmente
    const hasAutoFilledRef = useRef(false);

    // Pre-llenar automáticamente con la ubicación del header (si está disponible)
    useEffect(() => {
        // Solo auto-llenar si:
        // 1. Hay ubicación del hook (del header)
        // 2. No se ha auto-llenado antes
        // 3. No hay ubicación ya seleccionada en el form
        if (userLocation && !hasAutoFilledRef.current && !selectedLocation) {
            hasAutoFilledRef.current = true;

            const coords: Coordinate = {
                lat: userLocation.latitude,
                lng: userLocation.longitude,
            };

            form.setValue('location', coords, { shouldValidate: true });
            setAddressDetected(true);

            // Si ya tiene dirección del hook, usarla
            if (userLocation.address) {
                form.setValue('address', userLocation.address, { shouldValidate: true });
            }
        }
    }, [userLocation, selectedLocation, form]);

    // Usar la primera ciudad disponible para calcular el envío
    const selectedCityData = cities?.length > 0 ? cities[0] : undefined;

    const { subtotal, tax, shipping, total, additionalFees } = calculateCartTotals(cartItems, vendors, deliveryZones, selectedCityData, appSettings, selectedLocation);
    const selectedQrPayment = (appSettings?.paymentMethods?.qrPayments || []).find(p => `qr_${p.id}` === paymentMethod);
    
    if (cartItems.length === 0) {
        return (
            <div className="mx-auto px-2 sm:px-3 md:px-4 py-8 sm:py-10 md:py-12 text-center" style={{ maxWidth: '1600px' }}>
                 <AlertTriangle className="mx-auto h-10 w-10 sm:h-12 sm:w-12 md:h-14 md:w-14 text-destructive mb-3 sm:mb-4" />
                <h1 className="text-xl sm:text-2xl md:text-3xl font-bold">Tu carrito está vacío</h1>
                <p className="text-sm sm:text-base text-muted-foreground mt-2">Añade productos para poder finalizar tu compra.</p>
                <Button asChild className="mt-4 sm:mt-6 text-sm sm:text-base px-4 sm:px-6">
                    <a href="/">Volver al Inicio</a>
                </Button>
            </div>
        )
    }

    const onSubmit = async (data: CheckoutFormValues) => {
        if (!data.location) {
            toast({
                title: "Ubicación Requerida",
                description: "Por favor, marca tu ubicación de entrega en el mapa.",
                variant: "destructive"
            });
            return;
        }

        let paymentMethodName = 'Desconocido';
        if (data.paymentMethod === 'card') paymentMethodName = 'Tarjeta de Crédito';
        else if (data.paymentMethod === 'cash') paymentMethodName = 'Efectivo';
        else {
            const qrPayment = (appSettings?.paymentMethods?.qrPayments || []).find(p => `qr_${p.id}` === data.paymentMethod);
            if (qrPayment) paymentMethodName = qrPayment.name;
        }

        const newOrder = await addOrder({
            customerId: currentUser?.id, // Asociar el pedido al usuario logueado
            customerName: data.name,
            customerPhone: data.phone,
            total,
            shippingFee: shipping,
            items: cartItems.map(item => {
              const vendor = getVendorById(item.product.vendorId);
              // Si tiene variante, usar el precio de la variante
              const itemPrice = item.options?.variant?.price ?? item.product.offerPrice ?? item.product.price;
              return {
                productId: item.product.id,
                productName: item.product.name,
                vendor: vendor?.name || 'Desconocido',
                quantity: item.quantity,
                price: itemPrice,
                costPrice: item.product.costPrice,
                options: item.options,
                payoutStatus: 'pending',
                // Guardar información de la variante
                variantName: item.options?.variant?.name,
                variantPrice: item.options?.variant?.price,
              };
            }),
            paymentMethod: paymentMethodName,
            customerAddress: data.address,
            customerCoordinates: data.location
        });

        console.log("Pedido realizado:", newOrder);

        toast({
            title: "¡Pedido Realizado!",
            description: `Tu pedido ha sido realizado con éxito.`,
        });
        
        cartDispatch({ type: 'CLEAR_CART' });

        router.push(`/order/${newOrder.id}/receipt`);
    };

    const handleAllowLocation = () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLocation: Coordinate = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    form.setValue('location', userLocation, { shouldValidate: true });
                    setAddressDetected(true);
                    
                    getAddressFromCoordinates(userLocation, (address) => {
                        form.setValue('address', address, { shouldValidate: true });
                        toast({
                            title: "Ubicación y Dirección Obtenidas",
                            description: `Tu dirección se ha actualizado a: ${address}`,
                        });
                    });
                },
                () => {
                    toast({
                        title: "Error de Ubicación",
                        description: "No se pudo obtener tu ubicación. Asegúrate de haber concedido los permisos en tu navegador.",
                        variant: "destructive",
                    });
                }
            );
        } else {
            toast({
                title: "Navegador no compatible",
                description: "Tu navegador no soporta la geolocalización.",
                variant: "destructive",
            });
        }
    };
    
    return (
        <div className="mx-auto px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-8 lg:py-12" style={{ maxWidth: '1600px' }}>
            <h1 className="text-xl xs:text-2xl sm:text-3xl md:text-4xl font-bold font-headline mb-4 sm:mb-6 md:mb-8 text-center">Finalizar Compra</h1>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 md:gap-8 lg:gap-12">
                <div className="lg:col-span-1">
                    <Card>
                        <CardHeader className="p-3 sm:p-4 md:p-6">
                            <CardTitle className="text-lg sm:text-xl md:text-2xl">Detalles de Compra</CardTitle>
                        </CardHeader>
                        <CardContent className="p-3 sm:p-4 md:p-6">
                            <Form {...form}>
                                <form id="checkout-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 sm:space-y-5 md:space-y-6">
                                    <div>
                                        <h3 className="text-base sm:text-lg md:text-xl font-semibold mb-3 sm:mb-4">Contacto y Envío</h3>
                                        <div className="space-y-3 sm:space-y-4">
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                                <FormField control={form.control} name="name" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm sm:text-base">Nombre Completo</FormLabel>
                                                    <FormControl><Input placeholder="Juan Pérez" className="text-sm sm:text-base" {...field} /></FormControl>
                                                    <FormMessage className="text-xs sm:text-sm" />
                                                </FormItem>
                                                )} />
                                                <FormField control={form.control} name="phone" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm sm:text-base">Teléfono</FormLabel>
                                                    <FormControl><Input placeholder="987654321" type="tel" className="text-sm sm:text-base" {...field} /></FormControl>
                                                    <FormMessage className="text-xs sm:text-sm" />
                                                </FormItem>
                                                )} />
                                            </div>

                                            <FormField
                                                control={form.control}
                                                name="location"
                                                render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm sm:text-base">Ubicación de Entrega</FormLabel>
                                                    <FormControl>
                                                        <Button type="button" variant="outline" className="w-full text-sm sm:text-base px-3 sm:px-4 py-2 sm:py-2.5" onClick={handleAllowLocation}>
                                                            <LocateFixed className="mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Usar mi Ubicación Actual para el Envío
                                                        </Button>
                                                    </FormControl>
                                                    <FormDescription className="text-xs sm:text-sm">
                                                        {addressDetected && <span className="text-green-600 flex items-center gap-2"><CheckCircle className="h-3 w-3 sm:h-4 sm:w-4" /> ¡Ubicación detectada!</span>}
                                                    </FormDescription>
                                                     <FormMessage className="text-xs sm:text-sm" />
                                                </FormItem>
                                            )} />

                                            <FormField control={form.control} name="address" render={({ field }) => (
                                            <FormItem>
                                                <FormControl>
                                                    <AddressAutocomplete
                                                        label="Dirección de Entrega"
                                                        placeholder="Busca tu dirección de entrega..."
                                                        value={field.value}
                                                        onChange={field.onChange}
                                                        onSelectAddress={(result: GeocodeResult) => {
                                                            // Actualizar coordenadas automáticamente
                                                            form.setValue('location', result.coordinates, { shouldValidate: true });
                                                            setAddressDetected(true);
                                                        }}
                                                        required
                                                        id="address"
                                                        skipInitialSearch={addressDetected} // No buscar si ya viene pre-llenado
                                                    />
                                                </FormControl>
                                                <FormMessage className="text-xs sm:text-sm" />
                                            </FormItem>
                                            )} />
                                        </div>
                                    </div>
                                    
                                    <Separator />
                                    
                                    <FormField
                                        control={form.control}
                                        name="paymentMethod"
                                        render={({ field }) => (
                                        <FormItem className="space-y-2 sm:space-y-3">
                                            <FormLabel className="text-base sm:text-lg md:text-xl font-semibold">Método de Pago</FormLabel>
                                            <FormControl>
                                                <RadioGroup
                                                    onValueChange={field.onChange}
                                                    defaultValue={field.value}
                                                    className="grid grid-cols-1 gap-3 sm:gap-4"
                                                >
                                                    {appSettings.paymentMethods.gateway?.enabled && (
                                                        <FormItem>
                                                            <FormControl>
                                                                <RadioGroupItem value="card" id="card" className="peer sr-only" />
                                                            </FormControl>
                                                            <Label htmlFor="card" className="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-popover p-3 sm:p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary">
                                                                <CreditCard className="mb-2 sm:mb-3 h-5 w-5 sm:h-6 sm:w-6" />
                                                                <span className="text-sm sm:text-base">Tarjeta de Crédito/Débito</span>
                                                            </Label>
                                                        </FormItem>
                                                    )}

                                                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                                                        {hasCash && (
                                                            <FormItem>
                                                                <FormControl>
                                                                    <RadioGroupItem value="cash" id="cash" className="peer sr-only" />
                                                                </FormControl>
                                                                <Label htmlFor="cash" className="flex flex-col items-center justify-center rounded-md border-2 border-muted bg-popover p-3 sm:p-4 h-full hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary">
                                                                    <Wallet className="mb-1 sm:mb-2 h-4 w-4 sm:h-5 sm:w-5" />
                                                                    <span className="text-xs sm:text-sm text-center">Efectivo</span>
                                                                </Label>
                                                            </FormItem>
                                                        )}

                                                        {qrPayments.map(qr => (
                                                            <FormItem key={qr.id}>
                                                                <FormControl>
                                                                    <RadioGroupItem value={`qr_${qr.id}`} id={`qr_${qr.id}`} className="peer sr-only" />
                                                                </FormControl>
                                                                <Label htmlFor={`qr_${qr.id}`} className="flex flex-col items-center justify-center rounded-md border-2 border-muted bg-popover p-3 sm:p-4 h-full hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary">
                                                                    <Image src={qr.qrImageUrl} alt={qr.name} width={16} height={16} className="mb-1 sm:mb-2 sm:w-5 sm:h-5" sizes="(max-width: 640px) 16px, 20px"/>
                                                                    <span className="text-xs sm:text-sm text-center">{qr.name}</span>
                                                                </Label>
                                                            </FormItem>
                                                        ))}
                                                    </div>
                                                </RadioGroup>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                        )}
                                    />

                                    {hasGateway && paymentMethod === 'card' && (
                                        <div className="space-y-3 sm:space-y-4">
                                            <h3 className="text-base sm:text-lg md:text-xl font-semibold mb-3 sm:mb-4">Detalles de la Tarjeta</h3>
                                            <FormField control={form.control} name="cardNumber" render={({ field }) => (
                                                <FormItem>
                                                <FormLabel className="text-sm sm:text-base">Número de Tarjeta</FormLabel>
                                                <FormControl><Input placeholder="•••• •••• •••• ••••" className="text-sm sm:text-base" {...field} /></FormControl>
                                                <FormMessage className="text-xs sm:text-sm" />
                                                </FormItem>
                                            )} />
                                            <div className="grid grid-cols-2 gap-3 sm:gap-4">
                                                <FormField control={form.control} name="expiryDate" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm sm:text-base">Fecha de Vencimiento</FormLabel>
                                                    <FormControl><Input placeholder="MM/AA" className="text-sm sm:text-base" {...field} /></FormControl>
                                                    <FormMessage className="text-xs sm:text-sm" />
                                                </FormItem>
                                                )} />
                                                <FormField control={form.control} name="cvc" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm sm:text-base">CVC</FormLabel>
                                                    <FormControl><Input placeholder="123" className="text-sm sm:text-base" {...field} /></FormControl>
                                                    <FormMessage className="text-xs sm:text-sm" />
                                                </FormItem>
                                                )} />
                                            </div>
                                        </div>
                                    )}

                                    {selectedQrPayment && (
                                        <Card className="bg-muted/50 p-3 sm:p-4 space-y-3 sm:space-y-4">
                                            <CardTitle className="text-base sm:text-lg md:text-xl">Pagar con {selectedQrPayment.name}</CardTitle>
                                            <div className="text-center">
                                                <p className="text-sm sm:text-base text-muted-foreground">Monto a Pagar:</p>
                                                <p className="text-2xl sm:text-3xl md:text-4xl font-bold">{formatCurrency(total, appSettings.currencySymbol)}</p>
                                            </div>
                                            <div className="flex flex-col items-center gap-3 sm:gap-4">
                                                <Image src={selectedQrPayment.qrImageUrl} alt={`Código QR de ${selectedQrPayment.name}`} width={160} height={160} className="rounded-lg border-2 border-primary w-40 h-40 sm:w-48 sm:h-48 md:w-52 md:h-52" sizes="(max-width: 640px) 160px, (max-width: 768px) 192px, 208px" />
                                                <p className="text-center text-xs sm:text-sm text-muted-foreground">{selectedQrPayment.instructions}</p>
                                            </div>
                                             <FormField
                                                control={form.control}
                                                name="qrPaymentConfirmed"
                                                render={({ field }) => (
                                                    <FormItem className="flex flex-row items-start space-x-2 sm:space-x-3 space-y-0 rounded-md border p-3 sm:p-4 shadow-sm bg-background">
                                                    <FormControl>
                                                        <Checkbox
                                                            checked={field.value}
                                                            onCheckedChange={field.onChange}
                                                        />
                                                    </FormControl>
                                                    <div className="space-y-1 leading-none">
                                                        <FormLabel className="text-sm sm:text-base">He realizado el pago</FormLabel>
                                                        <FormDescription className="text-xs sm:text-sm">
                                                            Marca esta casilla para confirmar que has completado el pago.
                                                        </FormDescription>
                                                        <FormMessage className="text-xs sm:text-sm" />
                                                    </div>
                                                    </FormItem>
                                                )}
                                            />
                                        </Card>
                                    )}

                                </form>
                            </Form>
                        </CardContent>
                    </Card>
                </div>
                <div className="lg:col-span-1">
                <Card className="lg:sticky lg:top-24">
                    <CardHeader className="p-3 sm:p-4 md:p-6">
                    <CardTitle className="text-lg sm:text-xl md:text-2xl">Resumen del Pedido</CardTitle>
                    </CardHeader>
                    <CardContent className="p-3 sm:p-4 md:p-6">
                        <div className="space-y-3 sm:space-y-4">
                            {cartItems.map((item) => {
                                // Si tiene variante, usar el precio de la variante
                                const price = item.options?.variant?.price ?? item.product.offerPrice ?? item.product.price;
                                const vendor = getVendorById(item.product.vendorId);
                                return (
                                <div key={item.instanceId} className="flex justify-between items-start gap-2 sm:gap-3">
                                    <div className="flex items-center gap-2 sm:gap-3 md:gap-4 flex-1 min-w-0">
                                        <Image src={item.product.imageUrl} alt={item.product.name} width={40} height={40} className="rounded-md w-10 h-10 sm:w-12 sm:h-12 flex-shrink-0" sizes="(max-width: 640px) 40px, 48px" data-ai-hint="food item" />
                                        <div className="min-w-0 flex-1">
                                            <p className="font-medium text-sm sm:text-base truncate">
                                                {item.product.name}
                                                {item.options?.variant && (
                                                    <span className="text-primary"> ({item.options.variant.name})</span>
                                                )}
                                            </p>
                                            <p className="text-xs sm:text-sm text-muted-foreground">Cant: {item.quantity}</p>
                                            {item.options && (item.options.cutlery || item.options.drink) &&
                                                <div className="text-xs text-muted-foreground mt-1">
                                                    {item.options.cutlery && <span>+ Cubiertos</span>}
                                                    {item.options.cutlery && item.options.drink && <br/>}
                                                    {item.options.drink && <span>+ Bebida: {item.options.drink}</span>}
                                                </div>
                                            }
                                        </div>
                                    </div>
                                    <p className="font-medium text-sm sm:text-base flex-shrink-0">{formatCurrency(price * item.quantity, appSettings.currencySymbol, 'es-PE')}</p>
                                </div>
                                )
                            })}
                        </div>
                    <Separator className="my-3 sm:my-4" />
                    <div className="space-y-1.5 sm:space-y-2">
                        <div className="flex justify-between text-sm sm:text-base">
                        <p className="text-muted-foreground">Subtotal</p>
                        <p>{formatCurrency(subtotal, appSettings.currencySymbol)}</p>
                        </div>
                        {additionalFees > 0 && (
                            <div className="flex justify-between text-sm sm:text-base">
                                <p className="text-muted-foreground">Tarifas Adicionales</p>
                                <p>{formatCurrency(additionalFees, appSettings.currencySymbol)}</p>
                            </div>
                        )}
                        <div className="flex justify-between text-sm sm:text-base">
                            <p className="text-muted-foreground">
                                Impuesto (sobre comisión)
                            </p>
                            <p>{formatCurrency(tax, appSettings.currencySymbol)}</p>
                        </div>
                        <div className="flex justify-between text-sm sm:text-base">
                        <p className="text-muted-foreground">Envío</p>
                        <p className="text-right">{selectedLocation ? formatCurrency(shipping, appSettings.currencySymbol) : 'Detecta tu ubicación'}</p>
                        </div>
                    </div>
                    <Separator className="my-3 sm:my-4" />
                    <div className="flex justify-between font-bold text-base sm:text-lg md:text-xl">
                        <p>Total</p>
                        <p>{formatCurrency(total, appSettings.currencySymbol)}</p>
                    </div>
                    </CardContent>
                    <CardFooter className="p-3 sm:p-4 md:p-6">
                    <Button type="submit" form="checkout-form" className="w-full text-sm sm:text-base px-4 sm:px-6 py-2 sm:py-2.5" disabled={cartItems.length === 0 || !selectedLocation}>
                        <Lock className="mr-2 h-3 w-3 sm:h-4 sm:w-4" /> Realizar Pedido
                    </Button>
                    </CardFooter>
                </Card>
                </div>
            </div>
        </div>
    );
}


export default function CheckoutPage() {
    return (
        <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
            <React.Suspense fallback={<div>Cargando...</div>}>
                <CheckoutPageContent />
            </React.Suspense>
        </AuthGuard>
    )
}
