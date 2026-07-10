<?php

namespace App\Http\Resources\V2;

use App\Models\Brand;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserProductReviewsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $photo_paths = isset($data->photos) ? get_images_path($data->photos) : [];
                $photos = [];
                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "" ) {
                            $item = array();
                            $item['path'] = $photo_paths[$i];
                            $photos[]= $item;
                        }
                    }
                }
                return [
                    'user' => [
                        'id' => $data->user_id ?? $data->user->id,
                        'name' => $data->name ?? $data->user->name,
                        'phone' => $data->phone ?? $data->user->phone,
                        'email' => $data->email ?? $data->user->email,
                    ],
                    'product' => [
                        'id' => $data->product->id,
                        'slug' => $data->product->slug,
                        'name' => $data->product->getTranslation('name'),
                        'thumbnail_img' => api_asset($data->product->thumbnail_img),
                        'stroked_price' => home_base_price($data->product, true),
                        'main_price' => home_discounted_base_price($data->product, true, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null),
                        'nonformated_price' => home_discounted_base_price($data->product, false, isset(Auth::guard('api')->user()->id)?Auth::guard('api')->user()->id:null),
                        'brand' => Brand::find($data->product->brand_id)->slug ?? null,
                    ],
                    'rating' => $data->rating,
                    'comment' => $data->comment,
                    'photos' => $photos,
                    'status' => $data->status,
                    'viewed' => $data->viewed,
                    'created_at' => Carbon::parse($data->created_at)->format('d-m-Y H:i:s'),
                    'updated_at' => Carbon::parse($data->updated_at)->format('d-m-Y H:i:s')
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
