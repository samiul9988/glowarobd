import Container from "@/components/Container";
import FeaturedCard from "@/components/cards/FeaturedCard";
import { apiBaseUrlV2 } from "@/config/apiConfig";
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

const BannerTwo = async () => {
  const res = await cacheableFetcher<ApiResponse>("/banners", {
    baseUrl:apiBaseUrlV2,
    next: {
      revalidate: 300,
    },
  });
if(res && res.banner2.data.length === 0){
    return null;
}

  return (
    <section className="py-5 lg:py-7">
      <Container className="w-full">
        <div className="grid grid-cols-2 gap-2 md:grid-cols-4 md:gap-7 ">
          {res && res.banner2.data.length > 0 && res.banner2.data.map((category, i) => (
              <FeaturedCard key={category?.id+i} {...category} i={i} />
            ))}
        </div>
      </Container>
    </section>
  );
};

export default BannerTwo;
