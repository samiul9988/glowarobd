import { create } from "zustand";

interface ProductFilterState {
  category: string | number | null;
  page: number;
  limit: number;
  name: string;
  brand_id: string;
  min_price: number | null;
  max_price: number | null;
  sort_by: string;
  rating: number | null;

  // cache min/max by category
  cachedMinMax: Record<string | number, { min: number; max: number }>;

  setFilter: (key: string, value: any) => void;
  setMinMaxCache: (category: string | number, min: number, max: number) => void;
  resetFilters: (category?: string | number | null) => void;
}

export const useCategoryStore = create<ProductFilterState>((set, get) => ({
  category: null,
  page: 1,
  limit: 12,
  name: "",
  brand_id: "",
  min_price: null,
  max_price: null,
  sort_by: "rand",
  rating: null,
  cachedMinMax: {},

  setFilter: (key, value) =>
    set((state) => {
      // Reset filters when category changes
      if (key === "category" && value !== state.category) {
        return {
          category: value,
          page: 1,
          limit: 12,
          name: "",
          brand_id: "",
          min_price: null,
          max_price: null,
          sort_by: "",
          rating: null,
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

  // Cache min/max per category
  setMinMaxCache: (category, min, max) =>
    set((state) => ({
      cachedMinMax: {
        ...state.cachedMinMax,
        [category]: { min, max },
      },
    })),

  resetFilters: (category = get().category) =>
    set({
      category,
      page: 1,
      limit: 12,
      name: "",
      brand_id: "",
      min_price: null,
      max_price: null,
      sort_by: "",
      rating: null,
    }),
}));
