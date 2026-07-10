import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import Image from "next/image";
import Link from "next/link";

interface DataType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

interface ApiResponse {
  data: DataType[];
  status: number;
  success: boolean;
}

const Logo = async () => {
  const data = await fetcher<ApiResponse>("/business-settings", {
    baseUrl: apiBaseUrl,
    next: {
      revalidate: REVALIDATE_TIME,
    },
  });

  if (!data) {
    return null;
  }

  const headerLogo = data.data.filter(item => item.type === "header_logo")[0] ?? "";

  return (
    <Link href={"/"} className="md:pl-2 md:justify-start md:w-fit w-full  flex items-center justify-center -mt-[10px]">
      <Image src={headerLogo.image_url} alt="logo" width={161} height={44} priority className="object-contain" />
    </Link>
  );
};

export default Logo;
