import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { NextResponse } from "next/server";

export async function GET() {
  try {
    const res = await fetch(`${apiBaseUrl}/facebook-feed.xml`, {
      cache: "no-store",
    });

    if (!res.ok) {
      return new NextResponse("Failed to fetch Facebook feed", {
        status: res.status,
      });
    }

    const xml = await res.text();

    return new NextResponse(xml, {
      headers: {
        "Content-Type": "application/xml",
        "Cache-Control": `public, s-maxage=${3600}, stale-while-revalidate=86400`,
      },
    });
  } catch (error) {
    console.error("Error fetching Facebook feed:", error);
    return new NextResponse("Internal Server Error", { status: 500 });
  }
}
