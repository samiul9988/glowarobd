import Image from "next/image";
import Link from "next/link";
import React from "react";

const NotFound = () => {
  return (
    <div className="flex flex-col items-center justify-center py-10 md:py-20 lg:py-28">
      {/* <ShoppingCart className="h-7 w-7 md:h-12 md:w-12 text-site-gray-400" /> */}
      <span className="block mb-4 md:mb-6">
        <Image src="/images/404-image.gif" alt="empty cart" width={537} height={402} />
      </span>

      <h3 className="text-[28px] md:text-[40px] leading-[34px] md:leading-[44px] font-normal text-site-gray-800 mb-2">
        Look like you’re lost
      </h3>
      <p className="text-sm md:text-base text-site-gray-500">The page you are looking for not available</p>

      <Link
        replace
        href="/"
        className="text-center bg-site-gray-900 text-white py-2 px-6 rounded-xl font-medium hover:bg-site-gray-700 transition-colors mt-6 md:mt-10"
      >
        Explore Products
      </Link>
    </div>
  );
};

export default NotFound;
