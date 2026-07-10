"use client";

import { cn } from "@/lib/utils";
import { motion } from "framer-motion";
import Link from "next/link";
import { ReactNode, useState } from "react";

type HoverItem = {
  label: string;
  icon?: ReactNode;
  href?: string; // if provided -> render Link
  onClick?: () => void; // if provided -> render Button
};

interface HoverHighlightGroupProps {
  items: HoverItem[];
  className?: string;
}

export function HoverHighlightGroup({
  items,
  className,
}: HoverHighlightGroupProps) {
  const [hovered, setHovered] = useState<number | null>(null);

  return (
    <div
      role="tablist"
      className={cn(
        "relative inline-flex items-center rounded-md border bg-background/60 backdrop-blur-sm h-12 p-1",
        className
      )}
    >
      {hovered !== null && (
        <motion.div
          layoutId="hover-highlight"
          className="absolute top-1 bottom-1 rounded-md bg-background shadow-sm border border-border/60"
          initial={false}
          animate={{
            left: `calc(${hovered} * 100% / ${items.length})`,
            width: `calc(100% / ${items.length})`,
          }}
          transition={{ type: "spring", stiffness: 300, damping: 30 }}
        />
      )}

      {items.map((item, i) => {
        const commonProps = {
          onMouseEnter: () => setHovered(i),
          onMouseLeave: () => setHovered(null),
          className: cn(
            "relative z-10 flex-1 inline-flex items-center justify-center gap-2 px-4 text-sm font-medium transition-colors",
            "text-muted-foreground hover:text-foreground"
          ),
        };

        if (item.href) {
          return (
            <Link
              prefetch={false}
              key={item.label}
              href={item.href}
              {...commonProps}
            >
              {item.label}
            </Link>
          );
        }

        return (
          <button key={item.label} onClick={item.onClick} {...commonProps}>
            {item.label}
          </button>
        );
      })}
    </div>
  );
}
