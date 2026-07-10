"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import { Autoplay, Navigation } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";

import { useWindowWidth } from "@/hooks/useWindowWidth";
import { motion } from "framer-motion";
import "swiper/css";
import "swiper/css/navigation";
import SectionHeader from "../SectionHeader";
import TestimonialCard from "../cards/TestimonialCard";

interface Props {
  testimonials: TestimonialItem[];
  name?: string;
}

export default function TestimonialSlider({ testimonials, name }: Props) {
  const prevRef = useRef<HTMLDivElement>(null);
  const nextRef = useRef<HTMLDivElement>(null);
  const [swiperInstance, setSwiperInstance] = useState<any>(null);
  const [activePage, setActivePage] = useState(0);

  const width = useWindowWidth();

  // slidesPerView logic
  let slidesPerView = 1.4;
  if (width >= 768 && width < 1024) slidesPerView = 2.33;
  else if (width >= 1024) slidesPerView = 3;

  const DESKTOP_GROUP = 3;

  // Pagination indices: show ALL pages (full + incomplete)
  const pagesIndices = useMemo(() => {
    if (width < 1024) return []; // no pagination for mobile/tablet
    const totalPages = Math.ceil(testimonials.length / DESKTOP_GROUP);
    return Array.from({ length: totalPages }, (_, i) => i * DESKTOP_GROUP);
  }, [testimonials.length, width]);

  // attach navigation
  useEffect(() => {
    if (!swiperInstance) return;
    if (prevRef.current && nextRef.current) {
      swiperInstance.params.navigation.prevEl = prevRef.current;
      swiperInstance.params.navigation.nextEl = nextRef.current;
      swiperInstance.navigation.destroy();
      swiperInstance.navigation.init();
      swiperInstance.navigation.update();
    }
  }, [swiperInstance]);

  // Swiper init
  const handleSwiperInit = (swiper: any) => {
    setSwiperInstance(swiper);
    if (width >= 1024) {
      const idx =
        typeof swiper.activeIndex === "number" ? swiper.activeIndex : 0;
      setActivePage(Math.floor(idx / DESKTOP_GROUP));
    } else {
      setActivePage(0);
    }
  };

  // Swiper slide change
  const handleSlideChange = (swiper: any) => {
    if (width >= 1024) {
      const idx = swiper.activeIndex ?? 0;
      setActivePage(Math.floor(idx / DESKTOP_GROUP));
    }
  };

  // handle resize
  useEffect(() => {
    if (!swiperInstance) return;
    if (width < 1024) {
      setActivePage(0);
      return;
    }
    const idx = swiperInstance.activeIndex ?? 0;
    const page = Math.floor(idx / DESKTOP_GROUP);
    setActivePage(page);
  }, [pagesIndices, width, swiperInstance]);

  return (
    <div className="testimonial relative w-full">
      {/* Header + Arrows */}
      <SectionHeader
        title={name ?? "Testimonials"}
        icon="/images/testimonial-msg-icon.png"
        // link="/"
        className="mb-0"
      />

      {/* Swiper */}
      <Swiper
        autoHeight={false}
        modules={[Navigation, Autoplay]}
        slidesPerView={slidesPerView}
        centeredSlides={width < 1024}
        centeredSlidesBounds={width < 1024}
        spaceBetween={16}
        loop={false}
        autoplay={false}
        onSwiper={handleSwiperInit}
        onSlideChange={handleSlideChange}
        breakpoints={{
          640: { slidesPerView: 1.4, spaceBetween: 16, slidesPerGroup: 1 },
          768: { slidesPerView: 2.33, spaceBetween: 16, slidesPerGroup: 1 },
          1024: {
            slidesPerView: 3,
            spaceBetween: 40,
            slidesPerGroup: DESKTOP_GROUP,
          },
        }}
        className="!pb-4 md:!pb-4"
      >
        {testimonials.map((testimonial, i) => (
          <SwiperSlide
            key={i}
            className="rounded-md !transition-all !duration-200"
          >
            {i < Math.ceil(slidesPerView) ? (
              <motion.div
                initial={{ y: 100, opacity: 0 }}
                whileInView={{ y: 0, opacity: 1 }}
                transition={{ duration: 0.4, delay: i * 0.1 }}
                viewport={{ once: true, amount: 0 }}
              >
                <TestimonialCard testimonial={testimonial} />
              </motion.div>
            ) : (
              <TestimonialCard testimonial={testimonial} />
            )}
          </SwiperSlide>
        ))}
      </Swiper>

      {width >= 1024 && pagesIndices.length > 1 && (
        <div className="mt-4 flex justify-center gap-2">
          {pagesIndices.map((startIndex, pageIndex) => (
            <button
              key={pageIndex}
              onClick={() => swiperInstance?.slideTo(startIndex, 400)}
              className={`h-2 w-2 rounded-full transition-all ${
                activePage === pageIndex
                  ? "bg-site-primary-500 w-5"
                  : "bg-site-primary-200 cursor-pointer"
              }`}
              aria-label={`Go to testimonials page ${pageIndex + 1}`}
            />
          ))}
        </div>
      )}
    </div>
  );
}
