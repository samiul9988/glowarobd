"use client";

import { SpecialOfferZoneTab } from "@/components/tabs/SpecialOfferZoneTab";
import { motion, useScroll, useSpring, useTransform } from "framer-motion";
import Image from "next/image";
import { useRef } from "react";

interface Props {
  productsByFlashDeal: {
    flashdeal: FlashDealType;
    products: ProductType[] | undefined;
  }[];
}

const SpecialOfferZone = ({ productsByFlashDeal }: Props) => {
  const sectionRef = useRef(null);

  // Scroll progress
  const { scrollYProgress } = useScroll({
    target: sectionRef,
    offset: ["start end", "end start"],
  });

  // Smooth scroll spring
  const smoothProgress = useSpring(scrollYProgress, {
    stiffness: 100,
    damping: 25,
    mass: 0.5,
  });

  // Common transforms
  const translateY = useTransform(smoothProgress, [0, 1], [-80, 120]);

  const rotate = useTransform(
    smoothProgress,
    [0, 0.3, 0.6, 1],
    [20, 10, -8, 0],
  );

  const scale = useTransform(
    smoothProgress,
    [0, 0.3, 0.6, 1],
    [0.85, 0.95, 1.05, 1],
  );

  // Custom variations for each item
  const slowTranslate = useTransform(smoothProgress, [0, 1], [-40, 60]);
  const fastTranslate = useTransform(smoothProgress, [0, 1], [-100, 160]);

  const fade = useTransform(smoothProgress, [0, 0.1, 1], [0, 1, 1]);

  return (
    <div
      ref={sectionRef}
      className="relative w-full overflow-clip rounded-[14px] bg-[linear-gradient(279.47deg,#A084BD_0%,#432760_99.21%)] transition-transform duration-1000 ease-in-out p-3 md:p-[30px]"
    >
      
      {/* Tab */}
      <SpecialOfferZoneTab data={productsByFlashDeal} />
    </div>
  );
};

export default SpecialOfferZone;
