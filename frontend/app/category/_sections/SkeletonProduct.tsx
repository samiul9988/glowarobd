"use client";

import { Skeleton } from "@/components/ui/skeleton";

export default function SkeletonProduct() {
  return (
    <div className="flex-1">
      {/* Header row */}
      {/* <div className="flex items-center justify-between gap-4 mb-6">
        <Skeleton className="h-8 w-40" />
        <Skeleton className="h-8 w-28" />
      </div> */}

      {/* Product grid skeleton */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-6">
        {[...Array(12)].map((_, i) => (
          <div
            key={i}
            className="w-full aspect-square rounded-md border border-gray-200 p-3 space-y-3"
          >
            {/* Product image */}
            <Skeleton className="w-full h-32 rounded-md" />
            {/* Reviews */}
            <Skeleton className="h-4 w-20" />
            {/* Title */}
            <Skeleton className="h-4 w-3/4" />
            {/* Price */}
            <Skeleton className="h-6 w-24" />
          </div>
        ))}
      </div>
    </div>
  );
}
