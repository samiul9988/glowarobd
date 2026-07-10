import React from "react";
import { Skeleton } from "../ui/skeleton";

const CartSkeleton = () => {
  return (
    <div className="mr-3 md:mr-10">
      <div className="flex gap-2 md:gap-4">
        <Skeleton className="w-[82px] h-[82px] md:w-[100px] md:h-[100px] shrink-0" />
        <div className="w-full space-y-1 md:space-y-2">
          <Skeleton className="w-full h-3 md:h-6" />
          <Skeleton className="w-1/2 h-3 md:h-6" />
          <Skeleton className="w-3/4 h-2 md:h-4" />
        </div>
      </div>
    </div>
  );
};

export default CartSkeleton;
