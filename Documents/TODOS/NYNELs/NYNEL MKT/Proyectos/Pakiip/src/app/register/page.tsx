

"use client";

import Link from "next/link";
import Image from "next/image";
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { useRouter } from "next/navigation";
import { Logo } from "@/components/icons/Logo";
import { useAppData } from "@/hooks/use-app-data";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useState } from "react";
import { CitySector, User, VerificationMethods } from "@/lib/placeholder-data";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { LocationPickerMap } from "@/components/LocationPickerMap";

const registerSchema = z.object({
  name: z.string().min(2, "El nombre es obligatorio"),
  email: z.string().email("Dirección de correo electrónico no válida"),
  phone: z.string().min(1, "El teléfono es obligatorio"),
  dni: z.string().min(1, "El DNI es obligatorio"),
  city: z.string({ required_error: "Debes seleccionar una ubicación." }),
  sector: z.string({ required_error: "Debes seleccionar un sector." }),
  password: z.string().min(8, "La contraseña debe tener al menos 8 caracteres"),
  coordinates: z.object({
    lat: z.number(),
    lng: z.number(),
  }).optional(),
  verificationMethod: z.string().optional(),
  verificationCode: z.string().optional(),
});

type RegisterFormValues = z.infer<typeof registerSchema>;

function RegisterLogo() {
  const { appSettings: settings } = useAppData();
  if (settings.logoUrl) {
    return <Image src={settings.logoUrl} alt={settings.appName} width={40} height={40} className="mx-auto h-8 w-8 sm:h-10 sm:w-10 object-contain mb-2" sizes="(max-width: 640px) 32px, 40px" />
  }
  return <Logo className="mx-auto h-8 w-8 sm:h-10 sm:w-10 text-primary mb-2" />;
}

export default function RegisterPage() {
  const { toast } = useToast();
  const router = useRouter();
  const { appSettings: settings, cities, saveUser } = useAppData();
  const [availableSectors, setAvailableSectors] = useState<CitySector[]>([]);

  const availableVerificationMethods = Object.entries(settings.verificationMethods)
        .filter(([, isEnabled]) => isEnabled)
        .map(([method]) => method as keyof VerificationMethods);

  const form = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      dni: "",
      password: "",
      verificationMethod: availableVerificationMethods.length > 0 ? availableVerificationMethods[0] : undefined,
    },
  });

  const selectedCityName = form.watch("city");
  const selectedVerificationMethod = form.watch("verificationMethod");

  const handleCityChange = (cityName: string) => {
    form.setValue("city", cityName);
    const selectedCity = cities.find(c => c.name === cityName);
    setAvailableSectors(selectedCity?.sectors || []);
    form.setValue("sector", ""); // Reset sector on city change
  };


  function onSubmit(data: RegisterFormValues) {
    const newUser: User = {
        id: `u${Date.now()}`,
        name: data.name,
        email: data.email,
        phone: data.phone,
        dni: data.dni,
        city: data.city,
        sector: data.sector,
        coordinates: data.coordinates,
    };
    saveUser(newUser);

    toast({
        title: "¡Registro Exitoso!",
        description: "Tu cuenta ha sido creada. Ahora puedes iniciar sesión.",
    });
    router.push("/login");
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
    <div className="flex items-center justify-center min-h-[calc(100vh-10rem)] py-8 sm:py-12 mx-auto px-2 sm:px-3 md:px-4" style={{ maxWidth: '1600px' }}>
        <Card className="w-full max-w-sm xs:max-w-md sm:max-w-lg mx-auto">
            <CardHeader className="text-center p-4 sm:p-6">
                <RegisterLogo />
                <CardTitle className="font-headline text-xl xs:text-2xl sm:text-3xl">Crear una Cuenta</CardTitle>
                <CardDescription className="text-xs sm:text-sm">
                    Únete a {settings.appName} para empezar a ordenar.
                </CardDescription>
            </CardHeader>
            <CardContent className="p-4 sm:p-6">
                <Form {...form}>
                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 sm:space-y-6">
                        <FormField
                            control={form.control}
                            name="name"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Nombre Completo</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Juan Pérez" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <div className="grid grid-cols-1 xs:grid-cols-2 gap-3 sm:gap-4">
                             <FormField
                                control={form.control}
                                name="email"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Correo Electrónico</FormLabel>
                                        <FormControl>
                                            <Input placeholder="tu@ejemplo.com" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
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
                                        <FormLabel className="text-sm sm:text-base">Número de Teléfono</FormLabel>
                                        <FormControl>
                                            <Input placeholder="987654321" type="tel" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>
                         <div className="grid grid-cols-1 xs:grid-cols-2 gap-3 sm:gap-4">
                            <FormField
                                control={form.control}
                                name="dni"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">DNI</FormLabel>
                                        <FormControl>
                                            <Input placeholder="12345678" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                        </FormControl>
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
                                        <Select onValueChange={handleCityChange} defaultValue={field.value}>
                                            <FormControl>
                                                <SelectTrigger className="h-10 sm:h-11 text-sm sm:text-base">
                                                    <SelectValue placeholder="Selecciona" />
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
                            name="sector"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Sector</FormLabel>
                                    <Select
                                        onValueChange={field.onChange}
                                        defaultValue={field.value}
                                        disabled={!selectedCityName || availableSectors.length === 0}
                                    >
                                        <FormControl>
                                            <SelectTrigger className="h-10 sm:h-11 text-sm sm:text-base">
                                                <SelectValue placeholder="Selecciona tu sector" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            {availableSectors.map(sector => (
                                                <SelectItem key={sector.name} value={sector.name}>{sector.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="password"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Contraseña</FormLabel>
                                    <FormControl>
                                        <Input type="password" placeholder="••••••••" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                         <FormField
                            control={form.control}
                            name="coordinates"
                            render={({ field }) => (
                            <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Marca tu Ubicación Principal (Opcional)</FormLabel>
                                    <FormControl>
                                        <div className="h-48 sm:h-64 w-full rounded-md border">
                                            <LocationPickerMap
                                                onLocationSelect={(coords) => form.setValue('coordinates', coords, { shouldValidate: true })}
                                            />
                                        </div>
                                    </FormControl>
                                    <FormDescription className="text-xs sm:text-sm">
                                        Marcar tu ubicación aquí agilizará tus futuras compras.
                                    </FormDescription>
                                    <FormMessage />
                            </FormItem>
                        )} />

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


                        <Button type="submit" className="w-full h-10 sm:h-11 text-sm sm:text-base">
                            Registrarse
                        </Button>
                    </form>
                </Form>
                 <div className="mt-4 text-center text-xs sm:text-sm">
                    ¿Ya tienes una cuenta?{" "}
                    <Link href="/login" className="underline hover:text-primary">
                      Inicia Sesión
                    </Link>
                  </div>
                  <div className="mt-4 pt-4 border-t space-y-2">
                    <p className="text-center text-xs sm:text-sm text-muted-foreground font-medium">
                      ¿Quieres vender o repartir?
                    </p>
                    <div className="flex flex-col gap-2">
                      <Link href="/vendor/register">
                        <Button variant="outline" className="w-full h-10 sm:h-11 text-sm sm:text-base">
                          Registrar Tienda
                        </Button>
                      </Link>
                      <Link href="/driver/register">
                        <Button variant="outline" className="w-full h-10 sm:h-11 text-sm sm:text-base">
                          Ser Repartidor
                        </Button>
                      </Link>
                    </div>
                  </div>
            </CardContent>
        </Card>
    </div>
  );
}
