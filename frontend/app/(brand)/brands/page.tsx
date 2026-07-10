import PageHeader from "@/components/PageHeader";
import { ChevronRight } from "lucide-react";
import React from "react";
import BrandsListSection from "./_components/BrandsListSection";
import { metaData } from "@/metadata/staticMetaData";

const Brands = () => {
  return (
    <>
      <PageHeader
        title="Top Brands"
        breadcrumb={
          <span className="flex items-center gap-2 text-sm font-medium text-white/50">
            Home <ChevronRight size={15} />
            <span className="text-white/80">Brands</span>
          </span>
        }
        animateImg1Url="/images/product-1.png"
        animateImg1Style="h-[55px] w-[55px] md:h-[95px] md:w-[95px] absolute left-2 -bottom-2 md:left-16 md:-bottom-3"
        animateImg2Url="/images/product-2.png"
        animateImg2Style="h-[60px] w-[60px] md:h-[123px] md:w-[55px] absolute left-5 -top-2 md:left-38 lg:left-42 md:-top-5"
        animateImg4Url="/images/product-4.png"
        animateImg4Style="h-[150px] w-[30px] md:h-[200px] md:w-[35px] lg:h-[227px] lg:w-[40px] absolute right-5 top-4"
      />
      <BrandsListSection />
    </>
  );
};

export default Brands;

export const metadata = {
  title: metaData.brands.title,
  description: metaData.brands.description,
};
