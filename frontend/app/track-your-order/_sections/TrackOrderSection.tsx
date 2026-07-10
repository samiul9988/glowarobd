"use client";

import OrderProgress from "@/app/(dashbord)/purchase-history/[id]/_components/OrderProgress";
import Container from "@/components/Container";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { api } from "@/lib/axios";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import Image from "next/image";
import { useForm } from "react-hook-form";
import toast from "react-hot-toast";
import * as z from "zod";

// Zod schema for order code validation
const orderSchema = z.object({
  orderCode: z
    .string()
    .min(1, { message: "Order code is required" })
    .min(16, "Order code must be at least 16 characters"),
});

type OrderFormValues = z.infer<typeof orderSchema>;

type ApiResponse = {
  success: boolean;
  message: string;
  data: PurchaseDetailsOrder;
};

const TrackOrderSection = () => {
  const form = useForm<OrderFormValues>({
    resolver: zodResolver(orderSchema),
    defaultValues: { orderCode: "" },
  });

  // Mutation for tracking order
  const { mutate, data, isPending } = useMutation({
    mutationKey: ["track-order-details"],
    mutationFn: async (orderCode: string) => {
      const res = await api.get(`/track-order?code=${orderCode}`);
      return res.data as ApiResponse;
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || "Something went wrong");
    },
  });

  const onSubmit = (values: OrderFormValues) => {
    mutate(values.orderCode);
  };

  const orderDetails = data?.data;

  return (
    <section>
      <Container className="py-12">
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(onSubmit)}
            className="mx-auto flex w-full max-w-4xl flex-col gap-2 rounded-[10px] border p-4 md:p-6"
          >
            <h3 className="mb-4 text-3xl">Check your order status</h3>
            <FormField
              control={form.control}
              name="orderCode"
              render={({ field }) => (
                <FormItem>
                  <FormLabel className="text-site-gray-500 text-sm font-normal">
                    Order Code
                  </FormLabel>
                  <div className="flex items-center">
                    <FormControl>
                      <Input
                        placeholder="Enter your order code"
                        {...field}
                        className="site-input-field rounded-tr-none rounded-br-none"
                      />
                    </FormControl>
                    <button
                      type="submit"
                      disabled={isPending}
                      className="bg-site-gray-700 hover:bg-site-gray-900 rounded-0 shrink-0 cursor-pointer rounded-tr-lg rounded-br-lg p-3 text-base font-medium text-white transition-colors disabled:opacity-70"
                    >
                      {isPending ? "Tracking..." : "Track Order"}
                    </button>
                  </div>
                  <FormMessage />
                </FormItem>
              )}
            />

            {orderDetails && (
              <div className="border-site-gray-100 mt-4 rounded-[10px] border bg-gradient-to-b from-[#F3FAFF] to-white p-4 md:mt-8 md:p-8">
                {/* Order details header */}
                <div className="space-y-0.5 md:space-y-1.5">
                  <div className="flex items-center gap-3">
                    <span className="text-site-gray-900 text-sm font-bold">
                      Order ID: {orderDetails.code}
                    </span>
                    <span className="badge text-site-gray-400 bg-site-gray-50">
                      {orderDetails.payment_status_string}
                    </span>
                  </div>
                  <span className="text-site-gray-400 text-sm">
                    Date: {orderDetails.date}
                  </span>
                </div>

                {/* Order details steps status */}
                <div className="my-12">
                  <OrderProgress status={orderDetails.delivery_status} />
                </div>

                {/* Order details body */}
                <div className="flex flex-col gap-10 md:flex-row md:gap-20">
                  {/* Left */}
                  <div className="flex-1 space-y-3 md:space-y-6">
                    <div className="space-y-0.5 md:space-y-1">
                      <span className="text-site-gray-400 block text-xs">
                        Shipping address:
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.shipping_address.address},
                        {orderDetails.shipping_address.area},
                        {orderDetails.shipping_address.city},
                        {orderDetails.shipping_address.country}
                      </span>
                    </div>

                    <div className="space-y-0.5 md:space-y-1">
                      <span className="text-site-gray-400 block text-xs">
                        Receiver Name:
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.shipping_address.name}
                      </span>
                    </div>

                    <div className="space-y-0.5 md:space-y-1">
                      <span className="text-site-gray-400 block text-xs">
                        Phone:
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.shipping_address.phone}
                      </span>
                    </div>
                  </div>
                  {/* Right */}
                  <div className="flex-1 space-y-3 md:space-y-6">
                    <div className="space-y-0.5 md:space-y-1">
                      <span className="text-site-gray-400 block text-xs">
                        Shipping method:
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.shipping_method}
                      </span>
                    </div>
                    <div className="space-y-0.5 md:space-y-1">
                      <span className="text-site-gray-400 block text-xs">
                        Payment method:
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.payment_type}
                      </span>
                    </div>
                  </div>
                </div>

                {/* Order details footer */}
                <div className="border-site-gray-100 mt-8 flex flex-col gap-8 rounded-[8px] border bg-white p-6 md:mt-12 md:flex-row md:gap-16">
                  {/* Left */}
                  <div className="flex-1 space-y-4 md:space-y-5">
                    {orderDetails.items?.data.map((product) => (
                      <div key={product.id} className="flex items-center gap-2">
                        <Image
                          src={imageBaseHostUrl + product.thumbnail_image}
                          alt="product"
                          height={60}
                          width={60}
                        />
                        <div className="space-y-1">
                          <p className="text-site-gray-900 line-clamp-2 text-sm">
                            {product.product_name}
                          </p>
                          <div className="space-x-2">
                            <span className="text-site-gray-900 text-sm font-semibold">
                              {product.price}
                            </span>
                            <span className="text-site-gray-400 text-sm">
                              X{product.quantity}
                            </span>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                  {/* Right */}
                  <div className="mt-auto w-full space-y-2 md:w-[220px]">
                    <div className="flex items-center justify-between">
                      <span className="text-site-gray-600 text-sm">
                        Subtotal
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.subtotal}
                      </span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-site-gray-600 text-sm">Tax</span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.tax}
                      </span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-site-gray-600 text-sm">
                        Delivery Charge
                      </span>
                      <span className="text-site-gray-900 text-sm font-semibold">
                        {orderDetails.shipping_cost}
                      </span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-site-gray-600 text-sm">
                        Coupon Discount
                      </span>
                      <span className="text-sm font-semibold text-[#0F9918]">
                        -{orderDetails.coupon_discount}
                      </span>
                    </div>
                    <hr />
                    <div className="mt-2 flex items-center justify-between">
                      <span className="text-site-gray-900 text-base font-bold">
                        Total
                      </span>
                      <span className="text-site-gray-900 text-base font-bold">
                        {orderDetails.grand_total}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </form>
        </Form>
      </Container>
    </section>
  );
};

export default TrackOrderSection;
