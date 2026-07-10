import { create } from "zustand";

interface MuteState {
  isMuted: boolean;
  setIsMuted: () => void;
}

export const useGlobalMute = create<MuteState>((set) => ({
  isMuted: true,
  setIsMuted: () => set((state) => ({ isMuted: !state.isMuted })),
}));
