"use client";

import ProductCard from "@/components/cards/ProductCard";
import Container from "@/components/Container";
import { motion, Variants, AnimatePresence } from "framer-motion";
import { LoaderCircle } from "lucide-react";
import { useEffect, useState } from "react";
import { useInView } from "react-intersection-observer";
import { useBrandStore } from "@/store/filter/useBrandStore";
import { useBrandProducts } from "@/hooks/filter/useBrandProducts";
import FilterBrandDrawerMobile from "../_components/FilterBrandDrawerMobile";
import FilterBrandInlineWidgets from "@/app/(brand)/brand/_components/filter/FilterBrandInlineWidgets";
import SortBySelectBrandOption from "../_components/filter/SortBySelectBrandOption";
import SkeletonProduct from "./SkeletonProduct";

type ProductCardWrapperProps = {
  product: ProductType;
  index: number;
};

const ProductCardWrapper = ({ product, index }: ProductCardWrapperProps) => {
  const { ref, inView } = useInView({
    threshold: 0.1,
    triggerOnce: true,
  });

  const variant: Variants = {
    initial: { opacity: 0, y: 50 },
    animate: {
      opacity: 1,
      y: 0,
      transition: {
        delay: (index % 12) * 0.05,
        duration: 0.4,
        ease: "easeOut",
      },
    },
  };

  return (
    <motion.div
      ref={ref}
      variants={variant}
      initial="initial"
      animate={inView ? "animate" : "initial"}
    >
      <ProductCard {...product} />
    </motion.div>
  );
};

const ProductFilterBrandSection = () => {
  const { data, isLoading, isFetching } = useBrandProducts();
  const { page, setFilter } = useBrandStore();
  const [products, setProducts] = useState<ProductType[]>([]);

  const { ref, inView } = useInView({
    threshold: 0.1,
    triggerOnce: false,
  });

  // Accumulate products
  useEffect(() => {
    if (!data?.data) return;

    if (page === 1) {
      setProducts([]); // clear previous products
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

  return (
    <section className="mt-4 pb-4 md:mt-12">
      <Container className="flex items-start gap-10">
        <div className="flex-1">
          <div className="mb-6 flex items-center justify-between gap-4">
            <div className="flex flex-1 items-center gap-3">
              <FilterBrandInlineWidgets />
              <SortBySelectBrandOption />
            </div>
            <FilterBrandDrawerMobile />
          </div>

          {isLoading && page === 1 ? (
            <SkeletonProduct />
          ) : !isLoading && data?.meta?.total === 0 ? (
            <div className="flex min-h-[400px] items-center justify-center">
              <p className="text-site-gray-500 text-lg">No products found</p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-6 lg:grid-cols-5">
                <AnimatePresence mode="popLayout">
                  {products.map((product, i) => (
                    <ProductCardWrapper
                      key={`${product.id}-${i}`}
                      product={product}
                      index={i}
                    />
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

export default ProductFilterBrandSection;
