"use client";

import ProductCard from "@/components/cards/ProductCard";
import Container from "@/components/Container";
import { cn } from "@/lib/utils";
import { useShowHeader } from "@/store/useShowHeader";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import { motion, Variants, AnimatePresence } from "framer-motion";
import { LoaderCircle } from "lucide-react";
import { useEffect, useState } from "react";
import { useInView } from "react-intersection-observer";
import FilterCategoryWidgets from "../_components/filter/FilterCategoryWidgets";
import FilterDrawerMobile from "../_components/FilterDrawerMobile";
import SortBySelectOption from "../_components/filter/SortBySelectOption";
import SkeletonProduct from "./SkeletonProduct";
import { useCategoryStore } from "@/store/filter/useCategoryStore";
import { useCategoryProducts } from "@/hooks/filter/useCategoryProducts";

const ProductFilterSection = ({ allCategories }: { allCategories: CategoryNode[] }) => {
  const { showHeader } = useShowHeader();
  const { data, isLoading, isFetching } = useCategoryProducts();
  const {
    page,
    setFilter,
    category,
    name,
    brand_id,
    min_price,
    max_price,
    sort_by,
    rating,
  } = useCategoryStore();

  const [products, setProducts] = useState<ProductType[]>([]);

  const { ref, inView } = useInView({
    threshold: 0.1,
    triggerOnce: false,
  });

  // Accumulate products
  // useEffect(() => {
  //   if (!data?.data) return;

  //   if (page === 1) {
  //     setProducts(data.data);
  //   } else {
  //     setProducts((prev) => [...prev, ...data.data]);
  //   }
  // }, [data?.data, page]);

  useEffect(() => {
    if (!data?.data) return;

    if (page === 1) {
      setProducts([]); // clear previous (important)
      setTimeout(() => setProducts(data.data), 0);
    } else {
      setProducts((prev) => [...prev, ...data.data]);
    }
  }, [data?.data, page]);

  // hasMore based on CURRENT page meta
  const hasMore =
    data?.meta?.current_page != null &&
    data.meta.current_page < data.meta.last_page;

  // Infinite scroll
  useEffect(() => {
    if (inView && hasMore && !isFetching) {
      setFilter("page", page + 1);
    }
  }, [inView, hasMore, isFetching, page, setFilter]);

  // Animation variants
  const variant: Variants = {
    initial: { opacity: 0, y: 50 },
    animate: (i: number) => ({
      opacity: 1,
      y: 0,
      transition: {
        delay: (i % 10) * 0.05,
        duration: 0.4,
        ease: "easeOut",
      },
    }),
  };

  return (
    <section className="mt-4 pb-4 md:mt-12">
      <Container className="flex items-start gap-10">
        {/* Filter widget */}
        {/* <div
          className={cn(
            "sticky top-0 hidden py-2 transition-all lg:block",
            showHeader && "top-[140px]",
          )}
        >
          <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
            <ScrollArea.Viewport
              className={cn(
                "p-4",
                showHeader ? "h-[calc(100dvh-140px)]" : "h-screen",
              )}
              onWheel={(e) => e.stopPropagation()}
            >
            </ScrollArea.Viewport>
            <ScrollArea.Scrollbar
              orientation="vertical"
              className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
            >
              <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
            </ScrollArea.Scrollbar>
          </ScrollArea.Root>
        </div> */}

        {/* Products */}
        <div className="flex-1">
          <div className="mb-6 flex items-center justify-between gap-4">
            <div className="flex flex-1 gap-3  items-center ">
              <FilterCategoryWidgets allCategories={allCategories} />
              <SortBySelectOption />
            </div>
            <FilterDrawerMobile />
          </div>

          {isLoading && page === 1 ? (
            <SkeletonProduct />
          ) : products.length === 0 ? (
            <div className="flex min-h-[400px] items-center justify-center">
              <p className="text-site-gray-500 text-lg">No products found</p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-6 lg:grid-cols-5">
                <AnimatePresence mode="popLayout">
                  {products.map((product, i) => (
                    <motion.div
                      key={`${product.id}-${category}-${brand_id}-${min_price}-${max_price}-${sort_by}-${rating}-${i}`}
                      custom={i}
                      variants={variant}
                      initial="initial"
                      whileInView="animate"
                      viewport={{ once: true, amount: 0.1 }}
                    >
                      <ProductCard {...product} />
                    </motion.div>
                  ))}
                </AnimatePresence>
              </div>

              {/* Infinite Scroll Loader */}
              {(hasMore || isFetching) && (
                <div
                  ref={ref}
                  className="my-6 flex h-16 items-center justify-center"
                >
                  {isFetching ? (
                    <p className="bg-site-gray-50 text-site-gray-900 flex items-center rounded-full px-4 py-2 text-sm font-semibold">
                      <LoaderCircle className="mr-1 animate-spin duration-100" />
                      Loading more...
                    </p>
                  ) : (
                    <div className="h-6 w-6" />
                  )}
                </div>
              )}
            </>
          )}
        </div>
      </Container>
    </section>
  );
};

export default ProductFilterSection;
