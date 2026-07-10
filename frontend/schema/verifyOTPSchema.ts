import z from "zod";

export const verifyOTPSchema = z.object({
  otp: z.string().min(6, {
    message: "OTP must be 6 characters.",
  }),
});