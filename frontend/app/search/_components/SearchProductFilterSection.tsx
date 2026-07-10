"use client";

import ProductCard from "@/components/cards/ProductCard";
import Container from "@/components/Container";
import { apiBaseUrl } from "@/config/apiConfig";
import { cn } from "@/lib/utils";
import { useShowHeader } from "@/store/useShowHeader";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import { motion, Variants } from "framer-motion";
import { LoaderCircle } from "lucide-react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useEffect, useRef, useState, useTransition } from "react";
import { ProductListApiResponse } from "../page";
import SortBySelectOption from "@/app/category/_components/filter/SortBySelectOption";
import FilterSearchDrawerMobile from "@/app/category/_components/FilterSearchDrawerMobile";
import SkeletonProduct from "@/app/category/_sections/SkeletonProduct";

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Meta {
  current_page: number;
  from: number;
  last_page: number;
  links: PaginationLink[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}

interface Props {
  products: ProductType[];
  meta: Meta;
  categories?: CategoryNode[] | null;
  allBrands: BrandItemResponse[] | null;
  category_slug: string;
  SLIDER_MAX: number;
  SLIDER_MIN: number;
}

const SearchProductFilterSection = ({
  products: initialProducts,
  meta: initialMeta,
  categories,
  allBrands,
  category_slug,
  SLIDER_MAX,
  SLIDER_MIN,
}: Props) => {
  const showHeader = useShowHeader((state) => state.showHeader);
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [, startTransition] = useTransition();

  // URL params
  const urlKeyword = searchParams.get("keyword") || "";
  const urlBrandId = searchParams.get("brand");
  const urlMinPrice = searchParams.get("min_price");
  const urlMaxPrice = searchParams.get("max_price");
  const urlSortBy = searchParams.get("sort_by");
  const urlRating = searchParams.get("rating");

  // States
  const [selectedBrand, setSelectedBrand] = useState<string | null>(urlBrandId);
  const [selectedRating, setSelectedRating] = useState<number | null>(
    urlRating ? Number(urlRating) : null,
  );
  const [priceRange, setPriceRange] = useState<[number, number]>([
    Math.max(SLIDER_MIN, Number(urlMinPrice) || SLIDER_MIN),
    Math.min(SLIDER_MAX, Number(urlMaxPrice) || SLIDER_MAX),
  ]);

  const [items, setItems] = useState<ProductType[]>(initialProducts);
  const [currentMeta, setCurrentMeta] = useState<Meta>(initialMeta);
  const [loading, setLoading] = useState(false);
  const [filterLoading, setFilterLoading] = useState(false);
  const loadMoreRef = useRef<HTMLDivElement | null>(null);
  const loadingRef = useRef(loading);
  const debounceTimerRef = useRef<NodeJS.Timeout | null>(null);

  // Sync brand & rating with URL
  useEffect(() => {
    setSelectedBrand(urlBrandId || null);
    setSelectedRating(urlRating ? Number(urlRating) : null);
  }, [urlBrandId, urlRating]);

  // Sync slider with URL and clamp
  useEffect(() => {
    const min = Math.max(SLIDER_MIN, Number(urlMinPrice) || SLIDER_MIN);
    const max = Math.min(SLIDER_MAX, Number(urlMaxPrice) || SLIDER_MAX);
    setPriceRange([min, max]);
  }, [urlMinPrice, urlMaxPrice, SLIDER_MIN, SLIDER_MAX]);

  useEffect(() => {
    loadingRef.current = loading;
  }, [loading]);

  // Fetch filtered products
  useEffect(() => {
    const fetchFilteredProducts = async () => {
      setFilterLoading(true);
      try {
        const params = new URLSearchParams({
          keyword: urlKeyword,
          page: "1",
          limit: "12",
        });
        if (urlBrandId) params.set("brand", urlBrandId);
        if (urlRating) params.set("rating", urlRating!);
        if (urlMinPrice && urlMaxPrice) {
          params.set("min_price", urlMinPrice);
          params.set("max_price", urlMaxPrice);
        }
        if (urlSortBy) params.set("sort_by", urlSortBy!);

        const response = await fetch(
          `${apiBaseUrl}/search?${params.toString()}`,
        );
        const data: ProductListApiResponse = await response.json();

        if (data?.data) {
          setItems(data.data);
          if (data.meta) setCurrentMeta(data.meta);
        }
      } catch (error) {
        console.error("Filter fetch failed:", error);
      } finally {
        setFilterLoading(false);
      }
    };

    fetchFilteredProducts();
  }, [urlBrandId, urlRating, urlMinPrice, urlMaxPrice, urlSortBy, urlKeyword]);

  // Infinite scroll
  useEffect(() => {
    if (currentMeta.current_page >= currentMeta.last_page) return;

    const observer = new IntersectionObserver(
      async (entries) => {
        const target = entries[0];
        if (target.isIntersecting && !loadingRef.current) {
          setLoading(true);
          try {
            const nextPage = currentMeta.current_page + 1;
            const params = new URLSearchParams({
              keyword: urlKeyword,
              page: String(nextPage),
              limit: "12",
            });
            if (urlBrandId) params.set("brand", urlBrandId);
            if (urlRating) params.set("rating", urlRating!);

            if (urlMinPrice && urlMaxPrice) {
              params.set("min_price", urlMinPrice);
              params.set("max_price", urlMaxPrice);
            }
            if (urlSortBy) params.set("sort_by", urlSortBy!);

            const response = await fetch(
              `${apiBaseUrl}/search?${params.toString()}`,
            );
            const data: ProductListApiResponse = await response.json();

            if (data?.data?.length) {
              setItems((prev) => {
                const merged = [...prev, ...data.data];
                const unique = Array.from(
                  new Map(merged.map((i) => [i.id, i])),
                ).map(([, value]) => value);
                return unique;
              });
              if (data.meta) setCurrentMeta(data.meta);
            }
          } catch (error) {
            console.error("Load more failed:", error);
          } finally {
            setLoading(false);
          }
        }
      },
      { threshold: 1 },
    );

    if (loadMoreRef.current) observer.observe(loadMoreRef.current);
    return () => observer.disconnect();
  }, [
    currentMeta,
    urlBrandId,
    urlRating,
    urlMinPrice,
    urlMaxPrice,
    urlSortBy,
    urlKeyword,
  ]);

  // Update URL filters
  const updateFilters = (updates: {
    brand_id?: string | null;
    rating?: number | null;
    min_price?: number;
    max_price?: number;
  }) => {
    const params = new URLSearchParams(searchParams.toString());

    if (updates.brand_id !== undefined) {
      if (updates.brand_id) params.set("brand", updates.brand_id);
      else params.delete("brand");
    }

    if (updates.rating !== undefined) {
      if (updates.rating) params.set("rating", String(updates.rating));
      else params.delete("rating");
    }

    if (updates.min_price !== undefined && updates.max_price !== undefined) {
      params.set("min_price", String(updates.min_price));
      params.set("max_price", String(updates.max_price));
    }

    params.delete("page");
    startTransition(() => {
      router.push(`${pathname}?${params.toString()}`);
    });
  };

  const handleBrandChange = (brandSlug: string | null) => {
    setSelectedBrand(brandSlug);
    updateFilters({ brand_id: brandSlug });
  };

  const handleRatingChange = (rating: number | null) => {
    setSelectedRating(rating);
    updateFilters({ rating });
  };

  const handlePriceChange = (range: [number, number]) => {
    // Clamp values
    const clamped: [number, number] = [
      Math.max(SLIDER_MIN, Math.min(range[0], SLIDER_MAX)),
      Math.max(SLIDER_MIN, Math.min(range[1], SLIDER_MAX)),
    ];
    setPriceRange(clamped);

    // Debounce URL update
    if (debounceTimerRef.current) clearTimeout(debounceTimerRef.current);
    debounceTimerRef.current = setTimeout(() => {
      updateFilters({ min_price: clamped[0], max_price: clamped[1] });
    }, 250);
  };

  useEffect(() => {
    return () => {
      if (debounceTimerRef.current) clearTimeout(debounceTimerRef.current);
    };
  }, []);

  const variant: Variants = {
    initial: { opacity: 0, y: 50 },
    animate: (i: number) => ({
      opacity: 1,
      y: 0,
      transition: { delay: (i % 12) * 0.08, duration: 0.4, ease: "easeOut" },
    }),
  };

  return (
    <section className="mt-6 pb-4 md:mt-12">
      <Container className="flex items-start gap-10">
        {/* Sidebar */}
        <div
          className={cn(
            "sticky top-0 hidden py-2 transition-all lg:block",
            showHeader && "top-[140px]",
          )}
        >
          <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
            <ScrollArea.Viewport
              className={cn(
                "p-4",
                showHeader ? "h-[calc(100dvh-140px)]" : "h-screen",
              )}
              onWheel={(e) => e.stopPropagation()}
            >
              {/* <FilterWidgets
                categories={categories || null}
                allBrands={allBrands}
                selectedBrand={selectedBrand}
                setSelectedBrand={handleBrandChange}
                selectedRating={selectedRating}
                setSelectedRating={handleRatingChange}
                priceRange={priceRange}
                setPriceRange={handlePriceChange}
                SLIDER_MIN={SLIDER_MIN}
                SLIDER_MAX={SLIDER_MAX}
              /> */}
            </ScrollArea.Viewport>
            <ScrollArea.Scrollbar
              orientation="vertical"
              className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
            >
              <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
            </ScrollArea.Scrollbar>
          </ScrollArea.Root>
        </div>

        {/* Products */}
        <div className="flex-1">
          <div className="mb-6 flex items-center justify-between gap-4">
            <div className="flex flex-1 items-center justify-between rounded-[10px] bg-gray-50 p-2 lg:px-6 lg:py-3">
              <p className="hidden text-base font-normal text-gray-500 md:block">
                <b className="font-bold text-gray-900">
                  {currentMeta.total || 0}
                </b>{" "}
                Results Found
              </p>
              <SortBySelectOption />
            </div>

            {/* <FilterSearchDrawerMobile
              categories={categories || null}
              allBrands={allBrands}
              selectedBrand={selectedBrand}
              setSelectedBrand={handleBrandChange}
              selectedRating={selectedRating}
              setSelectedRating={handleRatingChange}
              priceRange={priceRange}
              setPriceRange={handlePriceChange}
              SLIDER_MIN={SLIDER_MIN}
              SLIDER_MAX={SLIDER_MAX}
            />
          </div> */}

            {filterLoading ? (
              <SkeletonProduct />
            ) : items.length === 0 ? (
              <div className="flex min-h-[400px] items-center justify-center">
                <p className="text-site-gray-500 text-lg">No products found</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
                {items.map((product, i) => (
                  <motion.div
                    key={product.id}
                    custom={i}
                    variants={variant}
                    initial="initial"
                    whileInView="animate"
                    viewport={{ once: true, amount: 0 }}
                  >
                    <ProductCard {...product} />
                  </motion.div>
                ))}
              </div>
            )}

            {currentMeta.current_page < currentMeta.last_page && (
              <div
                ref={loadMoreRef}
                className="my-4 flex h-16 items-center justify-center md:my-6"
              >
                {loading && (
                  <p className="bg-site-gray-50 text-site-gray-900 flex items-center rounded-full px-4 py-2 text-sm font-semibold">
                    <LoaderCircle className="mr-1 animate-spin duration-100" />
                    Loading more...
                  </p>
                )}
              </div>
            )}
          </div>
        </div>
      </Container>
    </section>
  );
};

export default SearchProductFilterSection;
