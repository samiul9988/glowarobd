"use client";

import Container from "@/components/Container";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { useCountdown } from "@/hooks/useCountdown";
import { api } from "@/lib/axios";
import { cn } from "@/lib/utils";
import { useSession } from "@/store/useAuthStore";
import { useCartStore } from "@/store/useCartStore";
import { useShowHeader } from "@/store/useShowHeader";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { LoaderCircle, Minus, Plus } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useMemo, useState } from "react";
import toast from "react-hot-toast";
import { FaAngleRight, FaStar } from "react-icons/fa";
import ProductInfo from "../_components/ProductInfo";
import { useGuestUserId } from "@/store/useGuestStore";
import ProductGallery from "./ProductGallery";
import VariantSelector from "./VariantSelector";
import { SkinConcernIcon, SkinTypeIcon } from "@/components/icons/icon-library";
import { trackAddToCart } from "@/lib/trackEvent/trackAddToCart";
import BodyText from "@/components/BodyText";
import Heading from "@/components/Heading";
import ProductDescriptionTabs from "./ProductDescriptionTabs";

interface DetailsProps {
  product: ProductDetailType;
}

interface SelectedVariant {
  [key: string]: string | undefined;
  color?: string;
}

interface isWishlistResponseType {
  message: string;
  is_in_wishlist: boolean;
  product_id: number;
  wishlist_id: number;
}

const DetailsSection = ({ product }: DetailsProps) => {
  // Local state
  const [quantity, setQuantity] = useState(1);
  const router = useRouter();
  const showHeader = useShowHeader((state) => state.showHeader);
  // Global state for auth modal

  // Get product images from API data
  const images = product?.photos.map(
    (photo) => `${imageBaseHostUrl}${photo.path}`,
  );
  // Get product variants from API data
  const [selectedVariant, setSelectedVariant] = useState<SelectedVariant>({});
  const handleVariantChange = (variant: Record<string, string>) => {
    setSelectedVariant(variant);
  };

  // Variant generate function
  function generateVariant(data: SelectedVariant): string {
    const { color, ...options } = data;

    // Remove spaces from all option values, filtering out undefined values
    const cleanedOptions = Object.values(options)
      .filter((v): v is string => v !== undefined)
      .map((v) => v.replace(/\s+/g, ""));

    // Join color (if exists) with cleaned options
    return color
      ? [color, ...cleanedOptions].join("-")
      : cleanedOptions.join("-");
  }

  const generatedSelectedVariant = generateVariant(selectedVariant);

  const handleQuantityChange = (newQuantity: number) => {
    if (newQuantity >= 1) {
      setQuantity(newQuantity);
    }
  };

  // short Description limit
  const [expanded, setExpanded] = useState(false);

  // Add to cart functionality
  const queryClient = useQueryClient();
  const { accessToken } = useToken();
  const { user } = useSession();
  const { setOpen } = useCartStore();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user.id : guestId;

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
        variant: generatedSelectedVariant ? generatedSelectedVariant : "",
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
        setOpen(true);
        trackAddToCart(product);
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
  const handleAddToCart = () => {
    addToCart({ id: product?.id, quantity });
  };

  // Buy now mutation
  const { mutate: buyNow, isPending: isBuying } = useMutation({
    mutationKey: ["buy_now", userId],
    mutationFn: async ({
      id,
      quantity = 1,
    }: {
      id: number;
      quantity: number;
    }) => {
      const { data } = await api.post(`/carts/add`, {
        id,
        variant: generatedSelectedVariant ? generatedSelectedVariant : "",
        user_id: userId,
        quantity,
      });
      return data as CartResponseType;
    },
    onSuccess: (data: any) => {
      if (data.result) {
        trackAddToCart(product);
        router.push("/checkout");
      } else {
        toast.error(data.message);
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

  // Buy now handler with checking is user is logged in
  const handleBuyNow = () => {
    buyNow({ id: product?.id, quantity });
  };

  // Add to wishlist mutation
  const { mutate: addToWishlist } = useMutation({
    mutationKey: ["add_to_wishlist", userId],
    mutationFn: async ({ id }: { id: number }) => {
      const { data } = await api.post(`/wishlists`, {
        product_id: id,
        user_id: userId,
      });
      return data as WishlistResponseType;
    },
    onSuccess: (data: any) => {
      toast.success(
        data.message || "Product is successfully added to your wishlist",
      );
      queryClient.invalidateQueries({
        queryKey: ["is_wish_listed", product?.id, userId],
      });

      queryClient.invalidateQueries({
        queryKey: ["wishlist", userId],
      });
    },
    onError: (error: any) => {
      toast.error(error.message || "Something went wrong");
    },
  });

  // check if its wishlist mutation
  const { data: isWishListed } = useQuery({
    queryKey: ["is_wish_listed", product?.id, userId],
    enabled: !!product?.id && (!!user?.id || !!guestId),
    queryFn: async () => {
      const { data } = await api.get(
        `/wishlists-check-product?product_id=${product?.id}&user_id=${userId}`,
      );

      return data as isWishlistResponseType;
    },
  });

  // Remove from wishlist mutation
  const { mutate: removeFromWishlist } = useMutation({
    mutationKey: ["remove_from_wishlist", product?.id, userId],
    mutationFn: async ({ id }: { id: number }) => {
      const { data } = await api.delete(`/wishlists/${id}`, {
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
      });
      return data as WishlistResponseType;
    },
    onSuccess: (data: any) => {
      toast.success(
        data.message || "Product is successfully removed from your wishlist",
      );
      queryClient.invalidateQueries({
        queryKey: ["is_wish_listed", product?.id, userId],
      });

      queryClient.invalidateQueries({
        queryKey: ["wishlist", userId],
      });
    },
    onError: (error: any) => {
      toast.error(error.message || "Something went wrong");
    },
  });

  // Wishlist handler with checking is user is logged in
  const handleAddToWishlist = () => {
    if (isWishListed?.is_in_wishlist) {
      removeFromWishlist({ id: isWishListed?.wishlist_id });
    } else {
      addToWishlist({ id: product?.id });
    }
  };
  //   truncate text
  function truncateWords(text: string, keep: number = 4) {
    if (!text) return "";
    const words = text.trim().split(/\s+/);
    return words.length <= keep ? text : words.slice(0, keep).join(" ") + "...";
  }

  const hasFlashDeal = !!product?.flash_deal?.data?.end_date;

  const endDate = useMemo(() => {
    if (!hasFlashDeal) return undefined;
    return new Date(product.flash_deal.data.end_date * 1000);
  }, [hasFlashDeal, product?.flash_deal?.data?.end_date]);

  const { timeLeft, isExpired } = useCountdown(endDate);
  return (
    <section className="py-4 md:py-8">
      <Container>
        <div className="flex flex-col lg:flex-row">
          <div
            className={cn(
              "w-full transition-all duration-500 lg:sticky lg:w-[560px] lg:flex-shrink-0 lg:self-start",
              showHeader ? "top-[140px]" : "top-10",
            )}
          >
            <BodyText
              variant="one"
              className="mb-4 flex items-center gap-1 truncate text-base"
            >
              Home
              {product?.category?.name && (
                <>
                  <FaAngleRight />
                  {product?.category?.name}
                </>
              )}
              <FaAngleRight />
              {truncateWords(product?.name, 4)}
            </BodyText>
            {/* product gallery */}
            <ProductGallery
              images={images}
              productTitle={product?.name}
              productDescription={product?.short_description}
              handleAddToWishlist={handleAddToWishlist}
              isProductWishListed={isWishListed?.is_in_wishlist!}
            />
          </div>

          <div className="mt-6 w-full lg:mt-0 lg:flex-1 lg:pl-[109px]">
            {/* Brand */}
            <div className="mb-4 flex items-center gap-2">
              {product?.brand && (
                <Link
                  prefetch={false}
                  href={`/brand/${product.brand.slug}`}
                  className="text-site-primary-600 text-sm font-semibold uppercase"
                >
                  {product?.brand?.name}
                </Link>
              )}
              |
              {product?.category && (
                <Link
                  prefetch={false}
                  href={`/category/${product?.category?.slug}`}
                  className="text-sm font-semibold uppercase"
                >
                  {product?.category?.name}
                </Link>
              )}
            </div>
            {/* Product Title */}
            {product?.name && (
              <h1 className="text-site-gray-900 mb-4 text-2xl font-bold sm:text-3xl md:text-4xl lg:text-5xl xl:text-[40px]">
                {product?.name}
              </h1>
            )}
            {/* Rating */}
            <div className="mb-6 flex items-center gap-3">
              <div className="bg-site-gray-50 border-site-gray-100 flex items-center gap-[5px] rounded-full border px-4 py-1">
                <FaStar className="text-[#FF9017]" />
                <span className="text-site-gray-600 font-bold">
                  {Number(product?.rating) > 0 ? product?.rating : "0.0"}
                </span>{" "}
                |
                <Link
                  href="#review_section"
                  className="text-site-gray-600 font-medium"
                >
                  <span className="text-site-gray-600 mr-1 font-bold">
                    {product?.rating_count}
                  </span>
                  Rating & Reviews
                </Link>
              </div>
            </div>

            {/* Flash sell animated box */}
            <div className="relative overflow-clip rounded-[10px] p-1">
              {/* <div
                className="absolute top-1/2 left-1/2 -z-1 h-[1000px] w-[1000px] -translate-x-1/2 -translate-y-1/2 animate-[spin_4s_linear_infinite] rounded-[10px]"
                style={{
                  background:
                    "conic-gradient(from 0deg, #FF77BF, #FFEC82, #D26EFF, #FF77BF)",
                }}
              /> */}
              <div className="overflow-clip rounded-[10px]">
                {/* Flash Deal */}
                {/* {hasFlashDeal && !isExpired && (
                  <div className="relative flex flex-wrap items-center justify-center gap-5 bg-gradient-to-r from-[#E91A29] to-[#A229FA] p-6 md:justify-between md:p-10">
                    <Image
                      src="/images/flash-sale.gif"
                      alt="power"
                      height={150}
                      width={150}
                      loading="lazy"
                      className="absolute top-0 left-0"
                    />

                    <Image
                      src="/images/flash-sale.gif"
                      alt="power"
                      height={150}
                      width={150}
                      loading="lazy"
                      className="absolute top-0 right-0 md:right-12"
                    />

                    <div className="flex items-center gap-3">
                      <Image
                        src="/images/power.png"
                        alt="power"
                        height={38}
                        width={38}
                        loading="lazy"
                      />
                      <div className="">
                        <p className="text-xl font-bold text-white uppercase md:text-[26px]">
                          Flash Sale
                        </p>
                        <p className="text-base font-normal text-white/80">
                          Only{" "}
                          <span className="font-extrabold text-white">
                            {product.flash_deal.data.quantity}
                          </span>{" "}
                          left at this price!
                        </p>
                      </div>
                    </div>

                    <div className="flex items-center gap-4">
                      <div className="flex flex-col items-center space-y-1">
                        <span className="text-site-gray-900 grid h-11 w-11 place-content-center rounded-[6px] border border-white bg-white/90 text-center text-base font-bold">
                          {timeLeft.days}
                        </span>
                        <span className="text-xs font-normal text-white/80 uppercase">
                          Days
                        </span>
                      </div>
                      <div className="flex flex-col items-center space-y-1">
                        <span className="text-site-gray-900 grid h-11 w-11 place-content-center rounded-[6px] border border-white bg-white/90 text-center text-base font-bold">
                          {timeLeft.hours}
                        </span>
                        <span className="text-xs font-normal text-white/80 uppercase">
                          hours
                        </span>
                      </div>

                      <div className="flex flex-col items-center space-y-1">
                        <span className="text-site-gray-900 grid h-11 w-11 place-content-center rounded-[6px] border border-white bg-white/90 text-center text-base font-bold">
                          {timeLeft.minutes}
                        </span>
                        <span className="text-xs font-normal text-white/80 uppercase">
                          min
                        </span>
                      </div>
                      <div className="flex flex-col items-center space-y-1">
                        <span className="text-site-gray-900 grid h-11 w-11 place-content-center rounded-[6px] border border-white bg-white/90 text-center text-base font-bold">
                          {timeLeft.seconds}
                        </span>
                        <span className="text-xs font-normal text-white/80 uppercase">
                          sec
                        </span>
                      </div>
                    </div>
                  </div>
                )} */}

                {/* Price section */}
                <div className="z-10 flex flex-wrap items-center gap-2.5 bg-white pb-6 md:pb-10">
                  <div className="flex items-center gap-2.5">
                    <span className="text-site-primary-600 mr-2 text-xl font-semibold tracking-tighter md:text-2xl md:text-[32px]">
                      {product?.main_price}
                    </span>
                    {product?.has_discount && (
                      <>
                        <span className="text-site-gray-400 mr-3 text-lg font-medium line-through md:text-[22px]">
                          {product?.stroked_price}
                        </span>
                        <div className="rounded-[8px] bg-[#E7F5E8] px-[13px] py-1 text-xs text-[#0F9918] md:py-[7px] md:text-sm">
                          Save
                          <span className="mx-0.5 font-bold">
                            ৳
                            {product.calculable_price
                              ? (
                                  parseFloat(
                                    product.stroked_price.replace(
                                      /[^\d.]/g,
                                      "",
                                    ),
                                  ) - product.calculable_price
                                ).toFixed(2)
                              : (
                                  parseFloat(
                                    product.stroked_price.replace(
                                      /[^\d.]/g,
                                      "",
                                    ),
                                  ) -
                                  parseFloat(
                                    product.main_price.replace(/[^\d.]/g, ""),
                                  )
                                ).toFixed(2)}
                          </span>
                        </div>
                      </>
                    )}
                  </div>
                </div>
              </div>
            </div>
            <hr className="bg-site-gray-100 mb-6 h-[1px] w-full md:mb-10" />

            <VariantSelector
              choiceOption={product?.choice_options}
              colorOptions={product?.color_options}
              onVariantChange={handleVariantChange}
            />
            {/* Quantity and Add to Cart */}
            <div className="bottom-0 left-0 z-30 flex w-full items-center gap-2.5 max-md:fixed max-md:bg-white max-md:shadow-[0px_1px_15px_10px_#00000010] md:mb-8 md:gap-4">
              {/* Quantity Selector */}
              <div className="bg-site-secondary-50 flex items-center rounded-full p-1.5 md:p-[9px]">
                <button
                  onClick={() => handleQuantityChange(quantity - 1)}
                  className="grid h-[34px] w-[34px] cursor-pointer place-items-center rounded-full bg-white transition-colors"
                  disabled={quantity <= 1}
                >
                  <Minus
                    strokeWidth={2}
                    className="text-site-gray-600"
                    size={18}
                  />
                </button>
                <span className="min-w-[40px] text-center font-medium text-gray-900">
                  {quantity}
                </span>
                <button
                  onClick={() => handleQuantityChange(quantity + 1)}
                  className="grid h-[34px] w-[34px] cursor-pointer place-items-center rounded-full bg-white transition-colors"
                  disabled={product?.current_stock == quantity}
                >
                  <Plus
                    strokeWidth={2}
                    className="text-site-gray-600"
                    size={18}
                  />
                </button>
              </div>
              {/* Buy Now Button */}
              <button
                onClick={handleBuyNow}
                disabled={!product?.current_stock || isBuying}
                className={cn(
                  "flex flex-1 cursor-pointer items-center justify-center gap-1 rounded-full border-2 py-2 text-sm font-bold transition-colors md:px-6 md:py-3 md:text-base",
                  "border-site-secondary-500 bg-site-gray-50 text-site-secondary-500 hover:bg-site-secondary-50",
                  "disabled:cursor-not-allowed disabled:opacity-50",
                  !product.current_stock && "hidden",
                )}
              >
                {isBuying && <LoaderCircle className="animate-spin" />}
                {isBuying ? "Buying..." : "Buy Now"}
              </button>

              {/* Add to Cart Button */}
              <button
                disabled={isPending || product?.current_stock <= 0}
                onClick={handleAddToCart}
                className={cn(
                  "border-site-secondary-500 mt-0 flex flex-1 cursor-pointer items-center justify-center gap-1 rounded-full border py-2 pl-3 text-sm font-bold text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50 md:px-6 md:py-3 md:text-base",
                  product?.current_stock > 0
                    ? "bg-site-secondary-500 hover:bg-site-secondary-600"
                    : "bg-gray-800 opacity-50 hover:bg-gray-900",
                  isPending && "cursor-not-allowed",
                )}
              >
                {product?.current_stock > 0 ? (
                  <>
                    {isPending && <LoaderCircle className="animate-spin" />}
                    {isPending ? "Adding..." : "Add to Bag"}
                  </>
                ) : (
                  "Stock out"
                )}
              </button>
            </div>

            {/* Description */}
            {product?.short_description && (
              <div className="mb-8">
                <div className="leading-relaxed text-gray-600">
                  {/* short description */}
                  <h2 className="text-site-gray-800 mb-3 text-base font-bold md:text-[23px]">
                    Short Description
                  </h2>
                  <div className="relative">
                    <div
                      className={`short-description text-lg leading-relaxed font-normal text-gray-600 ${
                        expanded ? "line-clamp-none" : "line-clamp-3"
                      }`}
                      dangerouslySetInnerHTML={{
                        __html: product?.short_description,
                      }}
                    />
                    <button
                      onClick={() => setExpanded(!expanded)}
                      className="text-site-primary-600 inline cursor-pointer font-medium underline transition-colors hover:text-gray-700"
                    >
                      {expanded ? "See less" : "See more"}
                    </button>
                  </div>
                </div>
              </div>
            )}

            {/* Product Information */}
            <div className="mb-6 md:mb-10">
              <ProductInfo product={product} />
            </div>
            <ProductDescriptionTabs product={product} />
          </div>
        </div>
      </Container>
    </section>
  );
};

export default DetailsSection;
