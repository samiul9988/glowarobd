// import { apiFakeBaseUrl } from "@/config/apiConfig";
// import { fetcher } from "@/lib/fetcher";

const BabyBathtubProductsSection = async () => {
  // const data = await fetcher<BabyProductsType>("/baby-bathtub-products", {
  //   baseUrl: apiFakeBaseUrl,
  // });

  return (
    <section>
      {/* <Container>
        {data && data.products.length > 0 ? (
          <ProductTopArrowSlider {...data} />
        ) : (
          <div className="col-span-2 md:col-span-4 text-center py-10">
            <p className="text-gray-500 text-lg">
              No products available right now.
            </p>
          </div>
        )}
      </Container> */}
    </section>
  );
};

export default BabyBathtubProductsSection;
