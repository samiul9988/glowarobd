<?php

namespace App\Http\Resources\V3;

use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BusinessSettingCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $data_val = $this->collection->map(function($data){
            if($data->value!='') {
                if($data->type == 'app_popup_product_id' || $data->type == 'web_popup_product_id'){
                    $product = Product::select(['name', 'slug'])->find($data->value);
                }elseif($data->type == 'app_popup_category_id' || $data->type == 'web_popup_category_id'){
                    $category = Category::select(['name', 'slug'])->find($data->value);
                }elseif($data->type == 'app_popup_flash_deal_id' || $data->type == 'web_popup_flash_deal_id'){
                    $flash_deal = FlashDeal::select(['title', 'slug'])->find($data->value);
                }elseif($data->type == 'app_popup_brand_id' || $data->type == 'web_popup_brand_id'){
                    $brand = Brand::select(['name', 'slug'])->find($data->value);
                }
            }
            if ($data->type == 'customs_menu_71') {
                $menus = json_decode($data->value, true);

                foreach ($menus as &$menu) {
                    $menu['icon'] = !empty($menu['icon']) ? api_asset($menu['icon']) : '';
                }
                unset($menu);
                $data->value = json_encode($menus);
            }
            if ($data->value!='' && in_array($data->type, ['app_popup_product_id', 'web_popup_product_id', 'app_popup_category_id', 'web_popup_category_id', 'app_popup_flash_deal_id', 'web_popup_flash_deal_id', 'app_popup_brand_id', 'web_popup_brand_id'])) {
                $redirectUrl = match($data->type) {
                    'app_popup_product_id', 'web_popup_product_id' => $product->slug ?? '',
                    'app_popup_category_id', 'web_popup_category_id' => $category->slug ?? '',
                    'app_popup_flash_deal_id', 'web_popup_flash_deal_id' => $flash_deal->slug ?? '',
                    'app_popup_brand_id', 'web_popup_brand_id' => $brand->slug ?? '',
                    default => '',
                };
            }
            return [
                'type' => $data->type,
                'value' => $data->type == 'verification_form' ? json_decode($data->value) : $data->value,
                'image_url' => (string) (is_numeric($data->value) ? uploaded_asset($data->value) : $data->value),
                'redirect_url' => $redirectUrl ?? '',
                'popup_product_name' => $product->name ?? '',
                'popup_category_name' => $category->name ?? '',
                'popup_flash_deal_name' => $flash_deal->title ?? '',
                'popup_brand_name' => $brand->name ?? '',
            ];
        });

        return [
            'data' => $data_val
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
