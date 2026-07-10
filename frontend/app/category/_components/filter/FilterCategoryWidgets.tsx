"use client";

import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { fetchCategories } from "@/lib/api/fetchCategories";
import { useQuery } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import { IoStar } from "react-icons/io5";
import CategoriesAccordion from "../CategoriesAccordion";
import PriceRangeSelection from "./PriceRangeSelection";
import { useCategoryStore } from "@/store/filter/useCategoryStore";
import { FaAngleDown } from "react-icons/fa";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { ChevronDown } from "lucide-react";

const FilterCategoryWidgets = ({ allCategories:categories }: { allCategories: CategoryNode[] }) => {
  // Unified state for which filter is open
  const [openFilter, setOpenFilter] = useState<'category' | 'price' | 'rating' | null>(null);

  // Rating filtering
  const { rating, setFilter } = useCategoryStore();
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
  // const { data: categories } = useQuery({
  //   queryKey: ["categories"],
  //   queryFn: fetchCategories,
  //   staleTime: 1000 * 60 * 5,
  //   refetchOnWindowFocus: false,
  // });

  // Close filter when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as HTMLElement;
      if (!target.closest('.filter-dropdown')) {
        setOpenFilter(null);
      }
    };

    if (openFilter) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }
  }, [openFilter]);


  // Brand filtering
//   const { data: allBrands } = useQuery({
//     queryKey: ["brands"],
//     queryFn: async () => {
//       const res = await cacheableFetcher(`/brands?limit=50`, {
//          revalidate: 300,

//       });

//       return res as BrandApiResponse<BrandItemResponse[]>;
//     },
//   });

  const { brand_id, setFilter: setBrandFilter } = useCategoryStore();
  const [selectedBrand, setSelectedBrand] = useState<string | null>(
    brand_id ?? null,
  );



  return (
    <div className="top-4 self-start max-md:hidden">
      <div className="gap-3 flex items-center ">
        {/* Category Filter */}
        <div className="relative filter-dropdown">
          <button 
            className={`filter-button ${openFilter === 'category' ? 'active-filter' : ''}`} 
            onClick={() => setOpenFilter(openFilter === 'category' ? null : 'category')}
          >
            Categories 
            <ChevronDown size={16} className="w-4 h-4 font-normal" />

          </button>
          {openFilter === 'category' && 
            <div className="filter-content">
              {categories && categories.length > 0 && (
                <CategoriesAccordion categories={categories} />
              )}
            </div>
          }
        </div>

        {/* Price Filter */}
        <PriceRangeSelection 
          isOpen={openFilter === 'price'}
          onToggle={() => setOpenFilter(openFilter === 'price' ? null : 'price')}
        />

        {/* Rating Filter */}
        <div className="relative filter-dropdown">
          <button 
            className={`filter-button ${openFilter === 'rating' ? 'active-filter' : ''}`} 
            onClick={() => setOpenFilter(openFilter === 'rating' ? null : 'rating')}
          >
            Rating
            <ChevronDown size={16} className="w-4 h-4 font-normal" />

          </button>
          {openFilter === 'rating' && (
            <div className="filter-content">
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="rating-all"
                  checked={selectedRating === null}
                  onCheckedChange={() => handleRatingSelect(null)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label htmlFor="rating-all" className="!text-site-gray-900 cursor-pointer">
                  All
                </Label>
              </div>
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="rating-5"
                  checked={selectedRating === 5}
                  onCheckedChange={() => handleRatingSelect(5)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label htmlFor="rating-5" className="!text-site-gray-900 cursor-pointer">
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">5.0</span>
                  </div>
                </Label>
              </div>
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="rating-4"
                  checked={selectedRating === 4}
                  onCheckedChange={() => handleRatingSelect(4)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label htmlFor="rating-4" className="!text-site-gray-900 cursor-pointer">
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">4.0</span>
                  </div>
                </Label>
              </div>
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="rating-3"
                  checked={selectedRating === 3}
                  onCheckedChange={() => handleRatingSelect(3)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label htmlFor="rating-3" className="!text-site-gray-900 cursor-pointer">
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">3.0</span>
                  </div>
                </Label>
              </div>
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="rating-2"
                  checked={selectedRating === 2}
                  onCheckedChange={() => handleRatingSelect(2)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label htmlFor="rating-2" className="!text-site-gray-900 cursor-pointer">
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">2.0</span>
                  </div>
                </Label>
              </div>
              <div className="flex items-center gap-2.5 py-1">
                <Checkbox
                  id="rating-1"
                  checked={selectedRating === 1}
                  onCheckedChange={() => handleRatingSelect(1)}
                  className="h-5 w-5 border border-gray-300 !bg-white data-[state=checked]:!border-site-secondary-500 data-[state=checked]:!bg-site-secondary-500 data-[state=checked]:text-white"
                />
                <Label htmlFor="rating-1" className="!text-site-gray-900 cursor-pointer">
                  <div className="flex items-center gap-0.5">
                    <IoStar className="text-[#FF8A00]" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <IoStar className="text-site-gray-100" size={16} />
                    <span className="text-site-gray-900 mt-0.5 ml-0.5 inline-block text-sm font-semibold">1.0</span>
                  </div>
                </Label>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default FilterCategoryWidgets;
