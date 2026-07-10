"use client";

import { useEffect } from "react";
import ProductFilterSection from "../_sections/ProductFilterSection";
import { useCategoryStore } from "@/store/filter/useCategoryStore";

interface Props {
  category_slug: string;
  allCategories: CategoryNode[];
}

const CategoryWrapper = ({ category_slug , allCategories }: Props) => {
  const { setFilter } = useCategoryStore();

  useEffect(() => {
    setFilter("category", category_slug);
  }, [category_slug]);

  return (
    <>
      <ProductFilterSection allCategories={allCategories} />
    </>
  );
};

export default CategoryWrapper;
