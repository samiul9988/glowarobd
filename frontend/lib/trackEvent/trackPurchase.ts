import { getBusinessSettings } from "@/actions/getBusinessSettings";
import { sendGTMEvent } from "@next/third-parties/google";

export async function trackPurchase(order: {
  id: string | number;
  total: number;
  currency?: string;
  items: {
    id: string | number;
    name: string;
    category?: string;
    price: number;
    quantity: number;
  }[];
}) {
  const businessSettings = (await getBusinessSettings()) as BusinessDataType[];
  const isGAOn =
    businessSettings.find((item) => item.type === "google_analytics")?.value ==
    "1";
  // const isPixelOn =
  //   businessSettings.find((item) => item.type === "facebook_pixel")?.value ==
  //   "1";

  const currency = order.currency || "BDT";
  const value = Number(order.total);

  // --- Google Analytics / GTM Purchase Event ---
  if (isGAOn) {
    sendGTMEvent({
      event: "purchase",
      transaction_id: order.id,
      value,
      currency,
      items: order.items.map((item) => ({
        item_id: item.id,
        item_name: item.name,
        category: item.category || "",
        quantity: item.quantity,
        price: item.price,
      })),
    });
  }

  // --- Facebook Pixel Purchase Event ---
  // if (isPixelOn && typeof window !== "undefined" && window.fbq) {
  //   window.fbq("track", "Purchase", {
  //     content_ids: order.items.map((item) => item.id),
  //     content_type: "product",
  //     contents: order.items.map((item) => ({
  //       id: item.id,
  //       quantity: item.quantity,
  //       item_price: item.price,
  //     })),
  //     value,
  //     currency,
  //   });
  // }
}
