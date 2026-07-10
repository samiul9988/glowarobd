import { create } from "zustand";

interface ShowState {
  show: boolean;
  setShow: (value: boolean) => void;
}

export const useDashboardSheet = create<ShowState>((set) => ({
  show: false,
  setShow: (value) => set({ show: value }),
}));
