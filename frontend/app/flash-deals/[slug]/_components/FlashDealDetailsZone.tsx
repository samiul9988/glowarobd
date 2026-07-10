"use client";

import Container from "@/components/Container";
import { apiBaseUrl, imageBaseHostUrl } from "@/config/apiConfig";
import { useIntersectionObserver } from "@/hooks/useIntersectionObserver";
import { fetcher } from "@/lib/fetcher";
import { useInfiniteQuery } from "@tanstack/react-query";
import { AnimatePresence, motion } from "framer-motion";
import { Loader2 } from "lucide-react";
import Image from "next/image";
import { useEffect, useMemo } from "react";
import FlashDealDetailsProductGrid from "./FlashDealDetailsProductGrid";
import { useCountdown } from "@/hooks/useCountdown";

interface Props {
  res: DealDetailsResponse;
  flashDeal: FlashDealType;
}

const FlashDealDetailsZone = ({ res, flashDeal }: Props) => {



  // Infinite Query setup
  const { data, fetchNextPage, hasNextPage, isFetchingNextPage, isFetching } =
    useInfiniteQuery({
      queryKey: ["flash-deal-products", flashDeal.slug],
      queryFn: async ({ pageParam = 1 }) => {
        const response = await fetcher<DealDetailsResponse>(
          `/flash-deal-products/${flashDeal.slug}?page=${pageParam}`,
          { baseUrl: apiBaseUrl },
        );
        return response;
      },
      getNextPageParam: (lastPage) => {
        if (!lastPage?.meta) return undefined;
        const { current_page, last_page } = lastPage.meta;
        return current_page < last_page ? current_page + 1 : undefined;
      },
      initialPageParam: 1,
      initialData: {
        pages: [res],
        pageParams: [1],
      },
    });

  // Intersection Observer for auto infinite scroll
  const { ref, isIntersecting } = useIntersectionObserver<HTMLDivElement>({
    threshold: 0.1,
  });

  useEffect(() => {
    if (isIntersecting && hasNextPage && !isFetchingNextPage) {
      fetchNextPage();
    }
  }, [isIntersecting, hasNextPage, isFetchingNextPage, fetchNextPage]);

  // Combine all loaded products safely
  const allProducts =
    data?.pages.flatMap((page) => (page ? page.data : [])) ?? [];

  // First load loading state
  if (isFetching && !data) {
    return (
      <div className="flex items-center justify-center py-12">
        <Loader2 className="h-6 w-6 animate-spin text-gray-500" />
      </div>
    );
  }

  // If no products, render nothing
  if (!allProducts.length) return null;

  const endDate = useMemo(() => {
    return new Date(Number(flashDeal.date) * 1000);
  }, [flashDeal]);

  const { timeLeft, isExpired } = useCountdown(endDate);

  return (
    <section className="py-7 md:py-10 lg:py-12">
      <Container>
        {/* Banner */}
        <div className="relative mb-4 h-[180px] w-full overflow-hidden rounded-lg md:mb-8 md:h-[227px]">
          <Image
            src={imageBaseHostUrl + flashDeal.banner}
            alt="Flash Deal Banner"
            fill
            className="object-cover object-center"
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 100vw, 1200px"
            placeholder="blur"
            blurDataURL="/blur-placeholder.jpg"
            loading="lazy"
          />
        </div>
        {!isExpired && (
          <div className="mb-5 flex items-center justify-center gap-4 md:justify-start">
            <div className="flex flex-col items-center space-y-1">
              <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
                {timeLeft.days}
              </span>
              <span className="text-site-gray-400 text-xs font-normal uppercase">
                Days
              </span>
            </div>
            <div className="flex flex-col items-center space-y-1">
              <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
                {timeLeft.hours}
              </span>
              <span className="text-site-gray-400 text-xs font-normal uppercase">
                hours
              </span>
            </div>

            <div className="flex flex-col items-center space-y-1">
              <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
                {timeLeft.minutes}
              </span>
              <span className="text-site-gray-400 text-xs font-normal uppercase">
                min
              </span>
            </div>
            <div className="flex flex-col items-center space-y-1">
              <span className="bg-site-primary-500 grid h-11 w-11 place-content-center rounded-[6px] border border-white text-center text-base font-bold text-white">
                {timeLeft.seconds}
              </span>
              <span className="text-site-gray-400 text-xs font-normal uppercase">
                sec
              </span>
            </div>
          </div>
        )}

        {/* Header */}
        <div className="relative flex w-full justify-center overflow-clip rounded-tl-[10px] rounded-tr-[10px] bg-[url('/images/flashDeal-banner-bg.png')] bg-cover px-10 md:justify-between">
          <div className="z-30 flex h-[98px] items-center justify-center text-center">
            <h2 className="text-site-gray-900 text-[32px] leading-8 md:text-[40px] md:leading-11">
              {flashDeal.title}
            </h2>
          </div>
          <div className="flex items-center gap-4">
            <Image
              src="/images/baby.gif"
              alt="Girl"
              width={166}
              height={134}
              className="absolute top-0 right-[6%] hidden md:block"
            />
            <Image
              src="/images/traffic-light.gif"
              alt="Traffic Light"
              width={60}
              height={60}
              className="hidden md:block"
            />
          </div>
        </div>

        {/* Products Grid */}
        <div className="bg-site-primary-900 w-full rounded-br-[10px] rounded-bl-[10px] p-5 md:p-8">
          <AnimatePresence mode="wait">
            <motion.div
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              transition={{ duration: 0.25 }}
            >
              <FlashDealDetailsProductGrid
                res={{ ...res, data: allProducts }}
              />
            </motion.div>
          </AnimatePresence>

          {/* Infinite scroll trigger */}
          {hasNextPage && (
            <div ref={ref} className="mt-6 flex justify-center">
              {isFetchingNextPage && (
                <div className="flex items-center gap-2 text-white">
                  <Loader2 className="h-6 w-6 animate-spin text-white" />
                  Loading more...
                </div>
              )}
            </div>
          )}
        </div>
      </Container>
    </section>
  );
};

export default FlashDealDetailsZone;
