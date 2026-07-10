"use client";

import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";

interface Props extends React.ComponentProps<"div"> {
  className?: string;
}

const ProductCardSkeleton = ({ className }: Props) => {
  return (
    <div
      className={cn(
        "group relative flex flex-col overflow-clip rounded-[20px] bg-slate-100",
        className,
      )}
    >
      {/* Badges Skeleton */}
      <div className="absolute top-3 left-3 z-10 flex flex-col gap-1">
        <Skeleton className="h-5 w-12 rounded-full" />
        <Skeleton className="h-5 w-16 rounded-full" />
      </div>

      {/* Heart Skeleton */}
      <div className="absolute top-3 right-3 z-50 h-10 w-10 rounded-full shadow-md shadow-black/10" />

      {/* Image Skeleton */}
      <Skeleton className="h-[150px] w-full rounded-t-[20px] bg-slate-100 md:h-[266px]" />

      <div className="space-y-3 px-4 py-5">
        {/* Product Title Skeleton */}
        <Skeleton className="h-5 w-3/4 rounded-md" />

        {/* Price Skeleton */}
        <div className="flex items-center justify-between">
          <div className="flex space-x-2">
            <Skeleton className="h-6 w-16 rounded-md" />
            <Skeleton className="h-5 w-12 rounded-md" />
          </div>

          {/* Rating Skeleton */}
          <Skeleton className="h-6 w-12 rounded-full" />
        </div>

        {/* Button Skeleton */}
        <Skeleton className="h-10 w-full rounded-lg" />
      </div>
    </div>
  );
};

export default ProductCardSkeleton;
