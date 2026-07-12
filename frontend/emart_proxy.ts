import { cookies } from "next/headers";
import type { NextRequest } from "next/server";
import { NextResponse } from "next/server";

export async function proxy(request: NextRequest) {
  // Get cookie
  const cookieStore = await cookies();
  const token = cookieStore.get("access_token")?.value;
  const { pathname } = request.nextUrl;

  // Protected routes
  const protectedRoutes = [
    "/dashboard",
    "/dashboard/:path*",
    "/support-ticket",
    "/profile",
    "/purchase-history",
  ];

  // Auth routes
  const authRoutes = ["/login", "/register", "/forgot-password"];

  // Redirect to login page if not logged in and trying to access a protected route.
  if (protectedRoutes.includes(request.nextUrl.pathname) && !token) {
    return NextResponse.redirect(new URL("/login", request.url));
  }

  // Redirect to home page if logged in and trying to access an auth route.
  if (authRoutes.includes(request.nextUrl.pathname) && token) {
    return NextResponse.redirect(new URL("/", request.url));
  }

  // 👇 Check if someone typed /amdin (mistyped)
  if (pathname === "/admin") {
    return NextResponse.rewrite("https://api.glowaro.com");
  }

  const isProtected = protectedRoutes.some((path) => pathname.startsWith(path));

  if (isProtected && !token) {
    const redirectUrl = new URL("/", request.url);
    redirectUrl.searchParams.set("authModal", "open"); // open modal
    return NextResponse.redirect(redirectUrl);
  }

  // Otherwise, allow
  return NextResponse.next();
}

// Apply middleware to all routes
export const config = {
  matcher: [
    "/dashboard/:path*",
    "/purchase-history",
    "/support-ticket",
    "/profile",
    "/register",
    "/forgot-password",
    "/reset-password",
    "/verify-email",
    "/verify-phone",
    "/email-verification",
    "/phone-verification",
    "/admin",
  ],
};
