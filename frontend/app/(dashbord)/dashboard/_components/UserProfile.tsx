"use client";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/axios";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { badges } from "@/lib/badge";
import Link from "next/link";
import { PencilLine } from "lucide-react";
interface Props {
  userData: User | null;
}

export default function UserProfile({ userData }: Props) {
  const { user, logout } = useSession();
  const { accessToken } = useToken();

  // Fetch User Data
  const { data } = useQuery({
    queryKey: ["get_user_data"],
    queryFn: async () => {
      const res = await api.post(
        `/get-user-by-access_token`,
        { access_token: accessToken },
        { headers: { Authorization: `Bearer ${accessToken}` } },
      );
      return res.data as UserResponse;
    },
    enabled: !!accessToken,
  });
  const imageSrc =
    (data?.avatar_original && imageBaseHostUrl + data.avatar_original) ||
    "/images/placeholder.png";

  const currentGroup = badges.find(
    (group) => group.id === user?.customer_group?.id,
  );
  return (
    <>
      {/* Profile Section */}
      {userData && (
        <div className="border-site-gray-100 mb-[40px] flex items-center justify-between gap-4 rounded-xl border px-5 py-4">
          {/* Avatar */}
          <div className="flex items-center gap-4">
            <div className="relative">
              <Avatar className="relative h-14 w-14 border">
                <AvatarImage src={imageSrc || ""} alt={userData.name} />
                <AvatarFallback className="bg-site-primary-100 h-14 w-14 font-bold">
                  {userData.name
                    ?.split(" ")
                    .map((n) => n[0])
                    .join("")
                    .toUpperCase()}
                </AvatarFallback>
              </Avatar>

              {/* Badge */}
              {/* {currentGroup && (
                    <span className="absolute bottom-0 left-1/2 h-8 w-8 translate-x-[-50%] translate-y-[35%]">
                        <Image src={currentGroup.badge} alt="badge" fill />
                    </span>
                    )} */}
            </div>

            {/* Info */}
            <div className="flex min-w-0 flex-col items-center justify-center gap-1">
              <p className="text-site-gray-900 font-inter text-base font-semibold capitalize">
                {userData.name}
              </p>
              {userData?.email && (
                <p className="text-site-gray-600 truncate text-sm">
                  {userData.email}
                </p>
              )}
              {userData?.phone && (
                <p className="text-site-gray-600 truncate text-sm">
                  {userData.phone}
                </p>
              )}
            </div>
          </div>
          <Link
            href="/profile"
            className="text-site-primary-600 hover:text-site-primary-500 border-site-primary-600 mb flex items-center gap-1 rounded-full border-2 px-6 py-2 text-center text-xs font-bold"
          >
            Edit Profile
          </Link>
        </div>
      )}
    </>
  );
}
