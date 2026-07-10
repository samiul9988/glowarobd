"use client";

import { Skeleton } from "@/components/ui/skeleton";

interface CategorySkeletonProps {
  count?: number;
}

const CategorySkeleton = ({ count = 24 }: CategorySkeletonProps) => {
  const skeletonArray = Array.from({ length: count });

  return (
    <div className="flex flex-wrap justify-center gap-8">
      {skeletonArray.map((_, index) => (
        <div
          key={index}
          className="border-site-gray-100 flex flex-col items-center justify-center gap-3"
        >
          <Skeleton className="h-[120px] w-[140px] rounded-lg" />
          <Skeleton className="mt-2 h-5 w-[80px]" />
        </div>
      ))}
    </div>
  );
};

export default CategorySkeleton;
