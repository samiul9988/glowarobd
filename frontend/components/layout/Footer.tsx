import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import FooterWrapper from "./_components/footer/FooterWrapper";
import SupportStickyButton from "../SupportStickyButton";

interface DataType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

const Footer = async () => {
  const data = await fetcher<ApiResponseType<DataType[]>>(
    "/business-settings",
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  /**
   * Extract footer contact info
   */

  const aboutUs =
    data?.data.filter((item) => item.type === "about_us_description")[1]
      ?.value ?? "";

  /**
   * Extract socials links
   */
  const showSocialLinks =
    data?.data.filter((item) => item.type === "show_social_links")[0]?.value ===
    "on";

  const facebookLink =
    data?.data.filter((item) => item.type === "facebook_link")[0]?.value ?? "";

  const tiktokLink =
    data?.data.filter((item) => item.type === "tiktok_link")[0]?.value ?? "";

  const instagramLink =
    data?.data.filter((item) => item.type === "instagram_link")[0]?.value ?? "";

  const youtubeLink =
    data?.data.filter((item) => item.type === "youtube_link")[0]?.value ?? "";

  const socialsLinks = {
    facebookLink,
    tiktokLink,
    youtubeLink,
    instagramLink,
    showSocialLinks,
  };

  /**
   * Extract copyright text
   */
  const copyRightText =
    data?.data.filter((item) => item.type === "frontend_copyright_text")[1]
      .value ?? "";

  const paymentMethodImg =
    data?.data.filter((item) => item.type === "payment_method_images")[0]
      .image_url ?? "";

  /**
   * App download links
   */
//   const appStoreLink =
//     data?.data.filter((item) => item.type === "app_store_link")[0].value ?? "";
//   const playStoreLink =
//     data?.data.filter((item) => item.type === "play_store_link")[0].value ?? "";

//   const appDownloadLinks = {
//     appStoreLink,
//     playStoreLink,
//   };

  /**
   * Extract customer care links
   */
  // const labelsRes = data.data.filter(
  //   (item) => item.type === "widget_one_labels"
  // )[1].value;
  // const labelsData: string[] = JSON.parse(labelsRes) ?? [];

  // const linksRes = data.data.filter(
  //   (item) => item.type === "widget_one_links"
  // )[0].value;
  // const linksData: string[] = JSON.parse(linksRes) ?? [];

  return (
    <>
      <FooterWrapper
        socialLinks={socialsLinks}
        copyRightText={copyRightText}
        paymentMethodImg={paymentMethodImg}
        aboutUs={aboutUs}
      />
      <SupportStickyButton />
      
    </>
  );
};

export default Footer;
