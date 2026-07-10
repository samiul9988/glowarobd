"use client";

import { useEffect, useRef, useState } from "react";
import { currencySymbol, imageBaseHostUrl } from "@/config/apiConfig";
import { Info } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { IoStar } from "react-icons/io5";
import { ModalCloseIcon, ShieldCheckIcon } from "../icons/icon-library";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";

interface Props {
  testimonial: TestimonialItem;
}

const TestimonialCard = ({ testimonial }: Props) => {
  const [open, setOpen] = useState(false);
  const reviewRef = useRef<HTMLParagraphElement>(null);
  const [showSeeMore, setShowSeeMore] = useState(false);

  // Overflow detection using ResizeObserver for responsive
  useEffect(() => {
    const el = reviewRef.current;
    if (!el) return;

    const checkOverflow = () => {
      const style = window.getComputedStyle(el);
      const lineHeight = parseFloat(style.lineHeight || "0");
      const maxLines = 2;
      const maxHeight = lineHeight * maxLines;
      setShowSeeMore(el.scrollHeight > maxHeight + 1);
    };

    checkOverflow();

    const observer = new ResizeObserver(checkOverflow);
    observer.observe(el);
    window.addEventListener("resize", checkOverflow);

    return () => {
      observer.disconnect();
      window.removeEventListener("resize", checkOverflow);
    };
  }, [testimonial.comment]);

  const ReviewBody = ({ full }: { full?: boolean }) => (
    <p
      ref={full ? undefined : reviewRef}
      className={`text-site-gray-500 mb-0 min-h-12 text-sm leading-6 font-normal md:text-base ${
        full ? "" : "line-clamp-2"
      }`}
    >
      {testimonial?.comment}
    </p>
  );

  return (
    <div className="relative my-6 overflow-clip rounded-[26px] p-[3px] lg:my-0">
      <div
        className="absolute top-1/2 left-1/2 -z-1 h-[1000px] w-[1000px] -translate-x-1/2 -translate-y-1/2 animate-[spin_10s_linear_infinite] rounded-[10px] blur-2xl"
        style={{
          background:
            "conic-gradient(rgb(97, 218, 251) 20deg, transparent 120deg)",
        }}
      />
      <div className="bg-site-gray-50 overflow-clip rounded-[26px] p-3 lg:p-4">
        <div className="flex flex-col justify-between space-y-3 md:space-y-6">
          <div className="mb-2">
            {/* Header */}
            <div className="flex items-center gap-3">
              <h4 className="text-site-gray-900 text-lg font-normal capitalize md:text-[23px]">
                {testimonial?.user_name ??
                  testimonial?.user?.name ??
                  "anonymous"}
              </h4>
              <div className="flex items-center gap-1">
                <ShieldCheckIcon size={20} />
                <span className="text-site-primary-500 text-xs font-semibold md:text-sm">
                  Verified
                </span>
              </div>
            </div>

            {/* Stars + Comment */}
            <div className="space-y-2">
              <div className="flex items-center gap-0.5">
                {[1, 2, 3, 4, 5].map((star) => (
                  <IoStar
                    key={star}
                    className={
                      star <= (testimonial?.rating || 0)
                        ? "text-[#FF8800]"
                        : "text-site-gray-200"
                    }
                    size={16}
                  />
                ))}
              </div>

              <ReviewBody />
              {!showSeeMore && <p className="mt-[25px]"></p>}

              {/* See more button only if overflowing */}
              {showSeeMore && (
                <Dialog open={open} onOpenChange={setOpen}>
                  <DialogTrigger asChild>
                    <button className="text-site-primary-500 cursor-pointer text-sm font-medium hover:underline">
                      See more
                    </button>
                  </DialogTrigger>

                  <DialogContent className="bg-site-gray-50 w-full !max-w-[96%] rounded-[26px] p-4 md:!max-w-lg [&>button]:hidden">
                    {/* Close button */}
                    <div
                      className="absolute top-3 right-3 cursor-pointer"
                      onClick={() => setOpen(false)}
                    >
                      <ModalCloseIcon className="fill-site-gray-300 transition-colors hover:fill-red-400" />
                    </div>

                    <DialogHeader>
                      <DialogTitle hidden>
                        {testimonial?.user?.name}&apos;s Review
                      </DialogTitle>
                    </DialogHeader>

                    <div className="flex flex-col space-y-3 md:space-y-6">
                      <div className="flex items-center gap-3">
                        <h4 className="text-site-gray-900 text-lg font-normal md:text-[23px]">
                          {testimonial?.user?.name}
                        </h4>
                        {testimonial?.user?.verified && (
                          <div className="flex items-center gap-1">
                            <ShieldCheckIcon size={20} />
                            <span className="text-site-primary-500 text-xs font-semibold md:text-sm">
                              Verified
                            </span>
                          </div>
                        )}
                      </div>

                      <div className="space-y-2">
                        <div className="flex items-center gap-0.5">
                          {[1, 2, 3, 4, 5].map((star) => (
                            <IoStar
                              key={star}
                              className={
                                star <= (testimonial?.rating || 0)
                                  ? "text-[#FF8800]"
                                  : "text-site-gray-200"
                              }
                              size={16}
                            />
                          ))}
                        </div>
                        <ReviewBody full />
                      </div>

                      {/* Product Info */}
                      <Link
                        href={`/${testimonial?.product?.slug}`}
                        className="group border-site-primary-50 hover:border-site-primary-300 relative flex gap-2 rounded-[10px] border bg-gray-50 p-1 transition-colors lg:gap-4 lg:p-2"
                      >
                        <div className="relative inline-block h-[60px] w-[60px] shrink-0 overflow-clip rounded-sm md:h-[75px] md:w-[75px]">
                          <Image
                            className="rounded-sm border border-white transition-transform duration-300 ease-in-out group-hover:scale-110"
                            src={`${imageBaseHostUrl}${testimonial?.product?.thumbnail_image}`}
                            alt={testimonial?.product?.name}
                            fill
                            loading="lazy"
                            style={{ objectFit: "cover" }}
                          />
                        </div>
                        <div className="space-y-1">
                          <p className="text-site-gray-900 line-clamp-2 text-xs font-normal md:text-sm">
                            {testimonial?.product?.name}
                          </p>
                          <div className="text-site-primary-500 flex flex-wrap items-center gap-1.5 text-xs font-normal">
                            <span className="text-site-secondary-600 text-xs leading-6 font-bold md:text-base">
                              {
                                testimonial?.product
                                  ?.formatted_base_discounted_price
                              }
                            </span>
                            <span className="shrink-0 rounded-[100px] bg-white px-2 py-1 max-md:text-[10px]">
                              Save{" "}
                              <span className="font-semibold">
                                ৳{testimonial.product?.save}
                              </span>{" "}
                            </span>
                          </div>
                        </div>
                        <div className="group/plus absolute right-2 bottom-2 z-10 hidden h-7 w-7 cursor-pointer place-content-center rounded-[5px] text-white transition-colors md:grid">
                          <Info className="text-site-gray-700 h-4 w-4 transition-transform duration-300 group-hover/plus:rotate-180" />
                        </div>
                      </Link>
                    </div>
                  </DialogContent>
                </Dialog>
              )}
            </div>
          </div>

          {/* Footer Product Info */}
          <div>
            <Link
              href={`/${testimonial?.product?.slug}`}
              className="group border-site-primary-50 hover:border-site-primary-300 relative flex gap-2 rounded-[10px] border bg-gray-50 p-1 transition-colors lg:gap-4 lg:p-2"
            >
              <div className="relative inline-block h-[60px] w-[60px] shrink-0 overflow-clip rounded-sm md:h-[75px] md:w-[75px]">
                <Image
                  className="rounded-sm border border-white transition-transform duration-300 ease-in-out group-hover:scale-110"
                  src={`${imageBaseHostUrl}${testimonial?.product?.thumbnail_image}`}
                  alt={testimonial?.product?.name}
                  fill
                  loading="lazy"
                  style={{ objectFit: "cover" }}
                />
              </div>
              <div className="space-y-1">
                <p className="text-site-gray-900 line-clamp-2 text-xs font-normal md:text-sm">
                  {testimonial?.product?.name}
                </p>
                <div className="text-site-primary-500 flex flex-wrap items-center gap-1.5 text-xs font-normal">
                  <span className="text-site-secondary-600 text-xs leading-6 font-bold md:text-base">
                    {testimonial?.product?.formatted_base_discounted_price}
                  </span>
                  <span className="shrink-0 rounded-[100px] bg-white px-1 py-1 max-md:text-[10px] lg:px-2">
                    Save{" "}
                    <span className="font-semibold">
                      {currencySymbol}
                      {testimonial.product?.save}
                    </span>{" "}
                  </span>
                </div>
              </div>
              <div className="group/plus absolute right-2 bottom-2 z-10 hidden h-7 w-7 cursor-pointer place-content-center rounded-[5px] text-white transition-colors md:grid">
                <Info className="text-site-gray-700 h-4 w-4 transition-transform duration-300 group-hover/plus:rotate-180" />
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TestimonialCard;
