"use client";

import Link from "next/link";
import Image from "next/image";
import { motion } from "framer-motion";
import { Info } from "lucide-react";
import { Autoplay, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";
import "swiper/css";
import "swiper/css/pagination";
import { imageBaseHostUrl } from "@/config/apiConfig";

interface Props {
  data: { products: ProductInVideo[] };
  isProductPage?: boolean;
}

export default function ProductHorizontalSlider({ data }: Props) {
  return (
    <div className="product-vertical-slider relative w-full overflow-hidden">
      <Swiper
        direction="vertical"
        slidesPerView={1}
        spaceBetween={0}
        loop={true}
        pagination={{
          clickable: true,
        }}
        autoplay={{
          delay: 3000,
          disableOnInteraction: false,
        }}
        speed={700}
        autoHeight={true}
        modules={[Autoplay, Pagination]}
        className="auto-swiper"
      >
        {data?.products?.map((product, index) => (
          <SwiperSlide key={index}>
            <motion.div
              initial={{ y: 40, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ duration: 0.4 }}
              className="py-2"
            >
              <Link
                href={`/${product?.slug || "#"}`}
                className="group hover:border-site-primary-300 relative flex gap-1 rounded-[10px] border border-gray-200 bg-gray-50 p-1.5 transition-colors lg:gap-4"
              >
                {/* Image */}
                <div className="relative h-[60px] w-[60px] shrink-0 overflow-hidden rounded-sm lg:w-[75px]">
                  <Image
                    src={imageBaseHostUrl + product.thumbnail_image}
                    alt={product.name}
                    fill
                    className="object-cover transition-transform duration-300 group-hover:scale-110"
                  />
                </div>

                {/* Info */}
                <div className="flex flex-col justify-center text-left">
                  <p className="text-site-gray-900 line-clamp-2 text-xs font-normal md:text-sm">
                    {product.name}
                  </p>
                  <div className="text-site-primary-500 flex items-center gap-1.5 text-xs font-normal">
                    <span className="text-site-secondary-600 font-bold md:text-base">
                      {product.formatted_base_discounted_price}
                    </span>
                    {product.save > 0 && (
                      <span className="rounded-full bg-white px-2 py-1 text-[10px] md:text-xs">
                        Save{" "}
                        <span className="font-semibold">
                          {product.currency}
                          {product.save}
                        </span>
                      </span>
                    )}
                  </div>
                </div>

                <div className="absolute right-1 bottom-1 hidden h-6 w-6 place-content-center lg:grid">
                  <Info className="text-site-gray-700 h-4 w-4" />
                </div>
              </Link>
            </motion.div>
          </SwiperSlide>
        ))}
      </Swiper>

      {/* Custom styles */}
      <style jsx global>{`
        /* Fix auto height */
        .product-vertical-slider .auto-swiper,
        .product-vertical-slider .auto-swiper .swiper-slide {
          height: auto !important;
        }

        .product-vertical-slider .auto-swiper .swiper-wrapper {
          align-items: stretch !important;
        }

        .product-vertical-slider .swiper-pagination {
          left: 3px;
          width: 0px !important;
        }
        .product-vertical-slider .swiper-pagination-bullet {
          width: 4px;
          height: 4px;
          margin: 3px !important;
        }
      `}</style>
    </div>
  );
}
