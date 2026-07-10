"use client";

import { GoogleAnalytics, GoogleTagManager } from "@next/third-parties/google";

interface Props {
  isEnableGoogleAnalytics: boolean;
  googleAnalyticsId: string;
  isEnableGoogleTagManager: boolean;
  googleTagManagerId: string;
}
export default function GoogleAnalyticsClient({
  isEnableGoogleAnalytics,
  googleAnalyticsId,
  isEnableGoogleTagManager,
  googleTagManagerId,
}: Props) {
  return (
    <>
      {isEnableGoogleAnalytics && googleAnalyticsId && (
        <GoogleAnalytics gaId={googleAnalyticsId} />
      )}
      {isEnableGoogleTagManager && googleTagManagerId && (
        <GoogleTagManager gtmId={googleTagManagerId} />
      )}
    </>
  );
}
