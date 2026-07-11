"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useCartStore } from "@/store/useCartStore";
import { useAuthStore } from "@/store/useAuthStore";
import {
  useUserShippingAddresses,
  useUpdateAddressInCart,
  useCreateOrder,
  useCartWithDelivery,
  useCart,
  usePaymentTypes,
  useStoreDeliveryInfo,
  useCartSummary,
  checkoutKeys,
  useStoreValidateData,
  useSSLBeginPayment,
  usebKashPayment,
} from "@/hooks/queries/useCheckout";
import AddressSelector from "./AddressSelector";
import AddressForm from "./AddressForm";
import PaymentMethods from "./PaymentMethods";
import DeliveryMethods from "./DeliveryMethods";
import OrderSummary from "./OrderSummary";
import toast from "react-hot-toast";
import Container from "@/components/Container";
import Heading from "@/components/Heading";
import { Plus } from "lucide-react";
import { Dialog, DialogTrigger } from "@radix-ui/react-dialog";
import { DialogContent, DialogTitle } from "@/components/ui/dialog";
import { cn } from "@/lib/utils";
import { useShowHeader } from "@/store/useShowHeader";
import { useGuestUserId } from "@/store/useGuestStore";
import {
  DialogHeader,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/components/ui/input-otp";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { verifyOTPSchema } from "@/schema/verifyOTPSchema";
import z from "zod";
import { useTransition } from "react";
import { checkoutOtpVerificationAction } from "@/actions/checkoutOtpVerificationAction";
import ResendOtpTimer from "@/components/modals/_components/ResendOtpTimer";
import { checkoutOtpResendAction } from "@/actions/checkoutOtpResendAction";
import { useToken } from "@/store/useTokenStore";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import BodyText from "@/components/BodyText";
import Link from "next/link";
import { removeLocalStorageKey } from "@/hooks/useRemoveLocalStgKey";
import { useOrderStore } from "@/hooks/useOrderStore";
import { ModalCloseIcon } from "@/components/icons/icon-library";
import { trackBeginCheckout } from "@/lib/trackEvent/trackBeginCheckout";
import { trackPurchase } from "@/lib/trackEvent/trackPurchase";
import { api } from "@/lib/axios";
import BlankAddress from "./BlankAddress";
import { encryptId } from "@/lib/encryption";

export default function CheckoutMain() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { user } = useAuthStore();
  const { clearCart } = useCartStore();
  const showHeader = useShowHeader((state) => state.showHeader);
  const [addAddressModal, setAddAddressModal] = useState(false);
  const { guestId, clearGuestId } = useGuestUserId();
  const { addOrder } = useOrderStore();
  const userId = user?.id ? user.id : guestId!;
  // State
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(
    null,
  );
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [editingAddress, setEditingAddress] = useState<any>(null);
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<
    "cash_payment" | "sslcommerz_payment" | "bkash" | ""
  >("");
  const [selectedDeliveryMethod, setSelectedDeliveryMethod] =
    useState<string>("");
  const [isProcessingOrder, setIsProcessingOrder] = useState(false);
  const [isAccepted, setIsAccepted] = useState(false);
  const [note, setNote] = useState("");
  const [hasTracked, setHasTracked] = useState(false);

  // Queries
  const { data: addressesData } = useUserShippingAddresses(userId!);
  const { data: cartWithDeliveryData } = useCartWithDelivery(
    userId!,
    selectedAddressId!,
    !!userId && !!selectedAddressId,
  );
  const { data: basicCartData } = useCart(userId!, !!user?.id || !!guestId);
  const { data: cartSummary } = useCartSummary(userId!);

  // react query mutation
  const updateAddressInCartMutation = useUpdateAddressInCart();
  const createOrderMutation = useCreateOrder();
  const storeDeliveryInfoMutation = useStoreDeliveryInfo();
  const storeValidateDataMutation = useStoreValidateData();
  const beginPaymentMutation = useSSLBeginPayment();
  const beginbKashPaymentMutation = usebKashPayment();

  // get cart data
  const { data: cartData, isLoading: isCartLoading } = useQuery({
    queryKey: ["get_cart", userId],
    queryFn: async () => {
      const { data } = await api.post(`/carts/${userId}`, {});
      return data as Cart[];
    },
    enabled: !!userId,
  });
  // const changePaymentStatusMutation = usePaymentStatusChange();
  // local state
  const addresses = addressesData?.data || [];
  const [selectedAddress, setSelectedAddress] = useState<ShippingAddress>(
    addresses[0],
  );
  const [openOtpModal, setOpenOtpModal] = useState(false);
  const [isPending, startTransition] = useTransition();
  const [otpPromiseResolver, setOtpPromiseResolver] = useState<
    ((value: OtpVerificationProps) => void) | null
  >(null);
  const { accessToken } = useToken();
  const form = useForm<z.infer<typeof verifyOTPSchema>>({
    resolver: zodResolver(verifyOTPSchema),
    defaultValues: { otp: "" },
  });

  // Get cart items from server data (with delivery info or basic cart)
  const serverCartItems = (() => {
    // First try cartWithDeliveryData
    if (cartWithDeliveryData && Array.isArray(cartWithDeliveryData)) {
      return cartWithDeliveryData.reduce((total, cart) => {
        return total + (cart.cart_items?.length || 0);
      }, 0);
    }

    if (basicCartData && Array.isArray(basicCartData)) {
      // Fallback to basicCartData
      return basicCartData.reduce((total, cart) => {
        return total + (cart.cart_items?.length || 0);
      }, 0);
    }

    return 0;
  })();

  // Auto-select first address if available
  useEffect(() => {
    if (addresses.length > 0) {
      const firstAddress = addresses[0];
      setSelectedAddressId(firstAddress.id);
      setSelectedAddress(firstAddress);
    }
  }, [addresses]);

  // Auto-select first delivery method if available
  useEffect(() => {
    if (
      cartWithDeliveryData &&
      cartWithDeliveryData.length > 0 &&
      !selectedDeliveryMethod
    ) {
      const firstMethod =
        cartWithDeliveryData[0]?.shipping_type?.[0]?.methods?.[0];
      if (firstMethod) {
        handleDeliveryMethodChange(firstMethod.value);
      }
    }
  }, [cartWithDeliveryData, selectedDeliveryMethod]);

  // Update cart address when address is selected
  useEffect(() => {
    if (userId && selectedAddressId) {
      updateAddressInCartMutation.mutate({
        user_id: userId,
        address_id: selectedAddressId,
      });
    }
  }, [userId, selectedAddressId]);

  // begin checkout  start end
  useEffect(() => {
    if (!cartSummary || !cartData?.length || hasTracked) return;

    const cartItems = cartData[0]?.cart_items || [];

    const trackingItems: TrackingItemType[] = cartItems.map((item) => ({
      id: item.id,
      name: item.product_name,
      price: item.price,
      quantity: item.quantity,
    }));

    const triggerBeginCheckout = async () => {
      try {
        if (!trackingItems.length) return;
        await trackBeginCheckout({
          // total: cartSummary?.grand_total_value || 0,
          total:
            parseFloat(cartSummary?.grand_total_value.replace(",", "")) ?? 0,
          currency: "BDT",
          items: trackingItems,
        });
        setHasTracked(true);
      } catch (error) {
        return;
      }
    };

    triggerBeginCheckout();
  }, [cartSummary, cartData]);

  // begin checkout event end

  const handleAddressSelect = (addressId: number) => {
    setSelectedAddressId(addressId);
    const foundAddress = addresses.find(
      (address: ShippingAddress) => address.id === addressId,
    );
    setSelectedAddress(foundAddress);
  };

  const handleAddNewAddress = () => {
    setEditingAddress(null);
    setShowAddressForm(true);
  };

  const handleEditAddress = (address: any) => {
    setAddAddressModal(true);
    setEditingAddress(address);
    setShowAddressForm(true);
  };

  const handleAddressFormSuccess = () => {
    setShowAddressForm(false);
    setEditingAddress(null);
  };

  const handleAddressFormCancel = () => {
    setShowAddressForm(false);
    setEditingAddress(null);
  };

  // handle accept terms
  const handleChange = (checked: boolean) => {
    setIsAccepted(checked);
  };

  // handle otp modal close click
  const handleOtpModalClose = (close: boolean) => {
    setOpenOtpModal(close);
    setIsProcessingOrder(false);
  };

  // handle delivery method change
  const handleDeliveryMethodChange = (method: string) => {
    setSelectedDeliveryMethod(method);
    // Store delivery info when method changes
    if (userId) {
      const deliveryData = {
        user_id: userId,
        shipping_type_9: "home_delivery",
        pickup_point_id_9: "",
        shipping_method_9: method,
      };

      storeDeliveryInfoMutation.mutate(deliveryData);
    }
  };

  // handle otp submit
  const onSubmitOtp = async (data: z.infer<typeof verifyOTPSchema>) => {
    if (!selectedAddress.phone) {
      toast.error("Please add valid phone number");
      return null;
    }

    try {
      const res = await checkoutOtpVerificationAction({
        phone: selectedAddress.phone,
        verification_code: data.otp,
      });

      if (res?.success && res.data) {
        setOpenOtpModal(false);
        form.reset();
        const otpResponse: OtpVerificationProps = {
          success: res.success,
          message: res.message,
          data: res.data as any as OTPVerificationResponse,
        };
        if (otpPromiseResolver) otpPromiseResolver(otpResponse);
        return otpResponse;
      } else {
        toast.error(res?.message || "OTP verification failed");
        return null;
      }
    } catch (error: any) {
      toast.error(error?.message || "Something went wrong");
      return null;
    }
  };

  //   HANDLE  otp resend
  const handleResendOTP = () => {
    startTransition(async () => {
      const res = await checkoutOtpResendAction({
        phone: selectedAddress?.phone,
      });
      if (res?.data) {
        if (!res.data.result) {
          toast.error(res.data.message);
        } else {
          toast.success(res.data.message);
        }
      }
    });
  };

  // handle otp response
  const getOtpResponse = async (data: z.infer<typeof verifyOTPSchema>) => {
    if (!selectedAddress.phone) {
      toast.error("Please add valid phone number");
      return;
    }
    const response = await onSubmitOtp(data);
    return response;
  };

  // handle guest order
  const handleGuestUserToUser =
    async (): Promise<OtpVerificationProps | null> => {
      if (!guestId) return null;

      const validateData = await storeValidateDataMutation.mutateAsync({
        temp_user_id: guestId,
        name: selectedAddress.name,
        phone: selectedAddress.phone,
        address: selectedAddress.address,
      });

      if (!validateData?.success) return null;

      // OTP verification temporarily disabled — use response directly.
      // To re-enable: uncomment lines below AND restore send_verification_code in backend.
      /*
      toast.success(
        validateData.message ||
          "A verification code has been sent to your phone.",
      );
      setOpenOtpModal(true);

      const otpResponse: OtpVerificationProps = await new Promise((resolve) => {
        setOtpPromiseResolver(() => resolve);
      });

      return otpResponse;
      */

      // Bypass: return response directly (backend now returns user_id + access_token)
      if (validateData?.user_id) {
        return {
          success: true,
          message: validateData.message || "Order placed successfully",
          data: {
            user_id: validateData.user_id,
            access_token: validateData.access_token,
          } as any as OTPVerificationResponse,
        };
      }

      return null;
    };

  const trackPurchseEvent = async (response: OrderResponse) => {
    if (!cartSummary || !cartWithDeliveryData) return;
    // purchase event
    try {
      await trackPurchase({
        id: response?.order_id,
        total: parseFloat(cartSummary?.grand_total_value.replace(",", "")) ?? 0,
        currency: "BDT",
        items: cartWithDeliveryData?.[0]?.cart_items.map((item: CartItems) => ({
          id: item.product_id,
          name: item.product_name,
          price: Number(item.price),
          quantity: Number(item.quantity),
        })),
      });
    } catch (error) {
      return;
    }
  };

  // handle create order
  const handleCreateOrder = async ({
    order_userId,
    paymentMethod,
    token,
  }: {
    order_userId: number;
    paymentMethod: "cash_payment" | "sslcommerz_payment" | "bkash";
    token: string;
  }) => {
    const orderData = {
      user_id: order_userId.toString(),
      payment_type: paymentMethod,
      order_source: "website" as const,
      note: note,
    };
    try {
      const response = await createOrderMutation.mutateAsync({
        data: orderData,
        token,
      });
      if (response.result === true) {
        await trackPurchseEvent(response);
        return response;
      } else {
        toast.error(response.message || "Something went wrong!");
      }
    } catch (error) {
      toast.error("Something went wrong!");
      return;
    }
  };

  // handle  order success remove fresh all
  const onOrderSucessClearAll = async (response: OrderResponse) => {
    queryClient.invalidateQueries({ queryKey: ["get_cart", userId] });
    queryClient.invalidateQueries({ queryKey: ["get_subtotal", userId] });
    queryClient.invalidateQueries({ queryKey: checkoutKeys.all });
    clearCart();
    if (guestId) {
      clearGuestId();
      removeLocalStorageKey("guestUserId");
    }
    addOrder(response.order_id.toString());
  };
  // handle cash on delivery
  const handleCashOnDelivery = async (orderResponse: OrderResponse) => {
    try {
      if (orderResponse.result === true) {
        await onOrderSucessClearAll(orderResponse);
        toast.success("Order placed successfully!");
        router.push("/order-success/" + encryptId(orderResponse.order_id));
      }
    } catch (error) {
      return;
    }
  };

  // handle ssl commerz
  const handleOrderBySSLCommerz = async ({
    orderResponse,
    currUserId,
  }: {
    orderResponse: OrderResponse;
    currUserId: number;
  }) => {
    try {
      const response = await beginPaymentMutation.mutateAsync({
        params: {
          payment_type: "cart_payment",
          combined_order_id: orderResponse.combined_order_id,
          // amount: cartSummary.grand_total_value,
          amount:
            parseFloat(cartSummary?.grand_total_value.replace(",", "")) ?? 0,
          user_id: currUserId,
        },
      });

      if (response?.result === true) {
        await onOrderSucessClearAll(orderResponse);
        router.push(response.url);
      } else {
        await onOrderSucessClearAll(orderResponse);
        router.push(`/order-success/${encryptId(orderResponse.order_id)}`);
      }
    } catch (error: any) {
      toast.error(
        error?.response?.data?.message || "Payment initialization failed",
      );
    }
  };

  const handleOrderByBkashPayment = async ({
    orderResponse,
    currUserId,
  }: {
    orderResponse: OrderResponse;
    currUserId: number;
  }) => {
    try {
      const response = await beginbKashPaymentMutation.mutateAsync({
        params: {
          payment_type: "cart_payment",
          combined_order_id: orderResponse.combined_order_id,
          // amount: cartSummary.grand_total_value,
          amount:
            parseFloat(cartSummary?.grand_total_value.replace(",", "")) ?? 0,
          user_id: currUserId,
        },
      });

      if (response?.result === true) {
        await onOrderSucessClearAll(orderResponse);
        router.push(response.url);
      } else {
        await onOrderSucessClearAll(orderResponse);
        router.push(`/order-success/${encryptId(orderResponse.order_id)}`);
      }
    } catch (error: any) {
      toast.error(
        error?.response?.data?.message || "Payment initialization failed",
      );
    }
  };
  // handle place order
  const handlePlaceOrder = async () => {
    setIsProcessingOrder(true);
    try {
      if (!selectedPaymentMethod)
        return toast.error("Please select a payment method");
      if (!addresses && !selectedAddressId)
        return toast.error("Please select an address");
      if (!selectedDeliveryMethod)
        return toast.error("Please select a delivery method");
      if (!serverCartItems || serverCartItems === 0)
        return toast.error("Your cart is empty");

      let currUserId = user?.id;
      let token = accessToken ? accessToken : "";

      // Guest user check
      if (guestId) {
        const guestResponse = await handleGuestUserToUser();
        currUserId = guestResponse?.data?.user_id;
        token = guestResponse?.data.access_token!;
      }
      if (!currUserId) return toast.error("Somthing went wrong");

      // Create order
      const orderResponse = await handleCreateOrder({
        order_userId: currUserId,
        paymentMethod: selectedPaymentMethod,
        token,
      });

      if (!orderResponse?.result) {
        return toast.error("Somthing went wrong");
      }

      // Payment flow
      switch (selectedPaymentMethod) {
        case "cash_payment":
          await handleCashOnDelivery(orderResponse);
          break;

        case "sslcommerz_payment":
          await handleOrderBySSLCommerz({ orderResponse, currUserId });
          break;

        case "bkash":
          await handleOrderByBkashPayment({ orderResponse, currUserId });
          break;

        default:
          toast.error("Invalid payment method");
      }
    } catch (error: any) {
      toast.error(error?.message || "Something went wrong!");
    } finally {
      setIsProcessingOrder(false);
    }
  };

  const canPlaceOrder =
    userId &&
    isAccepted &&
    addresses.length > 0 &&
    selectedAddressId &&
    serverCartItems > 0 &&
    selectedDeliveryMethod &&
    selectedPaymentMethod;

  return (
    <Container className="py-8 md:pt-12 md:pb-16">
      <div className="flex flex-col justify-between gap-6 lg:gap-12 md:flex-row">
        <div className="w-full max-w-[650px] space-y-6">
          <div className="border-none bg-transparent shadow-none">
            <div className="space-y-5 p-0 md:space-y-8">
              {/* otp verification */}
              <Dialog
                open={openOtpModal}
                onOpenChange={() => setOpenOtpModal(false)}
              >
                <DialogContent
                  className="rounded-2xl sm:max-w-md [&>button]:hidden"
                  onInteractOutside={(e) => e.preventDefault()}
                  onEscapeKeyDown={(e) => e.preventDefault()}
                >
                  {/* Close button */}
                  <div
                    className="absolute top-5 right-5 inline-block cursor-pointer"
                    onClick={() => handleOtpModalClose(false)}
                  >
                    <ModalCloseIcon className="fill-site-gray-300 transition-colors hover:fill-red-400" />
                  </div>
                  <DialogHeader>
                    <DialogTitle className="text-center text-2xl">
                      Verify your OTP
                    </DialogTitle>
                    <DialogDescription className="text-center text-gray-500">
                      Enter the 6-digit code sent to your phone number{" "}
                      {selectedAddress?.phone && (
                        <span className="font-semibold">
                          {selectedAddress?.phone}
                        </span>
                      )}
                    </DialogDescription>
                  </DialogHeader>

                  <Form {...form}>
                    <form
                      onSubmit={form.handleSubmit(getOtpResponse)}
                      className="mt-6 space-y-6"
                    >
                      <FormField
                        control={form.control}
                        name="otp"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel hidden>Enter OTP</FormLabel>
                            <FormControl>
                              <InputOTP maxLength={6} {...field}>
                                <InputOTPGroup className="flex justify-center gap-3">
                                  {Array.from({ length: 6 }).map((_, i) => (
                                    <InputOTPSlot
                                      key={i}
                                      index={i}
                                      className="!ring-site-primary/40 data-[active=true]:border-site-primary"
                                    />
                                  ))}
                                </InputOTPGroup>
                              </InputOTP>
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <DialogFooter>
                        <button
                          disabled={isPending}
                          type="submit"
                          className={cn(
                            "rounded-full bg-site-secondary-500 hover:bg-site-secondary-600 w-full cursor-pointer  px-6 py-3 font-medium text-white transition-colors",
                            isPending && "cursor-not-allowed opacity-70",
                          )}
                        >
                          {isPending ? "Verifying..." : "Verify"}
                        </button>
                      </DialogFooter>
                    </form>
                  </Form>
                  <div className="flex items-center justify-center">
                    <ResendOtpTimer duration={40} onResend={handleResendOTP} />
                  </div>
                </DialogContent>
              </Dialog>
              {/* end otp verification */}

              {/* Address Section */}
              <div>
                <Dialog
                  open={addAddressModal}
                  onOpenChange={setAddAddressModal}
                >
                  <div className="mb-5 flex items-center justify-between">
                    <h4  className="lg:text-2xl text-xl font-bold">
                      <span className="hidden lg:block">Select Delivery Address</span>
                       <span className="lg:hidden">Delivery Address</span>
                    </h4>

                    <DialogTrigger asChild>
                        {addresses.length > 0 && 
                            <button
                                type="button"
                                onClick={handleAddNewAddress}
                                className="font-bold  flex cursor-pointer items-center gap-1  rounded-[10px]  px-2 py-1.5 text-base text-site-primary-600 hover:text-site-primary-500 transition duration-300 lg:gap-2 md:px-4 md:py-2 md:text-lg leading-5"
                            >
                                <span className="h-5 w-5 border-[1.5px] border-site-primary-600 flex items-center justify-center p-[2px] rounded-full font-bold">
                                <Plus className="w-4 h-4 font-bold" />
                                </span>
                                Add New Address
                            </button>

                        }
                    </DialogTrigger>
                  </div>

                  {/* address modal  */}
                  <DialogContent className="bg-site-gray-50 max-w-[95%] p-4 md:max-w-[500px] md:p-4">
                    <DialogTitle className="hidden">form</DialogTitle>
                    <AddressForm
                      userId={user?.id || guestId!}
                      guestId={guestId}
                      existingAddress={editingAddress}
                      onSuccess={handleAddressFormSuccess}
                      onCancel={handleAddressFormCancel}
                      setAddAddressModal={setAddAddressModal}
                    />
                  </DialogContent>

                  {addresses.length > 0 ? (
                    <AddressSelector
                      userId={user?.id ? user.id : guestId!}
                      selectedAddressId={selectedAddressId || undefined}
                      onAddressSelect={handleAddressSelect}
                      onAddNewAddress={handleAddNewAddress}
                      handleEditAddress={handleEditAddress}
                    />
                  ) : (
                    <BlankAddress
                        handleAddNewAddress={handleAddNewAddress}
                        addresses={addresses}
                    />
                  )}
                </Dialog>
              </div>
              {/* end address modal */}

              {/* Delivery Methods */}
              {addresses.length > 0 && (
                <DeliveryMethods
                  cartWithDeliveryData={cartWithDeliveryData}
                  selectedMethod={selectedDeliveryMethod}
                  onMethodChange={handleDeliveryMethodChange}
                  isLoading={updateAddressInCartMutation.isPending}
                />
              )}

              {/* Payment Methods */}
              <PaymentMethods
                selectedMethod={selectedPaymentMethod}
                onMethodChange={setSelectedPaymentMethod}
              />

              {/* note */}
              <div className="space-y-4 pt-4 bg-site-gray-50 rounded-2xl p-4">
                <Heading variant="h5" className="text-lg font-bold">
                  Additional Note
                </Heading>

                <BodyText
                  variant="two"
                  className="text-site-gray-400  font-bold mb-4"
                >
                    Add any special instruction for your order. (Optional)                
                </BodyText>
                
                <textarea
                  onChange={(e) => setNote(e.target.value)}
                  className="border-site-gray-100 h-[120px] w-full resize-none rounded-md border bg-white p-2 shadow-[5px] focus:outline-none"
                  placeholder="Write your note here..."
                />

              </div>
            </div>
          </div>
        </div>

        {/* Order Summary */}
        <div className="w-full max-w-[450px]">
          <div
            className={cn(
              "sticky top-4 mt-4 transition-all duration-400 ease-in-out md:mt-0",
              showHeader && "top-[140px]",
            )}
          ></div>
          <div className="">
            <OrderSummary
              cartProducts={[]} // We'll use server data in OrderSummary
              cartSummary={cartSummary}
              cartWithDeliveryData={cartWithDeliveryData || basicCartData}
              isLoading={
                updateAddressInCartMutation.isPending ||
                storeDeliveryInfoMutation.isPending
              }
              userId={userId}
            />
            <div className="!bg-[#FAF5FF]  border-site-gray-100 hidden flex-col rounded-br-md rounded-bl-md border border-t-0 p-3 pt-0 md:flex md:p-6">
              <div className="flex items-center gap-2">
                <Checkbox
                  id="accept_terms_conditon"
                  className="h-5 w-5"
                  checked={isAccepted}
                  onCheckedChange={handleChange}
                />
                <Label
                  className="cursor-pointer"
                  htmlFor="accept_terms_conditon"
                >
                  <BodyText className="text-[#243752]" variant="two">
                    I accept the{" "}
                    <Link
                      className="underline"
                      prefetch={false}
                      target="_blank"
                      href="/page/terms"
                    >
                      Terms & Conditions
                    </Link>{" "}
                    &{" "}
                    <Link
                      className="underline"
                      prefetch={false}
                      target="_blank"
                      href="/page/privacypolicy"
                    >
                      Privacy Policy
                    </Link>
                  </BodyText>
                </Label>
              </div>
              <button
                type="submit"
                onClick={handlePlaceOrder}
                disabled={!canPlaceOrder || isProcessingOrder}
                className={`bg-site-secondary-500 rounded-full hover:bg-site-secondary-600 mt-6 flex max-h-[52px] w-full cursor-pointer items-center justify-center font-bold px-10 py-2 text-center text-base  text-white transition-colors duration-300 ease-in-out md:py-4 ${!canPlaceOrder ? "cursor-not-allowed opacity-50" : ""}`}
              >
                {isProcessingOrder
                  ? "Processing..."
                  : selectedPaymentMethod === "cash_payment"
                    ? "Confirm Order"
                    : "Confirm Order"}
              </button>
            </div>
          </div>
        </div>
        {/* Place Order Button */}
      </div>
      <div className="mt-4 flex flex-col pt-8 md:hidden">
        <div className="flex gap-2 md:items-center">
          <Checkbox
            id="accept_terms_conditon"
            className="h-5 w-5"
            checked={isAccepted}
            onCheckedChange={handleChange}
          />
          <Label className="cursor-pointer" htmlFor="accept_terms_conditon">
            <BodyText className="text-[#243752]" variant="two">
              I accept the{" "}
              <Link
                className="underline"
                prefetch={false}
                target="_blank"
                href="/page/terms"
              >
                Terms & Conditions
              </Link>{" "}
              &{" "}
              <Link
                className="underline"
                prefetch={false}
                target="_blank"
                href="/page/privacypolicy"
              >
                Privacy Policy
              </Link>
            </BodyText>
          </Label>
        </div>
        <button
          type="submit"
          onClick={handlePlaceOrder}
          disabled={!canPlaceOrder || isProcessingOrder}
          className={`bg-site-secondary-500 hover:bg-site-gray-900 mt-6 flex max-h-[52px] w-full cursor-pointer items-center justify-center rounded-[10px] px-10 py-2 text-center text-base font-semibold text-white transition-colors duration-300 ease-in-out md:py-4 ${!canPlaceOrder ? "cursor-not-allowed opacity-50" : ""}`}
        >
          {isProcessingOrder
            ? "Processing..."
            : selectedPaymentMethod === "cash_payment"
              ? "Confirm Order"
              : "Confirm Order"}
        </button>
      </div>
    </Container>
  );
}
