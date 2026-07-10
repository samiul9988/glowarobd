"use client";

import { usePaymentTypes } from "@/hooks/queries/useCheckout";
import { Card, CardContent } from "@/components/ui/card";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { CreditCard, Banknote, Smartphone } from "lucide-react";
import Heading from "@/components/Heading";
import BodyText from "@/components/BodyText";
import Image from "next/image";
import CustomImage from "@/components/cards/CustomImage";

interface PaymentMethod {
  payment_type: string;
  payment_type_key: string;
  image: string;
  name: string;
  title: string;
  offline_payment_id: number;
  details: string;
}

type PaymentType = "cash_payment" | "sslcommerz_payment" | "bkash";

interface PaymentMethodsProps {
  selectedMethod: PaymentType | "";
  onMethodChange: (method: PaymentType) => void;
}

const paymentIcons = {
  cash_payment: Banknote,
  sslcommerz_payment: CreditCard,
  bkash: Smartphone,
};

export default function PaymentMethods({
  selectedMethod,
  onMethodChange,
}: PaymentMethodsProps) {
  const { data: paymentTypesData, isLoading, error } = usePaymentTypes();

  // Debug logging

  if (isLoading) {
    return (
      <div className="flex flex-wrap w-full items-center gap-2">
        {[1, 2, 3, 4].map((i) => (
          <div
            key={i}
            className="h-16 max-w-[48%] w-1/2 bg-gray-200 animate-pulse rounded-lg"
          />
        ))}
      </div>
    );
  }

  // Handle both direct array and nested data structure
  const paymentMethods = Array.isArray(paymentTypesData)
    ? paymentTypesData
    : paymentTypesData?.data || [];

  if (error) {
    return (
      <div className="space-y-3">
        <h3 className="text-lg font-medium">Payment Method</h3>
        <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p className="text-red-600">Failed to load payment methods</p>
          <p className="text-sm text-red-500 mt-1">{error.message}</p>
        </div>
      </div>
    );
  }

  if (paymentMethods.length === 0 && !isLoading) {
    return (
      <div className="space-y-3">
        <h3 className="text-lg font-medium">Payment Method</h3>
        <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p className="text-yellow-600">No payment methods available</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-4 bg-site-gray-50 p-4 rounded-2xl">
      <Heading variant="h5" className="text-lg font-bold">
        Choose Payment Option
      </Heading>

      <RadioGroup
        value={selectedMethod || undefined}
        onValueChange={(value) => onMethodChange(value as PaymentType)}
      >
        <div className="flex items-center flex-wrap gap-2">
          {paymentMethods.map((method: PaymentMethod) => {
            const IconComponent =
              paymentIcons[method.payment_type as keyof typeof paymentIcons] ||
              CreditCard;

            return (
              <Card
                key={method.payment_type}
                className="cursor-pointer  shadow-none  w-full h-full flex items-center max-h-[35px] transition duration-300 ease-in-out bg-transparent border-none "
              >
                <CardContent className="p-0  h-full flex items-center w-full min-h-[35px]">
                  <label
                    className="flex items-center space-x-3 h-full w-full cursor-pointer"
                    htmlFor={`payment-${method.payment_type}`}
                  >
                    <RadioGroupItem
                      value={method.payment_type}
                      id={`payment-${method.payment_type}`}
                      className={`
                        h-5 cursor-pointer w-5 rounded-full border-2 border-site-gray-100
                        data-[state=checked]:border-site-primary-600
                        data-[state=checked]:text-site-primary-600
                        [&>span>svg]:data-[state=checked]:fill-site-primary-600
                        [&>span>svg]:data-[state=checked]:text-site-primary-600
                        [&>span>svg]:h-3 [&>span>svg]:w-3

                      `}
                    />
                    <div className="flex items-center space-x-3 cursor-pointer flex-1">
                      {method.image ? (
                        <CustomImage
                          src={method.image}
                          alt={method.name}
                          width={48}
                          height={48}
                          className="rounded object-contain"
                        />
                      ) : (
                        <IconComponent className="h-5 w-5 text-gray-600" />
                      )}
                      <div>
                        {method.title && (
                          <BodyText variant="two" className="font-medium text-site-gray-800 text-lg capitalize">
                            {method.title}
                          </BodyText>
                        )}
                      </div>
                    </div>
                  </label>
                </CardContent>
              </Card>
            );
          })}
        </div>
      </RadioGroup>
    </div>
  );
}
