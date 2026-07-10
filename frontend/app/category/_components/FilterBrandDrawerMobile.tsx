"use client";

import { SlidersHorizontal } from "lucide-react";

import {
  Sheet,
  SheetContent,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import FilterCategoryWidgets from "./filter/FilterCategoryWidgets";
import FilterBrandWidgets from "./filter/FilterBrandWidgets";

const FilterBrandDrawerMobile = () => {
  return (
    <Sheet>
      <SheetTrigger asChild>
        <div className="grid h-[52px] w-[52px] cursor-pointer place-content-center rounded-[10px] bg-gray-50 lg:hidden">
          <SlidersHorizontal className="text-site-gray-900" />
        </div>
      </SheetTrigger>
      <SheetContent className="p-0" tabIndex={-1}>
        <SheetTitle className="hidden">Filters</SheetTitle>
        <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
          <ScrollArea.Viewport
            className="h-[calc(100dvh-52px)] p-4"
            onWheel={(e) => e.stopPropagation()}
          >
            <FilterBrandWidgets />
          </ScrollArea.Viewport>
          <ScrollArea.Scrollbar
            orientation="vertical"
            className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
          >
            <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
          </ScrollArea.Scrollbar>
        </ScrollArea.Root>
      </SheetContent>
    </Sheet>
  );
};

export default FilterBrandDrawerMobile;
