"use client";

import { useEffect, useRef, useState } from "react";
import { Autoplay, Navigation, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";

import { useWindowWidth } from "@/hooks/useWindowWidth";
import { motion } from "framer-motion";
import { ChevronLeft, ChevronRight } from "lucide-react";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import ProductCard from "../cards/ProductCard";
import SectionHeader from "../SectionHeader";

interface Props {
  data: ProductType[];
  name: string;
  icon?: string;
  link?: string;
}

export default function ProductTopArrowSlider({
  data,
  name,
  icon,
  link,
}: Props) {
  const prevRef = useRef<HTMLDivElement>(null);
  const nextRef = useRef<HTMLDivElement>(null);
  const [swiperInstance, setSwiperInstance] = useState<any>(null);
  const [isBeginning, setIsBeginning] = useState(true);
  const [isEnd, setIsEnd] = useState(false);

  const ref = useRef(null);
  const width = useWindowWidth();

  // Responsive limit
  let limit = 5; // default
  if (width < 640)
    limit = 3; // mobile
  else if (width >= 640 && width < 768)
    limit = 3; // small tablets
  else if (width >= 768 && width < 1024)
    limit = 3; // tablets
  else if (width >= 1024) limit = 5; // desktop

  // Navigation refs
  useEffect(() => {
    if (swiperInstance && prevRef.current && nextRef.current) {
      swiperInstance.params.navigation.prevEl = prevRef.current;
      swiperInstance.params.navigation.nextEl = nextRef.current;
      swiperInstance.navigation.destroy();
      swiperInstance.navigation.init();
      swiperInstance.navigation.update();

      setIsBeginning(swiperInstance.isBeginning);
    }
  }, [swiperInstance]);

  return (
    <div className="relative w-full">
      {/* Header + Arrows */}
      <motion.div
        initial={{ y: 100, opacity: 0 }}
        whileInView={{ y: 0, opacity: 1 }}
        transition={{ duration: 0.4 }}
        viewport={{ once: true }}
        className="flex items-center justify-between"
      >
        <SectionHeader title={name} link={link} icon={icon} />
      </motion.div>
      {/* Prev Button */}
      <div
        ref={prevRef}
        className={`group absolute top-1/2 z-10 -translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full bg-white p-3 shadow  lg:left-2 ${
          isBeginning ? "hidden" : "block max-lg:hidden"
        }`}
      >
        <ChevronLeft
          size={24}
          className="text-site-gray-900 group-hover:text-site-primary-500 transition-all duration-300 ease-in-out"
        />
      </div>

      {/* Next Button */}
      <div
        ref={nextRef}
        className={`group absolute top-1/2 z-10 translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full bg-white p-3 shadow-md  lg:right-2 ${
          isEnd ? "hidden" : "block max-lg:hidden"
        }`}
      >
        <ChevronRight
          size={24}
          className="text-site-gray-900 group-hover:text-site-primary-500 transition-all duration-300 ease-in-out"
        />
      </div>
      {/* Swiper */}
      <Swiper
        modules={[Navigation, Pagination, Autoplay]}
        slidesPerView={2.2}
        spaceBetween={8}
        loop={false}
        pagination={false}
        autoplay={false}
        onSwiper={(swiper) => setSwiperInstance(swiper)}
        onSlideChange={(swiper) => {
          setIsBeginning(swiper.isBeginning);
          setIsEnd(swiper.isEnd);
        }}
        breakpoints={{
          640: { slidesPerView: 2.2, spaceBetween: 8 },
          768: { slidesPerView: 3.2, spaceBetween: 12 },
          1024: { slidesPerView: 5, spaceBetween: 24 },
        }}
      >
        {data &&
          data.map((slide, i) => (
            <SwiperSlide key={i}>
              {i < limit ? (
                // Animate only first 6 slides
                <motion.div
                  ref={ref}
                  initial={{ y: 100, opacity: 0 }}
                  whileInView={{ y: 0, opacity: 1 }}
                  transition={{ duration: 0.4, delay: i * 0.1 }}
                  viewport={{ once: true, amount: 0 }}
                >
                  <ProductCard {...slide} />
                </motion.div>
              ) : (
                <ProductCard {...slide} />
              )}
            </SwiperSlide>
          ))}

        {/* <SwiperSlide>
          <Link
            href={"/products"}
            className="bg-site-primary h-full rounded-[20px] flex items-center justify-center group"
          >
            <span className="text-white text-sm group-hover:underline inline-flex items-center gap-1">
              <Link2 size={20} />
              View All
            </span>
          </Link>
        </SwiperSlide> */}
      </Swiper>
    </div>
  );
}
