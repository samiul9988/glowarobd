"use client";

import ProductCard from "@/components/cards/ProductCard";
import { motion } from "framer-motion";

import "swiper/css";
import "swiper/css/navigation";

interface Props {
  res: DealDetailsResponse;
}

export default function FlashDealDetailsProductGrid({ res }: Props) {
  const { data } = res;

  return (
    <div className="relative w-full">
      <div className="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-5 lg:grid-cols-5 xl:grid-cols-5">
        {data.map((slide, i) => (
          <motion.div
            key={i}
            initial={{ y: 40, opacity: 0 }}
            whileInView={{ y: 0, opacity: 1 }}
            transition={{
              duration: 0.3,
              ease: "easeOut",
              delay: i * 0.04, // group-based delay (faster)
            }}
            viewport={{ once: true, amount: 0.1 }} // triggers earlier while scrolling
          >
            <ProductCard {...slide} />
          </motion.div>
        ))}
      </div>
    </div>
  );
}
