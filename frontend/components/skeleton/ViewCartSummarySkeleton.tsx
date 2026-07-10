import React from "react";
import { Skeleton } from "../ui/skeleton";

const ViewCartSummarySkeleton = () => {
  return (
    <div className="w-full border border-site-gray-100 rounded-[10px] px-5 py-4">
      <div className="flex items-center justify-between">
        <Skeleton className="w-16 h-4 md:h-5" />
        <Skeleton className="w-16 h-4 md:h-5" />
      </div>
      <div className="flex items-center justify-between mt-2 mb-4">
        <Skeleton className="w-28 h-4 md:h-5" />
        <Skeleton className="w-28 h-4 md:h-5" />
      </div>

      <Skeleton className="w-full h-6 md:h-10" />
    </div>
  );
};

export default ViewCartSummarySkeleton;
