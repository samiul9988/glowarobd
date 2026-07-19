"use client";

import {
  HistoryIcon,
  SearchCategoryIcon,
} from "@/components/icons/icon-library";

const TOP_SELLING = [
  "The Ordinary Niacinamide 10% + Zinc 1%",
  "Cosrx Advanced Snail 96 Mucin Essence",
  "Cosrx Low pH Good Morning Cleanser",
  "Skin Aqua Super Moisture Gel SPF50",
  "AXIS-Y Dark Spot Correcting Glow Serum",
  "iUNIK Centella Calming Gel Cream",
  "Beauty of Joseon Ginseng Essence",
  "Missha Soft Finish Sun Milk",
  "COSRX Salicylic Acid Gentle Cleanser",
  "Some By Mi AHA BHA PHA 30 Days Toner",
];

import { Input } from "@/components/ui/input";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { useMediaQuery } from "@/hooks/useMediaQuery";
import { useProductSearch } from "@/hooks/useProductSearch";
import { useSearchHistory } from "@/hooks/useSearchHistory";
import { cn } from "@/lib/utils";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import { ArrowLeft, LoaderCircle, Search } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { IoCloseCircleOutline } from "react-icons/io5";

interface ProductImageProps {
  src: string | null;
  alt: string;
}

const fallback = "/images/placeholder.png";

function ProductImage({ src, alt }: ProductImageProps) {
  const [imgSrc, setImgSrc] = useState(src ? imageBaseHostUrl + src : fallback);
  return (
    <Image
      src={imgSrc}
      alt={alt}
      width={0}
      height={0}
      className="aspect-square h-[62px] w-[62px] rounded-[6px] object-cover"
      onError={() => setImgSrc(fallback)}
      loading="lazy"
      style={{ objectFit: "cover" }}
      placeholder="blur"
      blurDataURL={fallback}
    />
  );
}

interface Props {
  className?: string;
  setShowMobileSearchBar?: (value: boolean) => void;
  showMobileSearchBar?: boolean;
}

export default function SearchBar({
  className,
  setShowMobileSearchBar,
  showMobileSearchBar,
}: Props) {
  const [search, setSearch] = useState("");
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [focused, setFocused] = useState(false);
  const [lastTypedTime, setLastTypedTime] = useState<number>(0);
  const [instantSearch, setInstantSearch] = useState(false);
  const wrapperRef = useRef<HTMLDivElement>(null);
  const searchRef = useRef<HTMLInputElement>(null);
  const [placeholderText, setPlaceholderText] = useState("");
  const [animPaused, setAnimPaused] = useState(false);
  const animIdxRef = useRef(0);
  const charIdxRef = useRef(0);
  const deletingRef = useRef(false);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const router = useRouter();
  const { suggestions, addSuggestion } = useSearchHistory();
  const isMobile = useMediaQuery("(max-width: 1024px)");

  useEffect(() => {
    if (isMobile) {
      searchRef.current?.focus();
    }
  }, [isMobile]);

  useEffect(() => {
    if (animPaused) return;
    const current = TOP_SELLING[animIdxRef.current];
    const atEnd = charIdxRef.current >= current.length;
    const speed = deletingRef.current ? 55 : atEnd ? 1500 : 120;

    timerRef.current = setTimeout(() => {
      if (!deletingRef.current) {
        charIdxRef.current++;
        setPlaceholderText(current.slice(0, charIdxRef.current));
        if (charIdxRef.current >= current.length) {
          deletingRef.current = true;
        }
      } else {
        charIdxRef.current--;
        setPlaceholderText(current.slice(0, charIdxRef.current));
        if (charIdxRef.current === 0) {
          deletingRef.current = false;
          animIdxRef.current = (animIdxRef.current + 1) % TOP_SELLING.length;
        }
      }
    }, speed);

    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, [placeholderText, animPaused]);

  const { categories, products, loading, hasSearched } =
    useProductSearch(search);

  // Show suggestions only if not instant search
  useEffect(() => {
    if (
      !instantSearch &&
      search.trim() &&
      !loading &&
      (products.length > 0 || (hasSearched && products.length === 0))
    ) {
      setShowSuggestions(true);
    } else {
      setShowSuggestions(false);
    }
  }, [search, products, loading, hasSearched, instantSearch]);

  // Close suggestions if clicked outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        wrapperRef.current &&
        !wrapperRef.current.contains(event.target as Node)
      ) {
        setShowSuggestions(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Handle submit (Enter key or search button)
  const handleSubmit = (e?: React.FormEvent) => {
    e?.preventDefault();

    if (!search.trim()) return;

    const now = Date.now();
    const timeDiff = now - lastTypedTime;

    if (timeDiff < 1000) {
      // User typed and hit Enter instantly
      setInstantSearch(true);
    }

    // Always go to search page
    router.push(`/search?keyword=${encodeURIComponent(search.trim())}`);
    addSuggestion(search);
    setShowSuggestions(false);
    setShowMobileSearchBar?.(false);
    setFocused(false);
  };

  // Handle focus
  const handleFocus = () => {
    if (
      !instantSearch &&
      !loading &&
      (products.length > 0 || (hasSearched && products.length === 0))
    ) {
      setShowSuggestions(true);
    }

    setFocused(true);
    setAnimPaused(true);
  };

  return (
    <div
      className={cn("relative w-full max-w-[394px]", className)}
      ref={wrapperRef}
    >
      <div className="absolute top-6 left-0 z-30 w-full md:-top-6">
        <div
          className={cn(
            "rounded-[30px] border border-transparent",
            focused || loading || showSuggestions
              ? "lg:border-site-gray-100 from-[#F3FAFF] to-[#FFFFFF] lg:bg-gradient-to-b lg:shadow-[0px_4px_100px_0px_#0000001F]"
              : "",
          )}
        >
          <form
            onSubmit={handleSubmit}
            className="relative flex items-center gap-1"
          >
            {/* Search and Clear button */}
            <div className="absolute top-1/2 right-2 flex -translate-y-1/2 items-center gap-3">
              {search.length > 0 && (
                <IoCloseCircleOutline
                  type="button"
                  size={22}
                  className="text-site-gray-200 hover:text-site-gray-400 cursor-pointer transition-colors"
                  onClick={() => {
                    setSearch("");
                    setInstantSearch(false);
                    searchRef.current?.focus();
                  }}
                />
              )}
              <button
                type="submit"
                className="bg-site-secondary-50 flex h-[36px] w-[36px] cursor-pointer items-center justify-center rounded-full text-white"
              >
                <Search
                  strokeWidth={3}
                  className="text-site-secondary-500 h-4 w-4 font-extrabold"
                />
              </button>
            </div>

            <button
              className="inline-block p-1 lg:hidden"
              onClick={() => setShowMobileSearchBar?.(false)}
            >
              <ArrowLeft className="h-[18px] w-[18px]" />
            </button>

            {/* Input */}
            <Input
              ref={searchRef}
              type="text"
              placeholder={animPaused ? "Search Products" : placeholderText || "Search Products"}
              className="placeholder:text-site-gray-400 focus:!border-site-primary-500 hover:border-site-primary-500 border-site-gray-100 text-site-gray-700 h-11 rounded-full border !bg-[#FFFFFFCC] pr-16 pl-6 transition focus-visible:ring-0"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setLastTypedTime(Date.now());
                setInstantSearch(false);
                setAnimPaused(true);
              }}
              onFocus={handleFocus}
              onBlur={() => { setFocused(false); if (!search.trim()) setAnimPaused(false); }}
            />
          </form>

          {/* Suggestions and Loading */}
          {search.trim() && showSuggestions && (
            <div className="-top-4 left-0 z-20 mt-1 w-full rounded-sm px-3">
              {products.length > 0 ? (
                <>
                  <div>
                    <p className="text-site-gray-400 mt-6 mb-2 text-sm">
                      Category Suggestions
                    </p>

                    {categories && categories.length > 0 ? (
                      <ScrollArea.Root
                        className="relative overflow-clip"
                        data-lenis-ignore
                      >
                        <ScrollArea.Viewport
                          className="max-h-[160px]"
                          onWheel={(e) => e.stopPropagation()}
                        >
                          {categories.map((cat, i) => (
                            <div
                              className="mt-2 flex items-center gap-2"
                              onClick={() => {
                                setShowSuggestions(false);
                                setShowMobileSearchBar?.(false);
                              }}
                              key={i}
                            >
                              <SearchCategoryIcon />
                              <Link
                                key={i}
                                href={`/category/${cat.slug}`}
                                className="text-site-gray-800 text-sm font-semibold"
                              >
                                {cat.name}
                              </Link>
                            </div>
                          ))}
                        </ScrollArea.Viewport>
                        <ScrollArea.Scrollbar
                          orientation="vertical"
                          className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
                        >
                          <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
                        </ScrollArea.Scrollbar>
                      </ScrollArea.Root>
                    ) : (
                      <div
                        className="mt-2 flex items-center gap-2"
                        onClick={() => {
                          setShowSuggestions(false);
                          setShowMobileSearchBar?.(false);
                        }}
                      >
                        <SearchCategoryIcon />
                        <Link
                          href={`/categories`}
                          className="text-site-gray-800 text-sm font-semibold"
                        >
                          See all categories
                        </Link>
                      </div>
                    )}

                    <hr className="border-site-gray-100 my-5" />
                    <p className="text-site-gray-400 mb-1 text-sm">Products</p>
                  </div>
                  <ScrollArea.Root
                    className="relative mb-2 overflow-clip"
                    data-lenis-ignore
                  >
                    <ScrollArea.Viewport
                      className="max-h-[400px]"
                      onWheel={(e) => e.stopPropagation()}
                    >
                      {products.map((item, i) => (
                        <Link
                          onClick={() => {
                            setShowSuggestions(false);
                            setShowMobileSearchBar?.(false);
                          }}
                          key={i}
                          href={`/product/${item.slug}`}
                          className="hover:bg-site-primary/10 mr-1 block rounded-[10px] p-2"
                        >
                          <div className="flex gap-4">
                            <ProductImage
                              src={item.thumbnail_image}
                              alt={item.name}
                            />
                            <div className="space-y-1">
                              <p className="text-site-gray-900 line-clamp-2 text-sm font-normal">
                                {item.name}
                              </p>
                              <div className="flex items-center gap-2.5">
                                <p className="text-site-gray-900 text-base font-bold">
                                  {item.currency}
                                  {item.base_discounted_price}
                                </p>

                                {item.base_discounted_price !==
                                  item.base_price && (
                                  <p
                                    className={cn(
                                      "text-site-gray-300 text-sm font-normal",
                                      item.base_discounted_price !==
                                        item.base_price && "line-through",
                                    )}
                                  >
                                    {item.currency}
                                    {item.base_price}
                                  </p>
                                )}
                              </div>
                            </div>
                          </div>
                        </Link>
                      ))}
                    </ScrollArea.Viewport>
                    <ScrollArea.Scrollbar
                      orientation="vertical"
                      className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
                    >
                      <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
                    </ScrollArea.Scrollbar>
                  </ScrollArea.Root>
                </>
              ) : (
                hasSearched && (
                  <div className="py-3">
                    <p className="text-site-gray-800 text-center text-sm">
                      No results found.
                    </p>
                  </div>
                )
              )}
            </div>
          )}

          {/* Search Loading */}
          {search.trim() && loading && (
            <div className="mt-5 pb-2">
              <LoaderCircle className="text-site-primary mx-auto animate-spin" />
            </div>
          )}

          {/* Recent Suggestions */}
          {focused && !showSuggestions && loading && (
            <div className="px-3 pb-4">
              {suggestions.length > 0 && (
                <p className="text-site-gray-400 mt-6 mb-2 text-sm">
                  Recent Search
                </p>
              )}
              {suggestions.length > 0 ? (
                suggestions.map((item, i) => (
                  <div
                    key={i}
                    className="mt-2 flex cursor-pointer items-center gap-2"
                    onMouseDown={() => {
                      setSearch(item);
                    }}
                  >
                    <HistoryIcon />
                    <span className="text-site-gray-800 text-sm font-semibold">
                      {item}
                    </span>
                  </div>
                ))
              ) : (
                <div className="flex items-center gap-2">
                  <span className="text-site-gray-800 text-sm font-semibold"></span>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
