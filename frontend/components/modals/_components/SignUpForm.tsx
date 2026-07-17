"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Eye, EyeOff, Mail, Phone } from "lucide-react";
import { useState, useTransition } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
// import { LoginAction } from "@/actions/loginAction";
// import { GoogleIcon } from "@/components/icons/icon-library";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { signupSchema } from "@/schema/signupSchema";
import { useFormControl } from "@/store/useFormControl";
import toast from "react-hot-toast";
import { SignupAction } from "@/actions/signUpAction";
import { useGuestUserId } from "@/store/useGuestStore";
import { GoogleLoginResponse } from "./LoginForm";
import { GoogleAction } from "@/actions/GoogleAuth";
import { apiBaseUrl } from "@/config/apiConfig";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";
import { api } from "@/lib/axios";
import { useQuery } from "@tanstack/react-query";
import { usePathname, useRouter } from "next/navigation";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useWindowWidth } from "@/hooks/useWindowWidth";
import { GoogleLogin, useGoogleLogin } from "@react-oauth/google";
import * as ScrollArea from "@radix-ui/react-scroll-area";
import { useLoadBusinessSettings } from "@/hooks/useLoadBusinessSettings";
import { FcGoogle } from "react-icons/fc";
import Image from "next/image";
import SecondaryButton from "@/components/buttons/SecondaryButton";
import Link from "next/link";
import PrimaryButton from "@/components/buttons/PrimaryButton";

const SignUpForm = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isPending, startTransition] = useTransition();
  const { guestId, clearGuestId } = useGuestUserId();
  const router = useRouter();
  const { setOpen } = useAuthModalStore();
  const width = useWindowWidth();
  const pathName = usePathname();
  const [isPhoneLogin, setIsPhoneLogin] = useState(true);

  const {
    isLogin,
    setIsLogin,
    setIsAuth,
    setIsVerifyOTP,
    setIsSignupOTP,
    setUserId,
  } = useFormControl();

  const form = useForm<z.infer<typeof signupSchema>>({
    resolver: zodResolver(signupSchema),
    defaultValues: {
      name: "",
      contact: "",
      password: "",
      confirmPassword: "",
    },
  });

  function onSubmit(values: z.infer<typeof signupSchema>) {
    startTransition(async () => {
      const res = await SignupAction({
        ...values,
        temp_user_id: guestId as string,
      });

      if (res.data) {
        if (res.data.result === false) {
          toast.error(res.data.message);
        } else {
          toast.success(res.data.message);

          // set user id into global state
          setUserId(res.data.user_id);

          form.reset();
          setIsAuth(false);
          //   setIsVerifyOTP(true);
          router.push("/auth/verify-otp");
          setIsSignupOTP(true);
        }
      }
    });
  }

  // Google Login Handler
  const handleSuccess = async (credentialResponse: any) => {
    const token = credentialResponse.access_token;

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
          router.push(width > 991 ? "/dashboard" : "/my-panel");
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
    flow: "implicit",
    onError: handleError,
  });

  // const { data: isGoogleLoginActive } = useQuery({
  //   queryKey: ["google_login"],
  //   queryFn: async () => {
  //     const { data } = await api.get("/business-settings");
  //     return data.data.filter(
  //       (item: BusinessDataType) => item.type === "google_login",
  //     )[0] as BusinessDataType;
  //   },
  // });

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
        <div className="flex-1 px-3 py-7 lg:px-6">
          <h3 className="text-site-gray-700 mb-3 text-2xl font-medium md:text-[32px]">
            Create Your Account
          </h3>

          {/* Scrollable Form Body */}
          <Form {...form}>
            <div className="relative overflow-clip" data-lenis-ignore>
              <div
                className="flex-1 space-y-6"
                onWheel={(e) => e.stopPropagation()}
              >
                <form onSubmit={form.handleSubmit(onSubmit)}>
                  {/* other login */}
                  <>
                    <div className="just login-with-google flex w-full items-center justify-center gap-3 pt-1">
                      {/* <GoogleLogin  /> */}
                      {isPhoneLogin ? (
                        <button
                          type="button"
                          className="border-site-gray-200 flex h-[36px] w-[36px] cursor-pointer items-center justify-center rounded-full border-1 p-1.5"
                          onClick={() => setIsPhoneLogin(false)}
                        >
                          <Mail size={20} />
                        </button>
                      ) : (
                        <button
                          type="button"
                          className="border-site-gray-200 flex h-[36px] w-[36px] cursor-pointer items-center justify-center rounded-full border-1 p-1.5"
                          onClick={() => setIsPhoneLogin(true)}
                        >
                          <Phone size={20} />
                        </button>
                      )}
                    </div>
                  </>

                  <div className="space-y-3">
                    {/* Name field */}
                    <FormField
                      control={form.control}
                      name="name"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel className="text-site-gray-500 text-xs font-normal">
                            Name
                          </FormLabel>
                          <FormControl>
                            <Input
                              type="text"
                              placeholder="Jhon Doe"
                              {...field}
                              className="site-input-field"
                            />
                          </FormControl>
                          <FormMessage className="-translate-y-1 text-[13px]" />
                        </FormItem>
                      )}
                    />
                    {/* Email or Phone field */}
                    {/* <FormField
                    control={form.control}
                    name="contact"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-site-gray-500 text-xs font-normal">
                          Phone / E-mail
                        </FormLabel>
                        <FormControl>
                          <Input
                            type="text"
                            placeholder="017XXXXXXXX"
                            {...field}
                            className="site-input-field"
                          />
                        </FormControl>
                        <FormMessage className="-translate-y-1 text-[13px]" />
                      </FormItem>
                    )}
                  /> */}
                    {isPhoneLogin ? (
                      <FormField
                        control={form.control}
                        name="contact"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel className="text-site-gray-500 text-xs font-normal">
                              Phone
                            </FormLabel>
                            <FormControl>
                              <Input
                                placeholder="01XXXXXXXXX"
                                type="tel"
                                inputMode="numeric"
                                pattern="[0-9]*"
                                {...field}
                                className="site-input-field !py-1"
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
                              />
                            </FormControl>
                            <FormMessage className="-translate-y-1 text-[13px]" />
                          </FormItem>
                        )}
                      />
                    )}

                    {/* Password field */}
                    <div className="flex flex-col items-start gap-4 md:flex-row">
                      <FormField
                        control={form.control}
                        name="password"
                        render={({ field, fieldState }) => (
                          <FormItem className="w-full">
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

                            {/* Error message */}
                            <FormMessage className="-translate-y-1 text-[13px]" />
                          </FormItem>
                        )}
                      />
                      <FormField
                        control={form.control}
                        name="confirmPassword"
                        render={({ field, fieldState }) => (
                          <FormItem className="w-full">
                            <FormLabel className="text-site-gray-500 text-xs font-normal">
                              Confirm Password
                            </FormLabel>
                            <FormControl>
                              <div className="relative">
                                <Input
                                  type={
                                    showConfirmPassword ? "text" : "password"
                                  }
                                  placeholder="••••••••"
                                  {...field}
                                  className="site-input-field"
                                />
                                <button
                                  type="button"
                                  onClick={() =>
                                    setShowConfirmPassword((prev) => !prev)
                                  }
                                  className="absolute top-3 right-3 cursor-pointer text-gray-500 hover:text-gray-700"
                                  tabIndex={-1}
                                >
                                  {showConfirmPassword ? (
                                    <EyeOff className="h-5 w-5" />
                                  ) : (
                                    <Eye className="h-5 w-5" />
                                  )}
                                </button>
                              </div>
                            </FormControl>

                            {/* Error message */}
                            <FormMessage className="-translate-y-1 text-[13px]" />
                          </FormItem>
                        )}
                      />
                    </div>
                  </div>

                  {/* Submit button */}
                  <div className="mt-10 md:mt-16">
                    <PrimaryButton
                      type="submit"
                      disabled={isPending}
                      className=""
                    >
                      {isPending ? "Creating account..." : "Create Account"}
                    </PrimaryButton>

                    <p className="text-site-gray-900 mt-2 text-center text-sm">
                      Have an account?{" "}
                      <Link
                        href="/auth/login"
                        className="cursor-pointer font-semibold text-[#007AFF]"
                        onClick={() => setIsLogin(true)}
                      >
                        Log in
                      </Link>
                    </p>
                  </div>

                  {/* Other login methods */}
                  <div className="mt-2 py-2">
                    <div className="text-site-gray-300 flex items-center gap-3 text-sm">
                      <hr className="border-site-gray-100 flex-1" />
                      or
                      <hr className="border-site-gray-100 flex-1" />
                    </div>
                  </div>
                  {getSettings?.value === "1" && (
                    // <button
                    //   type="button"
                    //   className="border-site-gray-200 flex h-[36px] w-[36px] cursor-pointer items-center justify-center rounded-full border-1 p-1.5"
                    //   onClick={() => login()}
                    // >
                    //   <FcGoogle size={20} />
                    // </button>
                    <SecondaryButton
                      title="Login with Google"
                      className=""
                      onClick={() => login()}
                    >
                      <FcGoogle size={24} /> Login With Google
                    </SecondaryButton>
                  )}
                </form>
              </div>
            </div>
          </Form>
        </div>
      </div>
    </div>
  );
};

export default SignUpForm;
