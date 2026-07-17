"use client";

import {
  ArrowUpIcon,
  MobileMenuBrandIcon,
  MobileMenuFireIcon,
  MobileMenuPurchaseIcon,
  MobileMenuSupportIcon,
  ModalCloseIcon,
} from "@/components/icons/icon-library";
import {
  Sheet,
  SheetContent,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { cn } from "@/lib/utils";
import { AnimatePresence, motion } from "framer-motion";
import { Menu } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import type SwiperType from "swiper";
import "swiper/css";
import { Swiper, SwiperSlide } from "swiper/react";

import * as ScrollArea from "@radix-ui/react-scroll-area";

interface Props {
  logo: string;
  categories: (Categories & { subCategories: Categories[] })[] | null;
  appLinks: {
    appStoreLink: string;
    playStoreLink: string;
  };
}

const STORAGE_KEY = "Glowaro_lastActiveCategory";
const FALLBACK_IMAGE = "/images/placeholder.png";

const MobileMenu = ({ logo, categories, appLinks }: Props) => {
  const [isOpen, setIsOpen] = useState(false);

  const filteredCategories =
    categories?.filter((cat) => cat.subCategories?.length > 0) ?? [];

  // 1) lazy init so value exists before first render/mount
  const [activeIndex, setActiveIndex] = useState<number>(() => {
    try {
      const s = localStorage.getItem(STORAGE_KEY);
      return s ? Number(s) : 0;
    } catch {
      return 0;
    }
  });

  // 2) swiper ref to programmatically slide
  const swiperRef = useRef<SwiperType | null>(null);

  // keep localStorage in sync (and snap swiper when index changes)
  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_KEY, String(activeIndex));
    } catch {}
    if (swiperRef.current) {
      // ensure visible slide matches activeIndex
      swiperRef.current.slideTo(activeIndex, 300);
    }
  }, [activeIndex]);

  // guard in case saved index is out of range after categories update
  useEffect(() => {
    if (
      activeIndex >= filteredCategories.length &&
      filteredCategories.length > 0
    ) {
      setActiveIndex(0);
    }
  }, [filteredCategories.length]); // run when categories change

  const activeCategory = filteredCategories[activeIndex] ?? null;

  return (
    <Sheet open={isOpen} onOpenChange={setIsOpen}>
      <SheetTrigger asChild className="">
        <div className="min-w-[52px] w-[52px] flex items-center justify-center h-[42px] bg-white  rounded-full lg:hidden">
            <Menu size={24} className="w-6 h-6 text-site-gray-700 cursor-pointer "/>
        </div>
      
      </SheetTrigger>

      <SheetContent
        side="left"
        className="w-[90%] gap-2 p-4 focus:outline-0 [&>button]:hidden"
      >
        <SheetTitle hidden>Dashboard Menu</SheetTitle>

        <div className="flex items-center justify-between pb-4">
          {/* Glowaro logo */}
          <Link href={"/"} onClick={() => setIsOpen(false)}>
            <Image src={logo} alt="Glowaro Logo" height={40} width={136} />
          </Link>

          {/* Close button */}
          <div className="cursor-pointer" onClick={() => setIsOpen(false)}>
            <ModalCloseIcon className="fill-site-gray-300 transition-colors hover:fill-red-400" />
          </div>
        </div>

        <div>
          {/* Categories slider */}
          <div className="pb-5">
            <Swiper
              slidesPerView="auto"
              spaceBetween={5}
              className="px-4"
              initialSlide={activeIndex}
              onSwiper={(s) => {
                swiperRef.current = s;
              }}
            >
              {filteredCategories.map((cat, index) => (
                <SwiperSlide
                  key={cat.id}
                  style={{ width: "auto" }}
                  onClick={() => setActiveIndex(index)}
                  className="!w-[72px] text-center"
                >
                  <div className="group flex cursor-pointer flex-col items-center gap-3.5 transition-colors">
                    <Image
                      src={imageBaseHostUrl + cat.featured_icon}
                      alt="about image"
                      height={84}
                      width={72}
                      loading="lazy"
                      // fallback image
                      onError={(e) => {
                        const target = e.currentTarget as HTMLImageElement;
                        target.src = FALLBACK_IMAGE;
                      }}
                    />
                    <span
                      className={cn(
                        "text-site-gray-900 group-hover:text-site-primary text-xs font-medium duration-200",
                        {
                          "text-site-primary": activeIndex === index,
                        },
                      )}
                    >
                      {cat.name}
                    </span>
                  </div>
                </SwiperSlide>
              ))}
            </Swiper>
          </div>

          {/* Scroll area with Subcategories */}
          <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
            <ScrollArea.Viewport
              className="h-[calc(100dvh-220px)] flex-1 space-y-6 overflow-y-auto lg:h-auto"
              onWheel={(e) => e.stopPropagation()}
            >
              {/* Subcategory Cards */}
              <div className="bg-site-primary-50 rounded-[10px] p-4">
                <AnimatePresence mode="wait">
                  {activeCategory ? (
                    <motion.div
                      key={activeCategory.id}
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -20 }}
                      transition={{ duration: 0.22 }}
                      className="grid grid-cols-1"
                    >
                      {activeCategory.subCategories.map((sub, index) => (
                        <div key={sub.id}>
                          <Link
                            onClick={() => setIsOpen(false)}
                            href={"/category/" + sub.slug}
                            className={cn(
                              "group flex items-center justify-between py-3",
                              index ===
                                activeCategory.subCategories.length - 1 &&
                                "pb-0",
                              index === 0 && "pt-0",
                            )}
                          >
                            <span className="text-site-gray-700 group-hover:text-site-primary text-sm font-medium transition-colors">
                              {sub.name}
                            </span>
                            <ArrowUpIcon className="stroke-site-gray-700 group-hover:stroke-site-primary h-5 w-5" />
                          </Link>
                          {/* if last subcategory, don't show bottom border */}
                          {index !==
                            activeCategory.subCategories.length - 1 && (
                            <hr className="border-site-gray-100" />
                          )}
                        </div>
                      ))}
                    </motion.div>
                  ) : null}
                </AnimatePresence>
              </div>

              {/* Static links section */}
              <div className="mt-5 space-y-2">
                <Link
                  href={"/auth/login"}
                  className="bg-site-gray-50 group flex items-center gap-3 rounded-[10px] px-4 py-3"
                  onClick={() => setIsOpen(false)}
                >
                  <MobileMenuPurchaseIcon />
                  <span className="text-site-gray-600 group-hover:text-site-primary text-sm font-medium transition-colors">
                    Login
                  </span>
                </Link>

                <Link
                  href={"/auth/registration"}
                  className="bg-site-gray-50 group flex items-center gap-3 rounded-[10px] px-4 py-3"
                  onClick={() => setIsOpen(false)}
                >
                  <MobileMenuPurchaseIcon />
                  <span className="text-site-gray-600 group-hover:text-site-primary text-sm font-medium transition-colors">
                    Sign Up
                  </span>
                </Link>

                <Link
                  href={"/"}
                  className="bg-site-gray-50 group flex items-center gap-3 rounded-[10px] px-4 py-3"
                  onClick={() => setIsOpen(false)}
                >
                  <MobileMenuPurchaseIcon />
                  <span className="text-site-gray-600 group-hover:text-site-primary text-sm font-medium transition-colors">
                    Purchase History
                  </span>
                </Link>

                <Link
                  href={"/flash-deals"}
                  className="bg-site-gray-50 group flex items-center gap-3 rounded-[10px] px-4 py-3"
                  onClick={() => setIsOpen(false)}
                >
                  <MobileMenuFireIcon />
                  <span className="text-site-gray-600 group-hover:text-site-primary text-sm font-medium transition-colors">
                    Offers
                  </span>
                </Link>

                <Link
                  href={"/"}
                  className="bg-site-gray-50 group flex items-center gap-3 rounded-[10px] px-4 py-3"
                  onClick={() => setIsOpen(false)}
                >
                  <MobileMenuBrandIcon />
                  <span className="text-site-gray-600 group-hover:text-site-primary text-sm font-medium transition-colors">
                    Brands
                  </span>
                </Link>

                <Link
                  href={"/"}
                  className="bg-site-gray-50 group flex items-center gap-3 rounded-[10px] px-4 py-3"
                  onClick={() => setIsOpen(false)}
                >
                  <MobileMenuSupportIcon />

                  <span className="text-site-gray-600 group-hover:text-site-primary text-sm font-medium transition-colors">
                    Support
                  </span>
                </Link>
              </div>

              {/* Download app banner */}
              <div className="mt-5 rounded-[10px] bg-gradient-to-bl from-[#77D9FF] to-white p-4">
                <div className="text-center">
                  <h6 className="text-site-gray-900 text-[23px]">
                    Download Our Apps
                  </h6>
                  <p className="text-site-gray-900/60 text-sm leading-5">
                    Get more special offers & Discount
                  </p>
                </div>
                <div className="mt-4 flex items-center justify-center gap-3">
                  <Link
                    href={appLinks.appStoreLink}
                    target="_blank"
                    className="transition-transform hover:scale-105"
                  >
                    <Image
                      src="/images/footer/apple-store.png"
                      alt="App Store"
                      width={0}
                      height={0}
                      className="h-[32px] w-[96px] object-contain sm:h-[36px] sm:w-[108px] md:h-[40px] md:w-[118px]"
                      loading="lazy"
                    />
                  </Link>
                  <Link
                    href={appLinks.playStoreLink}
                    target="_blank"
                    className="transition-transform hover:scale-105"
                  >
                    <Image
                      src="/images/footer/play-store.png"
                      alt="Play Store"
                      width={0}
                      height={0}
                      className="h-[32px] w-[96px] object-contain sm:h-[36px] sm:w-[108px] md:h-[40px] md:w-[118px]"
                      loading="lazy"
                    />
                  </Link>
                </div>
              </div>
            </ScrollArea.Viewport>
            <ScrollArea.Scrollbar
              orientation="vertical"
              className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
            >
              <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
            </ScrollArea.Scrollbar>
          </ScrollArea.Root>
        </div>
      </SheetContent>
    </Sheet>
  );
};

export default MobileMenu;
