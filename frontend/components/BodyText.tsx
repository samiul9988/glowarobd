// components/typography/BodyText.tsx
import clsx from "clsx";
import React from "react";

interface BodyTextProps {
  children: React.ReactNode;
  className?: string;
  variant?: "one" | "two" | "three" | "four";
}

export default function BodyText({
  children,
  className = "",
  variant = "one",
}: BodyTextProps) {
  const variants = {
    one: "text-site-gray-600 text-base",
    two: "text-[#505050] text-sm",
    three: "text-[#505050] text-xs",
    four: "text-[#505050] text-[10px]",
  };

  return <p className={clsx(variants[variant], className)}>{children}</p>;
}
