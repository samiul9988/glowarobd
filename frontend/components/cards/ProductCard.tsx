"use client";

import { imageBaseHostUrl } from "@/config/apiConfig";
import { api } from "@/lib/axios";
import { cn } from "@/lib/utils";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { Handbag, Heart } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import toast from "react-hot-toast";
import { GoStarFill } from "react-icons/go";

const fallback = "/images/placeholder.png";

const ProductCard = (product: ProductType) => {
  const {
    id,
    name,
    main_price,
    thumbnail_image,
    stroked_price,
    is_new,
    in_stock,
    current_stock,
    has_discount,
    web_price,
    slug,
    total_reviews,
    formatted_discount,
  } = product;

  const [imgSrc, setImgSrc] = useState(imageBaseHostUrl + thumbnail_image);

  const { accessToken } = useToken();
  const { setOpen } = useAuthModalStore();
  const { guestId, createGuestId } = useGuestUserId();
  const queryClient = useQueryClient();
  const userId = accessToken ? undefined : (guestId || createGuestId());

  const currentUserId = accessToken || userId;

  // --- Wishlist check ---
  const { data: isWishListed, refetch: refetchWishlist } = useQuery({
    queryKey: ["is_wish_listed", id, currentUserId],
    enabled: !!id && !!currentUserId,
    queryFn: async () => {
      const { data } = await api.get(
        `/wishlists-check-product?product_id=${id}&user_id=${currentUserId}`,
      );
      return data as { is_in_wishlist: boolean; wishlist_id: number };
    },
  });

  // --- Add to cart ---
  const { mutate: addToCart, isPending: cartPending } = useMutation({
    mutationKey: ["add_to_cart", currentUserId],
    mutationFn: async () => {
      const { data } = await api.post("/carts/add", {
        id,
        variant: "",
        user_id: currentUserId,
        quantity: 1,
      });
      return data as { result: boolean; message: string };
    },
    onSuccess: (data) => {
      if (data.result === false) {
        toast.error(data.message);
      } else {
        toast.success("Added to cart");
      }
      queryClient.invalidateQueries({ queryKey: ["get_cart", currentUserId] });
      queryClient.invalidateQueries({ queryKey: ["get_subtotal", currentUserId] });
    },
    onError: () => toast.error("Something went wrong"),
  });

  // --- Wishlist toggle ---
  const { mutate: toggleWishlist } = useMutation({
    mutationKey: ["toggle_wishlist", id, currentUserId],
    mutationFn: async () => {
      if (isWishListed?.is_in_wishlist) {
        await api.delete(`/wishlists/${isWishListed.wishlist_id}`, {
          headers: { Authorization: `Bearer ${accessToken}` },
        });
      } else {
        await api.post("/wishlists", { product_id: id, user_id: currentUserId });
      }
    },
    onSuccess: () => {
      if (isWishListed?.is_in_wishlist) {
        toast.success("Removed from wishlist");
      } else {
        toast.success("Added to wishlist");
      }
      refetchWishlist();
      queryClient.invalidateQueries({ queryKey: ["wishlist", currentUserId] });
    },
    onError: () => toast.error("Something went wrong"),
  });

  const handleWishlist = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    toggleWishlist();
  };

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    addToCart();
  };

  const outOfStock = !in_stock || current_stock <= 0;

  return (
    <div className="group border-site-gray-50 shadow-custom relative flex flex-col overflow-clip rounded-[10px] border bg-white">
      {/* Badges */}
      {!outOfStock ? (
        <div className="absolute top-1 left-1 z-10 flex flex-col items-start gap-1 lg:top-2 lg:left-2">
          {has_discount && formatted_discount?.trim() !== "" && (
            <span className="rounded-full bg-[#FA045B] px-2 py-1 text-xs font-bold text-white">
              {formatted_discount}
            </span>
          )}
          {is_new && (
            <span className="rounded-full bg-[#2B6BF4] px-2 py-1 text-xs font-bold text-white">
              NEW
            </span>
          )}
        </div>
      ) : (
        <span className="absolute top-1 left-1 z-10 rounded-full bg-site-gray-900 px-2 py-1 text-xs font-bold text-white lg:top-2 lg:left-2">
          STOCK OUT
        </span>
      )}

      {/* Wishlist Heart — hover on desktop, always on mobile */}
      {in_stock && current_stock > 0 && (
        <button
          onClick={handleWishlist}
          className={cn(
            "absolute top-1 right-1 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-md shadow-black/10 transition-all duration-300 ease-out cursor-pointer",
            "md:translate-x-2 md:opacity-0 md:group-hover:translate-x-0 md:group-hover:opacity-100",
            "lg:top-2 lg:right-2 lg:h-9 lg:w-9",
          )}
        >
          <Heart
            size={18}
            strokeWidth={1.5}
            className={cn(
              "transition-colors",
              isWishListed?.is_in_wishlist
                ? "fill-rose-500 text-rose-500"
                : "text-site-gray-500 hover:text-rose-400",
            )}
          />
        </button>
      )}

      {/* Product Image */}
      <Link
        href={`/product/${slug}`}
        prefetch={false}
        className={cn(
          "relative aspect-square overflow-hidden bg-white",
          outOfStock && "opacity-50",
        )}
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

      {/* Card Body */}
      <div className={cn("flex flex-1 flex-col p-2 md:p-4", outOfStock && "opacity-50")}>
        {/* Reviews */}
        <p className="mb-1 flex items-center gap-1 text-sm font-normal text-site-secondary-600">
          <GoStarFill size={14} className="text-[#FF9017]" />
          <span className="font-bold text-site-gray-900">{total_reviews}</span>
          Reviews
        </p>

        {/* Title */}
        <Link
          prefetch={false}
          href={`/product/${slug}`}
          className="inline-flex min-h-10 items-center"
        >
          <p className="line-clamp-2 text-sm font-normal text-site-gray-900">
            {name}
          </p>
        </Link>

        {/* Price */}
        <div className="mt-3 flex flex-wrap items-center gap-1.5 md:mt-4">
          {stroked_price && has_discount && (
            <span className="text-xs text-site-gray-400 line-through md:text-base">
              {stroked_price}
            </span>
          )}
          <span className="text-sm font-bold text-site-primary-600 md:text-[19px]">
            {web_price}
          </span>
        </div>

        {/* Add to Cart — hover on desktop, always on mobile */}
        <div
          className={cn(
            "mt-auto pt-2 transition-all duration-300 ease-out",
            "md:max-h-0 md:overflow-hidden md:opacity-0 md:group-hover:max-h-12 md:group-hover:opacity-100",
            "max-md:max-h-12 max-md:opacity-100",
          )}
        >
          <button
            onClick={handleAddToCart}
            disabled={outOfStock || cartPending}
            className={cn(
              "flex w-full items-center justify-center gap-1.5 rounded-full py-2 text-center text-sm font-medium transition-colors cursor-pointer",
              outOfStock
                ? "cursor-not-allowed bg-gray-300 text-gray-500"
                : "bg-site-primary text-white hover:bg-site-primary/90",
            )}
          >
            <Handbag size={18} strokeWidth={1.5} />
            {outOfStock ? "Stock Out" : cartPending ? "Adding..." : "Add to Cart"}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
