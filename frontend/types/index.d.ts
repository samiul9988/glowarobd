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

interface GlobalApiResponse<T> {
  data: T;
  success: boolean;
  status: number;
}

declare global { interface Window { lenis?: { stop?: () => void; start?: () => void; }; } }
