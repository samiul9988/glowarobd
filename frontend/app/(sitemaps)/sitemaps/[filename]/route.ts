import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { NextResponse } from "next/server";

interface Params {
  params: Promise<{
    filename: string;
  }>;
}

export async function GET(request: Request, { params }: Params) {
  try {
    const { filename } = await params;

    const res = await fetch(`${apiBaseUrl}/sitemaps/${filename}`, {
      next: { revalidate: REVALIDATE_TIME },
    });

    if (!res.ok)
      return new NextResponse("Failed to fetch sitemap file", {
        status: res.status,
      });

    const xml = await res.text();

    return new NextResponse(xml, {
      headers: {
        "Content-Type": "application/xml",
        "Cache-Control": `public, s-maxage=${REVALIDATE_TIME}, stale-while-revalidate=86400`,
      },
    });
  } catch (error) {
    console.error("Error fetching dynamic sitemap:", error);
    return new NextResponse("Internal Server Error", { status: 500 });
  }
}
