import { getBusinessSettings } from "@/actions/getBusinessSettings";
import { sendGTMEvent } from "@next/third-parties/google";

export async function trackBeginCheckout(cart: {
  id?: string | number;
  total: number;
  currency?: string;
  items: TrackingItemType[]; // use simplified type
}) {
  const businessSettings = (await getBusinessSettings()) as BusinessDataType[];
  const isGAOn =
    businessSettings.find((item) => item.type === "google_analytics")?.value ===
    "1";
  // const isPixelOn =
  //   businessSettings.find((item) => item.type === "facebook_pixel")?.value ===
  //   "1";

  const currency = (cart.currency || "BDT").toUpperCase();
  const value = Number(cart.total);

  // GA4 / GTM
  if (isGAOn) {
    sendGTMEvent({
      event: "begin_checkout",
      value,
      currency,
      items: cart.items.map((item) => ({
        item_id: String(item.id),
        item_name: item.name,
        category: item.category || "",
        price: Number(item.price),
        quantity: Number(item.quantity),
      })),
    });
  }

  // // Facebook Pixel
  // if (isPixelOn && typeof window !== "undefined" && window.fbq) {
  //   window.fbq("track", "InitiateCheckout", {
  //     value,
  //     currency,
  //     content_type: "product",
  //     content_ids: cart.items.map((item) => String(item.id)),
  //     contents: cart.items.map((item) => ({
  //       id: String(item.id),
  //       quantity: Number(item.quantity),
  //       item_price: Number(item.price),
  //     })),
  //   });
  // }
}
