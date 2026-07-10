import FlashDealSkeleton from "@/components/skeleton/FlashDealSkeleton";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { fetcher } from "@/lib/fetcher";
import { notFound } from "next/navigation";
import { Suspense } from "react";
import FlashDealDetailsZone from "./_components/FlashDealDetailsZone";

interface Props {
  params: Promise<{
    slug: string;
  }>;
}

export default async function FlashDealsDetails({ params }: Props) {
  const { slug } = await params;

  const res = await fetcher<DealDetailsResponse>(
    `/flash-deal-products/${slug}`,
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  const flashDealRes = await cacheableFetcher<FlashDealResponse>(
    `/flash-deals`,
    {
      revalidate: REVALIDATE_TIME,
    },
  );

  const flashDeal = flashDealRes?.data.find((item) => item.slug === slug);

  // Check if data is available
  if (!res || res.data.length === 0 || !flashDeal) {
    return notFound();
  }

  return (
    <>
      <Suspense fallback={<FlashDealSkeleton />}>
        <FlashDealDetailsZone res={res} flashDeal={flashDeal} />
      </Suspense>
    </>
  );
}
