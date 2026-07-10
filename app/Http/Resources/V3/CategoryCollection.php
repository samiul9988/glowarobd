<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Utility\CategoryUtility;

class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $banner = isset($data->banner) ? json_decode($data->banner, true) : null;
                $pageBanner = isset($data->page_banner) ? json_decode($data->page_banner, true) : null;
                $icon = isset($data->icon) ? json_decode($data->icon, true) : null;
                $featuredIcon = isset($data->featured_icon) ? json_decode($data->featured_icon, true) : null;
                $bgImage = isset($data->bg_image) ? json_decode($data->bg_image, true) : null;

                $appSlider = isset($data->app_slider) ? json_decode($data->app_slider, true) : [];
                foreach ($appSlider ?? [] as $key => $value) {
                    $appSlider[$key] = api_asset($value);
                }
                $appBanner1 = isset($data->app_banner1) ? json_decode($data->app_banner1, true) : [];
                foreach ($appBanner1 ?? [] as $key => $value) {
                    $appBanner1[$key] = api_asset($value);
                }
                $appBanner2 = isset($data->app_banner2) ? json_decode($data->app_banner2, true) : [];
                foreach ($appBanner2 ?? [] as $key => $value) {
                    $appBanner2[$key] = api_asset($value);
                }

                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->name,
                    'banner' => isset($banner) && isset($banner['app']) ? api_asset($banner['app']) : null,
                    'page_banner' => isset($pageBanner) && isset($pageBanner['app']) ? api_asset($pageBanner['app']) : null,
                    'icon' => isset($icon) && isset($icon['app']) ? api_asset($icon['app']) : null,
                    'featured_icon' => isset($featuredIcon) && isset($featuredIcon['app']) ? api_asset($featuredIcon['app']) : null,
                    'bg_image' => isset($bgImage) && isset($bgImage['app']) ? api_asset($bgImage['app']) : null,
                    'child_bg_color' => $data->child_bg_color ?? '#4E1E35',
                    // 'bg_color' => $data->parentCategory?->child_bg_color ?? null,
                    'app_slider' => $appSlider,
                    'app_banner1' => $appBanner1,
                    'app_banner2' => $appBanner2,
                    'app_featured_image' => api_asset($data->app_featured_image),
                    'app_home_page_image' => api_asset($data->app_home_page_image),
                    'number_of_children' => CategoryUtility::get_immediate_children_count($data->id),
                    'links' => [
                        'products' => route('api.products.category', $data->id),
                        'sub_categories' => route('subCategories.index', $data->id)
                    ],
                    'design' => $data->design ? 'design_'.$data->design : 'design_1',
                    // 'products_count' => $data->products_count ?? 0,
                    'products_count' => CategoryUtility::get_products_count($data->id),
                    'banners' => [
                        'web' => isset($banner['web']) ? api_asset($banner['web']) : null,
                        'mobile' => isset($banner['mobile']) ? api_asset($banner['mobile']) : null,
                        'app' => isset($banner['app']) ? api_asset($banner['app']) : null,
                    ],
                    'page_banners' => [
                        // This will be used for mobile view in category page in new UI
                        'web' => isset($pageBanner['web']) ? api_asset($pageBanner['web']) : null,
                        'mobile' => isset($pageBanner['mobile']) ? api_asset($pageBanner['mobile']) : null,
                        'app' => isset($pageBanner['app']) ? api_asset($pageBanner['app']) : null,
                    ],
                    'icons' => [
                        // This will be used for mobile view in sub-category card in new UI
                        'web' => isset($icon['web']) ? api_asset($icon['web']) : null,
                        'mobile' => isset($icon['mobile']) ? api_asset($icon['mobile']) : null,
                        'app' => isset($icon['app']) ? api_asset($icon['app']) : null,
                    ],
                    'featured_icons' => [
                        'web' => isset($featuredIcon['web']) ? api_asset($featuredIcon['web']) : null,
                        'mobile' => isset($featuredIcon['mobile']) ? api_asset($featuredIcon['mobile']) : null,
                        'app' => isset($featuredIcon['app']) ? api_asset($featuredIcon['app']) : null,
                    ],
                    'meta' => [
                        'title' => $data->meta_title ?? $data->name,
                        'description' => $data->meta_description,
                        'keywords' => $data->meta_keywords ?? '',
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
