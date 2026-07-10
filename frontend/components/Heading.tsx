// components/typography/Heading.tsx
import clsx from "clsx";
import React from "react";

interface HeadingProps {
  children: React.ReactNode;
  className?: string;
  variant?: "display1" | "display2" | "h1" | "h2" | "h3" | "h4" | "h5" | "h6";
}

export default function Heading({
  children,
  className = "",
  variant = "h1",
}: HeadingProps) {
  const baseStyle = "  text-site-gray-800 ";
  const variants = {
    display1: "text-[83px] leading-[88px]",
    display2: "text-[75px] leading-[80px]",
    h1: "text-[60px] leading-[64px]",
    h2: "text-[52px] leading-[56px]",
    h3: "text-[40px] leading-[44px]",
    h4: "text-[32px] leading-[36px]",
    h5: "text-[26px] leading-[30px]",
    h6: "text-xl lg:text-[23px] leading-[27px]",
  };

  const Tag: React.ElementType =
    variant === "display1" || variant === "display2" ? "h1" : variant;

  return (
    <Tag className={clsx(baseStyle, variants[variant], className)}>
      {children}
    </Tag>
  );
}
