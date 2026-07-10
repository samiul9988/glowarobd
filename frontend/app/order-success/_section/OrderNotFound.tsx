"use client";

import { AlertCircle } from "lucide-react";
import Link from "next/link";
import { motion } from "framer-motion";

const OrderNotFound = () => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="flex min-h-[60vh] flex-col items-center justify-center px-4 text-center"
    >
      <AlertCircle className="mb-4 h-16 w-16 text-red-500" />
      <h2 className="mb-2 text-2xl font-semibold">Order Not Found</h2>
      <p className="mb-6 text-gray-600">
        Sorry, we couldn’t find your order. Please check the order ID and try
        again.
      </p>
      <Link
        href="/"
        className="rounded-lg bg-black px-6 py-2 text-white transition-all hover:bg-gray-800"
      >
        Go Back to Home
      </Link>
    </motion.div>
  );
};

export default OrderNotFound;
