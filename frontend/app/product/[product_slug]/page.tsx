import ProductDescriptionTabs from "@/app/product/[product_slug]/_sections/ProductDescriptionTabs";
import {
  apiBaseUrl,
  defaultImage,
  imageBaseHostUrl,
  publicBaseUrl,
  siteName,
} from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import { notFound } from "next/navigation";
import DetailsSection from "./_sections/DetailsSection";
import FAQSection from "./_sections/FAQSection";
import ProductFeatures from "./_sections/ProductFeatures";
import RecommendedProductSection from "./_sections/RecommendedProductSection";
import ReviewSection from "./_sections/ReviewSection";
import { getProductSchema } from "@/metadata/jsonScema";
import RealtedProduct from "./_sections/RealtedProduct";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

interface Props {
  params: Promise<{ product_slug: string }>;
}

const ProductDetails = async ({ params }: Props) => {
  const { product_slug } = await params;

  const data = await cacheableFetcher<ApiResponseType<ProductDetailType[]>>(
    `/products/${product_slug}`,
    {
      baseUrl: apiBaseUrl,
      revalidate: 300,

    },
  );

  if (!data || !data?.data[0]?.id ) {
    return notFound();
  }

  const product = data.data[0]; // Get the first product from the array
  // Fetch reviews for this product
  const reviewsData = await fetcher<ReviewsResponseType>(
    `/reviews/product/${product.id}?page=1&per_page=${5}`,
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  const settings = await fetcher<ApiResponseType<SettingsType[]>>(
    "/business-settings",
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  const productJsonLd = await getProductSchema(product);
  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(productJsonLd) }}
      />
      <DetailsSection product={product} />
      {/* <ProductFeatures product={product} /> */}
      {/* <ReviewSection
        product={product}
        reviews={reviewsData}
        businessSettings={settings?.data}
      /> */}
      {/* <RealtedProduct productId={product?.id} icon={product?.thumbnail_image} /> */}

      {/* <FAQSection product={product} faqs={product?.custom_fields?.faqs} /> */}
      <RecommendedProductSection productId={product.id} />
    </>
  );
};

export default ProductDetails;

/**
 * Generate metadata for the product page
 * @param {Object} params - slug of the product
 */

export async function generateMetadata({ params }: Props) {
  const { product_slug } = await params;
  const data = await cacheableFetcher<ApiResponseType<ProductDetailType[]>>(
    `/products/${product_slug}`,
    {
      baseUrl: apiBaseUrl,
      next: {
        revalidate: REVALIDATE_TIME,
      },
    },
  );

  if (!data || data.data.length === 0) {
    return {
      title: "Product not found",
      description: "Product not found",
    };
  }

  const product = data && data.data[0];
  const ogImage =
    `${imageBaseHostUrl}${product?.thumbnail_image}` || defaultImage;
  const title = product?.meta_title ? product?.meta_title : product?.name;
  const description = product?.meta_description
    ? product?.meta_description
    : product?.name;
  const keywords = product?.tags;
  return {
    title: title,
    description: description,
    images: ogImage,

    openGraph: {
      title: title,
      description: description,
      url: `${publicBaseUrl}/products/${product_slug}`,
      siteName: siteName,
      keywords: keywords,

      images: [
        {
          url: ogImage,
          width: 1051,
          height: 553,
        },
      ],
    },
  };
}
