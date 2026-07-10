"use client";

import { Skeleton } from "@/components/ui/skeleton";
import Container from "../Container";

const ProductHighlightCardSkeleton = () => {
  return (
    <Container>
      <div className="flex flex-col-reverse items-center justify-between gap-8 lg:flex-row">
        {/* Left Side */}
        <div className="relative flex h-[370px] w-[320px] items-end justify-center md:h-[550px] md:w-[500px] md:px-4">
          {/* Background Shape */}
          <div className="h-[290px] w-full rounded-tl-[9999px] rounded-tr-[9999px] bg-[linear-gradient(121.94deg,#E4FFC9_0%,#93BE75_100%)] md:h-[460px] md:w-[455px]" />

          {/* Product Image */}
          <Skeleton className="absolute bottom-0 h-full w-full rounded-lg" />

          {/* Top Rated Badge */}
          <div className="absolute top-0 right-0 translate-x-[25px] translate-y-[35px] rounded-[10px] bg-white px-6 py-3 shadow-[0px_4px_16px_0px_#0000001F] md:-translate-x-[35px] md:translate-y-[35px]">
            <Skeleton className="mb-2 h-3 w-20" />
            <div className="flex gap-1">
              {[...Array(5)].map((_, i) => (
                <Skeleton key={i} className="h-3 w-3 rounded-sm" />
              ))}
            </div>
          </div>

          {/* Discount Badge */}
          <div className="absolute right-0 bottom-0 inline-block -translate-x-[106px] -translate-y-[70px] space-y-2 rounded-[10px] bg-white px-6 py-2 shadow-[0px_4px_16px_0px_#0000001F]">
            <Skeleton className="h-3 w-24" />
            <Skeleton className="h-4 w-32" />
          </div>
        </div>

        {/* Right Side */}
        <div className="w-full lg:max-w-[605px]">
          <div className="space-y-4">
            <div className="space-y-2 md:space-y-3">
              <Skeleton className="h-4 w-32" />
              <Skeleton className="h-6 w-48 md:h-10 md:w-80" />
            </div>
            <Skeleton className="h-4 w-full md:w-[90%]" />
            <Skeleton className="h-4 w-3/4 md:w-[80%]" />
            <Skeleton className="h-4 w-1/2 md:w-[60%]" />
          </div>

          {/* Highlights */}
          <div className="mt-8 mb-8 grid grid-cols-2 gap-3 md:mb-10 md:gap-5">
            {[1, 2, 3, 4].map((i) => (
              <div key={i} className="flex items-center gap-4">
                <Skeleton className="h-[60px] w-[60px] rounded-md" />
                <Skeleton className="h-5 w-24" />
              </div>
            ))}
          </div>

          {/* Button */}
          <Skeleton className="h-10 w-40 rounded-[10px]" />
        </div>
      </div>
    </Container>
  );
};

export default ProductHighlightCardSkeleton;
