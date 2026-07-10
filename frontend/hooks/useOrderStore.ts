"use client";

import { create } from "zustand";
import { persist } from "zustand/middleware";

interface OrderState {
  orders: string[];
  addOrder: (orderId: string) => void;
  exists: (orderId: string) => boolean;
  clearOrders: () => void;
}

export const useOrderStore = create<OrderState>()(
  persist(
    (set, get) => ({
      orders: [],

      addOrder: (orderId) => {
        const existing = get().orders;
        if (!existing.includes(orderId)) {
          set({ orders: [...existing, orderId] });
        }
      },

      exists: (orderId) => {
        return get().orders.includes(orderId);
      },

      clearOrders: () => set({ orders: [] }),
    }),
    {
      name: "eMart_OrderStore",
    },
  ),
);
