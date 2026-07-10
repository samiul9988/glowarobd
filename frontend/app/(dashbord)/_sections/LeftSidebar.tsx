"use client";

import {
  LayersIcon,
  LogoutIcon,
  OrderListIcon,
  StoreIcon,
  // SupportTicketIcon,
  UserSettingsIcon,
} from "@/components/icons/icon-library";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { Heart, LogOut } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";

interface Props {
  userData: User | null;
}

const mobileTabs = [
  {
    value: "dashboard",
    label: "Dashboard",
    icon: LayersIcon,
    path: "/dashboard",
  },
  {
    value: "purchase-history",
    label: "Orders",
    icon: OrderListIcon,
    path: "/purchase-history",
  },
  {
    value: "profile",
    label: "Profile",
    icon: UserSettingsIcon,
    path: "/profile",
  },
  {
    value: "logout",
    label: "Logout",
    icon: LogoutIcon,
    path: "/logout",
  },
];

export default function LeftSidebar({ userData }: Props) {
  const pathName = usePathname();
  const { logout } = useSession();

  // Logout Handler
  const handleLogout = async () => {
    await logout();
    setTimeout(() => (window.location.href = "/"), 100);
  };

  // Mobile Navigation Handler
  // const handleMobileChange = async (value: string) => {
  //   if (value === "logout") {
  //     await logout();
  //     removeLocalStorageKey("access_token");
  //     setTimeout(() => (window.location.href = "/"), 100);
  //     return;
  //   }
  //   router.push(`/${value}`);
  // };

  // NavItem Component (Desktop)
  const NavItem = ({
    href,
    Icon,
    label,
  }: {
    href: string;
    Icon: React.ElementType;
    label: string;
  }) => {
    const active = pathName === href;
    return (
      <Link
        prefetch={false}
        href={href}
        className={`group flex items-center gap-4 rounded-sm px-4 py-2 text-base transition duration-200 ${
          active
            ? "bg-site-secondary-500 font-semibold text-white"
            : "text-site-gray-700 hover:bg-site-gray-100"
        }`}
      >
        <Icon
          className={`stroke-[#1E2939] transition duration-300 ${
            active && "stroke-white"
          }`}
        />
        {label}
      </Link>
    );
  };

  // UI
  return (
    <div className="hidden h-fit w-full space-y-7 rounded-2xl bg-gradient-to-b from-[#EFE6F8] to-[#FAF5FF] p-3 md:block md:p-6 lg:max-w-[288px]">
      {/* Navigation Menu (Desktop) */}
      <nav className="hidden space-y-2 md:block">
        <NavItem href="/dashboard" Icon={LayersIcon} label="Dashboard" />
        <NavItem
          href="/purchase-history"
          Icon={StoreIcon}
          label="Purchase History"
        />
        <NavItem href="/my-wishlist" Icon={Heart} label="My Wishlist" />
        <NavItem href="/profile" Icon={UserSettingsIcon} label="Settings" />

        {/* Logout */}
        <button
          onClick={handleLogout}
          className="hover:bg-site-gray-100 text-site-gray-700 flex w-full cursor-pointer items-center gap-4 rounded-md px-4 py-2 text-base transition duration-300"
        >
          <LogOut className="stroke-[#1E2939] transition duration-300" />
          Logout
        </button>
      </nav>

      {/* Mobile Tabs */}
      {/* <div className="block lg:hidden">
        <div className="grid grid-cols-4 gap-3">
          {mobileTabs.map(({ value, label, icon: Icon, path }) => {
            const isActive = pathName === path;
            const isLogout = value === "logout";

            return (
              <button
                key={value}
                onClick={() => handleMobileChange(value)}
                className={`flex flex-col items-center justify-center gap-1 rounded-xl p-3 transition-all duration-200 ${
                  isLogout
                    ? "border-site-gray-200 hover:bg-site-gray-100 border bg-white text-[#E54545]"
                    : isActive
                      ? "bg-site-primary-500 text-white shadow-md"
                      : "text-site-gray-700 border-site-gray-200 border bg-white"
                }`}
              >
                <Icon
                  className={`h-5 w-5 ${
                    isActive
                      ? "stroke-white"
                      : isLogout
                        ? "stroke-[#E54545]"
                        : "stroke-site-gray-700"
                  }`}
                />
                <span className="text-center text-xs font-medium">{label}</span>
              </button>
            );
          })}
        </div>
      </div> */}
    </div>
  );
}
