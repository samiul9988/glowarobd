"use client";

import { Slider } from "@/components/ui/slider";
import { currencySymbol } from "@/config/apiConfig";
import { useCategoryProducts } from "@/hooks/filter/useCategoryProducts";
import { useCategoryStore } from "@/store/filter/useCategoryStore";
import { ChevronDown } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { FaAngleDown } from "react-icons/fa";

interface PriceRangeSelectionProps {
  isOpen: boolean;
  onToggle: () => void;
}

export default function PriceRangeSelection({ isOpen, onToggle }: PriceRangeSelectionProps) {
  const { min_price, max_price, setFilter, category } = useCategoryStore();
  const { data } = useCategoryProducts();

  // Cache the initial min/max range per category (never changes after first load)
  const [categoryRanges, setCategoryRanges] = useState<
    Record<string | number, { min: number; max: number }>
  >({});

  const currentRange = category ? categoryRanges[category] : undefined;

  // Store the initial range ONCE per category
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

  // Fixed range from cache
  const apiMin = currentRange?.min ?? 0;
  const apiMax = currentRange?.max ?? 0;

  // Slider values - reflect current filter or default to range
  const [sliderValues, setSliderValues] = useState<[number, number]>([
    apiMin,
    apiMax,
  ]);

  const debounceRef = useRef<NodeJS.Timeout | null>(null);

  // Update slider position when range is available or filters change
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

  // Don't render until we have valid data
  if (!data || apiMin === 0 || apiMax === 0 || apiMin >= apiMax) {
    return (
      <div className="text-sm text-gray-400 rounded-full w-[130px] h-[36px] bg-site-gray-50"></div>
    );
  }

  return (
    <div className="relative filter-dropdown">
      <button
        onClick={onToggle}
        className={`filter-button ${isOpen ? 'active-filter' : ''}`}
      >
        Price
        <ChevronDown size={16} className="w-4 h-4 font-normal" />
      </button>
      {isOpen && (
        <div className="filter-content ">
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

            <div className="flex gap-2 mt-4">
              {/* Min Input */}
              <div className="flex flex-1 flex-col">
                <label
                  htmlFor="min"
                  className="text-site-gray-400 text-sm font-normal mb-1"
                >
                  Min
                </label>
                <input
                  id="min"
                  type="text"
                  inputMode="numeric"
                  placeholder="Min price"
                  className="border-site-secondary-50 focus:border-site-gray-100 text-site-gray-900 bg-site-secondary-50 no-spinner w-full !appearance-none rounded-[4px] border px-2 py-1 text-base font-semibold focus:outline-none"
                  value={`${currencySymbol}${sliderValues[0]}`}
                  onChange={handleMinInputChange}
                />
              </div>

              {/* Max Input */}
              <div className="flex flex-1 flex-col">
                <label
                  htmlFor="max"
                  className="text-site-gray-400 text-sm font-normal mb-1"
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
}
