"use client";

import { useWindowWidth } from "@/hooks/useWindowWidth";
import { motion } from "framer-motion";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import "swiper/css";
import "swiper/css/navigation";
import { Autoplay, Navigation } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";
import ProductCard from "../cards/ProductCard";

interface Props {
  data: ProductType[];
}

export default function FeaturedSlider({ data }: Props) {
  const prevRef = useRef<HTMLDivElement>(null);
  const nextRef = useRef<HTMLDivElement>(null);
  const [swiperInstance, setSwiperInstance] = useState<any>(null);

  const [isBeginning, setIsBeginning] = useState(true);
  const [isEnd, setIsEnd] = useState(false);

  const width = useWindowWidth();

  let slidesPerView = 2.25;
  if (width >= 768 && width < 1024) slidesPerView = 3.25;
  else if (width >= 1024) slidesPerView = 3;

  useEffect(() => {
    if (swiperInstance && prevRef.current && nextRef.current) {
      swiperInstance.params.navigation.prevEl = prevRef.current;
      swiperInstance.params.navigation.nextEl = nextRef.current;
      swiperInstance.navigation.destroy();
      swiperInstance.navigation.init();
      swiperInstance.navigation.update();

      setIsBeginning(swiperInstance.isBeginning);
      setIsEnd(swiperInstance.isEnd);
    }
  }, [swiperInstance]);

  return (
    <div className="relative w-full lg:w-[calc(100%-520px)]">
      {/* Prev Button */}
      <div
        ref={prevRef}
        className={`group absolute top-1/2 z-10 -translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full bg-white p-3 shadow md:left-2 lg:left-0 ${
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
        className={`group absolute top-1/2 z-10 translate-x-1/2 -translate-y-1/2 cursor-pointer rounded-full bg-white p-3 shadow-md md:right-3 lg:right-0 ${
          isEnd ? "hidden" : "block max-lg:hidden"
        }`}
      >
        <ChevronRight
          size={24}
          className="text-site-gray-900 group-hover:text-site-primary-500 transition-all duration-300 ease-in-out"
        />
      </div>

      <Swiper
        modules={[Navigation, Autoplay]}
        slidesPerView={slidesPerView}
        // centeredSlides
        centeredSlidesBounds
        spaceBetween={8}
        loop={false}
        autoplay={false}
        onSwiper={(swiper) => setSwiperInstance(swiper)}
        onSlideChange={(swiper) => {
          setIsBeginning(swiper.isBeginning);
          setIsEnd(swiper.isEnd);
        }}
        breakpoints={{
          640: { slidesPerView: 2.25, spaceBetween: 8 },
          768: { slidesPerView: 3.33, spaceBetween: 10 },
          1024: { slidesPerView: 3, spaceBetween: 20 },
        }}
      >
        {data.map((slide, i) => (
          <SwiperSlide key={i}>
            {i < 3 ? (
              <motion.div
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
      </Swiper>
    </div>
  );
}
