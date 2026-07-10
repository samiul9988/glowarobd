import Container from "@/components/Container";
import ShopByConcernSlider from "@/components/sliders/ShopByConcernSlider";
import TopCategoriesSlider from "@/components/sliders/TopCategoriesSlider";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { fetcher } from "@/lib/fetcher";

interface CategoryType {
  id: number;
  name: string;
  slug?: string;
  image: string;
  title?:string
}

interface ApiResponse {
  data: CategoryType[];
  status: number;
  success: boolean;
}

const ShopByConcern = async () => {
  const res = await cacheableFetcher<ApiResponse>("/skin-concerns?limit=10", {
    baseUrl: apiBaseUrl,
    revalidate: 300,

  });

  if (!res || res.data.length === 0) {
    return null;
  }

  return (
    <section className="pb-10 md:pb-[60px] ">
      <Container className="">
        {res && res.data.length > 0 && (
          <ShopByConcernSlider data={res.data} />
        )}
      </Container>
    </section>
  );
};

export default ShopByConcern;
