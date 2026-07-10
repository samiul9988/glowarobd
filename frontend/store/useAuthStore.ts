"use client";

import { getServerSession, logoutAction, User } from "@/lib/getServerSession";
import { setAuthToken, clearAuthToken } from "@/lib/auth-utils";
import { useEffect } from "react";
import { create } from "zustand";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";

interface AuthState {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  logout: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  isLoading: true,
  isAuthenticated: false,

  logout: async () => {
    set({ isLoading: true });

    try {
      const success = await logoutAction();

      if (success) {
        clearAuthToken(); // Clear token from storage
        set({ user: null, isAuthenticated: false, isLoading: false });
      } else {
        set({ isLoading: false });
      }
    } catch (err) {
      set({ isLoading: false });
      console.error("Logout error:", err);
    }
  },
}));

export const useSession = () => {
  const { user, isLoading, isAuthenticated, logout } = useAuthStore();

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const user = await getServerSession();

        useAuthStore.setState({
          user,
          isAuthenticated: !!user,
          isLoading: false,
        });

        // Set token for API calls if user is authenticated
        if (user && typeof window !== "undefined") {
          const token = document.cookie
            .split("; ")
            .find((row) => row.startsWith("access_token="))
            ?.split("=")[1];

          if (token) {
            setAuthToken(token);
          }
        }
      } catch {
        useAuthStore.setState({
          user: null,
          isAuthenticated: false,
          isLoading: false,
        });
      }
    };

    fetchUser();
  }, []);

  return { user, isLoading, isAuthenticated, logout };
};
