"use client";

import { Skeleton } from "@/components/ui/skeleton";
import Container from "../Container";

const FlashDealSkeleton = () => {
  return (
    <Container className="py-10">
      <div className="space-y-6">
        {/* Banner Skeleton */}
        <div className="relative mb-4 h-[227px] w-full overflow-hidden rounded-lg md:mb-8">
          <Skeleton className="h-full w-full" />
        </div>

        {/* Title Skeleton */}
        <div className="flex items-center justify-between">
          <Skeleton className="h-12 w-full" />
        </div>

        {/* Product Grid Skeleton */}
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="space-y-2">
              <Skeleton className="h-[180px] w-full rounded-md" />
              <Skeleton className="h-4 w-3/4" />
              <Skeleton className="h-4 w-1/2" />
            </div>
          ))}
        </div>
      </div>
    </Container>
  );
};

export default FlashDealSkeleton;
