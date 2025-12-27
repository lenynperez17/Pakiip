

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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { useRouter } from "next/navigation";
import { useAppData } from "@/hooks/use-app-data";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { DeliveryDriver, VerificationMethods, Coordinate } from "@/lib/placeholder-data";
import { LocationPickerMap } from "@/components/LocationPickerMap";
import { useState } from "react";
import Image from "next/image";
import { ImagePlus } from "lucide-react";

const registerSchema = z.object({
  name: z.string().min(2, "El nombre completo es obligatorio"),
  email: z.string().email("Dirección de correo electrónico no válida"),
  dni: z.string().min(8, "El DNI debe tener al menos 8 caracteres"),
  phone: z.string().min(9, "El número de celular no es válido"),
  city: z.string({ required_error: "Debes seleccionar una ubicación." }),
  address: z.string().optional(),
  vehicle: z.enum(["Moto", "Coche", "Bicicleta"], {
    required_error: "Debes seleccionar un tipo de vehículo.",
  }),
  bankAccount: z.string().min(10, "El número de cuenta bancaria no es válido").optional(),
   coordinates: z.object({
    lat: z.number(),
    lng: z.number(),
  }, { required_error: "Por favor, marca tu ubicación base en el mapa."}),
  documentImageUrl: z.string().optional(),
  profileImageUrl: z.string().optional(),
  verificationMethod: z.string().optional(),
  verificationCode: z.string().optional(),
});

type RegisterFormValues = z.infer<typeof registerSchema>;

export default function DriverRegisterPage() {
    const { toast } = useToast();
    const router = useRouter();
    const { appSettings: settings, cities, saveDriver, currentUser } = useAppData();
    const [documentPreview, setDocumentPreview] = useState<string | null>(null);
    const [profilePreview, setProfilePreview] = useState<string | null>(null);

    const availableVerificationMethods = Object.entries(settings.verificationMethods)
        .filter(([, isEnabled]) => isEnabled)
        .map(([method]) => method as keyof VerificationMethods);

  // Pre-llenar datos si el usuario está logueado como customer
  const defaultName = currentUser?.role === 'customer' ? currentUser.name : "";
  const defaultEmail = currentUser?.role === 'customer' ? currentUser.email : "";
  const defaultDni = currentUser?.role === 'customer' ? currentUser.dni : "";
  const defaultPhone = currentUser?.role === 'customer' ? currentUser.phone : "";

  const form = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: defaultName,
      email: defaultEmail,
      dni: defaultDni,
      phone: defaultPhone,
      bankAccount: "",
      verificationMethod: availableVerificationMethods.length > 0 ? availableVerificationMethods[0] : undefined,
    },
  });
  
  const selectedVerificationMethod = form.watch("verificationMethod");

  function onSubmit(data: RegisterFormValues) {
    const newDriver: DeliveryDriver = {
        id: `d${Date.now()}`,
        name: data.name,
        email: data.email,
        dni: data.dni,
        phone: data.phone,
        bankAccount: data.bankAccount,
        vehicle: data.vehicle,
        coordinates: data.coordinates,
        documentImageUrl: data.documentImageUrl,
        profileImageUrl: data.profileImageUrl,
        commissionRate: 80, // Default commission rate
        status: 'Pendiente',
        debt: 0,
    };
    saveDriver(newDriver);

    toast({
        title: "¡Solicitud de Registro Enviada!",
        description: "Tu solicitud está en revisión. Nos pondremos en contacto contigo en breve.",
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
  
  const onLocationSet = (address: string, coords: Coordinate) => {
      form.setValue('address', address, {shouldValidate: true});
      form.setValue('coordinates', coords, { shouldValidate: true });
  }

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, setPreview: React.Dispatch<React.SetStateAction<string | null>>, fieldName: "documentImageUrl" | "profileImageUrl") => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        const result = reader.result as string;
        setPreview(result);
        form.setValue(fieldName, result);
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="mx-auto px-2 sm:px-3 md:px-4 py-8 sm:py-12" style={{ maxWidth: '1600px' }}>
        <Card className="w-full max-w-sm xs:max-w-md sm:max-w-lg md:max-w-2xl mx-auto">
            <CardHeader className="p-4 sm:p-6">
                <CardTitle className="font-headline text-xl xs:text-2xl sm:text-3xl">Conviértete en Repartidor</CardTitle>
                <CardDescription className="text-xs sm:text-sm">
                    Únete a nuestro equipo en {settings.appName}. Completa el formulario para empezar.
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
                                        <Input placeholder="ej. Carlos Ruiz" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <FormField
                                control={form.control}
                                name="email"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Email de Contacto</FormLabel>
                                        <FormControl>
                                            <Input placeholder="tu@email.com" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                            <FormField
                                control={form.control}
                                name="dni"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">DNI</FormLabel>
                                        <FormControl>
                                            <Input placeholder="Tu número de DNI" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>
                         <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <FormField
                                control={form.control}
                                name="phone"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel className="text-sm sm:text-base">Celular</FormLabel>
                                        <FormControl>
                                            <Input placeholder="987654321" type="tel" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
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
                                        <FormLabel className="text-sm sm:text-base">Ubicación de Operación</FormLabel>
                                        <Select onValueChange={field.onChange} defaultValue={field.value}>
                                            <FormControl>
                                                <SelectTrigger className="h-10 sm:h-11 text-sm sm:text-base">
                                                    <SelectValue placeholder="Selecciona tu ubicación" />
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
                            name="vehicle"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Tipo de Vehículo</FormLabel>
                                    <Select onValueChange={field.onChange} defaultValue={field.value}>
                                        <FormControl>
                                            <SelectTrigger className="h-10 sm:h-11 text-sm sm:text-base">
                                                <SelectValue placeholder="Selecciona tu vehículo" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            <SelectItem value="Moto">Moto</SelectItem>
                                            <SelectItem value="Coche">Coche</SelectItem>
                                            <SelectItem value="Bicicleta">Bicicleta</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                         <FormField
                            control={form.control}
                            name="coordinates"
                            render={({ field }) => (
                            <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Marca tu Ubicación Base</FormLabel>
                                    <FormControl>
                                        <div className="h-48 sm:h-64 w-full rounded-md border">
                                            <LocationPickerMap
                                                onLocationSelect={onLocationSet}
                                            />
                                        </div>
                                    </FormControl>
                                    <FormDescription className="text-xs sm:text-sm">
                                        Esta será tu ubicación principal de operación.
                                    </FormDescription>
                                    <FormMessage />
                            </FormItem>
                        )} />

                         <FormField
                            control={form.control}
                            name="address"
                            render={({ field }) => (
                            <FormItem>
                                <FormLabel className="text-sm sm:text-base">Dirección Base Detectada</FormLabel>
                                <FormControl><Input placeholder="Tu dirección aparecerá aquí..." className="h-10 sm:h-11 text-sm sm:text-base" {...field} readOnly /></FormControl>
                                <FormMessage />
                            </FormItem>
                            )} />


                        <FormField
                            control={form.control}
                            name="bankAccount"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Cuenta Bancaria (para pagos)</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Tu número de cuenta" className="h-10 sm:h-11 text-sm sm:text-base" {...field} />
                                    </FormControl>
                                    <FormDescription className="text-xs sm:text-sm">
                                        Asegúrate que sea la cuenta correcta para recibir tus comisiones.
                                    </FormDescription>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                         <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <FormField
                                control={form.control}
                                name="profileImageUrl"
                                render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Foto de Perfil</FormLabel>
                                    <FormControl>
                                        <div className="flex items-center justify-center w-full">
                                            <label htmlFor="profile-file" className="flex flex-col items-center justify-center w-full h-28 sm:h-32 border-2 border-dashed rounded-lg cursor-pointer bg-muted hover:bg-muted/80">
                                                {profilePreview ? (
                                                    <Image src={profilePreview} alt="Vista previa" width={100} height={100} className="object-contain h-full py-2"/>
                                                ) : (
                                                    <div className="flex flex-col items-center justify-center pt-4 pb-5">
                                                        <ImagePlus className="w-6 h-6 sm:w-8 sm:h-8 mb-2 text-gray-500" />
                                                        <p className="mb-2 text-xs sm:text-sm text-center text-gray-500">Sube una foto tuya</p>
                                                    </div>
                                                )}
                                                <Input id="profile-file" type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, setProfilePreview, "profileImageUrl")} />
                                            </label>
                                        </div>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )} />
                             <FormField
                                control={form.control}
                                name="documentImageUrl"
                                render={({ field }) => (
                                <FormItem>
                                    <FormLabel className="text-sm sm:text-base">Foto de Documento (DNI/Licencia)</FormLabel>
                                    <FormControl>
                                        <div className="flex items-center justify-center w-full">
                                            <label htmlFor="document-file" className="flex flex-col items-center justify-center w-full h-28 sm:h-32 border-2 border-dashed rounded-lg cursor-pointer bg-muted hover:bg-muted/80">
                                                {documentPreview ? (
                                                    <Image src={documentPreview} alt="Vista previa" width={100} height={100} className="object-contain h-full py-2"/>
                                                ) : (
                                                    <div className="flex flex-col items-center justify-center pt-4 pb-5">
                                                        <ImagePlus className="w-6 h-6 sm:w-8 sm:h-8 mb-2 text-gray-500" />
                                                        <p className="mb-2 text-xs sm:text-sm text-center text-gray-500">Sube foto de tu documento</p>
                                                    </div>
                                                )}
                                                <Input id="document-file" type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, setDocumentPreview, "documentImageUrl")} />
                                            </label>
                                        </div>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )} />
                         </div>
                        
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
                            Enviar Solicitud
                        </Button>
                    </form>
                </Form>
            </CardContent>
        </Card>
    </div>
  );
}

    
