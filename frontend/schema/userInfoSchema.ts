import z from "zod";

export const userInfoSchema = z
  .object({
    name: z
      .string()
      .min(1, { message: "Name is required" })
      .max(50, { message: "Name must be less than 50 characters" })
      .trim(),
    phone: z.string().optional(),
    email: z.string().optional(),
    gender: z.string().min(1, { message: "Please select a gender." }),
    dob: z
      .date({ message: "Please select your date of birth" })
      .refine((date) => !!date, {
        message: "Please select your date of birth",
      })
      .refine(
        (date) => {
          const age = new Date().getFullYear() - date.getFullYear();
          return age >= 13;
        },
        { message: "You must be at least 13 years old" }
      ),
    password: z
      .string()
      .optional()
      .refine((val) => !val || val.length >= 6, {
        message: "Password must be at least 6 characters",
      }),

    confirmPassword: z.string().optional(),
  })
  .superRefine((data, ctx) => {
    if (data.password) {
      if (!data.confirmPassword || data.confirmPassword.length === 0) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "Please confirm your password",
          path: ["confirmPassword"],
        });
        return;
      }

      if (data.password !== data.confirmPassword) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: "Passwords do not match",
          path: ["confirmPassword"],
        });
      }
    }
  });
