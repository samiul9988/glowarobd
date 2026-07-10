"use client";

import { AnimatePresence, motion } from "framer-motion";
import { X } from "lucide-react";
import { useState } from "react";
import { BiSolidMessageSquareDetail } from "react-icons/bi";
import { IoCall, IoLogoWhatsapp } from "react-icons/io5";
import { RiCustomerService2Fill } from "react-icons/ri";
import { EmainIcon } from "./icons/icon-library";

const SupportStickyButton = () => {
  const [open, setOpen] = useState(false);

  const toggleMenu = () => setOpen((prev) => !prev);

  const buttons = [
    {
      id: 1,
      label: "WhatsApp",
      icon: <IoLogoWhatsapp className="text-[26px] md:text-[34px]" />,
      color: "bg-[#29A71A]",
      link: "https://wa.me/+8801714117604",
    },
    {
      id: 2,
      label: "Email",
      icon: <EmainIcon />,
      color: "bg-site-primary",
      link: "mailto:info@emartwayskincare.com.bd",
    },
    {
      id: 3,
      label: "Call",
      icon: <IoCall className="text-[24px] md:text-[32px]" />,
      color: "bg-[#E95285]",
      link: "tel:+8809666767604",
    },
  ];

  return (
    <AnimatePresence>
      <motion.div className="fixed right-2 bottom-18 z-50 flex flex-col items-end space-y-2 p-1 md:right-6 md:bottom-6">
        <AnimatePresence>
          {open &&
            buttons.map((btn, index) => (
              <motion.a
                key={btn.id}
                href={btn.link}
                target="_blank"
                rel="noopener noreferrer"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: 20 }}
                transition={{ delay: index * 0.05 }}
                className={`${btn.color} flex h-12 w-12 items-center justify-center rounded-full text-white shadow-lg md:h-14 md:w-14`}
                aria-label={btn.label}
              >
                {btn.icon}
              </motion.a>
            ))}
        </AnimatePresence>

        <motion.button
          whileTap={{ scale: 0.9 }}
          onClick={toggleMenu}
          className={`flex h-12 w-12 cursor-pointer items-center justify-center rounded-full text-white shadow-lg md:h-14 md:w-14 ${
            open ? "bg-gray-700" : "bg-site-primary-700"
          }`}
          aria-label="Support menu toggle"
        >
          <motion.div
            key={open ? "close" : "support"}
            initial={{ rotate: 0, scale: 0.8, opacity: 0 }}
            animate={{ rotate: 360, scale: 1, opacity: 1 }}
            exit={{ rotate: -180, opacity: 0 }}
            transition={{ duration: 0.3 }}
            className="flex items-center justify-center"
          >
            {open ? (
              <X className="text-[22px] md:text-[28px]" />
            ) : (
              <RiCustomerService2Fill className="text-[25px] md:text-[32px]" />
            )}
          </motion.div>
        </motion.button>
      </motion.div>
    </AnimatePresence>
  );
};

export default SupportStickyButton;
