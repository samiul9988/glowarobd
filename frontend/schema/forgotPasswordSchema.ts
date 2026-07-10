import z from "zod";

export const forgotPasswordSchema = z.object({
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
});
