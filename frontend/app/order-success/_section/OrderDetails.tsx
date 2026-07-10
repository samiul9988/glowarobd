"use client";

import BodyText from "@/components/BodyText";
import Heading from "@/components/Heading";
import { imageBaseHostUrl } from "@/config/apiConfig";
import { useOrderStore } from "@/hooks/useOrderStore";
import Image from "next/image";
import Link from "next/link";
import toast from "react-hot-toast";
import OrderNotFound from "./OrderNotFound";

// Types
export interface Reward {
  amount: string;
  point: string;
  is_applied: number; // 0 or 1
}

export interface ShippingAddress {
  name: string;
  email: string;
  address: string;
  country: string;
  state: string;
  city: string;
  area?: string;
  phone?: string;
  zip?: string;
}

export interface OrderLinks {
  details: string;
}

export interface OrderDetails {
  id: number;
  user_id: number;
  code: string;
  date: string;

  subtotal: string;
  shipping_cost: string;
  tax: string;
  grand_total: string;
  total_amount_paid: string;
  coupon_discount: string;

  payment_status: "paid" | "unpaid" | string;
  payment_status_string: string;
  payment_type: string;
  manually_payable: boolean;
  payments: any[];

  delivery_status: string;
  delivery_status_string: string;
  shipping_method: string;
  shipping_type: string;
  shipping_type_string: string;

  shipping_address: ShippingAddress;
  reward: Reward;
  links: OrderLinks;

  cancel_request: boolean;
}

export interface OrderProduct {
  id: number;
  product_id: number;
  product_name: string;
  variation: string | null;

  price: string;
  tax: string;
  shipping_cost: string;
  coupon_discount: string;

  quantity: number;

  payment_status: "paid" | "unpaid" | string;
  payment_status_string: string;

  delivery_status: string;
  delivery_status_string: string;

  refund_section: boolean;
  refund_button: boolean;
  refund_label: string;
  refund_request_status: number;

  link: string;
  thumbnail_image: string;
}

interface OrderDetailsProps {
  orderDetails: any; //  api issue
  orderProduct: any;
  orderId: string;
}

const OrderSuccessDetails = ({
  orderDetails,
  orderProduct,
  orderId,
}: OrderDetailsProps) => {
  const order = orderDetails?.data?.[0] || orderDetails;
  const products =
    orderProduct?.data ||
    (Array.isArray(orderProduct) ? orderProduct : [orderProduct]);

  const exists = useOrderStore((state) => state.exists);
  const isExist = exists(orderId);
  if (!isExist) {
    toast.error("Order not found");
    return <OrderNotFound />;
  }

  return (
    <div className="mx-auto max-w-4xl px-4 py-8">
      {order && order?.id > 0 ? (
        <div>
          {/* Success Header */}
          <div className="mb-8 text-center">
            <div className="mb-6">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="64"
                height="64"
                fill="currentColor"
                className="mx-auto text-green-600"
                viewBox="0 0 16 16"
              >
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
              </svg>
              {/* <Image
                src="/images/order-success.gif"
                width={200}
                height={200}
                alt="order success"
              /> */}
            </div>
            <Heading variant="h3" className="mb-4 font-bold text-gray-900">
              Order Successfully Placed!
            </Heading>
            <BodyText variant="two" className="text-lg text-gray-600">
              Thank you for your purchase. Your order has been confirmed.
            </BodyText>
          </div>

          {/* Order Details Card */}
          <div className="overflow-hidden rounded-lg bg-white shadow-sm">
            <div className="border-b bg-gray-50 px-6 py-4">
              <div className="flex items-center justify-between">
                <Heading
                  variant="h6"
                  className="text-xl font-semibold text-gray-900"
                >
                  Order Details
                </Heading>
                {/* <button
                  onClick={() => window.print()}
                  className="bg-gray-600 text-white text-sm rounded font-medium py-2 px-4 hover:bg-gray-700 transition-colors flex items-center gap-2"
                >
                  <BsPrinter />
                  Print
                </button> */}
              </div>
            </div>

            <div className="p-2 lg:p-6">
              {/* Order Information */}

              <div className="flex flex-col gap-4 lg:flex-row lg:gap-5">
                <div className="w-full lg:w-1/2">
                  <h4 className="mb-2 text-lg">Order Status</h4>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Order
                    </BodyText>
                    <span>:</span>
                    <span className="text-site-gray-400 text-sm">
                      {order?.code}
                    </span>
                  </div>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Payment Status
                    </BodyText>
                    <span>:</span>
                    <span
                      className={`text-sm ${(order?.payment_status || order?.payment_status_string) == "unpaid" ? "text-red-500" : "text-green-500"}`}
                    >
                      {order?.payment_status_string || order?.payment_status}
                    </span>
                  </div>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Payment Type
                    </BodyText>
                    <span>:</span>
                    <span className={`text-site-gray-400 text-sm`}>
                      {order?.payment_type}
                    </span>
                  </div>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Shipping Method:
                    </BodyText>
                    <span>:</span>
                    <span className="text-site-gray-400 text-sm">
                      {order?.shipping_method}
                    </span>
                  </div>
                </div>

                {/* shipping address */}
                <div className="w-full lg:w-1/2">
                  <h4 className="mb-2 text-lg">Shipping Address</h4>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Name
                    </BodyText>
                    <span>:</span>
                    <span className="text-site-gray-400 text-sm">
                      {order.shipping_address.name}
                    </span>
                  </div>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Phone
                    </BodyText>
                    <span>:</span>
                    <span className="text-site-gray-400">
                      {order.shipping_address.phone}
                    </span>
                  </div>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 truncate"
                    >
                      Area
                    </BodyText>
                    <span>:</span>
                    <span className="text-site-gray-400">
                      {order.shipping_address.address}
                    </span>
                  </div>
                  <div className="flex items-center gap-4 py-2">
                    <BodyText
                      variant="two"
                      className="text-site-gray-900 w-1/3 flex-grow truncate"
                    >
                      Address
                    </BodyText>
                    <span className="ml-6">:</span>
                    <span className={`text-site-gray-400 text-sm`}>
                      {order?.shipping_address?.area &&
                        `${order.shipping_address.area}, `}
                      {order?.shipping_address?.city &&
                        `${order.shipping_address.city}, `}
                      {order?.shipping_address?.state &&
                        `${order.shipping_address.state} `}
                      {order?.shipping_address?.postal_code}
                    </span>
                  </div>
                </div>
              </div>

              {/* product items */}
              <hr className="my-6" />
              <div className="mt-10 mb-6">
                <div className="w-full">
                  <h4 className="mb-2 text-lg">Order Items</h4>
                  {products && products.length > 0 && (
                    <div className="mb-4 w-full border-b border-gray-200 pb-4">
                      {/* Header Row */}
                      <div className="mb-3 flex w-full items-center">
                        <div className="w-3/5">Product</div>
                        <div className="w-1/5 text-center">Quantity</div>
                        <div className="w-1/5 text-center">Price</div>
                      </div>

                      {/* Product Rows */}
                      {products.map((product: any, index: number) => (
                        <div key={index} className="mb-2 flex w-full">
                          <div className="flex w-3/5 items-center gap-2 text-sm font-normal">
                            <div className="relative h-10 w-16 lg:h-16 lg:w-16">
                              <Image
                                src={
                                  imageBaseHostUrl + product?.thumbnail_image
                                }
                                fill
                                className="rounded-md border-1 object-cover"
                                alt={product?.product_name}
                                unoptimized
                              />
                            </div>
                            <span className="line-clamp-2 text-xs lg:text-sm">
                              {product?.product_name}
                            </span>
                          </div>
                          <div className="flex w-1/5 items-center justify-center text-center text-sm font-normal">
                            {product?.quantity}
                          </div>
                          <div className="flex w-1/5 items-center justify-center text-center text-sm font-normal">
                            {product?.price}
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              {/* Order Summary */}
              <div className="space-y-3">
                <div className="flex justify-between py-2">
                  <span className="text-gray-600">Subtotal:</span>
                  <span className="font-medium">{order?.subtotal}</span>
                </div>
                <div className="flex justify-between py-2">
                  <span className="text-gray-600">Shipping Cost:</span>
                  <span className="font-medium">{order?.shipping_cost}</span>
                </div>
                <div className="flex justify-between py-2">
                  <span className="text-gray-600">Total TAX:</span>
                  <span className="font-medium">{order?.tax}</span>
                </div>
                <hr className="my-4" />
                <div className="flex justify-between py-2">
                  <span className="text-gray-600">Coupon Discount:</span>
                  <span className="font-medium">{order?.coupon_discount}</span>
                </div>
                <hr className="my-4" />
                <div className="flex justify-between py-2 text-lg font-semibold">
                  <span>Net Total:</span>
                  <span>{order?.grand_total}</span>
                </div>
              </div>
            </div>

            <div className="border-t bg-gray-50 px-6 py-4 text-center">
              <small className="text-gray-600">
                A confirmation email has been sent to your registered email
                address.
              </small>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="mt-8 flex items-center justify-center gap-4 text-center">
            <Link
              href="/"
              className="bg-site-gray-900 hover:site-gray-600 rounded-lg px-6 py-3 font-medium text-white transition-colors"
            >
              Return to Home
            </Link>
          </div>
        </div>
      ) : (
        <div className="text-center">
          <div className="mb-6">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="64"
              height="64"
              fill="currentColor"
              className="mx-auto text-red-500"
              viewBox="0 0 16 16"
            >
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
              <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
            </svg>
          </div>
          <h1 className="mb-4 text-4xl font-bold text-gray-900">
            Order Not Found!
          </h1>
          <p className="mb-8 text-lg text-gray-600">
            Order not found. Please check your order ID.
          </p>
          <Link
            href="/"
            className="bg-site-gray-900 hover:bg-site-gray-700 rounded-lg px-6 py-3 font-medium text-white transition-colors"
          >
            Return to Home
          </Link>
        </div>
      )}
    </div>
  );
};

export default OrderSuccessDetails;
