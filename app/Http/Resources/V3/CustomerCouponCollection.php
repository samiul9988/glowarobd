<?php

namespace App\Http\Resources\V3;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCouponCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $details = $data->coupon->details ? json_decode($data->coupon->details, true) : [];
                return [
                    'code' => $data->coupon->code,
                    'discount_type' => $data->coupon->discount_type,
                    'discount' => $data->coupon->discount,
                    'formatted_discount' => match ($data->coupon?->discount_type) {
                        'percent' => $data->coupon->discount . '% OFF',
                        'amount' => single_price($data->coupon->discount). ' OFF',
                        default => (string)$data->coupon->discount,
                    },
                    'start_date' => $data->coupon->start_date,
                    'end_date' => Carbon::parse($data->expire_date)->timestamp,
                    'formatted_end_date' => $data->expire_date ? 'Valid ' . Carbon::parse($data->expire_date)->format('d M Y') : '',
                    'min_buy' => $details['min_buy'] ?? 0,
                    'formatted_min_buy' => isset($details['min_buy']) && $details['min_buy'] > 0 ? 'Min. Spend: ' . single_price($details['min_buy']) : '* No Condition',
                    'details' => $details,
                    'description' => explode("\n", $data->coupon->description ?? '') ?? [],
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
