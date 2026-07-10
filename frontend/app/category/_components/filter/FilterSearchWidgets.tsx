"use client";

import { FilterIcon } from "@/components/icons/icon-library";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { CATEGORY_REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetchCategories } from "@/lib/api/fetchCategories";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { useCategoryStore } from "@/store/filter/useCategoryStore";
import { useSearchStore } from "@/store/filter/useSearchStore";
import { useQuery } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import { IoStar } from "react-icons/io5";
import CategoriesAccordion from "../CategoriesAccordion";
import PriceRangeSearchSelection from "./PriceRangeSearchSelection";

const FilterSearchWidgets = () => {
  // Rating filtering
  const {
    rating,
    category,
    brand_id: brand,
    setSearchFilter,
  } = useSearchStore();
  const [selectedRating, setSelectedRating] = useState<number | null>(
    rating ?? null,
  );

  const handleRatingSelect = (value: number | null) => {
    const newRating = value === selectedRating ? null : value;
    setSelectedRating(newRating);
    setSearchFilter("rating", newRating);
    setSearchFilter("page", 1); // reset pagination
  };

  // Brand filtering
  const { data: allBrands } = useQuery({
    queryKey: ["brands"],
    queryFn: async () => {
      const res = await cacheableFetcher(`/brands?limit=50`, {
        next: {
          revalidate: CATEGORY_REVALIDATE_TIME,
        },
      });

      return res as BrandApiResponse<BrandItemResponse[]>;
    },
  });

  const { brand_id, setFilter: setBrandFilter } = useCategoryStore();
  const [selectedBrand, setSelectedBrand] = useState<string | null>(
    brand_id ?? null,
  );

  const handleBrandSelect = (slug: string) => {
    const newValue = selectedBrand === slug ? null : slug;
    setSelectedBrand(newValue);
    setSearchFilter("brand_id", newValue);
    setSearchFilter("page", 1); // reset pagination
  };

  // Scroll to top
  useEffect(() => {
    setTimeout(() => {
      window.scrollTo({
        top: 0,
        behavior: "instant",
      });

      // Force Framer Motion re-check
      window.dispatchEvent(new Event("scroll"));
    }, 50);
  }, [rating, brand_id, brand, category]);

  // Category filtering
  const { data: categories } = useQuery({
    queryKey: ["categories"],
    queryFn: fetchCategories,
    staleTime: 1000 * 60 * 5, // cache for 5 minutes
    refetchOnWindowFocus: false, // optional
  });

  return (
    <div className="top-4 w-full self-start lg:w-[288px]">
      <div className="mb-4 flex items-center gap-3">
        <FilterIcon />
        <span className="text-site-gray-900 text-base font-semibold">
          Filter
        </span>
      </div>

      <div className="space-y-4 lg:space-y-14">
        {/* Price Filter */}
        <PriceRangeSearchSelection />

        {/* Category Filter */}
        <Accordion
          type="single"
          collapsible
          className="w-full"
          defaultValue="price"
        >
          <AccordionItem value="price">
            <AccordionTrigger className="mobile-button">
              Categories
            </AccordionTrigger>
            <AccordionContent className="pt-3 md:pt-6">
              {categories && categories.length > 0 && (
                <CategoriesAccordion categories={categories} />
              )}
            </AccordionContent>
          </AccordionItem>
        </Accordion>

        {/* Rating Filter */}
        <Accordion
          type="single"
          collapsible
          className="w-full"
          defaultValue="price"
        >
          <AccordionItem value="price">
            <AccordionTrigger className="mobile-button">
              Rating
            </AccordionTrigger>
            <AccordionContent className="flex flex-col gap-2.5 pt-3 text-balance md:gap-4 md:pt-6">
              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-all"
                  checked={selectedRating === null}
                  onCheckedChange={() => handleRatingSelect(null)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-all"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  All
                </Label>
              </div>
              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-5"
                  checked={selectedRating === 5}
                  onCheckedChange={() => handleRatingSelect(5)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-5"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                      5.0
                    </span>
                  </div>
                </Label>
              </div>

              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-4"
                  checked={selectedRating === 4}
                  onCheckedChange={() => handleRatingSelect(4)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-4"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                      4.0
                    </span>
                  </div>
                </Label>
              </div>

              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-3"
                  checked={selectedRating === 3}
                  onCheckedChange={() => handleRatingSelect(3)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-3"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                      3.0
                    </span>
                  </div>
                </Label>
              </div>

              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-2"
                  checked={selectedRating === 2}
                  onCheckedChange={() => handleRatingSelect(2)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-2"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                      2.0
                    </span>
                  </div>
                </Label>
              </div>

              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-1"
                  checked={selectedRating === 1}
                  onCheckedChange={() => handleRatingSelect(1)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-1"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                      1.0
                    </span>
                  </div>
                </Label>
              </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>

        {/* Brand Filter */}
        <Accordion
          type="single"
          collapsible
          className="w-full"
          defaultValue="price"
        >
          <AccordionItem value="brand">
            <AccordionTrigger className="mobile-button">Brand</AccordionTrigger>
            <AccordionContent className="scroll-thin flex max-h-[450px] flex-col gap-1.5 overflow-y-auto pt-3 text-balance md:pt-6">
              {allBrands && allBrands.data.length > 0 && (
                <>
                  {allBrands.data.map((brand: BrandItemResponse) => (
                    <div className="flex items-center gap-2.5" key={brand.slug}>
                      <Checkbox
                        id={brand.slug}
                        checked={selectedBrand === brand.slug}
                        onCheckedChange={() => handleBrandSelect(brand.slug)}
                        className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                      />
                      <Label
                        htmlFor={brand.slug}
                        className="!text-site-gray-900 flex w-full cursor-pointer py-1 md:py-2"
                      >
                        {brand.name}
                      </Label>
                    </div>
                  ))}
                </>
              )}
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </div>
  );
};

export default FilterSearchWidgets;
