<?php

namespace App\Http\Resources\V2;

use App\Models\Review;
use App\Models\Attribute;
use Nwidart\Modules\Facades\Module;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $user = Auth::guard('api')->user() ?? null;
        // $user_info = auth()->guard('api')->check() ? auth()->guard('api')->user()->load('customeringroup.group') : null;
        if($user){
            $user = $user->load('customeringroup.group');
        }
        return [
            'data' => $this->collection->map(function ($data) use($user) {
                $precision = 2;
                $calculable_price = getMinimumPriceByVariant($data, $data->stocks->first(), 'app', 1, $user);
                $calculable_price = number_format($calculable_price, $precision, '.', '');
                $calculable_price = floatval($calculable_price);
                // $calculable_price = round($calculable_price, 2);
                $photo_paths = get_images_path($data->photos);

                $photos = [];

                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "" ) {
                            $item = array();
                            $item['variant'] = "";
                            $item['path'] = $photo_paths[$i];
                            $photos[]= $item;
                        }

                    }

                }

                foreach ($data->stocks as $stockItem){
                    if($stockItem->image != null && $stockItem->image != ""){
                        $item = array();
                        $item['variant'] = $stockItem->variant;
                        $item['path'] = api_asset($stockItem->image) ;
                        $photos[]= $item;
                    }
                }

                $brand = [
                    'id'=> 0,
                    'name'=> "",
                    'logo'=> "",
                ];

                if($data->brand != null) {
                    $brand = [
                        'id'=> $data->brand->id,
                        'name'=> $data->brand->getTranslation('name'),
                        'logo'=> api_asset($data->brand->logo),
                    ];
                }

                $flash_deal = $data->flash_deal_product?->flash_deals ?? null;
                $flash_deals_data = null;
                if ($flash_deal) {
                    $flash_deals_data = [
                        'id' => (integer)$flash_deal->id,
                        'title' => $flash_deal->title,
                        'slug' => $flash_deal->slug,
                        'start_date' => $flash_deal->start_date,
                        'end_date' => $flash_deal->end_date,
                        'formatted_start_date' => \Carbon\Carbon::parse($flash_deal->start_date)->format('d M, Y h:i A'),
                        'formatted_end_date' => \Carbon\Carbon::parse($flash_deal->end_date)->format('d M, Y h:i A'),
                        'status' => $flash_deal->status,
                        'featured' => $flash_deal->featured,
                        "background_color" => $flash_deal->background_color,
                        "text_color" => $flash_deal->text_color,
                        "banner" => api_asset($flash_deal->banner),
                        "desktop_banner" => api_asset($flash_deal->desktop_banner),
                        'quantity' => (integer)$data->flash_deal_product->quantity,
                        'isValid' => $flash_deal->status == 1 && \Carbon\Carbon::parse($flash_deal->start_date)->isPast() && \Carbon\Carbon::parse($flash_deal->end_date)->isFuture(),
                    ];
                }

                $isFlashDealProduct = check_flash_deal_product($data);
                if ($isFlashDealProduct) {
                    $isInStock = data_get($flash_deals_data, 'quantity', 0) > 0;
                    $currentStock = data_get($flash_deals_data, 'quantity', 0);
                } else {
                    $isInStock = check_in_stock($data);
                    $currentStock = $data->stocks?->first()?->qty ?? 0;
                }

                return [
                    'id' => (integer)$data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'added_by' => $data->added_by,
                    'seller_id' => $data->user->id,
                    'shop_id' => $data->added_by == 'admin' ? 0 : $data->user->shop->id,
                    'shop_name' => $data->added_by == 'admin' ? translate('In House Product') : $data->user->shop->name,
                    'shop_logo' => $data->added_by == 'admin' ? api_asset(get_setting('header_logo')) : api_asset($data->user->shop->logo),
                    'photos' => $photos,
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'tags' => explode(',', $data->tags),
                    'price_high_low' => (double)explode('-', home_discounted_base_price($data, false, $user?->id ?? null))[0] == (double)explode('-', home_discounted_price($data, false, $user?->id ?? null))[1] ? format_price((double)explode('-', home_discounted_price($data, false, $user?->id ?? null))[0]) : "From " . format_price((double)explode('-', home_discounted_price($data, false, $user?->id ?? null))[0]) . " to " . format_price((double)explode('-', home_discounted_price($data, false, $user?->id ?? null))[1]),
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => json_decode($data->colors),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false, $user?->id ?? null),
                    'stroked_price' => home_base_price($data),
                    'main_price' => single_price(getMinimumPriceByVariant($data, $data->stocks->first(), 'app', 1, $user)),
                    'calculable_price' => $calculable_price,
                    'currency_symbol' => currency_symbol(),
                    'in_stock' => $isInStock,
                    'current_stock' => (int) $currentStock,
                    'unit' => $data->unit,
                    'rating' => (double)$data->rating,
                    // 'rating_count' => (integer)Review::where(['product_id' => $data->id, 'status' => 1])->count(),
                    'rating_count' => (int) $data->reviews->where('status', 1)->count() ?? 0, // Considering all approved reviews for rating count
                    'total_reviews' => (int) $data->reviews->where('status', 1)
                        ->whereNotNull('comment')
                        ->where('comment', '!=', '') // Ignoring reviews without comments
                        ->count(), // Considering only approved reviews and with comments for total reviews
                    'rating_counts' => [
                        $data->reviews->where('rating', 1)->count() ?? 0,
                        $data->reviews->where('rating', 2)->count() ?? 0,
                        $data->reviews->where('rating', 3)->count() ?? 0,
                        $data->reviews->where('rating', 4)->count() ?? 0,
                        $data->reviews->where('rating', 5)->count() ?? 0
                    ],
                    'earn_point' => (double)$data->earn_point,
                    'short_description' => $data->getTranslation('short_description'),
                    'description' => $data->getTranslation('description'),
                    'video_link' => $data->video_link != null ?  $data->video_link : "",
                    'video_aspect_ratio' => $data->video_aspect_ratio != null ?  $data->video_aspect_ratio : "",
                    'brand' => $brand,
                    'link' => \Illuminate\Support\Facades\Route::has('product') ? to_frontend(route('product', $data->slug)) : url('/product/'.$data->slug),
                    'is_preorder' => check_preorder_product($data),
                    'note' => $data->note,
                    'stock_in_alert' => Module::has('Waitlist') && Module::isEnabled('Waitlist'),
                    'shipping_discount' => check_shipping_discount_product([$data->id], 0),
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
                                    'image' => filled($item['image']) ? api_asset($item['image']) : "",
                                ];
                            }
                        }

                        return [
                            $field->productCustomField->slug => [
                                'type'  => $field->productCustomField->type,
                                'value' => $field->metaObject ? $items : json_decode($field->value, true),
                            ]
                        ];
                    })->toArray()
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function convertToChoiceOptions($data)
    {
        $result = array();
//        if($data) {
        foreach ($data as $key => $choice) {
            $item['name'] = $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->getTranslation('name');
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
//        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
