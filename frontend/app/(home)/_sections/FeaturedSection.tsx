import { getBusinessSettings } from "@/actions/getBusinessSettings";
import Container from "@/components/Container";
import LogoTickerWrapper from "@/components/LogoTickerWrapper";
import SectionHeader from "@/components/SectionHeader";
import FeaturedSlider from "@/components/sliders/FeaturedSlider";
import { apiBaseUrl, imageBaseHostUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import FeaturedMegaSale from "../_components/FeaturedMegaSale";

const FeaturedSection = async () => {
  const res = await fetcher<ApiResponseType<BrandType[]>>("/brands?limit=40", {
    baseUrl: apiBaseUrl,
    next: {
      revalidate: REVALIDATE_TIME,
    },
  });

  //   const data = await fetcher<ApiResponseType<ProductType[]>>(
  //     "/highlight-brand/products?sort_by=rand&limit=12",
  //     {
  //       baseUrl: apiBaseUrl,
  //       next: {
  //         revalidate: REVALIDATE_TIME,
  //       },
  //     },
  //   );

  const bs = await getBusinessSettings();

  const isSectionEnabled = bs.filter(
    (item) => item.type === "show_highlight_brand",
  )[0]?.value;

  // If section is disabled
  if (Number(isSectionEnabled) !== 1) {
    return null;
  }

  // If no data
  //   if (!data || data.data.length === 0) {
  //     return null;
  //   }

  return (
    <section className="relative mb-0 w-full overflow-clip bg-[linear-gradient(180deg,#E9D5FF_39.03%,#FFFFFF_93.75%)] py-10 md:py-[60px] lg:py-[72px]">
      {/* {Number(isSectionEnabled) == 1 && data && data.data.length && (
        <Container className="mb-10 md:mb-[100px]">
          <SectionHeader
            title={data.brand_name || ""}
            subTitle={data.title || ""}
            icon={imageBaseHostUrl + data.icon || ""}
            link={"/brand/" + data.brand_slug || ""}
            linkStyle="bg-site-primary-100/70 hover:bg-site-primary-100"
          />

          <div className="flex gap-0 lg:gap-8">
            <FeaturedMegaSale banner={data.banner || ""} />

            {data && data.data.length > 0 && <FeaturedSlider {...data} />}
          </div>
        </Container>
      )} */}

      {/* Logo ticker */}
      <>
        {res && res.data.length > 0 && <LogoTickerWrapper logos={res.data} />}
      </>
    </section>
  );
};

export default FeaturedSection;
