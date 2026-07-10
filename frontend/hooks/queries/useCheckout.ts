import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import {
  checkoutApi,
  ShippingAddress,
  ShippingCostRequest,
  OrderRequest,
  UpdateCartAddressRequest,
} from "@/lib/api/checkout";
import toast from "react-hot-toast";

// Query Keys
export const checkoutKeys = {
  all: ["checkout"] as const,
  addresses: (userId: string | number) =>
    [...checkoutKeys.all, "addresses", userId] as const,
  countries: () => [...checkoutKeys.all, "countries"] as const,
  states: (countryId: number) =>
    [...checkoutKeys.all, "states", countryId] as const,
  cities: (stateId: number) =>
    [...checkoutKeys.all, "cities", stateId] as const,
  areas: (cityId: number) => [...checkoutKeys.all, "areas", cityId] as const,
  paymentTypes: () => [...checkoutKeys.all, "paymentTypes"] as const,
  cartWithDelivery: (userId: string | number, addressId: number) =>
    [...checkoutKeys.all, "cartWithDelivery", userId, addressId] as const,
  cartSummary: (userId: string | number) =>
    [...checkoutKeys.all, "cartSummary"] as const,
  shippingCost: (data: ShippingCostRequest) =>
    [...checkoutKeys.all, "shippingCost", data] as const,
};

// Hooks for fetching data
export const useUserShippingAddresses = (
  userId: string | number,
  enabled = true,
) => {
  return useQuery({
    queryKey: checkoutKeys.addresses(userId),
    queryFn: () => checkoutApi.getUserShippingAddresses(userId),
    enabled: enabled && !!userId,
  });
};

export const useCountries = () => {
  return useQuery({
    queryKey: checkoutKeys.countries(),
    queryFn: checkoutApi.getCountries,
    staleTime: 1000 * 60 * 60, // 1 hour
  });
};

export const useStatesByCountry = (countryId: number, enabled = true) => {
  return useQuery({
    queryKey: checkoutKeys.states(countryId),
    queryFn: () => checkoutApi.getStatesByCountry(countryId),
    enabled: enabled && !!countryId,
    staleTime: 1000 * 60 * 60, // 1 hour
  });
};

export const useCitiesByState = (stateId: number, enabled = true) => {
  return useQuery({
    queryKey: checkoutKeys.cities(stateId),
    queryFn: () => checkoutApi.getCitiesByState(stateId),
    enabled: enabled && !!stateId,
    staleTime: 1000 * 60 * 30, // 30 minutes
  });
};

export const useAreasByCity = (cityId: number, enabled = true) => {
  return useQuery({
    queryKey: checkoutKeys.areas(cityId),
    queryFn: () => checkoutApi.getAreasByCity(cityId),
    enabled: enabled && !!cityId,
    staleTime: 1000 * 60 * 30, // 30 minutes
  });
};

export const usePaymentTypes = () => {
  return useQuery({
    queryKey: checkoutKeys.paymentTypes(),
    queryFn: () => checkoutApi.getPaymentTypes("", "both"),
    staleTime: 1000 * 60 * 60, // 1 hour
  });
};

export const useCartWithDelivery = (
  userId: number | string,
  addressId: number,
  enabled = true,
) => {
  return useQuery({
    queryKey: checkoutKeys.cartWithDelivery(userId, addressId),
    queryFn: () => checkoutApi.getCartWithDelivery(userId, addressId),
    enabled: enabled && !!userId && !!addressId,
  });
};

export const useCartSummary = (userId: string | number, enabled = true) => {
  return useQuery({
    queryKey: checkoutKeys.cartSummary(userId),
    queryFn: () => checkoutApi.getCartSummary(userId),
    enabled: enabled && !!userId,
  });
};

export const useCart = (userId: string | number, enabled = true) => {
  return useQuery({
    queryKey: [...checkoutKeys.all, "get_cart", userId] as const,
    queryFn: () => checkoutApi.getCart(userId),
    enabled: enabled && !!userId,
  });
};

export const useShippingCost = (data: ShippingCostRequest, enabled = true) => {
  return useQuery({
    queryKey: checkoutKeys.shippingCost(data),
    queryFn: () => checkoutApi.getShippingCost(data),
    enabled: enabled && !!data.user_id && !!data.city_name,
  });
};

// Mutation hooks
export const useCreateShippingAddress = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: checkoutApi.createShippingAddress,
    onSuccess: (data, variables) => {
      if (data.result) {
        toast.success("Address created successfully");
        queryClient.invalidateQueries({
          queryKey: checkoutKeys.addresses(variables.user_id),
        });
      } else {
        toast.error(data?.message || "Failed to create address");
      }
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || "Failed to create address");
    },
  });
};

export const useUpdateShippingAddress = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: checkoutApi.updateShippingAddress,
    onSuccess: (data, variables) => {
      toast.success("Address updated successfully");
      queryClient.invalidateQueries({
        queryKey: checkoutKeys.addresses(variables.user_id),
      });
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || "Failed to update address");
    },
  });
};

export const useDeleteShippingAddress = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: checkoutApi.deleteShippingAddress,
    onSuccess: () => {
      toast.success("Address deleted successfully");
      queryClient.invalidateQueries({
        queryKey: checkoutKeys.all,
      });
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || "Failed to delete address");
    },
  });
};

export const useUpdateAddressInCart = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: checkoutApi.updateAddressInCart,
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: checkoutKeys.all,
      });
    },
    onError: (error: any) => {
      toast.error(
        error.response?.data?.message || "Failed to update cart address",
      );
    },
  });
};

export const useCreateOrder = () => {
  return useMutation({
    mutationFn: checkoutApi.createOrder,
    onError: (error: any) => {
      toast.error(error.response?.data?.message || "Failed to create order");
    },
  });
};

export const usePayWithCOD = () => {
  return useMutation({
    mutationFn: checkoutApi.payWithCOD,
    onError: (error: any) => {
      toast.error(
        error.response?.data?.message || "Failed to process COD payment",
      );
    },
  });
};

export const useStoreDeliveryInfo = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: checkoutApi.storeDeliveryInfo,
    onSuccess: () => {
      // Invalidate cart queries to refresh data
      queryClient.invalidateQueries({
        queryKey: checkoutKeys.all,
      });
    },
    onError: (error: any) => {
      toast.error(
        error.response?.data?.message || "Failed to save delivery info",
      );
    },
  });
};

export const useStoreValidateData = () => {
  return useMutation({
    mutationFn: checkoutApi.storeValidateData,
    onError: (error: any) => {
      toast.error(
        error.response?.data?.message || "Please add shipping address",
      );
    },
  });
};

export const useStoreVerification = () => {
  return useMutation({
    mutationFn: checkoutApi.storeOtpVerification,
    onError: (error: any) => {
      toast.error(error.response?.data?.message || "Please Enter Otp");
    },
  });
};

// ssl commerz begin api
export const useSSLBeginPayment = () => {
  return useMutation<ApiResponse, any, { params: BeginPaymentParams }>({
    mutationFn: async ({ params }) => {
      const response = await checkoutApi.beginSSLPayment(params);
      return response;
    },
    onError: (error: any) => {
      toast.error(
        error?.response?.data?.message || "Failed to initialize payment",
      );
    },
  });
};

// bkash payment
export const usebKashPayment = () => {
  return useMutation<ApiResponse, any, { params: BeginPaymentParams }>({
    mutationFn: async ({ params }) => {
      const response = await checkoutApi.beginbKashPayment(params);
      return response;
    },
    onError: (error: any) => {
      toast.error(
        error?.response?.data?.message || "Failed to initialize payment",
      );
    },
  });
};

// payment status change
export const usePaymentStatusChange = () => {
  return useMutation({
    mutationFn: checkoutApi.changePaymentStatus,
  });
};

// get assign
export const useAssignedCoupons = (userId: number | string, token: string) => {
  return useQuery({
    queryKey: ["assigned-coupons", userId],
    queryFn: () => checkoutApi.getAssignedCoupons({ user_id: userId! }, token),
    enabled: !!userId && !!token,
    staleTime: 1000 * 60,
  });
};
