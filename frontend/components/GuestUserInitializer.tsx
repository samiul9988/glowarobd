"use client";

import { useEffect } from "react";
import { useToken } from "@/store/useTokenStore";
import { useGuestUserId } from "@/store/useGuestStore";
import { useSession } from "@/store/useAuthStore";

export function GuestUserInitializer() {
  const { accessToken } = useToken();
  const { user } = useSession();
  const syncGuestIdWithToken = useGuestUserId((s) => s.syncGuestIdWithToken);
  useEffect(() => {
    syncGuestIdWithToken();
  }, [accessToken, syncGuestIdWithToken, user]);

  return null; // this runs logic only
}
