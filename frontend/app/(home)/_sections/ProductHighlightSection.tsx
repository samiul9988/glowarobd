import Container from "@/components/Container";
import ProductHighlightSlider from "@/components/sliders/ProductHighlightSlider";
import { HOMEPAGE_SECTION_REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

const ProductHighlightSection = async () => {
  const res = await cacheableFetcher<HighlightResponse>("/highlights", {
    revalidate: HOMEPAGE_SECTION_REVALIDATE_TIME,
  });

  if (!res || res.data.length === 0) {
    return null;
  }

  return (
    <section className="bg-site-gray-50 highlight-section md:pt-[60px] lg:pt-[100px]">
      <Container>
        {res && res.data.length > 0 ? (
          <ProductHighlightSlider data={res.data} />
        ) : (
          <div className="col-span-2 py-10 text-center md:col-span-4">
            <p className="text-lg text-gray-500">
              No categories available right now.
            </p>
          </div>
        )}
      </Container>
    </section>
  );
};

export default ProductHighlightSection;
