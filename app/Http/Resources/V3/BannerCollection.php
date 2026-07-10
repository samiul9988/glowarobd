<?php

namespace App\Http\Resources\V3;

use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;

class BannerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        // return $this->collection;
        $modified = $this->collection;
        $data = $modified->map(function($data, $key) {
            $uri_segments = explode('/', $data);
            if(strpos($data, '.html') != false){
                $type = 'product';
                $wth = substr($uri_segments[3], 0, -5);
            }else{
                $type = $uri_segments[3] ?? '';
            }
            if($type == 'product'){
                $id = Cache::remember('uni_api_product'.$key, 86400, function () use ($wth) {
                    return Product::where('slug', $wth)->pluck('id') ?? '';
                });
            }elseif($type == 'category'){
                $id = Cache::remember('uni_api_category'.$key, 86400, function () use ($uri_segments) {
                    return Category::where('slug', $uri_segments[4])->pluck('id') ?? '';
                });
            }elseif($type == 'brand'){
                $id = Cache::remember('uni_api_brand'.$key, 86400, function () use ($uri_segments) {
                    return Brand::where('slug', $uri_segments[4])->pluck('id') ?? '';
                });
            }elseif($type == 'flash-deal'){
                $id = Cache::remember('uni_api_flash-deal'.$key, 86400, function () use ($uri_segments) {
                    return FlashDeal::where('slug', $uri_segments[4])->pluck('id') ?? '';
                });
            }else{
                $id = $uri_segments[4] ?? '';
            }

            return [
                'photo' => api_asset($key),
                'url' => $data,
                'position' => 1,
                'type' => $type,
                'id' => str_replace( array('[',']') , ''  , $id)
            ];
        });

        return [
            'data' => $data->values()
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
