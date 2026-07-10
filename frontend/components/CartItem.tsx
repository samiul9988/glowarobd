"use client";

import { imageBaseHostUrl } from "@/config/apiConfig";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { Minus, Plus, X } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import toast from "react-hot-toast";

const CartItem = (product: CartItemType) => {
  const queryClient = useQueryClient();
  const { accessToken } = useToken();
  const { user } = useSession();
  const { guestId } = useGuestUserId();

  // Delete from cart
  const { mutate: removeFromCart } = useMutation({
    mutationKey: ["delete_from_cart", user?.id ? user.id : guestId],
    mutationFn: async (id: number) => {
      const { data } = await api.delete(`/carts/${id}`, {});
      return data as CartResponseType;
    },

    onSuccess: (data) => {
      if (data.result) {
        toast.success(data.message);
      } else {
        toast.error(data.message);
      }

      queryClient.invalidateQueries({
        queryKey: ["get_cart", user?.id ? user.id : guestId],
      });
      queryClient.invalidateQueries({
        queryKey: ["get_subtotal", user?.id ? user.id : guestId],
      });
    },
  });

  // Inc & Dec cart item
  const { mutate } = useMutation({
    mutationKey: ["incdec_cart_item", user?.id ? user.id : guestId],
    mutationFn: async ({
      cart_ids,
      cart_quantities,
    }: {
      cart_ids: number;
      cart_quantities: number;
    }) => {
      const { data } = await api.post(`/carts/process`, {
        cart_ids,
        cart_quantities,
      });
      return data as CartResponseType;
    },

    onSuccess: (data) => {
      if (data.result === false) {
        toast.error(data.message);
      } else {
        toast.success(data.message);
      }

      queryClient.invalidateQueries({
        queryKey: ["get_cart", user?.id ? user.id : guestId],
      });
      queryClient.invalidateQueries({
        queryKey: ["get_subtotal", user?.id ? user.id : guestId],
      });
    },
  });

  // Increment cart item
  const incrementCartItem = (id: number) => {
    mutate({ cart_ids: id, cart_quantities: product.quantity + 1 });
  };

  // Decrement cart item
  const decrementCartItem = (id: number) => {
    mutate({ cart_ids: id, cart_quantities: product.quantity - 1 });
  };

  return (
    <div className="flex items-center gap-2 py-4 md:gap-4">
      {/* Remove Button */}
      <button
        onClick={() => removeFromCart(product.id)}
        className="border-site-gray-600 text-site-gray-400 mt-1 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-2 transition-colors hover:border-red-500 hover:text-red-500 sm:mt-0"
      >
        {/* <RxCross2 stroke="2" size={16}/> */}
        <X strokeWidth={2.5} size={12} />
      </button>
      <Image
        src={imageBaseHostUrl + product.product_thumbnail_image}
        alt={product.product_name}
        height={0}
        width={0}
        className="h-[60px] w-[60px] rounded-[6px] object-cover md:h-[74px] md:w-[74px]"
        onError={(e) => {
          const target = e.target as HTMLImageElement;
          target.src = "/images/placeholder.png";
        }}
      />
      <div className="flex-1 space-y-2">
        <div className="mb-2 flex flex-col justify-between gap-1">
          <Link
            href={`/product/${product?.product_slug}`}
            className="text-site-gray-900 line-clamp-2 text-sm"
          >
            {product.product_name}
          </Link>
          {product.variation && (
            <p className="text-xs text-gray-600">
              Variant: {product.variation}
            </p>
          )}
        </div>

        <div className="flex items-center justify-between gap-1">
          {/* <span className="text-site-gray-300 px-0.5 text-sm md:px-1">X</span> */}
          <p className="text-site-gray-900 text-base font-semibold">
            ৳{product.price}
          </p>
          <div className="bg-site-primary-50 flex items-center rounded-full p-1.5">
            <div className="flex items-center">
              <button
                className="text-site-gray-600 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-white transition-colors"
                onClick={() => decrementCartItem(product.id)}
              >
                <Minus
                  strokeWidth={2}
                  className="text-site-gray-800"
                  size={18}
                />
              </button>

              <span className="text-site-gray-600 w-8 text-center text-sm font-bold md:w-10">
                {product.quantity}
              </span>

              <button
                className="text-site-gray-800 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-white transition-colors"
                onClick={() => incrementCartItem(product.id)}
              >
                <Plus
                  strokeWidth={2}
                  className="text-site-gray-800"
                  size={18}
                />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CartItem;
