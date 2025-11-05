
"use client";

import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { HandHeart, ImagePlus, Loader2, MapPin, Sparkles, Truck, Wallet, CheckCircle, LocateFixed } from 'lucide-react';
import { useAppData } from '@/hooks/use-app-data';
import { useToast } from '@/hooks/use-toast';
import type { Coordinate, Favor } from '@/lib/placeholder-data';
import { getDistanceFromLatLonInKm, formatCurrency } from '@/lib/utils';
import { quoteFavor, QuoteFavorOutput } from '@/ai/flows/quote-favor-flow';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { AuthGuard } from '@/components/AuthGuard';
import { AddressAutocomplete } from '@/components/AddressAutocomplete';
import { GeocodeResult, reverseGeocode } from '@/lib/google-geocoding';

const favorSchema = z.object({
  description: z.string().min(10, "Por favor, describe tu favor con más detalle."),
  pickupLocation: z.object({ lat: z.number(), lng: z.number() }, { required_error: "Marca la ubicación de recogida en el mapa." }),
  deliveryLocation: z.object({ lat: z.number(), lng: z.number() }, { required_error: "Marca la ubicación de entrega en el mapa." }),
  pickupAddress: z.string().optional(),
  deliveryAddress: z.string().optional(),
  estimatedProductCost: z.coerce.number().min(0, "El costo estimado no puede ser negativo.").optional().default(0),
  photoDataUri: z.string().optional(),
});

type FavorFormValues = z.infer<typeof favorSchema>;

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

function AskAFavorPageContent() {
  const { appSettings, currentUser, addFavor } = useAppData();
  const { toast } = useToast();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const [quote, setQuote] = useState<QuoteFavorOutput | null>(null);
  const [photoPreview, setPhotoPreview] = useState<string | null>(null);
  const [isSubmitted, setIsSubmitted] = useState(false);
  
  const [pickupAddress, setPickupAddress] = useState<string | null>(null);
  const [deliveryAddress, setDeliveryAddress] = useState<string | null>(null);


  const form = useForm<FavorFormValues>({
    resolver: zodResolver(favorSchema),
    defaultValues: {
      description: "",
      estimatedProductCost: 0,
    },
  });

  const pickupLocation = form.watch('pickupLocation');
  const deliveryLocation = form.watch('deliveryLocation');

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const result = reader.result as string;
        setPhotoPreview(result);
        form.setValue('photoDataUri', result);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleAllowLocation = (locationType: 'pickupLocation' | 'deliveryLocation') => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLocation: Coordinate = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                form.setValue(locationType, userLocation, { shouldValidate: true });

                getAddressFromCoordinates(userLocation, (address) => {
                    if (locationType === 'pickupLocation') {
                        setPickupAddress(address);
                        form.setValue('pickupAddress', address);
                    } else {
                        setDeliveryAddress(address);
                        form.setValue('deliveryAddress', address);
                    }
                    toast({
                        title: `Ubicación de ${locationType === 'pickupLocation' ? 'Recogida' : 'Entrega'} Obtenida`,
                        description: address,
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

  async function onSubmit(data: FavorFormValues) {
    setIsLoading(true);
    setQuote(null);
    try {
      const distance = getDistanceFromLatLonInKm(
        data.pickupLocation.lat,
        data.pickupLocation.lng,
        data.deliveryLocation.lat,
        data.deliveryLocation.lng
      );

      const result = await quoteFavor({
        description: data.description,
        estimatedProductCost: data.estimatedProductCost,
        distanceKm: distance,
        photoDataUri: data.photoDataUri,
      });
      
      const newFavor: Omit<Favor, 'id' | 'date'> = {
          userName: currentUser?.name || 'Invitado',
          pickupAddress: data.pickupAddress || 'Ubicación en mapa',
          deliveryAddress: data.deliveryAddress || 'Ubicación en mapa',
          ...data,
          quote: result,
          status: 'Pendiente',
      }
      
      addFavor(newFavor);
      setQuote(result);
      setIsSubmitted(true);
      toast({
          title: "¡Solicitud Enviada!",
          description: "Hemos recibido tu favor. Un administrador lo revisará y asignará un repartidor en breve.",
      });

    } catch (error) {
      console.error("Error getting quote:", error);
      toast({
        title: "Error al Cotizar",
        description: "No se pudo obtener una cotización en este momento. Inténtalo de nuevo.",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  }

  if (isSubmitted) {
    return (
        <div className="mx-auto px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-12 text-center" style={{ maxWidth: '1600px' }}>
            <Card>
                <CardContent className="p-4 sm:p-6 md:p-10">
                    <CheckCircle className="h-12 w-12 sm:h-14 sm:w-14 md:h-16 md:w-16 text-green-500 mx-auto mb-3 sm:mb-4"/>
                    <h2 className="text-xl sm:text-2xl md:text-3xl font-bold font-headline mb-2">Solicitud Recibida</h2>
                    <p className="text-sm sm:text-base text-muted-foreground mb-4 sm:mb-6">Gracias por confiar en nosotros. Tu favor ha sido enviado a nuestro equipo para su revisión. Te notificaremos cuando un repartidor sea asignado.</p>
                     {quote && (
                        <Card className="mt-4 sm:mt-6 bg-muted/50 text-left">
                            <CardHeader className="p-3 sm:p-4 md:p-6">
                                <CardTitle className="text-base sm:text-lg">Resumen de tu Cotización</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 sm:space-y-3 text-xs sm:text-sm p-3 sm:p-4 md:p-6">
                                <div className="flex justify-between items-center gap-2">
                                    <span className="text-muted-foreground">Tarifa de Servicio:</span>
                                    <span className="font-semibold">{formatCurrency(quote.serviceFee, appSettings.currencySymbol)}</span>
                                </div>
                                <div className="flex justify-between items-center gap-2">
                                    <span className="text-muted-foreground">Tarifa de Envío:</span>
                                    <span className="font-semibold">{formatCurrency(quote.shippingFee, appSettings.currencySymbol)}</span>
                                </div>
                                <div className="border-t pt-2 mt-2 flex justify-between items-center font-bold gap-2">
                                    <span>Total del Servicio:</span>
                                    <span>{formatCurrency(quote.totalFee, appSettings.currencySymbol)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    )}
                    <Button onClick={() => router.push('/')} className="mt-6 sm:mt-8 h-9 sm:h-10 text-sm sm:text-base">Volver al Inicio</Button>
                </CardContent>
            </Card>
        </div>
    )
  }

  return (
    <div className="mx-auto px-2 sm:px-3 md:px-4 py-4 sm:py-6 md:py-12" style={{ maxWidth: '1600px' }}>
      <section className="text-center mb-6 sm:mb-8 md:mb-12">
        <h1 className="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold font-headline tracking-tight flex items-center justify-center gap-2 sm:gap-3">
          <HandHeart className="h-6 w-6 sm:h-8 sm:w-8 md:h-10 md:w-10 text-primary" />
          Pide un Favor
        </h1>
        <p className="mt-3 sm:mt-4 text-sm sm:text-base md:text-lg text-muted-foreground max-w-2xl mx-auto px-2">
          ¿Necesitas que compremos algo por ti o recojamos un paquete? Descríbelo aquí y te daremos una cotización al instante.
        </p>
      </section>

      <Card>
        <CardHeader className="p-3 sm:p-4 md:p-6">
          <CardTitle className="text-lg sm:text-xl">Detalles de tu Favor</CardTitle>
          <CardDescription className="text-xs sm:text-sm">Completa el formulario para obtener una cotización de nuestro servicio.</CardDescription>
        </CardHeader>
        <CardContent className="p-3 sm:p-4 md:p-6">
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 sm:space-y-6 md:space-y-8">
              <FormField
                control={form.control}
                name="description"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-sm sm:text-base">1. Describe tu favor</FormLabel>
                    <FormControl>
                      <Textarea placeholder="Ej: Comprar un cargador de iPhone en la tienda iShop de Real Plaza y llevarlo a mi oficina." rows={4} {...field} className="text-sm sm:text-base resize-none" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-6">
                  <FormField
                    control={form.control}
                    name="photoDataUri"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-sm sm:text-base">Foto de Referencia (Opcional)</FormLabel>
                        <FormControl>
                           <div className="flex items-center justify-center w-full">
                                <label htmlFor="dropzone-file" className="flex flex-col items-center justify-center w-full h-24 sm:h-28 md:h-32 border-2 border-dashed rounded-lg cursor-pointer bg-muted hover:bg-muted/80 transition-colors">
                                    {photoPreview ? (
                                        <Image src={photoPreview} alt="Vista previa" width={100} height={100} className="object-contain h-full py-2"/>
                                    ) : (
                                        <div className="flex flex-col items-center justify-center pt-3 pb-4 sm:pt-5 sm:pb-6">
                                            <ImagePlus className="w-6 h-6 sm:w-8 sm:h-8 mb-1 sm:mb-2 text-gray-500" />
                                            <p className="text-xs sm:text-sm text-gray-500 px-2 text-center">Haz clic para subir una foto</p>
                                        </div>
                                    )}
                                    <Input id="dropzone-file" type="file" className="hidden" accept="image/*" onChange={handleFileChange} />
                                </label>
                            </div>
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="estimatedProductCost"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-sm sm:text-base">Costo Estimado de Productos</FormLabel>
                        <div className="relative">
                            <Wallet className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <FormControl>
                              <Input type="number" step="0.01" placeholder="0.00" {...field} className="pl-9 h-9 sm:h-10 text-sm sm:text-base" />
                            </FormControl>
                        </div>
                        <FormDescription className="text-xs sm:text-sm">Si no necesitas comprar nada, déjalo en 0.</FormDescription>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
              </div>

               <div>
                 <h3 className="text-base sm:text-lg font-medium mb-3 sm:mb-4">2. Define la Ruta</h3>
                 <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-6">
                     <FormField
                        control={form.control}
                        name="pickupLocation"
                        render={({ field }) => (
                          <FormItem>
                             <FormLabel className="text-sm sm:text-base">Punto de Recogida</FormLabel>
                            <FormControl>
                                <Button type="button" variant="outline" className="w-full h-9 sm:h-10 text-xs sm:text-sm mb-2" onClick={() => handleAllowLocation('pickupLocation')}>
                                    <LocateFixed className="mr-1.5 sm:mr-2 h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    <span className="hidden sm:inline">Usar Ubicación Actual para Recogida</span>
                                    <span className="sm:hidden">Ubicación de Recogida</span>
                                </Button>
                            </FormControl>

                            {/* Campo de búsqueda manual con Google Maps */}
                            <div className="mt-2">
                              <p className="text-xs text-muted-foreground mb-2">O busca manualmente:</p>
                              <AddressAutocomplete
                                label=""
                                placeholder="Buscar dirección de recogida..."
                                value={pickupAddress || ""}
                                onChange={(value) => {
                                  setPickupAddress(value);
                                  form.setValue('pickupAddress', value);
                                }}
                                onSelectAddress={(result: GeocodeResult) => {
                                  setPickupAddress(result.fullAddress);
                                  form.setValue('pickupLocation', result.coordinates, { shouldValidate: true });
                                  form.setValue('pickupAddress', result.fullAddress);
                                  console.log('📍 Dirección de recogida:', result);
                                }}
                                id="pickup-address-search"
                              />
                            </div>

                             <FormDescription className="text-xs sm:text-sm mt-2">
                                {pickupAddress ? (
                                    <span className="flex items-center gap-1.5 sm:gap-2 text-green-600">
                                        <CheckCircle className="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0"/>
                                        <span className="break-words">{pickupAddress}</span>
                                    </span>
                                ) : (
                                    "Usa tu ubicación GPS o busca una dirección manualmente."
                                )}
                            </FormDescription>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                     <FormField
                        control={form.control}
                        name="deliveryLocation"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel className="text-sm sm:text-base">Punto de Entrega</FormLabel>
                            <FormControl>
                                <Button type="button" variant="outline" className="w-full h-9 sm:h-10 text-xs sm:text-sm mb-2" onClick={() => handleAllowLocation('deliveryLocation')}>
                                    <LocateFixed className="mr-1.5 sm:mr-2 h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    <span className="hidden sm:inline">Usar Ubicación Actual para Entrega</span>
                                    <span className="sm:hidden">Ubicación de Entrega</span>
                                </Button>
                            </FormControl>

                            {/* Campo de búsqueda manual con Google Maps */}
                            <div className="mt-2">
                              <p className="text-xs text-muted-foreground mb-2">O busca manualmente:</p>
                              <AddressAutocomplete
                                label=""
                                placeholder="Buscar dirección de entrega..."
                                value={deliveryAddress || ""}
                                onChange={(value) => {
                                  setDeliveryAddress(value);
                                  form.setValue('deliveryAddress', value);
                                }}
                                onSelectAddress={(result: GeocodeResult) => {
                                  setDeliveryAddress(result.fullAddress);
                                  form.setValue('deliveryLocation', result.coordinates, { shouldValidate: true });
                                  form.setValue('deliveryAddress', result.fullAddress);
                                  console.log('📍 Dirección de entrega:', result);
                                }}
                                id="delivery-address-search"
                              />
                            </div>

                             <FormDescription className="text-xs sm:text-sm mt-2">
                                {deliveryAddress ? (
                                    <span className="flex items-center gap-1.5 sm:gap-2 text-green-600">
                                        <CheckCircle className="h-3.5 w-3.5 sm:h-4 sm:w-4 flex-shrink-0"/>
                                        <span className="break-words">{deliveryAddress}</span>
                                    </span>
                                ) : (
                                    "Usa tu ubicación GPS o busca una dirección manualmente."
                                )}
                            </FormDescription>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                 </div>
               </div>

              <div className="flex justify-center pt-2 sm:pt-4">
                <Button type="submit" size="lg" disabled={isLoading} className="w-full sm:w-auto h-10 sm:h-11 text-sm sm:text-base">
                  {isLoading ? (
                    <><Loader2 className="mr-1.5 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5 animate-spin" /> Calculando y Enviando...</>
                  ) : (
                    <><Sparkles className="mr-1.5 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5" /> Cotizar y Enviar Favor</>
                  )}
                </Button>
              </div>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  );
}

export default function AskAFavorPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
      <AskAFavorPageContent />
    </AuthGuard>
  );
}
