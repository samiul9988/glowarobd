import Container from "@/components/Container";
import { Suspense } from "react";
import CategoryWrapperDesktop from "./CategoryWrapperDesktop";
import CategorySkeleton from "./_components/CategorySkeleton";
import CategoryWrapperMobile from "./CategoryWrapperMobile";
import { metaData } from "@/metadata/staticMetaData";
import PageHeader from "@/components/PageHeader";

const CategoriesPage = async () => {
  return (
    <>
      <PageHeader
        title="All Categories"
        className="max-h-[150px] bg-[linear-gradient(90deg,#FFE0E6_0%,#FBF8F9_49.04%,#FEEEFF_100%)] py-8 md:py-10"
        headingStyle="text-site-secondary-500"
        animateImg1Url="/images/category-header1.png"
        animateImg1Style="absolute -left-12 md:left-12 -bottom-[-20px] md:-bottom-[-30px] h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[146px] lg:w-[150px]"
        animateImg2Url="/images/category-header2.png"
        animateImg2Style="h-[100px] w-[100px] md:h-[120px] md:w-[120px] lg:h-[144px] lg:w-[144px] absolute -right-8 md:right-12 -bottom-[-20px] md:-bottom-[-25px]"
      />
      <section className="py-[30px] lg:py-[60px]">
        <Container className="">
          <Suspense fallback={<CategorySkeleton />}>
            <CategoryWrapperDesktop />
          </Suspense>
        </Container>

        {/* <Container className="md:hidden">
        <Suspense fallback={<CategorySkeleton />}>
          <CategoryWrapperMobile />
        </Suspense>
      </Container> */}
      </section>
    </>
  );
};

export default CategoriesPage;

export const metadata = {
  title: metaData.allCategories.title,
  description: metaData.allCategories.description,
};
