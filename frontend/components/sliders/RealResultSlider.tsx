"use client";

import { useWindowWidth } from "@/hooks/useWindowWidth";
import { motion } from "framer-motion";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import "swiper/css";
import "swiper/css/navigation";
import { Autoplay, Navigation } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";
import RealResultCard from "../cards/RealResultCard";

interface Props {
  data: Video[];
  isProductPage?: boolean;
}

export default function RealResultSlider({ data, isProductPage }: Props) {
  const prevRef = useRef<HTMLDivElement>(null);
  const nextRef = useRef<HTMLDivElement>(null);
  const [swiperInstance, setSwiperInstance] = useState<any>(null);

  const [isBeginning, setIsBeginning] = useState(true);
  const [isEnd, setIsEnd] = useState(false);

  const width = useWindowWidth();
  let slidesPerView = 1.25;
  if (width >= 768 && width < 1024) slidesPerView = 4;
  else if (width >= 1024) slidesPerView = 4;

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
    <div className="relative w-full">
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

      <Swiper
        nested={true}
        modules={[Navigation, Autoplay]}
        slidesPerView={4} // full 4 slides visible initially
        spaceBetween={20} // gap between slides
        loop={false}
        autoplay={false}
        onSwiper={(swiper) => setSwiperInstance(swiper)}
        onSlideChange={(swiper) => {
          setIsBeginning(swiper.isBeginning);
          setIsEnd(swiper.isEnd);
        }}
        breakpoints={{
          0: { slidesPerView: 1.3, spaceBetween: 20 }, // mobile
          640: { slidesPerView: 2.25, spaceBetween: 20 },
          768: { slidesPerView: 4, spaceBetween: 20 }, // tablet
          1024: { slidesPerView: 4, spaceBetween: 20 }, // desktop
        }}
      >
        {data &&
          data?.map((slide, i) => (
            <SwiperSlide key={i} className="h-auto">
              {i < Math.ceil(slidesPerView) ? (
                <motion.div
                  initial={{ y: 100, opacity: 0 }}
                  whileInView={{ y: 0, opacity: 1 }}
                  transition={{ duration: 0.4, delay: i * 0.1 }}
                  viewport={{ once: true, amount: 0 }}
                >
                  <RealResultCard
                    data={slide}
                    isProductPage={isProductPage}
                    index={i}
                  />
                </motion.div>
              ) : (
                <RealResultCard
                  data={slide}
                  isProductPage={isProductPage}
                  index={i}
                />
              )}
            </SwiperSlide>
          ))}
      </Swiper>
    </div>
  );
}
