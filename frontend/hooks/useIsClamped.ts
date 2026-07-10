// useIsClamped.tsx
import { useState, useLayoutEffect, RefObject } from "react";

export function useIsClamped(
  ref: RefObject<HTMLElement>,
  maxLines: number = 2,
): boolean {
  const [isClamped, setIsClamped] = useState(false);

  useLayoutEffect(() => {
    const el = ref.current;
    if (!el) return;

    const style = window.getComputedStyle(el);
    const lineHeight = parseFloat(style.lineHeight || "0");
    const maxHeight = lineHeight * maxLines;

    const check = () => {
      if (!el) return;
      const scrollH = el.scrollHeight;
      const clientH = el.clientHeight;
      setIsClamped(scrollH > maxHeight + 1); // +1 to account for rounding
    };

    check();
    window.addEventListener("resize", check);
    return () => {
      window.removeEventListener("resize", check);
    };
  }, [ref, maxLines]);

  return isClamped;
}
