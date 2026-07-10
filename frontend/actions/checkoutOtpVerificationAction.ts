"use server";

import { cookies } from "next/headers";
import { fetcher } from "@/lib/fetcher";
import { apiBaseUrl } from "@/config/apiConfig";
import toast from "react-hot-toast";

interface ApiResponse {
  message: string;
  result: boolean;
  success: boolean;
  access_token?: string;
  user_id?: number;
  user?: Record<string, any>;
}

interface OtpData {
  phone: string;
  verification_code?: string;
}

export async function checkoutOtpVerificationAction(data: OtpData) {
  if (!data.phone) {
    return { success: false, message: "Phone is required" };
  }

  try {
    const res: ApiResponse | null = await fetcher("/verify-phone", {
      method: "POST",
      body: JSON.stringify({
        phone: data.phone,
        verification_code: data.verification_code || "",
      }),
      baseUrl: apiBaseUrl,
    });


    if (!res || res.result === false) {
      return { success: false, message: res?.message || "Invalid credentials" };
    }

    // Set cookies server-side
   (await cookies()).set("access_token", res.access_token || "", {
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });

    (await cookies()).set("user_data", JSON.stringify(res.user), {
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });

    return { success: true, message: res.message, data: res };
  } catch (error) {
    return { success: false, message: "Something went wrong" };
  }
}
