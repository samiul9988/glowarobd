import React from "react";
import Container from "../Container";
import SupportServiceCardSkeleton from "./SupportServiceCardSkeleton";

const SupportServiceSectionSkeleton = () => {
  return (
    <div>
      <Container className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
        <SupportServiceCardSkeleton />
        <SupportServiceCardSkeleton />
        <SupportServiceCardSkeleton />
        <SupportServiceCardSkeleton />
      </Container>
    </div>
  );
};

export default SupportServiceSectionSkeleton;
