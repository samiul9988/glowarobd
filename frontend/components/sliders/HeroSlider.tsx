"use client";

import { imageBaseUrl } from "@/config/apiConfig";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { Autoplay, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

interface SliderType {
  photo: string;
  photo_web: string;
  url?: string;
}

interface Props {
  data: SliderType[];
}

export default function HeroSlider({ data: slides }: Props) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 100 }}
      whileInView={{ opacity: 1, y: 0 }}
      transition={{
        duration: 0.4,
      }}
      viewport={{ once: true, amount: 0 }}
      className="group relative w-full"
    >
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
        className="mySwiper rounded-md md:rounded-2xl"
      >
        {slides.map((slide, index) => (
          <SwiperSlide key={index}>
            <div className="relative">
              {slide.url ? (
                <Link href={slide.url} prefetch={false}>
                  {slide.photo_web && (
                    <Image
                      src={imageBaseUrl + slide.photo_web}
                      alt="Slide image desktop"
                      className="h-full w-full rounded-sm object-cover md:rounded-md"
                      width={1600}
                      height={528}
                    />
                  )}
                </Link>
              ) : (
                slide.photo_web && (
                  <Image
                    src={imageBaseUrl + slide.photo_web}
                    alt="Slide image desktop"
                    className="h-full w-full rounded-sm object-cover md:rounded-md"
                    width={1600}
                    height={528}
                  />
                )
              )}
            </div>
          </SwiperSlide>
        ))}
      </Swiper>
    </motion.div>
  );
}
