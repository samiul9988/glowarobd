<?php

namespace App\Http\Controllers\Api\Merchant\V1;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Events\OrderPlaced;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use Illuminate\Support\Facades\DB;
use App\Events\ProductStockAffected;
use App\Http\Controllers\Controller;
use App\Utility\NotificationUtility;
use App\Jobs\UpdateProductStockForRokomari;
use App\Http\Controllers\OrderController as BaseOrderController;

class OrderController extends Controller
{
    public function store(Request $request, $update = false)
    {
        try{
            $userId = $request->header('id');
            $user = User::find($userId);

            $order = Order::merchant()
                ->whereNotNull('merchant_order_id')
                ->where('merchant_order_id', $request->platform_order_id)
                ->where('merchant_source', $request->platform_source)
                ->where('user_id', $userId)
                ->first();

            if($order) {
                return response()->json([
                    'result' => false,
                    'message' => ('Order already exists'),
                    'order_id' => $order->id,
                ], 409); // Using 409 Conflict instead
            }

            // dd($user);
            $orderDetails = array_filter($request->order_details, function ($orderDetail) {
                return strtolower($orderDetail['supplier']) === 'glowaro';
            });

            // $productIds = array_map(function ($orderDetail) {
            //     return $orderDetail['supplier_product_id'];
            // }, $orderDetails);
            // $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            if(count($orderDetails) > 0){
                // dd($orderDetails);
                $shipping_address = $request->shipping_address;

                $shipping_address['postal_code'] = isset($shipping_address['postal_code']) ? $shipping_address['postal_code'] : '';
                DB::beginTransaction();

                $combined_order = new CombinedOrder;
                $combined_order->user_id = $user->id;
                $combined_order->shipping_address = json_encode($shipping_address);
                $combined_order->save();

                $order = new Order;
                $order->combined_order_id = $combined_order->id;
                $order->user_id = $user->id;
                $order->shipping_address = json_encode($shipping_address);

                $order->payment_type = $request->payment_type;
                $order->delivery_viewed = '0';
                $order->payment_status_viewed = '0';
                $order->code = config('app.order_no_prefix').date('YmdHis') . rand(10, 99); //date('Ymd-His') . rand(10, 99);
                $order->date = strtotime('now');
                $order->payment_status = 'unpaid';
                $order->order_source = strtolower($request->platform_source);
                $order->delivery_status = 'pending';
                $order->order_type = 'merchant';
                $order->merchant_order_id = $request->platform_order_id;
                $order->merchant_source = strtolower($request->platform_source);
                $order->merchant_payload = json_encode($request->all());

                $order->save();
                $subtotal = 0;
                $tax = 0;
                $shipping = 0;
                $coupon_discount = 0;
                foreach($orderDetails as $orderDetail){
                    $product = Product::find($orderDetail['supplier_product_id']);

                    $product_variation = $orderDetail['variation'] ?? null;
                    $product_stock = $product->stocks->where('variant', $product_variation)->first();

                    $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                    if ($lastPurchaseItem) {
                        $lastPurchasePrice = $lastPurchaseItem->price;
                    } else {
                        $lastPurchasePrice = 0;
                    }

                    if ($orderDetail['quantity'] > $product_stock->qty) {
                        $order->delete();
                        $combined_order->delete();
                        DB::rollBack();
                        return response()->json([
                            'result' => false,
                            'order_id' => 0,
                            'message' => ('The requested quantity is not available for ') . $product->name
                        ], 406);
                    }

                    $price = $orderDetail['wholesale_price'];
                    $subtotal += $price * $orderDetail['quantity'];
                    // $coupon_discount += $orderDetail['discount'];
                    if(!check_preorder_product($product)){
                        if ($product->digital != 1 && $orderDetail['quantity'] > $product_stock->qty && $product->allow_stock_out_purchases == 0) {
                            $order->delete();
                            $combined_order->delete();
                            DB::rollBack();
                            return response()->json([
                                'result' => false,
                                'order_id' => 0,
                                'message' => ('The requested quantity is not available for ') . $product->name
                            ], 406);
                        } elseif ($product->digital != 1) {
                            // if(in_array(strtolower($request->delivery_status), ['processing', 'packaging', 'shipped', 'completed', 'preorder', 'onhold'])){
                                $product_stock->qty -= $orderDetail['quantity'];
                                $product_stock->save();
                            // }

                            $isAddition = false;
                            // Store Stock Transaction
                            $transaction = [
                                'product_id'    => (int)$product->id,
                                'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                                'sku'           => $product_stock->sku ?? null,
                                'qty'           => abs($orderDetail['quantity']),
                                'isAddition'    => ($isAddition) ? 1 : 0,
                                'isSubtraction' => ($isAddition) ? 0 : 1,
                                'purpose'       => 'sales',
                                'purpose_id'    => $order->id ?? 0,
                                'note'          => 'New App Sales, Ref. ID = '.$order->code ?? 'Unknown'.''
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }
                    }else{
                        if ($product->digital != 1 && $orderDetail['quantity'] > ($product->preorder_max_qty - preorder_product_count($product))) {
                            $order->delete();
                            $combined_order->delete();
                            DB::rollBack();
                            return response()->json([
                                'result' => false,
                                'order_id' => 0,
                                'message' => ('The requested pre-order quantity is not available for ') . $product->name
                            ], 406);
                        }
                    }
                    $order_detail = new OrderDetail;
                    $order_detail->order_id = $order->id;
                    $order_detail->seller_id = $product->user_id;
                    $order_detail->product_id = $product->id;
                    $order_detail->variation = empty($product_variation) ? null : $product_variation;
                    $order_detail->price = $price * $orderDetail['quantity'];
                    $order_detail->shipping_cost = $request['shipping_cost'];
                    $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $price;

                    $order_detail->delivery_status = 'pending';

                    $shipping += $order_detail->shipping_cost;

                    $order_detail->quantity = $orderDetail['quantity'];
                    $order_detail->save();

                    $product->num_of_sale = $product->num_of_sale + $orderDetail['quantity'];
                    $product->save();

                    $order->seller_id = $product->user_id;
                }

                $order->grand_total = $subtotal + $tax + $shipping;

                $combined_order->grand_total += $order->grand_total;

                $order->save();

                // Adjust Ordered Product Stocks
                event(new OrderPlaced($order));
                $combined_order->save();

                DB::commit();
                logOrder($order, 'created');

                return response()->json([
                    'result' => true,
                    'message' => ('Order created successfully'),
                    'order_id' => $order->id,
                ], 201);
            }

            return response()->json([
                'result' => false,
                'message' => ('No order details found'),
                'order_id' => 0,
            ], 422);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => false,
                'order_id' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        try{
            $userId = $request->header('id');
            $user = User::find($userId);

            $order = Order::merchant()
                ->with('orderDetails')
                ->whereNotNull('merchant_order_id')
                ->where('merchant_order_id', $request->platform_order_id)
                ->where('merchant_source', $request->platform_source)
                ->where('user_id', $userId)
                ->first();

            if(!$order) {
                return response()->json([
                    'result' => false,
                    'message' => ('Order not found'),
                    'order_id' => 0,
                ], 404);
            }

            $orderDetails = array_filter($request->order_details, function ($orderDetail) {
                return strtolower($orderDetail['supplier']) === 'glowaro';
            });

            if(count($orderDetails) > 0){
                DB::beginTransaction();

                foreach($order->orderDetails as $existingOrderDetail) {
                    $product = Product::find($existingOrderDetail->product_id);
                    $product_stock = $product->stocks->where('variant', $existingOrderDetail->variation)->first();

                    if($product_stock) {
                        $product_stock->qty += $existingOrderDetail->quantity;
                        $product_stock->save();
                    }
                    $existingOrderDetail->delete();
                }

                $shipping_address = $request->shipping_address;

                $shipping_address['postal_code'] = isset($shipping_address['postal_code']) ? $shipping_address['postal_code'] : '';

                $combined_order = CombinedOrder::find($order->combined_order_id);
                if($combined_order) {
                    $combined_order->shipping_address = json_encode($shipping_address);
                    $combined_order->save();
                }

                $order->shipping_address = json_encode($shipping_address);
                $order->merchant_payload = json_encode($request->all());
                $order->save();

                $subtotal = 0;
                $tax = 0;
                $shipping = 0;
                $coupon_discount = 0;
                foreach($orderDetails as $orderDetail){
                    $product = Product::find($orderDetail['supplier_product_id']);

                    $product_variation = $orderDetail['variation'] ?? null;
                    $product_stock = $product->stocks->where('variant', $product_variation)->first();

                    $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                    if ($lastPurchaseItem) {
                        $lastPurchasePrice = $lastPurchaseItem->price;
                    } else {
                        $lastPurchasePrice = 0;
                    }

                    if ($orderDetail['quantity'] > $product_stock->qty) {
                        // $order->delete();
                        // $combined_order->delete();
                        DB::rollBack();
                        return response()->json([
                            'result' => false,
                            'order_id' => 0,
                            'message' => ('The requested quantity is not available for ') . $product->name
                        ], 406);
                    }

                    $price = $orderDetail['wholesale_price'];
                    $subtotal += $price * $orderDetail['quantity'];
                    // $coupon_discount += $orderDetail['discount'];
                    if(!check_preorder_product($product)){
                        if ($product->digital != 1 && $orderDetail['quantity'] > $product_stock->qty && $product->allow_stock_out_purchases == 0) {
                            // $order->delete();
                            // $combined_order->delete();
                            DB::rollBack();
                            return response()->json([
                                'result' => false,
                                'order_id' => 0,
                                'message' => ('The requested quantity is not available for ') . $product->name
                            ], 406);
                        } elseif ($product->digital != 1) {
                            $product_stock->qty -= $orderDetail['quantity'];
                            $product_stock->save();

                            $isAddition = false;
                            // Store Stock Transaction
                            $transaction = [
                                'product_id'    => (int)$product->id,
                                'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                                'sku'           => $product_stock->sku ?? null,
                                'qty'           => abs($orderDetail['quantity']),
                                'isAddition'    => ($isAddition) ? 1 : 0,
                                'isSubtraction' => ($isAddition) ? 0 : 1,
                                'purpose'       => 'sales',
                                'purpose_id'    => $order->id ?? 0,
                                'note'          => 'New App Sales, Ref. ID = '.$order->code ?? 'Unknown'.''
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }
                    }else{
                        if ($product->digital != 1 && $orderDetail['quantity'] > ($product->preorder_max_qty - preorder_product_count($product))) {
                            // $order->delete();
                            // $combined_order->delete();
                            DB::rollBack();
                            return response()->json([
                                'result' => false,
                                'order_id' => 0,
                                'message' => ('The requested pre-order quantity is not available for ') . $product->name
                            ], 406);
                        }
                    }
                    $order_detail = new OrderDetail;
                    $order_detail->order_id = $order->id;
                    $order_detail->seller_id = $product->user_id;
                    $order_detail->product_id = $product->id;
                    $order_detail->variation = empty($product_variation) ? null : $product_variation;
                    $order_detail->price = $price * $orderDetail['quantity'];
                    $order_detail->shipping_cost = $request['shipping_cost'];
                    $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $price;

                    $order_detail->delivery_status = 'pending';

                    $shipping += $order_detail->shipping_cost;

                    $order_detail->quantity = $orderDetail['quantity'];
                    $order_detail->save();

                    $product->num_of_sale = $product->num_of_sale + $orderDetail['quantity'];
                    $product->save();

                    $order->seller_id = $product->user_id;
                }

                $order->grand_total = $subtotal + $tax + $shipping;
                $combined_order->grand_total += $order->grand_total;
                $order->save();

                // Adjust Ordered Product Stocks
                event(new OrderPlaced($order));
                $combined_order->save();

                DB::commit();
                logOrder($order, 'updated');

                return response()->json([
                    'result' => true,
                    'message' => ('Order updated successfully'),
                    'order_id' => $order->id,
                ], 200);
            }

            return response()->json([
                'result' => false,
                'message' => ('No order details found'),
                'order_id' => 0,
            ], 422);
        }catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return response()->json([
                'result' => false,
                'order_id' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        // dd($request->all());
        try{
            $userId = $request->header('id');
            $userEmail = $request->header('email');

            $user = User::find($userId);

            $order = Order::merchant()
                ->whereNotNull('merchant_order_id')
                ->where('merchant_order_id', $request->platform_order_id)
                ->where('user_id', $user->id)
                ->first();
            if(!$order) {
                return response()->json([
                    'result' => false,
                    'message' => ('Order not found'),
                ], 404);
            }

            if(in_array($order->delivery_status, ['pending', 'processing', 'confirmed', 'hold', 'packaging']) && $request->delivery_status === 'cancelled') {
                $request->merge(['status' => 'cancelled']);
                app(BaseOrderController::class)->change_status($order, $request);
                return response()->json([
                    'result'=> true,
                    'message'=> ('Order has been cancelled'),
                ]);
            } elseif(!in_array($order->delivery_status, ['pending', 'processing', 'confirmed', 'hold', 'packaging']) && $request->delivery_status === 'cancelled') {
                return response()->json([
                    'result'=> false,
                    'message'=> ('Order cannot be cancelled'),
                ], 422);
            } else {
                return response()->json([
                    'result'=> true,
                    'message'=> ('Order status updated successfully'),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating order status from merchant api : ' . $e->getMessage());
            return response()->json([
                'result'=> false,
                'message'=> 'Server Error. Please try again later.',
            ],500);
        }
    }
}
