import Container from "@/components/Container";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import SpecialOfferZone from "../_components/SpecialOfferZone";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

const SpecialOfferZoneSection = async () => {
  // Fetch all flash deals
  const flashdealRes = await cacheableFetcher<ApiResponseType<FlashDealType[]>>(
    "/flash-deals",
    {
      baseUrl: apiBaseUrl,
      revalidate: 1, // Cache for 1 hour

    },
  );

  if (!flashdealRes || flashdealRes.data.length === 0) {
    return null;
  }

  const flashdeals = flashdealRes.data;

  // Fetch products for each category
  const productsByFlashDeal = await Promise.all(
    flashdeals.map(async (deal) => {
      const dealRes = await cacheableFetcher<ApiResponseType<ProductType[]>>(
        `/flash-deal-products/${deal.id}`,
        {
          baseUrl: apiBaseUrl,
          revalidate: 300, // Cache for 1 hour

        },
      );

      return {
        flashdeal: deal,
        products: dealRes?.data,
      };
    }),
  );

  return (
    <section className="pb-10 md:pb-[60px] ">
      <Container>
        <SpecialOfferZone productsByFlashDeal={productsByFlashDeal} />
      </Container>
    </section>
  );
};

export default SpecialOfferZoneSection;
