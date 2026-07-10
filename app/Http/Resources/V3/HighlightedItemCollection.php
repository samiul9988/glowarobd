<?php

namespace App\Http\Resources\V3;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HighlightedItemCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $user = Auth::guard('api')->user() ?? null;
        if(!$user && filled($request->header('uid',null))) {
            $user = User::find($request->header('uid'));
        }
        if($user){
            $user = $user->load('customeringroup.group');
        }
        $source = $request->header('source', 'app');
        return [
            'data' => $this->collection->map(function ($data) use ($source, $user) {
                $link = '';
                if(filled($data->custom_link)) {
                    $link = $data->custom_link;
                } elseif($data->linkable) {
                    $link = $data->linkable->slug;
                }
                $link_type = $data->linkable_type ? strtolower(class_basename($data->linkable_type)) : 'custom';

                if($link_type === 'product') {
                    $product_stock = $data->linkable->stocks->first();
                    $main_price = getMinimumPriceByVariant($data->linkable, $product_stock, $source, 1, $user);
                    $calculable_price = floatval(number_format($main_price, 2, '.', ''));
                    $base_price = home_base_price($data->linkable, false);
                    $discounted_base_price = home_discounted_base_price($data->linkable, false, $user?->id ?? null);
                }
                return [
                    'title' => $data->title,
                    'subtitle' => $data->subtitle,
                    'description' => $data->description,
                    'banner' => $data->banner_img ? api_asset($data->banner_img) : null,
                    'highlights' => array_map(fn($highlight) => [
                        'icon' => api_asset($highlight['icon'] ?? ''),
                        'label' => $highlight['label'] ?? ''
                    ], $data->highlights),
                    'link_type' => $link_type,
                    'link' => $link,
                    'show_button' => filled($data->button_text),
                    'button_text' => $data->button_text ?? '',
                    'show_pricing' => $link_type === 'product' ? true : false,
                    'pricing' => $link_type === 'product' ? [
                        'has_discount' => $base_price != $discounted_base_price,
                        'discount_type' => $data->linkable->discount_type,
                        'discount' => $data->linkable->discount,
                        'stroked_price' => currency_symbol() . (string) $base_price,
                        'main_price' => single_price($main_price),
                        'calculable_price' => $calculable_price,
                        'currency_symbol' => currency_symbol(),
                    ] : null,
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
}
