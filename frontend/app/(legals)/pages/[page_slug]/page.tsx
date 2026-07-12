import React from "react";
import { apiBaseUrl, publicBaseUrl, siteTitle } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { notFound } from "next/navigation";
import Image from "next/image";
import { FaFacebook, FaInstagram, FaTiktok, FaYoutube } from "react-icons/fa";
import PageHeader from "@/components/PageHeader";

interface Props {
  params: Promise<{
    page_slug: string;
  }>;
}

export interface PageData {
  title: string;
  slug: string;
  content: string;
  mobile_banner: string | null;
  desktop_banner: string | null;
  meta_title: string | null;
  meta_description: string | null;
  keywords: string | null;
  meta_image: string | null;
}

export interface PageApiResponse {
  result: boolean;
  data: PageData;
}

export default async function LegalPage({ params }: Props) {
  const { page_slug } = await params;

  const res = (await fetcher(`/pages/${page_slug}`, {
    baseUrl: apiBaseUrl,
    cache: "no-store",
  })) as PageApiResponse;

  if (!res || !res.data) {
    return notFound();
  }

  const pageData = res.data;

  return (
    <div className="custom-page-style mx-auto max-w-[1100px]">
      <PageHeader
        title={pageData.title}
        className="mb-6 max-h-[150px] bg-[linear-gradient(90deg,#FFE0E6_0%,#FBF8F9_49.04%,#FEEEFF_100%)] py-8 md:mb-8 md:py-10"
        headingStyle="text-site-secondary-500"
        animateImg1Url="/images/category-header1.png"
        animateImg1Style="absolute -left-12 md:left-12 -bottom-[-20px] md:-bottom-[-30px] h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[146px] lg:w-[150px]"
        animateImg2Url="/images/category-header2.png"
        animateImg2Style="h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[144px] lg:w-[144px] absolute -right-8 md:right-12 -bottom-[-20px] md:-bottom-[-25px]"
      />{" "}
      <div className="bg-site-gray-50 mx-auto w-full max-w-[800px] rounded-lg px-5 py-6">
        {pageData.title.toLowerCase() !== "about us" && (
          <div
            className="text-site-gray-700 prose"
            dangerouslySetInnerHTML={{ __html: pageData.content }}
          />
        )}
        {pageData.title.toLowerCase() === "about us" && (
          <>
            {/* Video container */}
            {/* <div className="bg-site-gray-50 mb-6 aspect-video overflow-clip rounded-[10px]">
            <iframe
              width="100%"
              height="100%"
              src="https://www.youtube.com/embed/nicUv9CUfjI?autoplay=1&mute=1&loop=1&playlist=nicUv9CUfjI&si=nvJCG8BrcgfDn52N"
              title="GlowaroSkincare Intro Video"
              frameBorder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerPolicy="strict-origin-when-cross-origin"
              allowFullScreen
            ></iframe>
          </div> */}

            {/* About Glowaro */}
            <div className="flex flex-col items-center gap-10 md:flex-row">
              {/* Left */}
              <div className="relative aspect-[288/180] w-full shrink-0 overflow-hidden rounded-[10px] md:max-w-[288px]">
                <Image
                  src="/images/legals/banner-1.png"
                  alt="banner-1"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 288px"
                />
              </div>

              {/* Right */}
              <div className="space-y-3">
                <h4 className="text-site-gray-900 text-[32px] font-bold">
                  Who We Are
                </h4>
                <p className="text-site-gray-700 text-sm leading-[22px]">
                  At GlowaroSkincare, we believe skincare is more than just
                  products — it's confidence, self-care, and feeling good in
                  your own skin. Our mission is to bring high-quality, safe, and
                  effective skincare products right to your doorstep. We're here
                  to help you look good, feel good, and glow naturally.
                </p>
              </div>
            </div>
            <hr className="my-5 md:my-10" />
            <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
              <div className="rounded-[10px] bg-[#FFE0E6] p-8 text-center">
                <span className="text-site-gray-700 text-[40px] font-semibold">
                  3K+
                </span>
                <span className="text-site-gray-700/80 block text-base">
                  Stock Products
                </span>
              </div>
              <div className="rounded-[10px] bg-[#e9e6e6] p-8 text-center">
                <span className="text-site-gray-700 text-[40px] font-semibold">
                  15K+
                </span>
                <span className="text-site-gray-700/80 block text-base">
                  Happy Customers
                </span>
              </div>
              <div className="rounded-[10px] bg-[#FEEEFF] p-8 text-center">
                <span className="text-site-gray-700 text-[40px] font-semibold">
                  40+
                </span>
                <span className="text-site-gray-700/80 block text-base">
                  Top Brands
                </span>
              </div>
            </div>
            <hr className="my-5 md:my-10" />
            <div className="flex flex-col items-center gap-10 md:flex-row">
              {/* Left */}
              <div className="space-y-3">
                <h4 className="text-site-gray-900 text-[32px] font-bold">
                  Our Story
                </h4>
                <p className="text-site-gray-700 text-sm leading-[22px]">
                  At GlowaroSkincare, we believe skincare is more than just
                  products — it's confidence, self-care, and feeling good in
                  your own skin. Our mission is to bring high-quality, safe, and
                  effective skincare products right to your doorstep. We're here
                  to help you look good, feel good, and glow naturally.
                </p>
              </div>
              {/* Right */}
              <div className="relative aspect-[288/180] w-full shrink-0 overflow-hidden rounded-[10px] md:max-w-[288px]">
                <Image
                  src="/images/legals/banner-2.png"
                  alt="banner-2"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 288px"
                />
              </div>
            </div>
            {/* <hr className="my-5 md:my-10" />
          <div className="space-y-9">
            <h4 className="text-site-gray-900 text-[32px]">
              Meet the Minds Behind Our Brand
            </h4>
            <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
              <div className="flex flex-col items-center space-y-4 text-center">
                <div className="relative aspect-[201/275] w-full max-w-[201px] overflow-hidden rounded-[10px]">
                  <Image
                    src="/images/legals/person-1.png"
                    alt="Aehtesham Aumee"
                    fill
                    className="object-cover"
                    sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 201px"
                    priority
                  />
                </div>
                <div className="text-center">
                  <span className="text-site-gray-900 mb-1 text-[20px] font-semibold">
                    Aehtesham Aumee
                  </span>
                  <span className="text-site-primary block text-xs">
                    Managing Director & Founder
                  </span>
                  <a
                    href="mailto:aumee@glowaro.com"
                    target="_blank"
                    className="text-site-gray-700 text-xs"
                  >
                    aumee@glowaro.com
                  </a>
                </div>
              </div>

              <div className="flex flex-col items-center space-y-4 text-center">
                <div className="relative aspect-[201/275] w-full max-w-[201px] overflow-hidden rounded-[10px]">
                  <Image
                    src="/images/legals/person-2.png"
                    alt="Bablu Ahmed"
                    fill
                    className="object-cover"
                    sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 201px"
                    priority
                  />
                </div>
                <div className="text-center">
                  <span className="text-site-gray-900 mb-1 text-[20px] font-semibold">
                    Bablu Ahmed
                  </span>
                  <span className="text-site-primary block text-xs">
                    Co Founder
                  </span>
                  <a
                    href="mailto:bablu@glowaro.com"
                    target="_blank"
                    className="text-site-gray-700 text-xs"
                  >
                    bablu@glowaro.com
                  </a>
                </div>
              </div>

              <div className="flex flex-col items-center space-y-4 text-center">
                <div className="relative aspect-[201/275] w-full max-w-[201px] overflow-hidden rounded-[10px]">
                  <Image
                    src="/images/legals/person-3.png"
                    alt="person-3"
                    fill
                    className="object-cover"
                    sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 201px"
                    priority
                  />
                </div>
                <div className="text-center">
                  <span className="text-site-gray-900 mb-1 text-[20px] font-semibold">
                    Sheikh Rasel
                  </span>
                  <span className="text-site-primary block text-xs">
                    Co Founder
                  </span>
                  <a
                    href="mailto:rasel@glowaro.com"
                    target="_blank"
                    className="text-site-gray-700 text-xs"
                  >
                    rasel@glowaro.com
                  </a>
                </div>
              </div>
            </div>
          </div> */}

            {/* Our community */}
            {/* <div className="bg-site-gray-50 border-site-gray-100 my-10 space-y-3 rounded-[10px] border p-5 md:p-8">
            <h4 className="text-site-gray-900 text-[32px]">
              Join Our Community
            </h4>
            <p className="text-site-gray-700 text-sm leading-[22px]">
              We're more than a store — we're a community that cares. Follow us
              for skincare tips, updates on new products, and exclusive offers.
              Let's make every day a glow day.
            </p>
            <div className="flex items-center gap-3">
              <a
                href="https://www.facebook.com/glowaro"
                target="_blank"
                className="hover:bg-site-primary/20 grid h-[38px] w-[38px] place-content-center rounded-full bg-white transition-colors"
              >
                <FaFacebook size={28} className="text-site-gray-900" />
              </a>

              <a
                href="#"
                target="_blank"
                className="hover:bg-site-primary/20 grid h-[38px] w-[38px] place-content-center rounded-full bg-white transition-colors"
              >
                <FaTiktok size={24} className="text-site-gray-900" />
              </a>

              <a
                href="https://www.youtube.com/channel/UCQPyA3Vf20QLK8yIF-62IOA"
                target="_blank"
                className="hover:bg-site-primary/20 grid h-[38px] w-[38px] place-content-center rounded-full bg-white transition-colors"
              >
                <FaYoutube size={25} className="text-site-gray-900" />
              </a>

              <a
                href="https://www.instagram.com/glowaro"
                target="_blank"
                className="hover:bg-site-primary/20 grid h-[38px] w-[38px] place-content-center rounded-full bg-white transition-colors"
              >
                <FaInstagram size={25} className="text-site-gray-900" />
              </a>
            </div>
          </div> */}

            {/* Image gallery */}
            <div className="grid grid-cols-1 gap-2 md:gap-4">
              {/* Column 1 (now like old column 2) */}
              {/* <div className="grid grid-cols-4 gap-4">
              <div className="relative col-span-2 aspect-[426/283] w-full max-w-[426px] overflow-hidden rounded-[10px]">
                <Image
                  src="/images/legals/photo-1.png"
                  alt="photo-1"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 426px"
                  priority
                />
              </div>
              <div className="relative aspect-[205/283] w-full max-w-[205px] overflow-hidden rounded-[10px]">
                <Image
                  src="/images/legals/photo-2.png"
                  alt="person-3"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 205px"
                  priority
                />
              </div>
              <div className="relative aspect-[205/283] w-full max-w-[205px] overflow-hidden rounded-[10px]">
                <Image
                  src="/images/legals/photo-3.png"
                  alt="person-3"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 205px"
                  priority
                />
              </div>
            </div> */}

              {/* Column 2 (now like old column 1) */}
              {/* <div className="grid grid-cols-4 gap-2 md:gap-4">
              <div className="relative aspect-[205/283] w-full max-w-[205px] overflow-hidden rounded-[10px]">
                <Image
                  src="/images/legals/photo-4.png"
                  alt="person-4"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 205px"
                  priority
                />
              </div>
              <div className="relative col-span-2 aspect-[426/283] w-full max-w-[426px] overflow-hidden rounded-[10px]">
                <Image
                  src="/images/legals/photo-5.png"
                  alt="photo-5"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 426px"
                  priority
                />
              </div>
              <div className="relative aspect-[205/283] w-full max-w-[205px] overflow-hidden rounded-[10px]">
                <Image
                  src="/images/legals/photo-6.png"
                  alt="person-6"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 205px"
                  priority
                />
              </div>
            </div> */}
            </div>
          </>
        )}
      </div>
    </div>
  );
}

export async function generateMetadata({ params }: Props) {
  const { page_slug } = await params;

  const res = (await fetcher(`/pages/${page_slug}`, {
    baseUrl: apiBaseUrl,
    cache: "no-store",
  })) as PageApiResponse;

  const pageData = res?.data;

  const metaTitle = pageData?.meta_title
    ? `${pageData.meta_title}  | ${pageData?.title + "GlowaroSkincare Limited | We Care About Your Skin" || "GlowaroSkincare Limited | We Care About Your Skin"}`
    : pageData?.title + "GlowaroSkincare Limited | We Care About Your Skin" ||
      "GlowaroSkincare Limited | We Care About Your Skin";

  const metaDescription = pageData?.meta_description
    ? `${pageData.meta_description} | ${pageData?.title || ""}`
    : "GlowaroSKINCARE is trusted & Authentic Cosmetics Company with Best Price. Next Day Delivery. Shop the latest top brands like Somebymi, CeraVe, The Ordinary, Bioderma, Purito, Cosrx, Nature Republic, Innisfree, iUNIK, The Dermalix, Neogen etc glowaro Glowaroglowaro";

  return {
    title: metaTitle,
    description: metaDescription,
    siteName: siteTitle,
    openGraph: {
      title: metaTitle,
      description: metaDescription,
      url: publicBaseUrl,
      siteName: siteTitle,
      type: "website",
    },
  };
}
