"use client";

import { Skeleton } from "@/components/ui/skeleton";

const YourGroupSkeleton = () => {
  return (
    <div className="flex flex-wrap gap-2 lg:gap-4">
      {Array.from({ length: 4 }).map((_, i) => (
        <div
          key={i}
          className="flex-1 flex flex-col items-center min-w-[150px] bg-site-gray-50 border-2 border-site-gray-100 rounded-md py-4 px-3 lg:py-5 lg:px-4 relative"
        >
          <Skeleton className="absolute top-1 left-1 h-6 w-6 rounded-full" />
          <Skeleton className="absolute top-1.5 right-1.5 h-4 w-4 rounded-full" />
          <Skeleton className="h-10 w-10 rounded-full mb-2.5 mt-4" />
          <Skeleton className="h-5 w-24 mb-2" />
          <Skeleton className="h-4 w-32" />
        </div>
      ))}
    </div>
  );
};

export default YourGroupSkeleton;
