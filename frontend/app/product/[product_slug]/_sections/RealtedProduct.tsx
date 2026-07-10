import Container from "@/components/Container";
import SectionHeader from "@/components/SectionHeader";
import RealResultSlider from "@/components/sliders/RealResultSlider";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

interface RelatedVideoRes {
  status: boolean;
  data: Video[];
  icon: "string";
}

const RealtedProduct = async ({
  productId,
  icon,
}: {
  productId: number;
  icon: string;
}) => {
  const videoRes = await cacheableFetcher<RelatedVideoRes>(
    `/products/related-videos/${productId}`,
    { revalidate: 210 },
  );

  if (!videoRes?.data?.length) return null;

  return (
    <section className="py-10 md:py-[60px] lg:py-[100px]">
      <Container>
        <SectionHeader
          title="Experience the Product"
          icon={imageBaseHostUrl + icon}
          link=""
        />

        {videoRes.data && (
          <RealResultSlider isProductPage={true} data={videoRes.data} />
        )}
      </Container>
    </section>
  );
};

export default RealtedProduct;
