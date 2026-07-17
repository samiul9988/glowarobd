import React from "react";
import { apiBaseUrl, imageBaseHostUrl, publicBaseUrl, siteTitle } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { notFound } from "next/navigation";
import Image from "next/image";
import { FaFacebook, FaInstagram, FaTiktok, FaYoutube } from "react-icons/fa";
import PageHeader from "@/components/PageHeader";

const BANNER_1 = "uploads/all/WPLAfa03W2yIZyseSaJTKJyf4dz3SmCvw85lHFTu.webp";
const BANNER_2 = "uploads/all/dcA671Mx1vOUlXSHmhwPCCezr06HHNWJLe4L5qcK.webp";

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
                  src={imageBaseHostUrl + BANNER_1}
                  alt="Glowaro Skincare Products"
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
                  Glowaro is Bangladesh's most trusted destination for authentic
                  Korean and global skincare. We bring you dermatologist-tested,
                  result-driven products that target every skin concern — from
                  stubborn acne and dark spots to uneven skin tone, open pores,
                  melasma, and premature aging. Every product we stock is
                  handpicked to deliver real, visible results for your unique
                  skin journey.
                </p>
              </div>
            </div>
            <hr className="my-5 md:my-10" />

            {/* Skin Concerns We Address */}
            <h4 className="text-site-gray-900 mb-6 text-center text-[28px] font-bold">
              Skin Concerns We Address
            </h4>
            <div className="mb-8 grid grid-cols-2 gap-3 md:grid-cols-4">
              {[
                { label: "Acne & Blemishes", bg: "#FFE0E6" },
                { label: "Dark Spots", bg: "#FFF3E0" },
                { label: "Uneven Skin Tone", bg: "#F3E5F5" },
                { label: "Melasma", bg: "#E8EAF6" },
                { label: "Open Pores", bg: "#E0F2F1" },
                { label: "Whiteheads", bg: "#FCE4EC" },
                { label: "Blackheads", bg: "#ECEFF1" },
                { label: "Wrinkles & Fine Lines", bg: "#FFF8E1" },
                { label: "Dryness & Dehydration", bg: "#E1F5FE" },
                { label: "Sun Damage", bg: "#FFF3E0" },
                { label: "Hyperpigmentation", bg: "#F3E5F5" },
                { label: "Soothing & Calming", bg: "#E8F5E9" },
              ].map((item, i) => (
                <div
                  key={i}
                  className="rounded-[10px] px-4 py-3 text-center text-sm font-medium text-gray-800"
                  style={{ backgroundColor: item.bg }}
                >
                  {item.label}
                </div>
              ))}
            </div>

            <hr className="my-5 md:my-10" />
            <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
              <div className="rounded-[10px] bg-[#FFE0E6] p-8 text-center">
                <span className="text-site-gray-700 text-[40px] font-semibold">
                  3K+
                </span>
                <span className="text-site-gray-700/80 block text-base">
                  Authentic Products
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
                  Global Brands
                </span>
              </div>
            </div>
            <hr className="my-5 md:my-10" />
            <div className="flex flex-col items-center gap-10 md:flex-row">
              {/* Left */}
              <div className="space-y-3">
                <h4 className="text-site-gray-900 text-[32px] font-bold">
                  Our Promise
                </h4>
                <p className="text-site-gray-700 text-sm leading-[22px]">
                  Every product at Glowaro goes through rigorous quality checks
                  before it reaches you. We partner directly with authorized
                  distributors and brands to guarantee 100% authenticity. Whether
                  you're fighting active acne, fading dark spots, minimizing open
                  pores, or building an anti-aging routine — we have the right
                  solution backed by science and real customer results. Your skin
                  deserves nothing less than the best, and we're committed to
                  delivering exactly that.
                </p>
              </div>
              {/* Right */}
              <div className="relative aspect-[288/180] w-full shrink-0 overflow-hidden rounded-[10px] md:max-w-[288px]">
                <Image
                  src={imageBaseHostUrl + BANNER_2}
                  alt="Glowaro Quality Promise"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 288px"
                />
              </div>
            </div>
            <hr className="my-5 md:my-10" />

            {/* Why Choose Us */}
            <h4 className="text-site-gray-900 mb-6 text-center text-[28px] font-bold">
              Why Choose Glowaro
            </h4>
            <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
              <div className="rounded-[10px] border border-gray-100 p-6 text-center">
                <div className="text-site-primary mb-3 text-3xl font-bold">100%</div>
                <h5 className="text-site-gray-900 mb-2 font-semibold">Authentic Products</h5>
                <p className="text-site-gray-700 text-sm leading-[22px]">
                  Every item is sourced directly from authorized distributors.
                  No fakes, no compromises.
                </p>
              </div>
              <div className="rounded-[10px] border border-gray-100 p-6 text-center">
                <div className="text-site-primary mb-3 text-3xl font-bold">24H</div>
                <h5 className="text-site-gray-900 mb-2 font-semibold">Fast Delivery</h5>
                <p className="text-site-gray-700 text-sm leading-[22px]">
                  Order today, glow tomorrow. We deliver across Bangladesh with
                  lightning-fast shipping.
                </p>
              </div>
              <div className="rounded-[10px] border border-gray-100 p-6 text-center">
                <div className="text-site-primary mb-3 text-3xl font-bold">24/7</div>
                <h5 className="text-site-gray-900 mb-2 font-semibold">Expert Support</h5>
                <p className="text-site-gray-700 text-sm leading-[22px]">
                  Not sure what your skin needs? Our skincare experts are here
                  to guide you — any time, any day.
                </p>
              </div>
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
