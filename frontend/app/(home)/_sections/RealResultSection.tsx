import Container from "@/components/Container";
import SectionHeader from "@/components/SectionHeader";
import { RealResultTab } from "@/components/tabs/RealResultTab";
import { HOMEPAGE_SECTION_REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

const RealResultSection = async () => {
  const videoRes = await cacheableFetcher<ApiResponseType<VideoTab[]>>(
    "/playlists/featured",
    {
      revalidate: HOMEPAGE_SECTION_REVALIDATE_TIME,
    },
  );

  if (!videoRes || videoRes.data.length === 0) {
    return null;
  }
  const videos = videoRes.data;

  return (
    <section className="pb-10 md:pb-[60px] ">
      <Container>
        <SectionHeader
          title="Glowaro Studio"
          icon="/images/video-play.png"
          link=""
        />

        {/* Real routines slider */}
        <RealResultTab allTabs={videos} />
      </Container>
    </section>
  );
};

export default RealResultSection;
