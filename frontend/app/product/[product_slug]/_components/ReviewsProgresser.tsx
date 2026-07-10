"use client";

import { useEffect, useRef, useState } from "react";
import { motion, useInView } from "framer-motion";
import { cn } from "@/lib/utils";
import { AvgStarShow } from "./AvgStarShow";

type RatingData = {
  avgRating: number;
  totalReview: number;
  ratingCount: number[];
  totalReviews: number;
};

export const ReviewProgresser = ({
  avgRating,
  totalReview,
  ratingCount,
  totalReviews,
}: RatingData) => {
  const ref = useRef<HTMLDivElement>(null);
  const isInView = useInView(ref, { once: true });
  const [animate, setAnimate] = useState(false);

  // Trigger animation only when in view
  useEffect(() => {
    if (isInView) {
      const timer = setTimeout(() => setAnimate(true), 200);
      return () => clearTimeout(timer);
    }
  }, [isInView]);

  // Calculate % based on total reviews
  const getPercent = (count: number) => {
    if (totalReview === 0) return 0;
    return (count / totalReview) * 100;
  };

  return (
    <div ref={ref} className="bg-site-primary-50 rounded-[10px] p-4 md:p-10">
      <div className="flex flex-row">
        {/* Left Side - Average Rating */}
        <div className="w-1/3">
          <div className="flex flex-col gap-1">
            <div className="text-site-gray-900 text-[42px] leading-[56px] font-medium md:text-[52px]">
              {avgRating.toFixed(1)}
            </div>
            <div className="flex items-center gap-0">
              {AvgStarShow(avgRating)}
            </div>
          </div>
          <div className="text-site-gray-500 mt-2 text-base capitalize">
            {totalReviews} reviews
          </div>
        </div>

        {/* Right Side - Progress Bars */}
        <div className="flex w-2/3 flex-col-reverse gap-1 md:gap-2">
          {ratingCount.map((count, index) => {
            const percentage = getPercent(count);
            return (
              <div key={index} className="flex items-center gap-2">
                <span className="w-4 text-sm">{index + 1}</span>
                <div className="h-[10px] flex-1 overflow-hidden rounded-full bg-white">
                  <motion.div
                    initial={{ width: 0 }}
                    animate={{
                      width: animate ? `${percentage}%` : 0,
                    }}
                    transition={{ duration: 0.8, ease: "easeOut" }}
                    className={cn("bg-site-primary-500 h-full rounded-full")}
                  />
                </div>
                <span>({count ?? 0})</span>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};
