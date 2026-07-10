import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";

interface ApiResponse {
  result: boolean;
  message: string;
}

export async function verifySignUpOTPAction(formData: {
  user_id: string;
  verification_code: string;
}) {
  try {
    if (!formData.user_id || !formData.verification_code) {
      return {
        success: false,
        message: "Invalid user data",
        data: null,
      };
    }

    const res: ApiResponse | null = await fetcher("/auth/confirm_code", {
      method: "POST",
      body: JSON.stringify({
        user_id: formData.user_id,
        verification_code: formData.verification_code,
      }),
      baseUrl: apiBaseUrl,
    });

    if (!res) {
      return {
        data: null,
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
      message: "Something went wrong",
      data: null,
    };
  }
}
