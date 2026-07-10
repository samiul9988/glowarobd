"use client";

import { verifySignUpOTPAction } from "@/actions/verifySignUpOTPAction";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/components/ui/input-otp";
import { verifyOTPSchema } from "@/schema/verifyOTPSchema";
import { useFormControl } from "@/store/useFormControl";
import { zodResolver } from "@hookform/resolvers/zod";
import { useTransition } from "react";
import { useForm } from "react-hook-form";
import toast from "react-hot-toast";
import z from "zod";
import ResendOtpTimer from "./ResendOtpTimer";
import { signUpResendOTPAction } from "@/actions/signUpResendOTPAction";
import { cn } from "@/lib/utils";
import { verifyForgotPasswordOTPAction } from "@/actions/verifyForgotPasswordOTPAction";
import { forgotPasswordResendOTPAction } from "@/actions/forgotPasswordResendOTPAction";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useRouter } from "next/navigation";
import PrimaryButton from "@/components/buttons/PrimaryButton";
import Link from "next/link";

const VerifyOTPForm = () => {
  const [isPending, startTransition] = useTransition();
  const [isOtpPending, startOtpTransition] = useTransition();
  const { setOpen } = useAuthModalStore();
  const router = useRouter();

  const {
    setIsLogin,
    setIsForgot,
    setIsAuth,
    setIsVerifyOTP,
    setIsReset,
    isSignupOTP,
    userId,
    contact,
  } = useFormControl();

  const form = useForm<z.infer<typeof verifyOTPSchema>>({
    resolver: zodResolver(verifyOTPSchema),
    defaultValues: {
      otp: "",
    },
  });

  function onSubmit(data: z.infer<typeof verifyOTPSchema>) {
    // Check if isSignupOTP request
    if (isSignupOTP) {
      startTransition(async () => {
        const res = await verifySignUpOTPAction({
          user_id: userId,
          verification_code: data.otp,
        });

        if (res.data) {
          if (res.data.result === false) {
            toast.error(res.data.message);
          } else {
            toast.success(res.data.message);

            // handle form state
            setIsAuth(true);
            setIsLogin(true);
            setIsVerifyOTP(false);
            setIsForgot(false);
            setIsReset(false);
            form.reset();
          }
        }
      });
    } else {
      startTransition(async () => {
        const res = await verifyForgotPasswordOTPAction({
          verification_code: data.otp,
        });

        if (res.data) {
          if (!res.data.result) {
            toast.error(res.data.message);
          } else {
            toast.success(res.data.message);
            // redirect to profile
            router.push("/profile");
            router.refresh();

            // handle form state
            setOpen(false);
            setIsAuth(true);
            setIsLogin(true);
            setIsReset(false);
            setIsVerifyOTP(false);
            setIsForgot(false);
            form.reset();
          }
        }
      });
    }
  }

  function handleResendOTP() {
    // Check if isSignupOTP request
    if (isSignupOTP) {
      startOtpTransition(async () => {
        const res = await signUpResendOTPAction({
          user_id: userId,
        });

        if (res.data) {
          if (!res.data.result) {
            toast.error(res.data.message);
          } else {
            toast.success(res.data.message);
          }
        }
      });
    } else {
      startOtpTransition(async () => {
        const res = await forgotPasswordResendOTPAction({
          // user email or phone from store
          email_or_phone: contact,
        });
        if (res.data) {
          if (!res.data.result) {
            toast.error(res.data.message);
          } else {
            toast.success(res.data.message);
          }
        }
      });
    }
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const isEmail = contact && emailRegex.test(contact);
  const boxSize =
    "!w-[45px] !h-[45px] !ring-site-primary/40 data-[active=true]:border-site-primary ";
  return (
    <div className="flex items-center justify-center px-4 py-7 lg:px-8">
      <div className="flex-1">
        <div className="space-y-2 max-md:text-center md:text-[40px]">
          <h3 className="text-site-gray-700 mb-3 text-2xl font-medium md:text-[32px]">
            Verify your OTP
          </h3>
          <p className="text-site-gray-500 mb-6 text-sm md:text-base">
            Enter the 6-digit code sent to your
            {isEmail ? " email " : " phone number "}
            <span className="font-bold">{contact}</span>
          </p>
        </div>

        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(onSubmit)}
            className="bg-red space-y-6"
          >
            <FormField
              control={form.control}
              name="otp"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="mb-1" hidden>
                    Enter OTP
                  </FormLabel>
                  <FormControl>
                    <InputOTP maxLength={6} {...field}>
                      <InputOTPGroup className="flex w-full gap-2 max-md:justify-center md:gap-2 lg:justify-between">
                        <InputOTPSlot index={0} className={boxSize} />
                        <InputOTPSlot index={1} className={boxSize} />
                        <InputOTPSlot index={2} className={boxSize} />
                        <InputOTPSlot index={3} className={boxSize} />
                        <InputOTPSlot index={4} className={boxSize} />
                        <InputOTPSlot index={5} className={boxSize} />
                      </InputOTPGroup>
                    </InputOTP>
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <PrimaryButton
              disabled={isPending}
              type="submit"
              className={cn("!mt-5", isPending && "cursor-not-allowed")}
            >
              {isPending ? "Verifying..." : "Verify"}
            </PrimaryButton>
          </form>
        </Form>

        <ResendOtpTimer duration={120} onResend={handleResendOTP} />

        <p className="text-site-gray-900 mt-2 text-center text-sm">
          Go back to{" "}
          <Link
            className="cursor-pointer font-semibold text-[#007AFF]"
            href="/auth/login"
          >
            Log in
          </Link>
        </p>
      </div>
    </div>
  );
};

export default VerifyOTPForm;
