"use client";

import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { Skeleton } from "@/components/ui/skeleton";
import { Slider } from "@/components/ui/slider";
import { currencySymbol } from "@/config/apiConfig";
import { useSearchProducts } from "@/hooks/filter/useSearchProducts";
import { useSearchStore } from "@/store/filter/useSearchStore";
import { useEffect, useRef, useState } from "react";

export default function PriceRangeSearchSelection() {
  const { min_price, max_price, setSearchFilter, keyword } = useSearchStore();
  const { data } = useSearchProducts();

  // local cache: store first-time range per category
  const [categoryRanges, setCategoryRanges] = useState<
    Record<string | number, { min: number; max: number }>
  >({});

  // when category changes, get its range (if stored)
  const currentRange = keyword ? categoryRanges[keyword] : undefined;

  // if not stored yet and backend gives new min/max → store it
  useEffect(() => {
    if (
      keyword &&
      data?.min_price != null &&
      data?.max_price != null &&
      !categoryRanges[keyword]
    ) {
      setCategoryRanges((prev) => ({
        ...prev,
        [keyword]: {
          min: data.min_price,
          max: data.max_price,
        },
      }));
    }
  }, [data, keyword, categoryRanges]);

  // derived min/max from cache or default 0
  const apiMin = currentRange?.min ?? 0;
  const apiMax = currentRange?.max ?? 0;

  // -------------------------------
  // Slider & Input Logic
  // -------------------------------
  const [minInput, setMinInput] = useState<string>("");
  const [maxInput, setMaxInput] = useState<string>("");
  const debounceRef = useRef<NodeJS.Timeout | null>(null);

  const sanitizeDigits = (v: string) => v.replace(/[^\d]/g, "");
  const clamp = (n: number, min: number, max: number) =>
    Math.max(min, Math.min(max, n));
  const roundToStep = (n: number, step = 1) => Math.round(n / step) * step;

  // reset inputs when category changes or new range comes
  useEffect(() => {
    if (apiMin === 0 && apiMax === 0) return;
    setMinInput(String(clamp(min_price ?? apiMin, apiMin, apiMax)));
    setMaxInput(String(clamp(max_price ?? apiMax, apiMin, apiMax)));
  }, [keyword, apiMin, apiMax]);

  const updateFilters = (min: number, max: number) => {
    setSearchFilter("min_price", min);
    setSearchFilter("max_price", max);
  };

  const handleSliderChange = (value: [number, number]) => {
    const rounded: [number, number] = [
      roundToStep(clamp(value[0], apiMin, apiMax)),
      roundToStep(clamp(value[1], apiMin, apiMax)),
    ];
    setMinInput(String(rounded[0]));
    setMaxInput(String(rounded[1]));

    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      updateFilters(rounded[0], rounded[1]);
    }, 1000);
  };

  useEffect(() => {
    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, []);

  if (!currentRange)
    return (
      <div className="space-y-3">
        <Skeleton className="h-6 w-full" />
        <Skeleton className="h-3 w-3/4" />
        <Skeleton className="h-6 w-full" />
      </div>
    );

  return (
    <Accordion
      type="single"
      collapsible
      className="w-full"
      defaultValue="price"
    >
      <AccordionItem value="price">
        <AccordionTrigger className="mobile-button">Price</AccordionTrigger>

        <AccordionContent className="flex flex-col gap-5 pt-3 pl-1.5 md:pt-6">
          <Slider
            min={apiMin}
            max={apiMax}
            step={1}
            value={[Number(minInput), Number(maxInput)]}
            onValueChange={handleSliderChange}
            className="[&_[role=slider]]:border-site-secondary-500 w-full pl-2 [&_[role=slider]]:border-3 [&_[role=slider]]:bg-white"
          />

          <div className="flex gap-2">
            {/* Min Input */}
            <div className="flex flex-1 flex-col">
              <label
                htmlFor="min"
                className="text-site-gray-400 text-sm font-normal"
              >
                Min
              </label>
              <input
                id="min"
                type="text"
                inputMode="numeric"
                placeholder="Min price"
                className="border-site-gray-100 focus:border-site-gray-100 text-site-gray-900 no-spinner w-full !appearance-none rounded-[4px] border p-2.5 text-base font-semibold focus:outline-none"
                value={`${currencySymbol}${minInput}`}
                onChange={(e) => setMinInput(sanitizeDigits(e.target.value))}
              />
            </div>

            {/* Max Input */}
            <div className="flex flex-1 flex-col">
              <label
                htmlFor="max"
                className="text-site-gray-400 text-sm font-normal"
              >
                Max
              </label>
              <input
                id="max"
                type="text"
                inputMode="numeric"
                placeholder="Max price"
                className="border-site-gray-100 focus:border-site-gray-100 text-site-gray-900 no-spinner w-full !appearance-none rounded-[4px] border p-2.5 text-base font-semibold focus:outline-none"
                value={`${currencySymbol}${maxInput}`}
                onChange={(e) => setMaxInput(sanitizeDigits(e.target.value))}
              />
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  );
}
