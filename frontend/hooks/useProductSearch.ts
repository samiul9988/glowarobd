import { useEffect, useState } from "react";
import { useSearchHistory } from "./useSearchHistory";

// Product type
interface Product {
  id: number;
  slug: string;
  name: string;
  thumbnail_image: string;
  base_price: number;
  base_discounted_price: number;
  save: number;
  currency: string;
}

// Category type
interface Category {
  id: number;
  name: string;
  slug: string;
}

// API response type
interface ApiResponseType {
  data: {
    products: Product[];
    categories: Category[];
  };
  status: number;
  success: boolean;
}

// Hook return type
type UseProductSearchResult = {
  products: Product[];
  categories: Category[];
  loading: boolean;
  error: string | null;
  hasSearched: boolean;
};

export function useProductSearch(term: string): UseProductSearchResult {
  const [debouncedTerm, setDebouncedTerm] = useState(term);
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [hasSearched, setHasSearched] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const debounceDelay = 1000; // 1 second debounce

  // Debounce logic
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedTerm(term);
    }, debounceDelay);

    return () => clearTimeout(handler);
  }, [term]);

  // Fetch logic
  useEffect(() => {
    if (debouncedTerm.trim().length < 3) {
      setProducts([]);
      setCategories([]);
      setHasSearched(false);
      return;
    }

    const controller = new AbortController();

    const fetchProducts = async () => {
      setLoading(true);
      setError(null);
      setHasSearched(true);

      try {
        const res = await fetch(
          `${process.env.NEXT_PUBLIC_API_BASE_URL_LIVE}/search-suggestion?query=${encodeURIComponent(
            debouncedTerm,
          )}`,
          {
            signal: controller.signal,
            method: "GET",
          },
        );

        if (!res.ok) {
          throw new Error(`Error: ${res.statusText}`);
        }

        const response: ApiResponseType = await res.json();

        if (response.success && response.data) {
          setProducts(response.data.products || []);
          setCategories(response.data.categories || []);
        } else {
          setProducts([]);
          setCategories([]);
          setError("Failed to load search results");
        }
      } catch (err: any) {
        if (err.name !== "AbortError") {
          setError(err.message || "Unknown error");
        }
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();

    return () => controller.abort();
  }, [debouncedTerm]);

  return { products, categories, loading, error, hasSearched };
}
