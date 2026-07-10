"use client";

import { imageBaseHostUrl } from "@/config/apiConfig";
import { Cross, Handbag, Heart, X } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useState } from "react";

import { api } from "@/lib/axios";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useSession } from "@/store/useAuthStore";
import toast from "react-hot-toast";
import { useGuestUserId } from "@/store/useGuestStore";
import { trackAddToCart } from "@/lib/trackEvent/trackAddToCart";

interface Props {
  id: number;
  product: {
    id: number;
    name: string;
    thumbnail_image: string;
    base_price: string;
    main_price: string;
    rating: number;
    slug: string;
  };
}

const ProductCard = (item: Props) => {
  const { id: wishlist_id, product } = item;
  const { user } = useSession();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user?.id : guestId;
  const queryClient = useQueryClient();

  // Delete wishlist item
  const { mutate } = useMutation({
    mutationKey: ["delete_wishlist", userId],
    mutationFn: async (id: number) => {
      const { data } = await api.delete(`/wishlists/${wishlist_id}`);
      return data;
    },
    onSuccess: (data) => {
      toast.success(
        data.message || "Product is successfully added to your wishlist",
      );
      queryClient.invalidateQueries({ queryKey: ["wishlist", userId] });
    },
    onError: (error) => {
      toast.error(error.message || "Something went wrong");
    },
  });

  const [imgSrc, setImgSrc] = useState(
    imageBaseHostUrl + product.thumbnail_image,
  );
  const fallback = "/images/placeholder.png";
  // Add to cart mutation
  const { mutate: addToCart, isPending } = useMutation({
    mutationKey: ["add_to_cart", userId],
    mutationFn: async ({
      id,
      quantity = 1,
    }: {
      id: number;
      quantity: number;
    }) => {
      const { data } = await api.post(`/carts/add`, {
        id,
        user_id: userId,
        quantity,
      });
      return data as CartResponseType;
    },

    onSuccess: (data: any) => {
      if (data.result === false) {
        toast.error(data.message);
      } else {
        toast.success(data.message);
      }
      queryClient.invalidateQueries({
        queryKey: ["get_cart", userId],
      });
      queryClient.invalidateQueries({
        queryKey: ["get_subtotal", userId],
      });
    },
    onError: (error: any) => {
      toast.error(error.message || "Something went wrong");
    },
  });

  // Add to cart handler with checking is user is logged in
  const handleAddToCart = ({
    id,
    quantity = 1,
  }: {
    id?: number;
    quantity?: number;
  }) => {
    if (!id) {
      toast.error("Invalid product");
      return;
    }

    addToCart({ id, quantity });
  };
  return (
    <div className="group shadow-custom relative flex items-center justify-between overflow-clip bg-white px-1 py-2 md:px-3">
      {/* Heart */}
      <div className="flex items-center">
        <div
          onClick={() => mutate(product.id)}
          className="cursor-pointer rounded-full bg-white p-1 md:p-2"
        >
          <X
            strokeWidth={1.5}
            className="hover:text-site-secondary-600 border-site-gray-600 rounded-full border p-0.5 font-bold text-[#141B34] transition-colors"
            size={20}
          />
        </div>
        <Link href={`/product/${product.slug}`} className="bg-white">
          <Image
            src={imgSrc}
            alt={product.name}
            width={150}
            height={150}
            className="h-auto w-16 min-w-16 rounded-md object-cover transition-transform duration-300 ease-in-out group-hover:scale-105 md:w-20 md:min-w-20"
            onError={() => setImgSrc(fallback)}
            loading="lazy"
            placeholder="blur"
            blurDataURL={fallback}
            unoptimized
          />
        </Link>

        <div className="p-3 md:p-3">
          {/* <p className="text-site-secondary-600 mb-1 text-sm font-medium">
            <span className="font-bold">{0}</span> Reviews
            </p> */}
          {/* Product Title */}
          <Link
            href={`/product/${product.slug}`}
            className="inline-flex items-center"
          >
            <p className="text-site-gray-900 line-clamp-2 text-sm font-normal">
              {product.name}
            </p>
          </Link>
          <div className="mt-3 flex flex-wrap items-center justify-between gap-1 md:mt-4 md:items-center">
            <div className="nd:gap-2 relative flex items-center gap-1.5 md:flex-row">
              {/* Price */}
              <span className="text-site-gray-900 text-sm font-semibold md:text-[19px]">
                {product.main_price}
              </span>

              {/* Discount Price */}
              {product.base_price && (
                <span className="">
                  <span className="text-site-gray-300 text-xs line-through md:text-base">
                    {product.base_price}
                  </span>
                </span>
              )}
            </div>

            {/* Rating */}
            {/* <span className="bg-site-gray-50 px-2.5 md:px-3 py-1 flex items-center gap-1 md:gap-2 rounded-full">
                <MdStar className="text-[#FF9017]" />
            </span> */}
          </div>
        </div>
      </div>
      <button
        className="border-site-secondary-500 hover:bg-site-secondary-100/90 text-site-secondary-500 mt-4 flex max-w-[90px] min-w-[90px] cursor-pointer items-center justify-center gap-1.5 rounded-full border-2 bg-[#EFE6F8] px-1 py-1.5 text-center text-xs font-bold transition-colors duration-300 ease-in-out md:w-[120px] md:max-w-[120px] md:text-sm"
        onClick={() => handleAddToCart({ id: product?.id, quantity: 1 })}
      >
        Add to Bag
      </button>
    </div>
  );
};

export default ProductCard;
