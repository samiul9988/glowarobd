import PageHeader from "@/components/PageHeader";
import { ChevronRight } from "lucide-react";
import CheckoutMain from "./_components/CheckoutMain";
import { metaData } from "@/metadata/staticMetaData";
import AuthConfirmation from "./_components/AuthConfirmation";

export default async function CheckoutPage() {
  return (
    <div className="min-h-screen  ">
      <PageHeader
        title="Checkout"
        pageBanner={"/images/page-baner.png"}
        className="max-h-[150px]  py-8 md:py-10"
       
       
      />
      {/* <AuthConfirmation /> */}
      <CheckoutMain />
    </div>
  );
}

export const metadata = {
  title: metaData.checkout.title,
  description: metaData.checkout.description,
};
