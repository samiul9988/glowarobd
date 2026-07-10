import { apiBaseUrl } from "@/config/apiConfig";

export async function cacheableFetcher<T>(
  url: string,
  options: RequestInit & {baseUrl?: string, revalidate?: number } = {},
): Promise<T | null> {
  const { baseUrl = apiBaseUrl, revalidate, ...fetchOptions } = options;
  const finalUrl = `${baseUrl}${url}`;

  try {
    const res = await fetch(finalUrl, {
      ...fetchOptions,
      headers: {
        "Content-Type": "application/json",
        ...(fetchOptions.headers || {}),
      },
      next: revalidate ? { revalidate } : undefined,
    });

    const data = await res.json();
    return data as T;
  } catch (error) {
    return null;
  }
}

// example of usees
// const featuredProducts = await cacheableFetcher<Product[]>("/products/featured", {
//   revalidate: 60,
// });
