"use client";

import {
  AboutIcon,
  ReturnIcon,
  ShippingIcon,
  SupportIcon,
  TermsIcon,
} from "@/components/icons/icon-library";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { cn } from "@/lib/utils";
import { useShowHeader } from "@/store/useShowHeader";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";

interface Props {
  links: string[];
  labels: string[];
}

export default function SideBar({ labels, links }: Props) {
  const router = useRouter();
  const pathName = usePathname();
  const { showHeader } = useShowHeader();

  const handleMobileChange = (value: string) => {
    router.push(value.startsWith("/") ? value : `/${value}`);
  };

  // Map link keywords to icon
  const getIcon = (slug: string) => {
    const s = slug.toLowerCase();
    if (s.includes("about")) return AboutIcon;
    if (s.includes("term")) return TermsIcon;
    if (s.includes("shipping") || s.includes("delivery")) return ShippingIcon;
    if (s.includes("privacy")) return SupportIcon;
    if (s.includes("return") || s.includes("refund")) return ReturnIcon;
    if (s.includes("warrant")) return SupportIcon;
    return SupportIcon; // fallback
  };

  // Get active link for mobile select
  const activeLink =
    links.find((link) => pathName.endsWith(link.replace("/", ""))) || links[0];

  return (
    <div
      className={cn(
        "bg-site-gray-50 w-full space-y-7 rounded-lg p-6 transition-all duration-300 lg:sticky lg:max-w-[393px]",
        showHeader ? "top-40" : "top-10",
      )}
    >
      {/* Desktop Navigation */}
      <nav className="mb-0 hidden space-y-2 lg:block">
        {links.map((link, index) => {
          const href = link.startsWith("/") ? link : `/${link}`;
          const label = labels[index] ?? link;
          const active = pathName === href;

          const Icon = getIcon(link);

          return (
            <Link
              key={link}
              href={href}
              prefetch={false}
              className={`group flex items-center gap-4 rounded-md p-4 text-base transition duration-200 ${
                active
                  ? "bg-site-primary-500 font-semibold text-white"
                  : "text-site-gray-500 hover:bg-site-gray-100"
              }`}
            >
              <Icon
                className={`h-5 w-5 transition duration-300 ${
                  active ? "fill-white" : "fill-site-gray-600"
                }`}
              />
              {label}
            </Link>
          );
        })}
      </nav>

      {/* Mobile Dropdown */}
      <div className="block lg:hidden">
        <Select value={activeLink} onValueChange={handleMobileChange}>
          <SelectTrigger className="text-site-gray-500 w-full rounded-md px-4 py-5 text-base">
            <SelectValue placeholder="Select Page" />
          </SelectTrigger>

          <SelectContent className="rounded-md border border-gray-200 bg-white shadow-lg">
            {links.map((link, index) => (
              <SelectItem key={link} value={link}>
                {labels[index] ?? link}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>
    </div>
  );
}
