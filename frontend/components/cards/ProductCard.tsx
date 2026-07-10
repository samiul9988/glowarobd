"use client";

import { imageBaseHostUrl } from "@/config/apiConfig";
import { Star } from "lucide-react";
// import { useCartStore } from "@/store/useCartStore";
import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import { GoStarFill } from "react-icons/go";

const ProductCard = (product: ProductType) => {
  const {
    name,
    main_price,
    thumbnail_image,
    stroked_price,
    is_new,
    is_preorder,
    in_stock,
    current_stock,
    has_discount,
    web_price,
    slug,
    rating,
    total_reviews,
    formatted_discount,
  } = product;
  // const { addToCart } = useCartStore();

  const [imgSrc, setImgSrc] = useState(imageBaseHostUrl + thumbnail_image);
  const fallback = "/images/placeholder.png";
  return (
    <div className="group border-site-gray-50 shadow-custom relative flex flex-col overflow-clip rounded-[10px] border bg-white">
      {/* Product Badges */}
      {in_stock && current_stock > 0 ? (
        <div className="absolute top-1 left-1 z-10 flex flex-col items-start gap-1 lg:top-2 lg:left-2">
          {/* Discount badge */}
          {has_discount && formatted_discount?.trim() !== "" && (
            <span className="font-bold rounded-full bg-[#FA045B] px-2 py-1 text-xs  text-white">
              {formatted_discount}
            </span>
          )}

          {/* New badge */}
          {is_new && (
            <span className="font-bold rounded-full bg-[#2B6BF4] px-2 py-1 text-xs  text-white">
              NEW
            </span>
          )}
        </div>
      ) : (
        // Stock out badge //absolute top-1/2 left-1/2 flex -translate-x-1/2 -translate-y-1/2
        (!in_stock || current_stock <= 0) && (
          <span className="font-bold absolute top-1 left-1 z-10 flex w-fit flex-col items-start gap-1 rounded-full bg-site-gray-900 px-2 py-1 text-xs  text-white opacity-100 lg:top-2 lg:left-2">
            STOCK OUT
          </span>
        )
      )}

      {/* Heart */}
      {/* <div className="absolute top-3 right-3 z-50 bg-white p-2 rounded-full shadow-md shadow-black/10 cursor-pointer">
        <Heart
          strokeWidth={1}
          className="text-site-primary-600 hover:fill-site-primary-600"
        />
      </div> */}
      <Link
        href={`/product/${slug}`}
        prefetch={false}
        className={`relative aspect-square overflow-hidden bg-white ${!in_stock && current_stock <= 0 && "opacity-50"}`}
      >
        <Image
          src={imgSrc}
          alt={name}
          fill
          className="object-cover transition-transform duration-300 ease-in-out group-hover:scale-110"
          onError={() => setImgSrc(fallback)}
          loading="lazy"
          placeholder="blur"
          blurDataURL={fallback}
          unoptimized
        />
      </Link>

      <div
        className={`p-2 md:p-4 ${!in_stock && current_stock <= 0 && "opacity-50"}`}
      >
        <p className=" text-site-secondary-600 mb-1 text-sm font-normal flex items-center gap-1">
            < GoStarFill size={14} className="text-[#FF9017]"/> 
            <span className="font-bold text-site-gray-900">{total_reviews}</span>
            Reviews
        </p>
        {/* Product Title */}
        <Link
          prefetch={false}
          href={`/product/${slug}`}
          className="inline-flex min-h-10 items-center"
        >
          <p className="text-site-gray-900 line-clamp-2 text-sm font-normal">
            {name}
          </p>
        </Link>
        <div className="mt-3 flex flex-wrap items-center justify-between gap-1 md:mt-4 md:items-center">
          <div className="nd:gap-2 relative flex items-center gap-1.5 md:flex-row">
            

            {/* Discount Price */}
            {stroked_price && has_discount && (
              <span className="">
                <span className="text-site-gray-400 text-xs line-through md:text-base">
                  {stroked_price}
                </span>
              </span>
            )}
            {/* Price */}
            <span className="text-site-primary-600 text-sm font-bold md:text-[19px]">
              {web_price}
            </span>
          </div>

          {/* Rating */}
          {/* <span className="bg-site-gray-50 px-2.5 md:px-3 py-1 flex items-center gap-1 md:gap-2 rounded-full">
            <MdStar className="text-[#FF9017]" />
          </span> */}
        </div>
        {/* <button
          className="mt-4 flex items-center justify-center gap-1.5 text-white bg-site-primary hover:bg-site-primary/90 w-full rounded-full py-1.5 text-center text-sm transition-colors duration-300 ease-in-out cursor-pointer"
          // onClick={() => addToCart({ ...product, quantity: 1 })}
        >
          <Handbag size={22} strokeWidth={1} />
          Add to cart
        </button> */}
      </div>
    </div>
  );
};

export default ProductCard;
