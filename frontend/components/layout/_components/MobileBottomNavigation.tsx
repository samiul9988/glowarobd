"use client";

import { UserCircleIcon } from "@/components/icons/icon-library";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useDashboardSheet } from "@/store/useDashboardSheet";
import { useToken } from "@/store/useTokenStore";
import { Handbag, Heart, Home, LayoutGrid, Zap } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useLayoutEffect, useRef, useState } from "react";

const navItems = [
  { name: "Home", icon: <Home size={22} />, href: "/" },
  { name: "Categories", icon: <LayoutGrid size={22} />, href: "/categories" },
  { name: "Bag", icon: <Handbag size={22} />, href: "/cart" },
  { name: "Flash Deals", icon: <Zap size={22} />, href: "/flash-deals" },
  {
    name: "Account",
    icon: <UserCircleIcon size={24} strokeWidth={2} />,
    href: "/my-panel",
  },
];
export default function MobileBottomNavigation() {
  const containerRef = useRef<HTMLUListElement>(null);
  const pathname = usePathname();
  const [itemWidth, setItemWidth] = useState(0);
  const [isReady, setIsReady] = useState(false);
  const { setOpen } = useAuthModalStore();
  const { accessToken } = useToken();

  // auto recalc width on resize or scrollbar show/hide
  useLayoutEffect(() => {
    if (!containerRef.current) return;

    const updateWidth = () => {
      if (containerRef.current) {
        setItemWidth(containerRef.current.offsetWidth / navItems.length);
      }
    };

    updateWidth();
    const observer = new ResizeObserver(updateWidth);
    observer.observe(containerRef.current);

    window.addEventListener("resize", updateWidth);
    return () => {
      observer.disconnect();
      window.removeEventListener("resize", updateWidth);
    };
  }, []);

  useEffect(() => {
    const timer = setTimeout(() => setIsReady(true), 150);
    return () => clearTimeout(timer);
  }, []);

  // include Account as active if sheet is open
  const activeIndex = navItems.findIndex((item) => {
    if (item.name === "Account") return true;
    return pathname === item.href;
  });

  const indicatorX =
    itemWidth > 0 && activeIndex !== -1
      ? activeIndex * itemWidth + itemWidth / 2 - 28
      : 0;

  return (
    <div className="fixed right-0 bottom-0 left-0 z-50 bg-white shadow-[0_-2px_8px_rgba(0,0,0,0.1)] md:hidden">
      <div className="relative flex justify-center py-1">
        <div className="relative flex h-[56px] w-full items-center justify-center">
          <ul
            ref={containerRef}
            className="relative mb-0 flex w-full justify-between"
          >
            {navItems.map((item, index) => {
              const isAccount = item.name === "Account";
              const isActive = isReady && activeIndex === index;

              return (
                <li
                  key={item.name}
                  className="relative z-10 !mb-0 flex h-[56px] flex-1 flex-col items-center justify-center"
                >
                  {isAccount ? (
                    <Link
                      href="/my-panel"
                      className={`flex flex-col items-center justify-center ${isActive ? "text-site-primary-500" : "text-gray-900"}`}
                    >
                      <span
                        className={`transition-all duration-500 ${
                          isActive ? "" : "text-gray-900"
                        }`}
                      >
                        {item.icon}
                      </span>
                      <span
                        className={`text-[0.8rem] font-medium transition-all duration-500 ${
                          isActive ? "" : "text-gray-900 opacity-100"
                        }`}
                      >
                        {item.name}
                      </span>
                    </Link>
                  ) : (
                    <Link
                      href={item.href}
                      className={`flex flex-col items-center justify-center ${isActive ? "text-site-primary-500" : "text-gray-900"}`}
                    >
                      <span
                        className={`transition-all duration-500 ${
                          isActive ? "" : "text-gray-900"
                        }`}
                      >
                        {item.icon}
                      </span>
                      <span
                        className={`text-[0.8rem] font-medium transition-all duration-500 ${
                          isActive ? "" : "text-gray-900 opacity-100"
                        }`}
                      >
                        {item.name}
                      </span>
                    </Link>
                  )}
                </li>
              );
            })}

            {/* Indicator */}
            {/* {activeIndex !== -1 && (
              <li
                className={`bg-site-primary absolute top-0 z-0 h-[56px] w-[56px] -translate-y-[20%] rounded-full transition-transform duration-300 ease-out ${
                  isReady ? "opacity-100" : "opacity-0"
                }`}
                style={{
                  transform: `translateX(${indicatorX}px)`,
                }}
              />
            )} */}
          </ul>
        </div>
      </div>
    </div>
  );
}
