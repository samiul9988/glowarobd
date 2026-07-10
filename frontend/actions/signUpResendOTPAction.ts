"use server";

import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";

interface ApiResponse {
  result: boolean;
  message: string;
};

export async function signUpResendOTPAction({ user_id }: { user_id: string }) {

  try {
    const res: ApiResponse | null = await fetcher("/auth/resend_code", {
      method: "POST",
      body: JSON.stringify({
        user_id: user_id,
      }),
      baseUrl: apiBaseUrl,
    });

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
