import { create } from "zustand";

interface BrandFilterState {
  category: string | number | null;
  brand: string | number | null;
  page: number;
  limit: number;
  name: string;
  brand_id: string;
  min_price: number | null;
  max_price: number | null;
  sort_by: string;
  rating: number | null;

  cachedMinMax: Record<string | number, { min: number; max: number }>;

  setFilter: (key: string, value: any) => void;
  setMinMaxCache: (category: string | number, min: number, max: number) => void;
  resetFilters: (brand?: string | number | null) => void;
}

export const useBrandStore = create<BrandFilterState>((set, get) => ({
  category: null,
  brand: null,
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
      // Reset filters when brand changes
      if (key === "brand" && value !== state.brand) {
        return {
          ...state,
          brand: value,
          page: 1,
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

  setMinMaxCache: (category, min, max) =>
    set((state) => ({
      cachedMinMax: {
        ...state.cachedMinMax,
        [category]: { min, max },
      },
    })),

  resetFilters: (brand = get().brand) =>
    set({
      ...get(),
      brand,
      page: 1,
      name: "",
      brand_id: "",
      min_price: null,
      max_price: null,
      sort_by: "",
      rating: null,
    }),
}));
