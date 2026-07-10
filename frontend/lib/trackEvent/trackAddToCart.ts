import { getBusinessSettings } from "@/actions/getBusinessSettings";
import { sendGTMEvent } from "@next/third-parties/google";

export async function trackAddToCart(product: ProductDetailType) {
  const businessSettings = (await getBusinessSettings()) as BusinessDataType[];

  const isGAOn =
    businessSettings.find((item) => item.type === "google_analytics")?.value ==
    "1";
  // const isPixelOn =
  //   businessSettings.find((item) => item.type === "facebook_pixel")?.value ==
  //   "1";

  // const isGAOn = true;
  // const isPixelOn = true;
  const value = Number(product.calculable_price);
  // Google Analytics / GTM event
  if (isGAOn) {
    sendGTMEvent({
      event: "add_to_cart",
      currency: "BDT",
      value,
      items: [
        {
          item_id: product.id,
          item_name: product.name,
          category: product.category?.name || "",
          quantity: 1,
          price: value,
        },
      ],
    });
  }

  // Facebook Pixel tracking
  // if (isPixelOn && typeof window != "undefined" && window.fbq) {
  //   window.fbq("track", "AddToCart", {
  //     content_name: product.name,
  //     content_category: product.category?.name || "",
  //     content_ids: [product.id],
  //     content_type: "product",
  //     value,
  //     currency: "BDT",
  //   });
  // }
}
