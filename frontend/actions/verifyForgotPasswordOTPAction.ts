"use server";

import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { cookies } from "next/headers";

interface ApiResponse {
  result: boolean;
  message: string;
  access_token: string;
  user: {
    id: number;
    email: string;
    phone: string;
    type: string;
    gender: string;
  };
}

export async function verifyForgotPasswordOTPAction(formData: {
  verification_code: string;
}) {
  try {
    if (!formData.verification_code) {
      return {
        success: false,
        message: "Invalid user data",
        data: null,
      };
    }

    const res: ApiResponse | null = await fetcher(
      "/auth/password/confirm_reset",
      {
        method: "POST",
        body: JSON.stringify({
          verification_code: formData.verification_code,
        }),
        baseUrl: apiBaseUrl,
      }
    );


    if (!res) {
      return {
        data: null,
        message: "Something went wrong",
        success: false,
      };
    }

    // user login if valid user
    if (res.user) {
      (await cookies()).set("access_token", res.access_token || "", {
        maxAge: 60 * 60 * 24 * 7, // 7 days
      });

      (await cookies()).set("user_data", JSON.stringify(res.user), {
        maxAge: 60 * 60 * 24 * 7, // 7 days
      });
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
