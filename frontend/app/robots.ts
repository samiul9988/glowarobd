import { publicBaseUrl } from "@/config/apiConfig";
import { MetadataRoute } from "next";

export default function robots(): MetadataRoute.Robots {
  return {
    rules: [
      {
        userAgent: "*",
        allow: "/",
        disallow: ["/api/*", "/dashboard/*", "/checkout/*", "/cart/*"],
      },
      // Disallow bots for security
      { userAgent: "AhrefsBot", disallow: "/" },
      { userAgent: "MJ12bot", disallow: "/" },
      { userAgent: "BLEXBot", disallow: "/" },
      { userAgent: "DotBot", disallow: "/" },
      { userAgent: "Screaming Frog SEO Spider", disallow: "/" },
      { userAgent: "Offline Explorer", disallow: "/" },
      { userAgent: "TeleportPro", disallow: "/" },
      { userAgent: "Web Image Collector", disallow: "/" },
      { userAgent: "LinkWalker", disallow: "/" },
      { userAgent: "SemrushBot", disallow: "/" },
      { userAgent: "ZoominfoBot", disallow: "/" },
    ],

    sitemap: `${publicBaseUrl}/sitemap.xml`,
  };
}
