"use client";

import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { useSearchStore } from "@/store/filter/useSearchStore";
import { useQuery } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import { IoStar } from "react-icons/io5";
import { ChevronDown } from "lucide-react";
import PriceRangeSearchDropdown from "./PriceRangeSearchDropdown";

type FilterKey = "price" | "rating" | "brand" | null;

const FilterSearchInlineWidgets = () => {
  const { rating, brand_id, setSearchFilter } = useSearchStore();
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
    setSearchFilter("rating", newRating);
    setSearchFilter("page", 1);
  };

  const handleBrandSelect = (slug: string) => {
    const newValue = selectedBrand === slug ? null : slug;
    setSelectedBrand(newValue);
    setSearchFilter("brand_id", newValue);
    setSearchFilter("page", 1);
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
        <PriceRangeSearchDropdown
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
                  id="search-rating-all"
                  checked={selectedRating === null}
                  onCheckedChange={() => handleRatingSelect(null)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label
                  htmlFor="search-rating-all"
                  className="!text-site-gray-900 cursor-pointer"
                >
                  All
                </Label>
              </div>
              {[5, 4, 3, 2, 1].map((star) => (
                <div className="flex items-center gap-2.5 py-1" key={star}>
                  <Checkbox
                    id={`search-rating-${star}`}
                    checked={selectedRating === star}
                    onCheckedChange={() => handleRatingSelect(star)}
                    className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                  />
                  <Label
                    htmlFor={`search-rating-${star}`}
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
            <div className="filter-content max-h-[320px] overflow-y-auto">
              {allBrands && allBrands.data.length > 0 ? (
                allBrands.data.map((brand) => (
                  <div className="flex items-center gap-2.5 py-1" key={brand.slug}>
                    <Checkbox
                      id={`search-brand-${brand.slug}`}
                      checked={selectedBrand === brand.slug}
                      onCheckedChange={() => handleBrandSelect(brand.slug)}
                      className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                    />
                    <Label
                      htmlFor={`search-brand-${brand.slug}`}
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
          )}
        </div>
      </div>
    </div>
  );
};

export default FilterSearchInlineWidgets;

