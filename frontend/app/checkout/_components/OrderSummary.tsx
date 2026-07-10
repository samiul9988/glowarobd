"use client";

import Image from "next/image";
import Link from "next/link";
import { Card, CardContent } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { formatMoney } from "@/lib/formatMoney";
import { useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import toast from "react-hot-toast";
import { httpClient } from "@/lib/http-client";

import { CartWithDelivery } from "@/lib/api/checkout";
import Heading from "@/components/Heading";
import BodyText from "@/components/BodyText";
import UserAssignCoupon from "./UserAssignCoupon";
import { useAssignedCoupons } from "@/hooks/queries/useCheckout";
import { useToken } from "@/store/useTokenStore";
import { currencySymbol } from "@/config/apiConfig";
import CustomImage from "@/components/cards/CustomImage";

interface shipiingDiscont {
  status: boolean;
  amount: string | number;
  min_amount: string;
}
interface cartSummaryProps {
  coupon_applied: boolean;
  coupon_code: null;
  discount: string;
  grand_total: string;
  grand_total_value: number;
  shipping_cost: string;
  shipping_discount: shipiingDiscont;
  calculable_sub_total: string;
  sub_total: string;
  tax: string;
}
interface OrderSummaryProps {
  cartProducts: any[];
  cartWithDeliveryData?: CartWithDelivery[];
  isLoading?: boolean;
  cartSummary: cartSummaryProps;
  userId: number | string;
}

// Coupon API functions
const couponApi = {
  applyCoupon: async (data: {
    user_id: number | string;
    owner_id: number;
    coupon_code: string;
  }) => {
    const response = await httpClient.post("/coupon-apply", data);
    return response.data;
  },

  removeCoupon: async (data: {
    user_id: number | string;
    owner_id: number;
  }) => {
    const response = await httpClient.post("/coupon-remove", data);
    return response.data;
  },
};

export default function OrderSummary({
  cartProducts,
  cartWithDeliveryData,
  isLoading = false,
  cartSummary,
  userId,
}: OrderSummaryProps) {
  const imageBaseUrl = process.env.NEXT_PUBLIC_IMG_HOST_URL || "";
  const queryClient = useQueryClient();
  // Coupon state
  const [couponCode, setCouponCode] = useState("");
  const [couponError, setCouponError] = useState("");
  const [appliedCoupon, setAppliedCoupon] = useState<string | null>(
    cartSummary?.coupon_applied ? cartSummary?.coupon_code : null,
  );
  const [applyingCouponCode, setApplyingCouponCode] = useState<string | null>(
    null,
  );
  const { accessToken } = useToken();
  // Coupon mutations
  const applyCouponMutation = useMutation({
    mutationFn: couponApi.applyCoupon,
    onSuccess: (data) => {
      if (data.result === true) {
        toast.success(data.message || "Coupon applied successfully!");
        // Set applied coupon from the mutation data or from couponCode state
        const appliedCode = data.coupon_code || couponCode;
        setAppliedCoupon(appliedCode);
        setCouponCode("");
        setCouponError("");
        // Invalidate cart queries to refresh data
        queryClient.invalidateQueries({ queryKey: ["checkout"] });
      } else {
        toast.error(data.message || "Failed to apply coupon");
        setCouponError(data.message || "Failed to apply coupon");
      }
      setApplyingCouponCode(null);
    },
    onError: (error: any) => {
      setCouponError(error.response?.data?.message || "Failed to apply coupon");
      setApplyingCouponCode(null);
    },
  });

  const removeCouponMutation = useMutation({
    mutationFn: couponApi.removeCoupon,
    onSuccess: (data) => {
      if (data.result === true) {
        toast.success(data.message || "Coupon removed successfully!");
        setAppliedCoupon(null);
        // Invalidate cart queries to refresh data
        queryClient.invalidateQueries({ queryKey: ["checkout"] });
      } else {
        setCouponError(data.message || "Failed to remove coupon");
      }
    },
    onError: (error: any) => {
      setCouponError(
        error.response?.data?.message || "Failed to remove coupon",
      );
    },
  });

  // Handle coupon apply
  const handleApplyCoupon = () => {
    if (!couponCode.trim()) {
      setCouponError("Please enter a coupon code");
      return;
    }

    applyCouponMutation.mutate({
      user_id: userId,
      owner_id: 1,
      coupon_code: couponCode.trim(),
    });
  };

  // Handle coupon remove
  const handleRemoveCoupon = () => {
    removeCouponMutation.mutate({
      user_id: userId,
      owner_id: 1,
    });
  };

  // get user assign coupon
  const { data: assignCouponData } = useAssignedCoupons(userId, accessToken!);

  const isApplyingCoupon = applyCouponMutation.isPending;
  const isRemovingCoupon = removeCouponMutation.isPending;

  // Handle coupon apply from coupon list
  const handleApplyCouponFromList = (code: string) => {
    setCouponError(""); // Clear any previous errors
    setCouponCode(code); // Set the coupon code so it can be used in onSuccess
    setApplyingCouponCode(code); // Track which coupon is being applied
    applyCouponMutation.mutate({
      user_id: userId,
      owner_id: 1,
      coupon_code: code,
    });
  };

  // Use cart data from API if available, otherwise use local cart
  const displayCartItems =
    cartWithDeliveryData?.[0]?.cart_items || cartProducts;

  if (displayCartItems.length === 0) {
    return (
      <Card>
        <CardContent className="p-6 text-center">
          <p className="mb-4 text-gray-500">Your cart is empty</p>
          <Link
            href="/categories"
            className="ring-offset-background focus-visible:ring-ring bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-10 items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
          >
            Continue Shopping
          </Link>
        </CardContent>
      </Card>
    );
  }
  // get total saved
  const totalSaved = displayCartItems.reduce((acc, item) => {
    const savedPerItem = (item.base_price - item.price) * item.quantity;
    return acc + savedPerItem;
  }, 0);

  let remainingAmount = 0;

  const isEnabledShippingDiscount =
    cartSummary?.shipping_discount?.status == true;

  if (isEnabledShippingDiscount) {
    const minAmount =
      parseFloat(cartSummary?.shipping_discount?.min_amount) ?? 0;
    const calculableSubTotal =
      parseFloat(cartSummary.calculable_sub_total.replace(",", "")) ?? 0;
    remainingAmount = minAmount - calculableSubTotal;
  }

  return (
    <div>
      {userId && !userId.toString().startsWith("tmp") && (
        <UserAssignCoupon
          userId={userId}
          assignCouponData={assignCouponData}
          onApplyCoupon={handleApplyCouponFromList}
          appliedCoupon={appliedCoupon}
          applyingCouponCode={applyingCouponCode}
        />
      )}
      {isEnabledShippingDiscount && (
        <>
          {remainingAmount <= 0 ? (
            <div className="mb-2 rounded-sm bg-green-100/60 py-3 text-center text-sm text-green-600 capitalize">
              You Are Enjoying <span className="font-bold">free delivery</span>
              !!
            </div>
          ) : (
            <div className="mb-2 rounded-sm bg-green-100/60 py-3 text-center text-sm text-green-600 capitalize">
              Buy More{" "}
              <span className="font-bold">৳{remainingAmount.toFixed(2)} </span>
              To Get <span className="font-bold">free delivery</span> !!
            </div>
          )}
        </>
      )}
      <div className="sticky top-0 !bg-[linear-gradient(180deg,#EFE6F8_0%,#FAF5FF_100%)]  rounded-2xl md:rounded-bl-none md:rounded-br-none   p-3 md:rounded-tl-2xl md:rounded-tr-2xl md:border-b-0 md:p-6">
        <Card className="!bg-transparent rounded-none border-0 p-0 shadow-none">
          <Heading variant="h5" className="font-bold mb-8 text-lg ">
            Order Summary
          </Heading>
          <CardContent className="space-y-4 p-0">
            {/* Cart Items */}
            {/* <div className="space-y-3">
              {displayCartItems.map((item, index) => (
                <div key={item.id || index} className="flex items-center gap-2">
                  <div className="relative">
                    <CustomImage
                      src={`${imageBaseUrl}${item.product_thumbnail_image}`}
                      alt={item.product_name}
                      width={60}
                      height={60}
                      className="rounded-md object-cover"
                    />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <div className="flex max-w-full flex-col overflow-hidden">
                        <div className="inline">
                          <BodyText
                            variant="two"
                            className="text-site-gray-900 relative text-xs"
                          >
                            <span className="line-clamp-2">
                              {item.product_name}{" "}
                            </span>
                          </BodyText>
                        </div>

                        <div className="mt-1 flex items-center gap-1.5 lg:gap-2">
                          <span className="text-site-gray-400 rounded-full text-xs">
                            {item.quantity} x
                          </span>
                          <span className="text-xs font-semibold text-gray-900">
                            {currencySymbol}
                            {item.price}
                          </span>

                          {item.base_price > item.price && (
                            <span className="text-xs text-gray-500 line-through">
                              {currencySymbol}
                              {item.base_price}
                            </span>
                          )}

                          {item.base_price > item.price && (
                            <span className="rounded-md bg-green-100/60 px-2 py-1 text-[9px] text-green-700">
                              Saved {currencySymbol}
                              {(item.base_price - item.price).toFixed(2)}
                            </span>
                          )}
                        </div>
                      </div>
                    </div>
                    {item.variation && (
                      <p className="text-xs text-gray-500">
                        {typeof item.variation === "string" && item.variation}
                      </p>
                    )}
                  </div>
                  <div className="text-sm font-medium">
                    ৳{formatMoney(item.price * item.quantity)}
                  </div>
                </div>
              ))}
            </div> */}
            {userId && (
              <div className="my-8">
                <BodyText
                  className="text-site-gray-900 font-semibold"
                  variant="one"
                >
                  Have Coupon / Voucher?
                </BodyText>

                {/* Show applied coupon */}
                {appliedCoupon && (
                  <>
                    <div className="mt-3 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3">
                      <div className="flex items-center gap-2">
                        <svg
                          className="h-5 w-5 text-green-600"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M5 13l4 4L19 7"
                          />
                        </svg>
                        <span className="font-medium text-green-700">
                          Coupon "{appliedCoupon}" applied
                        </span>
                      </div>
                      <button
                        onClick={handleRemoveCoupon}
                        disabled={isRemovingCoupon}
                        className="text-sm font-medium text-red-600 hover:text-red-800 disabled:opacity-50"
                      >
                        {isRemovingCoupon ? "Removing..." : "Remove"}
                      </button>
                    </div>
                    {couponError && (
                      <BodyText variant="three" className="mt-1 text-[#EE3434]">
                        {couponError}
                      </BodyText>
                    )}
                  </>
                )}

                {/* Coupon input - only show if no coupon is applied */}
                {!appliedCoupon && (
                  <>
                    <div className="relative mt-4 flex items-center">
                      <input
                        value={couponCode}
                        onChange={(e) => setCouponCode(e.target.value)}
                        onKeyPress={(e) => {
                          if (e.key === "Enter") {
                            handleApplyCoupon();
                          }
                        }}
                        className="bg-white flex-1 rounded-full px-5 py-2 focus:shadow-none focus:outline-0"
                        placeholder="Enter coupon code"
                        disabled={isApplyingCoupon}
                      />
                      <button
                        onClick={handleApplyCoupon}
                        disabled={isApplyingCoupon || !couponCode.trim()}
                        className="bg-site-primary-100 border border-site-primary-600 hover:text-white hover:bg-site-primary-600 absolute right-0 h-full w-[99px] cursor-pointer rounded-full p-2 text-site-primary-600 transition duration-300 ease-in disabled:cursor-not-allowed disabled:opacity-50"
                      >
                        {isApplyingCoupon ? "Applying..." : "Apply"}
                      </button>
                    </div>
                    {couponError && (
                      <BodyText variant="three" className="mt-1 text-[#EE3434]">
                        {couponError}
                      </BodyText>
                    )}
                  </>
                )}
              </div>
            )}

            {/* Summary Details */}
            {isLoading ? (
              <div className="space-y-3">
                {[1, 2, 3, 4, 5].map((i) => (
                  <div
                    key={i}
                    className="h-4 animate-pulse rounded bg-gray-200"
                  />
                ))}
                <hr />
                <div className="h-4 animate-pulse rounded bg-gray-200" />
              </div>
            ) : (
              <div className="space-y-3">
                <div className="flex justify-between text-sm">
                  <BodyText
                    variant="two"
                    className="text-site-gray-600 capitalize"
                  >
                    Subtotal
                  </BodyText>
                  <BodyText
                    variant="one"
                    className="text-site-gray-900 text-sm font-medium capitalize"
                  >
                    {cartSummary?.sub_total}
                  </BodyText>
                </div>

                {/* <div className="flex justify-between text-sm">
                  <BodyText
                    variant="two"
                    className="text-site-gray-600 capitalize"
                  >
                    Tax
                  </BodyText>
                  <BodyText
                    variant="one"
                    className="text-site-gray-900 text-sm font-medium capitalize"
                  >
                    {cartSummary?.tax}
                  </BodyText>
                </div> */}

                <div className="flex justify-between text-sm">
                  <BodyText
                    variant="two"
                    className="text-site-gray-600 capitalize"
                  >
                    Delivery
                  </BodyText>
                  <BodyText
                    variant="one"
                    className="text-site-gray-900 text-sm font-medium capitalize"
                  >
                    {cartSummary?.shipping_cost === "৳0.00" ? (
                      <span className="font-bold text-green-500">free</span>
                    ) : (
                      cartSummary?.shipping_cost
                    )}
                  </BodyText>
                </div>

                {cartSummary?.discount && (
                  <div className="flex justify-between text-sm">
                    <BodyText variant="two" className="capitalize">
                      Coupon Discount {appliedCoupon && `(${appliedCoupon})`}
                    </BodyText>
                    <BodyText
                      variant="one"
                      className="text-site-gray-900 text-sm font-medium capitalize"
                    >
                      {cartSummary?.discount}
                    </BodyText>
                  </div>
                )}
                {/* {totalSaved > 0 && (
                  <div className="item-center 0 flex justify-between rounded-sm p-1 text-sm">
                    <BodyText variant="two" className="capitalize">
                      Your Total Savings for This Order
                    </BodyText>
                    <BodyText
                      variant="one"
                      className="text-sm font-bold !text-green-500 capitalize"
                    >
                      {currencySymbol}
                      {totalSaved.toFixed(2)}
                    </BodyText>
                  </div>
                )} */}
                <Separator />
                <div className="flex justify-between font-semibold">
                  <BodyText
                    variant="one"
                    className="text-site-gray-900 font-semibold capitalize"
                  >
                    Total
                  </BodyText>
                  <BodyText
                    variant="one"
                    className="text-site-gray-900 font-semibold capitalize"
                  >
                    {cartSummary?.grand_total}
                  </BodyText>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
