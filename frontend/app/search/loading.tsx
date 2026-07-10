"use client";

import Container from "@/components/Container";
import { Skeleton } from "@/components/ui/skeleton";

const Loading = () => {
  return (
    <section className="my-4 md:my-8">
      <Container>
        <Skeleton className="mb-6 h-24 w-full md:mb-8 md:h-[172px]" />
        <div className="flex items-start gap-10">
          {/* Sidebar skeleton */}
          <div className="hidden w-[288px] space-y-6 lg:block">
            {/* Filter title */}
            <Skeleton className="h-6 w-24" />
            {/* Price slider */}
            <div className="space-y-3">
              <Skeleton className="h-4 w-full" />
              <div className="flex gap-2">
                <Skeleton className="h-8 w-16" />
                <Skeleton className="h-8 w-16" />
              </div>
            </div>
            {/* Categories */}
            <div className="space-y-3">
              {[...Array(6)].map((_, i) => (
                <div key={i} className="flex items-center gap-3">
                  <Skeleton className="h-4 w-4 rounded-full" />
                  <Skeleton className="h-5 w-32" />
                </div>
              ))}
            </div>
            {/* Filter title */}
            <Skeleton className="h-6 w-24" />
            {/* Price slider */}
            <div className="space-y-3">
              <Skeleton className="h-4 w-full" />
              <div className="flex gap-2">
                <Skeleton className="h-8 w-16" />
                <Skeleton className="h-8 w-16" />
              </div>
            </div>
          </div>

          {/* Main content */}
          <div className="flex-1">
            {/* Header row */}
            <div className="mb-6 flex items-center justify-between gap-4">
              <Skeleton className="h-8 w-40" />
              <Skeleton className="h-8 w-28" />
            </div>

            {/* Product grid skeleton */}
            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
              {[...Array(12)].map((_, i) => (
                <div
                  key={i}
                  className="aspect-square w-full space-y-3 rounded-md border border-gray-200 p-3"
                >
                  {/* Product image */}
                  <Skeleton className="h-32 w-full rounded-md" />
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
        </div>
      </Container>
    </section>
  );
};

export default Loading;
