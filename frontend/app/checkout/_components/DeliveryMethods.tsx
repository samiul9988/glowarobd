"use client";

import { Card, CardContent } from "@/components/ui/card";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { Truck } from "lucide-react";
import Image from "next/image";
import { CartWithDelivery, ShippingMethod } from "@/lib/api/checkout";
import Heading from "@/components/Heading";
import BodyText from "@/components/BodyText";
import CustomImage from "@/components/cards/CustomImage";

interface DeliveryMethodsProps {
  cartWithDeliveryData?: CartWithDelivery[];
  selectedMethod?: string;
  onMethodChange: (method: string) => void;
  isLoading?: boolean;
}

export default function DeliveryMethods({
  cartWithDeliveryData,
  selectedMethod,
  onMethodChange,
  isLoading = false,
}: DeliveryMethodsProps) {
  if (isLoading) {
    return (
      <div className="space-y-3">
        <Heading variant="h5" className="text-lg font-medium">
          Choose Delivery Type
        </Heading>
        <div className="flex flex-wrap w-full items-center gap-2">
          {[1, 2, 3, 4].map((i) => (
            <div
              key={i}
              className="h-16 max-w-[48%] w-1/2 bg-gray-200 animate-pulse rounded-lg"
            />
          ))}
        </div>
      </div>
    );
  }

  
  // Get shipping methods from the first cart (assuming single vendor for now)
  const shippingMethods =
  cartWithDeliveryData?.[0]?.shipping_type?.[0]?.methods || [];
  
  if (shippingMethods.length === 0) {
    return null;
  }

  // if delivery method find
  return (
    <div className="space-y-4 pt-4 hidden">
      <Heading variant="h5" className="text-lg font-medium">
        Choose Delivery Type
      </Heading>

      <RadioGroup
        className="flex items-center flex-wrap"
        value={selectedMethod}
        onValueChange={onMethodChange}
      >
        {shippingMethods.map((method: ShippingMethod, index: number) => (
          <Card
            key={`${method.value}-${index}`}
            className="cursor-pointer hover:bg-gray-50 shadow-none  lg:max-w-[49%] w-full h-full flex items-center max-h-[70px] hover:border-1 hover:border-site-gray-900 transition duration-300 ease-in-out"
          >
            <CardContent className="p-0 h-full flex items-center w-full">
              <label
                className="flex items-center space-x-3 p-4 h-full w-full cursor-pointer"
                htmlFor={`delivery-${method.value}-${index}`}
              >
                <RadioGroupItem
                  value={method.value}
                  id={`delivery-${method.value}-${index}`}
                  className={`
                    h-5 cursor-pointer w-5 rounded-full border-2 border-site-gray-100
                    data-[state=checked]:border-site-primary-500
                    data-[state=checked]:text-site-primary-500
                    [&>span>svg]:data-[state=checked]:fill-site-primary-500
                    [&>span>svg]:data-[state=checked]:text-site-primary-500
                     [&>span>svg]:h-3 [&>span>svg]:w-3

                  `}
                />
                <div className="flex items-center space-x-3 cursor-pointer flex-1">
                  <div className="flex items-center space-x-3 flex-1">
                    {method.method_logo ? (
                      <CustomImage
                        src={method.method_logo}
                        alt={method.method_name}
                        width={42}
                        height={42}
                        className="rounded object-contain"
                      />
                    ) : (
                      <Truck className="h-8 w-8 text-gray-600" />
                    )}
                    <div className="flex-1">
                      <BodyText className="" variant="two">
                        {method.method_name}
                      </BodyText>
                      <BodyText
                        className="text-site-gray-700 font-semibold mt-1"
                        variant="two"
                      >
                        ৳{method.method_price}
                      </BodyText>
                    </div>
                  </div>
                </div>
              </label>
            </CardContent>
          </Card>
        ))}
      </RadioGroup>
    </div>
  );
}
