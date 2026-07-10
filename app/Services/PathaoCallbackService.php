<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Log;
use App\Events\ProductStockAffected;

class PathaoCallbackService
{
    private $order;
    private $user;
    public function handle(array $data): void
    {
        $orderCode = $data['merchant_order_id'] ?? null;
        $event = $data['event'] ?? 'unknown';

        Log::channel('pathao_callback')->info("Pathao Webhook Event: {$event}", $data);

        $this->order = Order::with('user', 'orderDetails')->where('code', $orderCode)->first();

        if (!$this->order) {
            Log::channel('pathao_callback')->warning("Order not found: {$orderCode}");
            return;
        }

        $this->user = $this->order->user ?? null;

        match ($event) {
            'order.created' => $this->handleCreated($data),
            'order.picked' => $this->handlePickedup($data),
            'order.at-the-sorting-hub' => $this->handleHubReached($data, 'sorting hub'),
            'order.received-at-last-mile-hub' => $this->handleHubReached($data, 'last mile hub'),
            'order.assigned-for-delivery' => $this->handleAssigned($data),
            'order.delivered' => $this->handleDelivered($data),
            'order.partial-delivery' => $this->handlePartialDelivered($data, 'partial'),
            'order.on-hold' => $this->handleOnHold($data),
            'order.returned' => $this->handleReturned($data),
            'order.in-transit' => $this->handleInTransit($data),
            // 'order.paid' => $this->handlePaidEvent($data),
            default => $this->handleUnknown($data),
        };
    }

    protected function handleCreated(array $data): void
    {
        try {
            $this->order->delivery_fee = $data['delivery_fee'] ?? 0;
            $this->order->save();

            // foreach ($this->order->orderDetails as $orderDetail) {
            //     $product = Product::with('stocks')->find($orderDetail->product_id);

            //     $product_variation = $orderDetail->variation ?? null;

            //     $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
            //     if ($lastPurchaseItem) {
            //         $lastPurchasePrice = $lastPurchaseItem->price;
            //     } else {
            //         $lastPurchasePrice = 0;
            //     }

            //     if (is_null($orderDetail->last_purchase_price)) {
            //         $orderDetail->last_purchase_price = $lastPurchasePrice === 0 ? ($orderDetail->price / $orderDetail->quantity) : $lastPurchasePrice;

            //         $orderDetail->save();
            //     }
            // }

            Log::channel('pathao_callback')->info("Order #" . $this->order->code . " delivery fee updated");
        } catch (\Exception $e) {
            // Silent catch
        }
    }

    protected function handleAssigned(array $data): void
    {
        $message = "Order " . $this->order->code . " is now assigned for delivery";
        $this->logOrder('delivery_status', $message);
        Log::channel('pathao_callback')->info("Order #" . $this->order->code . " assigned to rider");
    }

    protected function handlePickedup(array $data): void
    {
        $this->order->delivery_status = 'on_the_way';
        $this->order->save();

        $message = "Order " . $this->order->code . " is now on the way";
        $this->logOrder('delivery_status', $message);
        Log::channel('pathao_callback')->info("Order #" . $this->order->code . " is now on the way");
    }

    protected function handleHubReached(array $data, string $hub): void
    {
        $this->order->delivery_status = 'on_the_way';
        $this->order->save();

        $message = "Order " . $this->order->code . " reached to the {$hub}";
        $this->logOrder('delivery_status', $message);
        Log::channel('pathao_callback')->info("Order #" . $this->order->code . " reached to the {$hub}");
    }

    protected function handlePaidEvent(array $data)
    {
        $this->order->payment_status = strtolower($data['payment_status'] ?? 'paid');
        $this->order->due_amount = 0;
        $this->order->save();
    }

    protected function handleDelivered(array $data, string $status = 'paid'): void
    {
        $collected_amount = $data['collected_amount'] ?? 0;
        $reason = $data['reason'] ?? '';

        $order_due_amount = calculate_due($this->order) ?? 0;
        if ($collected_amount >= floor($order_due_amount)) {
            $this->order->payment_status = 'paid';
            $this->order->due_amount = 0;
            $payment = make_payment($this->order, [
                'method' => 'pathao',
                'bank_type' => 'cash',
            ], $collected_amount);
        } elseif ($collected_amount > 0 && $status === 'partial') {
            $this->order->payment_status = 'partial';
            $this->order->due_amount = max(0, floor($order_due_amount) - $collected_amount);
            $payment = make_payment($this->order, [
                'method' => 'pathao',
                'bank_type' => 'cash',
            ], $collected_amount);
        }
        $this->order->delivery_status = 'delivered';
        $this->order->save();

        if (!is_null($this->user)) {
            $this->user->delivered_order = $this->user->delivered_order + 1;
            $this->user->save();
        }

        $message = "Order " . $this->order->code . " has been " . ($status === 'partial' ? " partially" : "") . " delivered";
        if ($reason) {
            $message .= " - " . $reason;
        }
        $this->logOrder('delivery_status', $message);
        Log::channel('pathao_callback')->info("Order #" . $this->order->code . " delivered");
    }

    protected function handlePartialDelivered(array $data, string $status = 'partial'): void
    {
        $this->handleDelivered($data, $status);
    }

    protected function handleOnHold(array $data): void
    {
        $reason = $data['reason'] ?? '';
        $message = "Order " . $this->order->code . " marked to Hold";
        if ($reason) {
            $message .= " - " . $reason;
        }
        $this->logOrder('delivery_status', $message);
        Log::channel('pathao_callback')->info("Order #" . $this->order->code . " marked to Hold");
    }

    protected function handleReturned(array $data): void
    {
        Log::channel('pathao_callback')->info('Order Returned Process is currently disabled.', $data);
        // $reason = $data['reason'] ?? '';

        // $message = "Order ". $this->order->code ." delivery status updated to Returned";
        // if($reason) {
        //     $message .= " - ". $reason;
        // }
        // $this->logOrder('delivery_status', $message);
        // $this->manageStock('returned');
        // Log::channel('pathao_callback')->info("Order #". $this->order->code ." returned");
    }

    protected function handleInTransit(array $data): void
    {
        $message = "Order " . $this->order->code . " is now In Transit";
        $this->logOrder('delivery_status', $message);
        Log::channel('pathao_callback')->info("Order #" . $this->order->code . " is now In Transit");
    }

    protected function handleUnknown(array $data): void
    {
        Log::channel('pathao_callback')->info("Unknown Pathao webhook event", $data);
    }

    protected function logOrder($action, $message = ''): void
    {
        $log = new \App\Models\OrderLog();
        $log->order_id = $this->order->id;
        $log->managed_by = 0;
        $log->action = $action;
        $log->message = $message;
        $log->save();
    }

    protected function manageStock($status)
    {
        OrderDetail::where('order_id', $this->order->id)->update([
            'delivery_status' => $status
        ]);
        foreach ($this->order->orderDetails as $orderDetail) {
            $variant = $orderDetail->variation ?? '';

            $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $variant)->first();

            if ($product_stock != null) {
                $product_stock->qty += $orderDetail->quantity;
                $product_stock->save();

                $isAddition = true;
                // Store Stock Transaction
                $transaction = [
                    'product_id'    => (int)$orderDetail->product_id,
                    'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                    'sku'           => $product_stock->sku ?? null,
                    'qty'           => abs($orderDetail->quantity),
                    'isAddition'    => ($isAddition) ? 1 : 0,
                    'isSubtraction' => ($isAddition) ? 0 : 1,
                    'purpose'       => 'order_returned',
                    'purpose_id'    => $this->order->id ?? 0,
                    'note'          => 'Order Returned From Pathao, Ref. ID = ' . $this->order->code ?? 'Unknown'
                ];
                // Trigger The Event
                event(new ProductStockAffected($transaction));
            }
        }
        $this->order->save();

        if (!is_null($this->user)) {
            $this->user->delivered_order = $this->user->delivered_order - 1;
            $this->user->save();
        }
    }
}
