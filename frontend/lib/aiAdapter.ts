/**
 * AI API Response Adapter
 * Transforms AI API (ai.glowaro.com) responses to match frontend format.
 */
import { useAiApi } from "@/config/apiConfig";

function links(id: number) {
  return { products: `https://api.glowaro.com/api/v3/products/category/${id}`, sub_categories: `https://api.glowaro.com/api/v3/sub-categories/${id}` };
}
function catTemplate(c: any): any {
  return { id: c.id, slug: c.slug || "", name: c.name, banner: "", page_banner: null, icon: "", featured_icon: "", bg_image: null, app_slider: [""], app_banner1: [""], app_banner2: [""], app_featured_image: "", app_home_page_image: "", number_of_children: (c.children || []).length, child_bg_color: "#4E1E35", links: links(c.id), design: "design_1", products_count: 0, page_banners: { mobile: "", web: "", app: "" }, banners: { mobile: "", web: "", app: "" }, icons: { mobile: "", web: "", app: "" } };
}
function trBrand(b: any): any { return { id: b.id, name: b.name, slug: b.slug, logo: b.url || "" }; }
function trProduct(p: any): any {
  const mp = p.sale_price || p.price || p.regular_price || "0", rp = p.regular_price || mp;
  const sv = p.regular_price && p.sale_price ? Math.round(((parseFloat(p.regular_price) - parseFloat(p.sale_price)) / parseFloat(p.regular_price)) * 100) : 0;
  return { id: p.id || 0, name: p.name || "", slug: p.slug || "", brand: "", current_stock: p.stock_status === "in_stock" ? 10 : 0, discount_type: p.sale_price ? "percent" : "", flash_deal: { is_flash_deal: 0, data: "" }, links: { details: p.url || "#" }, main_price: `৳${mp}`, unit_price: `৳${mp}`, web_price: `৳${mp}`, stroked_price: `৳${rp}`, nonformated_price: parseFloat(mp) || 0, short_description: "", description: "", min_order_amount: 1, formatted_base_discounted_price: `৳${mp}`, save: sv, rating: p.rating || 0, sales: 0, thumbnail_image: p.image || "", shipping_discount: { amount: 0, status: false, min_amount: 0 }, is_new: false, in_stock: p.stock_status === "in_stock", is_preorder: false, has_discount: !!p.sale_price, total_reviews: 0, formatted_discount: p.sale_price ? `-${sv}%` : "" };
}
function empty() { return { data: [], success: true, status: 200 }; }
function emptyProducts() {
  return { data: [], meta: { current_page: 1, last_page: 1, per_page: "20", from: 0, to: 0, total: 0 }, links: { first: "", last: "", prev: null, next: null }, success: true, status: 200 };
}
function bsItem(type: string, value = "", image = ""): any {
  return { type, value, image_url: image, popup_product_name: "", popup_category_name: "", popup_flash_deal_name: "", popup_brand_name: "" };
}
function syntheticBusinessSettings(): any {
  return { data: [
    bsItem("customs_menu_71", JSON.stringify([{ id: 1, label: "Home", url: "/", icon: "", background_color: "", background_font_color: "" }, { id: 2, label: "Shop", url: "/category/skin-care", icon: "", background_color: "", background_font_color: "", children: [] }])),
    bsItem("header_logo", "", "https://api.glowaro.com/uploads/all/D1AWhUhqV4KKRldyJJeL94WUNvbzQjtRfbIoX3NX.webp"),
    bsItem("app_store_link", ""),
    bsItem("play_store_link", "/images/login-banner.png"),
    bsItem("user_login_banner", "", "/images/login-banner.png"),
    bsItem("user_registration_banner", ""),
    bsItem("show_highlight_brand", "1"),
    bsItem("show_social_links", "on"),
    bsItem("facebook_link", "#"),
    bsItem("tiktok_link", "#"),
    bsItem("instagram_link", "#"),
    bsItem("youtube_link", "#"),
    bsItem("about_us_description", "Glowaro - Online Beauty Store"),
    bsItem("frontend_copyright_text", "© 2026 Glowaro. All rights reserved."),
    bsItem("frontend_copyright_text", ""),
    bsItem("payment_method_images", "", ""),
    bsItem("meta_title", "Glowaro Skincare Limited | We Care About Your Skin"),
    bsItem("meta_description", "Glowaro SKINCARE is trusted & Authentic Cosmetics Company with Best Price."),
    bsItem("meta_keywords", "glowaro, skincare, beauty"),
    bsItem("meta_image", ""),
    bsItem("google_analytics", "0"),
    bsItem("google_tagmanager", "0"),
    bsItem("google_analytics_id", ""),
    bsItem("google_tagmanager_id", ""),
  ], success: true, status: 200 };
}

export async function fetchFromAI(endpoint: string, _options?: RequestInit): Promise<any> {
  const path = endpoint.split("?")[0];
  if (path === "/business-settings") return syntheticBusinessSettings();
  if (path.startsWith("/sliders") || path.startsWith("/flash-deals")) return empty();
  if (path.startsWith("/deal-products")) return emptyProducts();
  if (path.startsWith("/banners")) return { banner1: { data: [] }, banner2: { data: [] }, success: true, status: 200 };
  if (path.startsWith("/testimonials") || path.startsWith("/highlights") || path.startsWith("/videos") || path.startsWith("/categories/featured") || path.startsWith("/skin-concerns")) return empty();
  if (path.startsWith("/new-arrival-products") || path.startsWith("/baby") || path.startsWith("/product-category")) return emptyProducts();

  const AI_API = process.env.NEXT_PUBLIC_AI_API_URL || "https://ai.glowaro.com";
  const url = `${AI_API}/api${endpoint}`;
  try {
    const res = await fetch(url, { headers: { "Content-Type": "application/json", source: "web" } });
    const json = await res.json();
    if (path === "/categories") return { data: (json.data || []).map(catTemplate), success: true, status: 200 };
    if (path.startsWith("/sub-categories/")) return { data: [], success: true, status: 200 };
    if (path === "/brands" || path.startsWith("/brands?")) return { data: (json.data || []).map(trBrand), success: true, status: 200 };
    if (path.startsWith("/search")) return { data: (json.data || []).map(trProduct), meta: { current_page: json.current_page || 1, last_page: 1, per_page: "20", from: 0, to: 0, total: (json.data || []).length }, links: { first: "", last: "", prev: null, next: null }, success: true, status: 200 };
    return json;
  } catch { return empty(); }
}
