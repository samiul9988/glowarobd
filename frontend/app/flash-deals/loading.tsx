"use client";

import Container from "@/components/Container";
import { Skeleton } from "@/components/ui/skeleton";

export default function Loading() {
  return (
    <section className="py-20">
      <Container>
        <div className="space-y-8">
          {/* Tabs Skeleton */}
          <div className="hide-scrollbar relative mb-6 flex gap-3 overflow-x-auto whitespace-nowrap">
            {Array.from({ length: 4 }).map((_, i) => (
              <Skeleton
                key={i}
                className="h-[42px] w-[120px] rounded-[10px] md:h-[56px] md:w-[160px]"
              />
            ))}
          </div>

          {/* Flash Deal Sections */}
          {Array.from({ length: 2 }).map((_, sectionIndex) => (
            <div key={sectionIndex} className="space-y-4">
              {/* Banner Skeleton */}
              <div className="bg-site-gray-50 relative flex w-full justify-between overflow-hidden rounded-t-[10px] px-10 py-6">
                <div className="flex items-center">
                  <Skeleton className="h-8 w-44 rounded-md md:h-10 md:w-60" />
                </div>
                <div className="flex items-center gap-4">
                  <Skeleton className="h-[60px] w-[60px] rounded-full" />
                  <Skeleton className="h-[60px] w-[60px] rounded-full" />
                </div>
              </div>

              {/* Product Grid Skeleton */}
              <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-6 lg:grid-cols-5">
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
          ))}
        </div>
      </Container>
    </section>
  );
}
