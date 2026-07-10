interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedProductsResponse {
  current_page: number;
  from: number;
  last_page: number;
  links: PaginationLink[];
  path: string;
  per_page: string;
  to: number;
  total: number;
}

interface FilteringResponse extends GlobalApiResponse<ProductType[]> {
  min_price: number;
  max_price: number;
  meta: PaginatedProductsResponse;
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}
