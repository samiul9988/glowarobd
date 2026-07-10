import { create } from "zustand";

interface ShowHeaderState {
  showHeader: boolean;
  setShowHeader: (value: boolean) => void;
}

export const useShowHeader = create<ShowHeaderState>((set) => ({
  showHeader: true,
  setShowHeader: (value) => set({ showHeader: value }),
}));
