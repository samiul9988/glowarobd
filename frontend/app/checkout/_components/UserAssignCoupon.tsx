"use client";

type CouponDetails = {
  product_id: string;
};

type CouponData = {
  code: string;
  discount: number;
  discount_type: "amount" | "percent";
  start_date: number | string;
  end_date: string;
  details: CouponDetails;
  formatted_discount: string;
  formatted_end_date: string;
};

type CouponResponse = {
  data: CouponData[];
  success: boolean;
  status: number;
};

import { Swiper, SwiperSlide } from "swiper/react";
import "swiper/css";
import Heading from "@/components/Heading";
import BodyText from "@/components/BodyText";

type Coupon = {
  id: number;
  discount: string;
  validDate: string;
};

type UserAssignCouponProps = {
  userId: string | number;
  assignCouponData: CouponResponse;
  onApplyCoupon: (code: string) => void;
  appliedCoupon: string | null;
  applyingCouponCode: string | null;
};

export default function UserAssignCoupon({
  assignCouponData,
  onApplyCoupon,
  appliedCoupon,
  applyingCouponCode,
}: UserAssignCouponProps) {
  if (assignCouponData && !assignCouponData.data.length) {
    return;
  }

  return (
    assignCouponData &&
    assignCouponData?.data?.length > 0 && (
      <div className="mb-8 md:mb-[50px]">
        <Heading variant="h5" className="mb-6 text-lg font-medium">
          Available Coupons
        </Heading>

        <Swiper
          spaceBetween={24}
          slidesPerView="auto"
          freeMode={true}
          autoplay={false}
        >
          {assignCouponData &&
            assignCouponData?.data?.length > 0 &&
            assignCouponData?.data?.map((coupon) => (
              <SwiperSlide key={coupon.code} style={{ width: "auto" }}>
                <div className="relative flex h-[130px] w-[200px] items-center overflow-hidden bg-white">
                  {/* Left Gold Strip */}
                  <div className="flex h-full w-[44px] items-center justify-center rounded-tl-xl rounded-bl-xl bg-gradient-to-b from-[#EABF7A] to-[#916A2C] pr-0 pl-4 text-xs font-semibold text-white">
                    <BodyText
                      variant="two"
                      className="rotate-180 font-semibold tracking-wider text-white [writing-mode:vertical-rl]"
                    >
                      DISCOUNT
                    </BodyText>
                  </div>

                  {/* Coupon Details */}
                  <div className="border-site-gray-100 flex h-full w-full flex-1 flex-col justify-center rounded-tr-xl rounded-br-xl border px-2 text-center transition-shadow duration-300 hover:shadow-md">
                    <div className="flex flex-col items-start justify-start px-1">
                      <BodyText className="text-xl font-bold text-[#1E2939]">
                        {coupon.formatted_discount}
                      </BodyText>
                      <p className="mt-1 text-sm text-gray-500">
                        {coupon.formatted_end_date}
                      </p>
                      <button
                        onClick={() => onApplyCoupon(coupon.code)}
                        disabled={appliedCoupon === coupon.code}
                        className={`mt-3 inline-block w-full cursor-pointer rounded-full bg-[#243752] px-2 py-1 text-sm text-white transition-colors hover:bg-[#1d2f45] disabled:cursor-not-allowed disabled:opacity-50 ${
                          appliedCoupon === coupon.code
                            ? "cursor-default bg-green-600"
                            : ""
                        }`}
                      >
                        {appliedCoupon === coupon.code
                          ? "Applied"
                          : "Apply Code"}
                      </button>
                    </div>
                  </div>

                  {/* Ticket Notch */}
                  <div className="absolute top-1/2 left-0 h-8 w-8 -translate-x-1/2 -translate-y-1/2 rounded-full border border-gray-200 bg-white"></div>
                </div>
              </SwiperSlide>
            ))}
        </Swiper>
      </div>
    )
  );
}
