import { Skeleton } from "@/components/ui/skeleton";

const FeaturedCardSkeleton = () => {
  return (
    <div className="relative flex h-[220px] w-full max-w-[382px] flex-col items-center justify-between rounded-[20px] border p-3 md:h-[300px] md:p-8 lg:h-[400px] bg-site-gray-50">
      {/* Text skeletons */}
      <div className="w-full space-y-2">
        <Skeleton className="h-4 w-1/3" />
        <Skeleton className="h-6 w-full" />
      </div>

      <div className="relative">
        {/* Pattern skeleton */}
        <Skeleton className="absolute bottom-0 left-0 h-20 w-20 lg:h-40 lg:w-40" />
        <Skeleton className="h-[100px] w-[100px] rounded-xl lg:h-[180px] lg:w-[180px]" />
      </div>
    </div>
  );
};

export default FeaturedCardSkeleton;
