<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RewardPointLogCollection extends ResourceCollection
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
                    'type'  => $data->activity_type,
                    'earn'  => $data->earned,
                    'spent' => $data->spent,
                    'text'  => $data->activity_str,
                    'date'  => $data->updated_at->format('Y-m-d'),
                    'time'  => $data->updated_at->format('H:i:s')
                ];
            })
        ];
    }

    public function with($request){
        return [
            'success' => true,
            'status'  => 200,
            'message' => 'Found user log'
        ];
    }
}
