"use client";

import BodyText from "@/components/BodyText";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import {
  useDeleteShippingAddress,
  useUserShippingAddresses,
} from "@/hooks/queries/useCheckout";
import { ShippingAddress } from "@/lib/api/checkout";
import { Edit, LocateFixedIcon, LucidePencilLine, Phone } from "lucide-react";
import { DeleteAddressButton } from "./DeleteAddressButton";
import { RiPhoneFill, RiUserLocationFill } from "react-icons/ri";
import { FaMapMarkerAlt } from "react-icons/fa";

interface AddressSelectorProps {
  userId: string | number;
  selectedAddressId?: number;
  onAddressSelect: (addressId: number) => void;
  onAddNewAddress: () => void;
  handleEditAddress: (address: ShippingAddress) => void;
}

export default function AddressSelector({
  userId,
  selectedAddressId,
  onAddressSelect,
  onAddNewAddress,
  handleEditAddress,
}: AddressSelectorProps) {
  const { data: addressesData, isLoading } = useUserShippingAddresses(userId);
  const deleteAddressMutation = useDeleteShippingAddress();
  const addresses = addressesData?.data || [];

  // onLoading
  if (isLoading) {
    return (
      <div className="space-y-3">
        {[1, 2].map((i) => (
          <div key={i} className="h-20 animate-pulse rounded-lg bg-gray-200" />
        ))}
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {addresses.length > 0 ? (
        <RadioGroup
          value={selectedAddressId?.toString()}
          onValueChange={(value) => onAddressSelect(parseInt(value))}
        >
          {addresses.map((address: ShippingAddress & { id: number }) => (
            <Card
              key={address.id}
              className="relative mb-2 shadow-none hover:bg-[#FBEDF5] data-[state=checked]:bg-[#FBEDF5] data-[state=checked]:border-site-primary-600 [state=checked]:border-[1.5px] rounded-[16px] bg-site-gray-50"
              data-state={
                selectedAddressId === address.id ? "checked" : "unchecked"
              }
            >
              <CardContent className="p-4 h-full">
                <div className="flex items-center space-x-4 h-full">
                  <RadioGroupItem
                    value={address.id.toString()}
                    id={`address-${address.id}`}
                    className={`border-site-gray-100 data-[state=checked]:border-site-primary-600 data-[state=checked]:text-site-primary-600 [&>span>svg]:data-[state=checked]:fill-site-primary-500 [&>span>svg]:data-[state=checked]:text-site-primary-500 h-5 w-5 cursor-pointer rounded-full border-2 [&>span>svg]:h-3 [&>span>svg]:w-3`}
                  />
                  <Label
                    htmlFor={`address-${address.id}`}
                    className="flex-1 cursor-pointer"
                  >
                    <div className="space-y-1">
                      <div className="flex items-center gap-1">
                        <BodyText
                          className="text-site-gray-800 font-bold capitalize mb-1"
                          variant="one"
                        >
                          {address?.name}
                        </BodyText>
                       
                      </div>
                      <BodyText
                        className="text-site-gray-700 font-normal data-[state=checked]:text-[#3080B5] flex gap-1 items-center"
                        variant="two"
                      >
                       <RiPhoneFill /> {address?.phone && address?.phone}
                      </BodyText>
                      <BodyText
                        variant="two"
                        className="text-site-gray-700 font-normal flex gap-2 items-baseline"
                      >
                        <FaMapMarkerAlt />
                        {address.area_name && `${address.area_name}, `}
                        {address.city_name && `${address.city_name}, `}
                        {address?.state_name}
                        {address.postal_code && `, ${address.postal_code}`}
                      </BodyText>
                    </div>
                  </Label>
                  <div className="h-full flex items-center flex-col justify-between">
                   <div
                    data-state={selectedAddressId === address.id ? "checked" : "unchecked"}
                    className="
                        border-site-gray-200 bg-site-gray-100 text-site-gray-700
                        border
                        data-[state=checked]:border-[#FA045B]
                        data-[state=checked]:bg-[#F8DCEB]
                        data-[state=checked]:text-[#FA045B]
                        px-2 py-0.5 rounded-full text-xs
                    "
                    >
                    {address.type || address.address_type || "Address"}
                    </div>

                    <div className="flex items-center gap-2">
                        <button
                        onClick={(e) => handleEditAddress(address)}
                        className="mr-0 cursor-pointer "
                        >
                        <LucidePencilLine className="h-4 w-4" />
                        </button>

                        <DeleteAddressButton
                        addressId={address.id}
                        deleteAddressMutation={deleteAddressMutation}
                        />
                    </div>
                   
                    
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </RadioGroup>
      ) : (
        <Card>
          <CardContent className="p-6 text-center">
            <p className="mb-4 text-gray-500">No addresses found</p>
            <Button onClick={onAddNewAddress}>Add Your First Address</Button>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
