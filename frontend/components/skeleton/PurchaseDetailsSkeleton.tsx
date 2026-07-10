import React from "react";
import { Skeleton } from "../ui/skeleton";

const PurchaseDetailsSkeleton = () => {
  return (
    <div className="border border-site-gray-100 p-4 md:p-8 rounded-[10px] bg-gradient-to-b from-[#F3FAFF] to-white animate-in fade-in duration-500">
      {/* Header */}
      <div className="space-y-3 mb-8">
        <div className="flex items-center gap-3">
          <Skeleton className="h-4 w-[120px]" />
          <Skeleton className="h-4 w-[80px]" />
        </div>
        <Skeleton className="h-3 w-[100px]" />
      </div>

      {/* Steps */}
      <div className="my-10">
        <Skeleton className="h-8 w-full rounded-md" />
      </div>

      {/* Address + Info */}
      <div className="flex flex-col md:flex-row gap-10 md:gap-20">
        <div className="flex-1 space-y-4">
          <Skeleton className="h-4 w-3/4" />
          <Skeleton className="h-4 w-2/3" />
          <Skeleton className="h-4 w-1/2" />
        </div>
        <div className="flex-1 space-y-4">
          <Skeleton className="h-4 w-3/4" />
          <Skeleton className="h-4 w-2/3" />
        </div>
      </div>

      {/* Products + Totals */}
      <div className="border border-site-gray-100 mt-8 md:mt-12 bg-white p-6 rounded-[8px] flex flex-col md:flex-row gap-8 md:gap-16">
        {/* Left */}
        <div className="flex-1 space-y-4">
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="flex gap-3 items-center">
              <Skeleton className="h-[60px] w-[60px] rounded-md" />
              <div className="space-y-2 w-full">
                <Skeleton className="h-4 w-3/4" />
                <Skeleton className="h-4 w-1/4" />
              </div>
            </div>
          ))}
        </div>

        {/* Right */}
        <div className="w-full md:w-[220px] space-y-3">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="flex justify-between">
              <Skeleton className="h-4 w-[80px]" />
              <Skeleton className="h-4 w-[60px]" />
            </div>
          ))}
          <hr />
          <div className="flex justify-between mt-2">
            <Skeleton className="h-5 w-[100px]" />
            <Skeleton className="h-5 w-[80px]" />
          </div>
        </div>
      </div>
    </div>
  );
};

export default PurchaseDetailsSkeleton;
