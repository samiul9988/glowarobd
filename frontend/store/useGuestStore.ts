import { create } from "zustand";
import { persist } from "zustand/middleware";
import { generateGuestId } from "@/utils/generateGuestId";
import { useToken } from "./useTokenStore";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";

interface GuestUserState {
  guestId: string | null;
  createGuestId: () => string | null;
  clearGuestId: () => void;
  syncGuestIdWithToken: () => void;
}

export const useGuestUserId = create<GuestUserState>()(
  persist(
    (set, get) => ({
      guestId: null,

      createGuestId: () => {
        const existingGuestId = get().guestId;
        if (existingGuestId) return existingGuestId;

        const newGuestId = generateGuestId();
        set({ guestId: newGuestId });
        return newGuestId;
      },

      clearGuestId: () => set({ guestId: null }),

      syncGuestIdWithToken: () => {
        const token = useToken.getState().accessToken;
        if (token) {
          set({ guestId: null });
          removeLocalStorageKey("guestUserId");
        } else {
          const existingGuestId = get().guestId;
          if (!existingGuestId) {
            const newGuestId = generateGuestId();
            set({ guestId: newGuestId });
          }
        }
      },
    }),
    {
      name: "guestUserId",
    }
  )
);
