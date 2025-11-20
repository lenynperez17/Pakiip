

"use client";

import { Product } from '@/lib/placeholder-data';
import React, { createContext, useContext, useReducer, ReactNode, useMemo, useEffect, useRef } from 'react';
import { useAppData } from './use-app-data';
import { saveCartToBackend, loadCartFromBackend } from '@/lib/backend-adapter';

type CartItemOptions = {
    cutlery?: boolean;
    drink?: string;
};

type CartItem = {
  // A unique ID for this specific cart instance, combining product and options
  instanceId: string;
  productId: string;
  quantity: number;
  options?: CartItemOptions;
};

export type EnrichedCartItem = {
  product: Product;
  quantity: number;
  instanceId: string;
  options?: CartItemOptions;
};

type CartState = {
  items: CartItem[];
};

type AddItemPayload = {
    product: Product;
    options?: CartItemOptions;
}

type CartAction =
  | { type: 'ADD_ITEM'; payload: AddItemPayload }
  | { type: 'REMOVE_ITEM'; payload: { instanceId: string } }
  | { type: 'UPDATE_QUANTITY'; payload: { instanceId: string; quantity: number } }
  | { type: 'CLEAR_CART' }
  | { type: 'SET_STATE'; payload: CartState };

type CartContextValue = {
  items: EnrichedCartItem[];
  dispatch: React.Dispatch<CartAction>;
  totalItems: number;
  totalPrice: number;
};

const CartContext = createContext<CartContextValue | undefined>(undefined);

const createInstanceId = (productId: string, options?: CartItemOptions) => {
    const optionsString = options ? Object.entries(options).sort().join(',') : '';
    return `${productId}-${optionsString}`;
}

const cartReducer = (state: CartState, action: CartAction): CartState => {
  switch (action.type) {
    case 'ADD_ITEM': {
      const { product, options } = action.payload;
      const instanceId = createInstanceId(product.id, options);
      const existingItem = state.items.find(item => item.instanceId === instanceId);

      if (existingItem) {
        return {
          ...state,
          items: state.items.map(item =>
            item.instanceId === instanceId
              ? { ...item, quantity: item.quantity + 1 }
              : item
          ),
        };
      }
      return {
        ...state,
        items: [...state.items, { productId: product.id, quantity: 1, options, instanceId }],
      };
    }
    case 'REMOVE_ITEM':
      return {
        ...state,
        items: state.items.filter(item => item.instanceId !== action.payload.instanceId),
      };
    case 'UPDATE_QUANTITY': {
      if (action.payload.quantity <= 0) {
        return {
          ...state,
          items: state.items.filter(item => item.instanceId !== action.payload.instanceId),
        };
      }
      return {
        ...state,
        items: state.items.map(item =>
          item.instanceId === action.payload.instanceId
            ? { ...item, quantity: action.payload.quantity }
            : item
        ),
      };
    }
    case 'CLEAR_CART':
        return { ...state, items: [] };
    case 'SET_STATE':
        return action.payload;
    default:
      return state;
  }
};

const initialState: CartState = {
  items: [],
};

export const CartProvider = ({ children }: { children: ReactNode }) => {
  const [state, dispatch] = useReducer(cartReducer, initialState);
  const { getAllProducts, currentUser } = useAppData();
  const isLoadingCart = useRef(false);
  const hasLoadedCart = useRef(false);

  // 🔥 CARGAR CARRITO DE FIREBASE al montar/cambiar usuario
  useEffect(() => {
    async function loadCart() {
      if (!currentUser || isLoadingCart.current || hasLoadedCart.current) return;

      isLoadingCart.current = true;
      console.log('🛒 Cargando carrito de Firebase para usuario:', currentUser.id);

      try {
        const result = await loadCartFromBackend(currentUser.id);

        if (result.success && result.data?.items) {
          console.log(`✅ Carrito cargado: ${result.data.items.length} items`);
          dispatch({ type: 'SET_STATE', payload: { items: result.data.items } });
        } else {
          console.log('📦 Carrito vacío');
        }

        hasLoadedCart.current = true;
      } catch (error) {
        console.error('❌ Error cargando carrito:', error);
      } finally {
        isLoadingCart.current = false;
      }
    }

    loadCart();
  }, [currentUser]);

  // 🔥 GUARDAR CARRITO EN FIREBASE cada vez que cambia
  useEffect(() => {
    async function saveCart() {
      // No guardar si:
      // 1. No hay usuario autenticado
      // 2. Estamos cargando el carrito inicial
      // 3. Aún no hemos cargado el carrito desde Firebase
      if (!currentUser || isLoadingCart.current || !hasLoadedCart.current) return;

      console.log('💾 Guardando carrito en Firebase...');

      try {
        const result = await saveCartToBackend(currentUser.id, { items: state.items });

        if (result.success) {
          console.log(`✅ Carrito guardado: ${state.items.length} items`);
        } else {
          console.error('❌ Error guardando carrito:', result.error);
        }
      } catch (error) {
        console.error('❌ Error guardando carrito:', error);
      }
    }

    saveCart();
  }, [state.items, currentUser]);

  // 🧹 LIMPIAR carrito cuando el usuario cierra sesión
  useEffect(() => {
    if (!currentUser && hasLoadedCart.current) {
      console.log('🧹 Usuario cerró sesión, limpiando carrito local');
      dispatch({ type: 'CLEAR_CART' });
      hasLoadedCart.current = false;
    }
  }, [currentUser]);

  const { items, totalPrice, totalItems } = useMemo(() => {
    const allProducts = getAllProducts();

    const enriched = state.items.map(item => {
        const product = allProducts.find(p => p.id === item.productId);
        return {
            product,
            quantity: item.quantity,
            options: item.options,
            instanceId: item.instanceId
        };
    }).filter((item): item is EnrichedCartItem => !!item.product);

    const price = enriched.reduce((total, item) => {
        const itemPrice = item.product.offerPrice ?? item.product.price;
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
        return total + (itemPrice + optionsPrice) * item.quantity;
    }, 0);

    const count = enriched.reduce((total, item) => total + item.quantity, 0);

    return { items: enriched, totalPrice: price, totalItems: count };
  }, [state, getAllProducts]);

  const contextValue = {
    items,
    dispatch,
    totalItems,
    totalPrice,
  };

  return React.createElement(CartContext.Provider, { value: contextValue as CartContextValue }, children);
};

export const useCart = () => {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};
