interface TestimonialItem {
  user_id: number;
  user_name: string;
  hide_username: number;
  avatar: string;
  rating: number;
  comment: string;
  photos: string[];
  time: string;
  user: {
    name: string;
    verified: boolean;
  };
  product: {
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
  };
}

interface TestimonialResponse extends GlobalApiResponse<TestimonialItem[]> {}
