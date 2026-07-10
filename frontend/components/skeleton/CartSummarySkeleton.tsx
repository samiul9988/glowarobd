import React from "react";
import { Skeleton } from "../ui/skeleton";

const CartSummarySkeleton = () => {
  return (
    <div className="mr-3 left-0 absolute bottom-3 md:bottom-12 w-full">
      <div className="mx-3 md:mx-10">
        <div className="flex items-center justify-between">
          <Skeleton className="w-24 h-4 md:h-5" />
          <Skeleton className="w-24 h-4 md:h-5" />
        </div>
        <div className="space-y-2 md:space-y-3 mt-6">
          <Skeleton className="w-full h-6 md:h-10" />
          <Skeleton className="w-full h-6 md:h-10" />
        </div>
      </div>
    </div>
  );
};

export default CartSummarySkeleton;
