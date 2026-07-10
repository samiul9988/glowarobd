import { NextResponse } from "next/server";

export async function POST(request: Request) {
  const { password } = await request.json();

  if (password === process.env.NEXT_PUBLIC_PREVIEW_PASSWORD) {
    const response = NextResponse.json({ ok: true });

    // Set cookie on the response
    response.cookies.set("preview_auth", "1", {
      httpOnly: true,
      secure: process.env.NEXT_PUBLIC_NODE_ENV === "production",
      path: "/",
      maxAge: 60 * 60 * 12, // 12 hours
    });

    return response;
  }

  return new NextResponse("Unauthorized", { status: 401 });
}
