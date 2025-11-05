
"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { useRouter } from "next/navigation";
import { useAppData } from "@/hooks/use-app-data";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Vendor, VerificationMethods, Coordinate } from "@/lib/placeholder-data";
import { LocationPickerMap } from "@/components/LocationPickerMap";

const registerSchema = z.object({
  businessName: z.string().min(2, "El nombre del negocio es obligatorio"),
  contactEmail: z.string().email("Dirección de correo electrónico no válida"),
  phone: z.string().min(9, "El número de teléfono no es válido"),
  category: z.string({ required_error: "Debes seleccionar una categoría." }),
  city: z.string({ required_error: "Debes seleccionar una ubicación." }),
  address: z.string().optional(),
  coordinates: z.object({
    lat: z.number(),
    lng: z.number(),
  }, { required_error: "Por favor, marca la ubicación de tu tienda en el mapa."}),
  description: z.string().min(20, "Por favor, proporciona una breve descripción de tu negocio (al menos 20 caracteres)."),
  bankName: z.string().min(2, "El nombre del banco es obligatorio"),
  accountHolder: z.string().min(2, "El titular de la cuenta es obligatorio"),
  accountNumber: z.string().min(10, "El número de cuenta no es válido"),
  verificationMethod: z.string().optional(),
  verificationCode: z.string().optional(),
});

type RegisterFormValues = z.infer<typeof registerSchema>;

export default function VendorRegisterPage() {
    const { toast } = useToast();
    const router = useRouter();
    const { appSettings: settings, cities, saveVendor, categories, currentUser } = useAppData();

    const availableVerificationMethods = Object.entries(settings.verificationMethods)
        .filter(([, isEnabled]) => isEnabled)
        .map(([method]) => method as keyof VerificationMethods);

  // Pre-llenar email y teléfono si el usuario está logueado como customer
  const defaultEmail = currentUser?.role === 'customer' ? currentUser.email : "";
  const defaultPhone = currentUser?.role === 'customer' ? currentUser.phone : "";

  const form = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      businessName: "",
      contactEmail: defaultEmail,
      phone: defaultPhone,
      address: "",
      description: "",
      bankName: "",
      accountHolder: "",
      accountNumber: "",
      verificationMethod: availableVerificationMethods.length > 0 ? availableVerificationMethods[0] : undefined,
    },
  });

  const selectedVerificationMethod = form.watch("verificationMethod");
  
  const onLocationSet = (address: string, coords: Coordinate) => {
      form.setValue('address', address, {shouldValidate: true});
      form.setValue('coordinates', coords, { shouldValidate: true });
  }

  function onSubmit(data: RegisterFormValues) {
     const newVendor: Vendor = {
        id: `v${Date.now()}`,
        name: data.businessName,
        email: data.contactEmail,
        phone: data.phone,
        description: data.description,
        category: data.category,
        location: data.city,
        address: data.address || '',
        coordinates: data.coordinates,
        commissionRate: 15, // Default commission
        imageUrl: 'https://placehold.co/64x64.png',
        bannerUrl: 'https://placehold.co/1200x400.png',
        products: [],
        productCategories: [],
        isFeatured: false,
        status: 'pending',
        dni: '', // Add a field for DNI if needed
        bankAccount: {
            bankName: data.bankName,
            accountHolder: data.accountHolder,
            accountNumber: data.accountNumber
        }
    };
    saveVendor(newVendor);
    toast({
        title: "¡Registro Enviado!",
        description: "Tu solicitud está en revisión. Nos pondremos en contacto en breve.",
    });
    router.push("/");
  }

  const handleSendCode = () => {
      let methodText = "";
      if (selectedVerificationMethod === 'sms') methodText = "tu celular vía SMS";
      if (selectedVerificationMethod === 'whatsapp') methodText = "tu WhatsApp";
      if (selectedVerificationMethod === 'email') methodText = "tu correo electrónico";
      
      toast({
          title: "Código Enviado (Simulado)",
          description: `Hemos enviado un código de verificación a ${methodText}.`
      });
  }

  return (
    <div className="mx-auto px-2 sm:px-3 md:px-4 py-8 sm:py-12" style={{ maxWidth: '1600px' }}>
        <Card className="w-full max-w-sm xs:max-w-md sm:max-w-lg md:max-w-2xl mx-auto">
            <CardHeader className="p-4 sm:p-6">
                <CardTitle className="font-headline text-xl xs:text-2xl sm:text-3xl">Registra tu Tienda</CardTitle>
                <CardDescription className="text-xs sm:text-sm">
                    Únete a {settings.appName} y llega a más clientes. Completa el formulario para registrar tu tienda.
                </CardDescription>
            </CardHeader>
            <CardContent className="p-4 sm:p-6">
                <Form {...form}>
                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 sm:space-y-6 md:space-y-8">
                        <FormField
                            control={form.control}
                            name="businessName"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Nombre del Negocio</FormLabel>
                                    <FormControl>
                                        <Input placeholder="ej. Pizzeria Don Marco" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <FormField
                                control={form.control}
                                name="contactEmail"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Email de Contacto</FormLabel>
                                        <FormControl>
                                            <Input placeholder="contacto@tunegocio.com" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                             <FormField
                                control={form.control}
                                name="phone"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Teléfono de Contacto</FormLabel>
                                        <FormControl>
                                            <Input placeholder="987654321" type="tel" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                             <FormField
                                control={form.control}
                                name="category"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Categoría Principal del Negocio</FormLabel>
                                        <Select onValueChange={field.onChange} defaultValue={field.value}>
                                            <FormControl>
                                                <SelectTrigger className="h-10 sm:h-11 text-sm sm:text-base">
                                                    <SelectValue placeholder="Selecciona una categoría" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                {categories.map(cat => (
                                                    <SelectItem key={cat.id} value={cat.name}>{cat.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                            <FormField
                                control={form.control}
                                name="city"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Ubicación</FormLabel>
                                        <Select onValueChange={field.onChange} defaultValue={field.value}>
                                            <FormControl>
                                                <SelectTrigger className="h-10 sm:h-11 text-sm sm:text-base">
                                                    <SelectValue placeholder="Selecciona una ubicación" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                {cities.map(city => (
                                                    <SelectItem key={city.id} value={city.name}>{city.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>

                         <FormField
                            control={form.control}
                            name="coordinates"
                            render={({ field }) => (
                            <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Marca la Ubicación de tu Tienda</FormLabel>
                                    <FormControl>
                                        <div className="h-48 sm:h-64 w-full rounded-md border">
                                            <LocationPickerMap
                                                onLocationSelect={onLocationSet}
                                            />
                                        </div>
                                    </FormControl>
                                    <FormDescription className="text-xs sm:text-sm">
                                        Haz clic en el mapa para marcar la ubicación exacta de tu tienda.
                                    </FormDescription>
                                    <FormMessage />
                            </FormItem>
                        )} />

                        <FormField
                            control={form.control}
                            name="address"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Dirección del Local</FormLabel>
                                    <FormControl>
                                        <Input placeholder="La dirección detectada aparecerá aquí..." className="h-10 sm:h-11 text-sm sm:text-base" {...field} readOnly/>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />


                        <FormField
                            control={form.control}
                            name="description"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Descripción del Negocio</FormLabel>
                                    <FormControl>
                                        <Textarea placeholder="Cuéntanos qué hace que tu negocio sea especial..." className="text-sm sm:text-base" {...field} />
                                    </FormControl>
                                    <FormDescription className="text-xs sm:text-sm">
                                        Esto se mostrará a los clientes en tu página de tienda.
                                    </FormDescription>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        
                        <Card>
                            <CardHeader className="p-4 sm:p-6">
                                <CardTitle className="text-lg sm:text-xl">Información Bancaria</CardTitle>
                                <CardDescription className="text-xs sm:text-sm">Introduce los datos de la cuenta donde recibirás tus pagos.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 sm:space-y-4 p-4 sm:p-6">
                                <FormField
                                    control={form.control}
                                    name="bankName"
                                    render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-sm sm:text-base">Nombre del Banco</FormLabel>
                                            <FormControl>
                                                <Input placeholder="ej. BCP, Interbank" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                                <FormField
                                    control={form.control}
                                    name="accountHolder"
                                    render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-sm sm:text-base">Nombre del Titular de la Cuenta</FormLabel>
                                            <FormControl>
                                                <Input placeholder="ej. Juan Pérez" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                                <FormField
                                    control={form.control}
                                    name="accountNumber"
                                    render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-sm sm:text-base">Número de Cuenta</FormLabel>
                                            <FormControl>
                                                <Input placeholder="ej. 123-4567890-1-23" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                            </CardContent>
                        </Card>
                        
                       {availableVerificationMethods.length > 0 && (
                            <Card className="p-3 sm:p-4 bg-muted/50">
                                <FormLabel className="text-sm sm:text-base">Verificar Cuenta</FormLabel>
                                <FormField
                                    control={form.control}
                                    name="verificationMethod"
                                    render={({ field }) => (
                                        <FormItem className="space-y-2 sm:space-y-3 pt-2">
                                            <FormControl>
                                                <RadioGroup onValueChange={field.onChange} defaultValue={field.value} className="flex flex-col space-y-1">
                                                    {settings.verificationMethods.email && (
                                                        <FormItem className="flex items-center space-x-3 space-y-0">
                                                            <FormControl><RadioGroupItem value="email" /></FormControl>
                                                            <FormLabel className="font-normal text-xs sm:text-sm">Verificar por Correo Electrónico</FormLabel>
                                                        </FormItem>
                                                    )}
                                                    {settings.verificationMethods.sms && (
                                                        <FormItem className="flex items-center space-x-3 space-y-0">
                                                            <FormControl><RadioGroupItem value="sms" /></FormControl>
                                                            <FormLabel className="font-normal text-xs sm:text-sm">Verificar por SMS</FormLabel>
                                                        </FormItem>
                                                    )}
                                                     {settings.verificationMethods.whatsapp && (
                                                        <FormItem className="flex items-center space-x-3 space-y-0">
                                                            <FormControl><RadioGroupItem value="whatsapp" /></FormControl>
                                                            <FormLabel className="font-normal text-xs sm:text-sm">Verificar por WhatsApp</FormLabel>
                                                        </FormItem>
                                                    )}
                                                </RadioGroup>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />

                                {selectedVerificationMethod && (
                                    <FormField
                                        control={form.control}
                                        name="verificationCode"
                                        render={({ field }) => (
                                            <FormItem className="mt-3 sm:mt-4">
                                                <FormLabel className="text-sm sm:text-base">Código de Verificación</FormLabel>
                                                <div className="flex gap-2">
                                                    <FormControl>
                                                        <Input placeholder="123456" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                                    </FormControl>
                                                    <Button type="button" variant="secondary" className="h-10 sm:h-11 text-xs sm:text-sm px-3 sm:px-4 whitespace-nowrap" onClick={handleSendCode}>Enviar</Button>
                                                </div>
                                                <FormMessage />
                                            </FormItem>
                                        )}
                                    />
                                )}
                            </Card>
                        )}

                        <Button type="submit" className="w-full h-10 sm:h-11 text-sm sm:text-base bg-primary hover:bg-primary/90">
                            Enviar para Aprobación
                        </Button>
                    </form>
                </Form>
            </CardContent>
        </Card>
    </div>
  );
}
