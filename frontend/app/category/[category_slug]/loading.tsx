"use client";

import Container from "@/components/Container";
import { Skeleton } from "@/components/ui/skeleton";

const Loading = () => {
  return (
    <section className="mt-4 pb-4 md:mt-12">
    <Container>
        <div className="py-10 h-[100px] lg:h-[150px] bg-site-gray-50 w-full mb-4 rounded-lg">

        </div>
    </Container>
    <Container className="flex items-start gap-10">
    {/* Products */}
    <div className="flex-1">
        {/* Header row with filters and sort */}
        <div className="mb-6 flex items-center justify-between gap-4">
        <div className="flex flex-1 gap-3 items-center">
            {/* Filter buttons skeleton - Desktop only */}
            <div className="gap-3 flex items-center max-md:hidden">
            <Skeleton className="h-[42px] w-[120px] rounded-full bg-slate-200" />
            <Skeleton className="h-[42px] w-[100px] rounded-full bg-slate-200" />
            <Skeleton className="h-[42px] w-[100px] rounded-full bg-slate-200" />
            </div>
            {/* Sort dropdown skeleton */}
            <Skeleton className="h-[42px] w-[140px] rounded-full bg-slate-200 max-md:flex-1" />
        </div>
        {/* Mobile filter button skeleton */}
        <Skeleton className="h-[52px] w-[52px] rounded-[10px] bg-slate-200 lg:hidden" />
        </div>

        {/* Product grid skeleton */}
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-6 lg:grid-cols-5">
        {[...Array(10)].map((_, i) => (
            <div
            key={i}
            className="w-full space-y-3 rounded-md border border-gray-200 p-3"
            >
            {/* Product image */}
            <Skeleton className="aspect-square w-full rounded-md bg-slate-200" />
            
            {/* Brand/Category */}
            <Skeleton className="h-3 w-16 bg-slate-200" />
            
            {/* Title */}
            <div className="space-y-2">
                <Skeleton className="h-4 w-full bg-slate-200" />
                <Skeleton className="h-4 w-3/4 bg-slate-200" />
            </div>
            
            {/* Rating */}
            <Skeleton className="h-4 w-20 bg-slate-200" />
            
            {/* Price */}
            <div className="flex items-center gap-2">
                <Skeleton className="h-6 w-20 bg-slate-200" />
                <Skeleton className="h-4 w-16 bg-slate-200" />
            </div>
            </div>
        ))}
        </div>
    </div>
    </Container>
    </section>
  );
};

export default Loading;
