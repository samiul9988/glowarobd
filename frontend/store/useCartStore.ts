import { create } from "zustand";
import { persist } from "zustand/middleware";

export interface CartItem {
  id: number;
  product_id: number;
  product_name: string;
  product_thumbnail_image: string;
  price: number;
  quantity: number;
  variant?: {
    variation: Record<string, string>;
  };
}

interface CartState {
  // Cart UI state
  isOpen: boolean;
  setOpen: (open: boolean) => void;
  toggle: () => void;

  // Cart items
  cart: CartItem[];
  addToCart: (item: CartItem) => void;
  removeFromCart: (itemId: number) => void;
  updateQuantity: (itemId: number, quantity: number) => void;
  clearCart: () => void;

  // Computed values
  totalPrice: number;
  totalItems: number;
}

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      // Cart UI state
      isOpen: false,
      setOpen: (open) => set({ isOpen: open }),
      toggle: () => set((state) => ({ isOpen: !state.isOpen })),

      // Cart items
      cart: [],

      addToCart: (item) =>
        set((state) => {
          const existingItem = state.cart.find(
            (cartItem) => cartItem.id === item.id
          );

          if (existingItem) {
            return {
              cart: state.cart.map((cartItem) =>
                cartItem.id === item.id
                  ? { ...cartItem, quantity: cartItem.quantity + item.quantity }
                  : cartItem
              ),
            };
          }

          return { cart: [...state.cart, item] };
        }),

      removeFromCart: (itemId) =>
        set((state) => ({
          cart: state.cart.filter((item) => item.id !== itemId),
        })),

      updateQuantity: (itemId, quantity) =>
        set((state) => ({
          cart: state.cart.map((item) =>
            item.id === itemId ? { ...item, quantity } : item
          ),
        })),

      clearCart: () => set({ cart: [] }),

      // Computed values
      get totalPrice() {
        return get().cart.reduce(
          (total, item) => total + item.price * item.quantity,
          0
        );
      },

      get totalItems() {
        return get().cart.reduce((total, item) => total + item.quantity, 0);
      },
    }),
    {
      name: "cart-storage",
      partialize: (state) => ({ cart: state.cart }),
    }
  )
);
