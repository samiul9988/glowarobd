import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { NextResponse } from "next/server";

export async function GET() {
  try {
    const res = await fetch(`${apiBaseUrl}/sitemap`, {
      next: {
        revalidate: REVALIDATE_TIME,
      },
    });

    if (!res.ok) {
      // Handle non-200 responses
      return new NextResponse("Failed to fetch sitemap", {
        status: res.status,
      });
    }

    const xml = await res.text();

    return new NextResponse(xml, {
      headers: {
        "Content-Type": "application/xml",
        "Cache-Control": `public, s-maxage=${REVALIDATE_TIME}, stale-while-revalidate=86400`,
      },
    });    
  } catch (error) {
    console.error("Error fetching sitemap:", error);
    return new NextResponse("Internal Server Error", { status: 500 });
  }
}
