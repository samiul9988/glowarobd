"use client";

import { Skeleton } from "@/components/ui/skeleton";
import { Heart } from "lucide-react";

export default function WishlistCardSkeleton() {
  return (
    <div className="group border-site-gray-50 shadow-custom relative flex flex-col overflow-clip rounded-[10px] border bg-white">
      {/* Heart placeholder */}
      <div className="absolute top-3 right-3 z-10 rounded-full bg-white p-2 shadow-md shadow-black/10">
        <Heart strokeWidth={1} className="text-gray-400" />
      </div>

      {/* Image Skeleton */}
      <div className="relative aspect-square overflow-hidden bg-gray-100">
        <Skeleton className="absolute inset-0 h-full w-full" />
      </div>

      {/* Content Skeleton */}
      <div className="flex flex-col gap-2 p-3 md:p-4">
        {/* Reviews */}
        <Skeleton className="h-4 w-24 rounded-md" />

        {/* Title */}
        <div className="space-y-2">
          <Skeleton className="h-4 w-full rounded-md" />
          <Skeleton className="h-4 w-3/4 rounded-md" />
        </div>

        {/* Price + Rating section */}
        <div className="mt-3 flex items-center justify-between gap-2 md:mt-4">
          <Skeleton className="h-5 w-20 rounded-md" />
          <Skeleton className="h-5 w-12 rounded-full" />
        </div>
      </div>
    </div>
  );
}
