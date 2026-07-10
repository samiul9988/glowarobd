<?php

namespace App\Http\Resources\Merchant;

use Auth;
use App\Models\Brand;
use App\Models\Review;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductStockCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'stock' => (integer)$data->stocks->first()->qty,
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
            'message' => 'Products stock fetched successfully',
            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage()
            ]
        ];
    }
}
