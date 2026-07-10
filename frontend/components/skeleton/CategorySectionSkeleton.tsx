"use client";

import Container from "../Container";
import CategoryCardSkeleton from "./CategoryCardSkeleton";

const CategorySectionSkeleton = () => {
  return (
    <section className="py-5 lg:py-7">
      <Container>
        <div
          className="
            no-scrollbar 
            md:grid md:grid-cols-5 md:gap-x-5 md:gap-y-8 md:overflow-hidden
            
            grid 
            grid-flow-col 
            grid-rows-2 
            auto-cols-[calc((100%/3.5))] 
            gap-3 
            overflow-x-auto 
            overflow-y-hidden
          "
        >
          {Array.from({ length: 10 }).map((_, i) => (
            <div
              key={i}
              className="group w-full min-w-[100px] p-1"
            >
              <CategoryCardSkeleton />
            </div>
          ))}
        </div>
      </Container>
    </section>
  );
};

export default CategorySectionSkeleton;
