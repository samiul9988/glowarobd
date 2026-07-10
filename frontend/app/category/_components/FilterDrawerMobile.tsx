"use client";

import { SlidersHorizontal } from "lucide-react";

import {
  Sheet,
  SheetContent,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import FilterCategoryWidgetsMobile from "./filter/FilterCategoryWidgetsMobile";
import { FilterIcon } from "@/components/icons/icon-library";

const FilterDrawerMobile = () => {
  return (
    <Sheet>
      <SheetTrigger asChild>
        <div className="grid h-[48px] w-[48px] cursor-pointer place-content-center rounded-[10px] bg-gray-50 md:hidden">
          <SlidersHorizontal className="text-site-gray-900" />
        </div>
      </SheetTrigger>
      <SheetContent className="p-0" tabIndex={-1}>
        <div className="mt-2 ml-4 flex items-center gap-3">
          <FilterIcon />
          <span className="text-site-gray-900 text-base font-semibold">
            Filter
          </span>
        </div>
        <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
          <ScrollArea.Viewport
            className="h-[calc(100dvh-52px)] p-4"
            onWheel={(e) => e.stopPropagation()}
          >
            <FilterCategoryWidgetsMobile />
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

export default FilterDrawerMobile;
