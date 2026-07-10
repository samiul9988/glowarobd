<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FlashDealCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'title' => $data->title,
                    'start_date' => (int) $data->start_date,
                    'date' => (int) $data->end_date,
                    'banner' => api_asset($data->desktop_banner),
                    'mobile_banner' => api_asset($data->banner),
                    'background_color' => $data->background_color,
                    'is_valid' => $data->isValid(),
                ];
            }),
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200,
        ];
    }
}
