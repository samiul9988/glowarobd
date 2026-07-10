import PageHeader from "@/components/PageHeader";
import { ChevronRight } from "lucide-react";
import React from "react";
import WishlistSection from "./_sections/WishlistSection";

const WishlistPage = () => {
  return (
    <>
      <PageHeader
        title="My Wishlist"
        className="max-h-[150px] bg-[linear-gradient(90deg,#FFE0E6_0%,#FBF8F9_49.04%,#FEEEFF_100%)] py-8 md:py-10"
        headingStyle="text-site-secondary-500"
        animateImg1Url="/images/category-header1.png"
        animateImg1Style="absolute -left-12 md:left-12 -bottom-[-20px] md:-bottom-[-30px] h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[146px] lg:w-[150px]"
        animateImg2Url="/images/category-header2.png"
        animateImg2Style="h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[144px] lg:w-[144px] absolute -right-8 md:right-12 -bottom-[-20px] md:-bottom-[-25px]"
      />

      <WishlistSection />
    </>
  );
};

export default WishlistPage;
