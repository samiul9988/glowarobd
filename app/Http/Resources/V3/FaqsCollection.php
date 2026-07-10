<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FaqsCollection extends ResourceCollection
{
    
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => (int) $data->id,
                    'question' => $data->question,
                    'answer' => $data->answer,
                    'category_id' => $data->category_id
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
