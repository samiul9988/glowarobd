import { apiBaseUrl } from "@/config/apiConfig";
import { useBrandStore } from "@/store/filter/useBrandStore";
import { useQuery } from "@tanstack/react-query";

export const useBrandProducts = () => {
  const filters = useBrandStore();
  const setMinMaxCache = useBrandStore((s) => s.setMinMaxCache);

  return useQuery<FilteringResponse>({
    queryKey: [
      "brand-products",
      filters.brand,
      filters.category,
      filters.page,
      filters.limit,
      filters.name,
      filters.brand_id,
      filters.min_price,
      filters.max_price,
      filters.sort_by,
      filters.rating,
    ],
    queryFn: async () => {
      if (!filters.brand) return null; // safety check

      const params = new URLSearchParams();
      if (filters.page) params.append("page", String(filters.page));
      if (filters.limit) params.append("limit", String(filters.limit));
      if (filters.name) params.append("name", filters.name);
      if (filters.brand_id) params.append("brand_id", filters.brand_id);
      if (filters.min_price != null)
        params.append("min_price", String(filters.min_price));
      if (filters.max_price != null)
        params.append("max_price", String(filters.max_price));
      if (filters.sort_by) params.append("sort_by", filters.sort_by);
      if (filters.rating != null)
        params.append("rating", String(filters.rating));

      const url = `${apiBaseUrl}/products/brand/${filters.brand}?${params.toString()}`;
      const res = await fetch(url);
      if (!res.ok) throw new Error("Failed to fetch products");
      const json = await res.json();

      // Cache min/max per category if available
      if (
        filters.category &&
        json?.min_price !== undefined &&
        json?.max_price !== undefined
      ) {
        setMinMaxCache(filters.category, json.min_price, json.max_price);
      }

      return json;
    },
    enabled: !!filters.brand, // fetch only when brand exists
  });
};
