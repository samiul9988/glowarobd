"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useMemo, useRef, useState } from "react";
import FlashDealProductGrid from "./FlashDealProductGrid";
import Image from "next/image";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { useCountdown } from "@/hooks/useCountdown";

interface Props {
  data: {
    flashDeal: FlashDealType;
    products: ProductType[] | undefined;
  }[];
}

export const FlashDealZoneTab = ({ data }: Props) => {
  const tabContainerRef = useRef<HTMLDivElement | null>(null);
  const tabsRef = useRef<HTMLButtonElement[]>([]);
  const sectionRef = useRef<HTMLDivElement | null>(null);

  const [activeTabIndex, setActiveTabIndex] = useState<number>(
    data.length > 1 ? data.length : 0,
  ); // "All" tab if multiple deals
  const [tabUnderlineStyle, setTabUnderlineStyle] = useState({
    left: 0,
    width: 0,
  });

  const [isDragging, setIsDragging] = useState(false);
  const [dragStartX, setDragStartX] = useState(0);
  const [scrollStart, setScrollStart] = useState(0);
  const [movedDistance, setMovedDistance] = useState(0);

  const isAllTabActive = activeTabIndex === data.length;

  // Update underline position on active tab change
  useEffect(() => {
    const currentTab = tabsRef.current[activeTabIndex];
    if (currentTab) {
      setTabUnderlineStyle({
        left: currentTab.offsetLeft,
        width: currentTab.clientWidth,
      });
    }
  }, [activeTabIndex, data]);

  // Drag handlers for scrollable tabs
  const handleMouseDown = (e: React.MouseEvent<HTMLDivElement>) => {
    setIsDragging(true);
    setMovedDistance(0);
    if (tabContainerRef.current) {
      setDragStartX(e.pageX - tabContainerRef.current.offsetLeft);
      setScrollStart(tabContainerRef.current.scrollLeft);
    }
  };

  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!isDragging || !tabContainerRef.current) return;
    e.preventDefault();
    const x = e.pageX - tabContainerRef.current.offsetLeft;
    const walk = x - dragStartX;
    setMovedDistance(Math.abs(walk));
    tabContainerRef.current.scrollLeft = scrollStart - walk;
  };

  const handleMouseUpOrLeave = () => setIsDragging(false);

  const handleTabClick = (index: number) => {
    if (movedDistance < 5) setActiveTabIndex(index);
  };

  return (
    <div>
      {/* Tabs */}
      <div
        ref={tabContainerRef}
        className="hide-scrollbar relative mb-6 flex overflow-x-auto whitespace-nowrap"
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUpOrLeave}
        onMouseLeave={handleMouseUpOrLeave}
      >
        {/* Animated Underline */}
        <motion.span
          layout
          transition={{ type: "tween", stiffness: 300, damping: 30 }}
          className="absolute top-0 bottom-0 z-10 flex overflow-hidden rounded-[10px]"
          style={{
            left: tabUnderlineStyle.left,
            width: tabUnderlineStyle.width,
          }}
        >
          <span className="h-full w-full rounded-[10px] bg-gradient-to-r from-[#E41B39] to-[#A529F2]" />
        </motion.span>

        {/* Tab Buttons */}
        {data.length > 0 && (
          <div className="flex space-x-3">
            {data.length > 0 && (
              <button
                ref={(el) => {
                  if (el) tabsRef.current[data.length] = el;
                }}
                className={`my-auto cursor-pointer rounded-[10px] border-[2px] px-2.5 py-1.5 text-center font-medium transition duration-100 ${
                  isAllTabActive
                    ? "z-20 border-white bg-gradient-to-r from-[#E41B39] to-[#A529F2] text-white"
                    : "text-site-gray-900 border-site-gray-900 hover:text-site-gray-800"
                } md:px-4 md:py-2 md:text-[23px]`}
                onClick={() => handleTabClick(data.length)}
              >
                All
              </button>
            )}

            {data.map(({ flashDeal }, index) => (
              <button
                key={flashDeal.id}
                ref={(el) => {
                  if (el) tabsRef.current[index] = el;
                }}
                className={`my-auto cursor-pointer rounded-[10px] border-[2px] px-2.5 py-1.5 text-center font-medium transition duration-100 ${
                  activeTabIndex === index
                    ? "z-20 border-white bg-gradient-to-r from-[#E41B39] to-[#A529F2] text-white"
                    : "text-site-gray-900 border-site-gray-900 hover:text-site-gray-800"
                } md:px-4 md:py-2 md:text-[23px]`}
                onClick={() => handleTabClick(index)}
              >
                {flashDeal.title}
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Tab Content */}
      {isAllTabActive ? (
        <div className="space-y-8">
          {data.map(({ flashDeal, products }) => (
            <FlashDealSection
              key={flashDeal.id}
              flashDeal={flashDeal}
              products={products || []}
            />
          ))}
        </div>
      ) : (
        <FlashDealSection
          key={data[activeTabIndex]?.flashDeal.id}
          flashDeal={data[activeTabIndex]?.flashDeal}
          products={data[activeTabIndex]?.products || []}
        />
      )}
    </div>
  );
};

// Reusable FlashDeal section component
const FlashDealSection = ({
  flashDeal,
  products,
}: {
  flashDeal: FlashDealType;
  products: ProductType[];
}) => {
  const endDate = useMemo(() => {
    return new Date(Number(flashDeal.date) * 1000);
  }, [flashDeal]);

  const { timeLeft, isExpired } = useCountdown(endDate);

  return (
    <div>
      <div className="relative mb-4 h-[180px] w-full overflow-hidden rounded-lg md:mb-8 md:h-[227px]">
        <Image
          src={imageBaseHostUrl + flashDeal.banner}
          alt="Flash Deal Banner"
          fill
          className="object-cover object-center"
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 100vw, 1200px"
          placeholder="blur"
          blurDataURL="/blur-placeholder.jpg"
          loading="lazy"
        />
      </div>
      {!isExpired && (
        <div className="mb-5 flex items-center justify-center gap-4 md:justify-start">
          <div className="flex flex-col items-center space-y-1">
            <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
              {timeLeft.days}
            </span>
            <span className="text-site-gray-400 text-xs font-normal uppercase">
              Days
            </span>
          </div>
          <div className="flex flex-col items-center space-y-1">
            <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
              {timeLeft.hours}
            </span>
            <span className="text-site-gray-400 text-xs font-normal uppercase">
              hours
            </span>
          </div>

          <div className="flex flex-col items-center space-y-1">
            <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
              {timeLeft.minutes}
            </span>
            <span className="text-site-gray-400 text-xs font-normal uppercase">
              min
            </span>
          </div>
          <div className="flex flex-col items-center space-y-1">
            <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
              {timeLeft.seconds}
            </span>
            <span className="text-site-gray-400 text-xs font-normal uppercase">
              sec
            </span>
          </div>
        </div>
      )}
      {/* Flash deal items title */}
      <div className="relative flex w-full justify-center overflow-clip rounded-tl-[10px] rounded-tr-[10px] bg-[url('/images/flashDeal-banner-bg.png')] bg-cover px-10 transition-transform duration-1000 ease-in-out md:justify-between">
        <div className="z-30 flex h-[98px] items-center justify-center text-center">
          <h2 className="text-site-gray-900 text-[32px] leading-8 md:text-[40px] md:leading-11">
            {flashDeal.title}
          </h2>
        </div>

        <div className="flex items-center gap-4">
          <Image
            src="/images/baby.gif"
            alt="Girl"
            width={166}
            height={134}
            className="absolute top-0 right-[6%] hidden md:block"
          />
          <Image
            src="/images/traffic-light.gif"
            alt="Traffic Light"
            width={60}
            height={60}
            className="hidden md:block"
          />
        </div>
      </div>

      {/* Products Grid */}
      <div className="bg-site-primary-900 w-full rounded-br-[10px] rounded-bl-[10px] p-5 md:p-8">
        <AnimatePresence mode="wait">
          <motion.div
            key={flashDeal.id}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.25 }}
          >
            <FlashDealProductGrid data={products} flashDeal={flashDeal} />
          </motion.div>
        </AnimatePresence>
      </div>
    </div>
  );
};
