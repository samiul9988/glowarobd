import Container from "@/components/Container";
import TestimonialSlider from "@/components/sliders/TestimonialSlider";
import { apiLiveBaseUrl } from "@/config/apiConfig";
import { HOMEPAGE_SECTION_REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

const TestimonialSection = async () => {
  // Fetch all flash deals
  const testimonialRes = await cacheableFetcher<TestimonialResponse>(
    "/reviews/featured?limit=12",
    {
      revalidate: HOMEPAGE_SECTION_REVALIDATE_TIME,
    },
  );

  if (!testimonialRes || testimonialRes.data.length === 0) {
    return null;
  }
  const testimonials = testimonialRes.data;

  return (
    <section className="pt-10 md:py-[60px] lg:py-[100px]">
      <Container>
        <TestimonialSlider testimonials={testimonials} name="Testimonials" />
      </Container>
    </section>
  );
};

export default TestimonialSection;
