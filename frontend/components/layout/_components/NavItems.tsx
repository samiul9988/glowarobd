"use client";

import { imageBaseHostUrl } from "@/config/apiConfig";
import { cn } from "@/lib/utils";
import {
  ChevronLeft,
  ChevronRight,
  ChevronRight as ArrowRight,
  Menu,
} from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useRef, useState, useEffect } from "react";
import "swiper/css";
import "swiper/css/free-mode";
import { Autoplay, FreeMode } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";

interface Props {
  menu: NavItemsType[];
  categories: (Categories & { subCategories: Categories[] })[] | null;
}
const fallback = "/images/placeholder.png";
const FALLBACK_IMAGE = "/images/placeholder.png";

export function NavItems({ menu, categories }: Props) {
  const [imgSrc, setImgSrc] = useState(fallback);
  const swiperRef = useRef<any>(null);
  const [canPrev, setCanPrev] = useState(false);
  const [canNext, setCanNext] = useState(false);
  const [hoveredCategory, setHoveredCategory] = useState<number | null>(null);
  const [hoveredSubCategory, setHoveredSubCategory] = useState<number | null>(
    null,
  );
  const [subMenuPosition, setSubMenuPosition] = useState({ top: 0, left: 0 });
  const categoryRefs = useRef<{ [key: number]: HTMLDivElement | null }>({});

  const updateArrows = () => {
    const s = swiperRef.current;
    if (!s) return;
    setCanPrev(s.progress > 0);
    setCanNext(s.progress < 1);
  };

  useEffect(() => {
    if (hoveredCategory && categoryRefs.current[hoveredCategory]) {
      const rect =
        categoryRefs.current[hoveredCategory]!.getBoundingClientRect();
      setSubMenuPosition({ top: rect.top, left: rect.right + 0 });
    }
  }, [hoveredCategory]);

  return (
    <nav className="navitems-menu relative bg-white">
      <div className="py-2.5 md:pt-4">
        <ul className="relative flex justify-center gap-3 !pl-0">
          {/* Left Arrow */}
          {canPrev && (
            <button
              aria-label="Previous"
              onClick={() => swiperRef.current?.slidePrev()}
              className="border-site-gray-100 hover:border-site-primary text-site-primary absolute top-1/2 left-0 z-30 hidden -translate-y-1/2 cursor-pointer rounded-full border bg-white p-1 shadow-md transition md:block lg:left-0"
            >
              <ChevronLeft className="h-5 w-5" />
            </button>
          )}
          <div className="group relative hidden lg:inline-block">
            {/* Button */}
            <button className="hover:bg-site-primary-100/80 border-site-gray-50 inline-flex cursor-pointer items-center gap-1 rounded-full border bg-[#F9F9F9] px-3 py-1.5 text-sm font-medium whitespace-nowrap text-gray-800 transition duration-300">
              <Menu className="h-5 w-5" />
              All Category
            </button>

            {/* Main Dropdown - wrapper for positioning */}
            <div className="absolute top-full left-0 z-[100] hidden py-4 group-hover:block">
              <div className="w-64 rounded-lg border border-gray-100 bg-white shadow-lg">
                <div
                  className="scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200 h-[400px] overflow-y-auto"
                  data-lenis-ignore
                  onWheel={(e) => e.stopPropagation()}
                >
                  {categories?.map((category) => (
                    <div
                      key={category.id}
                      ref={(el) => {
                        categoryRefs.current[category.id] = el;
                      }}
                      className="relative"
                      onMouseEnter={() => setHoveredCategory(category.id)}
                      onMouseLeave={() => setHoveredCategory(null)}
                    >
                      <Link
                        href={`/category/${category.slug}`}
                        className="hover:bg-site-primary-50 hover:text-site-primary flex items-center justify-between gap-2 rounded-md px-3 py-2.5 text-sm font-medium text-gray-700 transition"
                      >
                        <span>{category.name}</span>
                        {category.subCategories?.length > 0 && (
                          <ArrowRight className="h-4 w-4 text-gray-400" />
                        )}
                      </Link>
                    </div>
                  ))}
                </div>
              </div>

              {/* Subcategories - positioned with fixed positioning */}
              {categories?.map(
                (category) =>
                  category.subCategories &&
                  category.subCategories.length > 0 &&
                  hoveredCategory === category.id && (
                    <div
                      key={`sub-${category.id}`}
                      className="fixed z-[101] w-64 rounded-lg border border-gray-100 bg-white shadow-lg"
                      style={{
                        left: `${subMenuPosition.left}px`,
                        top: `${subMenuPosition.top}px`,
                      }}
                      onMouseEnter={() => setHoveredCategory(category.id)}
                      onMouseLeave={() => setHoveredCategory(null)}
                    >
                      <div
                        className="scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200 h-[400px] overflow-y-auto"
                        data-lenis-ignore
                        onWheel={(e) => e.stopPropagation()}
                      >
                        {category.subCategories.map((sub) => (
                          <div
                            key={sub.id}
                            className="relative"
                            onMouseEnter={() => setHoveredSubCategory(sub.id)}
                            onMouseLeave={() => setHoveredSubCategory(null)}
                          >
                            <Link
                              href={`/category/${sub.slug}`}
                              className="hover:bg-site-primary-50 hover:text-site-primary flex items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-600 transition"
                            >
                              {sub.featured_icon && (
                                <Image
                                  src={imageBaseHostUrl + sub.featured_icon}
                                  alt={sub.name}
                                  height={24}
                                  width={24}
                                  className="object-contain"
                                  loading="lazy"
                                  onError={(e) => {
                                    const target =
                                      e.currentTarget as HTMLImageElement;
                                    target.src = FALLBACK_IMAGE;
                                  }}
                                />
                              )}
                              <span className="flex-1">{sub.name}</span>
                            </Link>
                          </div>
                        ))}
                      </div>
                    </div>
                  ),
              )}
            </div>
          </div>

          {/* end sub categories */}
          <Swiper
            modules={[Autoplay, FreeMode]}
            spaceBetween={12}
            slidesPerView="auto"
            freeMode={true}
            autoplay={false}
            breakpoints={{
              0: { spaceBetween: 4 },
              768: { spaceBetween: 12 },
            }}
            className="dynamic-swiper"
            onSwiper={(s) => {
              swiperRef.current = s;
              updateArrows();
            }}
            onSlideChange={updateArrows}
            onReachBeginning={updateArrows}
            onReachEnd={updateArrows}
            onFromEdge={updateArrows}
            onTouchEnd={updateArrows} // handle drag/swipe
            onResize={updateArrows} // handle window resize
          >
            {menu.map((item) => (
              <SwiperSlide key={item.id} className="group relative !w-auto">
                <Link
                  scroll={true}
                  href={"/" + item.url || "/"}
                  className={cn(
                    "hover:bg-site-primary-100/80 border-site-gray-50 inline-flex items-center gap-1 rounded-full border bg-[#F9F9F9] px-3 py-1.5 text-sm font-medium text-gray-800 transition duration-300",
                    item.label === "Offers" && "bg-site-cornflower-200",
                  )}
                >
                  {item.icon && (
                    <Image
                      src={`${imageBaseHostUrl}${item.icon}`}
                      alt={item.label}
                      width={20}
                      height={20}
                      className="object-contain"
                      unoptimized
                      onError={() => setImgSrc(fallback)}
                    />
                  )}
                  {item.label}
                </Link>
              </SwiperSlide>
            ))}
          </Swiper>

          {/* Right Arrow */}
          {canNext && (
            <button
              aria-label="Next"
              onClick={() => swiperRef.current?.slideNext()}
              className="border-site-gray-100 hover:border-site-primary text-site-primary absolute top-1/2 right-0 z-30 hidden -translate-y-1/2 cursor-pointer rounded-full border bg-white p-1 shadow-md transition md:block lg:right-0"
            >
              <ChevronRight className="h-5 w-5" />
            </button>
          )}
        </ul>
      </div>
    </nav>
  );
}
