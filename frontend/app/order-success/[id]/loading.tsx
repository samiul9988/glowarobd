"use client";

import Container from "@/components/Container";
import { Skeleton } from "@/components/ui/skeleton";

export default function LoadingPage() {
  return (
    <section className="py-6 md:py-10">
      <Container>
        {/* Header Section */}
        <div className="mb-8 text-center">
          <div className="mx-auto mb-6 h-16 w-16 animate-pulse rounded-full bg-gray-200" />
          <Skeleton className="mx-auto mb-2 h-6 w-56" />
          <Skeleton className="mx-auto h-4 w-72" />
        </div>

        {/* Order Details Card */}
        <div className="overflow-hidden rounded-lg bg-white shadow-sm">
          <div className="border-b bg-gray-50 px-6 py-4">
            <Skeleton className="h-5 w-40" />
          </div>

          <div className="space-y-8 p-4 md:p-6">
            {/* Order + Shipping Info */}
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              {/* Order Info */}
              <div className="space-y-3">
                <Skeleton className="mb-2 h-5 w-32" />
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="flex items-center gap-4">
                    <Skeleton className="h-4 w-32" />
                    <Skeleton className="h-4 w-40 flex-1" />
                  </div>
                ))}
              </div>

              {/* Shipping Info */}
              <div className="space-y-3">
                <Skeleton className="mb-2 h-5 w-40" />
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="flex items-center gap-4">
                    <Skeleton className="h-4 w-28" />
                    <Skeleton className="h-4 w-40 flex-1" />
                  </div>
                ))}
              </div>
            </div>

            {/* Product List */}
            <div>
              <Skeleton className="mb-4 h-5 w-32" />
              {[...Array(3)].map((_, i) => (
                <div
                  key={i}
                  className="flex items-center justify-between border-b border-gray-200 py-3"
                >
                  <div className="flex w-3/5 items-center gap-3">
                    <Skeleton className="h-12 w-12 rounded-md" />
                    <Skeleton className="h-4 w-48" />
                  </div>
                  <Skeleton className="h-4 w-12" />
                  <Skeleton className="h-4 w-16" />
                </div>
              ))}
            </div>

            {/* Order Summary */}
            <div className="space-y-3">
              {[...Array(3)].map((_, i) => (
                <div key={i} className="flex justify-between">
                  <Skeleton className="h-4 w-32" />
                  <Skeleton className="h-4 w-20" />
                </div>
              ))}
              <hr />
              <div className="flex justify-between">
                <Skeleton className="h-5 w-40" />
                <Skeleton className="h-5 w-24" />
              </div>
            </div>
          </div>

          <div className="border-t bg-gray-50 px-6 py-4 text-center">
            <Skeleton className="mx-auto h-4 w-80" />
          </div>
        </div>

        {/* Action Buttons */}
        <div className="mt-8 flex justify-center">
          <Skeleton className="h-10 w-40 rounded-lg" />
        </div>
      </Container>
    </section>
  );
}
