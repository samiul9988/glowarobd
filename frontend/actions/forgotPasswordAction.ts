"use server";

import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { forgotPasswordSchema } from "@/schema/forgotPasswordSchema";
import z from "zod";

interface ApiResponse {
  result: boolean;
  message: string;
  verification_code: number;
}

export async function forgotPasswordAction(
  fromData: z.infer<typeof forgotPasswordSchema>
) {
  try {
    const result = forgotPasswordSchema.safeParse(fromData);

    if (!result.success) {
      return {
        success: false,
        message: "Something went wrong",
        data: null,
      };
    }

    const res: ApiResponse | null = await fetcher(
      "/auth/password/forget_request",
      {
        method: "POST",
        body: JSON.stringify({
          email_or_phone: result.data.contact,
        }),
        baseUrl: apiBaseUrl,
      }
    );

    if (!res) {
      return {
        success: false,
        message: "Something went wrong",
        data: null,
      };
    }

    return {
      success: true,
      message: res.message,
      data: res,
    };
  } catch (error) {
    return {
      success: false,
      message: "Something went wrong",
      data: null,
    };
  }
}
