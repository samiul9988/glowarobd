// FAKE API DATA TYPE
interface categoriesType {
  title: string;
  categories: category[];
}

interface Product {
  id: number;
  title: string;
  price: number;
  rating: number;
  discount: number;
  popular: boolean;
  new: boolean;
  image: string;
}

// API DATA TYPE
interface BusinessDataType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

interface ProductType {
  id: number;
  name: string;
  slug: string;
  brand: string;
  current_stock: number;
  discount_type: "percent" | "amount" | string;
  flash_deal: {
    is_flash_deal: number;
    data: string;
  };
  links: {
    details: string;
  };
  main_price: string;
  unit_price: string;
  web_price: string;
  stroked_price: string;
  nonformated_price: number;
  short_description: string;
  description: string;
  min_order_amount: number;
  formatted_base_discounted_price: string;
  save: number;
  rating: number;
  sales: number;
  thumbnail_image: string;
  shipping_discount: {
    amount: number;
    status: boolean;
    min_amount: number;
  };
  is_new: boolean;
  in_stock: boolean;
  is_preorder: boolean;
  has_discount: boolean;
  total_reviews: number;
  formatted_discount: string;
}

interface CategoryType {
  id: number;
  name: string;
  slug: string;
  banner: string;
  icon: string;
  featured_icon: string;
  number_of_children: number;
  links: {
    products: string;
    sub_categories: string;
  };
}

interface NavItemsType {
  id: number;
  label: string;
  url: string;
  icon: string;
  background_color: string;
  background_font_color: string;
  children?: {
    id: number;
    label: string;
    url: string;
    icon: string;
    background_color: string;
    background_font_color: string;
    children?: {
      id: number;
      label: string;
      url: string;
      icon: string;
      background_color: string;
      background_font_color: string;
    }[];
  }[];
}

interface MetaType {
  current_page: number;
  last_page: number;
  per_page: string;
  from: number;
  to: number;
  total: number;
  path: string;
  links?: {
    url: string;
    label: string;
    active: boolean;
  };
}

interface ApiResponseType<T> {
  data: T;
  status: number;
  success: boolean;
  meta?: MetaType;
  title?: string;
  icon?: string;
  brand_name?: string;
  brand_slug?: string;
  icon?: string;
  banner?: string;
}

interface ApiResponseTypeCategory<T> {
  data: T;
  status: number;
  success: boolean;
  meta?: MetaType;
  max_price: number;
  min_price: number;
}

interface BrandType {
  id: number;
  name: string;
  slug: string;
  logo: string;
}

// Product Detail Types
interface ProductPhoto {
  variant: string;
  path: string;
}

interface ProductBrand {
  id: number;
  name: string;
  slug: string;
  logo: string;
}

interface FlashDealData {
  id: number;
  title: string;
  start_date: number;
  end_date: number;
  status: number;
  featured: number;
  background_color: string;
  text_color: string;
  banner: string;
  desktop_banner: string;
  slug: string;
  quantity: number;
  created_at: string;
  updated_at: string;
}

interface ProductFlashDeal {
  is_flash_deal: number;
  data: FlashDealData;
}

interface ProductCategory {
  id: number;
  name: string;
  slug: string;
  logo: string;
}

interface ShippingDiscount {
  amount: number;
  status: boolean;
  min_amount: number;
}

// Custom Fields Types
interface CustomFieldValue {
  title: string;
  description?: string;
  image?: string;
}

interface CustomField {
  banner: string;
  type: "multi_select" | "html_box" | string;
  value: CustomFieldValue[] | string;
}

interface ProductCustomFields {
  key_ingredient?: CustomField;
  ingredients?: CustomField;
  skin_type?: CustomField;
  skin_concern?: CustomField;
  faqs?: CustomField;
  how_to_use?: CustomField;
  highlight?: CustomField;
}

// Review Types
interface ReviewType {
  user_id: number;
  user_name: string;
  hide_username: boolean;
  avatar: string;
  rating: number;
  comment: string | null;
  photos: string[];
  time: string;
}

interface ReviewPaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

interface ReviewPaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

interface ReviewsResponseType {
  data: ReviewType[];
  links: ReviewPaginationLinks;
  meta: ReviewPaginationMeta;
  success: boolean;
  status: number;
}
type ProductChoiceItem = {
  name: string;
  title: string;
  options: string[];
}[];
interface ColorOptions {
  [hexCode: string]: string;
}

interface ProductDetailType {
  id: number;
  slug: string;
  name: string;
  added_by: string;
  seller_id: number;
  shop_id: number;
  shop_name: string;
  shop_logo: string;
  photos: ProductPhoto[];
  thumbnail_image: string;
  tags: string[];
  price_high_low: string;
  choice_options: ProductChoiceItem;
  can_post_review: boolean;
  colors: any[];
  has_discount: boolean;
  discount_type: string;
  stroked_price: string;
  main_price: string;
  calculable_price: number;
  currency_symbol: string;
  current_stock: number;
  unit: string;
  rating: number;
  rating_count: number;
  earn_point: number;
  short_description: string;
  description: string;
  video_link: string;
  video_aspect_ratio: string;
  brand: ProductBrand;
  link: string;
  is_preorder: boolean;
  note: string | null;
  shipping_discount: ShippingDiscount;
  flash_deal: ProductFlashDeal;
  num_of_sale: number;
  category: ProductCategory;
  rating_counts: number[];
  total_reviews: number;
  is_new: boolean;
  custom_fields: ProductCustomFields;
  meta_title: string;
  meta_img: string;
  meta_description: string;
  color_options: ColorOptions;
  sku: number;
}

interface Navigator {
  share?: (data?: ShareData) => Promise<void>;
}
interface SettingsType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

// Cart Types
interface CartItemType {
  id: number;
  owner_id: number;
  user_id: number;
  product_id: number;
  product_name: string;
  min_order_amount: number;
  product_thumbnail_image: string;
  variation: string | null;
  price: number;
  base_price: number;
  currency_symbol: string;
  tax: number;
  shipping_cost: number;
  shipping_type: string;
  shipping_method: string | null;
  quantity: number;
  lower_limit: number;
  upper_limit: number;
  product_slug: string;
}

interface Cart {
  name: string;
  owner_id: number;
  cart_items: CartItemType[];
}

interface CartResponseType {
  result: boolean;
  message: string;
}

interface WishlistResponseType {
  result: boolean;
  message: string;
}

interface CartSummary {
  sub_total: string;
  tax: string;
  shipping_cost: string;
  shipping_discount: string[];
  discount: string;
  grand_total: string;
  grand_total_value: number;
  coupon_code: string | null;
  coupon_applied: boolean;
}

interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
  type: string;
  phone: string;
  avatar_original: string | null;
}

interface WishlistResponse {
  data: {
    id: number;
    product: {
      id: number;
      name: string;
      thumbnail_image: string;
      base_price: string;
      main_price: string;
      rating: number;
      slug: string;
    };
  }[];
  success: boolean;
  status: number;
}

// Your group type
interface UserGroupMessage {
  title: string;
  video_url: string;
  offers: string[];
}

interface Group {
  id: number;
  name: string;
  image: string;
  icon: string;
  min_order_qty: number;
  message: UserGroupMessage;
}

interface GroupApiResponse {
  groups: Group[];
}

// Global User Response Type
interface UserResponse {
  result: boolean;
  id: number;
  name: string;
  email: string;
  avatar: string | null;
  avatar_original: string | null;
  date_of_birth: string | null; // or Date | null if parsed
  gender: "male" | "female" | "other" | string;
  phone: string;
  reward_point: number;

  group: {
    id: number;
    name: string;
    icon: string;
    image: string;
    min_order_qty: number;
    min_order_amount: number;
    discount: number;
    discount_type: "percent" | "flat" | string;
    start_date: number; // timestamp
    end_date: number; // timestamp
    delivery_discount: number;
    delivery_discount_amount: number | null;
    ordering: number;
    message: {
      title: string;
      video_url: string;
      offers: string[];
    };
  };
}

// Purchase History
interface PurchaseHistoryResponse {
  data: PurchaseHistoryItem[];
  links: PaginationLinks;
  meta: PaginationMeta;
  success: boolean;
  status: number;
}

interface PurchaseHistoryItem {
  id: number;
  code: string;
  user_id: number;
  payment_type: string;
  payment_status: "paid" | "unpaid" | "pending" | string;
  payment_status_string: string;
  delivery_status:
    | "pending"
    | "processing"
    | "delivered"
    | "cancelled"
    | string;
  delivery_status_string: string;
  grand_total: string;
  date: string;
  links: {
    details: string;
  };
  reward: RewardInfo;
  payments: Payment[];
  total_amount_paid: string;
}

interface RewardInfo {
  amount: string;
  point: number;
  is_applied: number;
}

interface Payment {
  [key: string]: any;
}

interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  links: PaginationLink[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PurchaseDetailsOrder {
  id: number;
  code: string;
  user_id: number;
  shipping_address: {
    name: string;
    email: string;
    address: string;
    country: string;
    state: string;
    city: string;
    area: string;
    postal_code: string | null;
    phone: string;
  };
  payment_type: string;
  shipping_type: string;
  shipping_type_string: string;
  shipping_method: string;
  payment_status: string;
  payment_status_string: string;
  delivery_status:
    | "pending"
    | "processing"
    | "packaging"
    | "confirmed"
    | "picked_up"
    | "on_the_way"
    | "delivered"
    | "cancelled";
  delivery_status_string: string;
  grand_total: string;
  coupon_discount: string;
  shipping_cost: string;
  subtotal: string;
  tax: string;
  date: string;
  cancel_request: boolean;
  manually_payable: boolean;
  links: {
    details: string;
  };
  reward: {
    amount: string;
    point: string;
    is_applied: number;
  };
  payments: any[];
  total_amount_paid: string;
  items?: {
    data: OrderItem[];
  };
}

// Shipping Address Type
interface DivisionResponse {
  data: {
    id: number;
    country_id: number;
    name: string;
  }[];
  success: boolean;
  status: number;
}

interface DistrictResponse {
  data: {
    id: number;
    state_id: number;
    name: string;
    cost: number;
  }[];
  success: boolean;
  status: number;
}

interface AreaResponse {
  data: {
    id: number;
    city_id: number;
    name: string;
    cost: number;
  }[];
  success: boolean;
  status: number;
}

interface ShippingAddress {
  id: number;
  user_id: number;
  address: string;
  country_id: number;
  state_id: number; // division id
  city_id: number; // district id
  area_id: number;
  country_name: string;
  state_name: string;
  city_name: string;
  area_name: string;
  postal_code: string | null;
  phone: string;
  set_default: number; // 0 or 1
  location_available: boolean;
  lat: number;
  lang: number;
  type: "home" | "office" | "other";
}

interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  variation: string | null;
  price: string; // Example: "৳1,092.50"
  tax: string; // Example: "৳0.00"
  shipping_cost: string; // Example: "৳60.00"
  coupon_discount: string; // Example: "৳0.00"
  quantity: number;
  payment_status: "paid" | "unpaid" | "pending" | string;
  payment_status_string: string;
  delivery_status:
    | "pending"
    | "processing"
    | "confirmed"
    | "picked_up"
    | "on_the_way"
    | "delivered"
    | "cancelled"
    | string;
  delivery_status_string: string;
  refund_section: boolean;
  refund_button: boolean;
  refund_label: string;
  refund_request_status: number;
  link: string;
  thumbnail_image: string;
}

interface Category {
  id: number;
  slug: string;
  name: string;
  products_count?: number;
}
interface CategoryNode {
  id: string | number;
  name: string;
  slug: string;
  products_count?: number;
  subCategories?: CategoryNode[];
}
interface BrandApiResponse<T> {
  data: T;
  success: boolean;
  status: number;
}
interface BrandItemResponse {
  id: number;
  slug: string;
  name: string;
}
interface ShippingAddress {
  id: number;
  user_id: number;
  address: string;
  country_id: number;
  state_id: number; // division id
  city_id: number; // district id
  area_id: number;
  country_name: string;
  state_name: string;
  city_name: string;
  area_name: string;
  postal_code: string | null;
  phone: string;
  set_default: number; // 0 or 1
  location_available: boolean;
  lat: number;
  lang: number;
  type: "home" | "office" | "other";
}
interface ShippingAddress {
  id: number;
  user_id: number;
  address: string;
  country_id: number;
  state_id: number; // division id
  city_id: number; // district id
  area_id: number;
  country_name: string;
  name?: string;
  state_name: string;
  city_name: string;
  area_name: string;
  postal_code: string | null;
  phone: string;
  set_default: number; // 0 or 1
  location_available: boolean;
  lat: number;
  lang: number;
  type: "Home" | "Office" | "Other";
}

interface VerifyOtpModalProps {
  open: boolean;
  onClose: () => void;
  phone: string;
}

interface OTPVerificationResponse {
  result: boolean;
  message: string;
  user_id: number;
  access_token: string;
  token_type: string;
  expires_at: string;
  user: {
    id: number;
    type: "customer";
    name: string;
    email: string | null;
    avatar: string | null;
    avatar_original: string;
    phone: string;
    gender: "male" | "female" | null;
    date_of_birth: string | null;
    customer_group: {
      id: number;
      name: string;
    };
  };
}

interface OtpVerificationProps {
  data: OTPVerificationResponse;
  message: string;
  success: boolean;
}

// order success response
interface OrderResponse {
  combined_order_id: number;
  message: string;
  order_id: number;
  result: boolean;
}

// ssl beign
interface BeginPaymentParams {
  payment_type: string;
  combined_order_id: number | string;
  amount: number;
  user_id: number;
}

interface ApiResponse {
  result: boolean;
  message: string;
  url: string;
}

interface Categories {
  id: number;
  slug: string;
  name: string;
  banner: string;
  page_banner: string | null;
  icon: string;
  featured_icon: string;
  bg_image: string | null;
  app_slider: string[];
  app_banner1: string[];
  app_banner2: string[];
  app_featured_image: string;
  app_home_page_image: string;
  number_of_children: number;
  child_bg_color: string;
  links: {
    products: string;
    sub_categories: string;
  };
  design: string;
  products_count: number;
  page_banners: {
    mobile: string;
    web: string;
    app: string;
  };
  banners: {
    mobile: string;
    web: string;
    app: string;
  };
  icons: {
    mobile: string;
    web: string;
    app: string;
  };
}

interface VideoTab {
  id: number;
  title: string;
  slug: string;
  description: string;
  thumbnail: string;
  videos_count: number;
  videos: Video[];
}

// Video / reel info
interface Video {
  id: number;
  title: string;
  slug: string;
  description: string;
  thumbnail: string;
  video_url: string;
  views_count: number;
  type: string;
  products_count: number;
  products: ProductInVideo[];
}

// Product object inside a video
interface ProductInVideo {
  id: number;
  slug: string;
  name: string;
  thumbnail_image: string;
  base_price: number;
  base_discounted_price: number;
  formatted_base_price: string;
  formatted_base_discounted_price: string;
  save: number;
  currency: string;
}

interface BusinessDataType {
  type: string;
  value: string;
  image_url: string;
  popup_product_name: string;
  popup_category_name: string;
  popup_flash_deal_name: string;
  popup_brand_name: string;
}

interface CartItems {
  id: number;
  owner_id: number;
  user_id: number;
  product_id: number;
  product_name: string;
  product_thumbnail_image: string;
  variation: string | null;
  price: number;
  base_price: number;
  currency_symbol: string;
  tax: number;
  shipping_cost: number;
  shipping_type: "home_delivery" | "pickup" | string;
  shipping_method: number;
  quantity: number;
  lower_limit: number;
  upper_limit: number;
}

interface TrackingItemType {
  id: string | number;
  name: string;
  price: number;
  quantity: number;
  category?: string;
}
