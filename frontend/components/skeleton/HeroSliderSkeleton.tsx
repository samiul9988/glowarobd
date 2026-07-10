import React from "react";
import Container from "../Container";
import { Skeleton } from "../ui/skeleton";

const HeroSliderSkeleton = () => {
  return (
    <div className="my-4 md:my-6">
      <Container>
        <Skeleton className="w-full h-[250px] md:h-[250px] lg:h-[528px] rounded-[20px] bg-slate-200" />
      </Container>
    </div>
  );
};

export default HeroSliderSkeleton;
