"use client";

import {
  DashboardSheetBagIcon,
  DashboardSheetCallIcon,
  DashboardSheetDashboardIcon,
  DashboardSheetEmailIcon,
  DashboardSheetHeartIcon,
  DashboardSheetLogoutIcon,
  DashboardSheetProfileIcon,
  DashboardSheetPurchaseIcon,
  DashboardSheetWhatsappIcon,
} from "@/components/icons/icon-library";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";
import { api } from "@/lib/axios";
import { badges } from "@/lib/badge";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useSession } from "@/store/useAuthStore";
import { useDashboardSheet } from "@/store/useDashboardSheet";
import { useFormControl } from "@/store/useFormControl";
import { useToken } from "@/store/useTokenStore";
import { useQuery } from "@tanstack/react-query";
import { ChevronRight } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import React from "react";

interface DataType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

const MobileBottomNavigationSheet = () => {
  const { show, setShow } = useDashboardSheet();
  const { accessToken } = useToken();
  const { user, logout } = useSession();
  const { setOpen } = useAuthModalStore();
  const { setIsLogin } = useFormControl();

  /** Fetch user data */
  const { data: userData } = useQuery({
    queryKey: ["get_user_data"],
    queryFn: async () => {
      const res = await api.post(
        "/get-user-by-access_token",
        { access_token: accessToken },
        { headers: { Authorization: `Bearer ${accessToken}` } },
      );
      return res.data as UserResponse;
    },
    enabled: !!accessToken && !!user?.id,
  });

  const currentGroup = badges.find(
    (group) => group.id === user?.customer_group?.id,
  );

  // Get logo
  // const { data: logo } = useQuery({
  //   queryKey: ["get_logo"],
  //   queryFn: async () => {
  //     const { data } = await api.get("/business-settings");
  //     return data.data.filter(
  //       (item: DataType) => item.type === "header_logo",
  //     )[0] as DataType;
  //   },
  // });

  /** Logout handler */
  const handleLogout = async () => {
    setShow(false);
    await logout();
    removeLocalStorageKey("access_token");
    setTimeout(() => (window.location.href = "/"), 100);
  };

  /** Reusable menu link */
  const MenuLink = ({
    href,
    icon,
    label,
    target,
  }: {
    href: string;
    icon: React.ReactNode;
    label: string;
    target?: string;
  }) => (
    <Link
      href={href}
      onClick={() => setShow(false)}
      className="group flex items-center justify-between px-3 py-4"
      target={target}
    >
      <span className="text-site-gray-800 group-hover:text-site-gray-400 flex items-center gap-3 font-semibold transition-colors">
        {icon}
        {label}
      </span>
      <ChevronRight
        className="text-site-gray-400"
        size={20}
        strokeWidth={2.5}
      />
    </Link>
  );
  return (
    <div>
      <div className="py-6">
        <div hidden>Dashboard Menu</div>

        {/* Header */}
        {/* <div className="flex items-center justify-between pb-4">
          {logo && (
            <Link href={"/"} onClick={() => setShow(false)}>
              <Image
                src={logo.image_url}
                alt="logo"
                width={161}
                height={44}
                priority
                className="object-contain"
              />
            </Link>
          )}

          <div className="cursor-pointer" onClick={() => setShow(false)}>
            <ModalCloseIcon className="fill-site-gray-300 transition-colors hover:fill-red-400" />
          </div>
        </div> */}

        {/* Scroll area with Subcategories */}
        <div className="relative">
          <div className="flex-1">
            {/* User Info */}
            {userData && (
              <div className="bg-site-primary-50 flex items-center gap-5 overflow-clip rounded-tl-[10px] rounded-tr-[10px] px-5 pt-4 pb-4">
                <div className="relative flex h-20 w-20 shrink-0 flex-col items-center">
                  <Image
                    src={
                      userData.avatar_original
                        ? imageBaseHostUrl + userData.avatar_original
                        : "/images/avater.png"
                    }
                    onError={(e) => {
                      e.currentTarget.src = "/images/avater.png";
                    }}
                    alt="User avatar"
                    height={80}
                    width={80}
                    className="border-site-primary-600 h-20 w-20 rounded-full border-2 object-cover"
                  />

                  {/* {currentGroup && (
                    <div className="bg-site-primary-600 text-site-primary-50 absolute -bottom-4 flex -translate-y-2 items-center gap-1 rounded-full px-1 py-0 pr-2 text-[13px] font-medium shadow-md">
                      <Image
                        src={currentGroup.badge}
                        alt="badge"
                        height={16}
                        width={16}
                        className="shrink-0"
                      />
                      {currentGroup?.name && (
                        <span className="text-[10px] whitespace-nowrap">
                          {currentGroup?.name}
                        </span>
                      )}
                    </div>
                  )} */}
                </div>

                <div className="flex flex-col">
                  <p className="text-site-gray-800 text-lg font-semibold capitalize">
                    {userData.name}
                  </p>
                  <p className="text-site-gray-200 text-xs">{userData.email}</p>
                  <Link
                    href="/profile"
                    onClick={() => setShow(false)}
                    className="text-site-primary text-xs font-semibold"
                  >
                    View Profile
                  </Link>
                </div>
              </div>
            )}

            {/* Menu List */}
            <div className="">
              {/* Dashboard */}

              {userData && (
                <Link
                  href="/dashboard"
                  onClick={() => setShow(false)}
                  className="bg-site-primary-50/80 group flex items-center justify-between px-3 py-4"
                >
                  <span className="text-site-gray-800 group-hover:text-site-gray-400 flex items-center gap-3 font-semibold transition-colors">
                    <DashboardSheetDashboardIcon />
                    Dashboard
                  </span>
                  <ChevronRight
                    className="text-site-gray-400"
                    size={20}
                    strokeWidth={2.5}
                  />
                </Link>
              )}
              <hr />

              {/* Grouped links */}
              <div className="bg-site-primary-50/80 overflow-clip rounded-br-[10px] rounded-bl-[10px]">
                {/* <p className="text-site-gray-400 pl-3 text-sm font-semibold uppercase">
                  PERSONALIZATION
                </p> */}

                {userData && (
                  <>
                    <MenuLink
                      href="/profile"
                      icon={<DashboardSheetProfileIcon />}
                      label="Edit Profile"
                    />
                    <hr />
                    <MenuLink
                      href="/purchase-history"
                      icon={<DashboardSheetPurchaseIcon />}
                      label="Purchase History"
                    />
                    <hr />
                  </>
                )}
                <MenuLink
                  href="/cart"
                  icon={<DashboardSheetBagIcon />}
                  label="My Bag"
                />
                <hr />
                <MenuLink
                  href="/wishlist"
                  icon={<DashboardSheetHeartIcon />}
                  label="My Wishlist"
                />
                {/* <hr />
                <MenuLink
                  href="/dashboard"
                  icon={<DashboardSheetStarIcon />}
                  label="My Reviews"
                /> */}
              </div>

              {/* Grouped links */}
              {/* <div className="overflow-clip rounded-[10px] bg-[#F1FFEB]">
                <p className="text-site-gray-400 pt-4 pl-3 text-sm font-semibold uppercase">
                  Support
                </p>
                
                <MenuLink
                  href="mailto:info@glowaro.com"
                  icon={<DashboardSheetCallIcon />}
                  label="Email"
                />
                <hr />
                <MenuLink
                  href="https://wa.me/8801714117604"
                  icon={<DashboardSheetWhatsappIcon />}
                  label="Whatsapp"
                  target="_blank"
                />
                <hr />
                <MenuLink
                  href="tel:+8809666767604"
                  icon={<DashboardSheetCallIcon />}
                  label="Call"
                />
              </div> */}

              {/* Logout */}
              {userData && (
                <div
                  onClick={handleLogout}
                  className="group mt-2 flex cursor-pointer items-center justify-between rounded-[10px] bg-[#FCECEC] px-3 py-4"
                >
                  <span className="text-site-gray-800 flex items-center gap-3 font-semibold transition-colors group-hover:text-red-500">
                    <DashboardSheetLogoutIcon />
                    Logout
                  </span>
                  <ChevronRight
                    className="text-site-gray-400"
                    size={20}
                    strokeWidth={2.5}
                  />
                </div>
              )}

              {/* Signup & Login button */}
              {!userData && (
                <div className="mt-12 space-y-3">
                  <button
                    onClick={() => {
                      setOpen(true);
                      setShow(false);
                      setIsLogin(true);
                    }}
                    className="bg-site-primary hover:bg-site-primary-600 w-full flex-1 cursor-pointer rounded-lg px-6 py-3 text-base font-medium text-white transition-colors focus:outline-none"
                  >
                    Log in
                  </button>
                  <button
                    onClick={() => {
                      setOpen(true);
                      setShow(false);
                      setIsLogin(false);
                    }}
                    className="bg-site-gray-700 hover:bg-site-gray-900 w-full flex-1 cursor-pointer rounded-lg px-6 py-3 text-base font-medium text-white transition-colors focus:outline-none"
                  >
                    Sign up
                  </button>
                </div>
              )}
            </div>
          </div>
          {/* <div className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none">
            <div className="flex-1 rounded-full bg-gray-400" />
          </div> */}
        </div>
      </div>
    </div>
  );
};

export default MobileBottomNavigationSheet;
