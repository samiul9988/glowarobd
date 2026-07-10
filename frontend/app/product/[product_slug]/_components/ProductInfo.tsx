"use client";

import useDeviceDetect from "@/hooks/useDeviceDetect";
import Link from "next/link";

interface DetailsProps {
  product: ProductDetailType;
}

export default function ProductInfo({ product }: DetailsProps) {
//   const { type } = useDeviceDetect();

//   // all possible links
//   const appLinks = {
//     android: "https://play.google.com/store/apps/details?id=bd.com.emartway",
//     ios: "https://apps.apple.com/us/app/emartway-skincare/id1619605100",
//   };

  // determine which link to show based on device type
//   let appLink = appLinks.android;

//   if (type === "ios") {
//     appLink = appLinks.ios;
//   } else if (type === "android") {
//     appLink = appLinks.android;
//   } else if (type === "desktop") {
//     // for macOS → iOS link, for Windows/Linux → Android link
//     const platform = navigator?.platform?.toLowerCase() || "";
//     if (platform.includes("mac")) {
//       appLink = appLinks.ios;
//     } else {
//       appLink = appLinks.android;
//     }
//   }

  return (
    <div className="space-y-4 md:space-y-8 ">
        <div className="bg-site-gray-50  rounded-[16px]  p-4 md:p-6">
            <div className="grid grid-cols-1  gap-y-3 ">

                {/* SKU */}
                {product?.sku && (
                <div className="flex items-start gap-3">
                    <p className="text-site-gray-300 text-sm font-semibold w-32">
                    SKU Code
                    </p>
                    <p className="text-site-gray-900 text-sm font-medium">
                    : {product?.sku}
                    </p>
                </div>
                )}

                {/* Stock */}
                {/* <div className="flex items-start gap-3">
                <p className="text-site-gray-300 text-sm font-semibold w-32">
                    Stock
                </p>
                <p className="text-site-gray-900 text-sm font-medium">
                    : {product?.current_stock} {product?.unit} Available
                </p>
                </div> */}

                {/* Skin Concern */}
                {product.custom_fields?.skin_concern?.value &&
                Array.isArray(product.custom_fields.skin_concern.value) && (
                   
                    <div className="flex items-start gap-3">
                        <p className=" w-32 text-site-gray-300 text-sm font-semibold">
                        Skin Concern
                        </p>
                        <p className="text-site-gray-900 text-sm font-medium">
                        :{" "}
                        {product.custom_fields.skin_concern.value
                            .map((item) => item.title)
                            .join(", ")}
                        </p>
                    </div>
                )}

                {/* Skin Type */}
                {product.custom_fields?.skin_type?.value &&
                Array.isArray(product.custom_fields.skin_type.value) && (
                    <div className="flex items-start gap-3">
                        <p className="text-site-gray-300 text-sm font-semibold w-32">
                        Skin Type
                        </p>
                        <p className="text-site-gray-900 text-sm font-medium">
                        :{" "}
                        {product.custom_fields.skin_type.value
                            .map((item) => item.title)
                            .join(", ")}
                        </p>
                    </div>
                )}

                {/* Delivery */}
                <div className="flex items-start gap-3">
                    <p className="text-site-gray-300 text-sm font-semibold w-32">
                        Estimate Delivery
                    </p>
                    <p className="text-site-gray-900 text-sm font-medium">
                        :{" "}
                        {product?.shipping_discount?.status
                        ? "Free Delivery"
                        : "Within 1 to 3 Days"}
                    </p>
                </div>

            </div>
        </div>
    </div>
  );
}
