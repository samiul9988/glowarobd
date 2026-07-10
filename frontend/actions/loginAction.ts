"use server";

import { apiBaseUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { loginSchema } from "@/schema/loginSchema";
import { cookies } from "next/headers";
import z from "zod";

interface ApiResponse {
  message: string;
  result: boolean;
  success: boolean;
  access_token?: string; // token include
  user?: {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    type: string;
  } | null;
  errors?: Record<string, string[]>;
}

export async function LoginAction(fromData: z.infer<typeof loginSchema>) {
  try {
    const result = loginSchema.safeParse(fromData);

    if (!result.success) {
      return {
        success: false,
        message: "Something went wrong",
        data: null,
      };
    }

    const res: ApiResponse | null = await fetcher("/auth/login", {
      method: "POST",
      body: JSON.stringify({
        email: result.data.contact,
        password: result.data.password,
        temp_user_id: result.data.temp_user_id,
      }),
      baseUrl: apiBaseUrl,
    });

    if (!res) {
      return {
        success: false,
        message: "Invalid credentials",
        data: null,
      };
    }

    if (res.result === false) {
      return {
        success: false,
        message: res.message,
        data: res,
      };
    }

    (await cookies()).set("access_token", res.access_token || "", {
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });

    (await cookies()).set("user_data", JSON.stringify(res.user), {
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });

    return {
      success: true,
      message: res.message,
      data: res,
    };
  } catch (error) {
    return {
      success: false,
      message: "Something went wrong",
    };
  }
}
