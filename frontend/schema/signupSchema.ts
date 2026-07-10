import { z } from "zod";

export const signupSchema = z
  .object({
    name: z
      .string()
      .min(1, { message: "Name is required" })
      .max(50, { message: "Name is too long" }),

    contact: z
      .string()
      .trim()
      .min(1, { message: "Email or phone is required" })
      .superRefine((value, ctx) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phoneRegex = /^[0-9]{11,15}$/;

        const isEmail = emailRegex.test(value);
        const isPhone = phoneRegex.test(value);

        if (!isEmail && !isPhone) {
          if (value.includes("@")) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: "Invalid email address",
            });
          } else if (/^\d/.test(value)) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: "Invalid phone number",
            });
          } else {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: "Invalid contact format",
            });
          }
        }
      }),
    password: z
      .string()
      .min(1, { message: "Password is required" })
      .min(6, { message: "Password must be at least 6 characters long" }),

    confirmPassword: z
      .string()
      .min(1, { message: "Confirm password is required" }),
    temp_user_id: z.string().optional(),
  })
  .refine((data) => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  });
