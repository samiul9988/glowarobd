"use server";

import { GoogleLoginResponse } from "@/components/modals/_components/LoginForm";
import { cookies } from "next/headers";

export async function GoogleAction(data: GoogleLoginResponse) {
  try {
    (await cookies()).set("access_token", data.access_token || "", {
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });
    (await cookies()).set("user_data", JSON.stringify(data.user), {
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });

    return {
      success: true,
      message: data.message,
      data: data.user,
    };
  } catch (error) {
    return {
      success: false,
      message: "Something went wrong",
    };
  }
}
