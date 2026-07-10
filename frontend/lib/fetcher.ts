import { apiBaseUrl } from "@/config/apiConfig";
import { getServerSession } from "./getServerSession";
import { getAccessToken } from "./getAccessToken";

export type FetcherOptions = RequestInit & {
  baseUrl?: string;
  next?: NextFetchRequestConfig;
};

export async function fetcher<T>(
  url: string,
  options: FetcherOptions = {},
): Promise<T | null> {
  const { baseUrl, next, ...fetchOptions } = options;

  const userData = await getServerSession();
  const token = getAccessToken();
  const finalUrl = `${baseUrl ?? apiBaseUrl}${url}`;
  // ---- Conditional header building ----
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    source: "web",
    ...(fetchOptions.headers as Record<string, string>),
  };

  if (userData?.id) headers["uid"] = String(userData.id);
  if (token) headers["Authorization"] = `Bearer ${token}`;

  try {
    const res = await fetch(finalUrl, {
      ...fetchOptions,
      headers,
      // Enable ISR-like caching for 1 hour
      next: next ?? { revalidate: 3600 },
    });
    const data = await res.json();
    return data as T;
  } catch (error) {
    console?.error("Fetcher Error:========",finalUrl, error);
    return null;
  }
}
