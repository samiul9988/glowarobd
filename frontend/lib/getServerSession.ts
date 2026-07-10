"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

export type User = {
  id: number;
  type: "customer";
  name: string;
  email: string;
  avatar: string | null;
  avatar_original: string | null;
  phone: string;
  gender: "male" | "female" | "other" | string;
  date_of_birth: string | null;
  customer_group: {
    id: number;
    name: string;
  };
};

export async function getServerSession(): Promise<User | null> {
  const cookieStore = await cookies();
  const userCookie = cookieStore.get("user_data")?.value;

  if (!userCookie) {
    return null;
  }

  try {
    // Parse the stringified user object from the cookie
    const user = JSON.parse(userCookie) as User;
    return user;
  } catch (error) {
    console.error("Error parsing user_data cookie:", error);
    // Clear bad cookies if parsing fails
    const cookieStore = await cookies();

    cookieStore.set("user_data", "", { maxAge: 0, path: "/" });
    cookieStore.set("access_token", "", { maxAge: 0, path: "/" });
    return null;
  }
}

export async function logoutAction() {
  const cookieStore = await cookies();
  cookieStore.delete("access_token");
  cookieStore.delete("user_data");

  return redirect("/");
  // return { success: true, message: "Logged out successfully." };
}
