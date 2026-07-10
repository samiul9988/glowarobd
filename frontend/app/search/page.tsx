import PageHeader from "@/components/PageHeader";
import { metaData } from "@/metadata/staticMetaData";
import SearchWrapper from "./SearchWrapper";

// Props type
interface Props {
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}

// API response types
interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Links {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

interface Meta {
  current_page: number;
  from: number;
  last_page: number;
  links: PaginationLink[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface ProductListApiResponse {
  data: ProductType[];
  links: Links;
  meta: Meta;
  success: boolean;
  status: number;
  min_price: number;
  max_price: number;
}

export default async function SearchPage({ searchParams }: Props) {
  const params = await searchParams;

  // URL params
  const keyword = params.keyword as string;

  return (
    <>
      <PageHeader
        title={
          keyword
            ? `Search Results for “${keyword.slice(0, 30)}”`
            : "Search Results"
        }
        className="bg-[linear-gradient(90deg,#FFE0E6_0%,#FBF8F9_49.04%,#FEEEFF_100%)] max-h-[150px]  py-8 md:py-10"
        headingStyle="text-site-secondary-500"
        animateImg1Url="/images/category-header1.png"
        animateImg1Style="absolute -left-12 md:left-12 -bottom-[-20px] md:-bottom-[-30px] h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[146px] lg:w-[150px]"
        animateImg2Url="/images/category-header2.png"
        animateImg2Style="h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[144px] lg:w-[144px] absolute -right-8 md:right-12 -bottom-[-20px] md:-bottom-[-25px]"
      />

      <SearchWrapper keywordVal={keyword} />
    </>
  );
}

export const metadata = {
  title: metaData.search.title,
  description: metaData.search.description,
};
