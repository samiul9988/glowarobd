import Container from "@/components/Container";
import HeroSlider from "@/components/sliders/HeroSlider";
import { apiBaseUrl } from "@/config/apiConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

interface SliderType {
  photo: string;
  photo_web: string;
}

interface ApiResponse {
  data: SliderType[];
  version: string;
  success: boolean;
  status: number;
}
const HeroSection = async () => {
  const res = await cacheableFetcher<ApiResponse>("/sliders", {
    baseUrl: apiBaseUrl,
    next: {
      revalidate: 300,
    },
  });

  if (!res || res.data.length === 0) {
    return null;
  }

  return (
    <section className="hero-section mt-2 mb-2 md:mt-6 md:mb-4">
      <Container>
        {res && res.success ? (
          <HeroSlider data={res.data} />
        ) : (
          <div className="col-span-2 py-10 text-center md:col-span-4">
            <p className="text-lg text-gray-500">
              No sliders available right now.
            </p>
          </div>
        )}
      </Container>
    </section>
  );
};

export default HeroSection;
