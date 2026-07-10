"use client";

import { imageBaseUrl } from "@/config/apiConfig";
import { motion, useAnimation } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useRef, useState } from "react";

interface Props {
  logos: BrandType[];
  speedPxPerSec?: number; // pixels per second (default 120)
  direction?: "left" | "right"; // scroll direction
}

export default function LogoTicker({
  logos,
  speedPxPerSec = 120,
  direction = "right",
}: Props) {
  const containerRef = useRef<HTMLDivElement | null>(null);
  const firstSetRef = useRef<HTMLDivElement | null>(null);

  const [contentWidth, setContentWidth] = useState(0);
  const [copies, setCopies] = useState(2);

  const controls = useAnimation();

  // Measure width and calculate copies
  useEffect(() => {
    const measure = () => {
      const container = containerRef.current;
      const firstSet = firstSetRef.current;
      if (!container || !firstSet) return;

      const containerW = Math.ceil(container.offsetWidth);
      const setW = Math.ceil(firstSet.offsetWidth);

      if (!setW) return;

      setContentWidth(setW);
      const needed = Math.max(2, Math.ceil(containerW / setW) + 1);
      setCopies(needed);
    };

    measure();

    const ro = new ResizeObserver(measure);
    if (containerRef.current) ro.observe(containerRef.current);
    if (firstSetRef.current) ro.observe(firstSetRef.current);
    window.addEventListener("resize", measure);
    return () => {
      ro.disconnect();
      window.removeEventListener("resize", measure);
    };
  }, [logos]);

  // Start / restart animation when contentWidth, speed, or direction changes
  useEffect(() => {
    if (!contentWidth) return;

    // duration in seconds = total distance / speedPxPerSec
    const duration = contentWidth / Math.max(1, speedPxPerSec);

    const fromX = direction === "right" ? 0 : -contentWidth * (copies - 1);
    const toX = direction === "right" ? -contentWidth : 0;

    controls.set({ x: fromX });
    controls.start({
      x: [fromX, toX],
      transition: {
        duration,
        ease: "linear",
        repeat: Infinity,
        repeatType: "loop",
      },
    });
  }, [contentWidth, speedPxPerSec, direction, controls]);

  const FALLBACK_IMAGE = "/images/placeholder.png";
  // Prevent empty string from being passed to <Image />
  if (!logos || logos.length === 0) return null;

  return (
    <div ref={containerRef} className="w-full overflow-hidden">
      <motion.div
        animate={controls}
        style={{
          display: "flex",
          width: "max-content",
          willChange: "transform",
        }}
      >
        {Array.from({ length: copies }).map((_, copyIndex) => (
          <div
            key={copyIndex}
            ref={copyIndex === 0 ? firstSetRef : undefined}
            className="flex items-center"
          >
            {logos.map(({ logo, slug, name }, i) => (
              <Link prefetch={false} href={`/brand/${slug}`} key={i}>
                <div className="hover:border-site-primary-500 group relative mx-2.5 h-10 w-[90px] flex-shrink-0 overflow-clip rounded-full border-1 border-transparent bg-white/50 p-2 transition-all duration-200 ease-linear md:mx-5 md:h-[60px] md:w-40 md:border-2">
                  <Image
                    src={logo ? imageBaseUrl + logo : FALLBACK_IMAGE}
                    alt={name}
                    fill
                    onError={(e) => {
                      const target = e.currentTarget as HTMLImageElement;
                      target.src = FALLBACK_IMAGE;
                    }}
                    className="object-contain transition-transform duration-200 ease-linear group-hover:scale-110"
                  />
                </div>
              </Link>
            ))}
          </div>
        ))}
      </motion.div>
    </div>
  );
}
