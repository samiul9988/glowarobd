"use client";

import Lenis from "lenis";
import { ReactNode, useEffect, useRef } from "react";
import { usePathname } from "next/navigation";

export default function LenisProvider({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const lenisRef = useRef<Lenis | null>(null);

  useEffect(() => {
    // Prevent multiple instances
    if (lenisRef.current) return;

    const lenis = new Lenis({
      duration: 1.2,
      easing: (t) => 1 - Math.pow(1 - t, 3),
      smoothWheel: true,
      orientation: "vertical",
      gestureOrientation: "vertical",
    });

    lenisRef.current = lenis;

    function raf(time: number) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);

    return () => {
      lenis.destroy();
    };
  }, []);

  // Scroll to top on route change
  useEffect(() => {
    const lenis = lenisRef.current;
    if (lenis) {
      // Use a small timeout to ensure content is rendered
      setTimeout(() => lenis.scrollTo(0, { immediate: true }), 50);
    }
  }, [pathname]);

  return <>{children}</>;
}
