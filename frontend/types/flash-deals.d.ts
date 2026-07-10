interface FlashDealType {
  id: number;
  slug: string;
  title: string;
  banner: string;
  icon: string;
  date: string;
  featured_icon: string;
  number_of_children: number;
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
  icons: {
    mobile: string;
    web: string;
    app: string;
  };
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
  links: {
    url: string | null;
    label: string;
    active: boolean;
  };
  path: string;
  per_page: number;
  to: number;
  total: number;
}

interface DealDetailsResponse extends GlobalApiResponse<ProductType[]> {
  min_price: number;
  max_price: number;
  links: PaginationLinks;
  meta: PaginationMeta;
}

interface FlashDealResponse extends GlobalApiResponse<FlashDealType[]> {}

interface DealResponse extends GlobalApiResponse<ProductType[]> {}
