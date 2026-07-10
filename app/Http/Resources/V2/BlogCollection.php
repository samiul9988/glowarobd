<?php

namespace App\Http\Resources\V2;

use App\Models\Category;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BlogCollection extends ResourceCollection
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
                return [
                    'id' => (integer) $data->id,
                    'title' => $data->title,
                    'slug' => $data->slug,
                    'short_description' => $data->short_description,
                    'thumbnail' => api_asset($data->banner),
                    'category_id' => $data->category_id,
                    'category_name' => $data->category->category_name ?? '',
                    'category_slug' => $data->category->category_slug ?? '',
                    'description' => $data->description,
                    'meta_title' => $data->meta_title,
                    'meta_img' => api_asset($data->meta_img),
                    'meta_description' => $data->meta_description,
                    'meta_keywords' => $data->meta_keywords,
                    'status' => $data->status,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
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
