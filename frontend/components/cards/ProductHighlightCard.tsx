import { currencySymbol, imageBaseHostUrl } from "@/config/apiConfig";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { GoArrowRight } from "react-icons/go";
import { IoStar } from "react-icons/io5";

const ProductHighlightCard = ({
  title,
  subtitle,
  description,
  banner,
  highlights,
  link_type,
  link,
  show_button,
  button_text,
  pricing,
}: HighlightItem) => {
  return (
    <div className="flex flex-col items-center justify-between gap-8 lg:flex-row">
      {/* left side */}
      <div className="relative flex h-[370px] w-[320px] items-end justify-center md:h-[550px] md:w-[500px] md:px-4">
        <div className="h-[290px] w-full rounded-tl-[9999px] rounded-tr-[9999px] bg-[linear-gradient(121.94deg,#E4FFC9_0%,#93BE75_100%)] md:h-[460px] md:w-[455px]"></div>
        <Image
          src={imageBaseHostUrl + banner}
          alt="beauty"
          height={0}
          width={0}
          sizes="100vw"
          className="absolute bottom-0 h-full w-full object-cover"
          style={{ objectFit: "cover" }}
        />
        {/* Left side arrow with animation */}
        {/* after arrow: after:absolute after:top-[60%] after:-left-[8px] after:-translate-y-1/2 after:rounded-[2px] after:border-y-[8px] after:border-r-[10px] after:border-y-transparent after:border-r-white after:content-['']  */}

        <motion.div
          initial={{ scale: 0.5, opacity: 0 }}
          whileInView={{ scale: 1, opacity: 1 }}
          viewport={{ once: false, amount: 0.1 }}
          transition={{
            type: "spring",
            stiffness: 200,
            damping: 15,
            duration: 1,
            delay: 0.5,
          }}
          className="absolute top-0 right-0 inline-block translate-x-[25px] translate-y-[35px] space-y-1 rounded-[10px] bg-white px-4 py-2 shadow-[0px_4px_16px_0px_#0000001F] max-md:right-1 max-md:translate-x-[0px] md:-translate-x-[35px] md:translate-y-[35px] md:px-6"
        >
          <p className="text-site-gray-700 text-sm font-medium">Top Rated</p>
          <div className="flex items-center gap-0.5">
            <IoStar className="text-[#FF8800]" size={13} />
            <IoStar className="text-[#FF8800]" size={13} />
            <IoStar className="text-[#FF8800]" size={13} />
            <IoStar className="text-[#FF8800]" size={13} />
            <IoStar className="text-[#FF8800]" size={13} />
          </div>
        </motion.div>

        {pricing && (
          <motion.div
            initial={{ scale: 0.5, opacity: 0 }}
            whileInView={{ scale: 1, opacity: 1 }}
            viewport={{ once: false, amount: 0.1 }}
            transition={{
              type: "spring",
              stiffness: 200,
              damping: 15,
              duration: 1,
              delay: 0.7,
            }}
            className="absolute right-0 bottom-0 hidden -translate-x-[106px] -translate-y-[70px] space-y-1 rounded-[10px] bg-white px-6 py-2 shadow-[0px_4px_16px_0px_#0000001F] md:inline-block"
          >
            <p className="text-sm font-medium text-[#FF8800]">
              {pricing?.discount_type === "amount" && (currencySymbol || "৳")}
              {pricing?.discount}
              {pricing?.discount_type === "percent" && "%"} Discount
            </p>
            <div className="flex items-center gap-1">
              <span className="text-site-gray-900 text-[19px] font-semibold tracking-tight">
                {pricing?.main_price}
              </span>
              <span className="text-site-gray-300 text-base line-through">
                {pricing?.stroked_price}
              </span>
            </div>
          </motion.div>
        )}
        {pricing && (
          <motion.div
            initial={{ scale: 0.5, opacity: 0 }}
            whileInView={{ scale: 1, opacity: 1 }}
            viewport={{ once: false, amount: 0.1 }}
            transition={{
              type: "spring",
              stiffness: 200,
              damping: 15,
              delay: 0.2,
            }}
            className="absolute right-0 bottom-0 inline-block -translate-x-[106px] -translate-y-[70px] space-y-1 rounded-[10px] bg-white px-6 py-2 shadow-[0px_4px_16px_0px_#0000001F] md:hidden"
          >
            <p className="text-sm font-medium text-[#FF8800]">
              {pricing?.discount_type === "amount" && "৳"}
              {pricing?.discount}
              {pricing?.discount_type === "percent" && "%"} Discount
            </p>
            <div className="flex items-center gap-1">
              <span className="text-site-gray-900 text-[19px] font-semibold tracking-tight">
                {pricing?.main_price}
              </span>
              <span className="text-site-gray-300 text-base line-through">
                {pricing?.stroked_price}
              </span>
            </div>
          </motion.div>
        )}
      </div>

      {/* right side */}
      <div className="pb-5 max-md:text-center lg:max-w-[605px] lg:pb-0">
        <div className="space-y-4">
          <div className="space-y-1 md:space-y-3">
            <p className="text-site-gray-900 text-sm leading-6 font-medium md:text-base">
              {subtitle}
            </p>
            <h2 className="text-site-gray-900 text-[32px] leading-9 md:text-[52px] md:leading-14">
              {title}
            </h2>
          </div>
          <p className="text-site-gray-600 text-base leading-[22px] font-normal md:leading-6">
            {description}
          </p>
        </div>

        {/* Highlights */}
        <div className="mt-8 mb-8 grid grid-cols-2 gap-3 md:mb-10 md:gap-5">
          {highlights.map((highlight, index) => (
            <div
              key={index}
              className="flex flex-col gap-1 max-md:items-center md:flex-row md:items-center md:gap-4"
            >
              <Image
                src={imageBaseHostUrl + highlight.icon}
                alt="beauty"
                height={60}
                width={60}
              />
              <h6 className="text-site-gray-900 text-[23px]">
                {highlight.label}
              </h6>
            </div>
          ))}
        </div>

        {/* Button */}
        {show_button && (
          <Link
            href={
              link_type === "product"
                ? `/${link}`
                : link_type === "brand"
                  ? `/brand/${link}`
                  : link_type === "category"
                    ? `/category/${link}`
                    : link
            }
            className="bg-site-primary-500 hover:bg-site-primary-500/90 group inline-flex cursor-pointer items-center gap-2 rounded-[10px] px-6 py-2.5 text-sm font-semibold text-white transition-colors duration-300 ease-in-out md:gap-4 md:px-10 md:py-4 md:text-base"
          >
            {button_text}
            <GoArrowRight
              strokeWidth={0.9}
              className="h-5 w-5 transition-all group-hover:translate-x-1"
            />
          </Link>
        )}
      </div>
    </div>
  );
};

export default ProductHighlightCard;
