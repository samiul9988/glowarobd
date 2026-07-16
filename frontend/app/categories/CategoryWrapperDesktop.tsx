import CategoriesGrid from "./_components/CategoriesGrid";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

interface Categories {
  id: number;
  slug: string;
  name: string;
  banner: string;
  page_banner: string | null;
  icon: string;
  featured_icon: string;
  bg_image: string | null;
  app_slider: string[];
  app_banner1: string[];
  app_banner2: string[];
  app_featured_image: string;
  app_home_page_image: string;
  number_of_children: number;
  links: {
    products: string;
    sub_categories: string;
  };
  design: string;
  products_count: number;
}

const CategoryWrapperDesktop = async () => {
  const res = await cacheableFetcher<ApiResponseType<Categories[]>>(
    "/categories",
    {
      revalidate: 300,
    },
  );

  if (!res || !res.data) return <p>No categories</p>;

  return (
    <>
      <CategoriesGrid categories={res.data} />
    </>
  );
};

export default CategoryWrapperDesktop;
