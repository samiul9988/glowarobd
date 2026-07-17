"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Eye, EyeOff, Mail, Phone } from "lucide-react";
import { useState, useTransition } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { useGoogleLogin } from "@react-oauth/google";
import { LoginAction } from "@/actions/loginAction";
import { FcGoogle } from "react-icons/fc";

// import { jwtDecode } from "jwt-decode";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { loginSchema } from "@/schema/loginSchema";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useFormControl } from "@/store/useFormControl";
import { usePathname, useRouter } from "next/navigation";
import toast from "react-hot-toast";
import { useGuestUserId } from "@/store/useGuestStore";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";
import { useWindowWidth } from "@/hooks/useWindowWidth";
import { apiBaseUrl } from "@/config/apiConfig";
import { GoogleAction } from "@/actions/GoogleAuth";
import { AnimatePresence, motion } from "framer-motion";
import { useLoadBusinessSettings } from "@/hooks/useLoadBusinessSettings";
import { loginWithOtpSchema } from "@/schema/loginWithOtpSchema";
import { loginWithOtp } from "@/actions/loginWithOtp";
import Image from "next/image";
import PrimaryButton from "@/components/buttons/PrimaryButton";
import SecondaryButton from "@/components/buttons/SecondaryButton";
import { OtpIcon } from "@/components/icons/icon-library";
import Link from "next/link";

type LoginFormValues = z.infer<typeof loginSchema>;

export interface GoogleLoginResponse {
  result: boolean;
  message: string;
  access_token: string;
  token_type: string;
  expires_at: string;
  user: User;
}

interface User {
  id: number;
  type: string;
  name: string;
  email: string;
  avatar: string | null;
  avatar_original: string;
  phone: string | null;
  gender: string | null;
  date_of_birth: string | null;
  customer_group: {
    id: number;
    name: string;
  };
}

interface LookUpRes {
  result: boolean;
  message: string;
}
const LoginForm = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [isPending, startTransition] = useTransition();
  const {
    setIsLogin,
    setIsForgot,
    setIsAuth,
    setIsLoginWithOTP,
    contact,
    setContact,
    setUserId,
    setIsVerifyOTP,
  } = useFormControl();
  const { guestId, clearGuestId } = useGuestUserId();
  const { setOpen } = useAuthModalStore();
  const width = useWindowWidth();
  const router = useRouter();
  const pathName = usePathname();
  const [isPhoneLogin, setIsPhoneLogin] = useState(true);
  const [isNext, setIsNext] = useState(false);
  const [isChanging, setIsChanging] = useState(false);
  // phone and email regex
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRegex = /^[0-9]{11,15}$/;

  const form = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      contact: "",
      password: "",
    },
  });

  function onSubmit(values: LoginFormValues) {
    startTransition(async () => {
      // const res = await LoginAction(values);
      const res = await LoginAction({
        ...values,
        temp_user_id: guestId as string,
      });

      if (res.success) {
        toast.success(res.message);
        clearGuestId();
        removeLocalStorageKey("guestUserId");
        setOpen(false);
        if (pathName === "/checkout") {
          if (typeof window !== "undefined") window.location.reload();
        } else {
          if (width > 992) {
            router.push("/dashboard");
          } else {
            if (typeof window !== "undefined") window.location.reload();
          }
        }
      } else {
        toast.error(res.message);
      }
    });
  }

  // Google Login Handler
  const handleSuccess = async (credentialResponse: any) => {
    const token = credentialResponse.access_token || credentialResponse.code;

    const res = await fetch(apiBaseUrl + "/auth/social-login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        provider_id: token,
        provider_name: "google",
        email: "",
      }),
    });

    const data = (await res.json()) as GoogleLoginResponse;

    if (data.result) {
      const res = await GoogleAction(data);
      if (res.success) {
        toast.success(res.message);
        clearGuestId();
        removeLocalStorageKey("guestUserId");
        setOpen(false);
        if (pathName === "/checkout") {
          if (typeof window !== "undefined") window.location.reload();
        } else {
          if (width > 992) {
            router.push("/dashboard");
          } else {
            if (typeof window !== "undefined") window.location.reload();
          }
        }
      } else {
        toast.error(res.message);
      }
    }
  };

  const handleError = () => {
    console.error("Login Failed");
  };

  // get business settings
  const getSettings = useLoadBusinessSettings({
    key: "google_login",
    arrayIndex: 0,
  });

  // login with google
  const login = useGoogleLogin({
    onSuccess: (codeResponse) => handleSuccess(codeResponse),
    onError: handleError,
  });

  // handle next button
  const handleNext = async () => {
    const contact = form.getValues("contact");
    const isValid = await form.trigger("contact");
    if (isPhoneLogin) {
      if (!phoneRegex.test(contact)) {
        form.setError("contact", {
          type: "manual",
          message: "Invalid phone number",
        });
        return;
      }
    } else {
      if (!emailRegex.test(contact)) {
        form.setError("contact", {
          type: "manual",
          message: "Invalid email address",
        });
        return;
      }
    }

    if (!isValid) return;
    try {
      //   const res = await fetch(`${apiBaseUrl}/auth/lookup/${contact}`);
      //   const data: LookUpRes = await res.json();

      //   if (!res.ok || !data.result) {
      //     // toast.error(data.message || "Something went wrong.");
      //     form.setError("contact", {
      //       type: "manual",
      //       message: data.message || "Error",
      //     });
      //     return;
      //   }
      // Success
      setContact(contact);
      setIsChanging(true);
      setIsNext(true);
    } catch (error) {
      toast.error("Network error. Please try again.");
    }
  };

  // handle change
  const handleEditChange = () => {
    setIsChanging(false);
    setIsNext(false);
    setTimeout(() => {
      form.setFocus("contact");
    }, 100);
  };

  // handle login with otp
  function loginWithOtpHandler(values: z.infer<typeof loginWithOtpSchema>) {
    startTransition(async () => {
      const res = await loginWithOtp({
        ...values,
      });
      if (res.data) {
        if (res.data.result === false) {
          toast.error(res.data.message);
        } else {
          toast.success(res.data.message || "OTP sent successfully");
          // set user id into global state
          setUserId(res.data.user_id);
          router.push("/auth/verify-otp");
          //   setIsVerifyOTP(true);
          //   setIsAuth(false);
        }
      }
    });
  }

  return (
    <div className="flex items-center justify-center">
      <div className="flex flex-col">
        <Image
          src="/images/login-banner-image.png"
          alt="login banner"
          width={500}
          height={200}
          className="h-auto max-w-full"
        />
        <div className="flex-1 px-4 py-7 lg:px-8">
          <h3 className="text-site-gray-700 mb-3 text-2xl font-medium md:text-[32px]">
            Login to Glowaro
          </h3>
          <>
            <div className="just login-with-google flex w-full items-center justify-center gap-3">
              {/* <GoogleLogin  /> */}
              {isPhoneLogin ? (
                <button
                  title="Login with Email"
                  className="border-site-gray-200 flex h-[36px] w-[36px] cursor-pointer items-center justify-center rounded-full border-1 p-1.5"
                  onClick={() => {
                    setIsPhoneLogin(false);
                    form.reset();
                  }}
                  disabled={isChanging}
                >
                  <Mail size={20} />
                </button>
              ) : (
                <button
                  title="Login with Phone"
                  className="border-site-gray-200 flex h-[36px] w-[36px] cursor-pointer items-center justify-center rounded-full border-1 p-1.5"
                  onClick={() => {
                    setIsPhoneLogin(true);
                    form.reset();
                  }}
                  disabled={isChanging}
                >
                  <Phone size={20} />
                </button>
              )}
              {/* login with otp */}

              {/* <div
                    title="Login With OTP"
                    className=" flex cursor-pointer justify-end border p-1"
                    onClick={() => {
                        setIsAuth(false);
                        setIsLoginWithOTP(true);
                    }}
                    >
                    <span className="text-sm font-medium text-[#007AFF]">
                        Login with otp
                    </span>
                    </div> */}
            </div>
          </>

          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)}>
              <div className="space-y-3">
                {/* Email  & phone field */}
                <div className="relative">
                  {isPhoneLogin ? (
                    <FormField
                      control={form.control}
                      name="contact"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel className="text-site-gray-500 text-xs font-normal">
                            Phone Number
                          </FormLabel>
                          <FormControl>
                            <Input
                              placeholder="01XXXXXXXXX"
                              type="tel"
                              inputMode="numeric"
                              pattern="[0-9]*"
                              {...field}
                              readOnly={isChanging}
                              className="site-input-field !py-1"
                              defaultValue={contact}
                            />
                          </FormControl>
                          <FormMessage className="-translate-y-1 text-[13px]" />
                        </FormItem>
                      )}
                    />
                  ) : (
                    <FormField
                      control={form.control}
                      name="contact"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel className="text-site-gray-500 text-xs font-normal">
                            E-mail
                          </FormLabel>
                          <FormControl>
                            <Input
                              type="text"
                              placeholder="mail@example.com"
                              {...field}
                              className="site-input-field"
                              readOnly={isChanging}
                              defaultValue={contact}
                            />
                          </FormControl>
                          <FormMessage className="-translate-y-1 text-[13px]" />
                        </FormItem>
                      )}
                    />
                  )}
                  {isChanging && (
                    <button
                      type="button"
                      onClick={() => handleEditChange()}
                      className="absolute top-[48px] right-2 -translate-y-1/2 transform cursor-pointer text-sm font-medium text-[#007AFF]"
                    >
                      Change
                    </button>
                  )}
                </div>

                {/* Password field */}
                <AnimatePresence mode="wait">
                  {isNext && (
                    <motion.div
                      key="next-block"
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: 20 }}
                      transition={{ duration: 0.25, ease: "easeOut" }}
                    >
                      <div>
                        <FormField
                          control={form.control}
                          name="password"
                          render={({ field, fieldState }) => (
                            <FormItem>
                              <FormLabel className="text-site-gray-500 text-xs font-normal">
                                Password
                              </FormLabel>
                              <FormControl>
                                <div className="relative">
                                  <Input
                                    type={showPassword ? "text" : "password"}
                                    placeholder="••••••••"
                                    {...field}
                                    className="site-input-field"
                                  />
                                  <button
                                    type="button"
                                    onClick={() =>
                                      setShowPassword((prev) => !prev)
                                    }
                                    className="absolute top-3 right-3 cursor-pointer text-gray-500 hover:text-gray-700"
                                    tabIndex={-1}
                                  >
                                    {showPassword ? (
                                      <EyeOff className="h-5 w-5" />
                                    ) : (
                                      <Eye className="h-5 w-5" />
                                    )}
                                  </button>
                                </div>
                              </FormControl>

                              <FormMessage className="-translate-y-1 text-[13px]" />
                            </FormItem>
                          )}
                        />
                        <div className="flex items-center justify-end">
                          <Link
                            className="mt-2 cursor-pointer"
                            href="/auth/forgot-password"
                          >
                            <span className="text-sm font-medium text-[#007AFF]">
                              Forgot your password?
                            </span>
                          </Link>
                        </div>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>

              {/* Submit button */}
              <div className="mt-8 md:mt-10">
                {isNext === false && (
                  <PrimaryButton
                    type="button"
                    disabled={isPending}
                    onClick={() => handleNext()}
                    className=""
                  >
                    {isPending ? "Logging in..." : "Next"}
                  </PrimaryButton>
                )}
                {isNext && (
                  <PrimaryButton
                    type="submit"
                    disabled={isPending}
                    className=""
                  >
                    {isPending ? "Logging in..." : "Log in"}
                  </PrimaryButton>
                )}

                <p className="text-site-gray-900 mt-2 text-center text-sm">
                  New to Glowaro?{" "}
                  <Link
                    href="/auth/registration"
                    className="cursor-pointer font-semibold text-[#007AFF]"
                  >
                    Create an Account
                  </Link>
                </p>
              </div>

              {/* Other login methods */}
              <div className="mt-3">
                <div className="py-3">
                  <div className="text-site-gray-300 flex items-center gap-3 text-sm">
                    <hr className="border-site-gray-100 flex-1" />
                    or
                    <hr className="border-site-gray-100 flex-1" />
                  </div>
                </div>
                <SecondaryButton
                  className="mb-3 cursor-pointer"
                  type="button"
                  onClick={() => {
                    loginWithOtpHandler({ contact });
                  }}
                >
                  <span className="flex items-center gap-2">
                    <OtpIcon /> Login With OTP
                  </span>
                </SecondaryButton>
                {getSettings?.value === "1" && (
                  <SecondaryButton
                    title="Login with Google"
                    className=""
                    onClick={() => login()}
                  >
                    <FcGoogle size={24} /> Login With Google
                  </SecondaryButton>
                )}
              </div>
            </form>
          </Form>
        </div>
      </div>
    </div>
  );
};

export default LoginForm;
