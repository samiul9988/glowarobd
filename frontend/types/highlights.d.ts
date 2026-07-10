interface HighlightItem {
  title: string;
  subtitle: string;
  description: string;
  banner: string;
  highlights: {
    icon: string;
    label: string;
  }[];
  link_type: "product" | "category" | "external" | string;
  link: string;
  show_button: boolean;
  button_text: string;
  pricing: {
    has_discount: boolean;
    discount_type: "percent" | "fixed" | string;
    discount: number;
    stroked_price: string;
    main_price: string;
    calculable_price: number;
    currency_symbol: string;
  };
}

interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

interface MetaLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  links: MetaLink[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}

interface HighlightResponse extends GlobalApiResponse<HighlightItem[]> {
  links: PaginationLinks;
  meta: PaginationMeta;
}
