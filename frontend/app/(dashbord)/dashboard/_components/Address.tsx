"use client";

import { useQuery } from "@tanstack/react-query";
import { useSession } from "@/store/useAuthStore";
import Heading from "@/components/Heading";
import BodyText from "@/components/BodyText";
import { Button } from "@/components/ui/button";
import { useToken } from "@/store/useTokenStore";
import AddressSkeleton from "@/components/skeleton/AddressSkeleton";
import { api } from "@/lib/axios";
import { Card, CardContent } from "@/components/ui/card";
import { RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { RiPhoneFill } from "react-icons/ri";
import { FaMapMarkerAlt } from "react-icons/fa";
import { LucidePencilLine } from "lucide-react";

interface ApiResponse {
  data: Address[];
}

interface Address {
  id: number;
  type: string;
  phone: string;
  address: string;
  area_name: string;
  city_name: string;
  country_name: string;
  name: string;
  state_name: string;
  postal_code: string;
  address_type: string;
}

const Address = () => {
  const { user } = useSession();
  const { accessToken } = useToken();

  const { data: addresses, isLoading } = useQuery({
    queryKey: ["address"],
    queryFn: async () => {
      const { data } = await api.get(`/user/shipping/address/${user?.id}`, {
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
      });
      return data as ApiResponse;
    },
    enabled: !!user?.id && !!accessToken,
  });

  return (
    <div className="my-6 space-y-2">
      {isLoading ? (
        <>
          {[...Array(1)].map((_, i) => (
            <AddressSkeleton key={i} />
          ))}
        </>
      ) : (
        addresses?.data &&
        addresses.data.length > 0 &&
        addresses.data?.slice(0, 1)?.map((address) => (
          <>
            <Heading className="mt-4 mb-2 font-bold" variant="h6">
              Default address
            </Heading>
            <Card
              key={address.id}
              className="data-[state=checked]:border-site-primary-600 bg-site-gray-50 relative mb-2 rounded-[16px] shadow-none hover:bg-[#FBEDF5] data-[state=checked]:bg-[#FBEDF5] [state=checked]:border-[1.5px]"
            >
              <CardContent className="h-full p-4">
                <div className="flex h-full space-x-4">
                  <Label
                    htmlFor={`address-${address.id}`}
                    className="flex-1 cursor-pointer"
                  >
                    <div className="space-y-1">
                      <div className="flex items-center gap-1">
                        <BodyText
                          className="text-site-gray-800 mb-1 font-bold capitalize"
                          variant="one"
                        >
                          {address?.name}
                        </BodyText>
                      </div>
                      <BodyText
                        className="text-site-gray-700 flex items-center gap-1 font-normal data-[state=checked]:text-[#3080B5]"
                        variant="two"
                      >
                        <RiPhoneFill /> {address?.phone && address?.phone}
                      </BodyText>
                      <BodyText
                        variant="two"
                        className="text-site-gray-700 flex items-baseline gap-2 font-normal"
                      >
                        <FaMapMarkerAlt />
                        {address.area_name && `${address.area_name}, `}
                        {address.city_name && `${address.city_name}, `}
                        {address?.state_name}
                        {address.postal_code && `, ${address.postal_code}`}
                      </BodyText>
                    </div>
                  </Label>
                  <div className="flex flex-col items-center justify-between">
                    <div className="border-site-gray-200 bg-site-gray-100 text-site-gray-700 rounded-full border px-2 py-0.5 text-xs data-[state=checked]:border-[#FA045B] data-[state=checked]:bg-[#F8DCEB] data-[state=checked]:text-[#FA045B]">
                      {address.type || address.address_type || "Address"}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </>
        ))
      )}
    </div>
  );
};

export default Address;
