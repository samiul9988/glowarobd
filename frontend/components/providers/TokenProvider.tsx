"use client";

import { useToken } from "@/store/useTokenStore";
import { useEffect } from "react";

import { GoogleOAuthProvider } from "@react-oauth/google";

export default function TokenProvider({
  token,
  children,
}: {
  token: string | null;
  children: React.ReactNode;
}) {
  const { setAccessToken, finishLoading } = useToken();

  useEffect(() => {
    setAccessToken(token);
    finishLoading();
  }, [token, setAccessToken, finishLoading]);

  return (
    <GoogleOAuthProvider clientId={process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID!}>
      {children}
    </GoogleOAuthProvider>
  );
}
