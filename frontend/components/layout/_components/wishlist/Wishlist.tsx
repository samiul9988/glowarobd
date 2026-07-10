"use client";

import { HeartIcon } from "@/components/icons/icon-library";
import { Badge } from "@/components/ui/badge";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useQuery } from "@tanstack/react-query";
import { Heart } from "lucide-react";
import Link from "next/link";
import React from "react";

const Wishlist = () => {
  const { user } = useSession();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user?.id : guestId;
  // Get wishlist items
  const { data: wishlistItems } = useQuery({
    queryKey: ["wishlist", userId],
    queryFn: async () => {
      const { data } = await api.get(`/wishlists/${userId}`);
      return data as WishlistResponse;
    },
    enabled: !!userId,
  });

  return (
    <Link
      href="/wishlist"
      className="w-[32px] lg:w-[44px] h-[32px] lg:h-[44px] lg:flex items-center justify-center hover:bg-site-gray-100/60 bg-[#FFFFFFCC] rounded-full p-1 md:p-1.5 transition-all hidden"
    >
      <div className="relative">
        <Heart strokeWidth={2} className="lg:h-6 lg:w-6 h-5 w-5 text-[#583480]"/>
        <Badge
          className="!bg-site-primary absolute -top-2 -right-2 rounded-full px-1 py-0 text-xs"
          variant="destructive"
        >
          {wishlistItems?.data.length || 0}
        </Badge>
      </div>
    </Link>
  );
};

export default Wishlist;
