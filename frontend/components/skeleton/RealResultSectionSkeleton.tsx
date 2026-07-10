"use client";

import { Skeleton } from "@/components/ui/skeleton";
import Container from "../Container";

const RealResultSectionSkeleton = () => {
  return (
    <section className="pb-10 md:pb-[60px]">
      <Container>
        {/* Section Header Skeleton */}
        <div className="mb-6 flex items-center gap-3 md:mb-8">
          <Skeleton className="h-8 w-8 rounded-lg md:h-10 md:w-10" />
          <Skeleton className="h-8 w-48 rounded-md md:h-10 md:w-64" />
        </div>

        {/* Tab Navigation Skeleton */}
        <div className="mb-6 flex gap-2 overflow-hidden md:mb-8">
          {[1, 2, 3, 4].map((_, i) => (
            <Skeleton
              key={i}
              className="h-8 w-24 flex-shrink-0 rounded-full md:h-10 md:w-32"
            />
          ))}
        </div>

        {/* Video Slider Skeleton */}
        <div className="relative w-full">
          {/* Navigation Buttons */}
          <div className="pointer-events-none absolute top-1/2 z-10 hidden -translate-y-1/2 lg:flex">
            <Skeleton className="h-12 w-12 flex-shrink-0 rounded-full" />
          </div>
          <div className="pointer-events-none absolute top-1/2 right-0 z-10 hidden -translate-y-1/2 lg:flex">
            <Skeleton className="h-12 w-12 flex-shrink-0 rounded-full" />
          </div>

          {/* Video Cards Grid */}
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 lg:grid-cols-4">
            {[1, 2, 3, 4].map((_, i) => (
              <div key={i} className="flex flex-col gap-3">
                {/* Video Thumbnail */}
                <Skeleton className="h-[150px] w-full rounded-lg md:h-[200px] lg:h-[240px]" />

                {/* Video Title */}
                <Skeleton className="h-5 w-3/4 rounded-md" />

                {/* Product Info (Optional) */}
                <div className="flex gap-2">
                  <Skeleton className="h-10 w-10 flex-shrink-0 rounded-full" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-4 w-full rounded-md" />
                    <Skeleton className="h-3 w-1/2 rounded-md" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </Container>
    </section>
  );
};

export default RealResultSectionSkeleton;
