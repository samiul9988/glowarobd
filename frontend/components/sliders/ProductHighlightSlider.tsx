"use client";

import { Autoplay, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import ProductHighlightCard from "../cards/ProductHighlightCard";

interface Props {
  data: HighlightItem[];
}

export default function ProductHighlightSlider({ data: slides = [] }: Props) {
  return (
    <Swiper
      modules={[Pagination, Autoplay]}
      spaceBetween={30}
      slidesPerView={1}
      loop
      pagination={{ clickable: true }}
      autoplay={{
        delay: 4000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      }}
      speed={1000}
      className="mySwiper md:!pb-[100px] lg:!pb-[160px]"
    >
      {slides.map((slide, index) => (
        <SwiperSlide key={index}>
          <ProductHighlightCard {...slide} />
        </SwiperSlide>
      ))}
    </Swiper>
  );
}
