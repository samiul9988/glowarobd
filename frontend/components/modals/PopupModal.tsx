"use client";

import { Dialog, DialogContent } from "@/components/ui/dialog";
import { api } from "@/lib/axios";
import { useQuery } from "@tanstack/react-query";
import { X } from "lucide-react";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";

interface ApiResponseType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

const PopupModal = () => {
  const [open, setOpen] = useState(false);

  const { data } = useQuery({
    queryKey: ["popup"],
    queryFn: async () => {
      const res = await api.get("/business-settings");
      return res.data.data as ApiResponseType[];
    },
  });

  const appStoreLink =
    data?.find((item) => item.type === "app_store_link")?.value ?? "";
  const playStoreLink =
    data?.find((item) => item.type === "play_store_link")?.value ?? "";
  const showWebsitePopup = data?.find(
    (item) => item.type === "show_website_popup",
  )?.value;
  const websitePopupImage =
    data?.find((item) => item.type === "app_popup_image")?.image_url ?? "";

  useEffect(() => {
    const lastClosed = localStorage.getItem("Glowaro_popupClosedAt");
    const now = Date.now();

    // Show popup only if not closed within the last 24h
    if (!lastClosed || now - Number(lastClosed) > 24 * 60 * 60 * 1000) {
      const timer = setTimeout(() => setOpen(true), 2000);
      return () => clearTimeout(timer);
    }
  }, []);

  const handleOpenChange = (isOpen: boolean) => {
    if (!isOpen) {
      localStorage.setItem("Glowaro_popupClosedAt", Date.now().toString());
    }
    setOpen(isOpen);
  };

  return (
    <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogContent className="mx-auto w-11/12 overflow-clip rounded-[10px] border-0 bg-transparent p-0 focus:outline-none sm:max-w-[796px] [&>button]:hidden">
       {websitePopupImage && 
        <Image
          src={websitePopupImage}
          alt="Popup"
          width={0}
          height={0}
          sizes="100vw"
          className="aspect-square h-auto w-full object-contain"
          priority
        />
       }
        

        {/* Close button now calls handleOpenChange */}
        <div
          className="group absolute top-3 right-3 grid h-7 w-7 cursor-pointer place-content-center rounded-full bg-white transition-colors hover:bg-red-500"
          onClick={() => handleOpenChange(false)}
        >
          <X size={20} className="text-site-gray-700 group-hover:text-white" />
        </div>

        {/* App download section */}
        <div className="absolute right-5 bottom-5 flex items-center gap-3">
          <Link
            href={appStoreLink}
            target="_blank"
            className="transition-transform hover:scale-105"
          >
            <Image
              src="/images/footer/apple-store.png"
              alt="App Store"
              width={0}
              height={0}
              className="h-[32px] w-[96px] object-contain sm:h-[36px] sm:w-[108px] md:h-[40px] md:w-[118px]"
              loading="lazy"
            />
          </Link>
          <Link
            href={playStoreLink}
            target="_blank"
            className="transition-transform hover:scale-105"
          >
            <Image
              src="/images/footer/play-store.png"
              alt="Play Store"
              width={0}
              height={0}
              className="h-[32px] w-[96px] object-contain sm:h-[36px] sm:w-[108px] md:h-[40px] md:w-[118px]"
              loading="lazy"
            />
          </Link>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default PopupModal;
