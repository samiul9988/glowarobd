"use client";

import Container from "@/components/Container";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { api } from "@/lib/axios";
import { useQuery } from "@tanstack/react-query";
import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { Skeleton } from "@/components/ui/skeleton";

// Root Response Type
export interface BrandResponse {
  data: Brand[];
  links: PaginationLinks;
  meta: PaginationMeta;
  success: boolean;
  status: number;
}

export interface Brand {
  id: number;
  slug: string;
  name: string;
  logo: string;
  links: {
    products: string;
  };
}

export interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  links: PaginationMetaLink[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginationMetaLink {
  url: string | null;
  label: string;
  active: boolean;
}

const BrandsListSection = () => {
  const { data: brands, isLoading } = useQuery({
    queryKey: ["brands"],
    queryFn: async () => {
      const { data } = await api.get("/brands?limit=50");
      return data as BrandResponse;
    },
  });

  return (
    <section className="pt-10 pb-16 md:pb-[140px]">
      <Container>
        <div className="flex flex-wrap justify-center gap-x-4 gap-y-4 md:gap-x-5 md:gap-y-6">
          {/* Loading Skeleton */}
          {isLoading &&
            Array.from({ length: 30 }).map((_, i) => (
              <div
                key={i}
                className="border-site-gray-100 h-[50px] w-[108px] flex-shrink-0 rounded-full border bg-white/50 p-1 sm:h-[60px] sm:w-40 md:w-32"
              >
                <Skeleton className="h-full w-full rounded-full" />
              </div>
            ))}

          {/* Brand Items with Animation */}
          {!isLoading &&
            brands?.data.map((brand, index) => (
              <motion.div
                key={brand.id}
                initial={{ opacity: 0, scale: 0.6, y: 40, rotate: -5 }}
                animate={{ opacity: 1, scale: 1, y: 0, rotate: 0 }}
                transition={{
                  type: "spring",
                  stiffness: 120,
                  damping: 14,
                  delay: index * 0.05, // stagger effect
                }}
                className="cursor-pointer"
              >
                <Link href={`/brand/${brand.slug}`}>
                  <div className="border-site-gray-100 hover:border-site-primary group relative aspect-square h-[50px] w-[108px] flex-shrink-0 cursor-pointer overflow-hidden rounded-full border bg-white/50 p-2 transition-all sm:h-[60px] sm:w-40 md:w-32">
                    <Image
                      src={imageBaseHostUrl + brand.logo}
                      alt={brand.name}
                      fill
                      sizes="(max-width: 640px) 8rem, (max-width: 768px) 10rem, 10rem"
                      placeholder="blur"
                      blurDataURL="/images/placeholder.png"
                      className="bg-white object-contain p-1 opacity-0 transition-all duration-500 ease-in-out group-hover:scale-110"
                      onLoadingComplete={(img) =>
                        img.classList.remove("opacity-0")
                      }
                      loading="lazy"
                      draggable={false}
                    />
                  </div>
                </Link>
              </motion.div>
            ))}
        </div>
      </Container>
    </section>
  );
};

export default BrandsListSection;
