<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AdvertizementCollection extends ResourceCollection
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
            'data' => $this->collection->map(function($data){
                return [
                    'id' => $data->id,
                    'link_type' => $data->link_type,
                    'link' => $data->link,
                    'image' => api_asset($data->image),
                    'code' => $data->code,
                    'position' => $data->position,       
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200,
            'message' => 'successfully return'
        ];
    }
}
