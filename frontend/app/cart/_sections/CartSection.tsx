"use client";

import Container from "@/components/Container";
import PrimaryButton from "@/components/buttons/PrimaryButton";
import CartSkeleton from "@/components/skeleton/CartSkeleton";
import ViewCartSummarySkeleton from "@/components/skeleton/ViewCartSummarySkeleton";
import { currencySymbol, imageBaseHostUrl } from "@/config/apiConfig";
import { checkoutKeys } from "@/hooks/queries/useCheckout";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { Cross, CrossIcon, Minus, Plus, ShoppingCart, X } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import toast from "react-hot-toast";
import { RxCross2 } from "react-icons/rx";

const CartSection = () => {
  const { user } = useSession();
  const { accessToken } = useToken();
  const queryClient = useQueryClient();
  const { guestId } = useGuestUserId();
  const userId = user?.id ? user?.id : guestId;

  // Get cart items
  const { data: cartItems, isLoading: isCartLoading } = useQuery({
    queryKey: ["get_cart", userId],
    queryFn: async () => {
      const { data } = await api.post(`/carts/${userId}`, {});
      return data as Cart[];
    },
    enabled: !!userId,
  });

  // Get subtotal
  const { data: cartSummary, isLoading: isSummaryLoading } = useQuery({
    queryKey: ["get_subtotal", userId],
    queryFn: async () => {
      const { data } = await api.get(`/cart-summary/${userId}`, {
        headers: { Authorization: `Bearer ${accessToken}` },
      });
      return data as CartSummary;
    },
    enabled: !!userId,
  });

  // Calculate total quantity count
  const cart = (cartItems && cartItems[0]?.cart_items) || [];
  const total = cart.reduce((acc, item) => acc + item.quantity, 0);

  // Delete from cart
  const { mutate: removeFromCart } = useMutation({
    mutationKey: ["delete_from_cart", userId],
    mutationFn: async (id: number) => {
      const { data } = await api.delete(`/carts/${id}`, {
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
      });
      return data as CartResponseType;
    },

    onSuccess: (data) => {
      if (data.result) {
        toast.success(data.message);
      } else {
        toast.error(data.message);
      }

      queryClient.invalidateQueries({ queryKey: ["get_cart", userId] });
      queryClient.invalidateQueries({
        queryKey: [...checkoutKeys.all, "get_cart", userId] as const,
      });
      queryClient.invalidateQueries({ queryKey: ["get_subtotal", userId] });
    },
  });

  // Inc & Dec cart item
  const { mutate } = useMutation({
    mutationKey: ["incdec_cart_item", userId],
    mutationFn: async ({
      cart_ids,
      cart_quantities,
    }: {
      cart_ids: number;
      cart_quantities: number;
    }) => {
      const { data } = await api.post(
        `/carts/process`,
        {
          cart_ids,
          cart_quantities,
        },
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        },
      );
      return data as CartResponseType;
    },

    onSuccess: (data) => {
      if (data.result === false) {
        toast.error(data.message);
      } else {
        toast.success(data.message);
      }

      queryClient.invalidateQueries({ queryKey: ["get_cart", userId] });
      queryClient.invalidateQueries({
        queryKey: ["get_subtotal", userId],
      });
    },
  });

  //   // Increment cart item
  const incrementCartItem = (id: number) => {
    const product = cart.find((item) => item.id === id);

    if (product) {
      mutate({ cart_ids: id, cart_quantities: product.quantity + 1 });
    }
  };

  //   // Decrement cart item
  const decrementCartItem = (id: number) => {
    const product = cart.find((item) => item.id === id);

    if (product) {
      mutate({ cart_ids: id, cart_quantities: product.quantity - 1 });
    }
  };

  return (
    <div className="pt-6 pb-16 sm:pt-6 md:pt-10 md:pb-30">
      <Container>
        <div className="flex flex-col gap-5 md:gap-[100px] lg:flex-row">
          {/* Cart Items Section */}
          <div className="flex-1 xl:flex-[2]">
            {/* <h2 className="mb-4 text-xl font-semibold tracking-wide text-gray-900 sm:mb-6 sm:text-2xl">
              Shopping Cart ({cart.length})
            </h2> */}

            <div className="space-y-4 sm:space-y-6">
              {!cartItems || isCartLoading ? (
                <>
                  <CartSkeleton />
                  <CartSkeleton />
                  <CartSkeleton />
                  <CartSkeleton />
                </>
              ) : (
                <>
                  {cart.length > 0 ? (
                    <>
                      {cart.map((product) => (
                        <div
                          key={product.id}
                          className="flex flex-row items-start gap-3 border-b border-gray-100 pb-4 last:border-b-0 sm:items-center sm:pb-6 md:gap-4"
                        >
                          {/* Remove Button */}
                          <button
                            onClick={() => removeFromCart(product.id)}
                            className="border-site-gray-400 text-site-gray-400 mt-1 hidden h-5 w-5 cursor-pointer items-center justify-center rounded-full border-2 transition-colors hover:border-red-500 hover:text-red-500 sm:mt-0 sm:flex"
                          >
                            {/* <RxCross2 stroke="2" size={16}/> */}
                            <X strokeWidth={2.5} size={12} />
                          </button>

                          {/* Product Image */}
                          <div className="flex w-full items-start justify-between">
                            <div className="flex w-full flex-col justify-between gap-2 md:flex-row lg:gap-4">
                              <div className="flex items-center gap-2 md:gap-4">
                                <div className="lex-shrink-0 h-14 w-14 overflow-hidden rounded-lg bg-gray-100 md:h-[100px] md:w-[100px]">
                                  <Image
                                    src={
                                      imageBaseHostUrl +
                                      product.product_thumbnail_image
                                    }
                                    alt={product.product_name}
                                    width={100}
                                    height={100}
                                    className="h-full w-full object-cover"
                                    onError={(e) => {
                                      const target =
                                        e.target as HTMLImageElement;
                                      target.src = "/images/placeholder.png";
                                    }}
                                  />
                                </div>
                                <div className="">
                                  <div className="flex items-center gap-2">
                                    {/* Product Details */}
                                    <div className="min-w-0 flex-1 md:mb-2">
                                      <Link
                                        href={`/product/${product.product_slug}`}
                                        className="text-site-gray-900 mb-1 !line-clamp-1 block text-sm leading-5 lg:text-base"
                                      >
                                        {product.product_name}
                                      </Link>

                                      {product.variation && (
                                        <p className="text-xs text-gray-600">
                                          Variant:{" "}
                                          <span className="text-site-gray-500 font-bold">
                                            {product.variation}
                                          </span>
                                        </p>
                                      )}
                                      <div>
                                        <div className="mt-2 flex items-center gap-2">
                                          <span className="text-xs font-semibold text-gray-800 lg:text-lg">
                                            ৳{product.price}
                                          </span>
                                          {product.base_price >
                                            product.price && (
                                            <span className="text-xs text-gray-400 line-through lg:text-lg">
                                              {currencySymbol}
                                              {product.base_price}
                                            </span>
                                          )}
                                          {product.base_price >
                                            product.price && (
                                            <div className="rounded-md bg-[#E7F5E8] px-2 py-1 text-[10px] text-[#0F9918] md:text-base">
                                              Saved{" "}
                                              <span className="font-bold">
                                                {currencySymbol}
                                                {(
                                                  product?.quantity *
                                                  (product.base_price -
                                                    product.price)
                                                ).toFixed(2)}
                                              </span>
                                            </div>
                                          )}
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              {/* Item Total */}
                              <div className="ml-[60px] flex items-center gap-8 text-right sm:mt-0 md:mt-2 md:ml-0 md:justify-between">
                                {/* Quantity Controls */}
                                <div className="flex items-center justify-between gap-2.5 lg:flex">
                                  <div className="bg-site-primary-50 flex items-center rounded-full p-1.5">
                                    <button
                                      onClick={() =>
                                        decrementCartItem(product.id)
                                      }
                                      className="text-site-gray-600 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-white transition-colors md:h-7 md:w-7"
                                      disabled={product.quantity <= 1}
                                    >
                                      <Minus
                                        strokeWidth={2}
                                        className="text-site-gray-800"
                                        size={18}
                                      />
                                    </button>

                                    <span className="text-site-gray-800 w-10 text-center text-sm font-bold">
                                      {product.quantity}
                                    </span>

                                    <button
                                      onClick={() =>
                                        incrementCartItem(product.id)
                                      }
                                      className="text-site-gray-800 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-white transition-colors md:h-7 md:w-7"
                                    >
                                      <Plus
                                        strokeWidth={2}
                                        className="text-site-gray-800"
                                        size={18}
                                      />
                                    </button>
                                  </div>
                                  {/* Item Total */}
                                  <div className="text-right sm:mt-0 md:hidden md:min-w-[80px]">
                                    <span className="text-site-primary-600 text-sm font-bold md:text-lg">
                                      {currencySymbol}
                                      {product.price * product.quantity}
                                    </span>
                                  </div>
                                </div>
                                <span className="not-visited:text-site-primary-600 hidden text-base font-bold md:text-lg lg:block">
                                  {currencySymbol}
                                  {product.price * product.quantity}
                                </span>
                              </div>
                            </div>
                            {/* Remove Button */}
                            <button
                              onClick={() => removeFromCart(product.id)}
                              className="border-site-gray-400 text-site-gray-400 mx-2 mt-1 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border-2 p-1 transition-colors hover:border-red-500 hover:text-red-500 sm:mt-0 sm:hidden"
                            >
                              <X strokeWidth={2} size={16} />
                            </button>
                          </div>
                        </div>
                      ))}
                    </>
                  ) : (
                    <div className="bg-site-secondary-50/40 border-site-gray-50 flex flex-col items-center justify-center rounded-[10px] border py-10 md:py-20 lg:py-28">
                      {/* <ShoppingCart className="h-7 w-7 md:h-12 md:w-12 text-site-gray-400" /> */}
                      {/* <span className="mb-4 block animate-bounce md:mb-6">
                        
                      </span> */}

                      <p className="text-site-gray-700 mb-[5px] text-base leading-7 font-bold md:text-lg md:leading-[26px]">
                        Your Bag is empty!
                      </p>
                      <p className="text-site-gray-400 text-sm md:text-lg">
                        Discover our premium collection of products.
                      </p>

                      <Link
                        href="/"
                        className="text-site-primary-500 border-site-primary-500 bg-site-primary-50 hover:bg-site-primary-500 mt-2 rounded-full border px-3 py-2 text-center text-sm font-semibold transition-colors hover:text-white md:mt-6"
                      >
                        Start Shopping
                      </Link>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>

          {/* Summary Section */}
          <div className="sticky top-4 w-full lg:w-[400px]">
            {!cartSummary || isSummaryLoading ? (
              <ViewCartSummarySkeleton />
            ) : (
              <div className="rounded-[10px] border bg-gradient-to-b from-[#EFE6F8] to-[#FAF5FF] px-5 py-4">
                <h2 className="text-site-gray-900 mb-3 text-xl font-semibold md:mb-6 lg:text-2xl">
                  Total Price
                </h2>
                <div className="mb-6 space-y-4 rounded-2xl">
                  <div className="flex items-center justify-between">
                    <span className="text-gray-600">Quantity</span>
                    <span className="text-site-gray-900 font-bold">
                      {total} pcs
                    </span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-gray-600">Sub-Total</span>
                    <span className="text-site-gray-900 text-base font-semibold">
                      {cartSummary?.sub_total || 0}
                    </span>
                  </div>
                </div>
                <PrimaryButton link="/checkout" className="mt-4">
                  Checkout
                </PrimaryButton>
              </div>
            )}
          </div>
        </div>
      </Container>
    </div>
  );
};

export default CartSection;
