'use server";';

import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { loginWithOtpSchema } from "@/schema/loginWithOtpSchema";
import z from "zod";

interface ApiResponse {
  message: string;
  result: boolean;
  user_id: string;
}

export async function loginWithOtp(
  fromData: z.infer<typeof loginWithOtpSchema>,
) {
  const result = loginWithOtpSchema.safeParse(fromData);

  try {
    if (!result.success) {
      return {
        success: false,
        message: "Invalid user data",
        data: null,
      };
    }

    const res: ApiResponse | null = await fetcher("/auth/login-with-otp", {
      method: "POST",
      body: JSON.stringify({
        email_or_phone: result.data.contact,
      }),
      baseUrl: apiBaseUrl,
    });
    if (!res?.result) {
      return {
        data: res,
        message: "Something went wrong",
        success: false,
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
      message: "Something went wrong ",
      data: null,
    };
  }
}
