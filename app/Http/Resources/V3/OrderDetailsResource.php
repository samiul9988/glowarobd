<?php

namespace App\Http\Resources\V3;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'user_id' => (int) $this->user_id,
            'shipping_address' => json_decode($this->shipping_address),
            'payment_type' => ucwords(str_replace('_', ' ', $this->payment_type)),
            'shipping_type' => $this->orderDetails->first()->shipping_type != null ? $this->orderDetails->first()->shipping_type : "",
            'shipping_type_string' => $this->orderDetails->first()->shipping_type != null ? ucwords(str_replace('_', ' ', $this->orderDetails->first()->shipping_type)) : "",
            'shipping_method' => isset($this->orderDetails->first()->shippingMethod) ? $this->orderDetails->first()->shippingMethod->name : "",
            'payment_status' => $this->payment_status,
            'payment_status_string' => ucwords(str_replace('_', ' ', $this->payment_status)),
            'delivery_status' => $this->delivery_status,
            'delivery_status_string' => $this->delivery_status == 'pending'? "Order Placed" : ucwords(str_replace('_', ' ',  $this->delivery_status)),
            'grand_total' => format_price($this->grand_total),
            'coupon_discount' => format_price($this->coupon_discount),
            'shipping_cost' => format_price($this->orderDetails->sum('shipping_cost')),
            'subtotal' => format_price($this->orderDetails->sum('price')),
            'tax' => format_price($this->orderDetails->sum('tax')),
            'date' => Carbon::createFromTimestamp($this->date)->format('d-m-Y'),
            'cancel_request' => $this->cancel_request == 1,
            'manually_payable' => $this->manual_payment && $this->manual_payment_data == null,
            'links' => [
                'details' => ''
            ],
            'reward' => [
                'amount' => single_price(isset($this->reward_point_discount) ? $this->reward_point_discount : '0'),
                'point' => isset($this->applied_reward_point) ? $this->applied_reward_point: '0',
                'is_applied' => $this->reward_point_applied
            ],
            'payments' => $this->payments->map(function($payment) {
                return [
                    'invoice' => $payment->invoice_no,
                    'amount' => single_price($payment->amount),
                    'payment_method' => $payment->payment_method,
                    'date' => Carbon::parse($payment->created_at)->format('d-m-Y'),
                ];
            }),
            'total_amount_paid' => single_price($this->payments->sum('amount')),
            'items' => PurchaseHistoryItemsCollection::make($this->orderDetails)
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
