"use client";

import ProductFilterBrandSection from "@/app/category/_sections/ProductFilterBrandSection";
import { useBrandStore } from "@/store/filter/useBrandStore";
import { useEffect } from "react";

interface Props {
  brand: string;
}

const BrandWrapper = ({ brand }: Props) => {
  const { setFilter } = useBrandStore();

  useEffect(() => {
    setFilter("brand", brand);
  }, [brand]);

  return (
    <>
      <ProductFilterBrandSection />
    </>
  );
};

export default BrandWrapper;
