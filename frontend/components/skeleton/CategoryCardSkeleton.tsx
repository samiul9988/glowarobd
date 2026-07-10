"use client";

import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";

interface Props extends React.ComponentProps<"div"> {
  className?: string;
}

const CategoryCardSkeleton = ({ className }: Props) => {
  return (
    <div
      className={cn(
        "rounded-[20px] py-4 px-6 h-[300px] flex flex-col items-center justify-center gap-6 text-center bg-slate-200 w-full",
        className
      )}
    >
      {/* Image Skeleton */}
      <Skeleton className="w-[100px] md:w-[150px] h-[100px] md:h-[150px] rounded-full" />

      {/* Title Skeleton */}
      <Skeleton className="w-[100px] h-6 rounded-md" />
    </div>
  );
};

export default CategoryCardSkeleton;
