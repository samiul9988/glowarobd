import axios from "axios";

// Always use the original API for client-side operations (cart, wishlist, auth, checkout).
// SSR data fetching (categories, brands, products) is handled by fetcher/cacheableFetcher
// which use the AI adapter when NEXT_PUBLIC_USE_AI_API="true".
export const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL_LIVE,
  headers: {
    source: "web",
  },
});
