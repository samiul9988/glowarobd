<?php

namespace App\Http\Resources\V2;

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
            $product_name = '';
            $category_name = '';
            $brand_name = '';
            $flash_deal_name = '';
            if($data->type == 'app_popup_product_id'){
                $product_name = $data->value!=''?Product::findOrFail($data->value)->name:'';
            }elseif($data->type == 'app_popup_category_id'){
                $category_name = $data->value!=''?Category::findOrFail($data->value)->name:'';
            }elseif($data->type == 'app_popup_flash_deal_id'){
                $flash_deal_name = $data->value!=''?FlashDeal::findOrFail($data->value)->title:'';
            }elseif($data->type == 'app_popup_brand_id'){
                $brand_name = $data->value!=''?Brand::findOrFail($data->value)->name:'';
            }elseif ($data->type == 'customs_menu_71') {
                $menus = json_decode($data->value, true);

                foreach ($menus as &$menu) {
                    $menu['icon'] = !empty($menu['icon']) ? api_asset($menu['icon']) : '';
                }
                unset($menu);
                $data->value = json_encode($menus);
            }
            return [
                'type' => $data->type,
                'value' => $data->type == 'verification_form' ? json_decode($data->value) : $data->value,
                'image_url' => $data->type == 'app_popup_image' ? uploaded_asset($data->value) : uploaded_asset($data->value),
                'popup_product_name' => $product_name,
                'popup_category_name' => $category_name,
                'popup_flash_deal_name' => $flash_deal_name,
                'popup_brand_name' => $brand_name,
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
