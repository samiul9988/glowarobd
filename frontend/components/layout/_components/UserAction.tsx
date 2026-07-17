"use client";

import { SearchIcon, UserCircleIcon } from "@/components/icons/icon-library";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useToken } from "@/store/useTokenStore";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import Cart from "./cart/Cart";
import Wishlist from "./wishlist/Wishlist";
import MobileSearch from "./MobileSearch";
import { Search, UserRound } from "lucide-react";

export default function UserActions() {
  const { setOpen } = useAuthModalStore();

  const { accessToken } = useToken();

  // Check if auth-modal is open and show popup
  const searchParams = useSearchParams();
  const authModal = searchParams.get("authModal");
  const [showMobileSearchBar, setShowMobileSearchBar] = useState(false);
  useEffect(() => {
    if (authModal === "open") {
      setOpen(true);
    }
  }, [authModal, setOpen]);

  return (
    <div className="flex items-center justify-end gap-1.5 lg:w-[232px]">
      {/* Wishlist */}
      <Wishlist />
      {/* search */}
      <button
        className="flex h-[42px] w-[52px] items-center justify-center rounded-full bg-white p-1 transition-all md:h-8 md:w-8 md:p-1.5 lg:hidden"
        onClick={() => setShowMobileSearchBar(true)}
      >
        <div className="relative">
          <Search className="text-site-gray-900 h-6 w-6 md:h-5 md:w-5" />
        </div>
      </button>
      <MobileSearch
        showMobileSearchBar={showMobileSearchBar}
        setShowMobileSearchBar={setShowMobileSearchBar}
      />

      {/* User Profile */}
      <div className="">
        {accessToken ? (
          <Link
            href="/dashboard"
            className="hover:bg-site-gray-100/60 flex h-[42px] w-[42px] items-center justify-center rounded-full bg-[#FFFFFFCC] p-1 transition-all md:h-[44px] md:w-[44px] md:p-1.5"
          >
            <UserRound
              width={32}
              strokeWidth={2}
              className="h-5 w-5 text-[#583480] md:h-6 md:w-6"
            />
          </Link>
        ) : (
          <Link
            className="hover:bg-site-gray-100/60 flex h-[42px] w-[42px] cursor-pointer items-center justify-center rounded-full bg-[#FFFFFFCC] p-1 transition-all md:h-[44px] md:w-[44px] md:p-1.5"
            href="/auth/login"
          >
            <UserRound
              width={32}
              strokeWidth={2}
              className="h-5 w-5 text-[#583480] lg:h-6 lg:w-6"
            />
          </Link>
        )}
      </div>

      {/* Cart */}
      <div className="">
        <Cart />
      </div>
    </div>
  );
}
