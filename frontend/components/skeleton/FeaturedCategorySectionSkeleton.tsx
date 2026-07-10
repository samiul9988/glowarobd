import Container from "../Container";
import FeaturedCardSkeleton from "./FeaturedCardSkeleton";

const FeaturedCategorySectionSkeleton = () => {
  return (
    <div>
      <Container className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
        <FeaturedCardSkeleton />
        <FeaturedCardSkeleton />
        <FeaturedCardSkeleton />
        <FeaturedCardSkeleton />
      </Container>
    </div>
  );
};

export default FeaturedCategorySectionSkeleton;
