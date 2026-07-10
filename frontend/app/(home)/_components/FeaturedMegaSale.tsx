"use client";

import { imageBaseHostUrl } from "@/config/apiConfig";
import { motion } from "framer-motion";
import Image from "next/image";

interface Props {
  banner: string;
}

const FeaturedMegaSale = ({ banner }: Props) => {
  return (
    <motion.div
      initial={{ opacity: 0 }}
      whileInView={{ opacity: 1 }}
      transition={{ duration: 2, delay: 0.1 }}
      viewport={{ once: true, amount: 0 }}
      className="relative aspect-[772/372] w-full !rounded-[10px]" // maintain aspect ratio
    >
      <Image
        src={imageBaseHostUrl + banner}
        alt="banner"
        fill
        style={{ objectFit: "cover" }}
        sizes="100vw"
        priority
        className="rounded-[10px]"
      />
    </motion.div>
  );
};

export default FeaturedMegaSale;
