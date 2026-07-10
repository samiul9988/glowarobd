import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";
import { cookies } from "next/headers";

export async function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;

  /* ============================================================
   🔥 PREVIEW MODE (DEVELOPMENT TIME ONLY)
   👉 This whole block should be REMOVED in production.
  ============================================================ */
  //const previewPassword = process.env.NEXT_PUBLIC_PREVIEW_PASSWORD;

  //if (previewPassword) {
  //  const previewAuth = request.cookies.get("preview_auth");
  //  const isPreviewLoginPage = pathname === "/preview-login";

  //  // Already logged in, trying to access preview-login → redirect home
  //  if (isPreviewLoginPage && previewAuth?.value === "1") {
  //    return NextResponse.redirect(new URL("/", request.url));
  //  }

  //  // Not logged in, trying to access any page except preview-login → redirect login
  //  if (!isPreviewLoginPage && previewAuth?.value !== "1") {
  //    return NextResponse.redirect(new URL("/preview-login", request.url));
  //  }
  //}
  /* ============================================================
      END PREVIEW MODE — REMOVE LATER
  ============================================================ */

  /* ============================================================
      🔐 NORMAL AUTH SYSTEM (KEEP THIS IN FUTURE)
  ============================================================ */

  const cookieStore = await cookies();
  const token = cookieStore.get("access_token")?.value;

  const protectedRoutes = [
    "/dashboard",
    "/dashboard/:path*",
    "/support-ticket",
    "/profile",
    "/purchase-history",
  ];

  const authRoutes = ["/auth/login", "/auth/register", "/auth/forgot-password"];

  // If a protected route & not logged in → redirect to login
  if (protectedRoutes.includes(pathname) && !token) {
    return NextResponse.redirect(new URL("/auth/login", request.url));
  }

  // If logged in & trying to access login/register → send to home
  if (authRoutes.includes(pathname) && token) {
    return NextResponse.redirect(new URL("/", request.url));
  }

  // Mistyped /admin
  if (pathname === "/admin") {
    return NextResponse.rewrite("https://api.emartwayskincare.com.bd");
  }

  const isProtected = protectedRoutes.some((path) => pathname.startsWith(path));

  if (isProtected && !token) {
    const redirectUrl = new URL("/", request.url);
    redirectUrl.searchParams.set("authModal", "open");
    return NextResponse.redirect(redirectUrl);
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    // Preview system matcher — REMOVE IN FUTURE
    "/((?!api|_next/static|_next/image|favicon.ico|images|.*\\..*|preview-login).*)",

    // KEEP (auth system)
    "/dashboard/:path*",
    "/purchase-history",
    "/support-ticket",
    "/profile",
    "/auth/register",
    "/auth/forgot-password",
    "/auth/reset-password",
    "/auth/verify-email",
    "/auth/verify-phone",
    "/auth/email-verification",
    "/phone-verification",
    "/admin",
  ],
};
