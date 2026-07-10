import PageHeader from "@/components/PageHeader";
import CartSection from "./_sections/CartSection";
import { ChevronRight } from "lucide-react";
import { metaData } from "@/metadata/staticMetaData";

export default async function CartPage() {
  return (
    <>
      <PageHeader
        title="My Bag"
        pageBanner={"/images/page-baner.png"}
        className=" max-h-[150px]  py-8 md:py-10"
        breadcrumb={
          <span className="flex items-center gap-2 text-sm font-medium text-white/50">
            Home <ChevronRight size={15} />
            <span className="text-white/80">My Bag</span>
          </span>
        }
       
      />
      <CartSection />
    </>
  );
}

export const metadata = {
  title: metaData.cart.title,
  description: metaData.cart.description,
};
