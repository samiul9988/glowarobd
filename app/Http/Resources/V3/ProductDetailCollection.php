<?php

namespace App\Http\Resources\V3;

use App\Models\Attribute;
use App\Models\Color;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Services\GiftOfferService;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Nwidart\Modules\Facades\Module;

class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $user = Auth::guard('api')->user() ?? null;
        // $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
        if (! $user && filled($request->header('uid', null))) {
            $user = User::find($request->header('uid'));
        }
        if ($user) {
            $user = $user->load('customeringroup.group');
        }

        return [
            'data' => $this->collection->map(function ($data) use ($user, $request) {
                $source = $request->header('source', 'app');
                $precision = 2;
                $product_stock = filled($request->variant ?? null) ? $data->stocks->where('variant', $request->variant)->first() : $data->stocks->first();
                $mainPrice = getMinimumPriceByVariant($data, $product_stock, $source, 1, $user);
                $calculable_price = (float) number_format($mainPrice, $precision, '.', '');
                // $calculable_price = round($calculable_price, 2);
                $photo_paths = get_images_path($data->photos);

                $basePrice = home_base_price($data, false);
                $discountedBasePrice = home_discounted_base_price($data, false, $user?->id ?? null);
                $discountedPrice = home_discounted_price($data, false, $user?->id ?? null);

                $photos = [];

                if (! empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != '') {
                            $item = [];
                            $item['variant'] = '';
                            $item['path'] = $photo_paths[$i];
                            $photos[] = $item;
                        }

                    }

                }

                foreach ($data->stocks as $stockItem) {
                    if ($stockItem->image != null && $stockItem->image != '') {
                        $item = [];
                        $item['variant'] = $stockItem->variant;
                        $item['path'] = api_asset($stockItem->image);
                        $photos[] = $item;
                    }
                }

                $brand = [
                    'id' => 0,
                    'name' => '',
                    'logo' => '',
                ];

                if ($data->brand != null) {
                    $brand = [
                        'id' => $data->brand->id,
                        'name' => $data->brand->name,
                        'slug' => $data->brand->slug,
                        'logo' => api_asset($data->brand->logo),
                    ];
                }

                $category = [
                    'id' => 0,
                    'name' => '',
                    'logo' => '',
                ];

                if ($data->category != null) {
                    $category = [
                        'id' => $data->category->id,
                        'name' => $data->category->name,
                        'slug' => $data->category->slug,
                        'logo' => $data->category->app_featured_image ? api_asset($data->category->app_featured_image) : api_asset($data->category->featured_icon),
                    ];
                }

                $colors = json_decode($data->colors, true) ?? [];

                $whoCanReview = get_setting('who_can_post_reviews');
                $can_review = false;
                match ($whoCanReview) {
                    'everyone' => $can_review = true,
                    'all_registered_customers' => $can_review = filled($request->header('uid', null)),
                    'all_registered_buyers' => $can_review = filled($request->header('uid', null)) && Order::where('user_id', $request->header('uid', null))->where('delivery_status', 'delivered')->count() > 0,
                    default => $can_review = false,
                };

                $flash_deal = $data->flash_deal_product?->flash_deals ?? null;
                $flash_deals_data = null;
                if ($flash_deal) {
                    $flash_deals_data = [
                        'id' => (int) $flash_deal->id,
                        'title' => $flash_deal->title,
                        'slug' => $flash_deal->slug,
                        'start_date' => $flash_deal->start_date,
                        'end_date' => $flash_deal->end_date,
                        'formatted_start_date' => \Carbon\Carbon::parse($flash_deal->start_date)->format('d M, Y h:i A'),
                        'formatted_end_date' => \Carbon\Carbon::parse($flash_deal->end_date)->format('d M, Y h:i A'),
                        'status' => $flash_deal->status,
                        'featured' => $flash_deal->featured,
                        'background_color' => $flash_deal->background_color,
                        'text_color' => $flash_deal->text_color,
                        'banner' => api_asset($flash_deal->banner),
                        'desktop_banner' => api_asset($flash_deal->desktop_banner),
                        'quantity' => (int) min($product_stock->qty ?? 0, $data->flash_deal_product->quantity ?? 0),
                        'is_valid' => $flash_deal->isValid(),
                    ];
                }
                $isFlashDealProduct = check_flash_deal_product($data);
                if ($isFlashDealProduct && data_get($flash_deals_data, 'quantity', 0) > 0) {
                    $currentStock = min($product_stock->qty ?? 0, data_get($flash_deals_data, 'quantity', 0));
                    $isInStock = $currentStock > 0;
                } else {
                    $isFlashDealProduct = false;
                    $isInStock = check_in_stock($data);
                    $currentStock = $product_stock->qty ?? 0;
                }

                // dd($product_stock, $currentStock);
                return [
                    'id' => (int) $data->id,
                    'slug' => $data->slug,
                    'name' => $data->name,
                    'sku' => $data->barcode ?? '',
                    'added_by' => $data->added_by,
                    'seller_id' => $data->user?->id ?? 0,
                    'shop_id' => $data->added_by == 'admin' ? 0 : ($data->user?->shop?->id ?? 0),
                    'shop_name' => $data->added_by == 'admin' ? 'In House Product' : ($data->user?->shop?->name ?? ''),
                    'shop_logo' => $data->added_by == 'admin' ? api_asset(get_setting('header_logo')) : api_asset($data->user?->shop?->logo ?? ''),
                    'photos' => $photos,
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'faq_image' => is_null($data->faq_img) ? null : api_asset($data->faq_img),
                    'tags' => explode(',', $data->tags),
                    'price_high_low' => (float) explode('-', $discountedBasePrice)[0] == (float) explode('-', $discountedPrice)[1] ? format_price((float) explode('-', $discountedPrice)[0]) : 'From '.format_price((float) explode('-', $discountedPrice)[0]).' to '.format_price((float) explode('-', $discountedPrice)[1]),
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => $colors,
                    'color_options' => filled($colors) ? Color::whereIn('code', $colors)->pluck('name', 'code') : [],
                    'has_discount' => $basePrice != $discountedBasePrice || $basePrice != $mainPrice,
                    'discount_type' => $data->discount_type,
                    'stroked_price' => single_price($basePrice),
                    'main_price' => single_price($mainPrice),
                    'calculable_price' => $calculable_price,
                    'currency_symbol' => currency_symbol(),
                    'in_stock' => $isInStock,
                    'current_stock' => (int) $currentStock,
                    'unit' => $data->unit,
                    'rating' => (float) number_format($data->reviews?->avg('rating') ?? 0, 2),
                    // 'rating_count' => (int) Review::where(['product_id' => $data->id, 'status' => 1])
                    //     ->whereNotNull('comment')
                    //     ->where('comment', '!=', '')
                    //     ->count(),
                    'earn_point' => (float) $data->earn_point ?? 0,
                    'short_description' => $data->short_description,
                    'description' => $data->description,
                    'video_link' => $data->video_link != null ? $data->video_link : '',
                    'video_aspect_ratio' => $data->video_aspect_ratio != null ? $data->video_aspect_ratio : '',
                    'brand' => $brand,
                    'link' => \Illuminate\Support\Facades\Route::has('product') ? to_frontend(route('product', $data->slug)) : url('/product/'.$data->slug),
                    'is_preorder' => check_preorder_product($data),
                    'is_published' => $data->published == 1,
                    'note' => $data->note,
                    'shipping_discount' => check_shipping_discount_product([$data->id], 0),
                    'flash_deal' => [
                        'is_flash_deal' => $isFlashDealProduct,
                        // 'is_flash_deal' => ($flash_deals_data['isValid'] ?? false) ? 1 : 0,
                        'data' => $flash_deals_data ?? '',
                    ],
                    // 'gift_offers' => $this->getGiftOffersForProduct($data),
                    'num_of_sale' => (int) $data->num_of_sale,
                    'category' => $category,
                    'rating_counts' => [
                        $data->reviews->where('rating', 1)->count() ?? 0,
                        $data->reviews->where('rating', 2)->count() ?? 0,
                        $data->reviews->where('rating', 3)->count() ?? 0,
                        $data->reviews->where('rating', 4)->count() ?? 0,
                        $data->reviews->where('rating', 5)->count() ?? 0,
                    ],
                    'rating_count' => (int) $data->reviews?->count() ?? 0, // Considering all approved reviews for rating count
                    'total_reviews' => (int) $data->reviews->whereNotNull('comment')
                        ->where('comment', '!=', '') // Ignoring reviews without comments
                        ->count() ?? 0, // Considering only approved reviews and with comments for total reviews
                    'is_new' => $data->is_new,
                    'can_post_review' => $can_review,
                    'meta' => [
                        'title' => $data->meta_title ?? $data->name,
                        'description' => $data->meta_description,
                        'keywords' => $data->tags,
                        'img' => $data->meta_img ? api_asset($data->meta_img) : null,
                    ],
                    'stock_in_alert' => Module::has('Waitlist') && Module::isEnabled('Waitlist'),
                    'custom_fields' => $data->customFieldsData?->mapWithKeys(function ($field) {
                        $metaObjectItems = [];
                        $items = [];

                        if ($field->metaObject) {
                            $metaObjectItems = $field->metaObject->items
                                ->whereIn('id', json_decode($field->value, true))
                                ->values()
                                ->toArray();
                        }

                        if (count($metaObjectItems) > 0) {
                            foreach ($metaObjectItems as $item) {
                                $items[] = [
                                    'title' => $item['title'] ?? '',
                                    'description' => $item['description'] ?? '',
                                    'image' => filled($item['image']) ? api_asset($item['image']) : '',
                                ];
                            }
                        }

                        return [
                            $field->productCustomField->slug => [
                                'banner' => $field->productCustomField->banner ? api_asset($field->productCustomField->banner) : '',
                                'type' => $field->productCustomField->type,
                                'value' => $field->metaObject ? $items : json_decode($field->value, true),
                            ],
                        ];
                    })->toArray(),
                ];
            }),
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200,
        ];
    }

    protected function convertToChoiceOptions($data)
    {
        $result = [];
        if (!$data) {
            return $result;
        }
        foreach ($data as $key => $choice) {
            $item['name'] = $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->getTranslation('name');
            $item['options'] = $choice->values;
            array_push($result, $item);
        }

        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = [];
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }

        return $result;
    }

    /**
     * Get gift offers applicable to this product
     */
    protected function getGiftOffersForProduct($product)
    {
        // Use GiftOfferService for DRY principle
        $service = app(GiftOfferService::class);
        $offers = $service->getOffersForProduct($product);

        if ($offers->isEmpty()) {
            return [
                'has_offers' => false,
                'offers' => [],
            ];
        }

        return [
            'has_offers' => true,
            'offers' => $offers->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'slug' => $offer->slug,
                    'description' => $offer->description,
                    'banner' => api_asset($offer->banner),
                    'offer_type' => $offer->offer_type,
                    'max_qty_per_order' => (int) $offer->max_qty_per_order,
                    'start_date' => (int) $offer->start_date,
                    'end_date' => (int) $offer->end_date,
                    'items' => $offer->items->map(function ($item) {
                        $product = $item->product;
                        if (! $product) {
                            return null;
                        }

                        $originalPrice = (float) $product->unit_price;
                        $offerPrice = (float) $item->offer_price;
                        $discountPercent = $originalPrice > 0 ? round((($originalPrice - $offerPrice) / $originalPrice) * 100) : 0;
                        $isFree = $offerPrice == 0;
                        $availableQty = max(0, (int) $item->available_qty);

                        return [
                            'id' => $item->id,
                            'product_id' => $product->id,
                            'product_slug' => $product->slug,
                            'product_name' => $product->name,
                            'product_thumbnail' => api_asset($product->thumbnail_img),
                            'original_price' => $originalPrice,
                            'formatted_original_price' => format_price($originalPrice),
                            'offer_price' => $offerPrice,
                            'formatted_offer_price' => format_price($offerPrice),
                            'discount_percent' => $discountPercent,
                            'is_free' => $isFree,
                            'discount_label' => $isFree ? 'FREE' : ($discountPercent > 0 ? $discountPercent.'% OFF' : ''),
                            'available_qty' => $availableQty,
                            'is_in_stock' => check_in_stock($product) && $availableQty > 0,
                        ];
                    })->filter()->values(),
                ];
            }),
        ];
    }
}
