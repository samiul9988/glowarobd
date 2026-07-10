"use client";

import { useShowHeader } from "@/store/useShowHeader";
import { motion } from "framer-motion";
import React, { useEffect, useState } from "react";
import Container from "../Container";

interface Props {
  children: React.ReactNode;
}

const HeaderWrapper = ({ children }: Props) => {
  const [lastScrollY, setLastScrollY] = useState(0);
  const { showHeader, setShowHeader } = useShowHeader();

  useEffect(() => {
    const handleScroll = () => {
      const currentScrollY = window.scrollY;

      if (currentScrollY > 100) {
        // Hide/show only after 200px
        if (currentScrollY > lastScrollY) {
          setShowHeader(false);
        } else {
          setShowHeader(true);
        }
      } else {
        // Before 200px → always visible
        setShowHeader(true);
      }

      setLastScrollY(currentScrollY);
    };

    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, [lastScrollY]);

  return (
    <motion.header
      className="sticky top-0 z-50 bg-white pt-2.5"
      initial={{ y: 0 }}
      animate={{ y: showHeader ? 0 : -145 }}
      transition={{ duration: 0.4, ease: "easeInOut" }}
    >
    <Container>
      {children}
    </Container>
    </motion.header>
  );
};

export default HeaderWrapper;
