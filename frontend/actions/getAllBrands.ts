import { cacheableFetcher } from "@/lib/cacheableFetcher";

const REVALIDATE_TIME = 210; // 1 hour

export async function getAllBrands() {
  const data = await cacheableFetcher<BrandApiResponse<BrandItemResponse[]>>(
    "/brands?limit=50",
    {
      revalidate: 3600,
    },
  );
  return data || [];
}
