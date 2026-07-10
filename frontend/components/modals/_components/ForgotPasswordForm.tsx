"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useTransition } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";

import { forgotPasswordAction } from "@/actions/forgotPasswordAction";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { forgotPasswordSchema } from "@/schema/forgotPasswordSchema";
import { useFormControl } from "@/store/useFormControl";
import toast from "react-hot-toast";
import Link from "next/link";
import PrimaryButton from "@/components/buttons/PrimaryButton";

const ForgotPasswordForm = () => {
  const [isPending, startTransition] = useTransition();

  const {
    setIsForgot,
    setIsAuth,
    setIsVerifyOTP,
    setIsReset,
    setIsSignupOTP,
    setContact,
    contact,
  } = useFormControl();

  const form = useForm<z.infer<typeof forgotPasswordSchema>>({
    resolver: zodResolver(forgotPasswordSchema),
    defaultValues: {
      contact: "",
    },
  });

  function onSubmit(values: z.infer<typeof forgotPasswordSchema>) {
    startTransition(async () => {
      const res = await forgotPasswordAction(values);

      if (res.data) {
        if (res.data.result === false) {
          toast.error(res.data.message);
        } else {
          toast.success(res.data.message);

          // set user email or phone into global state
          setContact(values.contact);

          // handle form state
          setIsVerifyOTP(true);
          setIsForgot(false);
          setIsAuth(false);
          setIsReset(false);
          setIsSignupOTP(false);
          form.reset();
        }
      }
    });
  }

  return (
    <div className="flex items-center justify-center">
      <div className="flex-1 px-4 py-7 lg:px-8">
        <h3 className="text-site-gray-700 mb-8 text-3xl font-medium md:text-[32px]">
          Forgot password?
        </h3>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)}>
            <div className="space-y-3">
              {/* Email field */}
              <FormField
                control={form.control}
                name="contact"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-site-gray-500 text-sm font-normal">
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
              />
            </div>

            {/* Submit button */}
            <div className="mt-10 md:mt-16">
              <PrimaryButton
                disabled={isPending}
                type="submit"
                // className={cn(
                //   "bg-site-gray-700 hover:bg-site-gray-900 w-full flex-1 cursor-pointer rounded-lg px-6 py-3 text-base font-medium text-white transition-colors disabled:cursor-not-allowed disabled:opacity-80",
                //   isPending && "cursor-not-allowed opacity-80",
                // )}
              >
                {isPending ? "Sending OTP..." : "Send OTP"}
              </PrimaryButton>
            </div>
            <p
              className="text-site-gray-900 mt-2 text-center text-sm"
              onClick={() => {
                setIsAuth(true);
                setIsForgot(false);
                setIsVerifyOTP(false);
                setIsReset(false);
              }}
            >
              Go back to{" "}
              <Link
                href="/auth/login"
                className="cursor-pointer font-semibold text-[#007AFF]"
              >
                Log in
              </Link>
            </p>
          </form>
        </Form>
      </div>
    </div>
  );
};

export default ForgotPasswordForm;
