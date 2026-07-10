"use client";

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useMediaQuery } from "@/hooks/useMediaQuery";
import { useCategoryStore } from "@/store/filter/useCategoryStore";

import { useTransition } from "react";

const SortBySelectOption = () => {
  const { sort_by, setFilter } = useCategoryStore();
  const [isPending, startTransition] = useTransition();
  const isMobile = useMediaQuery("(max-width: 768px)");

  const handleSortChange = (value: string) => {
    startTransition(() => {
      setFilter("sort_by", value);
      setFilter("page", 1); // reset page on sort change
    });
  };

  return (
    <div className="flex items-center gap-2">
     
      <Select
        value={sort_by === "rand" ? undefined : sort_by}
        onValueChange={handleSortChange}
        disabled={isPending}
      >
        <SelectTrigger className="!ring-0 !outline-0 border-0 shadow-none  !w-[150px] justify-between flex items-center gap-1 text-site-gray-800 cursor-pointer px-5 py-2.5 text-base bg-site-gray-50 rounded-full  transition-colors hover:bg-site-secondary-500 hover:text-white">
          <SelectValue className="" placeholder={isMobile ? "Sort by" : "Sort by"} />
        </SelectTrigger>

        <SelectContent className="ring-0 max-w-[250px]">
          <SelectGroup>
            <SelectLabel hidden>Products</SelectLabel>
            <SelectItem value="newest">Newest</SelectItem>
            <SelectItem value="oldest">Oldest</SelectItem>
            <SelectItem value="price-asc">Low to High</SelectItem>
            <SelectItem value="price-desc">High to Low</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
  );
};

export default SortBySelectOption;
