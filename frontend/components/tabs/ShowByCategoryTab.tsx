"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useRef, useState } from "react";
import ShowByCategorySlider from "../sliders/ShowByCategorySlider";
import ShowByCategorySlider2 from "../sliders/ShowByCategorySlider2";
import { ArrowRight } from "lucide-react";
import Link from "next/link";
import { FaAngleRight } from "react-icons/fa";

interface Props {
  data: {
    category: CategoryType;
    products: ProductType[] | undefined;
  }[];
}

export const ShowByCategoryTab = ({ data }: Props) => {
  const tabsRef = useRef<HTMLButtonElement[]>([]);
  const tabContainerRef = useRef<HTMLDivElement | null>(null);
  const [activeTabIndex, setActiveTabIndex] = useState<number>(0);
  const [tabUnderlineWidth, setTabUnderlineWidth] = useState(0);
  const [tabUnderlineLeft, setTabUnderlineLeft] = useState(0);

  const [isDragging, setIsDragging] = useState(false);
  const [startX, setStartX] = useState(0);
  const [scrollLeft, setScrollLeft] = useState(0);

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
    tabContainerRef.current.scrollLeft = scrollLeft - walk;
  };

  const handleMouseUp = () => setIsDragging(false);
  const handleMouseLeave = () => setIsDragging(false);

  return (
    <div className="w-full  md:mt-10">
      {/* Scrollable Tab buttons */}
      <div
        ref={tabContainerRef}
        className="md:justify-center relative  md:mx-auto flex h-[36px] md:h-[44px] items-center overflow-x-auto hide-scrollbar"
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseLeave}
      >
        {/* Underline */}
        <motion.span
          layout
          transition={{ type: "tween", stiffness: 300, damping: 30 }}
          className=" absolute bottom-0 top-0 z-10 flex overflow-hidden rounded-3xl"
          style={{ left: tabUnderlineLeft, width: tabUnderlineWidth }}
        >
          <span className="h-full min-h-7 md:min-h-12 w-full bg-site-primary-500" />
        </motion.span>

        {/* Dynamic Tabs */}
        <div className="flex items-center   rounded-full bg-site-primary-100 h-full">
          {data.map(({ category }, index) => {
            const isActive = activeTabIndex === index;
            return (
              <button
                key={category.id}
                ref={(el) => {
                  if (el) tabsRef.current[index] = el;
                }}
                className={`${
                  isActive
                    ? "text-white font-bold bg-transparent z-20 transition duration-100"
                    : "  font-medium"
                } text-base  my-auto cursor-pointer select-none  py-1.5 md:py-2 px-2.5 md:px-4 text-center text-site-gray-900 whitespace-nowrap`}
                onClick={() => {
                  if (!isDragging) setActiveTabIndex(index);
                }}
              >
                {category.name}
              </button>
            );
          })}
        </div>
      </div>

      {/* Tab content with animation */}
      <div className="mt-8 rounded-xl">
        <AnimatePresence mode="wait">
          <motion.div
            key={data[activeTabIndex]?.category.id}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.25 }}
          >
            {/* Pass products dynamically to slider */}
            <ShowByCategorySlider data={data[activeTabIndex]?.products || []} category={data[activeTabIndex]?.category} />
          </motion.div>
        </AnimatePresence>
          <Link className="font-bold text-base flex items-center gap-1 justify-center w-full pt-5 md:pb-2" href={`/category/${data[activeTabIndex]?.category?.slug}`}>
            <AnimatePresence mode="wait">
                <motion.span
                key={data[activeTabIndex]?.category?.slug} // important for re-triggering animation
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ duration: 0.3, ease: "easeOut" }}
                className="flex items-center gap-1"
                >
                View All <FaAngleRight className="w-5 h-5" />
                </motion.span>
            </AnimatePresence>
          </Link>
      </div>
    </div>
  );
};
