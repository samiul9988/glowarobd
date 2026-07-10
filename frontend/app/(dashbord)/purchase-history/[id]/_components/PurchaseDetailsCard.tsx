"use client";

import PurchaseDetailsSkeleton from "@/components/skeleton/PurchaseDetailsSkeleton";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { api } from "@/lib/axios";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import Image from "next/image";
import { useState } from "react";
import toast from "react-hot-toast";
import OrderProgress from "./OrderProgress";
import { BiLeftArrowAlt } from "react-icons/bi";
import { Calendar, LocateFixedIcon, Package, Phone } from "lucide-react";
import { RiPhoneFill } from "react-icons/ri";
import { IoLocationSharp } from "react-icons/io5";
import { useRouter } from "next/navigation";
import CustomImage from "@/components/cards/CustomImage";

interface Props {
  id: string;
}

interface ApiResponse {
  success: boolean;
  status: number;
  data: PurchaseDetailsOrder[];
}

const PurchaseDetailsCard = ({ id }: Props) => {
  const { accessToken } = useToken();
  const queryClient = useQueryClient();
  const [openPopup, setOpenPopup] = useState(false);
  const router = useRouter();
  // Get order
  const { data: order, isLoading } = useQuery({
    queryKey: ["get-purchase-history-details", id],
    queryFn: async () => {
      const res = await api.get(`/purchase-history-details/${id}`, {
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
      });
      return res.data as ApiResponse;
    },
    enabled: !!id && !!accessToken,
  });

  // Cancel order
  const { mutate: cancelOrder } = useMutation({
    mutationKey: ["cancel-order"],
    mutationFn: async () => {
      const res = await api.post(
        `/purchase-history-cancel`,
        {
          order_id: orderDetails.id,
          user_id: orderDetails.user_id,
        },
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        },
      );
      return res.data;
    },

    onSuccess: (data) => {
      if (data && data.result) {
        toast.success(data.message);
      }
      queryClient.invalidateQueries({
        queryKey: ["get-purchase-history-details", id],
      });

      queryClient.invalidateQueries({
        queryKey: ["get_purchase_history"],
        exact: false,
      });
    },
  });

  interface ProductApiResponse {
    success: boolean;
    status: number;
    data: OrderItem[];
  }

  // Get purchase products
  const { data: products } = useQuery({
    queryKey: ["get_purchase_products", id],
    queryFn: async () => {
      const { data } = await api.get(`/purchase-history-items/${id}`, {
        headers: { Authorization: `Bearer ${accessToken}` },
      });
      return data as ProductApiResponse;
    },
    enabled: !!id && !!accessToken,
  });

  // const orderStatus = "packaging";

  const handleCancelOrder = () => {
    cancelOrder();
    setOpenPopup(false);
  };

  if (isLoading || !order) {
    return <PurchaseDetailsSkeleton />;
  }

  if (!order?.data[0]) {
    return (
      <div className="border-site-gray-100 rounded-[10px] border bg-gradient-to-b from-[#F3FAFF] to-white p-4 text-center md:p-8">
        <span className="text-site-gray-900 text-sm font-bold">
          Order not found!
        </span>
      </div>
    );
  }

  const orderDetails = order && order?.data[0];
  return (
    <div className="border-site-gray-100 rounded-[10px] border p-4 md:p-8">
      <div className="mb-12">
        <button
          className="padding-2 flex cursor-pointer items-center gap-2"
          onClick={() => router.back()}
        >
          <BiLeftArrowAlt size={20} /> Back to Order List
        </button>
      </div>
      {/* Order details header */}
      <div className="border-site-gray-100 bg-site-gray-50 space-y-0.5 rounded-2xl border px-2 py-3 md:space-y-1.5 lg:px-4">
        <div className="flex items-center justify-between">
          <div>
            <div className="text-site-gray-900 mb-3 text-sm font-bold">
              #{orderDetails.code}
            </div>
            <div className="flex items-center gap-1 lg:gap-4">
              <span className="text-site-gray-600 flex flex-nowrap items-center gap-1 text-xs capitalize lg:text-sm">
                <Calendar size={16} /> Date: {orderDetails.date}
              </span>

              <span className="text-site-gray-600 flex flex-nowrap items-center gap-1 text-xs capitalize lg:text-sm">
                <Package size={16} /> {orderDetails.delivery_status}
              </span>
              <span className="text-site-gray-600 flex flex-nowrap items-center gap-1 text-xs capitalize lg:text-sm">
                <Package size={16} /> {orderDetails.payment_status_string}
              </span>
            </div>
          </div>
          <div>
            <span className="text-site-primary-600 text-base font-bold lg:text-lg">
              {orderDetails.subtotal}
            </span>
          </div>
        </div>
      </div>

      {/* Order details steps status */}
      <div className="my-6">
        <OrderProgress status={orderDetails.delivery_status} />
      </div>

      {/* Order details body */}
      <div className="flex flex-col gap-7 md:flex-row md:gap-8">
        {/* Left */}
        <div className="border-site-gray-100 flex-1 space-y-3 rounded-lg border p-5">
          <div className="space-y-0.5 md:space-y-1">
            <span className="text-site-gray-900 mb-2 block text-sm font-bold">
              Delivery Address:
            </span>
            <div className="text-site-gray-900 mb-1 block text-lg font-bold capitalize">
              {orderDetails.shipping_address.name}
              {orderDetails?.shipping_type && (
                <span className="badge bg-site-gray-100 text-site-gray-500 ml-2 text-sm font-normal capitalize">
                  {orderDetails?.shipping_type_string}
                </span>
              )}
            </div>
            <span className="text-site-gray-700 flex gap-1 text-sm font-normal">
              <RiPhoneFill size={16} />
              {orderDetails.shipping_address.phone}
            </span>
            <div className="text-site-gray-700 flex gap-1 text-sm font-normal">
              <IoLocationSharp size={16} />
              <span>
                {orderDetails.shipping_address.address},
                {orderDetails.shipping_address.area},
                {orderDetails.shipping_address.city},
                {orderDetails.shipping_address.country}
              </span>
            </div>
          </div>
        </div>
        {/* Right */}
        <div className="border-site-gray-100 flex-1 space-y-3 rounded-lg border p-5">
          <div className="">
            <span className="text-site-gray-900 block text-sm font-bold">
              Others Info:
            </span>
          </div>
          {orderDetails?.shipping_method && (
            <div className="space-y-0.5">
              <span className="text-site-gray-400 block text-xs">
                Shipping method:
              </span>
              <span className="text-site-gray-900 text-sm font-semibold">
                {orderDetails?.shipping_method}
              </span>
            </div>
          )}
          <div className="space-y-0.5">
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
      <div className="mt-8 flex flex-col gap-8 rounded-[8px] bg-white md:mt-12 md:flex-row md:gap-16">
        {/* Left */}
        <div className="flex-1 space-y-4 md:space-y-5">
          {products?.data.map((product) => (
            <div key={product.id} className="flex items-center gap-2 md:gap-4">
              <div className="relative">
                <CustomImage
                  src={imageBaseHostUrl + product.thumbnail_image}
                  alt="product"
                  height={60}
                  className="rounded-sm border"
                  width={60}
                />
                <span className="bg-site-primary-600 absolute top-0 right-0 z-10 flex h-4 w-4 items-center justify-center rounded-[2px] p-0.5 text-sm font-bold text-white">
                  {product.quantity}
                </span>
              </div>

              <div className="space-y-1">
                <p className="text-site-gray-900 line-clamp-2 text-sm">
                  {product.product_name}
                </p>
                <div className="space-x-2">
                  <span className="text-site-gray-900 text-sm font-bold">
                    {product.price}
                  </span>
                </div>
              </div>
            </div>
          ))}
        </div>
        {/* Right */}
        <div className="border-site-gray-100 mt-auto w-full space-y-2 rounded-md border p-5 md:w-[220px]">
          <div className="border-site-gray-100 flex items-center justify-between border-b pb-1">
            <span className="text-site-gray-600 text-sm">Subtotal</span>
            <span className="text-site-gray-900 text-sm font-normal">
              {orderDetails.subtotal}
            </span>
          </div>
          <div className="border-site-gray-100 flex items-center justify-between border-b pb-1">
            <span className="text-site-gray-600 text-sm">Tax</span>
            <span className="text-site-gray-900 text-sm font-normal">
              {orderDetails.tax}
            </span>
          </div>
          <div className="border-site-gray-100 flex items-center justify-between border-b pb-1">
            <span className="text-site-gray-600 text-sm">Delivery Charge</span>
            <span className="text-site-gray-900 text-sm font-normal">
              {orderDetails.shipping_cost}
            </span>
          </div>
          <div className="flex items-center justify-between">
            <span className="text-site-gray-600 text-sm">Coupon Discount</span>
            <span className="text-sm font-semibold text-[#0F9918]">
              -{orderDetails.coupon_discount}
            </span>
          </div>
          <hr />
          <div className="mt-2 flex items-center justify-between">
            <span className="text-site-gray-900 text-base font-bold">
              Total
            </span>
            <span className="text-site-primary-600 text-base font-bold">
              {orderDetails.grand_total}
            </span>
          </div>
        </div>
      </div>

      {/* Cancel Button */}
      {orderDetails.delivery_status == "pending" && (
        <div className="mt-8 text-right">
          <button
            className="cursor-pointer rounded-full border-2 border-[#E54545] px-4 py-2 text-sm font-semibold text-[#E54545] hover:bg-[#E54545] hover:text-white"
            onClick={() => setOpenPopup(true)}
          >
            Cancel Order
          </button>

          {/* Confirm popup */}
          <AlertDialog open={openPopup} onOpenChange={setOpenPopup}>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle hidden>Are you sure?</AlertDialogTitle>
                <p>Are you sure?</p>
                <AlertDialogDescription>
                  This action will cancel your order. You cannot undo this
                  action.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel className="cursor-pointer">
                  Close
                </AlertDialogCancel>
                <AlertDialogAction
                  onClick={handleCancelOrder}
                  className="bg-site-primary hover:bg-site-primary/95 cursor-pointer"
                >
                  Yes, Cancel Order
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>
      )}
    </div>
  );
};

export default PurchaseDetailsCard;
