"use client";

import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { useBrandStore } from "@/store/filter/useBrandStore";
import { useQuery } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import { IoStar } from "react-icons/io5";
import { ChevronDown } from "lucide-react";
import PriceRangeBrandDropdown from "./PriceRangeBrandDropdown";
import { ScrollArea } from "@/components/ui/scroll-area";

type FilterKey = "price" | "rating" | "brand" | null;

const FilterBrandInlineWidgets = () => {
  const { rating, brand_id, setFilter } = useBrandStore();
  const [selectedRating, setSelectedRating] = useState<number | null>(
    rating ?? null,
  );
  const [selectedBrand, setSelectedBrand] = useState<string | null>(
    brand_id ?? null,
  );
  const [openFilter, setOpenFilter] = useState<FilterKey>(null);

  const handleRatingSelect = (value: number | null) => {
    const newRating = value === selectedRating ? null : value;
    setSelectedRating(newRating);
    setFilter("rating", newRating);
    setFilter("page", 1);
  };

  const handleBrandSelect = (slug: string) => {
    const newValue = selectedBrand === slug ? null : slug;
    setSelectedBrand(newValue);
    setFilter("brand_id", newValue);
    setFilter("page", 1);
  };

  useEffect(() => {
    setSelectedRating(rating ?? null);
  }, [rating]);

  useEffect(() => {
    setSelectedBrand(brand_id ?? null);
  }, [brand_id]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as HTMLElement;
      if (!target.closest(".filter-dropdown")) {
        setOpenFilter(null);
      }
    };

    if (openFilter) {
      document.addEventListener("mousedown", handleClickOutside);
      return () => document.removeEventListener("mousedown", handleClickOutside);
    }
  }, [openFilter]);

  const { data: allBrands } = useQuery({
    queryKey: ["brands"],
    queryFn: async () => {
      const res = await cacheableFetcher(`/brands?limit=50`, {
        next: {
          revalidate: 300,
        },
      });

      return res as BrandApiResponse<BrandItemResponse[]>;
    },
  });

  return (
    <div className="top-4 self-start max-md:hidden">
      <div className="flex items-center gap-3">
        <PriceRangeBrandDropdown
          isOpen={openFilter === "price"}
          onToggle={() =>
            setOpenFilter(openFilter === "price" ? null : "price")
          }
        />

        <div className="relative filter-dropdown">
          <button
            className={`filter-button ${openFilter === "rating" ? "active-filter" : ""}`}
            onClick={() =>
              setOpenFilter(openFilter === "rating" ? null : "rating")
            }
          >
            Rating
            <ChevronDown size={16} className="h-4 w-4 font-normal" />
          </button>
          {openFilter === "rating" && (
            <div className="filter-content">
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="brand-rating-all"
                  checked={selectedRating === null}
                  onCheckedChange={() => handleRatingSelect(null)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="brand-rating-all"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  All
                </Label>
              </div>
              {[5, 4, 3, 2, 1].map((star) => (
                <div className="flex items-center gap-2.5 py-1" key={star}>
                  <Checkbox
                    id={`brand-rating-${star}`}
                    checked={selectedRating === star}
                    onCheckedChange={() => handleRatingSelect(star)}
                    className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                  />
                  <Label
                    htmlFor={`brand-rating-${star}`}
                    className="!text-site-gray-900 cursor-pointer"
                  >
                    <div className="flex items-center gap-0.5">
                      {[...Array(5)].map((_, idx) => (
                        <IoStar
                          key={idx}
                          className={idx < star ? "text-[#FF8A00]" : "text-site-gray-100"}
                          size={16}
                        />
                      ))}
                      <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">
                        {star}.0
                      </span>
                    </div>
                  </Label>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="relative filter-dropdown">
          <button
            className={`filter-button ${openFilter === "brand" ? "active-filter" : ""}`}
            onClick={() =>
              setOpenFilter(openFilter === "brand" ? null : "brand")
            }
          >
            Brand
            <ChevronDown size={16} className="h-4 w-4 font-normal" />
          </button>
          {openFilter === "brand" && (
            <ScrollArea className="h-[320px] w-full rounded-md border filter-content" data-lenis-ignore>
            <div className="p-2">
              {allBrands && allBrands.data.length > 0 ? (
                allBrands.data.map((brand) => (
                  <div 
                    key={brand.slug} 
                    className="flex items-center gap-2.5 py-1"
                  >
                    <Checkbox
                      id={`brand-filter-${brand.slug}`}
                      checked={selectedBrand === brand.slug}
                      onCheckedChange={() => handleBrandSelect(brand.slug)}
                      className="h-5 w-5 border border-gray-300 !bg-white 
                        data-[state=checked]:!border-site-secondary-500 
                        data-[state=checked]:!bg-site-secondary-500 
                        data-[state=checked]:text-white"
                    />
                    <Label
                      htmlFor={`brand-filter-${brand.slug}`}
                      className="!text-site-gray-900 flex w-full cursor-pointer py-1 md:py-2"
                    >
                      {brand.name}
                    </Label>
                  </div>
                ))
              ) : (
                <p className="text-sm text-gray-500">No brands found</p>
              )}
            </div>
          </ScrollArea>
          )}
        </div>
      </div>
    </div>
  );
};

export default FilterBrandInlineWidgets;

