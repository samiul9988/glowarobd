"use client";

import { Badge } from "@/components/ui/badge";
import { Handbag } from "lucide-react";
import dynamic from "next/dynamic";
import CartCount from "./CartCount";

const CartCountWrapper = () => {
  return (
    <>
      <CartCount />
    </>
  );
};

export default CartCountWrapper;
