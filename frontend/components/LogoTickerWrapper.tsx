"use client";

import { motion } from "framer-motion";
import LogoTicker from "./LogoTicker";

interface Props {
  logos: BrandType[];
}

const LogoTickerWrapper = ({ logos }: Props) => {
  const middleIndex = Math.ceil(logos.length / 2);
  const logos1 = logos.slice(0, middleIndex);
  const logos2 = logos.slice(middleIndex);

  return (
    <motion.div
      initial={{ y: 100, opacity: 0 }}
      whileInView={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.4, delay: 0.1 }}
      viewport={{ once: true }}
      className="relative w-full space-y-2.5 overflow-hidden md:space-y-6"
    >
      <LogoTicker logos={logos1} direction="right" speedPxPerSec={40} />
      <LogoTicker logos={logos2} direction="left" speedPxPerSec={40} />
    </motion.div>
  );
};

export default LogoTickerWrapper;
