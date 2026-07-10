"use client";

import { cn } from "@/lib/utils";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { GoArrowRight } from "react-icons/go";

interface Props extends React.ComponentProps<"div"> {
  title: string;
  subTitle?: string;
  link?: string;
  icon?: string;
  className?: string;
  linkStyle?: string;
}

const SectionHeader = ({
  title,
  subTitle,
  link,
  icon,
  className,
  linkStyle,
}: Props) => {
  return (
    <motion.div
      initial={{ y: 100, opacity: 0 }}
      whileInView={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.4 }}
      viewport={{ once: true }}
      className={cn(
        "flex items-center justify-between mb-4 md:mb-8 w-full",
        className
      )}
    >
      <div className="flex items-center gap-2 md:gap-4 flex-wrap">
        {icon && (
          <div className="relative h-7 w-7 md:h-12 md:w-12">
            <Image src={icon} alt={title} fill loading="lazy" unoptimized />
          </div>
        )}

        <div>
          {subTitle && (
            <h4 className="font-bold text-site-gray-300  text-sm md:text-[23px]">
              {subTitle}
            </h4>
          )}
          <h3 className="text-gray-900 font-bold text-[23px] md:text-[52px] md:leading-14">
            {title}
          </h3>
        </div>
      </div>
      {link && (
        <Link
          href={link}
          className={cn(
            "flex items-center gap-2 text-site-primary-500 font-semibold bg-site-primary-50 hover:bg-site-primary-100/80 transition-colors px-4 py-2 rounded-full text-[12px] md:text-base group",
            linkStyle
          )}
        >
          See All
          <GoArrowRight
            className="h-4 w-4 md:h-6 md:w-6 group-hover:translate-x-1 transition-all"
            strokeWidth={0.3}
          />
        </Link>
      )}
    </motion.div>
  );
};

export default SectionHeader;
