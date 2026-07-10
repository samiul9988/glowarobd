"use client";

import { Slider } from "@/components/ui/slider";
import { currencySymbol } from "@/config/apiConfig";
import { useSearchProducts } from "@/hooks/filter/useSearchProducts";
import { useSearchStore } from "@/store/filter/useSearchStore";
import { ChevronDown } from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

interface PriceRangeSearchDropdownProps {
  isOpen: boolean;
  onToggle: () => void;
}

const PriceRangeSearchDropdown = ({
  isOpen,
  onToggle,
}: PriceRangeSearchDropdownProps) => {
  const { min_price, max_price, setSearchFilter, keyword } = useSearchStore();
  const { data } = useSearchProducts();

  const [keywordRanges, setKeywordRanges] = useState<
    Record<string | number, { min: number; max: number }>
  >({});

  const currentRange = keyword ? keywordRanges[keyword] : undefined;

  useEffect(() => {
    if (
      keyword &&
      data?.min_price != null &&
      data?.max_price != null &&
      !keywordRanges[keyword]
    ) {
      const min = Number(data.min_price);
      const max = Number(data.max_price);
      setKeywordRanges((prev) => ({
        ...prev,
        [keyword]: { min, max },
      }));
    }
  }, [data, keyword, keywordRanges]);

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
      setSearchFilter("min_price", value[0]);
      setSearchFilter("max_price", value[1]);
      setSearchFilter("page", 1);
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
      setSearchFilter("min_price", clamped);
      setSearchFilter("page", 1);
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
      setSearchFilter("max_price", clamped);
      setSearchFilter("page", 1);
    }, 500);
  };

  useEffect(() => {
    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, []);

  if (!data || apiMin === 0 || apiMax === 0 || apiMin >= apiMax) {
    return (
      <div className="text-sm text-gray-400 rounded-full w-[130px] h-[36px] bg-site-gray-50" />
    );
  }

  return (
    <div className="relative filter-dropdown">
      <button
        onClick={onToggle}
        className={`filter-button ${isOpen ? "active-filter" : ""}`}
      >
        Price
        <ChevronDown size={16} className="w-4 h-4 font-normal" />
      </button>
      {isOpen && (
        <div className="filter-content">
          <div className="mt-4">
            <Slider
              min={apiMin}
              max={apiMax}
              step={1}
              value={sliderValues}
              onValueChange={handleSliderChange}
              className="[&_[role=slider]]:border-site-secondary-500 w-full pl-2 [&_[role=slider]]:border-3 [&_[role=slider]]:bg-white"
            />
          </div>

          <div className="mt-4 flex gap-2">
            <div className="flex flex-1 flex-col">
              <label
                htmlFor="min"
                className="text-site-gray-400 mb-1 text-sm font-normal"
              >
                Min
              </label>
              <input
                id="min"
                type="text"
                inputMode="numeric"
                placeholder="Min price"
                className="border-site-secondary-50 bg-site-secondary-50 focus:border-site-gray-100 text-site-gray-900 no-spinner w-full !appearance-none rounded-[4px] border px-2 py-1 text-base font-semibold focus:outline-none"
                value={`${currencySymbol}${sliderValues[0]}`}
                onChange={handleMinInputChange}
              />
            </div>

            <div className="flex flex-1 flex-col">
              <label
                htmlFor="max"
                className="text-site-gray-400 mb-1 text-sm font-normal"
              >
                Max
              </label>
              <input
                id="max"
                type="text"
                inputMode="numeric"
                placeholder="Max price"
                className="border-site-secondary-50 bg-site-secondary-50 focus:border-site-gray-100 text-site-gray-900 no-spinner w-full !appearance-none rounded-[4px] border px-2 py-1 text-base font-semibold focus:outline-none"
                value={`${currencySymbol}${sliderValues[1]}`}
                onChange={handleMaxInputChange}
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PriceRangeSearchDropdown;

