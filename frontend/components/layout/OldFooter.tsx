// import { apiBaseUrl } from "@/config/apiConfig";
// import { REVALIDATE_TIME } from "@/config/cacheConfig";
// import { fetcher } from "@/lib/fetcher";
// import Container from "../Container";
// import AboutColumn from "./_components/footer/AboutColumn";
// import ContactInfoColumn from "./_components/footer/ContactInfoColumn";
// import CopyRightSection from "./_components/footer/CopyRightSection";
// import CustomerCareColumn from "./_components/footer/CustomerCareColumn";
// import MyAccountColumn from "./_components/footer/MyAccountColumn";

// interface DataType {
//   type: string;
//   value: string;
//   image_url: string;
//   popup_product_name: string;
//   popup_category_name: string;
//   popup_flash_deal_name: string;
//   popup_brand_name: string;
// }

// const OldFooter = async () => {
//   const data = await fetcher<ApiResponseType<DataType[]>>(
//     "/business-settings",
//     {
//       baseUrl: apiBaseUrl,
//       next: {
//         revalidate: REVALIDATE_TIME,
//       },
//     }
//   );

//   /**
//    * Extract footer contact info
//    */
//   const footerLogo = data.data.filter(
//     (item) => item.type === "footer_logo"
//   )[0] ?? {
//     image_url: "",
//   };

//   const aboutUs =
//     data.data.filter((item) => item.type === "about_us_description")[1] ?? "";

//   const contactAddress =
//     data.data.filter((item) => item.type === "contact_address")[1].value ?? "";

//   const contactPhone =
//     data.data.filter((item) => item.type === "contact_phone")[0].value ?? "";

//   const contactEmail =
//     data.data.filter((item) => item.type === "contact_email")[0].value ?? "";

//   /**
//    * Extract socials links
//    */
//   const showSocialLinks =
//     data.data.filter((item) => item.type === "show_social_links")[0].value ===
//     "on";

//   const facebookLink =
//     data.data.filter((item) => item.type === "facebook_link")[0].value ?? "";

//   const twitterLink =
//     data.data.filter((item) => item.type === "twitter_link")[0].value ?? "";

//   const linkedinLink =
//     data.data.filter((item) => item.type === "linkedin_link")[0].value ?? "";

//   const instagramLink =
//     data.data.filter((item) => item.type === "instagram_link")[0].value ?? "";

//   const youtubeLink =
//     data.data.filter((item) => item.type === "youtube_link")[0].value ?? "";

//   /**
//    * Extract customer care links
//    */
//   const labelsRes = data.data.filter(
//     (item) => item.type === "widget_one_labels"
//   )[1].value;
//   const labelsData: string[] = JSON.parse(labelsRes) ?? [];

//   const linksRes = data.data.filter(
//     (item) => item.type === "widget_one_links"
//   )[0].value;
//   const linksData: string[] = JSON.parse(linksRes) ?? [];

//   /**
//    * App download links
//    */
//   const appStore =
//     data.data.filter((item) => item.type === "app_store_link")[0].value ?? "";
//   const playStore =
//     data.data.filter((item) => item.type === "play_store_link")[0].value ?? "";

//   /**
//    * Extract copyright text
//    */
//   const copyRightText =
//     data.data.filter((item) => item.type === "frontend_copyright_text")[1]
//       .value ?? "";

//   const paymentMethodImg =
//     data.data.filter((item) => item.type === "payment_method_images")[0]
//       .image_url ?? "";

//   return (
//     <footer className="bg-gradient-to-b from-[#4F3E70] to-[#110000] mt-8 md:mt-16 lg:mt-24">
//       <Container className="pt-20 py-[90px] grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-40">
//         {/* Column 1 */}

//         <AboutColumn
//           aboutUsText={aboutUs.value ?? ""}
//           footerLogo={footerLogo.image_url ?? ""}
//           socialLinks={{
//             facebook: facebookLink,
//             twitter: twitterLink,
//             linkedin: linkedinLink,
//             instagram: instagramLink,
//             youtube: youtubeLink,
//           }}
//           showSocialLinks={showSocialLinks}
//         />

//         {/* Column 2 */}
//         <ContactInfoColumn
//           contactAddress={contactAddress}
//           contactPhone={contactPhone}
//           contactEmail={contactEmail}
//         />

//         {/* Column 3 */}
//         <CustomerCareColumn labels={labelsData} links={linksData} />

//         {/* Column 4 */}
//         <MyAccountColumn appStore={appStore} playStore={playStore} />
//       </Container>
//       <hr className="bg-white opacity-[12%]" />

//       <CopyRightSection
//         copyRightText={copyRightText}
//         paymentMethodImg={paymentMethodImg}
//       />
//     </footer>
//   );
// };

// export default OldFooter;
