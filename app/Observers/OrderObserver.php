<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order)
    {
        // Check if order is from current month
        $orderMonth = $order->created_at->format('Y-m');
        $currentMonth = now()->format('Y-m');


        if ($orderMonth < $currentMonth) {
            // Order is from previous month - skip logging
            if(count($order->orderDetails) > 0) {
                foreach($order->orderDetails as $orderDetail) {
                    $product_id = $orderDetail->product_id;
                    $product_variation = $orderDetail->variation;

                    $yesterday = $order->created_at->endOfMonth()->format('Y-m-d');
                    $from = date('Y-m-d 00:00:00', strtotime($yesterday));
                    $fromx = strtotime($from);

                    $to = date('Y-m-d 23:59:59', strtotime($yesterday));
                    $tox = strtotime($to);
                    if($product_variation) {
                        $products = ProductStock::where('product_id', $product_id)->where('variant', $product_variation)->first();
                    } else {
                        $products = ProductStock::where('product_id', $product_id)
                            ->where(function ($query) {
                                $query->where('variant', '')
                                    ->orWhereNull('variant');
                            })->first();
                    }
                    if($products) {
                        $product_variation = $products->variant;
                        $product_id = $products->product_id;
                        $variant_id = $products->id;
                        Log::info('OrderObserver updated - Creating closing stock', [
                            'product_variation' => $product_variation,
                            'product_id' => $product_id,
                            'variant_id' => $variant_id,
                            'yesterday' => $yesterday,
                            'from' => $from,
                            'to' => $to,
                            'fromx' => $fromx,
                            'tox' => $tox,
                        ]);
                        createOrUpdateClosingStock($product_variation, $product_id, $variant_id, $yesterday, $from, $to, $fromx, $tox);
                    }
                }
            }
        }
    }
}
