import { apiBaseUrl } from "@/config/apiConfig";

export async function cacheableFetcher<T>(
  url: string,
  options: RequestInit & {baseUrl?: string, revalidate?: number, next?: { revalidate?: number }} = {},
): Promise<T | null> {
  const { baseUrl = apiBaseUrl, revalidate, next, ...fetchOptions } = options;
  const finalUrl = `${baseUrl}${url}`;
  const revalidateTime = revalidate ?? next?.revalidate;

  try {
    const res = await fetch(finalUrl, {
      ...fetchOptions,
      headers: {
        "Content-Type": "application/json",
        source: "web",
        ...(fetchOptions.headers || {}),
      },
      next: revalidateTime ? { revalidate: revalidateTime } : undefined,
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
