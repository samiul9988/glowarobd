import { create } from "zustand";

interface SearchState {
  category: string | number | null;
  keyword: string;
  page: number;
  limit: number;
  min_price: number | null;
  max_price: number | null;
  brand_id: string;
  rating: number | null;
  sort_by: string;

  // cache min/max per keyword
  cachedMinMax: Record<string, { min: number; max: number }>;

  setSearchFilter: (key: string, value: any) => void;
  setMinMaxCache: (keyword: string, min: number, max: number) => void;
  resetSearch: (keyword?: string) => void;
}

export const useSearchStore = create<SearchState>((set, get) => ({
  category: "",
  keyword: "",
  page: 1,
  limit: 12,
  min_price: null,
  max_price: null,
  brand_id: "",
  rating: null,
  sort_by: "rand",
  cachedMinMax: {},

  setSearchFilter: (key, value) =>
    set((state) => {
      // Reset filters when keyword changes
      if (key === "keyword" && value !== state.keyword) {
        return {
          keyword: value,
          page: 1,
          limit: 12,
          min_price: null,
          max_price: null,
          brand: "",
          rating: null,
          sort_by: "",
        };
      }

      // Reset filters when min_price or max_price changes
      if (
        (key === "min_price" && value !== state.min_price) ||
        (key === "max_price" && value !== state.max_price)
      ) {
        return {
          ...state,
          page: 1,
          limit: 12,
          name: "",
          brand_id: "",
          sort_by: "",
          rating: null,
          [key]: value,
        };
      }
      return { ...state, [key]: value };
    }),

  setMinMaxCache: (keyword, min, max) =>
    set((state) => ({
      cachedMinMax: {
        ...state.cachedMinMax,
        [keyword]: { min, max },
      },
    })),

  resetSearch: (keyword = get().keyword) =>
    set({
      keyword,
      page: 1,
      limit: 12,
      min_price: null,
      max_price: null,
      brand_id: "",
      rating: null,
      sort_by: "",
    }),
}));
