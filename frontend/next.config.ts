import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    unoptimized: true, // enable Next optimization
    remotePatterns: [
      { protocol: "https", hostname: "product.coder71.com", pathname: "**" },
      {
        protocol: "https",
        hostname: "fake-api-coder71.vercel.app",
        pathname: "**",
      },
      {
        protocol: "https",
        hostname: "staging.emartwayskincare.com.bd",
        pathname: "**",
      },
      {
        protocol: "https",
        hostname: "stg.emartwayskincare.com.bd",
        pathname: "**",
      },
      {
        protocol: "https",
        hostname: "emart-cdn.s3.ap-south-1.amazonaws.com",
        pathname: "**",
      },
      {
        protocol: "https",
        hostname: "emart-cdn.s3.ap-south-1.amazonaws.com",
      },
      {
        protocol: "https",
        hostname: "emart-cdn.s3.ap-south-1.amazonaws.com",
      },
      {
        protocol: "https",
        hostname: "control.glowaro.com",
      },
      {
        protocol: "http",
        hostname: "localhost",
      },
      { protocol: "http", hostname: "localhost", pathname: "**" },
    ],
    
  },
};

export default nextConfig;
