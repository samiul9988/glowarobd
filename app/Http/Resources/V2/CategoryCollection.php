<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Utility\CategoryUtility;

class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $banner = isset($data->banner) ? json_decode($data->banner, true) : null;
                $icon = isset($data->icon) ? json_decode($data->icon, true) : null;
                $featuredIcon = isset($data->featured_icon) ? json_decode($data->featured_icon, true) : null;
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'banner' => isset($banner) && isset($banner['app']) ? api_asset($banner['app']) : "",
                    'icon' => isset($icon) && isset($icon['app']) ? api_asset($icon['app']) : "",
                    'featured_icon' => isset($featuredIcon) && isset($featuredIcon['app']) ? api_asset($featuredIcon['app']) : "",
                    'number_of_children' => CategoryUtility::get_immediate_children_count($data->id),
                    'links' => [
                        'products' => route('api.products.category', $data->id),
                        'sub_categories' => route('subCategories.index', $data->id)
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
