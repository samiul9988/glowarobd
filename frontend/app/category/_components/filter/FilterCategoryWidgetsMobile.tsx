"use client";

import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { fetchCategories } from "@/lib/api/fetchCategories";
import { useQuery } from "@tanstack/react-query";
import { useState } from "react";
import { IoStar } from "react-icons/io5";
import { useCategoryStore } from "@/store/filter/useCategoryStore";
import { Slider } from "@/components/ui/slider";
import { currencySymbol } from "@/config/apiConfig";
import { useCategoryProducts } from "@/hooks/filter/useCategoryProducts";
import { useEffect, useRef } from "react";
import Link from "next/link";
import CategoriesAccordion from "../CategoriesAccordion";

const FilterCategoryWidgetsMobile = () => {
  // Rating filtering
  const { rating, setFilter, category } = useCategoryStore();
  const [selectedRating, setSelectedRating] = useState<number | null>(
    rating ?? null,
  );

  const handleRatingSelect = (value: number | null) => {
    const newRating = value === selectedRating ? null : value;
    setSelectedRating(newRating);
    setFilter("rating", newRating);
    setFilter("page", 1);
  };

  // Category filtering
  const { data: categories } = useQuery({
    queryKey: ["categories"],
    queryFn: fetchCategories,
    staleTime: 1000 * 60 * 5,
    refetchOnWindowFocus: false,
  });

  // Price Range
  const { min_price, max_price } = useCategoryStore();
  const { data } = useCategoryProducts();

  const [categoryRanges, setCategoryRanges] = useState<
    Record<string | number, { min: number; max: number }>
  >({});

  const currentRange = category ? categoryRanges[category] : undefined;

  useEffect(() => {
    if (
      category &&
      data?.min_price != null &&
      data?.max_price != null &&
      !categoryRanges[category]
    ) {
      const min = parseFloat(String(data.min_price));
      const max = parseFloat(String(data.max_price));
      setCategoryRanges((prev) => ({
        ...prev,
        [category]: { min, max },
      }));
    }
  }, [data, category, categoryRanges]);

  const apiMin = currentRange?.min ?? 0;
  const apiMax = currentRange?.max ?? 0;

  const [sliderValues, setSliderValues] = useState<[number, number]>([
    apiMin,
    apiMax,
  ]);

  const debounceRef = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    if (apiMin > 0 && apiMax > 0) {
      const currentMin = min_price ?? apiMin;
      const currentMax = max_price ?? apiMax;
      setSliderValues([currentMin, currentMax]);
    }
  }, [apiMin, apiMax, min_price, max_price]);

  const handleSliderChange = (value: [number, number]) => {
    setSliderValues(value);

    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      setFilter("min_price", value[0]);
      setFilter("max_price", value[1]);
    }, 500);
  };

  const handleMinInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const sanitized = e.target.value.replace(/[^\d]/g, "");
    if (sanitized === "") return;

    const numValue = parseInt(sanitized, 10);
    const clamped = Math.max(apiMin, Math.min(sliderValues[1], numValue));

    setSliderValues([clamped, sliderValues[1]]);

    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      setFilter("min_price", clamped);
    }, 500);
  };

  const handleMaxInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const sanitized = e.target.value.replace(/[^\d]/g, "");
    if (sanitized === "") return;

    const numValue = parseInt(sanitized, 10);
    const clamped = Math.max(sliderValues[0], Math.min(apiMax, numValue));

    setSliderValues([sliderValues[0], clamped]);

    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      setFilter("max_price", clamped);
    }, 500);
  };

  useEffect(() => {
    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, []);

  return (
    <div className="w-full py-4">
      <Accordion type="multiple" className="w-full">
        {/* Categories */}
        <AccordionItem value="categories">
          <AccordionTrigger className="mobile-button !py-3">
            Categories
          </AccordionTrigger>
          <AccordionContent>
            <div className="flex flex-col gap-2">
              {categories && categories.length > 0 && (
                <CategoriesAccordion categories={categories} />
              )}
            </div>
          </AccordionContent>
        </AccordionItem>

        {/* Price Range */}
        {apiMin > 0 && apiMax > 0 && (
          <AccordionItem value="price">
            <AccordionTrigger className="mobile-button !py-3">
              Price Range
            </AccordionTrigger>
            <AccordionContent>
              <div className="flex flex-col gap-5 py-4">
                <Slider
                  min={apiMin}
                  max={apiMax}
                  step={1}
                  value={sliderValues}
                  onValueChange={handleSliderChange}
                  className="[&_[role=slider]]:border-site-secondary-500 w-full [&_[role=slider]]:border-3 [&_[role=slider]]:bg-white"
                />

                <div className="flex gap-2">
                  <div className="flex flex-1 flex-col">
                    <label
                      htmlFor="min-mobile"
                      className="text-site-gray-400 mb-1 text-sm font-normal"
                    >
                      Min
                    </label>
                    <input
                      id="min-mobile"
                      type="text"
                      inputMode="numeric"
                      placeholder="Min price"
                      className="border-site-gray-100 focus:border-site-gray-100 text-site-gray-900 w-full rounded-[4px] border p-2.5 text-base font-semibold focus:outline-none"
                      value={`${currencySymbol}${sliderValues[0]}`}
                      onChange={handleMinInputChange}
                    />
                  </div>

                  <div className="flex flex-1 flex-col">
                    <label
                      htmlFor="max-mobile"
                      className="text-site-gray-400 mb-1 text-sm font-normal"
                    >
                      Max
                    </label>
                    <input
                      id="max-mobile"
                      type="text"
                      inputMode="numeric"
                      placeholder="Max price"
                      className="border-site-gray-100 focus:border-site-gray-100 text-site-gray-900 w-full rounded-[4px] border p-2.5 text-base font-semibold focus:outline-none"
                      value={`${currencySymbol}${sliderValues[1]}`}
                      onChange={handleMaxInputChange}
                    />
                  </div>
                </div>
              </div>
            </AccordionContent>
          </AccordionItem>
        )}

        {/* Rating */}
        <AccordionItem value="rating">
          <AccordionTrigger className="mobile-button !py-3">
            Rating
          </AccordionTrigger>
          <AccordionContent>
            <div className="flex flex-col gap-3 py-2">
              <div className="flex items-center gap-2.5">
                <Checkbox
                  id="rating-all-mobile"
                  checked={selectedRating === null}
                  onCheckedChange={() => handleRatingSelect(null)}
                  className="data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="rating-all-mobile"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  All
                </Label>
              </div>

              {[5, 4, 3, 2, 1].map((ratingValue) => (
                <div key={ratingValue} className="flex items-center gap-2.5">
                  <Checkbox
                    id={`rating-${ratingValue}-mobile`}
                    checked={selectedRating === ratingValue}
                    onCheckedChange={() => handleRatingSelect(ratingValue)}
                    className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-blue-500 data-[state=checked]:!bg-blue-500 data-[state=checked]:text-white"
                  />
                  <Label
                    htmlFor={`rating-${ratingValue}-mobile`}
                    className="!text-site-gray-900 cursor-pointer"
                  >
                    <div className="flex items-center gap-0.5">
                      {Array.from({ length: 5 }).map((_, index) => (
                        <IoStar
                          key={index}
                          className={
                            index < ratingValue
                              ? "text-[#FF8A00]"
                              : "text-site-gray-100"
                          }
                          size={16}
                        />
                      ))}
                      <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                        {ratingValue}.0
                      </span>
                    </div>
                  </Label>
                </div>
              ))}
            </div>
          </AccordionContent>
        </AccordionItem>
      </Accordion>
    </div>
  );
};

export default FilterCategoryWidgetsMobile;
