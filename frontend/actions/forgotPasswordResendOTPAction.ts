"use server";

import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";

interface ApiResponse {
  result: boolean;
  message: string;
}

export async function forgotPasswordResendOTPAction({
  email_or_phone,
}: {
  email_or_phone: string;
}) {
  try {
    const res: ApiResponse | null = await fetcher(
      "/auth/password/resend_code",
      {
        method: "POST",
        body: JSON.stringify({
          email_or_code: email_or_phone,
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
    };
  }
}
