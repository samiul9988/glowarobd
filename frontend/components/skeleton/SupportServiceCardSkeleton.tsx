"use client";

import { Skeleton } from "@/components/ui/skeleton";

const SupportServiceCardSkeleton = () => {
  return (
    <div className="bg-slate-200 px-5 py-4 rounded-[20px] flex items-center gap-3 md:gap-6">
      {/* Image Skeleton */}
      <Skeleton className="h-20 w-20 rounded-lg" />

      <div className="space-y-2 flex-1">
        {/* Title Skeleton */}
        <Skeleton className="h-8 w-3/4" />

        {/* Subtitle Skeleton */}
        <Skeleton className="h-4 w-1/2" />
      </div>
    </div>
  );
};

export default SupportServiceCardSkeleton;
