"use client";

import Container from "@/components/Container";
import { Skeleton } from "@/components/ui/skeleton";

export default function LoadingPage() {
  return (
    <Container className="py-8 md:pt-12 md:pb-16">
      <div>
          <Skeleton className="w-full h-[150px] mb-4 rounded-lg" />
      </div>
      <div className="flex-col-reverse md:flex-row gap-12 flex justify-between">
        {/* Left Section - Forms */}
        <div className="max-w-[650px] w-full space-y-6">
          {/* Shipping Address Section */}
          <div className="space-y-4">
            <div className="flex justify-between items-center mb-5">
              <Skeleton className="h-7 w-40" />
              <Skeleton className="h-10 w-44" />
            </div>
            
            {/* Address Cards */}
            {[1, 2].map((i) => (
              <div key={i} className="border rounded-lg p-4">
                <div className="flex items-center space-x-4">
                  <Skeleton className="h-5 w-5 rounded-full" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-5 w-32" />
                    <Skeleton className="h-4 w-40" />
                    <Skeleton className="h-4 w-full" />
                  </div>
                  <div className="flex gap-2">
                    <Skeleton className="h-8 w-8" />
                    <Skeleton className="h-8 w-8" />
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Delivery Methods Section */}
          <div className="space-y-4 mt-8">
            <Skeleton className="h-7 w-48" />
            <div className="flex flex-wrap gap-2">
              {[1, 2, 3, 4].map((i) => (
                <Skeleton key={i} className="h-16 w-[48%] rounded-lg" />
              ))}
            </div>
          </div>

          {/* Payment Methods Section */}
          <div className="space-y-4 mt-8">
            <Skeleton className="h-7 w-56" />
            <div className="flex flex-wrap gap-2">
              {[1, 2, 3, 4].map((i) => (
                <Skeleton key={i} className="h-20 w-[48%] rounded-lg" />
              ))}
            </div>
          </div>
        </div>

        {/* Right Section - Order Summary */}
        <div className="max-w-[414px] w-full">
          <div className="sticky top-4 border rounded-lg p-6 space-y-4">
            <Skeleton className="h-8 w-40 mb-6" />
            
            {/* Cart Items */}
            {[1, 2, 3].map((i) => (
              <div key={i} className="flex gap-3 pb-4 border-b">
                <Skeleton className="h-20 w-20 rounded" />
                <div className="flex-1 space-y-2">
                  <Skeleton className="h-4 w-full" />
                  <Skeleton className="h-4 w-3/4" />
                  <Skeleton className="h-5 w-20" />
                </div>
              </div>
            ))}

            {/* Summary Details */}
            <div className="space-y-3 pt-4">
              <div className="flex justify-between">
                <Skeleton className="h-4 w-24" />
                <Skeleton className="h-4 w-16" />
              </div>
              <div className="flex justify-between">
                <Skeleton className="h-4 w-28" />
                <Skeleton className="h-4 w-16" />
              </div>
              <div className="flex justify-between">
                <Skeleton className="h-4 w-20" />
                <Skeleton className="h-4 w-16" />
              </div>
              <div className="flex justify-between pt-3 border-t">
                <Skeleton className="h-6 w-32" />
                <Skeleton className="h-6 w-20" />
              </div>
            </div>

            {/* Terms Checkbox */}
            <div className="flex gap-2 items-center mt-6">
              <Skeleton className="h-5 w-5" />
              <Skeleton className="h-4 w-full" />
            </div>

            {/* Place Order Button */}
            <Skeleton className="h-12 w-full rounded-lg mt-4" />
          </div>
        </div>
      </div>

      {/* Mobile Place Order Button */}
      <div className="md:hidden mt-6 pt-8 space-y-4">
        <div className="flex gap-2 items-center">
          <Skeleton className="h-5 w-5" />
          <Skeleton className="h-4 w-full" />
        </div>
        <Skeleton className="h-12 w-full rounded-lg" />
      </div>
    </Container>
  );
}
