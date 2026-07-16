import Container from "@/components/Container";
import FeaturedCard from "@/components/cards/FeaturedCard";
import { apiBaseUrlV2 } from "@/config/apiConfig";
import {
  HOMEPAGE_SECTION_REVALIDATE_TIME,
  REVALIDATE_TIME,
} from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import CategoriesSection from "./CategoriesSection";
import { delay } from "@/lib/delay";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

interface DataType {
  id: string;
  photo: string;
  position: number;
  type: string;
  url: string;
}

interface ApiResponse {
  banner1: { data: DataType[] };
  banner2: { data: DataType[] };
  banner3: { data: DataType[] };
}

const FeaturedCategorySection = async () => {
  const res = await cacheableFetcher<ApiResponse>("/banners", {
    baseUrl:apiBaseUrlV2,
    next: {
      revalidate: 300,
    },
  });
if(res && res.banner1.data.length === 0){
    return null;
}

  return (
    <section className="pb-10 md:pb-[60px] ">
      <Container className="w-full">
        <div className="grid grid-cols-2 gap-2 md:grid-cols-4 md:gap-7 featured-banner">
          {res && res.banner1.data.length > 0 && res.banner1.data.map((category, i) => (
              <FeaturedCard key={category?.id+i} {...category} i={i} />
            ))}
        </div>
      </Container>
    </section>
  );
};

export default FeaturedCategorySection;
