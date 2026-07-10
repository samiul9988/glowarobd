"use client";

import { useWindowWidth } from "@/hooks/useWindowWidth";
import { motion } from "framer-motion";
import Link from "next/link";
import "swiper/css";
import "swiper/css/navigation";
import ProductCard from "../cards/ProductCard";

interface Props {
  data: ProductType[];
  category: CategoryType;
}

export default function ShowByCategorySlider2({ data, category }: Props) {


  const width = useWindowWidth();

  let slidesPerView = 2.25;
  if (width >= 768 && width < 1024) slidesPerView = 3.25;
  else if (width >= 1024) slidesPerView = 5;



  return (
    <div className="relative w-full">
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-4 md:gap-5">
        {data.slice(0, 9).map((slide, i) => (
          <div key={i}>
            {i < Math.ceil(slidesPerView) ? (
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
          </div>
        ))}


        {
          category && (
            <Link href={`/category/${category.slug}`} className="text-white/90 group text-sm">
              <div className="bg-red-500 rounded-[10px] bg-gradient-to-b from-[#4D3589] to-[#00e0fb] h-full flex items-center justify-center text-center p-2 group-hover:underline group-hover:text-white transition-colors">
                See all Products
              </div>
            </Link>
          )
        }

      </div>
    </div>
  );
}
