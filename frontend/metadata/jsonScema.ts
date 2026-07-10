import { imageBaseUrl, publicBaseUrl, siteName } from "@/config/apiConfig";
export const mainJsonLd = {
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": `${publicBaseUrl}#organization`,
      name: siteName,
      alternateName: "GlowaroSkincare",
      url: publicBaseUrl,
      logo: `${publicBaseUrl}/images/logo.png`,
      description:
        "GlowaroSkincare is a trusted beauty and skincare store offering premium products in Bangladesh.",
      sameAs: [
        "https://www.facebook.com/emartwayskincare",
        "https://www.instagram.com/emartwayskincare/",
        "https://www.youtube.com/channel/UCQPyA3Vf20QLK8yIF-62IOA",
      ],
    },
    {
      "@type": "WebSite",
      "@id": `${publicBaseUrl}#website`,
      url: publicBaseUrl,
      name: siteName,
      publisher: { "@id": `${publicBaseUrl}#organization` },
      potentialAction: {
        "@type": "SearchAction",
        target: `${publicBaseUrl}search?keyword={search_term_string}`,
        "query-input": "required name=search_term_string",
      },
    },
  ],
};

export const getProductSchema = async (product: ProductDetailType) => ({
  "@context": "https://schema.org/",
  "@type": "Product",
  name: product.name,
  image: imageBaseUrl + product.thumbnail_image,
  description: product.description,
  brand: {
    "@type": "Brand",
    name: product.brand.name || siteName,
  },
  offers: {
    "@type": "Offer",
    url: `${publicBaseUrl}/${product.slug}`,
    priceCurrency: "BDT",
    price: product.calculable_price.toFixed(2),
    availability:
      product.current_stock > 0
        ? "https://schema.org/InStock"
        : "https://schema.org/OutOfStock",
    itemCondition: "https://schema.org/NewCondition",
    seller: {
      "@type": "Organization",
      name: { siteName },
    },
  },
});
