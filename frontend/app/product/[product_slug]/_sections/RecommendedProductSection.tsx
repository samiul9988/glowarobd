import Container from "@/components/Container";
import ProductTopArrowSlider from "@/components/sliders/ProductTopArrowSlider";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { fetcher } from "@/lib/fetcher";

const RecommendedProductSection = async ({
  productId,
}: {
  productId: number;
}) => {
  const data = await cacheableFetcher<ApiResponseType<ProductType[]>>(
    `/products/related/${productId}`,
    {
      baseUrl: apiBaseUrl,
      revalidate: 300,
    }
  );

  if (!data || data.data.length === 0) {
    return null;
  }

  return (
    <section className="py-5 md:py-12 overflow-clip">
      <Container>
        <ProductTopArrowSlider
          {...data}
          name="Similar Products"
          icon="/images/similarproduct.png"
        />
      </Container>
    </section>
  );
};

export default RecommendedProductSection;
