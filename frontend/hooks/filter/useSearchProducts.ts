import { apiBaseUrl } from "@/config/apiConfig";
import { useSearchStore } from "@/store/filter/useSearchStore";
import { useQuery } from "@tanstack/react-query";

export const useSearchProducts = () => {
  const filters = useSearchStore();

  return useQuery<FilteringResponse>({
    queryKey: [
      "search",
      filters.category,
      filters.keyword,
      filters.min_price,
      filters.max_price,
      filters.brand_id,
      filters.rating,
      filters.sort_by,
      filters.page,
    ],
    queryFn: async () => {
      const query = new URLSearchParams();

      if (filters.keyword) query.set("keyword", filters.keyword);
      if (filters.min_price) query.set("min_price", String(filters.min_price));
      if (filters.max_price) query.set("max_price", String(filters.max_price));
      if (filters.brand_id) query.set("brand", filters.brand_id);
      if (filters.rating) query.set("rating", String(filters.rating));
      if (filters.sort_by) query.set("sort_by", filters.sort_by);
      if (filters.page) query.set("page", String(filters.page));

      const url = `${apiBaseUrl}/search?${query.toString()}`;
      const res = await fetch(url);
      if (!res.ok) throw new Error("Failed to fetch search results");
      return res.json();
    },
    enabled: !!filters.keyword, // fetch only when a keyword exists
  });
};
