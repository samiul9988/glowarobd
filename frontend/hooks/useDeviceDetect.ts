"use client";

import { useEffect, useState } from "react";

export type DeviceInfo = {
  type: "android" | "ios" | "desktop" | "unknown";
  isAndroid: boolean;
  isIOS: boolean;
  isMobile: boolean;
  isDesktop: boolean;
};

export default function useDeviceDetect(): DeviceInfo {
  const [device, setDevice] = useState<DeviceInfo>({
    type: "unknown",
    isAndroid: false,
    isIOS: false,
    isMobile: false,
    isDesktop: false,
  });

  useEffect(() => {
    if (typeof navigator === "undefined") return;

    const ua = navigator.userAgent || navigator.vendor || window.opera;

    const isAndroid = /android/i.test(ua);

    // Improve iOS / iPad detection:
    const isIOS =
      (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) ||
      // additional: iPadOS reports as Mac but with touchpoints
      (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

    const isMobile = isAndroid || isIOS;
    const isDesktop = !isMobile;

    const type: DeviceInfo["type"] = isAndroid
      ? "android"
      : isIOS
        ? "ios"
        : isDesktop
          ? "desktop"
          : "unknown";

    setDevice({ type, isAndroid, isIOS, isMobile, isDesktop });
  }, []);

  return device;
}
