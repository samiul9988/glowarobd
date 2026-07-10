"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { imageBaseUrl } from "@/config/apiConfig";

interface Props {
  id: string;
  photo: string;
  position: number;
  type: string;
  url: string;
}

const SupportServiceCard = ({ photo, i }: Props & { i: number }) => {
  return (
    <motion.div
      className="rounded-[20px]"
      initial={{ y: 100, opacity: 0 }}
      whileInView={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.4, delay: i * 0.1 }}
      viewport={{ once: true, amount: 0.3 }}
    >
      <Image
        src={imageBaseUrl + photo}
        alt="Category"
        width={382}
        height={112}
        className="w-full h-auto object-cover"
        sizes="(min-width: 768px) 33vw, (min-width: 640px) 50vw, 100vw"
        loading="lazy"
        placeholder="blur"
        blurDataURL={imageBaseUrl + photo}
      />
    </motion.div>
  );
};

export default SupportServiceCard;
