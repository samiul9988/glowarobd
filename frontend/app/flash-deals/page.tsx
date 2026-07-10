import Container from "@/components/Container";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import { metaData } from "@/metadata/staticMetaData";
import { Metadata } from "next";
import FlashDealZone from "./_sections/FlashDealZone";
import { Zap } from "lucide-react";
import Link from "next/link";

const FlashDeal = async () => {
  // Fetch all flash deals
  const flashDealRes = await fetcher<FlashDealResponse>("/flash-deals", {
    baseUrl: apiBaseUrl,
    next: {
      revalidate: REVALIDATE_TIME,
    },
  });

  if (!flashDealRes || flashDealRes.data.length === 0) {
    return (
      <section className="py-10 md:py-16 lg:py-20">
        <div className="flex h-[60vh] flex-col items-center justify-center space-y-4 text-center">
          {/* Icon */}
          <div className="bg-site-primary-100 animate-bounce rounded-full p-4">
            <Zap className="text-site-primary h-8 w-8 md:h-10 md:w-10" />
          </div>

          {/* Title */}
          <h2 className="text-site-gray-800 text-xl md:text-2xl">
            No Flash Deals Available!
          </h2>

          {/* Subtitle */}
          <p className="text-site-gray-500 max-w-md text-sm md:text-base">
            We&apos;re preparing the next exciting offers for you. Please check
            back soon to grab amazing deals and discounts!
          </p>

          {/* Button */}
          <Link
            className="text-site-primary-500 border-site-primary-500 bg-site-primary-50 hover:bg-site-primary-500 mt-6 rounded-xl border px-3 py-2 text-center text-sm font-semibold transition-colors hover:text-white md:mt-12"
            href="/"
          >
            Start Shopping
          </Link>
        </div>
      </section>
    );
  }

  const flashDeals = flashDealRes.data;

  // Fetch products for each category
  const productsByFlashDeal = await Promise.all(
    flashDeals.map(async (deal) => {
      const dealRes = await fetcher<DealResponse>(
        `/flash-deal-products/${deal.id}`,
        {
          baseUrl: apiBaseUrl,
          next: {
            revalidate: REVALIDATE_TIME,
          },
        },
      );

      return {
        flashDeal: deal,
        products: dealRes?.data,
      };
    }),
  );

  return (
    <section className="py-7 md:py-10 lg:py-12">
      <Container>
        <FlashDealZone productsByFlashDeal={productsByFlashDeal} />
      </Container>
    </section>
  );
};

export default FlashDeal;

export const metadata: Metadata = {
  title: metaData.flashDeals.title,
  description: metaData.flashDeals.description,
};
