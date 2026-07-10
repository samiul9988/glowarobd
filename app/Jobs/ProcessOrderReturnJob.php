<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\StockAdjust;
use App\Models\ProductStock;
use Illuminate\Bus\Queueable;
use App\Models\StockAdjustItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\ProductStockAdjusted;
use App\Events\ProductStockAffected;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessOrderReturnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderReturnId;
    protected $stockAdjustId = null;

    public function __construct(int $orderReturnId)
    {
        $this->orderReturnId = $orderReturnId;
    }

    public function handle()
    {
        try {
            DB::beginTransaction();

            $orderReturn = OrderReturn::with(['order.allOrderDetails', 'items.orderItem'])->findOrFail($this->orderReturnId);
            $order = $orderReturn->order;

            if ($orderReturn->status !== 'processing') {
                Log::channel('custom')->info("ProcessPartialOrderReturnJob: Invalid order return status", [
                    'order_return_id' => $this->orderReturnId,
                    'is_partial' => $orderReturn->is_partial,
                    'status' => $orderReturn->status
                ]);
                return;
            }

            // Process full return
            if (!$orderReturn->is_partial) {
                $order->delivery_status = 'returned';
                $order->save();
                $this->logOrder($orderReturn, 'delivery_status', 'Order '. $order->code . ' has been returned');

                foreach ($order->allOrderDetails as $orderDetail) {
                    $orderDetail->delivery_status = 'returned';
                    $orderDetail->save();

                    // Restore stock for returned items
                    $this->restoreProductStock($order, $orderDetail, $orderDetail->quantity, $orderReturn->approved_by);
                }
            } else {
                // Process partial return
                foreach ($orderReturn->items as $returnItem) {
                    $orderDetail = $returnItem->orderItem;

                    if (!$orderDetail) {
                        Log::channel('custom')->info("ProcessPartialOrderReturnJob: Order detail not found", [
                            'return_item_id' => $returnItem->id,
                            'order_item_id' => $returnItem->order_item_id
                        ]);
                        continue;
                    }

                    // Validate return quantity
                    if ($returnItem->quantity > $orderDetail->quantity) {
                        Log::channel('custom')->info("ProcessPartialOrderReturnJob: Return quantity exceeds order quantity", [
                            'return_item_id' => $returnItem->id,
                            'return_quantity' => $returnItem->quantity,
                            'order_quantity' => $orderDetail->quantity
                        ]);
                        continue;
                    }

                    // Calculate the return percentage for this item
                    $returnPercentage = $returnItem->quantity / $orderDetail->quantity;

                    // Calculate the amount to deduct from the order detail
                    $priceToDeduct = round($orderDetail->price * $returnPercentage, 2);

                    // Update the order detail quantities and price
                    $orderDetail->quantity -= $returnItem->quantity;
                    $orderDetail->price = round($orderDetail->price - $priceToDeduct, 2);

                    // If quantity becomes 0 or negative, mark as returned and set values to 0
                    if ($orderDetail->quantity <= 0) {
                        $orderDetail->delivery_status = 'returned';
                        $orderDetail->quantity = 0;
                        $orderDetail->price = 0;
                        $orderDetail->last_purchase_price = 0;
                        // $orderDetail->tax = 0;
                        // $orderDetail->shipping_cost = 0;
                    }
                    $orderDetail->delivery_status = 'delivered';

                    $orderDetail->save();

                    // Restore stock for returned items
                    $this->restoreProductStock($order, $orderDetail, $returnItem->quantity, $orderReturn->approved_by);

                    Log::channel('custom')->info("ProcessPartialOrderReturnJob: Updated order detail", [
                        'order_detail_id' => $orderDetail->id,
                        'returned_quantity' => $returnItem->quantity,
                        'remaining_quantity' => $orderDetail->quantity,
                        'price_deducted' => $priceToDeduct,
                        'remaining_price' => $orderDetail->price,
                        'new_delivery_status' => $orderDetail->delivery_status
                    ]);
                }

                if($order->delivery_status !== 'delivered'){
                    $order->delivery_status = 'delivered';
                    $order->save();
                    $this->logOrder($orderReturn, 'delivery_status', 'Order '. $order->code . ' has been partially delivered');
                }
            }

            if($orderReturn->is_partial){
                // Recalculate and update order grand total
                $this->updateOrderGrandTotal($order);

                // Check if all items are returned to update order status
                $this->checkAndUpdateOrderStatus($order);
            }

            $orderReturn->status = 'approved';
            $orderReturn->save();

            DB::commit();

            Log::channel('custom')->info("ProcessPartialOrderReturnJob: Successfully processed partial return", [
                'order_return_id' => $this->orderReturnId,
                'order_id' => $order->id,
                'new_grand_total' => $order->grand_total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('custom')->error("ProcessPartialOrderReturnJob: Failed to process partial return", [
                'order_return_id' => $this->orderReturnId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function logOrder(OrderReturn $orderReturn, $action, $message = '')
    {
        try{
            $log = new \App\Models\OrderLog();
            $log->order_id = $orderReturn->order_id;
            $log->managed_by = $orderReturn->approved_by ?? null;
            $log->action = $action;
            $log->message = $message;
            $log->save();
        } catch (\Exception $e) {
            Log::channel('custom')->error("ProcessPartialOrderReturnJob: Failed to log order action", [
                'order_return_id' => $orderReturn->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function restoreProductStockOld(Order $order, OrderDetail $orderDetail, int $returnedQuantity, int $approved_by)
    {
        try {
            // Find the product stock record based on product_id and variation
            $productStock = null;

            if ($orderDetail->variation) {
                // Try to find exact match with variation
                $productStock = ProductStock::where('product_id', $orderDetail->product_id)
                    ->where('variant', $orderDetail->variation)
                    ->first();
            }

            // If no specific variant found, try to find the main product stock
            if (!$productStock) {
                $productStock = ProductStock::where('product_id', $orderDetail->product_id)
                    ->first();
            }

            if ($productStock) {
                $productStock->qty += $returnedQuantity;
                $productStock->save();

                $transaction = [
                    'product_id'    => (int)$orderDetail->product_id,
                    'variant'       => $productStock->variant ?? null,
                    'sku'           => $productStock->sku ?? null,
                    'qty'           => $returnedQuantity,
                    'isAddition'    => 1,
                    'isSubtraction' => 0,
                    'purpose'       => 'order_returned',
                    'purpose_id'    => $order->id ?? 0,
                    'note'          => 'Order Returned, Ref. ID = ' . ($order->code ?? 'Unknown'),
                ];

                event(new ProductStockAffected($transaction));

                Log::channel('custom')->info("ProcessPartialOrderReturnJob: Restored product stock", [
                    'order_id' => $order->id,
                    'product_id' => $orderDetail->product_id,
                    'product_stock_id' => $productStock->id,
                    'variation' => $orderDetail->variation,
                    'returned_quantity' => $returnedQuantity,
                    'new_stock_quantity' => $productStock->qty
                ]);
            } else {
                Log::channel('custom')->info("ProcessPartialOrderReturnJob: Product stock not found for restoration", [
                    'product_id' => $orderDetail->product_id,
                    'variation' => $orderDetail->variation
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('custom')->error("ProcessPartialOrderReturnJob: Failed to restore product stock", [
                'product_id' => $orderDetail->product_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function restoreProductStock(Order $order, OrderDetail $orderDetail, int $returnedQuantity, int $approved_by)
    {
        DB::beginTransaction();
        try {
            if (is_null($this->stockAdjustId)) {
                $stockadjustmodel = new StockAdjust;
                $stockadjustmodel->user_id = $approved_by;
                $stockadjustmodel->sa_number = config('app.stock_adjust_no_prefix').date('YmdHis') . rand(10, 99);
                $stockadjustmodel->sa_type =  'returned';
                $stockadjustmodel->sa_date = strtotime(date('Y-m-d'));
                $stockadjustmodel->note = 'Stock adjusted for returned order. Order ID: ' . $order->id;
                $stockadjustmodel->attachments = null;
                $stockadjustmodel->save();
                $this->stockAdjustId = $stockadjustmodel->id;
            }
            if ($this->stockAdjustId) {
                // product stock update
                $isAddition = true;
                $productStock = ProductStock::where('product_id', $orderDetail->product_id)
                    ->when($orderDetail->variation, function ($query) use ($orderDetail) {
                        return $query->where('variant', $orderDetail->variation);
                    })
                    ->first();
                if ($productStock) {
                    $isAddition = true;
                    $productStock->qty += $returnedQuantity;
                    $productStock->save();

                    // PO order item create
                    $stockadjustitemmodel = new StockAdjustItem;
                    $stockadjustitemmodel->stock_adjust_id = $this->stockAdjustId;
                    $stockadjustitemmodel->product_id = $orderDetail->product_id;
                    $stockadjustitemmodel->variant = $productStock->id;
                    $stockadjustitemmodel->qty = $returnedQuantity;
                    $stockadjustitemmodel->save();

                    // Store Stock Transaction
                    $transaction = [
                        'product_id'    => (int)$orderDetail->product_id,
                        'variant'       => empty($productStock->variant) ? null : $productStock->variant,
                        'sku'           => $productStock->sku ?? null,
                        'qty'           => $returnedQuantity,
                        'isAddition'    => ($isAddition) ? 1 : 0,
                        'isSubtraction' => ($isAddition) ? 0 : 1,
                        'purpose'       => 'order_returned',
                        'purpose_id'    => $this->stockAdjustId,
                        'note'          => 'New Stock Adjust, Ref ID = '.$stockadjustitemmodel->id ?? 'Unknown'.''
                    ];
                    DB::commit();
                    // Trigger The Event
                    event(new ProductStockAdjusted($stockadjustitemmodel));
                    event(new ProductStockAffected($transaction));
                    Log::channel('custom')->info("ProcessPartialOrderReturnJob: Stock adjusted", [
                        'order_id' => $order->id,
                        'product_id' => $orderDetail->product_id,
                        'product_stock_id' => $productStock->id,
                        'variation' => $orderDetail->variation,
                        'returned_quantity' => $returnedQuantity,
                        'new_stock_quantity' => $productStock->qty
                    ]);
                } else {
                    Log::channel('custom')->info("ProcessPartialOrderReturnJob: Product stock not found for restoration", [
                        'order_id' => $order->id,
                        'product_id' => $orderDetail->product_id,
                        'variation' => $orderDetail->variation
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->stockAdjustId = null;
            Log::channel('custom')->error("ProcessPartialOrderReturnJob: Failed to adjust stock", [
                'order_id' => $order->id,
                'product_id' => $orderDetail->product_id,
                'variation' => $orderDetail->variation,
                'product_stock' => $productStock ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function updateOrderGrandTotal(Order $order)
    {
        // Reload the order details to get updated values
        $order->load(['orderDetails', 'payments']);

        // Calculate new grand total using the same logic as the helper function
        $subtotal = $order->orderDetails->sum('price');
        $totalTax = $order->orderDetails->sum('tax');
        $totalShipping = $order->orderDetails->sum('shipping_cost');

        $newGrandTotal = $subtotal + $totalTax + $totalShipping - $order->coupon_discount;
        $paidAmount = $order->payments?->sum('amount') ?? 0;

        // Ensure grand total is not negative
        $order->grand_total = max($newGrandTotal, 0);
        $order->due_amount = $order->payment_status === 'paid' ? 0 : max($newGrandTotal - $paidAmount, 0);
        $order->save();

        Log::channel('custom')->info("ProcessPartialOrderReturnJob: Updated order grand total", [
            'order_id' => $order->id,
            'subtotal' => $subtotal,
            'total_tax' => $totalTax,
            'total_shipping' => $totalShipping,
            'coupon_discount' => $order->coupon_discount,
            'new_grand_total' => $order->grand_total
        ]);
    }

    private function checkAndUpdateOrderStatus(Order $order)
    {
        // Check if all order details are marked as returned or have zero quantity
        $activeItems = $order->allOrderDetails()
            ->where('delivery_status', '!=', 'returned')
            ->where('quantity', '>', 0)
            ->count();

        if ($activeItems === 0) {
            $order->delivery_status = 'returned';
            $order->save();

            Log::channel('custom')->info("ProcessPartialOrderReturnJob: Updated order status to returned", [
                'order_id' => $order->id
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::channel('custom')->error("ProcessPartialOrderReturnJob: Job failed", [
            'order_return_id' => $this->orderReturnId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
