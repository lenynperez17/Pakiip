

"use client";

import React from "react";
import Image from "next/image";
import Link from "next/link";
import { useCart } from "@/hooks/use-cart";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { ScrollArea } from "./ui/scroll-area";
import { Minus, Plus, Trash2, ShoppingCart } from "lucide-react";
import { SheetFooter, SheetClose } from "./ui/sheet";
import { useAppData } from "@/hooks/use-app-data";
import { Separator } from "./ui/separator";

export function CartSheetContent() {
  const { dispatch, totalItems, items: cartItems, totalPrice } = useCart();
  const { appSettings: settings } = useAppData();

  if (totalItems === 0) {
    return (
      <div className="flex flex-col items-center justify-center h-full text-center">
        <ShoppingCart className="h-16 w-16 text-muted-foreground mb-4" />
        <h3 className="text-xl font-semibold">Tu carrito está vacío</h3>
        <p className="text-muted-foreground mt-2">¡Añade algunos artículos deliciosos para empezar!</p>
        <SheetClose asChild>
            <Button asChild className="mt-4">
                <Link href="/">Explorar Tiendas</Link>
            </Button>
        </SheetClose>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-full">
      <ScrollArea className="flex-grow pr-4 -mr-4">
        <div className="flex flex-col gap-6 py-4">
          {cartItems.map((item, index) => {
            const price = item.product.offerPrice ?? item.product.price;
            let optionsPrice = 0;
            if (item.options?.cutlery && item.product.options?.cutleryPrice) {
                optionsPrice += item.product.options.cutleryPrice;
            }
            if (item.options?.drink) {
                const drinkOption = item.product.options?.drinks?.find(d => d.name === item.options.drink);
                if (drinkOption) {
                    optionsPrice += drinkOption.price;
                }
            }
            const totalItemPrice = (price + optionsPrice) * item.quantity;

            return (
              <React.Fragment key={item.instanceId}>
              <div className="flex items-start gap-4">
                  <div className="relative flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20">
                      <Image
                          src={item.product.imageUrl}
                          alt={item.product.name}
                          fill
                          className="rounded-md object-cover border"
                          sizes="(max-width: 640px) 64px, 80px"
                          data-ai-hint="food item"
                      />
                  </div>
                  <div className="flex-grow">
                      <p className="font-semibold leading-tight">{item.product.name}</p>
                      {item.options && (item.options.cutlery || item.options.drink) &&
                          <div className="text-xs text-muted-foreground mt-1">
                              {item.options.cutlery && <span>+ Cubiertos</span>}
                              {item.options.cutlery && item.options.drink && <br/>}
                              {item.options.drink && <span>+ Bebida: {item.options.drink}</span>}
                          </div>
                      }
                      <div className="flex items-center gap-2 mt-2">
                      <Button
                          variant="outline"
                          size="icon"
                          className="h-6 w-6"
                          onClick={() => dispatch({ type: 'UPDATE_QUANTITY', payload: { instanceId: item.instanceId, quantity: item.quantity - 1 } })}
                      >
                          <Minus className="h-4 w-4" />
                      </Button>
                      <Input readOnly value={item.quantity} className="h-6 w-10 text-center p-0"/>
                      <Button
                          variant="outline"
                          size="icon"
                          className="h-6 w-6"
                          onClick={() => dispatch({ type: 'ADD_ITEM', payload: { product: item.product, options: item.options } })}
                      >
                          <Plus className="h-4 w-4" />
                      </Button>
                      </div>
                  </div>
                  <div className="flex flex-col items-end gap-2">
                      <p className="font-semibold text-right">{settings.currencySymbol}{totalItemPrice.toFixed(2)}</p>
                      <Button
                      variant="ghost"
                      size="icon"
                      className="h-6 w-6 text-muted-foreground hover:text-destructive"
                      onClick={() => dispatch({ type: 'REMOVE_ITEM', payload: { instanceId: item.instanceId } })}
                      >
                      <Trash2 className="h-4 w-4" />
                      </Button>
                  </div>
              </div>
              {index < cartItems.length - 1 && <Separator />}
              </React.Fragment>
            )
          })}
        </div>
      </ScrollArea>
      <SheetFooter className="mt-auto pt-4 border-t">
        <div className="w-full space-y-4">
             <div className="flex justify-between font-semibold text-lg">
                <span>Subtotal:</span>
                <span>{settings.currencySymbol}{totalPrice.toFixed(2)}</span>
            </div>
            <p className="text-xs text-muted-foreground text-center">
                El envío y los impuestos se calcularán en la página de pago.
            </p>
            <SheetClose asChild>
                <Button asChild className="w-full">
                    <Link href="/checkout">Proceder al Pago</Link>
                </Button>
            </SheetClose>
            <SheetClose asChild>
                <Button variant="outline" className="w-full">
                    Seguir Comprando
                </Button>
            </SheetClose>
        </div>
      </SheetFooter>
    </div>
  );
}
