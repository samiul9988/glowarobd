"use client";

import { Badge } from "@/components/ui/badge";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useQuery } from "@tanstack/react-query";
import { Handbag, ShoppingBag } from "lucide-react";

const CartCount = () => {
  const { user } = useSession();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user?.id : guestId;

  // Get cart items
  const { data: cartItems, isLoading } = useQuery({
    queryKey: ["get_cart", userId],
    queryFn: async () => {
      const { data } = await api.post(`/carts/${userId}`, {});
      return data as Cart[];
    },
    enabled: !!userId,
  });

  // Extract cart items
  const cart = (cartItems && cartItems[0]?.cart_items) || [];

  return (
    <div className="  h-[32px] lg:h-[44px]  w-[32px] relative lg:px-3 lg:py-2.5 rounded-full gap-1 flex lg:w-[100px] justify-center items-center bg-site-secondary-500">
      <ShoppingBag 
        strokeWidth={2}
        className="text-white h-5 lg:h-6 lg:w-6 w-5 "
      />
      <span className="font-bold text-sm text-white hidden lg:block">Bag</span>
      <Badge
        className="absolute -top-1 w-4 h-4 lg:top-0.5 -right-1 lg:right-0.5 lg:static bg-site-secondary-50 text-site-secondary-500 rounded-full px-0.5 py-0.5 lg:px-1 lg:py-0 text-xs"
        variant="default"
      >
        {cart.length}
      </Badge>
    </div>
  );
};

export default CartCount;
