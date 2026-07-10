import CategorySectionSkeleton from "@/components/skeleton/CategorySectionSkeleton";
import FeaturedCategorySectionSkeleton from "@/components/skeleton/FeaturedCategorySectionSkeleton";
import HeroSliderSkeleton from "@/components/skeleton/HeroSliderSkeleton";
import ProductSectionSkeleton from "@/components/skeleton/ProductSectionSkeleton";
import RealResultSectionSkeleton from "@/components/skeleton/RealResultSectionSkeleton";
import { Suspense } from "react";
import DoctorConsultationSection from "./_sections/DoctorConsultationSection";
import FeaturedSection from "./_sections/FeaturedSection";
import HeroSection from "./_sections/HeroSection";
import ProductHighlightSection from "./_sections/ProductHighlightSection";
import RealResultSection from "./_sections/RealResultSection";
import SpecialOfferZoneSection from "./_sections/SpecialOfferZoneSection";
import TestimonialSection from "./_sections/TestimonialSection";
import BannerTwo from "./_sections/BannerTwo";
import ShopByConcern from "./_sections/ShopByConcern";
import ProductHighlightCardSkeleton from "@/components/skeleton/ProductHighlightCardSkeleton";
import CategoriesSection from "./_sections/CategoriesSection";
import FeaturedCategorySection from "./_sections/FeaturedCategorySection";
import NewArrivalProduct from "./_sections/NewArrivalProduct";
import ShopByCategorySection from "./_sections/ShopByCategorySection";
import { Metadata } from "next";

export default async function Home() {
  return (
    <>
      <Suspense fallback={<HeroSliderSkeleton />}>
        <HeroSection />
      </Suspense>

      <Suspense fallback={<CategorySectionSkeleton />}>
        <CategoriesSection />
      </Suspense>

      <Suspense fallback={<FeaturedCategorySectionSkeleton />}>
        <FeaturedCategorySection />
      </Suspense>

      <Suspense
        fallback={
          <ProductSectionSkeleton className="pb-10 md:pb-[60px] lg:pb-[100px]" />
        }
      >
        <SpecialOfferZoneSection />
      </Suspense>

      <Suspense fallback={<RealResultSectionSkeleton />}>
        <RealResultSection />
      </Suspense>
      <Suspense fallback={<ProductSectionSkeleton />}>
        <FeaturedSection />
      </Suspense>
      <Suspense
        fallback={
          <ProductSectionSkeleton className="pb-10 md:pb-[60px] lg:pb-[100px]" />
        }
      >
        <NewArrivalProduct />
      </Suspense>

      {/* <Suspense>
        <DoctorConsultationSection />
      </Suspense> */}
      <Suspense fallback={<CategorySectionSkeleton />}>
        <ShopByConcern />
      </Suspense>
      <Suspense fallback={<FeaturedCategorySectionSkeleton />}>
        <BannerTwo />
      </Suspense>

      <Suspense
        fallback={
          <ProductSectionSkeleton className="py-10 md:py-[60px] lg:py-[100px]" />
        }
      >
        <ShopByCategorySection />
      </Suspense>

      {/* <Suspense fallback={<ProductHighlightCardSkeleton />}>
        <ProductHighlightSection />
      </Suspense> */}

      {/* <Suspense>
        <TestimonialSection />
      </Suspense> */}
    </>
  );
}

export const metadata: Metadata = {
  metadataBase: new URL("https://glowaro.com"),
};
