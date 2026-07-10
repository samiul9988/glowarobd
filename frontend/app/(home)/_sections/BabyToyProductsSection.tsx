import Container from "@/components/Container";
import ProductTopArrowSlider from "@/components/sliders/ProductTopArrowSlider";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";

const BabyToyProductsSection = async ({ name, id }: CategoryType) => {
  const data = await fetcher<ApiResponseType<ProductType[]>>(
    `/products/category/${id}?page=1&limit=12`,
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    }
  );

  return (
    <section className="py-5 md:py-12">
      <Container>
        {data && data.data.length > 0 ? (
          <ProductTopArrowSlider data={data.data} name={name} />
        ) : (
          <div className="col-span-2 md:col-span-4 text-center py-10">
            <p className="text-gray-500 text-lg">
              No products available right now.
            </p>
          </div>
        )}
      </Container>
    </section>
  );
};

export default BabyToyProductsSection;
