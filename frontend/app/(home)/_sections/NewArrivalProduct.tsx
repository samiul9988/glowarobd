import { getBusinessSettings } from "@/actions/getBusinessSettings";
import Container from "@/components/Container";
import ProductTopArrowSlider from "@/components/sliders/ProductTopArrowSlider";
import { apiBaseUrl, imageBaseHostUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { fetcher } from "@/lib/fetcher";

const NewArrivalProduct = async () => {
  const data = await cacheableFetcher<ApiResponseType<ProductType[]>>(
    "/products/new-arrivals?sort_by=rand&limit=20",
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  // If no data
  if (!data || data.data.length === 0) {
    return ;
  }

  const bs = await getBusinessSettings();

  const isSectionEnabled = bs.filter(
    (item) => item.type === "new_arrival_products",
  )[0]?.value;

  // If section is disabled
  if (Number(isSectionEnabled) !== 1) {
    return null;
  }

  // If no data
  if (!data || data.data.length === 0) {
    return null;
  }

  return (
    <section className="pb-10 md:pb-[60px] ">
      <Container>
        <ProductTopArrowSlider
          {...data}
          name={data.title || ""}
          icon={"/images/new-arrival.png"}
          link="/category/new-arrivals"
        />
      </Container>
    </section>
  );
};

export default NewArrivalProduct;
