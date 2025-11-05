

"use client";

import { Product } from '@/lib/placeholder-data';
import React, { createContext, useContext, useReducer, ReactNode, useMemo } from 'react';
import { useAppData } from './use-app-data';

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
  const { getAllProducts, getVendorById } = useAppData();

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
