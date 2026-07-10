<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SliderCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                foreach($data as $d){
                    return [
                        'photo' => api_asset($d)
                    ];
                }
            })
        ];
    }

    public function with($request)
    {
        return [
            'version'=>'3.0.0',
            'success' => true,
            'status' => 200
        ];
    }
}
