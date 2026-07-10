"use client";

import { useSearchStore } from "@/store/filter/useSearchStore";
import { useEffect } from "react";
import ProductFilterSearchSection from "../category/_sections/ProductFilterSearchSection";

interface Props {
  keywordVal: string;
}

const SearchWrapper = ({ keywordVal }: Props) => {
  const { setSearchFilter } = useSearchStore();

  useEffect(() => {
    setSearchFilter("keyword", keywordVal);
  }, [keywordVal]);

  return (
    <>
      <ProductFilterSearchSection />
    </>
  );
};

export default SearchWrapper;
