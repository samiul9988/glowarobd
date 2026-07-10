"use client";
import Container from "@/components/Container";
import { imageBaseHostUrl } from "@/config/apiConfig";
import Image from "next/image";

interface Feature {
  id: string;
  imageSrc: string;
  title: string;
}

interface ProductFeaturesProps {
  product?: ProductDetailType;
  features?: Feature[];
  className?: string;
}

const ProductFeatures: React.FC<ProductFeaturesProps> = ({
  product,
  features,
  className = "",
}) => {
  // Get highlights from product API data
  const getHighlights = (): Feature[] => {
    if (
      product?.custom_fields?.highlight?.value &&
      Array.isArray(product.custom_fields.highlight.value)
    ) {
      return product.custom_fields.highlight.value.map(
        (highlight: any, index: number) => ({
          id: index.toString(),
          imageSrc: highlight.image
            ? `${imageBaseHostUrl}${highlight.image}`
            : "/images/placeholder.png",
          title: highlight.title,
        }),
      );
    }
    return features || [];
  };

  const displayFeatures = getHighlights();

  // Don't render if no features
  if (displayFeatures.length === 0) {
    return null;
  }

  return (
    <Container>
      <div className="py-[50px]">
        <div
          className={`w-full rounded-[10px] bg-gray-50 py-6 lg:py-10 ${className}`}
        >
          <div
            className={`grid gap-6 lg:gap-8 ${
              displayFeatures.length === 1
                ? "grid-cols-1 justify-center"
                : displayFeatures.length === 2
                  ? "grid-cols-2"
                  : displayFeatures.length === 3
                    ? "grid-cols-3 md:grid-cols-3"
                    : displayFeatures.length === 4
                      ? "grid-cols-3 md:grid-cols-4"
                      : "grid-cols-3 md:grid-cols-3 lg:grid-cols-6"
            }`}
          >
            {displayFeatures.map((feature) => (
              <div
                key={feature.id}
                className="flex flex-col items-center text-center"
              >
                {/* Icon Circle */}
                <div className="md:w-14items-center mb-3 flex h-12 w-12 justify-center md:h-14 lg:mb-4 lg:h-20 lg:w-20">
                  <Image
                    src={feature.imageSrc}
                    alt={feature.title}
                    width={72}
                    height={72}
                    className="rounded-full object-contain"
                    onError={(e) => {
                      const target = e.target as HTMLImageElement;
                      target.src = "/images/beauty-1.png";
                    }}
                  />
                </div>

                {/* Title */}
                <h3 className="text-site-gray-900 text-center text-base leading-[20px] tracking-wide md:text-[23px]">
                  {feature.title}
                </h3>
              </div>
            ))}
          </div>
        </div>
      </div>
    </Container>
  );
};

export default ProductFeatures;
