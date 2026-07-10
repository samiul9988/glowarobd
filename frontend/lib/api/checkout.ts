import { getAccessToken } from "@/lib/getAccessToken";
import { api } from "../axios";

// Types
export type PaymentType = "cash_payment" | "sslcommerz_payment" | "bkash";

export interface ShippingAddress {
  id?: number;
  user_id: string | number;
  name?: string;
  address: string;
  country_id: number;
  state_id: number;
  city_id: number;
  area_id: number;
  country_name?: string;
  state_name?: string;
  city_name?: string;
  area_name?: string;
  postal_code?: string;
  phone: string;
  address_type?: "Home" | "Office" | "Others";
  type?: string; // API uses 'type' instead of 'address_type'
  set_default?: number;
  location_available?: boolean;
  lat?: number;
  lang?: number;
}

export interface ShippingCostRequest {
  owner_id: number;
  user_id: string | number;
  city_name: string;
}

export interface OrderRequest {
  user_id: string;
  payment_type: "cash_payment" | "sslcommerz_payment" | "bkash" | "nagad";
  order_source: "website" | "IOS" | "Android";
}

export interface UpdateCartAddressRequest {
  user_id: string | number;
  address_id: number;
}

export interface CartItem {
  id: number;
  owner_id: number;
  user_id: string | number;
  product_id: number;
  product_name: string;
  product_thumbnail_image: string;
  variation: any;
  price: number;
  base_price: number;
  currency_symbol: string;
  tax: number;
  shipping_cost: number;
  shipping_type: string;
  shipping_method: any;
  quantity: number;
  lower_limit: number;
  upper_limit: number;
  min_order_amount?: number;
}

export interface ShippingMethod {
  name: string;
  value: string;
  method_name: string;
  method_price: string;
  method_logo: string;
}

export interface ShippingType {
  name: string;
  value: string;
  methods: ShippingMethod[];
  pickUpPoints: any[];
}

export interface CartWithDelivery {
  name: string;
  owner_id: number;
  cart_items: CartItem[];
  shipping_type: ShippingType[];
}

// Helper function to get HTTP api with token
// const getHttpClient = async () => {
//   if (typeof window === "undefined") {
//     // Server-side: get token from cookies
//     const token = await getAccessToken();
//     return createServerHttpClient(token || undefined);
//   }
//   // Client-side: use singleton with automatic token handling
//   return httpClient;
// };

// API Functions
export const checkoutApi = {
  // Get shipping cost
  getShippingCost: async (data: ShippingCostRequest) => {
    const response = await api.post("/shipping_cost", data);
    return response.data;
  },

  // Get user shipping addresses
  getUserShippingAddresses: async (userId: string | number) => {
    const response = await api.get(`/user/shipping/address/${userId}`);
    return response.data;
  },

  // Create shipping address
  createShippingAddress: async (data: ShippingAddress) => {
    const response = await api.post("/user/shipping/create", data);
    return response.data;
  },

  // Update shipping address
  updateShippingAddress: async (
    data: ShippingAddress & { id: string | number },
  ) => {
    const response = await api.post("/user/shipping/update", data);
    return response.data;
  },

  // Delete shipping address
  deleteShippingAddress: async (addressId: number) => {
    const response = await api.get(`/user/shipping/delete/${addressId}`);
    return response.data;
  },

  // Get cities by state
  getCitiesByState: async (stateId: number, name?: string) => {
    const params = name ? `?name=${name}` : "";
    const response = await api.get(`/cities-by-state/${stateId}${params}`);
    return response.data;
  },

  // Get states by country
  getStatesByCountry: async (countryId: number, name?: string) => {
    const params = name ? `?name=${name}` : "";
    const response = await api.get(
      `/states-by-country/${countryId}${params}`,
    );
    return response.data;
  },

  // Get countries
  getCountries: async () => {
    const response = await api.get("/countries");
    return response.data;
  },

  // Get areas by city
  getAreasByCity: async (cityId: number, name?: string) => {
    const params = name ? `?name=${name}` : "";
    const response = await api.get(`/areas-by-city/${cityId}${params}`);
    return response.data;
  },

  // Update address in cart
  updateAddressInCart: async (data: UpdateCartAddressRequest) => {
    const response = await api.post("/update-address-in-cart", data);
    return response.data;
  },

  // Get payment types
  getPaymentTypes: async (mode?: string, list?: string) => {
    const params = new URLSearchParams();
    if (mode) params.append("mode", mode);
    if (list) params.append("list", list);

    const response = await api.get(`/payment-types?${params.toString()}`);
    return response.data;
  },

  // Create order
  createOrder: async ({
    data,
    token,
  }: {
    data: OrderRequest;
    token?: string;
  }) => {
    const headers: Record<string, string> = {};
    if (token) headers.Authorization = `Bearer ${token}`;
    const response = await api.post("/order/store", data, { headers });
    return response.data;
  },

  // Cash on delivery payment
  payWithCOD: async (data: {
    user_id: string;
    payment_type: string;
    order_source: string;
  }) => {
    const response = await api.post("/payments/pay/cod", data);
    return response.data;
  },

  // Get cart with delivery info
  getCartWithDelivery: async (userId: string | number, addressId: number) => {
    const response = await api.post(
      `/cartswithdelivery/${userId}/${addressId}`,
    );
    return response.data;
  },

  getCartSummary: async (userId: string | number) => {
    const response = await api.get(`/cart-summary/${userId}`);
    return response.data;
  },

  // Get basic cart info without address
  getCart: async (userId: string | number) => {
    const response = await api.post(`/carts/${userId}`);
    return response.data;
  },

  // Store delivery info
  storeDeliveryInfo: async (data: {
    user_id: number | string;
    shipping_type_9?: string;
    pickup_point_id_9?: string;
    shipping_method_9?: string;
  }) => {
    const response = await api.post("/cart/store_delivery_info", data);
    return response.data;
  },

  // validate data
  storeValidateData: async (data: {
    temp_user_id: string | null;
    name?: string;
    phone?: string;
    address: string;
  }) => {
    const response = await api.post("/validate-data", data);
    return response.data;
  },

  //otp verification
  storeOtpVerification: async (data: {
    phone: string;
    verification_code?: string;
  }) => {
    const response = await api.post("/verify-phone", data);
    return response.data;
  },

  // ssl commaz begin
  beginSSLPayment: async (params: {
    payment_type: string;
    combined_order_id: number | string;
    amount: number;
    user_id: number;
  }) => {
    const response = await api.get("/sslcommerz/begin", {
      params,
      headers: {
        source: "web",
      },
    });

    return response.data;
  },

  // bkash payment
  beginbKashPayment: async (params: {
    payment_type: string;
    combined_order_id: number | string;
    amount: number;
    user_id: number;
  }) => {
    const response = await api.get("/bkash/begin", {
      params,
      headers: {
        source: "web",
      },
    });

    return response.data;
  },

  // change payment status
  changePaymentStatus: async (data: {
    order_id: number;
    user_id: number;
    status: string;
  }) => {
    const response = await api.post("/update-payment-status", data);
    return response.data;
  },

  // get assign coupon
  getAssignedCoupons: async (
    data: { user_id: number | string },
    token: string,
  ) => {

    // Add Authorization header if token exists
    const headers: Record<string, string> = {};
    if (token) headers.Authorization = `Bearer ${token}`;

    const response = await api.post("/get-assigned-coupons", data, {
      headers,
    });
    return response.data;
  },
};
