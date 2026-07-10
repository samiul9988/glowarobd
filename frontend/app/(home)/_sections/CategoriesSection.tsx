import Container from "@/components/Container";
import TopCategoriesSlider from "@/components/sliders/TopCategoriesSlider";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { fetcher } from "@/lib/fetcher";

interface Icon{
    app:string;
    mobile:string;
    web:string
}
interface CategoryType {
  id: number;
  name: string;
  slug: string;
  featured_icons: Icon;
}

interface ApiResponse {
  data: CategoryType[];
  status: number;
  success: boolean;
}

const CategoriesSection = async () => {
  const res = await cacheableFetcher<ApiResponse>("/categories/featured?limit=10", {
    baseUrl: apiBaseUrl,
    revalidate: 300,
  });
  if (!res || res.data.length === 0) {
    return null;
  }

  return (
    <section className="py-5 lg:py-7">
      <Container className="">
        {res && res.data.length > 0 ? (
          <TopCategoriesSlider data={res.data} />
        ) : (
          <div className="col-span-2 py-10 text-center md:col-span-4">
            <p className="text-lg text-gray-500">
              No categories available right now.
            </p>
          </div>
        )}
      </Container>
      {/* test */}
    </section>
  );
};

export default CategoriesSection;
