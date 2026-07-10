import PageHeader from "@/components/PageHeader";
import { ChevronRight } from "lucide-react";
import CategoryWrapper from "./CategoryWrapper";
import { fetcher } from "@/lib/fetcher";
import {
  apiBaseUrl,
  imageBaseHostUrl,
  publicBaseUrl,
  siteName,
} from "@/config/apiConfig";
import {
  CATEGORY_REVALIDATE_TIME,
  REVALIDATE_TIME,
} from "@/config/cacheConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import SubCategorySlider from "../_components/SubCategorySlider";
import { fetchCategories } from "@/lib/api/fetchCategories";

interface Props {
  params: Promise<{ category_slug: string }>;
}

const ProductCategory = async ({ params }: Props) => {
  const { category_slug } = await params;

  // Fetch category details with correct type
  const response = await cacheableFetcher<ApiResponse<CategoryDetails[]>>(
    `/category/${category_slug}`,
    {
      next: {
        revalidate: CATEGORY_REVALIDATE_TIME,
      },
    },
  );
  const category = response?.data?.[0];

  // get assign sub categories
  const subResponse = await cacheableFetcher<{ data: Category[] }>(
    `/sub-categories/${category?.id}`,
    { next: { revalidate: 3600 } },
  );

  const allCategories = await fetchCategories();

  return (
    <>
      <PageHeader
        title={category?.name || "Category Details"}
        className="bg-[linear-gradient(90deg,#FFE0E6_0%,#FBF8F9_49.04%,#FEEEFF_100%)] max-h-[150px]  py-8 md:py-10"
        headingStyle="text-site-secondary-500"
        animateImg1Url="/images/category-header1.png"
        animateImg1Style="absolute -left-12 md:left-12 -bottom-[-20px] md:-bottom-[-30px] h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[146px] lg:w-[150px]"
        animateImg2Url="/images/category-header2.png"
        animateImg2Style="h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[144px] lg:w-[144px] absolute -right-8 md:right-12 -bottom-[-20px] md:-bottom-[-25px]"
      />

      {subResponse?.data && subResponse?.data?.length > 0 && (
        <SubCategorySlider subcategoryRes={subResponse.data} />
      )}

      <CategoryWrapper category_slug={category_slug} allCategories={allCategories} />
    </>
  );
};

export default ProductCategory;

type ApiResponse<T> = {
  data: T;
  success: boolean;
  status: number;
  max_price: number;
  min_price: number;
};

type metaData = {
  description: string;
  keywords: string;
  title: string;
};
type CategoryDetails = {
  id: number;
  slug: string;
  name: string;
  banner: string | null;
  page_banner: string | null;
  icon: string | null;
  featured_icon: string | null;
  bg_image: string | null;
  app_slider: any[];
  app_banner1: any[];
  app_banner2: any[];
  app_featured_image: string;
  app_home_page_image: string;
  number_of_children: number;
  links: {
    products: string;
    sub_categories: string;
  };
  design: string;
  meta: metaData;
};

// metadata
export async function generateMetadata({ params }: Props) {
  const { category_slug } = await params;
  const response = await fetcher<ApiResponse<CategoryDetails[]>>(
    `/category/${category_slug}`,
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  const category = response?.data?.[0];
  const title = category?.meta.title || category?.name;
  const description = category?.meta.description || category?.name;
  const ogImage = `${imageBaseHostUrl}${category?.bg_image || ""}`;
  const tags = category?.meta.keywords || "";
  return {
    title,
    description,
    images: ogImage,
    openGraph: {
      title: category?.name,
      description,
      url: `${publicBaseUrl}/category/${category_slug}`,
      tags: tags,
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
