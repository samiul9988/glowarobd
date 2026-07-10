"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useRef, useState } from "react";
import ShowByCategorySlider from "../sliders/ShowByCategorySlider";
import Image from "next/image";

interface Props {
  data: {
    flashdeal: FlashDealType;
    products: ProductType[] | undefined;
  }[];
}

export const SpecialOfferZoneTab = ({ data }: Props) => {
  const tabsRef = useRef<HTMLButtonElement[]>([]);
  const tabContainerRef = useRef<HTMLDivElement | null>(null);

  const [activeTabIndex, setActiveTabIndex] = useState<number>(0);
  const [tabUnderlineWidth, setTabUnderlineWidth] = useState(0);
  const [tabUnderlineLeft, setTabUnderlineLeft] = useState(0);

  const [isDragging, setIsDragging] = useState(false);
  const [startX, setStartX] = useState(0);
  const [scrollLeft, setScrollLeft] = useState(0);
  const [moved, setMoved] = useState(0);

  useEffect(() => {
    const setTabPosition = () => {
      const currentTab = tabsRef.current[activeTabIndex];
      if (currentTab) {
        setTabUnderlineLeft(currentTab.offsetLeft);
        setTabUnderlineWidth(currentTab.clientWidth);
      }
    };
    setTabPosition();
  }, [activeTabIndex, data]);

  const handleMouseDown = (e: React.MouseEvent<HTMLDivElement>) => {
    setIsDragging(true);
    setMoved(0);
    if (tabContainerRef.current) {
      setStartX(e.pageX - tabContainerRef.current.offsetLeft);
      setScrollLeft(tabContainerRef.current.scrollLeft);
    }
  };

  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!isDragging || !tabContainerRef.current) return;
    e.preventDefault();
    const x = e.pageX - tabContainerRef.current.offsetLeft;
    const walk = x - startX;

    setMoved(Math.abs(walk));
    tabContainerRef.current.scrollLeft = scrollLeft - walk;
  };

  const handleMouseUp = () => setIsDragging(false);
  const handleMouseLeave = () => setIsDragging(false);

  const handleTabClick = (index: number) => {
    if (moved < 5) {
      setActiveTabIndex(index);
    }
  };

  return (
    <div className="w-full">

      {/* Scrollable Tab buttons */}
      <div className="flex items-center justify-between">
        <div className="z-30  text-center flex items-center gap-1 md:gap-2">
            {/* <div className="relative h-6 w-6 md:h-10 md:w-10 hidden sm:block">
                <Image src="/images/offer-icon.png" alt="offer" fill loading="lazy" unoptimized />
            </div> */}
            <h2 className="font-bold text-[20px] sm:text-[22px] text-white md:text-[40px] ">
            Offers for you
            </h2>
        </div>
        <div className="relative z-30 w-fit max-w-[400px] overflow-hidden rounded-full bg-[#FFFFFF1F]  backdrop-blur-lg md:max-w-[800px] ">
            {/* Headings */}
            
            <div
                ref={tabContainerRef}
                className="hide-scrollbar relative flex flex-nowrap items-center justify-center overflow-x-auto"
                onMouseDown={handleMouseDown}
                onMouseMove={handleMouseMove}
                onMouseUp={handleMouseUp}
                onMouseLeave={handleMouseLeave}
            >
            {/* Underline */}
            <motion.span
                layout
                transition={{ type: "tween", stiffness: 300, damping: 30 }}
                className="absolute top-0 bottom-0 z-10 flex overflow-hidden rounded-3xl"
                style={{ left: tabUnderlineLeft, width: tabUnderlineWidth }}
            >
                <span className="h-full min-h-7 w-full rounded-3xl text-site-secondary-500 bg-site-secondary-50 md:min-h-10" />
            </motion.span>

            {/* Dynamic Tabs */}
            <div className="flex flex-nowrap  ">
                {data.map(({ flashdeal }, index) => {
                const isActive = activeTabIndex === index;
                return (
                    <button
                    key={flashdeal.id}
                    ref={(el) => {
                        if (el) tabsRef.current[index] = el;
                    }}
                    className={`${
                        isActive
                        ? "text-site-secondary-500 font-bold z-20 bg-transparent transition duration-100"
                        : "text-[#FFFFFFCC] transition duration-100 hover:text-gray/90"
                    }  my-auto cursor-pointer rounded-full px-3.5 py-1.5 text-center  font-medium whitespace-nowrap select-none md:px-5 md:py-3 text-base`}
                    onClick={() => handleTabClick(index)}
                    >
                    {flashdeal.title}
                    </button>
                );
                })}
            </div>
            </div>

            {/* Progress bar */}
            {/* <div className="absolute bottom-0 left-1/2 -translate-x-1/2 w-[100%] rounded-full overflow-hidden z-10">
            <motion.div
                className="h-0.5 bg-white/90"
                animate={{
                width: `${((activeTabIndex + 1) / data.length) * 100}%`,
                }}
                transition={{ duration: 0.5, ease: "linear" }}
            />
            </div> */}
        </div>
      </div>

      {/* Tab content with animation */}
      <div className="mt-4 md:mt-[40px] rounded-xl">
        <AnimatePresence mode="wait">
          <motion.div
            key={data[activeTabIndex]?.flashdeal.id}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.25 }}
          >
            <ShowByCategorySlider
              data={data[activeTabIndex]?.products || []}
              category={data[activeTabIndex].flashdeal}
              link="/flash-deals/"
            />
          </motion.div>
        </AnimatePresence>
      </div>
    </div>
  );
};
