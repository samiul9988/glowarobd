import { create } from "zustand";

interface FormState {
  // Auth
  isAuth: boolean;
  setIsAuth: (value: boolean) => void;

  // Login
  isLogin: boolean;
  setIsLogin: (value: boolean) => void;

  // Forgot password
  isForgot: boolean;
  setIsForgot: (value: boolean) => void;

  // Verify OTP
  isVerifyOTP: boolean;
  setIsVerifyOTP: (value: boolean) => void;

  // Reset password
  isReset: boolean;
  setIsReset: (value: boolean) => void;

  // Signup OTP verify
  isSignupOTP: boolean;
  setIsSignupOTP: (value: boolean) => void;

  // Login with OTP verify
  isLoginWithOTP: boolean;
  setIsLoginWithOTP: (value: boolean) => void;

  // User Id
  userId: string;
  setUserId: (value: string) => void;

  // User phone or email
  contact: string;
  setContact: (value: string) => void;

  resetForm: () => void;
}

export const useFormControl = create<FormState>((set) => ({
  isAuth: true,
  setIsAuth: (value) => set({ isAuth: value }),

  isLogin: true,
  setIsLogin: (value) => set({ isLogin: value }),

  isForgot: false,
  setIsForgot: (value) => set({ isForgot: value }),

  isVerifyOTP: false,
  setIsVerifyOTP: (value) => set({ isVerifyOTP: value }),

  isReset: false,
  setIsReset: (value) => set({ isReset: value }),

  isSignupOTP: false,
  setIsSignupOTP: (value) => set({ isSignupOTP: value }),

  isLoginWithOTP: false,
  setIsLoginWithOTP: (value) => set({ isLoginWithOTP: value }),

  userId: "",
  setUserId: (value) => set({ userId: value }),

  contact: "",
  setContact: (value) => set({ contact: value }),

  resetForm: () =>
    set({
      isAuth: true,
      isLogin: true,
      isLoginWithOTP: false,
      isForgot: false,
      isVerifyOTP: false,
      isReset: false,
      isSignupOTP: false,
      userId: "",
      contact: "",
    }),
}));
