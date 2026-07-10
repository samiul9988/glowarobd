"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { useInfoModalStore } from "@/store/useInfoModalStore";

import Heading from "@/components/Heading";
import BodyText from "@/components/BodyText";
import InfoModal from "@/components/modals/InfoModal";
import YourGroupSkeleton from "@/components/skeleton/YourGroupSkeleton";

import { BadgeCheck, Info } from "lucide-react";
import Image from "next/image";
import { badges } from "@/lib/badge";

const YourGroup = () => {
  const { user } = useSession();
  const { accessToken } = useToken();
  const { isOpen, setOpen } = useInfoModalStore();

  // Get Global User Data
  const { data: userData, isLoading: isLoadingUserData } = useQuery({
    queryKey: ["get_user_data"],
    queryFn: async () => {
      const res = await api.post(
        `/get-user-by-access_token`,
        {
          access_token: accessToken,
        },
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        },
      );
      return res.data as UserResponse;
    },

    enabled: !!user?.id && !!accessToken,
  });

  // store the selected group for modal
  const [selectedGroup, setSelectedGroup] = useState<Group | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ["get_groups", user?.id],
    queryFn: async () => {
      const { data } = await api.post(
        "/get-group-list-with-user-current-group",
        {},
        { headers: { Authorization: `Bearer ${accessToken}` } },
      );
      return data as GroupApiResponse;
    },
    enabled: !!user?.id && !!accessToken,
  });

  const groups = data?.groups ?? [];

  return (
    <div className="pt-2 lg:pt-4">
      <Heading variant="h5" className="pb-4 text-[#1F2029]">
        Your Group
      </Heading>

      {isLoadingUserData || isLoading ? (
        <YourGroupSkeleton />
      ) : (
        <div className="flex flex-wrap gap-2 lg:gap-4">
          {groups.map((group, index) => (
            <div
              key={group.id}
              className="bg-site-gray-50 border-site-gray-50 relative flex min-w-[150px] flex-1 flex-col items-center rounded-md border-2 px-3 py-3 lg:px-4 lg:py-5"
            >
              {userData?.group.id === group.id && (
                <BadgeCheck
                  className="fill-site-primary absolute top-1 left-1 text-white"
                  size={28}
                />
              )}

              {/* Info button → open modal */}
              <Info
                onClick={() => {
                  setSelectedGroup(group);
                  setOpen(true);
                }}
                className="text-site-gray-300 hover:text-site-primary absolute top-1.5 right-1.5 cursor-pointer transition"
                size={18}
              />

              {/* Badge */}
              {badges[index] && (
                <Image
                  src={badges[index].badge}
                  alt={badges[index].name}
                  height={38}
                  width={38}
                  className="mb-2.5"
                />
              )}

              {/* Group name + info */}
              <Heading
                variant="h6"
                className="text-site-gray-900 pb-1.5 text-xl lg:text-[23px]"
              >
                {group.name}
              </Heading>

              <BodyText variant="two" className="text-site-gray-700">
                {group.min_order_qty}{" "}
                <span className="font-normal">purchases</span>
              </BodyText>
            </div>
          ))}
        </div>
      )}

      {/* Single global modal, dynamic content */}
      {selectedGroup && (
        <InfoModal
          open={isOpen}
          onClose={() => setOpen(false)}
          title={selectedGroup.name}
          messages={selectedGroup.message}
        />
      )}
    </div>
  );
};

export default YourGroup;
