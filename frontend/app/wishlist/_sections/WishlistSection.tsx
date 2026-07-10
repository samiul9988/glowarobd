"use client";

import WishlistCard from "@/components/cards/WishlistCard";
import Container from "@/components/Container";
import WishlistCardSkeleton from "@/components/skeleton/WishlistCardSkeleton";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useQuery } from "@tanstack/react-query";
import { Heart, HeartCrack, HeartHandshake, Zap } from "lucide-react";
import Link from "next/link";
import React from "react";

const WishlistSection = ({ isDashboard = false }) => {
  const { user } = useSession();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user?.id : guestId;

  // Get wishlist items
  const { data, isLoading } = useQuery({
    queryKey: ["wishlist", userId],
    queryFn: async () => {
      const { data } = await api.get(`/wishlists/${userId}`);
      return data as WishlistResponse;
    },
    enabled: !!userId,
  });
  return (
    <section className={`my-12 ${isDashboard ? "md:mt-0" : ""}`}>
      <Container>
        {/* Heading */}
        {/* {data && data.data.length > 0 && (
          <h2 className="text-site-gray-900 text-xl font-bold md:text-[32px]">
            Wishlist
          </h2>
        )} */}

        {/* Wishlist Grid */}
        <div
          className={`border-site-gray-50 grid gap-x-3 rounded-md border border-b-2 p-2 ${
            isDashboard
              ? "grid-cols-1 md:grid-cols-1"
              : "grid-cols-1 sm:grid-cols-2"
          }`}
        >
          {isLoading || !data ? (
            Array.from({ length: 10 }).map((_, i) => (
              <WishlistCardSkeleton key={i} />
            ))
          ) : (
            <>
              {data.data.length === 0 ? (
                <section className="col-span-full py-5 md:py-16">
                  <div className="flex flex-col items-center justify-center space-y-4 text-center">
                    {/* Icon */}
                    <div className="bg-site-primary-100 animate-bounce rounded-full p-4">
                      <HeartHandshake className="text-site-primary h-8 w-8 md:h-10 md:w-10" />
                    </div>

                    {/* Title */}
                    <h2 className="text-site-gray-800 text-xl md:text-2xl">
                      Your Wishlist is Empty
                    </h2>

                    {/* Subtitle */}
                    <p className="max-w-md text-sm text-gray-500 md:text-base">
                      You haven&apos;t added any items yet. Start exploring and
                      save your favorites!
                    </p>

                    {/* Button */}
                    <Link
                      className="text-site-primary-500 border-site-primary-500 bg-site-primary-50 hover:bg-site-primary-500 mt-6 rounded-full border px-3 py-2 text-center text-sm font-semibold transition-colors hover:text-white md:mt-12"
                      href="/"
                    >
                      Shopping
                    </Link>
                  </div>
                </section>
              ) : (
                data.data.map((item) => (
                  <WishlistCard key={item.id} {...item} />
                ))
              )}
            </>
          )}
        </div>
      </Container>
    </section>
  );
};

export default WishlistSection;
