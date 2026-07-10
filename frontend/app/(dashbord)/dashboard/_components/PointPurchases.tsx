"use client";

import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import BodyText from "@/components/BodyText";
import Heading from "@/components/Heading";
import { fetcher } from "@/lib/fetcher";
import { getAccessToken } from "@/lib/getAccessToken";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { useToken } from "@/store/useTokenStore";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";

import CountUp from "react-countup";
import { Heart, ShoppingBag } from "lucide-react";
import { InboxIcon } from "@/components/icons/icon-library";

interface CounterData {
  cart_item_count: number;
  wishlist_item_count: number;
  order_count: number;
}

interface PointData {
  message: string;
  point: number;
  status: number;
  success: boolean;
}

const PointPurchases = () => {
  const { accessToken } = useToken();
  const { user } = useSession();

  const { data: counterData } = useQuery({
    queryKey: ["get_profile", user?.id],
    queryFn: async () => {
      const response = await api.get(`/profile/counters/${user?.id}`, {
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
      });
      return response.data as CounterData;
    },
    enabled: !!user?.id && !!accessToken,
  });

  //   const { data: pointData } = useQuery({
  //     queryKey: ["get_point", user?.id],
  //     queryFn: async () => {
  //       const response = await api.get(`/reward/point`, {
  //         headers: {
  //           Authorization: `Bearer ${accessToken}`,
  //         },
  //       });
  //       return response.data as PointData;
  //     },
  //     enabled: !!user?.id && !!accessToken,
  //   });

  return (
    <div className="mb-[40px] grid w-full grid-cols-3 gap-2 lg:gap-4">
      <Link
        href="/cart"
        className="bg-site-primary-100 flex h-[75px] max-h-[75px] flex-col justify-center gap-1 rounded-[12px] px-2 py-1 lg:px-4 lg:py-2.5"
      >
        <div className="flex items-center gap-1 lg:gap-4">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-b from-white/36 to-white/24 p-[1px] lg:h-12 lg:w-12">
            <InboxIcon size={20} />
          </div>
          <div>
            <h2 className="text-site-gray-900 text-[20px] font-bold lg:text-[23px]">
              <CountUp
                start={0}
                end={counterData?.cart_item_count || 0}
                duration={2}
              />
            </h2>
            <p className="text-site-gray-600 text-[10px] font-normal lg:text-sm">
              In your cart
            </p>
          </div>
        </div>
      </Link>
      <Link
        href="/wishlist"
        className="flex h-[75px] max-h-[75px] flex-col justify-center gap-1 rounded-[12px] bg-[#FFF5E3] px-2 py-1 lg:px-4 lg:py-2.5"
      >
        <div className="flex items-center gap-1 lg:gap-4">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-b from-white/36 to-white/24 p-[1px] lg:h-12 lg:w-12">
            <Heart size={20} />
          </div>
          <div>
            <h2 className="text-site-gray-900 text-[20px] font-bold lg:text-[23px]">
              <CountUp
                start={0}
                end={counterData?.wishlist_item_count || 0}
                duration={2}
              />
            </h2>
            <p className="text-site-gray-600 text-[10px] font-normal lg:text-sm">
              Total in your Wishlist
            </p>
          </div>
        </div>
      </Link>

      <Link
        href="/purchase-history"
        className="flex h-[75px] max-h-[75px] flex-col justify-center gap-1 rounded-[12px] bg-[#E7F5E8] px-2 py-1 lg:px-4 lg:py-2.5"
      >
        <div className="flex items-center gap-1 lg:gap-4">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-b from-white/36 to-white/24 p-[1px] lg:h-12 lg:w-12">
            <ShoppingBag size={20} />
          </div>
          <div>
            <h2 className="text-site-gray-900 text-[20px] font-bold lg:text-[23px]">
              <CountUp
                start={0}
                end={counterData?.order_count || 0}
                duration={2}
              />
            </h2>
            <p className="text-site-gray-600 text-[10px] font-normal lg:text-sm">
              Total you purchase
            </p>
          </div>
        </div>
      </Link>
    </div>
  );
};

export default PointPurchases;
