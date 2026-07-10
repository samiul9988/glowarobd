<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCouponCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'code' => $data->coupon->code,
                    'discount' => $data->coupon->discount,
                    'discount_type' => $data->coupon->discount_type,
                    'start_date' => $data->coupon->start_date,
                    'end_date' => strtotime($data->expire_date),
                    'details' => json_decode($data->coupon->details, true),
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
