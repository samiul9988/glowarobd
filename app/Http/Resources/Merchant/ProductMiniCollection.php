<?php

namespace App\Http\Resources\Merchant;

use Auth;
use App\Models\Brand;
use App\Models\Review;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $customFieldsData = [];
                $brand = [];
                $category = [];
                if($data->brand != null){
                    $brand = [$data->brand->getTranslation('name')];
                }
                if($data->category != null){
                    $category = [$data->category->getTranslation('name')];
                }
                $photos = explode(',', $data->photos);
                foreach ($photos ?? [] as $key => $photo_id) {
                    $photos[$key] = uploaded_asset($photo_id);
                }
                if($data->customFieldsData->isNotEmpty()){
                    $customFieldsData = $data->customFieldsData?->mapWithKeys(function ($field) {
                        return [
                            $field->productCustomField->slug => $field->metaObject
                                ? $field->metaObject->items->whereIn('id', json_decode($field->value, true))->values()->map(function ($item) {
                                    return $item->title;
                                })->toArray()
                                : json_decode($field->value, true)
                        ];
                    })->toArray();
                }
                return [
                    'id' => $data->id,
                    'name' => $data->name ?? '',
                    'slug' => $data->slug,
                    'wholesale_price' => ceil(str_replace(',','',number_format(getMinimumPriceByVariant($data, $data->stocks->first()), 2))),
                    'mrp_price' => ceil(str_replace(',','',number_format(getMinimumPriceByVariant($data, $data->stocks->first()), 2))),
                    'stock' => (int) $data->stocks->first()->qty,
                    'short_description' => $data->short_description,
                    'description' => $data->description,
                    'skin_types' => data_get($customFieldsData, 'skin_type', []),
                    'key_ingredients' => data_get($customFieldsData, 'key_ingredient', []),
                    'good_for' => data_get($customFieldsData, 'good_for', []),
                    'ratings' => (double) $data->rating,
                    'reviews' => (int) Review::where(['product_id' => $data->id, 'status' => 1])->count(),
                    'product_categories' => $category,
                    'product_brands' => $brand,
                    'product_tags' => explode(',', $data->tags),
                    'thumbnail' => uploaded_asset($data->thumbnail_img) ?? '',
                    'pictures' => $photos,
                    'meta_title' => $data->meta_title,
                    'meta_description' => $data->meta_description,
                    'meta_img' => uploaded_asset($data->meta_img) ?? '',
                    'meta_tags' => $data->meta_tags ?? [],
                ];
            })
        ];
    }

    // exclude the 'meta' key from the response
    public function withResponse($request, $response)
    {
        $data = json_decode($response->getContent(), true);
        unset($data['meta'], $data['links']);
        $response->setContent(json_encode($data));
    }

    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Products fetched successfully',
            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ]
        ];
    }
}
