"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";

import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { resetPasswordSchema } from "@/schema/resetPasswordSchema";
import { useFormControl } from "@/store/useFormControl";
import { Eye, EyeOff } from "lucide-react";

const ResetPasswordForm = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  // const [isPending, startTransition] = useTransition();
  // const { setOpen } = useAuthModalStore();
  // const router = useRouter();

  const { setIsLogin, setIsForgot, setIsAuth, setIsVerifyOTP, setIsReset } =
    useFormControl();

  const form = useForm<z.infer<typeof resetPasswordSchema>>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      password: "",
      confirmPassword: "",
    },
  });

  function onSubmit(values: z.infer<typeof resetPasswordSchema>) {
    // handle form state
    setIsAuth(true);
    setIsVerifyOTP(false);
    setIsReset(false);
    setIsForgot(false);

    form.reset();
  }

  return (
    <div className="lg:min-h-[719px] px-8 md:px-20 lg:px-[127px] py-10 md:py-16 lg:py-[95px] flex items-center justify-center">
      <div className="flex-1">
        <h3 className="text-3xl md:text-[40px] text-site-gray-700 mb-8">
          Reset password?
        </h3>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)}>
            <div className="space-y-3">
              {/* Password field */}
              <FormField
                control={form.control}
                name="password"
                render={({ field, fieldState }) => (
                  <FormItem>
                    <FormLabel className="text-sm text-site-gray-500 font-normal">
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
                          onClick={() => setShowPassword((prev) => !prev)}
                          className="absolute right-3 top-3.5 text-gray-500 hover:text-gray-700 cursor-pointer"
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
                    <FormMessage className="text-[13px] -translate-y-1" />
                  </FormItem>
                )}
              />
              {/* Conform Password field */}
              <FormField
                control={form.control}
                name="confirmPassword"
                render={({ field, fieldState }) => (
                  <FormItem>
                    <FormLabel className="text-sm text-site-gray-500 font-normal">
                      Confirm Password
                    </FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Input
                          type={showConfirmPassword ? "text" : "password"}
                          placeholder="••••••••"
                          {...field}
                          className="site-input-field"
                        />
                        <button
                          type="button"
                          onClick={() =>
                            setShowConfirmPassword((prev) => !prev)
                          }
                          className="absolute right-3 top-3.5 text-gray-500 hover:text-gray-700 cursor-pointer"
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
                    <FormMessage className="text-[13px] -translate-y-1" />
                  </FormItem>
                )}
              />
            </div>

            {/* Submit button */}
            <div className="mt-10 md:mt-16">
              <button
                type="submit"
                className="flex-1 bg-site-gray-700 text-white py-3 px-6 rounded-lg font-medium hover:bg-site-gray-900 transition-colors cursor-pointer w-full text-base"
              >
                Reset password
              </button>

              <p className="text-center text-sm mt-2 text-site-gray-900">
                Go back to{" "}
                <span
                  className="text-[#007AFF] font-semibold cursor-pointer"
                  onClick={() => {
                    setIsAuth(true);
                    setIsReset(false);
                    setIsForgot(false);
                    setIsVerifyOTP(false);
                  }}
                >
                  Log in
                </span>
              </p>
            </div>
          </form>
        </Form>
      </div>
    </div>
  );
};

export default ResetPasswordForm;
