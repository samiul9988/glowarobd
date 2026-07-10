import { create } from "zustand";

interface TokenState {
  accessToken: string | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  setAccessToken: (token: string | null) => void;
  finishLoading: () => void;
}

export const useToken = create<TokenState>((set) => ({
  accessToken: null,
  isLoading: true,
  isAuthenticated: false,

  setAccessToken: (token) => {
    if (token) localStorage.setItem("access_token", token);
    else localStorage.removeItem("access_token");

    set({ accessToken: token, isAuthenticated: !!token });
  },

  finishLoading: () => set({ isLoading: false }),
}));
