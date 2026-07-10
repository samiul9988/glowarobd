'use client';

import Container from "@/components/Container";
import { Skeleton } from "@/components/ui/skeleton";

export default function LoadingPage() {
    return (
        <section className="py-4 md:py-8">
            <Container>
                <div className="flex flex-col lg:flex-row">
                    {/* Product Gallery Skeleton - Sticky on desktop */}
                    <div className="w-full lg:w-[560px] lg:flex-shrink-0 lg:sticky lg:top-10 lg:self-start">
                        {/* Breadcrumb Skeleton */}
                        <div className="mb-4">
                            <div className="flex items-center gap-1">
                                <Skeleton className="h-4 w-12" />
                                <Skeleton className="w-3 h-3" />
                                <Skeleton className="h-4 w-20" />
                                <Skeleton className="w-3 h-3" />
                                <Skeleton className="h-4 w-24" />
                            </div>
                        </div>

                        {/* Main Image */}
                        <Skeleton className="w-full aspect-square rounded-[10px] mb-4" />

                        {/* Thumbnail Images */}
                        <div className="flex gap-2 overflow-x-auto pb-2">
                            {[...Array(4)].map((_, index) => (
                                <Skeleton key={index} className="w-16 h-16 rounded-lg flex-shrink-0" />
                            ))}
                        </div>
                    </div>

                    {/* Product Details Skeleton */}
                    <div className="w-full lg:flex-1 lg:pl-[109px] mt-6 lg:mt-0">
                        {/* Brand & Category Skeleton */}
                        <div className="mb-4 flex items-center gap-2">
                            <Skeleton className="h-4 w-20" />
                            <Skeleton className="h-4 w-1" />
                            <Skeleton className="h-4 w-24" />
                        </div>

                        {/* Product Title Skeleton */}
                        <div className="mb-4">
                            <Skeleton className="h-10 w-full mb-2" />
                            <Skeleton className="h-10 w-3/4" />
                        </div>

                        {/* Rating Skeleton */}
                        <div className="flex items-center gap-3 mb-6">
                            <div className="flex items-center gap-2 bg-site-gray-50 border border-site-gray-100 px-4 py-1 rounded-full">
                                <Skeleton className="w-4 h-4" />
                                <Skeleton className="h-4 w-8" />
                                <Skeleton className="h-4 w-1" />
                                <Skeleton className="h-4 w-32" />
                            </div>
                        </div>

                        {/* Price Skeleton */}
                        <div className="flex items-center gap-2.5 mb-6 md:mb-10">
                            <Skeleton className="h-8 w-24" />
                            <Skeleton className="h-6 w-20" />
                            <Skeleton className="h-6 w-20 rounded-[8px]" />
                        </div>

                        {/* Divider */}
                        <Skeleton className="h-[1px] w-full mb-6 md:mb-10" />

                        {/* Variant Selector Skeleton */}
                        <div className="mb-8">
                            <Skeleton className="h-5 w-20 mb-3" />
                            <div className="flex gap-3">
                                {[...Array(4)].map((_, index) => (
                                    <Skeleton key={index} className="w-12 h-12 rounded-lg" />
                                ))}
                            </div>
                        </div>

                        {/* Quantity and Buttons Skeleton */}
                        <div className="flex items-center gap-2.5 mb-8 md:gap-4">
                            <Skeleton className="h-[50px] w-[130px] rounded-full" />
                            <Skeleton className="flex-1 h-[50px] rounded-full" />
                            <Skeleton className="flex-1 h-[50px] rounded-full" />
                        </div>

                        {/* Description Skeleton */}
                        <div className="mb-8">
                            <Skeleton className="h-6 w-40 mb-3" />
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-3/4" />
                            </div>
                            <Skeleton className="h-4 w-20 mt-2" />
                        </div>

                        {/* Product Information Skeleton */}
                        <div className="mb-6 md:mb-10">
                            <div className="space-y-4">
                                {[...Array(3)].map((_, index) => (
                                    <div key={index} className="flex items-start gap-3">
                                        <Skeleton className="w-6 h-6 flex-shrink-0" />
                                        <div className="flex-1">
                                            <Skeleton className="h-4 w-24 mb-1" />
                                            <Skeleton className="h-4 w-32" />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Divider */}
                        <Skeleton className="h-[1px] w-full mb-6 md:mb-10" />

                        {/* Product Description Tabs Skeleton */}
                        <div className="w-full border border-site-gray-50 rounded-[10px] overflow-hidden">
                            {/* Tab Navigation Skeleton */}
                            <div className="border-b border-site-gray-50 py-4">
                                <div className="flex space-x-3">
                                    {[...Array(3)].map((_, index) => (
                                        <div key={index} className="flex items-center gap-2 py-2 px-5 rounded-[8px] bg-site-gray-50">
                                            <Skeleton className="w-5 h-5" />
                                            <Skeleton className="h-4 w-20" />
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Tab Content Skeleton */}
                            <div className="bg-white p-5 sm:p-10">
                                <div className="space-y-4">
                                    <Skeleton className="h-4 w-full" />
                                    <Skeleton className="h-4 w-full" />
                                    <Skeleton className="h-4 w-3/4" />
                                    <Skeleton className="h-4 w-full" />
                                    <Skeleton className="h-4 w-5/6" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Container>
        </section>
    );
}
