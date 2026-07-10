import Container from "@/components/Container";
import React from "react";
import SideBar from "../../_components/SideBar";
import { fetcher } from "@/lib/fetcher";
import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import PageHeader from "@/components/PageHeader";

interface DataType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

interface Props {
  children: React.ReactNode;
}

const Layout = async ({ children }: Props) => {
  const data = await fetcher<ApiResponseType<DataType[]>>(
    "/business-settings",
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  if (!data || data.data.length === 0) {
    return null;
  }

  // Extract sidebar links
  const sidebarLinksString = data?.data.filter(
    (item) => item.type === "widget_one_links",
  )[0].value;

  const sidebarLabelsString = data?.data.filter(
    (item) => item.type === "widget_one_labels",
  )[1].value;

  // Parse them safely
  const sidebarLinks = sidebarLinksString ? JSON.parse(sidebarLinksString) : [];
  const sidebarLabels = sidebarLabelsString
    ? JSON.parse(sidebarLabelsString)
    : [];

  return (
    <section className="pb-20">
      <Container className="">{children}</Container>
    </section>
  );
};

export default Layout;
