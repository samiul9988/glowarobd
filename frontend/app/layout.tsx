import Footer from "@/components/layout/Footer";
import Header from "@/components/layout/Header";
// import { ThemeProvider } from "@/components/providers/theme-provider";
import { Inter } from "next/font/google";
import { Suspense } from "react";
import "./globals.css";

import LenisProvider from "@/components/providers/LenisProvider";
import ReactQueryProvider from "@/components/providers/ReactQueryProvider";
import TokenProvider from "@/components/providers/TokenProvider";
import localFont from "next/font/local";
import { cookies } from "next/headers";
import PopupModal from "@/components/modals/PopupModal";
import { GuestUserInitializer } from "@/components/GuestUserInitializer";
import {
  defaultImage,
  pixelAnalyticsId,
  publicBaseUrl,
  siteTitle,
} from "@/config/apiConfig";
import { getBusinessSettings } from "@/actions/getBusinessSettings";
import { mainJsonLd } from "@/metadata/jsonScema";
import GoogleAnalyticsBox from "@/components/Analytics/GoogleAnalyticsBox";
import Script from "next/script";
import MobileBottomNavigationProvider from "@/components/providers/MobileBottomNavigationProvider";
import { Toaster } from "react-hot-toast";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
});

export const sfprodisplay = localFont({
  src: [
    {
      path: "../fonts/SfProRegular.otf",
      weight: "400",
      style: "normal",
    },
     {
      path: "../fonts/sfprosemibold.otf",
      weight: "500",
      style: "normal",
    },
    {
      path: "../fonts/Sfprobold.otf",
      weight: "600",
      style: "normal",
    },
    {
      path: "../fonts/Sfprobold.otf",
      weight: "700",
      style: "normal",
    },
  ],
  variable: "--font-sfprodisplay",
  display: "swap",
});

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const cookieStore = await cookies();
  const token = cookieStore.get("access_token")?.value || null;

  return (
    <html lang="en" suppressHydrationWarning>
      <head>
        <Script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(mainJsonLd) }}
        />
        <GoogleAnalyticsBox />
      </head>
      <body
        className={` ${sfprodisplay.variable}  bg-site-background antialiased`}
        suppressHydrationWarning
      >
        <ReactQueryProvider>
          <TokenProvider token={token}>
            <LenisProvider>
              <MobileBottomNavigationProvider>
                {/* <PopupModal /> */}
                <Suspense fallback={null}>
                  <GuestUserInitializer />
                </Suspense>
                <Header />
                {children}
                <Suspense>
                  <Footer />
                </Suspense>
              </MobileBottomNavigationProvider>
            </LenisProvider>
          </TokenProvider>
          <Toaster />
        </ReactQueryProvider>
      </body>
    </html>
  );
}

export async function generateMetadata() {
  const settings = await getBusinessSettings();
  const metaTitle =
    settings.find((item) => item.type === "meta_title")?.value ||
    "Glowaro Skincare Limited | We Care About Your Skin";
  const metaDescription =
    settings.find((item) => item.type === "meta_description")?.value ||
    "Glowaro SKINCARE is trusted & Authentic Cosmetics Company with Best Price. Next Day Delivery. Shop the latest top brands like Somebymi, CeraVe, The Ordinary, Bioderma, Purito, Cosrx, Nature Republic, Innisfree, iUNIK, The Dermalix, Neogen etc glowaro Glowaroglowaro";
  const metaKeywords =
    settings.find((item) => item.type === "meta_keywords")?.value ||
    "glowaro,glowaro skincare,glowaro skincare,";
  const metaImage =
    settings.find((item) => item.type === "meta_image")?.value || defaultImage;
  return {
    title: metaTitle,
    description: metaDescription,
    keywords: metaKeywords,
    other: {
      ["fb:app_id"]: pixelAnalyticsId,
    },
    siteName: siteTitle,
    metadataBase: new URL("https://glowaro.com"),
    openGraph: {
      title: metaTitle,
      description: metaDescription,
      url: publicBaseUrl,
      siteName: siteTitle,
      images: [
        {
          url: metaImage,
          width: 1051,
          height: 553,
        },
      ],
      type: "website",
    },
    // icons: {
    //   icon: [
    //     { url: "/images/glowaro-favicon.png" },
    //     {
    //       url: "/images/glowaro-favicon.png",
    //       sizes: "32x32",
    //       type: "image/png",
    //     },
    //     {
    //       url: "/images/glowaro-favicon.png",
    //       sizes: "64x64",
    //       type: "image/png",
    //     },
    //   ],
    //   apple: [{ url: "/images/glowaro-favicon.png" }],
    // },
  };
}
