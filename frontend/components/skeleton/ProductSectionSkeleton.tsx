"use client";

import { Skeleton } from "@/components/ui/skeleton";
import Container from "../Container";
import ProductCardSkeleton from "./ProductCardSkeleton";

type Props = {
  className?: string;
};

const ProductSectionSkeleton = ({ className }: Props) => {
  return (
    <div className={className}>
      <Container>
        <div className="space-y-3 md:space-y-9">
          {/* Header */}
          <div className="flex items-center justify-between">
            {/* Title skeleton */}
            <Skeleton className="h-8 w-40 rounded-md md:h-10 md:w-60" />

            {/* See All skeleton */}
            <div className="hidden space-x-4 md:flex">
              <Skeleton className="h-10 w-40 rounded-full" />
            </div>
          </div>
          {/* Cards grid */}
          <div className="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-6 lg:grid-cols-5">
            <ProductCardSkeleton />
            <ProductCardSkeleton />
            <ProductCardSkeleton className="hidden md:flex" />
            <ProductCardSkeleton className="hidden lg:flex" />
            <ProductCardSkeleton className="hidden lg:flex" />
          </div>
        </div>
      </Container>
    </div>
  );
};

export default ProductSectionSkeleton;
