"use client";

import React from "react";
import { Skeleton } from "@/components/ui/skeleton";

const PurchaseHistorySkeleton = () => {
  const skeletonCards = Array.from({ length: 6 });

  return (
    <div className="w-full space-y-6">
      {/* Search + Filter Skeleton */}
      <div className="flex flex-col md:flex-row gap-4 justify-between items-center">
        <Skeleton className="h-10 w-full max-w-[300px] rounded-md" />
        <Skeleton className="h-10 w-[130px] rounded-md" />
      </div>

      {/* Skeleton Cards */}
      <div className="space-y-4">
        {skeletonCards.map((_, i) => (
          <div
            key={i}
            className="flex justify-between items-center p-4 bg-gray-50 rounded-lg"
          >
            <div className="flex-1 space-y-2">
              <Skeleton className="h-4 w-32 rounded" />
              <Skeleton className="h-3 w-20 rounded" />
            </div>
            <Skeleton className="h-4 w-16 rounded" />
          </div>
        ))}
      </div>

      {/* Pagination Skeleton */}
      <div className="flex gap-3 mt-6">
        <Skeleton className="h-8 w-16 rounded" />
        <Skeleton className="h-8 w-28 rounded" />
        <Skeleton className="h-8 w-16 rounded" />
      </div>
    </div>
  );
};

export default PurchaseHistorySkeleton;
