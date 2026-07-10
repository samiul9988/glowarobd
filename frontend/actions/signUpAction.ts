import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { signupSchema } from "@/schema/signupSchema";
import z from "zod";

interface ApiResponse {
  message: string;
  result: boolean;
  user_id: string;
}

export async function SignupAction(fromData: z.infer<typeof signupSchema>) {
  const result = signupSchema.safeParse(fromData);

  try {
    if (!result.success) {
      return {
        success: false,
        message: "Invalid user data",
        data: null,
      };
    }

    const res: ApiResponse | null = await fetcher("/auth/signup", {
      method: "POST",
      body: JSON.stringify({
        name: result.data.name,
        email_or_phone: result.data.contact,
        password: result.data.password,
        password_confirmation: result.data.password,
        register_by: "web",
        temp_user_id: result.data.temp_user_id,
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
