"use client";

import {imageBaseUrl } from "@/config/apiConfig";
import clsx from "clsx";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";

interface Props {
  id: string;
  photo: string;
  position: number;
  type: string;
  url: string;
}

const FeaturedCard = ({ photo, url, i }: Props & { i: number }) => {
  return (
    <Link prefetch={false} href={url || "#"}  className="banner-item ">
      <motion.div
        className="group relative flex flex-col h-full items-center justify-between"
        initial={{ y: 50, opacity: 0 }}
        whileInView={{ y: 0, opacity: 1 }}
        transition={{ duration: 0.4, delay: i * 0.1 }}
        viewport={{ once: true, amount: 0 }}
      >
        {/* Card Image */}
        <Image
          src={imageBaseUrl + photo}
          alt="Category"
          width={400}
          height={400}
          className="h-full w-full transform rounded-[10px] object-cover transition-transform duration-500 ease-in-out group-hover:scale-105"
          sizes="(min-width: 768px) 33vw, (min-width: 640px) 50vw, 100vw"
          unoptimized
        />
      </motion.div>
    </Link>
  );
};

export default FeaturedCard;
