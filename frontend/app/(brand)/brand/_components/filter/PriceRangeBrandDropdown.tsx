"use client";

import { Slider } from "@/components/ui/slider";
import { currencySymbol } from "@/config/apiConfig";
import { useBrandProducts } from "@/hooks/filter/useBrandProducts";
import { useBrandStore } from "@/store/filter/useBrandStore";
import { ChevronDown } from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

interface PriceRangeBrandDropdownProps {
  isOpen: boolean;
  onToggle: () => void;
}

const PriceRangeBrandDropdown = ({
  isOpen,
  onToggle,
}: PriceRangeBrandDropdownProps) => {
  const { min_price, max_price, setFilter, brand } = useBrandStore();
  const { data } = useBrandProducts();

  const [brandRanges, setBrandRanges] = useState<
    Record<string | number, { min: number; max: number }>
  >({});

  const currentRange = brand ? brandRanges[brand] : undefined;

  useEffect(() => {
    if (
      brand &&
      data?.min_price != null &&
      data?.max_price != null &&
      !brandRanges[brand]
    ) {
      const min = Number(data.min_price);
      const max = Number(data.max_price);
      setBrandRanges((prev) => ({
        ...prev,
        [brand]: { min, max },
      }));
    }
  }, [data, brand, brandRanges]);

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
      setFilter("page", 1);
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
      setFilter("page", 1);
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
      setFilter("page", 1);
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
                htmlFor="brand-min"
                className="text-site-gray-400 mb-1 text-sm font-normal"
              >
                Min
              </label>
              <input
                id="brand-min"
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
                htmlFor="brand-max"
                className="text-site-gray-400 mb-1 text-sm font-normal"
              >
                Max
              </label>
              <input
                id="brand-max"
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

export default PriceRangeBrandDropdown;

