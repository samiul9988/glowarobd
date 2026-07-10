import PageHeader from "@/components/PageHeader";
import {
  apiBaseUrl,
  defaultImage,
  imageBaseHostUrl,
  publicBaseUrl,
  siteName,
} from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import BrandWrapper from "./BrandWrapper";

interface Props {
  params: Promise<{ brand_slug: string }>;
  searchParams: Promise<{
    page?: string;
    brand_id?: string;
    rating?: string;
    min_price?: string;
    max_price?: string;
    sort_by?: string;
  }>;
}

type ApiResponse<T> = {
  data: T;
  success: boolean;
  status: number;
};

export interface BrandData {
  id: number;
  name: string;
  slug: string;
  logo: string;
  banner: string;
  meta: {
    title: string;
    description: string;
    keywords: string;
  };
}

const BrandProduct = async ({ params }: Props) => {
  const { brand_slug } = await params;

  // Fetch full brand details (for name / SEO)
  const brandRes = await fetcher<ApiResponse<BrandData>>(
    `/brands/${brand_slug}`,
    {
      baseUrl: apiBaseUrl,
      next: { revalidate: REVALIDATE_TIME },
    },
  );

  return (
    <>
      <PageHeader
        title={brandRes?.data?.name ?? "Brand Details"}
        className="bg-[linear-gradient(90deg,#FFE0E6_0%,#FBF8F9_49.04%,#FEEEFF_100%)] max-h-[150px]  py-8 md:py-10"
        headingStyle="text-site-secondary-500"
        animateImg1Url="/images/category-header1.png"
        animateImg1Style="absolute -left-12 md:left-12 -bottom-[-20px] md:-bottom-[-30px] h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[146px] lg:w-[150px]"
        animateImg2Url="/images/category-header2.png"
        animateImg2Style="h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[144px] lg:w-[144px] absolute -right-8 md:right-12 -bottom-[-20px] md:-bottom-[-25px]"
      />

      <BrandWrapper brand={brand_slug} />
    </>
  );
};

export default BrandProduct;

// ===================
// METADATA
// ===================
export async function generateMetadata({ params }: Props) {
  const { brand_slug } = await params;

  const brandRes = await fetcher<ApiResponse<BrandData>>(
    `/brands/${brand_slug}`,
    {
      baseUrl: apiBaseUrl,
      next: { revalidate: REVALIDATE_TIME },
    },
  );

  const title = brandRes?.data?.meta.title || "Brand Title";
  const description = brandRes?.data?.meta.description || "Brand Details";

  const ogImage = brandRes?.data?.logo
    ? `${imageBaseHostUrl}${brandRes.data.logo}`
    : defaultImage;

  return {
    title,
    description,
    images: ogImage,
    openGraph: {
      title,
      description,
      url: `${publicBaseUrl}/brand/${brand_slug}`,
      siteName,
      images: [
        {
          url: ogImage,
          width: 1051,
          height: 553,
        },
      ],
    },
  };
}
