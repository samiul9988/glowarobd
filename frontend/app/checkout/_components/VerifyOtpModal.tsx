"use client";

import * as React from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { InputOTP, InputOTPGroup, InputOTPSlot } from "@/components/ui/input-otp";
import { cn } from "@/lib/utils";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { verifyOTPSchema } from "@/schema/verifyOTPSchema";
import z from "zod";
import toast from "react-hot-toast";
import { useTransition } from "react";
import { checkoutOtpVerificationAction } from "@/actions/checkoutOtpVerificationAction";
import { useGuestUserId } from "@/store/useGuestStore";
import ResendOtpTimer from "@/components/modals/_components/ResendOtpTimer";
import { checkoutOtpResendAction } from "@/actions/checkoutOtpResendAction";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";

interface VerifyOtpModalProps {
  open: boolean;
  onClose: () => void;
  phone: string;
}

const VerifyOtpModal: React.FC<VerifyOtpModalProps> = ({ open, onClose, phone }) => {
  const [isPending, startTransition] = useTransition();
  const { clearGuestId } = useGuestUserId();
  const form = useForm<z.infer<typeof verifyOTPSchema>>({
    resolver: zodResolver(verifyOTPSchema),
    defaultValues: { otp: "" },
  });

  const onSubmit = async (data: z.infer<typeof verifyOTPSchema>) => {
    if (!phone) {
      toast.error("Please add valid phone number");
      return;
    }

    startTransition(async () => {
      try {
        const res = await checkoutOtpVerificationAction({ phone, verification_code:  data.otp });
        if (res?.success) {
            clearGuestId();
            removeLocalStorageKey("guestUserId");
            onClose();
            form.reset();
        } 
      } catch (error: any) {
        toast.error(error?.message || "Something went wrong");
      }
    });
  };

//   HANDLE resend
 const handleResendOTP = () => {
    startTransition(async () => {
      const res = await checkoutOtpResendAction({ phone: phone });
      if (res?.data) {
        if (!res.data.result) {
          toast.error(res.data.message);
        } else {
          toast.success(res.data.message);
        }
      }
    });
  };


  return (
    <Dialog open={open} onOpenChange={() => onClose()}>
      <DialogContent className="sm:max-w-md rounded-2xl">
        <DialogHeader>
          <DialogTitle className="text-2xl text-center">
            Verify your OTP
          </DialogTitle>
          <DialogDescription className="text-center text-gray-500">
            Enter the 6-digit code sent to your phone number{" "}
            {phone && <span className="font-semibold">{phone}</span>}
          </DialogDescription>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6 mt-6">
            <FormField
              control={form.control}
              name="otp"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Enter OTP</FormLabel>
                  <FormControl>
                    <InputOTP maxLength={6} {...field}>
                      <InputOTPGroup className="flex gap-3 justify-center">
                        {Array.from({ length: 6 }).map((_, i) => (
                          <InputOTPSlot
                            key={i}
                            index={i}
                            className="!ring-site-primary/40 data-[active=true]:border-site-primary"
                          />
                        ))}
                      </InputOTPGroup>
                    </InputOTP>
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <DialogFooter>
              <button
                disabled={isPending}
                type="submit"
                className={cn(
                  "w-full bg-site-gray-700 text-white py-3 px-6 rounded-lg font-medium hover:bg-site-gray-900 transition-colors",
                  isPending && "cursor-not-allowed opacity-70"
                )}
              >
                {isPending ? "Verifying..." : "Verify"}
              </button>
            </DialogFooter>
          </form>
        </Form>
        <div className="flex items-center justify-center">
        <ResendOtpTimer duration={40} onResend={handleResendOTP} />
        </div>
        
      </DialogContent>
    </Dialog>
  );
};

export default VerifyOtpModal;
