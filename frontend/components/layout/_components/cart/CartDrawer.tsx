"use client";

import CartItem from "@/components/CartItem";
import PrimaryButton from "@/components/buttons/PrimaryButton";
import CartSkeleton from "@/components/skeleton/CartSkeleton";
import CartSummarySkeleton from "@/components/skeleton/CartSummarySkeleton";
import { SheetClose, SheetHeader, SheetTitle } from "@/components/ui/sheet";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { useGuestUserId } from "@/store/useGuestStore";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import { useQuery } from "@tanstack/react-query";
import { ShoppingCart, X } from "lucide-react";
import Link from "next/link";

const CartDrawer = () => {
  const { user } = useSession();
  const { setOpen } = useCartStore();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user?.id : guestId;
  // Get cart items
  const { data: cartItems, isLoading: isCartLoading } = useQuery({
    queryKey: ["get_cart", userId],
    queryFn: async () => {
      const { data } = await api.post(`/carts/${userId}`, {});
      return data as Cart[];
    },
    enabled: !!userId,
  });

  // Extract cart items
  const cart = (cartItems && cartItems[0]?.cart_items) || [];

  const { data: cartSummary, isLoading: isSummaryLoading } = useQuery({
    queryKey: ["get_subtotal", userId],
    queryFn: async () => {
      const { data } = await api.get(`/cart-summary/${userId}`, {});
      return data as CartSummary;
    },
    enabled: !!userId,
  });

  return (
    <div className="z-10 flex h-full w-full flex-col gap-2 p-3 md:p-10 md:pr-0">
      {/* Header */}
      <SheetHeader className="flex flex-row items-center justify-between p-0 md:pr-10">
        <SheetTitle className="text-site-gray-900 text-xl font-bold md:text-[24px]">
          My Bag ({cart.length || 0})
        </SheetTitle>

        <SheetClose asChild className="focus:border-0 focus:outline-0">
          <button className="text-site-gray-900 cursor-pointer rounded-full p-2">
            <span className="border-site-gray-600 hover:bg-site-gray-100 block rounded-full border p-1 font-bold">
              <X className="h-4 w-4" />
            </span>
          </button>
        </SheetClose>
      </SheetHeader>

      {/* Cart Items (Scrollable) */}
      <SheetTitle className="hidden">Cart</SheetTitle>

      {isCartLoading ? (
        <>
          <CartSkeleton />
          <CartSkeleton />
          <CartSkeleton />
          <CartSkeleton />
        </>
      ) : (
        <>
          {cart.length > 0 ? (
            <ScrollArea.Root
              className="relative overflow-clip"
              data-lenis-ignore
            >
              <ScrollArea.Viewport
                className="h-[calc(100dvh-210px)] pr-3 md:h-[calc(100dvh-320px)] md:pr-10"
                onWheel={(e) => e.stopPropagation()}
              >
                {cart.map((item, index) => (
                  <div key={item.id}>
                    <CartItem {...item} />
                    {index !== cart.length - 1 && <hr />}
                  </div>
                ))}
              </ScrollArea.Viewport>
              <ScrollArea.Scrollbar
                orientation="vertical"
                className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
              >
                <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
              </ScrollArea.Scrollbar>
            </ScrollArea.Root>
          ) : (
            <div className="flex flex-1 flex-col items-center justify-center gap-2 pr-3 md:pr-10">
              <ShoppingCart className="text-site-gray-400 h-7 w-7 md:h-12 md:w-12" />
              <p className="text-site-gray-400 text-base md:text-lg">
                Your Bag is empty!
              </p>
            </div>
          )}
        </>
      )}

      {/* Cart Summary (Fixed bottom) */}
      {isSummaryLoading ? (
        <CartSummarySkeleton />
      ) : (
        <>
          {cart.length > 0 && (
            <div className="absolute right-0 bottom-0 w-full bg-white px-3 pb-3 md:px-10 md:pb-10">
              <div className="mt-3 flex flex-col items-center gap-3 space-x-3 md:mt-6 md:flex-row md:gap-0">
                <div className="mt-2 flex w-full justify-between md:w-[20%] md:gap-1">
                  <div className="flex w-full flex-row justify-between md:flex-col">
                    <span className="text-site-gray-600 ml-2 text-base font-medium md:ml-0">
                      Cart Total
                    </span>
                    <span className="text-site-primary-600 text-base font-bold">
                      {cartSummary?.sub_total || 0}
                    </span>
                  </div>
                </div>
                <Link
                  onClick={() => setOpen(false)}
                  href="/cart"
                  className="bg-site-secondary-500 hover:bg-site-secondary-600 block w-full flex-1 rounded-full px-8 py-2 text-center text-base font-semibold text-white transition-colors duration-300 ease-in-out md:w-[80%] md:py-3"
                >
                  View Bag
                </Link>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default CartDrawer;
