import Container from "@/components/Container";
import SectionHeader from "@/components/SectionHeader";
import { ShowByCategoryTab } from "@/components/tabs/ShowByCategoryTab";
import { apiBaseUrl, apiBaseUrlV2 } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import { fetcher } from "@/lib/fetcher";
import { cache } from "react";

const ShopByCategorySection = async () => {
  // Fetch all categories
  const categoryRes = await fetcher<ApiResponseType<CategoryType[]>>(
    "/home-categories",
    {
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  if (!categoryRes || categoryRes.data.length === 0) {
    return null;
  }

  const categories = categoryRes.data;

  // Fetch products for each category
  const productsByCategory = await Promise.all(
    categories.map(async (cat) => {
      const productRes = await cacheableFetcher<ApiResponseType<ProductType[]>>(
        `/products/category/${cat.id}?page=1&limit=12`,
        {
          baseUrl: apiBaseUrl,
          next: {
            revalidate: REVALIDATE_TIME,
          },
        },
      );
      return {
        category: cat,
        products: productRes?.data,
      };
    }),
  );

  return (
    <section className="py-5 lg:py-7 ">
      <Container className="">
        <div className="bg-site-primary-50 px-3 md:px-5 py-5 md:py-7 rounded-xl">
            <SectionHeader
            title="Shop By Category"
            className="md:text-center md:justify-center"
            />

            {/* Tab */}
            <ShowByCategoryTab data={productsByCategory} />
            
        </div>
      </Container>
    </section>
  );
};

export default ShopByCategorySection;
