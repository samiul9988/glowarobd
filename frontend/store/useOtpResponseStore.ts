import { create } from "zustand";

interface OtpState {
  otpResponse: any | null;
  setOtpResponse: (response: any) => void;
  clearOtpResponse: () => void;
}

export const useOtpStore = create<OtpState>((set) => ({
  otpResponse: null,
  setOtpResponse: (response) => set({ otpResponse: response }),
  clearOtpResponse: () => set({ otpResponse: null }),
}));
