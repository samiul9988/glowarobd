"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";
import CategoryImage from "./CategoryImage";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { motion } from "framer-motion";

interface CategoriesGridProps {
  categories: {
    id: number;
    slug: string;
    name: string;
    featured_icon: string;
  }[];
}

const CategoriesGrid = ({ categories }: CategoriesGridProps) => {
  const [cols, setCols] = useState(2);

  useEffect(() => {
    const handleResize = () => {
      const width = window.innerWidth;
      if (width >= 1024) setCols(7);
      else if (width >= 768) setCols(4);
      else if (width >= 640) setCols(3);
      else setCols(2);
    };

    handleResize();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const total = categories.length;
  const lastRowStart = Math.floor((total - 1) / cols) * cols;

  return (
    <div className="grid grid-cols-3 gap-x-2 gap-y-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 lg:gap-y-8">
      {categories.map((category, index) => {
        const isLastRow = index >= lastRowStart;
        return (
          <motion.div
            key={category.id}
            initial={{ opacity: 0, scale: 0.6, y: 40, rotate: -5 }}
            animate={{ opacity: 1, scale: 1, y: 0, rotate: 0 }}
            transition={{
              type: "spring",
              stiffness: 120,
              damping: 14,
              delay: index * 0.05,
            }}
          >
            <Link
              href={`/category/${category.slug}`}
              className={cn(
                "group flex flex-col items-center justify-center gap-3",
                isLastRow && "pb-0",
              )}
            >
              <CategoryImage
                src={imageBaseHostUrl + category.featured_icon}
                alt={category.name}
                className="h-[120px] w-[140px] object-contain transition-all duration-200 group-hover:scale-105"
              />
              <p className="text-site-gray-800 text-center text-base font-medium">
                {category.name}
              </p>
            </Link>
          </motion.div>
        );
      })}
    </div>
  );
};

export default CategoriesGrid;
