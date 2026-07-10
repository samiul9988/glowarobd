<?php

namespace App\Http\Resources\V2;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseHistoryMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'user_id' => intval($data->user_id),
                    'payment_type' => ucwords(str_replace('_', ' ', $data->payment_type)) ,
                    'payment_status' => $data->payment_status,
                    'payment_status_string' => ucwords(str_replace('_', ' ', $data->payment_status)),
                    'delivery_status' => $data->delivery_status,
                    'delivery_status_string' => $data->delivery_status == 'pending'? "Order Placed" : ucwords(str_replace('_', ' ',  $data->delivery_status)),
                    'grand_total' => format_price($data->grand_total) ,
                    'date' => Carbon::createFromTimestamp($data->date)->format('d-m-Y'),
                    'links' => [
                        'details' => ''
                    ],
                    'reward' => [
                        'amount' => single_price(isset($data->reward_point_discount) ? $data->reward_point_discount : 0),
                        'point' => isset($data->applied_reward_point) ? $data->applied_reward_point: 0,
                        'is_applied' => $data->reward_point_applied
                    ],
                    'payments' => $data->payments->map(function($payment) {
                        return [
                            'invoice' => $payment->invoice_no,
                            'amount' => single_price($payment->amount),
                            'payment_method' => $payment->payment_method,
                            'date' => Carbon::parse($payment->created_at)->format('d-m-Y'),
                        ];
                    }),
                    'total_amount_paid' => single_price($data->payments->sum('amount'))
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
