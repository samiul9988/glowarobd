"use client";

import { useEffect, useRef, useState } from "react";

import { useWindowWidth } from "@/hooks/useWindowWidth";
import { motion, useInView } from "framer-motion";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import CategoryCard from "../cards/CategoryCard";

interface Icon{
    app:string;
    mobile:string;
    web:string
}
interface Props {
  data: {
    id: number;
    name: string;
    slug: string;
    featured_icons: Icon;
  }[];
  title?: string;
}

export default function TopCategoriesSlider({ data }: Props) {
//   const prevRef = useRef<HTMLDivElement>(null);
//   const nextRef = useRef<HTMLDivElement>(null);
//   const [swiperInstance, setSwiperInstance] = useState<any>(null);

  const ref = useRef(null);
//   const isInView = useInView(ref, { once: true, amount: 0.3 });

  const width = useWindowWidth();

  let limit = 4; // default
  if (width < 640)
    limit = 4; // mobile
  else if (width >= 640 && width < 768)
    limit = 6; // small tablets
  else if (width >= 768 && width < 1024)
    limit = 12; // tablets
  else if (width >= 1024) limit = 12; // desktop

  // Autoplay toggle
  // useEffect(() => {
  //   if (swiperInstance) {
  //     if (isInView) {
  //       swiperInstance.autoplay.start();
  //     } else {
  //       swiperInstance.autoplay.stop();
  //     }
  //   }
  // }, [isInView, swiperInstance]);

  // Navigation refs
//   useEffect(() => {
//     if (swiperInstance && prevRef.current && nextRef.current) {
//       swiperInstance.params.navigation.prevEl = prevRef.current;
//       swiperInstance.params.navigation.nextEl = nextRef.current;
//       swiperInstance.navigation.destroy();
//       swiperInstance.navigation.init();
//       swiperInstance.navigation.update();
//     }
//   }, [swiperInstance]);
  return (
    <div className="relative w-full">
        <div
            className="
            no-scrollbar 
            md:grid md:grid-cols-5 md:gap-x-5 md:gap-y-8 md:overflow-hidden
            
            grid 
            grid-flow-col 
            grid-rows-2 
            auto-cols-[calc((100%/3.5))] 
            gap-3 
            overflow-x-auto 
            overflow-y-hidden
            "
        >
            {data.slice(0, 10).map((slide, i) => (
            <div
                key={slide.id}
                className="group w-full min-w-[100px] p-1 hover:shadow-xs"
            >
                {i < limit ? (
                <motion.div
                    ref={ref}
                    initial={{ y: 100, opacity: 0 }}
                    whileInView={{ y: 0, opacity: 1 }}
                    transition={{ duration: 0.4, delay: i * 0.1 }}
                    viewport={{ once: true, amount: 0 }}
                >
                    <CategoryCard {...slide} />
                </motion.div>
                ) : (
                <CategoryCard {...slide} />
                )}
            </div>
            ))}
        </div>
    </div>
  );
}
