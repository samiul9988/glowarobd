// ============================
// API Base URLs
// ============================
export const apiBaseUrl =
  process.env.NEXT_PUBLIC_API_BASE_URL_LIVE ||
  "https://api.glowaro.com/api/v3";

export const apiBaseUrlV2 =
  process.env.NEXT_PUBLIC_API_BASE_URL_LIVE_V2 ||
  "https://api.glowaro.com/api/v2";

export const apiLiveBaseUrl =
  process.env.NEXT_PUBLIC_API_BASE_URL_LIVE_TEST ||
  "https://api.glowaro.com/api/v3";

// ============================
// Image URLs
// ============================
export const imageBaseUrl =
  process.env.NEXT_PUBLIC_API_IMG_URL || "https://api.glowaro.com/";

export const imageBaseHostUrl =
  process.env.NEXT_PUBLIC_IMG_HOST_URL ||
  "https://api.glowaro.com/";

export const defaultImage = "/images/default-image.png";

// ============================
// AI API
// ============================
export const aiApiUrl =
  process.env.NEXT_PUBLIC_AI_API_URL || "https://ai.glowaro.com";

export const useAiApi =
  process.env.NEXT_PUBLIC_USE_AI_API === "true";

// ============================
// Public Site URLs
// ============================
export const publicBaseUrl =
  process.env.NEXT_PUBLIC_BASE_URL || "https://glowaro.com";

export const localhostUrl = "http://localhost:3000";

// ============================
// Site Metadata
// ============================
export const siteName =
  process.env.NEXT_PUBLIC_SITE_NAME || "Glowaro";

export const siteTitle =
  process.env.NEXT_PUBLIC_SITE_TITLE || "Glowaro - Online Beauty Store";

// ============================
// Security & Analytics
// ============================
export const pixelAnalyticsId =
  process.env.NEXT_PUBLIC_PIXEL_ANALYTICS || "83555312407035531";

export const googleTagManager =
  process.env.NEXT_PUBLIC_GOOGLE_TAG_MANAGER || "GTM-M4NWXSR";

export const googleAnalyticsId =
  process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS || "UA-0000000500-0";


export const currencySymbol = '৳'