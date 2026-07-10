<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HomeCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->category->name,
                    'banner' => api_asset($data->category->banner),
                    'icon' => api_asset($data->category->icon),
                    'featured_icon' => api_asset($data->category->featured_icon),
                    'links' => [
                        'products' => route('api.products.category', $data->category->id),
                        'sub_categories' => route('subCategories.index', $data->category->id)
                    ]
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
