"use client";

import { motion } from "framer-motion";
import Image from "next/image";
const GiftImgAnimation = () => {
  return (
    <motion.div
      className="absolute top-0 right-0 z-0 h-[300px] w-[300px] translate-x-[125px] translate-y-[140px] transform md:h-[390px] md:w-[390px] md:translate-x-[150px] md:translate-y-[180px] lg:translate-x-[220px] lg:-translate-y-[42px]"
      initial={{ opacity: 0, y: 150 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.1 }}
      transition={{ duration: 2, ease: "easeIn" }}
    >
      <motion.div
        className="h-[300px] w-[300px] md:h-[390px] md:w-[390px]"
        animate={{
          x: [0, 15, -10, 20, -15, 0],
          y: [0, -20, 15, -10, 20, 0],
          rotate: [0, 5, -5, 10, -10, 0],
          scale: [1, 1.05, 0.95, 1.02, 1],
        }}
        transition={{
          duration: 15,
          repeat: Infinity,
          ease: "easeInOut",
        }}
      >
        <Image
          src="/images/footer/footer-gift.png"
          alt="emartway-products"
          fill
        />
      </motion.div>
    </motion.div>
  );
};

export default GiftImgAnimation;
