"use client";

import React, { useEffect, useState } from "react";
import { cn } from "@/lib/utils";
import { motion } from "framer-motion";
import { Check, X } from "lucide-react";

interface OrderProgressProps {
  status:
    | "pending"
    | "processing"
    | "packaging"
    | "confirmed"
    | "picked_up"
    | "on_the_way"
    | "delivered"
    | "cancelled";
}

const OrderProgress: React.FC<OrderProgressProps> = ({ status }) => {
  const [animatedWidth, setAnimatedWidth] = useState("0%");
  const [currentStep, setCurrentStep] = useState(0);

  useEffect(() => {
    let width = "0%";
    let step = 0;

    switch (status) {
      case "pending":
      case "processing":
        width = "0%";
        step = 1;
        break;
      case "packaging":
      case "confirmed":
        width = "25%";
        step = 2;
        break;
      case "picked_up":
        width = "50%";
        step = 3;
        break;
      case "on_the_way":
        width = "75%";
        step = 4;
        break;
      case "delivered":
        width = "100%";
        step = 5;
        break;
      case "cancelled":
        width = "0%";
        step = 0;
        break;
    }

    setCurrentStep(step);
    setTimeout(() => setAnimatedWidth(width), 150);
  }, [status]);

  if (status === "cancelled") {
    return (
      <div className="flex flex-col items-center justify-center gap-4 rounded-lg border-2 border-red-200 bg-red-50 p-6">
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-red-500 text-xl font-semibold text-white">
          <X />
        </div>
        <div className="text-center">
          <p className="text-lg font-semibold text-red-700">Order Cancelled!</p>
          <p className="text-sm text-red-500">This order has been cancelled</p>
        </div>
      </div>
    );
  }

  const steps = [
    { id: 1, label: "Pending" },
    { id: 2, label: status === "packaging" ? "Packaging" : "Confirmed" },
    { id: 3, label: "Picked Up" },
    { id: 4, label: "On The Way" },
    { id: 5, label: "Delivered" },
  ];

  return (
    <div className="relative mx-auto w-full max-w-4xl py-6">
      {/* Progress Line */}
      <div className="relative mx-auto w-[calc(100%-62px)]">
        <div className="absolute top-[22px] left-0 h-1.5 w-full rounded bg-gray-100"></div>
        <motion.div
          className="bg-site-secondary-500 absolute top-[22px] left-0 h-1 origin-left rounded"
          initial={{ width: "0%" }}
          animate={{ width: animatedWidth }}
          transition={{
            duration: 1,
            ease: "easeInOut",
          }}
        />
      </div>

      {/* Steps */}
      <div className="relative mt-2 flex justify-between">
        {steps.map((step) => {
          const isActive = currentStep >= step.id;
          const isCurrent = currentStep === step.id;

          return (
            <div key={step.id} className="relative flex flex-col items-center">
              <motion.div
                className={cn(
                  "relative z-10 flex h-8 w-8 items-center justify-center rounded-full text-sm transition-all duration-300",
                  isActive
                    ? "bg-site-secondary-500 text-white"
                    : "text-site-secondary-500 border-site-secondary-500 border border-dashed",
                  isCurrent && "pulse-ring",
                )}
              >
                {isActive ? <Check size={16} strokeWidth={3} /> : `0${step.id}`}
              </motion.div>

              <span
                className={cn(
                  "mt-3 text-xs font-medium whitespace-nowrap sm:text-sm",
                  isActive ? "text-site-gray-800" : "text-site-gray-400",
                )}
              >
                {step.label}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default OrderProgress;
