
"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { PlusCircle, Settings2, Clock } from "lucide-react";
import type { DrinkOption, Product, Vendor } from "@/lib/placeholder-data";
import { useCart } from "@/hooks/use-cart";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { useAppData } from "@/hooks/use-app-data";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "./ui/dialog";
import { Checkbox } from "./ui/checkbox";
import { Label } from "./ui/label";
import { RadioGroup, RadioGroupItem } from "./ui/radio-group";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "./ui/tooltip";
import { Badge } from "./ui/badge";
import { cn } from "@/lib/utils";

// Countdown timer component
const CountdownTimer = () => {
  const calculateTimeLeft = () => {
    const now = new Date();
    const endOfDay = new Date(now);
    endOfDay.setHours(23, 59, 59, 999);

    const difference = endOfDay.getTime() - now.getTime();

    if (difference <= 0) {
      return { hours: '00', minutes: '00', seconds: '00' };
    }

    let hours = Math.floor((difference / (1000 * 60 * 60)) % 24);
    let minutes = Math.floor((difference / 1000 / 60) % 60);
    let seconds = Math.floor((difference / 1000) % 60);

    return {
      hours: hours.toString().padStart(2, '0'),
      minutes: minutes.toString().padStart(2, '0'),
      seconds: seconds.toString().padStart(2, '0'),
    };
  };

  const [timeLeft, setTimeLeft] = useState({
    hours: '00',
    minutes: '00',
    seconds: '00',
  });

  useEffect(() => {
    // Set initial time on client mount to avoid hydration mismatch
    setTimeLeft(calculateTimeLeft());

    const timer = setInterval(() => {
      setTimeLeft(calculateTimeLeft());
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  return (
     <div className="text-center">
        <p className="text-[10px] xs:text-xs font-semibold text-destructive">Termina en:</p>
        <div className="text-sm xs:text-base sm:text-lg font-mono font-bold tabular-nums tracking-wide text-yellow-400">
            <span>{timeLeft.hours}</span>:
            <span>{timeLeft.minutes}</span>:
            <span>{timeLeft.seconds}</span>
        </div>
    </div>
  );
};


export function ProductCard({ product }: {product: Product}) {
  const { dispatch } = useCart();
  const { toast } = useToast();
  const { appSettings: settings, getVendorById } = useAppData();

  const [isOptionsDialogOpen, setOptionsDialogOpen] = useState(false);
  const [wantsCutlery, setWantsCutlery] = useState(false);
  const [selectedDrink, setSelectedDrink] = useState<string | undefined>(undefined);
  const [imageLoaded, setImageLoaded] = useState(false);

  // Este check previene que el componente falle si no se pasa un producto
  if (!product) {
    return null;
  }

  const vendor = getVendorById(product.vendorId);

  const hasOptions = product.options && ((product.options.cutleryPrice && product.options.cutleryPrice > 0) || (product.options.drinks && product.options.drinks.length > 0));

  const handleAddToCart = () => {
    dispatch({ type: 'ADD_ITEM', payload: { product } });
    toast({
        title: "¡Añadido al carrito!",
        description: `${product.name} ha sido añadido a tu carrito.`,
    });
  };

  const handleAddWithOptions = () => {
    if (!hasOptions) {
        handleAddToCart();
        return;
    }
    
    dispatch({ 
        type: 'ADD_ITEM', 
        payload: { 
            product, 
            options: {
                cutlery: wantsCutlery,
                drink: selectedDrink,
            }
        } 
    });
    toast({
        title: "¡Añadido al carrito!",
        description: `${product.name} ha sido añadido con tus opciones.`,
    });
    setOptionsDialogOpen(false); // Close dialog after adding
    // Reset state for next time
    setWantsCutlery(false);
    setSelectedDrink(undefined);
  };

  const renderButton = () => {
    if (product.stock === 0) {
      return (
        <Button size="sm" disabled className="bg-gray-400 disabled:cursor-not-allowed w-full text-xs sm:text-sm">
          Agotado
        </Button>
      );
    }
    if (hasOptions) {
      return (
        <Dialog open={isOptionsDialogOpen} onOpenChange={setOptionsDialogOpen}>
            <DialogTrigger asChild>
                <Button size="sm" className="bg-primary/90 hover:bg-primary w-full text-xs sm:text-sm">
                     <PlusCircle className="mr-1.5 sm:mr-2 h-3.5 w-3.5 sm:h-4 sm:w-4" /> Añadir
                </Button>
            </DialogTrigger>
            <DialogContent className="p-4 sm:p-6">
                <DialogHeader>
                    <DialogTitle className="text-lg sm:text-xl md:text-2xl">{product.name}</DialogTitle>
                    <DialogDescription className="text-xs sm:text-sm">{product.description}</DialogDescription>
                </DialogHeader>
                <div className="space-y-3 sm:space-y-4 py-3 sm:py-4">
                    {product.options?.cutleryPrice && product.options.cutleryPrice > 0 && (
                        <div className="flex items-center space-x-2">
                            <Checkbox id="cutlery" checked={wantsCutlery} onCheckedChange={(checked) => setWantsCutlery(!!checked)} />
                            <Label htmlFor="cutlery" className="text-xs sm:text-sm">Incluir cubiertos (+{settings.currencySymbol}{product.options.cutleryPrice.toFixed(2)})</Label>
                        </div>
                    )}
                     {product.options?.drinks && product.options.drinks.length > 0 && (
                        <div className="space-y-2">
                             <Label className="text-xs sm:text-sm">Elige tu bebida</Label>
                             <RadioGroup value={selectedDrink} onValueChange={setSelectedDrink}>
                                {product.options.drinks.map((drink: DrinkOption) => (
                                    <div key={drink.name} className="flex items-center space-x-2">
                                        <RadioGroupItem value={drink.name} id={drink.name} />
                                        <Label htmlFor={drink.name} className="text-xs sm:text-sm">{drink.name} (+{settings.currencySymbol}{drink.price.toFixed(2)})</Label>
                                    </div>
                                ))}
                            </RadioGroup>
                        </div>
                    )}
                </div>
                <DialogFooter>
                    <Button onClick={handleAddWithOptions} className="text-xs sm:text-sm">Añadir al Carrito</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
      );
    }

    return (
      <Button size="sm" onClick={handleAddToCart} className="bg-primary/90 hover:bg-primary w-full text-xs sm:text-sm">
        <PlusCircle className="mr-1.5 sm:mr-2 h-3.5 w-3.5 sm:h-4 sm:w-4" /> Añadir
      </Button>
    );
  };
  
  // Logic for displaying prices
  let displayPrice, originalPrice;
  const hasSpecificOfferPrice = product.offerPrice && product.offerPrice > 0;

  if (hasSpecificOfferPrice) {
    displayPrice = product.offerPrice;
    originalPrice = product.price;
  } else if (product.isOffer) {
    displayPrice = product.price;
    originalPrice = product.price * 1.10; // Fake 10% markup for "original" price
  } else {
    displayPrice = product.price;
    originalPrice = null;
  }


  return (
    <Card className="flex flex-col text-center h-full group hover:shadow-lg transition-all duration-300">
        <CardHeader className="p-0 relative">
            {/* Logo del vendor en tooltip */}
            {vendor && (
            <TooltipProvider>
                <Tooltip>
                <TooltipTrigger asChild>
                    <Link
                      href={`/vendor/${vendor.id}`}
                      className="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 z-10 transition-transform hover:scale-110"
                      aria-label={`Ver tienda ${vendor.name}`}
                    >
                        <div className="relative w-8 h-8 xs:w-9 xs:h-9 sm:w-10 sm:h-10 md:w-12 md:h-12">
                          <Image
                              src={vendor.imageUrl}
                              alt={`Logo de ${vendor.name}`}
                              fill
                              className="rounded-full border-2 border-white shadow-lg object-cover"
                              sizes="(max-width: 640px) 32px, (max-width: 768px) 40px, 48px"
                              data-ai-hint="logo"
                          />
                        </div>
                    </Link>
                </TooltipTrigger>
                <TooltipContent>
                    <p className="text-[10px] xs:text-xs">{vendor.name}</p>
                </TooltipContent>
                </Tooltip>
            </TooltipProvider>
            )}

            {/* Badge de oferta */}
            {product.isOffer && (
              <div className="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 z-10">
                <Badge className="bg-red-500 text-white border-red-600 text-[9px] xs:text-[10px] px-1.5 xs:px-2 py-0.5 shadow-lg font-bold">
                  ¡OFERTA!
                </Badge>
              </div>
            )}

            {/* Imagen del producto con skeleton loader - CUADRADA */}
            <div className="aspect-square overflow-hidden rounded-t-lg relative bg-muted">
              {!imageLoaded && (
                <div className="absolute inset-0 animate-pulse bg-gradient-to-r from-muted via-muted-foreground/10 to-muted" />
              )}
              <Image
                src={product.imageUrl}
                alt={`Imagen de ${product.name}`}
                fill
                className={cn(
                  "object-cover w-full h-full transition-all duration-300 group-hover:scale-105",
                  imageLoaded ? "opacity-100" : "opacity-0"
                )}
                sizes="(max-width: 375px) 150px, (max-width: 640px) 200px, (max-width: 768px) 250px, (max-width: 1024px) 300px, 350px"
                onLoad={() => setImageLoaded(true)}
                data-ai-hint="food item"
              />

              {/* Overlay gradient en hover */}
              <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
            </div>
        </CardHeader>

        <CardContent className="p-2 xs:p-2.5 sm:p-3 flex-grow flex flex-col justify-center">
            <CardTitle className="font-headline text-xs xs:text-sm sm:text-base leading-tight line-clamp-2 min-h-[2rem] xs:min-h-[2.5rem] sm:min-h-[3rem]">
              {product.name}
            </CardTitle>
            {product.description && (
              <p className="text-[10px] xs:text-xs sm:text-sm text-muted-foreground mt-1 sm:mt-1.5 line-clamp-2 hidden sm:block">
                {product.description}
              </p>
            )}
        </CardContent>

        <CardFooter className="flex flex-col items-stretch gap-1.5 xs:gap-2 sm:gap-2.5 p-2 xs:p-2.5 sm:p-3 pt-0">
            <div className="flex flex-col items-center justify-end gap-1 xs:gap-1.5 sm:gap-2">
                <div className="text-center space-y-0.5">
                    {originalPrice && originalPrice > displayPrice && (
                        <p className="text-[10px] xs:text-xs font-normal text-muted-foreground line-through">
                          {settings.currencySymbol}{originalPrice.toFixed(2)}
                        </p>
                    )}
                    <p className="text-sm xs:text-base sm:text-lg md:text-xl font-bold text-primary">
                      {settings.currencySymbol}{displayPrice.toFixed(2)}
                    </p>
                </div>
                {product.isOffer && <CountdownTimer />}
            </div>

            {/* Stock warning */}
            {product.stock > 0 && product.stock <= 5 && (
              <p className="text-[10px] xs:text-xs text-orange-600 font-medium">
                ¡Solo quedan {product.stock}!
              </p>
            )}

            {renderButton()}
        </CardFooter>
    </Card>
  );
}
