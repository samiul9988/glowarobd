"use client";

import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useRef, useState } from "react";
import RealResultSlider from "../sliders/RealResultSlider";

export const RealResultTab = ({ allTabs }: { allTabs: VideoTab[] }) => {
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
  }, [activeTabIndex]);

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

  const handleMouseUp = () => {
    setIsDragging(false);
  };

  const handleMouseLeave = () => {
    setIsDragging(false);
  };

  return (
    <div className="w-full">
      {/* Scrollable Tab buttons */}
      {/* <div
        ref={tabContainerRef}
        className="hide-scrollbar relative mx-auto flex h-8 items-center overflow-x-auto md:h-10"
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseLeave}
      >
        <motion.span
          layout
          transition={{ type: "tween", stiffness: 300, damping: 30 }}
          className="absolute top-0 bottom-0 z-10 flex overflow-hidden rounded-3xl"
          style={{ left: tabUnderlineLeft, width: tabUnderlineWidth }}
        >
          <span className="bg-site-gray-900 h-full min-h-8 w-full rounded-3xl md:min-h-10" />
        </motion.span>

        <div className="flex space-x-2 px-2">
          {allTabs &&
            allTabs.length > 0 &&
            allTabs?.map((tab, index) => {
              const isActive = activeTabIndex === index;
              return (
                <button
                  key={tab.id}
                  ref={(el) => {
                    if (el) tabsRef.current[index] = el;
                  }}
                  className={`${
                    isActive
                      ? "z-20 bg-transparent text-white transition duration-100"
                      : "hover:text-site-gray-500 bg-[#FAFAFA]"
                  } text-site-gray-400 my-auto cursor-pointer rounded-full px-4 py-2 text-center text-xs font-medium whitespace-nowrap select-none md:text-base`}
                  onClick={() => {
                    if (!isDragging) {
                      setActiveTabIndex(index);
                    }
                  }}
                >
                  {tab?.title}
                </button>
              );
            })}
        </div>
      </div> */}

      {/* Tab content with animation */}
      <div className="md:mt-8 rounded-xl">
        <AnimatePresence mode="wait">
          <motion.div
            key={allTabs[activeTabIndex].id}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.25 }}
          >
            <RealResultSlider data={allTabs[activeTabIndex]?.videos} />
          </motion.div>
        </AnimatePresence>
      </div>
    </div>
  );
};
