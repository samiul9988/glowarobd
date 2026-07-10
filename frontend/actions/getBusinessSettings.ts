import { cacheableFetcher } from "@/lib/cacheableFetcher";

const REVALIDATE_TIME = 210; // 1 hour

export async function getBusinessSettings() {
  const data = await cacheableFetcher<ApiResponseType<BusinessDataType[]>>(
    "/business-settings",
    {
      revalidate: 3600,
    },
  );
  return data?.data || [];
}
