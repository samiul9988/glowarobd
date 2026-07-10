"use client";

import { usePathname } from "next/navigation";
import React from "react";
import MobileBottomNavigation from "../layout/_components/MobileBottomNavigation";
import MobileBottomNavigationSheet from "../layout/_components/MobileBottomNavigationSheet";

interface Props {
  children: React.ReactNode;
}

const MobileBottomNavigationProvider = ({ children }: Props) => {
  const pathname = usePathname();

  // top-level static pages
  const topLevelPages = [
    "/",
    "/categories",
    "/wishlist",
    "/profile",
    "/flash-deals",
    "/purchase-history",
    "/brands",
    "/dashboard",
    "/cart",
    "/search",
    "/track-your-order",
    "/checkout",
    "/my-panel",
  ];

  // path prefixes (for nested or dynamic pages)
  const prefixPages = ["/page/", "/category/"];

  const hideNav =
    !topLevelPages.includes(pathname) &&
    !prefixPages.some((prefix) => pathname.startsWith(prefix));

  return (
    <>
      {children}
      {!hideNav && <MobileBottomNavigation />}
      {/* <MobileBottomNavigationSheet /> */}
    </>
  );
};

export default MobileBottomNavigationProvider;
