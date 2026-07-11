<?php

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\ClubPointController;
use App\Http\Controllers\CommissionController;
use App\Jobs\KireibdJob;
use App\Jobs\UpdateProductStockForRokomari;
use App\Models\Addon;
use App\Models\Address;
use App\Models\Advertizement;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\Category;
use App\Models\City;
use App\Models\CombinedOrder;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Currency;
use App\Models\Customergroup;
use App\Models\Customeringroup;
use App\Models\CustomerPackage;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductsClosingStock;
use App\Models\ProductStock;
use App\Models\Shop;
use App\Models\SmsLog;
use App\Models\Translation;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wallet;
use App\Utility\CategoryUtility;
use App\Utility\NotificationUtility;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

if(!function_exists('createOrUpdateClosingStock')){
    function createOrUpdateClosingStock($product_variation, $product_id, $variant_id, $yesterday, $from, $to, $fromx, $tox) {
        $opening_purchase = DB::table('purchase_order_item')
            ->join('purchase_order', 'purchase_order.id', '=', 'purchase_order_item.purchase_order_id')
            ->where('product_id', $product_id)
            ->where('variant', $variant_id)
            ->where('purchase_order.purchase_date', '<', $fromx)
            ->sum('qty');
        $purchases = DB::table('purchase_order_item')
            ->join('purchase_order', 'purchase_order.id', '=', 'purchase_order_item.purchase_order_id')
            ->where('product_id', $product_id)
            ->where('variant', $variant_id)
            ->whereBetween('purchase_order.purchase_date', [$fromx, $tox])
            ->sum('qty');
        // dd($purchases);
        $opening_minus_adjustment = DB::table('stock_adjust_items')
            ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
            ->where('stock_adjust_items.product_id', $product_id)
            ->where('stock_adjust_items.variant', $variant_id)
            ->where(function ($query) {
                $query->where('stock_adjust.sa_type', 'damage')
                    ->orWhere('stock_adjust.sa_type', 'others')
                    ->orWhere(function($q) {
                        $q->where('stock_adjust.sa_type', 'transfer')
                        ->where('stock_adjust_items.adjust_type', 'subtract');
                    });
            })
            ->where('stock_adjust.sa_date', '<', $fromx)
            ->sum('stock_adjust_items.qty');

        $minus_adjustments = DB::table('stock_adjust_items')
            ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
            ->where('stock_adjust_items.product_id', $product_id)
            ->where('stock_adjust_items.variant', $variant_id)
            ->where(function ($query) {
                $query->where('stock_adjust.sa_type', 'damage')
                    ->orWhere('stock_adjust.sa_type', 'others')
                    ->orWhere(function($q) {
                        $q->where('stock_adjust.sa_type', 'transfer')
                        ->where('stock_adjust_items.adjust_type', 'subtract');
                    });
            })
            ->whereBetween('stock_adjust.sa_date', [$fromx, $tox])
            ->sum('stock_adjust_items.qty');

        $opening_plus_adjustment = DB::table('stock_adjust_items')
            ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
            ->where('stock_adjust_items.product_id', $product_id)
            ->where('stock_adjust_items.variant', $variant_id)
            ->where(function($query) {
                $query->where('stock_adjust.sa_type', 'returned')
                    ->orWhere(function($q) {
                        $q->where('stock_adjust.sa_type', 'transfer')
                        ->where('stock_adjust_items.adjust_type', 'add');
                    });
            })
            ->where('stock_adjust.sa_date', '<', $fromx)
            ->sum('stock_adjust_items.qty');

        $plus_adjustments = DB::table('stock_adjust_items')
            ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
            ->where('stock_adjust_items.product_id', $product_id)
            ->where('stock_adjust_items.variant', $variant_id)
            ->where(function($query) {
                $query->where('stock_adjust.sa_type', 'returned')
                    ->orWhere(function($q) {
                        $q->where('stock_adjust.sa_type', 'transfer')
                        ->where('stock_adjust_items.adjust_type', 'add');
                    });
            })
            ->whereBetween('stock_adjust.sa_date', [$fromx, $tox])
            ->sum('stock_adjust_items.qty');

            empty($product_variation) ? $opening_sell = DB::table('order_details')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('order_details.product_id', $product_id)
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'preorder')
                ->orWhere('order_details.delivery_status', '<>', 'preorder');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'cancelled')
                ->orWhere('order_details.delivery_status', '<>', 'cancelled');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'returned')
                ->orWhere('order_details.delivery_status', '<>', 'returned');
        })
        ->where('orders.created_at', '<', $from)
        ->sum('order_details.quantity')
        : $opening_sell = DB::table('order_details')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('order_details.product_id', $product_id)
        ->where('order_details.variation', $product_variation)
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'preorder')
                ->orWhere('order_details.delivery_status', '<>', 'preorder');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'cancelled')
                ->orWhere('order_details.delivery_status', '<>', 'cancelled');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'returned')
                ->orWhere('order_details.delivery_status', '<>', 'returned');
        })
        ->where('orders.created_at', '<', $from)
        ->sum('order_details.quantity');

            empty($product_variation) ? $sales = DB::table('order_details')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('order_details.product_id', $product_id)
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'preorder')
                ->orWhere('order_details.delivery_status', '<>', 'preorder');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'cancelled')
                ->orWhere('order_details.delivery_status', '<>', 'cancelled');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'returned')
                ->orWhere('order_details.delivery_status', '<>', 'returned');
        })
        ->whereBetween('orders.created_at', [$from, $to])
        ->sum('order_details.quantity')
        : $sales = DB::table('order_details')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('order_details.product_id', $product_id)
        ->where('order_details.variation', $product_variation)
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'preorder')
                ->orWhere('order_details.delivery_status', '<>', 'preorder');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'cancelled')
                ->orWhere('order_details.delivery_status', '<>', 'cancelled');
        })
        ->where(function ($query) {
            $query->where('orders.delivery_status', '<>', 'returned')
                ->orWhere('order_details.delivery_status', '<>', 'returned');
        })
        ->whereBetween('orders.created_at', [$from, $to])
        ->sum('order_details.quantity');

        // if ($data) {
            // dd($data);
        $openStock = ($opening_purchase + $opening_plus_adjustment) - ($opening_sell + $opening_minus_adjustment);

        $adjustments = $plus_adjustments - $minus_adjustments;
        $closingStock = $openStock + $purchases - $sales + $adjustments;

        ProductsClosingStock::updateOrCreate(
            [
                'product_id' => $product_id,
                'variant' => $variant_id,
                'date'       => Carbon::parse($yesterday)->format('Y-m-d 23:59:59'),
            ],
            [
                'closing_stock'                 => $closingStock,
                'last_opening_purchase'         => $opening_purchase,
                'last_opening_sale'             => $opening_sell,
                'last_opening_plus_adjustment'  => $opening_plus_adjustment,
                'last_opening_minus_adjustment' => $opening_minus_adjustment,
            ]
        );
    }
}

if (! function_exists('is_url')) {
    function is_url(string $url)
    {
        if (! filled($url)) {
            return false;
        }

        return Validator::make(
            ['url' => $url],
            ['url' => 'url']
        )->passes();
    }
}
if (! function_exists('logOrder')) {
    function logOrder(Order $order, string $action, ?string $message = null)
    {
        try {
            $log = new \App\Models\OrderLog;
            $log->order_id = $order->id;
            $log->managed_by = Auth::id() ?? null;
            $log->action = $action;
            $log->message = ! empty($message) ? $message : log_order_message($order, $action);
            $log->save();
        } catch (\Exception $e) {
            Log::error('Failed to log order - '.$e->getMessage());
        }
    }
}

if (! function_exists('log_order_message')) {
    function log_order_message($order, $action)
    {
        $message = 'Order '.$order->code;

        switch ($action) {
            case 'viewed':
                $message .= ' was viewed';
                break;
            case 'created':
                $message .= ' has been created';
                break;
            case 'updated':
                $message .= ' has been updated';
                break;
            case 'deleted':
                $message .= ' has been deleted';
                break;
            case 'packaged':
                $message .= ' has been packaged';
                break;
            case 'payment_status':
                $message .= ' payment status updated to '.str_replace('_', ' ', $order->payment_status);
                break;
            case 'delivery_status':
                $message .= ' delivery status updated to '.str_replace('_', ' ', $order->delivery_status);
                break;
            default:
                // $message .= ' has been updated';
                return null;
        }

        // $message .= ' by ' . ucwords(auth()->user()->name) . ' at ' . date('d-m-Y h:i A');
        return translate($message);
    }
}

if (! function_exists('any_in_array')) {
    function any_in_array(array $search, array $searchIn)
    {
        foreach ($search as $item) {
            if (in_array($item, $searchIn)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('pathao_success_rate')) {
    function pathao_success_rate(string $phone): array
    {
        try {
            $successRateRequest = new \Enan\PathaoCourier\Requests\PathaoUserSuccessRateRequest([
                'phone' => $phone,
            ]);

            $response = \Enan\PathaoCourier\Facades\PathaoCourier::GET_USER_SUCCESS_RATE($successRateRequest);

            return $response['data'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (! function_exists('get_customer_success_rate')) {
    function get_customer_success_rate(?int $user_id, ?string $phone)
    {
        if (is_null($user_id) && is_null($phone)) {
            return [
                'label' => 'Customer Success Rate',
                'success_rate' => 0,
                'total_orders' => 0,
                'delivered_orders' => 0,
                'returned_orders' => 0,
                'message' => '',
            ];
        }

        $orders = Order::where('order_type', '!=', 'merchant')
            ->when($user_id, function ($query) use ($user_id) {
                $query->whereNotNull('user_id')->where('user_id', $user_id);
            })
            ->when(! is_null($phone), function ($query) use ($phone) {
                $query->whereJsonContains('shipping_address', ['phone' => $phone]);
            })
            ->get();

        $message = '';
        if ($orders->isNotEmpty()) {
            $totalOrders = $orders->count();
            $deliveredOrders = $orders->where('delivery_status', 'delivered')->count();
            $returnedOrders = $orders->where('delivery_status', 'returned')->count();

            // $deliveredOrders = 0;
            // $returnedOrders = 1;
            // $successRate = ($deliveredOrders / $totalOrders) * 100;
            try {
                $successRate = ($returnedOrders / $deliveredOrders) * 100;
            } catch (DivisionByZeroError $e) {
                $successRate = 0;
            } catch (Exception $e) {
                $successRate = 0;
            }

            if ($deliveredOrders > 0 && $successRate <= 0) {
                $successRate = 100;
            } elseif ($successRate >= 100) {
                $successRate = 0;
            }

            if ($successRate <= 0) {
                $message = ($deliveredOrders > 0 && $deliveredOrders == $returnedOrders) ? translate('Bad') : '';
            } elseif ($successRate == 100 || $successRate <= 25) {
                $message = translate('Excellent');
            } elseif ($successRate <= 90) {
                $message = translate('Good');
            } else {
                $message = translate('Average');
            }

            return [
                'label' => 'Customer Success Rate',
                'success_rate' => (int) round($successRate),
                'total_orders' => $totalOrders,
                'delivered_orders' => $deliveredOrders,
                'returned_orders' => $returnedOrders,
                'message' => $message,
            ];
        }

        return [
            'label' => 'Customer Success Rate',
            'success_rate' => 0,
            'total_orders' => 0,
            'delivered_orders' => 0,
            'returned_orders' => 0,
            'message' => $message,
        ];
    }
}

if (! function_exists('get_courier_success_rate')) {
    function get_courier_success_rate($phone)
    {
        if (is_null($phone) || empty($phone)) {
            return null;
        }

        // Cache::forget("courier_success_rate_{$phone}");
        return Cache::remember("courier_success_rate_{$phone}", 60, function () use ($phone) {
            $model = \App\Models\CourierSuccessRate::where('phone', $phone)->first();

            if (! $model) {
                return null;
            }

            return [
                'label' => 'Courier Success Rate',
                'success_rate' => $model->success_rate ?? 0,
                'total_orders' => array_sum(array_map(fn ($item) => $item['total_parcels'] ?? 0, $model->summary ?? [])),
                'delivered_orders' => array_sum(array_map(fn ($item) => $item['delivered_parcels'] ?? 0, $model->summary ?? [])),
                'returned_orders' => array_sum(array_map(fn ($item) => $item['returned_parcels'] ?? 0, $model->summary ?? [])),
                'summary' => $model->summary ?? [],
            ];
        });
    }
}

if (! function_exists('greetings')) {
    function greetings()
    {
        $hour = now()->hour;

        if ($hour >= 5 && $hour < 12) {
            return 'Good Morning';
        } elseif ($hour >= 12 && $hour < 14) {
            return 'Good Noon';
        } elseif ($hour >= 14 && $hour < 17) {
            return 'Good Afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            return 'Good Evening';
        } else {
            return 'Good Night';
        }
    }
}

if (! function_exists('rewrite_url')) {
    /**
     * Rewrite URL
     */
    function rewrite_url(string $url, string $redirect_to, bool $update = false)
    {
        if ($url != null && $redirect_to != null && $url !== $redirect_to) {
            $existingRules = \App\Models\RewriteUrl::where('url', $redirect_to)
                ->where('redirect_to', $url)->get();
            if ($existingRules->isNotEmpty()) {
                foreach ($existingRules as $existingRule) {
                    $existingRule->delete();
                }
            }

            try {
                if ($update) {
                    $rewrite_url = \App\Models\RewriteUrl::where('url', $url)->first();
                    if ($rewrite_url) {
                        $rewrite_url->redirect_to = $redirect_to;
                        $rewrite_url->save();
                    }
                } else {
                    $rewrite_url = new \App\Models\RewriteUrl;
                    $rewrite_url->url = $url;
                    $rewrite_url->redirect_to = $redirect_to;
                    $rewrite_url->save();
                }
            } catch (\Exception $e) {
                Log::error('Failed to store/update rewrite urls - '.$e->getMessage());
            }
        } else {
            Log::info('URL or Redirect to is empty');
        }
    }
}

if (! function_exists('remove_rewrite_url')) {
    function remove_rewrite_url($url)
    {
        if ($url != null) {
            $rewrite_url = \App\Models\RewriteUrl::query()
                ->where(function ($query) use ($url) {
                    $query->where('url', $url)
                        ->orWhere('redirect_to', $url);
                })->first();
            if ($rewrite_url) {
                $rewrite_url->delete();
            }
        } else {
            Log::info('URL is empty');
        }
    }
}

if (! function_exists('get_copy_content')) {
    function get_copy_content($product)
    {
        if (! filled($product)) {
            return null;
        }
        if (is_array($product)) {
            return get_copy_content_array($product);
        }
        $normalPrice = home_price($product);
        $discountPrice = home_discounted_price($product);
        $info = '';
        $info .= '🛍 Product Name : '.($product->name ?? '')."\n\n";
        $info .= '💰 Price: '.single_price($product->unit_price)."\n";
        if ($normalPrice != $discountPrice) {
            $info .= '🔥 Discount Price: '.$discountPrice."\n";
        }
        $info .= "\n".'🛒 Order Now : '.to_frontend(route('product', $product->slug))."\n";

        return $info;
    }

    function get_copy_content_array($product)
    {
        if (! filled($product)) {
            return null;
        }

        // Convert to array and ensure it's accessible
        $product = Arr::accessible($product) ? $product : convertToArray($product);

        $normalPrice = home_price($product);
        $discountPrice = home_discounted_price($product);

        $info = '';
        $info .= '🛍 Product Name : '.Arr::get($product, 'name', '')."\n\n";
        $info .= '💰 Price: '.single_price(Arr::get($product, 'unit_price', 0))."\n";

        if ($normalPrice != $discountPrice) {
            $info .= '🔥 Discount Price: '.$discountPrice."\n";
        }

        $info .= "\n".'🛒 Order Now : '.to_frontend(route('product', Arr::get($product, 'slug', '')))."\n";

        return $info;
    }
}

if (! function_exists('generate_order_summary')) {
    function generate_order_summary(array|Collection $products, float $subtotal, ?float $tax, float $shipping_cost, ?float $discount, ?float $paid_amount, float $grand_total): ?string
    {
        $products = collect($products);
        if ($products->isEmpty()) {
            return '';
        }

        $summary = "👉ORDER SUMMARY \n\n";
        $summary .= "⭐PRODUCTS: (Product Name x Qty. = Price)\n\n";

        foreach ($products as $product) {
            $isGift = data_get($product, 'isGift', false);

            $name = data_get($product, 'name', 'Unknown Product');
            $quantity = (int) data_get($product, 'quantity', 0);
            $price = (float) data_get($product, 'price', 0);

            $summary .= $isGift ? "🎁 " : "📍 ";

            $summary .= sprintf(
                "%s x %d = %s\n\n",
                $name . ($isGift ? ' (Gift Item)' : ''),
                $quantity,
                single_price(max(0, $price * $quantity))
            );
        }

        $summary .= '🔴SUBTOTAL: '.single_price(max(0, $subtotal))."\n";
        if ($tax > 0) {
            $summary .= '🔴TAX: '.single_price(max(0, $tax))."\n";
        }
        if ($shipping_cost > 0) {
            $summary .= '🔴SHIPPING: '.single_price(max(0, $shipping_cost))."\n";
        }
        if ($discount > 0) {
            $summary .= '🔴DISCOUNT: (-)'.single_price(max(0, $discount))."\n";
        }
        if ($paid_amount > 0) {
            $summary .= '🔴PAID AMOUNT: (-)'.single_price(max(0, $paid_amount))."\n";
        }
        $summary .= '🛒GRAND TOTAL: '.single_price(max(0, $grand_total))."\n";

        return $summary;
    }
}

if (! function_exists('calculate_due')) {
    function calculate_due($order)
    {
        if (! $order) {
            return 0;
        }
        // Load payments relationship if not already loaded
        $order->loadMissing('payments');

        $totalPaid = $order->payments?->sum('amount') ?? 0;

        $due = (float) get_order_grand_total($order) - (float) $totalPaid;

        return max($due, 0);
    }
}
if (! function_exists('get_paid_amount')) {
    function get_paid_amount($order)
    {
        try{
            if (! $order) {
                return 0;
            }
            // Load payments relationship if not already loaded
            $order->loadMissing('payments');

            $totalPaid = $order->payments?->sum('amount') ?? 0;

            return max($totalPaid, 0);
        } catch (\Exception $e) {
            Log::error('Failed to calculate paid amount - '.$e->getMessage());
            return 0;
        }
    }
}
if (! function_exists('order_affected')) {
    function order_affected($order)
    {
        if (! $order) {
            return;
        }
        // Load orderDetails relationship if not already loaded
        $order->loadMissing('orderDetails');
        $orderItems = $order->orderDetails;
        $subtotal = $orderItems->sum('price');
        $tax = $orderItems->sum('tax');
        $shipping_cost = $orderItems[0]?->shipping_cost ?? 0;
        $grand_total = ($subtotal + $tax + $shipping_cost) - $order->coupon_discount;
        $paidAmount = $order->payments?->sum('amount') ?? 0;
        $order->grand_total = $grand_total;
        if ($paidAmount > 0 && $grand_total > $paidAmount) {
            $order->payment_status = 'partial';
            $order->due_amount = $grand_total - $paidAmount;
        } elseif ($paidAmount > 0 && $grand_total <= $paidAmount) {
            $order->payment_status = 'paid';
            $order->due_amount = 0;
        } else {
            $order->payment_status = 'unpaid';
            $order->due_amount = $grand_total;
        }
        $order->save();

        // dd($grand_total);
    }
}
if (! function_exists('get_order_grand_total')) {
    function get_order_grand_total($order)
    {
        try {
            if (! $order) {
                return 0;
            }
            // Load orderDetails relationship if not already loaded
            $order->loadMissing('orderDetails');

            $total = $order->orderDetails->sum('price') + $order->orderDetails->sum('tax') + $order->orderDetails->sum('shipping_cost') - ($order->coupon_discount ?? 0 + $order->reward_point_discount ?? 0);

            return max($total, 0);
        } catch (\Exception $e) {
            Log::error('Failed to calculate order grand total - '.$e->getMessage());
            return 0;
        }
    }
}
if (! function_exists('get_order_due_amount')) {
    function get_order_due_amount($order)
    {
        try {
            if (! $order) {
                return 0;
            }
            // Load orderDetails relationship if not already loaded
            $order->loadMissing('orderDetails');
            $order->loadMissing('payments');

            $totalPaid = $order->payments?->sum('amount') ?? 0;
            $due = get_order_grand_total($order) - $totalPaid;

            return max($due, 0);
        } catch (\Exception $e) {
            Log::error('Failed to calculate order due amount - '.$e->getMessage());
            return 0;
        }
    }
}
if (! function_exists('order_payment_status')) {
    function order_payment_status($order)
    {
        if ($order->payment_status == 'paid') {
            $color = 'green';
            $payment_status = 'Paid';
        } elseif ($order->payment_status == 'unpaid') {
            $color = 'red';
            $payment_status = 'Unpaid';
        } else {
            $color = 'orange';
            $payment_status = 'Due';
        }

        return '<span style="font-size:20px; font-weight:bold; color:'.$color.'">'.strtoupper($payment_status).'</span>';
    }
}

if (! function_exists('unlock_all_orders_except')) {
    /**
     * Unlock all orders except the current one which are locked by the current user
     *
     * @return bool
     */
    function unlock_all_orders_except(Order $order)
    {
        if (Auth::check()) {
            Order::where('id', '!=', $order->id)
                ->where('locked_by', auth()->user()->id)
                ->get()
                ->each(function ($order) {
                    $order->unlock();
                });

            return true;
        }

        return false;
    }
}

if (! function_exists('order_status_badge')) {
    function order_status_badge(Order $order)
    {
        if ($order->delivery_status == 'delivered' || $order->delivery_status == 'confirmed') {
            return '<span class="badge badge-inline badge-success">'.ucfirst(str_replace('_', ' ', $order->delivery_status)).'</span>';
        } elseif ($order->delivery_status == 'processing') {
            return '<span class="badge badge-inline badge-info">'.ucfirst(str_replace('_', ' ', $order->delivery_status)).'</span>';
        } elseif ($order->delivery_status == 'cancelled') {
            return '<span class="badge badge-inline badge-danger">'.ucfirst(str_replace('_', ' ', $order->delivery_status)).'</span>';
        } else {
            return '<span class="badge badge-inline badge-warning">'.ucfirst(str_replace('_', ' ', $order->delivery_status)).'</span>';
        }
    }
}

if (! function_exists('payment_status_badge')) {
    function payment_status_badge(Order $order)
    {
        if ($order->payment_status == 'paid') {
            return '<span class="badge badge-inline badge-success">'.ucfirst($order->payment_status).'</span>';
        } else {
            return '<span class="badge badge-inline badge-danger">'.ucfirst($order->payment_status).'</span>';
        }
    }
}

if (! function_exists('write_env')) {
    function write_env(array $data)
    {
        $envFile = base_path('.env');
        $envContents = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $keyPattern = '/^'.preg_quote($key, '/').'=.*/m';

            // Convert value to string (handle booleans, null, etc.)
            $value = is_numeric($value) || is_bool($value) ? var_export($value, true) : '"'.addslashes($value).'"';

            if (preg_match($keyPattern, $envContents)) {
                // Update existing key
                $envContents = preg_replace($keyPattern, "{$key}={$value}", $envContents);
            } else {
                // Append new key
                $envContents .= "\n{$key}={$value}";
            }
        }

        // Write back to .env file
        file_put_contents($envFile, $envContents);

        Artisan::call('config:clear');
    }
}

if (! function_exists('limit_text')) {
    function limit_text($text, $startLength = 20, $endLength = 10, $separator = '...')
    {
        if (strlen($text) > ($startLength + $endLength)) {
            return substr($text, 0, $startLength).$separator.substr($text, -$endLength);
        }

        return $text;
    }
}
if (! function_exists('get_order_count_based_delivery_status')) {
    function get_order_count_based_delivery_status($inhouse = null)
    {
        $counts = Order::selectRaw('
            COUNT(CASE WHEN delivery_status = "preorder" THEN 1 END) as preorder,
            COUNT(CASE WHEN delivery_status = "pending" AND order_type != "merchant" THEN 1 END) as pending,
            COUNT(CASE WHEN delivery_status = "processing" THEN 1 END) as processing,
            COUNT(CASE WHEN delivery_status = "hold" THEN 1 END) as hold,
            COUNT(CASE WHEN delivery_status = "confirmed" THEN 1 END) as confirmed,
            COUNT(CASE WHEN delivery_status = "packaging" THEN 1 END) as packaging,
            COUNT(CASE WHEN delivery_status = "picked_up" THEN 1 END) as picked_up,
            COUNT(CASE WHEN delivery_status = "on_the_way" THEN 1 END) as on_the_way,
            COUNT(CASE WHEN delivery_status = "on_delivery" THEN 1 END) as on_delivery,
            COUNT(CASE WHEN delivery_status = "delivered" THEN 1 END) as delivered,
            COUNT(CASE WHEN delivery_status = "returned" THEN 1 END) as returned,
            COUNT(CASE WHEN delivery_status = "cancelled" THEN 1 END) as cancelled,
            COUNT(CASE WHEN order_type = "merchant" AND delivery_status = "pending" THEN 1 END) as merchant
        ');
        if ($inhouse == null) {
            return $counts->first()->toArray();
        } else {
            $admin_user_id = User::where('user_type', 'admin')->first()->id;

            return $counts->where('seller_id', $admin_user_id)->first()->toArray();
        }
    }
}

// sensSMS function for OTP
if (! function_exists('sendSMS')) {
    function sendSMS(string $to, string $text, ?int $template_id = null, ?string $type = null, ?int $user_id = null)
    {
        $ans = SmsLog::where('phone', $to)->where('templateId', $template_id)->where('created_at', '>=', Carbon::now()->subDay()->toDateTimeString())->get();

        if (count($ans) > 3) {
            return true;
        } else {
            SmsLog::create([
                'user_id' => $user_id,
                'type' => $type ?: 'otp',
                'phone' => $to,
                'body' => $text,
                'templateId' => $template_id,
                'ip' => request()->ip(),
            ]);
            return SendSMSUtility::sendSMS($to, config('app.name'), $text, $template_id);
        }
    }
}

// highlights the selected navigation on admin panel
if (! function_exists('areActiveRoutes')) {
    function areActiveRoutes(array $routes, $output = 'active')
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) {
                return $output;
            }
        }

        return '';
    }
}

// highlights the selected navigation on frontend
if (! function_exists('areActiveRoutesHome')) {
    function areActiveRoutesHome(array $routes, $output = 'active')
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) {
                return $output;
            }
        }
    }
}

// highlights the selected navigation on frontend
if (! function_exists('default_language')) {
    function default_language()
    {
        return env('DEFAULT_LANGUAGE');
    }
}

/**
 * Save JSON File
 *
 * @return Response
 */
if (! function_exists('convert_to_usd')) {
    function convert_to_usd($amount)
    {
        $currency = Currency::find(get_setting('system_default_currency'));

        return (floatval($amount) / floatval($currency->exchange_rate)) * Currency::where('code', 'USD')->first()->exchange_rate;
    }
}

if (! function_exists('convert_to_kes')) {
    function convert_to_kes($amount)
    {
        $currency = Currency::find(get_setting('system_default_currency'));

        return (floatval($amount) / floatval($currency->exchange_rate)) * Currency::where('code', 'KES')->first()->exchange_rate;
    }
}

// filter products based on vendor activation system
if (! function_exists('filter_products')) {
    function filter_products(Builder $products)
    {
        $verified_sellers = verified_sellers_id();
        if (get_setting('vendor_system_activation') == 1) {
            $products = $products->where('approved', '1')->where('published', '1')->where('auction_product', 0)->where(function ($p) use ($verified_sellers) {
                $p->where('added_by', 'admin')->orWhere(function ($q) use ($verified_sellers) {
                    $q->whereIn('user_id', $verified_sellers);
                });
            });
        } else {
            $products = $products->where('published', '1')->where('auction_product', 0)->where('added_by', 'admin');
        }

        return $products;
    }
}

if (! function_exists('shouldHideStockOutProducts')) {
    function shouldHideStockOutProducts(): bool
    {
        return intval(json_decode(get_setting('show_stock_out_products'))) != 1;
    }
}

if (! function_exists('filter_stock_out_products')) {
    function filter_stock_out_products($products)
    {
        if ($products) {
            return $products->filter(function ($product) {
                $thisStocks = collect($product->stocks);
                if ($thisStocks->isNotEmpty()) {
                    $stockQuantity = $thisStocks->first()->qty;
                } else {
                    $stockQuantity = 0;
                }
                if (check_flash_deal_product(collect($product))) {
                    $stockQuantity = $product->flash_deal_product->quantity ?? 0;
                }

                return $stockQuantity > 0;
            });
        } else {
            return collect();
        }
    }
}

// cache products based on category
if (! function_exists('get_cached_products')) {
    function get_cached_products($category_id = null, $type = null)
    {
        $start = microtime(true);
        // Define the file path where want to store the JSON file
        $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');

        $products = collect();
        if (file_exists($cachedProductsFilePath)) {
            $jsonData = file_get_contents($cachedProductsFilePath);
            $products = collect(json_decode($jsonData, false));
        }

        if ($products->isEmpty() || ! file_exists($cachedProductsFilePath)) {
            $items = Product::with('thumbnail_image', 'stocks', 'productprices', 'flash_deal_product.flash_deals')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
            $jsonData = $items->toJson();
            // file_put_contents($cachedProductsFilePath, $jsonData);
            Storage::disk('public')->put('products/get_cached_products.json', $jsonData);
            $products = collect(json_decode($jsonData, false));
        }

        $verified_sellers = verified_sellers_id();
        if (get_setting('vendor_system_activation') == 1) {
            $products = $products->where('added_by', 'admin')->whereIn('user_id', $verified_sellers);
        } else {
            $products = $products->where('added_by', 'admin');
        }

        if ($category_id != null) {
            return Cache::remember($type.'products-category-'.$category_id, 60 * 60, function () use ($category_id, $products, $start) {
                $category_ids = CategoryUtility::children_ids($category_id);
                $category_ids[] = $category_id;

                $end = microtime(true);
                $executionTime = $end - $start;

                // dd(number_format($executionTime, 2) . ' seconds');
                return $products->whereIn('category_id', $category_ids)->take(12); // ->shuffle();
            });
        } else {
            $end = microtime(true);
            $executionTime = $end - $start;

            // dd(number_format($executionTime, 2) . ' seconds');
            return Cache::remember('products', 60 * 60, function () use ($products) {
                return $products->take(12); // ->shuffle();
            });
        }
    }
}

if (! function_exists('get_db_products')) {
    function get_db_products($category_id = null, $type = null)
    {
        $products = Product::with('thumbnail_image', 'stocks', 'latestStock', 'productprices', 'flash_deal_product.flash_deals')
            ->where('published', 1)
            ->where('approved', '1')
            ->where('auction_product', 0)
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })
            ->when(filled($type) && $type == 'home' && shouldHideStockOutProducts(), function ($query) {
                return $query->availableInStock();
            })
            ->limit(12)
            ->get();

        return $products;
        // return $products->take(12);
    }
}

if (! function_exists('verified_sellers_id')) {
    function verified_sellers_id()
    {
        return Cache::rememberForever('verified_sellers_id', function () {
            return \App\Models\Seller::where('verification_status', 1)->pluck('user_id')->toArray();
        });
    }
}

if (! function_exists('get_system_default_currency')) {
    function get_system_default_currency()
    {
        return Cache::remember('system_default_currency', 86400, function () {
            return Currency::findOrFail(get_setting('system_default_currency'));
        });
    }
}

// gets currency symbol
if (! function_exists('currency_symbol')) {
    function currency_symbol()
    {
        if (Session::has('currency_symbol')) {
            return Session::get('currency_symbol');
        }

        return get_system_default_currency()->symbol;
    }
}

// formats currency
if (! function_exists('format_price')) {
    function format_price($price)
    {
        if (get_setting('decimal_separator') == 1) {
            $fomated_price = number_format($price, get_setting('no_of_decimals'));
        } else {
            $fomated_price = number_format($price, get_setting('no_of_decimals'), ',', ' ');
        }

        if (get_setting('symbol_format') == 1) {
            return currency_symbol().$fomated_price;
        } elseif (get_setting('symbol_format') == 3) {
            return currency_symbol().' '.$fomated_price;
        } elseif (get_setting('symbol_format') == 4) {
            return $fomated_price.' '.currency_symbol();
        }

        return $fomated_price.currency_symbol();
    }
}

// converts currency to home default currency
if (! function_exists('convert_price')) {
    function convert_price($price)
    {
        if (Session::has('currency_code') && (Session::get('currency_code') != get_system_default_currency()->code)) {
            $price = floatval($price) / floatval(get_system_default_currency()->exchange_rate);
            $price = floatval($price) * floatval(Session::get('currency_exchange_rate'));
        }

        return $price;
    }
}

// formats price to home default price with convertion
if (! function_exists('single_price')) {
    function single_price($price)
    {
        return format_price(convert_price($price));
    }
}

if (! function_exists('get_shipping_price')) {
    function get_shipping_price($price, $user_id = null)
    {
        if (is_null($user_id) || Str::startsWith($user_id, 'tmp')) {
            return $price;
        }

        $user_info = ! is_null($user_id) ? User::find($user_id) : Auth::user();
        if ($user_info && $user_info->customeringroup) {
            return $user_info->customeringroup?->group?->delivery_discount_amount ?? $price;
        }

        return $price;
    }
}

if (! function_exists('discount_in_percentage')) {
    function discount_in_percentage($product)
    {
        try {
            $base = home_base_price($product, false);
            $reduced = home_discounted_base_price($product, false);
            $discount = $base - $reduced;
            $dp = ($discount * 100) / $base;

            return round($dp);
        } catch (DivisionByZeroError $e) {
            return 0;
        } catch (Exception $e) {

        }

        return 0;
    }
}

if (! function_exists('check_flash_deal_product')) {
    // function check_flash_deal_product($product)
    // {
    //     $flash_deals = [];
    //     if ($product instanceof \Illuminate\Support\Collection) {
    //         // $product is a Laravel collection
    //         $flashDealProduct = $product->get('flash_deal_product');
    //         if ($flashDealProduct !== null) {
    //             $getflashdealproduct = (object)$product->get('flash_deal_product');
    //             $flash_deals = $getflashdealproduct->flash_deals;
    //         }else{
    //             $flash_deals = null;
    //         }
    //     } elseif ($flash_deals != null) {
    //         // $product is not a Laravel collection
    //         $flash_deals = !empty($product->flash_deal_product) ? $product->flash_deal_product->flash_deals : null;
    //     }

    //     try {
    //         if($flash_deals->id != null && strtotime(date('Y-m-d H:i:s')) >= $flash_deals->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deals->end_date && $flash_deals->status==1){
    //             return 1;
    //         }else{
    //             return 0;
    //         }
    //     } catch (Exception $e) {

    //     }
    //     return 0;
    // }

    function check_flash_deal_product($product)
    {
        try {
            if ($product->flash_deal_product ?? null) {
                $flash_deal = $product->flash_deal_product->flash_deals ?? null;
                if ($flash_deal && $flash_deal->status == 1 && strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date) {
                    return 1;
                }
            }

            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (! function_exists('is_valid_flashdeal')) {
    function is_valid_flashdeal($flash_deal = null)
    {
        if (is_null($flash_deal)) {
            return false;
        }
        try {
            if ($flash_deal && $flash_deal->status == 1 && strtotime(date('Y-m-d H:i:s')) >= $flash_deal->start_date && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}

// Pre-order functions
if (! function_exists('check_preorder_product')) {
    function check_preorder_product($product)
    {
        // && preorder_product_count($product) < $product->preorder_max_qty
        try {
            if ($product->pre_order != 0 && strtotime(date('Y-m-d H:i:s')) >= $product->preorder_start_date && strtotime(date('Y-m-d H:i:s')) <= $product->preorder_end_date && $product->allow_stock_out_purchases == 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

        }

        return false;
    }
}

if (! function_exists('preorder_product_count')) {
    function preorder_product_count($product)
    {
        try {
            $from = date('Y-m-d H:i:s', $product->preorder_start_date);
            $to = date('Y-m-d H:i:s', $product->preorder_end_date);
            $count = OrderDetail::where('product_id', $product->id)->whereBetween('created_at', [$from, $to])->sum('quantity');

            return $count;
        } catch (Exception $e) {

        }

        return 0;
    }
}

if (! function_exists('has_preorder_product_to_cart')) {
    function has_preorder_product_to_cart($carts)
    {
        // Log::info($carts);
        $status = false;
        try {
            if (count($carts) > 0) {
                $preordercount = 0;
                foreach ($carts as $key => $cartItem) {
                    if (check_preorder_product($cartItem->product)) {
                        $preordercount++;
                    }
                }
                if ($preordercount > 0) {
                    $status = true;
                }
            } else {
                $status = false;
            }

        } catch (Exception $e) {

        }

        return $status;
    }
}

if (! function_exists('has_regular_product_to_cart')) {
    function has_regular_product_to_cart($carts)
    {
        $status = false;
        try {
            if (count($carts) > 0) {
                $preordercount = 0;
                foreach ($carts as $key => $cartItem) {
                    if (! check_preorder_product($cartItem->product)) {
                        $preordercount++;
                    }
                }
                if ($preordercount > 0) {
                    $status = true;
                }
            } else {
                $status = false;
            }

        } catch (Exception $e) {

        }

        return $status;
    }
}

// Pre-order functions end

if (! function_exists('save_product_discount')) {
    function save_product_discount($request, $collum, $collum_id)
    {
        try {
            $date_var = explode(' to ', $request->date_range);
            $start_date = strtotime($date_var[0]);
            $end_date = strtotime($date_var[1]);
            Product::where($collum, $collum_id)->update(['discount_start_date' => $start_date, 'discount_end_date' => $end_date, 'discount' => $request->discount, 'discount_type' => $request->discount_type, 'min_order_amount' => 0, 'max_qty' => 0]);

            return 1;
        } catch (Exception $e) {

        }

        return 0;
    }
}

if (! function_exists('get_total_cart_amount_check')) {
    function get_total_cart_amount_check($user_id, $carts = null, $guest = false)
    {
        try {
            $data['error'] = 0;
            $data['error_message'] = '';
            $data['total_order_amount'] = 0;
            $data['min_order_amount'] = 0;
            // $user_id = Auth::user()->id;
            if (empty($carts)) {
                $userField = $guest ? 'temp_user_id' : 'user_id';
                $carts = Cart::with('product')->whereNotNull($userField)->where($userField, $user_id)->get();
            }
            $totalcartamount = 0;
            $totalerror = 0;
            $minorderamontarray = [0];
            if ($carts && count($carts) > 0) {
                foreach ($carts as $key => $cartItem) {
                    $product = $cartItem->product;
                    $totalcartamount = $totalcartamount + ($cartItem['price'] + $cartItem['tax']) * $cartItem['quantity'];
                }
                foreach ($carts as $key => $cartItem) {
                    $product = $cartItem->product;
                    if ($product->min_order_amount != 0) {
                        if ($totalcartamount < $product->min_order_amount) {
                            $totalerror++;
                            $minorderamontarray[] = $product->min_order_amount;
                        }
                    }
                }
                $data['error'] = $totalerror;
                $data['error_message'] = ('Minimum order amount to continue this order is ').single_price(max($minorderamontarray));
                $data['total_order_amount'] = $totalcartamount;
                $data['min_order_amount'] = floatval(max($minorderamontarray));
            }

            return $data;
        } catch (Exception $e) {

        }

        return 0;
    }
}
if (! function_exists('get_total_cart_amount')) {
    function get_total_cart_amount()
    {
        try {
            $user_id = Auth::user()->id;
            $carts = Cart::where('user_id', $user_id)->get();
            $totalcartamount = 0;
            if ($carts && count($carts) > 0) {
                foreach ($carts as $key => $cartItem) {
                    $totalcartamount = $totalcartamount + ($cartItem['price'] + $cartItem['tax']) * $cartItem['quantity'];
                }
            }

            return $totalcartamount;
        } catch (Exception $e) {

        }

        return 0;
    }
}

if (! function_exists('update_product_discount')) {
    function update_product_discount($request, $collum)
    {
        try {
            if ($request->status == 1) {
                Product::where($collum, $request->id)->update(['discount_start_date' => $request->start_date, 'discount_end_date' => $request->end_date, 'discount' => $request->discount, 'discount_type' => $request->discount_type]);
            } else {
                $start_date = strtotime(date('Y-m-d H:i:s', strtotime('-4 days')));
                $enddate = strtotime(date('Y-m-d H:i:s', strtotime('-2 days')));
                Product::where($collum, $request->id)->update(['discount_start_date' => $start_date, 'discount_end_date' => $enddate, 'discount' => $request->discount, 'discount_type' => $request->discount_type]);
            }

            return 1;
        } catch (Exception $e) {

        }

        return 0;
    }
}
if (! function_exists('update_flash_deal_discount')) {
    /**
     * ! N + 1 Problematic Code
     */
    function update_flash_deal_discount_old($request)
    {
        try {
            if ($request->status == 1) {
                if (count($request->flash_deal_products) > 0) {
                    foreach ($request->flash_deal_products as $dealpro) {
                        $product = Product::findOrFail($dealpro->product_id);
                        $product->discount_start_date = $request->start_date;
                        $product->discount_end_date = $request->end_date;
                        $product->save();
                    }
                }
            } else {
                $start_date = strtotime(date('Y-m-d H:i:s', strtotime('-4 days')));
                $enddate = strtotime(date('Y-m-d H:i:s', strtotime('-2 days')));
                foreach ($request->flash_deal_products as $dealpro) {
                    $product = Product::findOrFail($dealpro->product_id);
                    $product->discount_start_date = $start_date;
                    $product->discount_end_date = $enddate;
                    $product->save();
                }
            }

            return 1;
        } catch (Exception $e) {

        }

        return 0;
    }

    /**
     * ? Optimized Code
     */
    function update_flash_deal_discount(FlashDeal $flashDeal)
    {
        try {
            $flashDeal->loadMissing('flash_deal_products');
            $productIds = $flashDeal->flash_deal_products
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->toArray();

            if (empty($productIds)) {
                return 0;
            }

            if ($flashDeal->status == 1) {
                $startDate = $flashDeal->start_date;
                $endDate = $flashDeal->end_date;
            } else {
                $now = Carbon::now();
                $startDate = $now->copy()->subDays(4)->timestamp;
                $endDate = $now->copy()->subDays(2)->timestamp;
            }

            Product::whereIn('id', $productIds)->update([
                'discount_start_date' => $startDate,
                'discount_end_date' => $endDate,
            ]);

            return 1;
        } catch (Exception $e) {

        }

        return 0;
    }
}

// Shows Price on page based on low to high
if (! function_exists('home_price')) {
    function home_price($product, $formatted = true)
    {
        if (is_array($product)) {
            return home_price_array($product, $formatted);
        }
        // Log::info('Product in home_price: ', ['product' => $product]);
        $lowest_price = isset($product->unit_price) ? $product->unit_price : $product->main_price;
        $highest_price = isset($product->unit_price) ? $product->unit_price : $product->main_price;
        if (count($product->productprices) > 0) {
            $productprices = $product->productprices->where('start_qty', '<=', 1)->where('end_qty', '>=', 1)->first();
            if ($productprices) {
                $lowest_price = $productprices->price;
                $highest_price = $productprices->price;
            }
            // dd($price);
        }

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                }
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $lowest_price += ($lowest_price * $product_tax->tax) / 100;
                $highest_price += ($highest_price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $lowest_price += $product_tax->tax;
                $highest_price += $product_tax->tax;
            }
        }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)).' - '.format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price.' - '.$highest_price;
        }
    }

    function home_price_array($product, $formatted = true)
    {
        // Ensure product is array accessible
        $product = Arr::accessible($product) ? $product : convertToArray($product);

        // Initialize default values
        $lowest_price = $highest_price = 0;

        try {
            // Get base price using Arr::get with fallbacks
            $price = (float) Arr::first([
                Arr::get($product, 'unit_price'),
                Arr::get($product, 'main_price'),
                Arr::get($product, 'web_price'),
                0, // Default fallback
            ], function ($value) {
                return $value !== null;
            });

            $lowest_price = $highest_price = $price;

            // Handle product prices (quantity discounts) using Arr helpers
            $productPrices = Arr::get($product, 'productprices', []);
            $applicablePrices = Arr::where($productPrices, function ($price) {
                return Arr::has($price, ['start_qty', 'end_qty', 'price']) &&
                       $price['start_qty'] <= 1 &&
                       $price['end_qty'] >= 1;
            });

            if (! empty($applicablePrices)) {
                $firstPrice = (float) Arr::get(Arr::first($applicablePrices), 'price', $price);
                $lowest_price = $highest_price = $firstPrice;
            }

            // Handle variant products using Arr helpers
            if (Arr::get($product, 'variant_product', false)) {
                $stockPrices = Arr::pluck(Arr::get($product, 'stocks', []), 'price');

                if (! empty($stockPrices)) {
                    $lowest_price = min($lowest_price, min($stockPrices));
                    $highest_price = max($highest_price, max($stockPrices));
                }
            }

            // Apply taxes using Arr helpers
            foreach (Arr::get($product, 'taxes', []) as $tax) {
                $taxAmount = (float) Arr::get($tax, 'tax', 0);
                $taxType = Arr::get($tax, 'tax_type');

                if ($taxType === 'percent') {
                    $lowest_price += ($lowest_price * $taxAmount) / 100;
                    $highest_price += ($highest_price * $taxAmount) / 100;
                } elseif ($taxType === 'amount') {
                    $lowest_price += $taxAmount;
                    $highest_price += $taxAmount;
                }
            }
        } catch (Exception $e) {
            // Log error if needed
            Log::error('Error in home_price: '.$e->getMessage());
            $lowest_price = $highest_price = 0;
        }

        // Format the output
        return $formatted
            ? ($lowest_price == $highest_price
                ? format_price(convert_price($lowest_price))
                : format_price(convert_price($lowest_price)).' - '.format_price(convert_price($highest_price)))
            : $lowest_price.' - '.$highest_price;
    }
}

// Shows Price on page based on low to high with discount
if (! function_exists('home_discounted_price')) {
    function home_discounted_price($product, $formatted = true, $user_id = null)
    {
        if (is_array($product)) {
            return home_discounted_price_array($product, $formatted, $user_id);
        }
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        $group_lowest_price = $product->unit_price;
        $group_highest_price = $product->unit_price;

        if (count($product->productprices) > 0) {
            $productprices = $product->productprices->where('start_qty', '<=', 1)->where('end_qty', '>=', 1)->first();
            if ($productprices) {
                $lowest_price = $productprices->price;
                $highest_price = $productprices->price;
            }
            // dd($price);
        }
        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                    $group_lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                    $group_highest_price = $stock->price;
                }
            }
        }

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $lowest_price -= ($lowest_price * $product->discount) / 100;
                $highest_price -= ($highest_price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $lowest_price -= $product->discount;
                $highest_price -= $product->discount;
            }
        }
        if ($user_id != null) {
            $user_info = User::findOrFail($user_id);
            if ($user_info->customeringroup) {
                $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($user_info->customeringroup->group->discount_type == 'percent') {
                        $group_highest_price -= ($group_highest_price * $user_info->customeringroup->group->discount) / 100;
                        $group_lowest_price -= ($group_lowest_price * $user_info->customeringroup->group->discount) / 100;
                    } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                        $group_highest_price -= $user_info->customeringroup->group->discount;
                        $group_lowest_price -= $user_info->customeringroup->group->discount;
                    }
                    if ($discount_applicable) {
                        if ($highest_price < $group_highest_price) {
                            $highest_price = $highest_price;
                            $lowest_price = $lowest_price;
                        } else {
                            $highest_price = $group_highest_price;
                            $lowest_price = $group_lowest_price;
                        }
                    } else {
                        $highest_price = $group_highest_price;
                        $lowest_price = $group_lowest_price;
                    }
                }
            }
        } else {
            if (isset(Auth::user()->id)) {
                if (Auth::user()->customeringroup) {
                    $discount_status = Auth::user()->customeringroup->group->discount_status;
                    $start_date = Auth::user()->customeringroup->group->start_date;
                    $end_date = Auth::user()->customeringroup->group->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                        if (Auth::user()->customeringroup->group->discount_type == 'percent') {
                            $group_highest_price -= ($group_highest_price * Auth::user()->customeringroup->group->discount) / 100;
                            $group_lowest_price -= ($group_lowest_price * Auth::user()->customeringroup->group->discount) / 100;
                        } elseif (Auth::user()->customeringroup->group->discount_type == 'amount') {
                            $group_highest_price -= Auth::user()->customeringroup->group->discount;
                            $group_lowest_price -= Auth::user()->customeringroup->group->discount;
                        }
                        if ($discount_applicable) {
                            if ($highest_price < $group_highest_price) {
                                $highest_price = $highest_price;
                                $lowest_price = $lowest_price;
                            } else {
                                $highest_price = $group_highest_price;
                                $lowest_price = $group_lowest_price;
                            }
                        } else {
                            $highest_price = $group_highest_price;
                            $lowest_price = $group_lowest_price;
                        }
                    }
                }
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $lowest_price += ($lowest_price * $product_tax->tax) / 100;
                $highest_price += ($highest_price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $lowest_price += $product_tax->tax;
                $highest_price += $product_tax->tax;
            }
        }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)).' - '.format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price.' - '.$highest_price;
        }
    }

    function home_discounted_price_array($product, $formatted = true, $user_id = null)
    {
        // Ensure product is an array using Arr::wrap if needed
        $product = Arr::accessible($product) ? $product : Arr::wrap($product);

        // Initialize prices with defaults using Arr::get
        $base_price = (float) Arr::get($product, 'unit_price', 0);
        $lowest_price = $highest_price = $group_lowest_price = $group_highest_price = $base_price;

        try {
            // Handle product prices (quantity discounts) with Arr::where
            $productPrices = Arr::get($product, 'productprices', []);
            $applicablePrices = Arr::where($productPrices, function ($price) {
                return Arr::has($price, ['start_qty', 'end_qty', 'price']) &&
                       $price['start_qty'] <= 1 &&
                       $price['end_qty'] >= 1;
            });

            if (! empty($applicablePrices)) {
                $firstPrice = (float) Arr::first($applicablePrices)['price'];
                $lowest_price = $highest_price = $group_lowest_price = $group_highest_price = $firstPrice;
            }

            // Handle variant products with Arr::pluck
            if (Arr::get($product, 'variant_product', false)) {
                $stockPrices = Arr::pluck(Arr::get($product, 'stocks', []), 'price');

                if (! empty($stockPrices)) {
                    $minStockPrice = min($stockPrices);
                    $maxStockPrice = max($stockPrices);

                    $lowest_price = min($lowest_price, $minStockPrice);
                    $highest_price = max($highest_price, $maxStockPrice);
                    $group_lowest_price = $lowest_price;
                    $group_highest_price = $highest_price;
                }
            }

            // Check product discount applicability with Arr::has
            $discount_applicable = empty(Arr::get($product, 'discount_start_date')) || (
                Arr::has($product, ['discount_start_date', 'discount_end_date']) &&
                time() >= strtotime(Arr::get($product, 'discount_start_date')) &&
                time() <= strtotime(Arr::get($product, 'discount_end_date'))
            );

            // Apply product discount
            if ($discount_applicable && Arr::has($product, ['discount_type', 'discount'])) {
                $discount = (float) Arr::get($product, 'discount');
                if (Arr::get($product, 'discount_type') == 'percent') {
                    $lowest_price -= ($lowest_price * $discount) / 100;
                    $highest_price -= ($highest_price * $discount) / 100;
                } elseif (Arr::get($product, 'discount_type') == 'amount') {
                    $lowest_price -= $discount;
                    $highest_price -= $discount;
                }
            }

            // Handle user group discounts with Arr::get chaining
            $user = $user_id ? User::find($user_id) : (Auth::check() ? Auth::user() : null);
            $group = optional($user)->customeringroup['group'] ?? null;

            if ($group && Arr::get($group, 'discount_status') == 1) {
                $currentTime = time();
                $startTime = strtotime(Arr::get($group, 'start_date'));
                $endTime = strtotime(Arr::get($group, 'end_date'));

                if ($currentTime >= $startTime && $currentTime <= $endTime) {
                    $groupDiscount = (float) Arr::get($group, 'discount');
                    $discountType = Arr::get($group, 'discount_type');

                    if ($discountType == 'percent') {
                        $group_lowest_price -= ($group_lowest_price * $groupDiscount) / 100;
                        $group_highest_price -= ($group_highest_price * $groupDiscount) / 100;
                    } elseif ($discountType == 'amount') {
                        $group_lowest_price -= $groupDiscount;
                        $group_highest_price -= $groupDiscount;
                    }

                    // Determine which price to use
                    if (! $discount_applicable || $highest_price >= $group_highest_price) {
                        $highest_price = $group_highest_price;
                        $lowest_price = $group_lowest_price;
                    }
                }
            }

            // Apply taxes with Arr::get
            foreach (Arr::get($product, 'taxes', []) as $tax) {
                $taxAmount = (float) Arr::get($tax, 'tax', 0);
                if (Arr::get($tax, 'tax_type') == 'percent') {
                    $lowest_price += ($lowest_price * $taxAmount) / 100;
                    $highest_price += ($highest_price * $taxAmount) / 100;
                } elseif (Arr::get($tax, 'tax_type') == 'amount') {
                    $lowest_price += $taxAmount;
                    $highest_price += $taxAmount;
                }
            }
        } catch (Exception $e) {
            // Log error if needed
            Log::error('Error in home_discounted_price: '.$e->getMessage());
            $lowest_price = $highest_price = 0;
        }

        // Format the output
        return $formatted
            ? ($lowest_price == $highest_price
                ? format_price(convert_price($lowest_price))
                : format_price(convert_price($lowest_price)).' - '.format_price(convert_price($highest_price)))
            : $lowest_price.' - '.$highest_price;
    }
}

// Shows Base Price
if (! function_exists('home_base_price_by_stock_id')) {
    function home_base_price_by_stock_id($id)
    {
        $product_stock = ProductStock::with('product:id', 'product.taxes')->findOrFail($id);
        $price = $product_stock->price;
        $tax = 0;

        foreach ($product_stock->product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        $price += $tax;

        return format_price(convert_price($price));
    }
}

if (! function_exists('home_base_prices_by_stock_ids')) {
    function home_base_prices_by_stock_ids(array $ids)
    {
        $product_stocks = ProductStock::with([
            'product:id',
            'product.taxes:id,product_id,tax_type,tax',
        ])->whereIn('id', $ids)->get();

        $prices = [];

        foreach ($product_stocks as $product_stock) {
            $price = $product_stock->price;
            $tax = 0;

            foreach ($product_stock->product->taxes as $product_tax) {
                if ($product_tax->tax_type == 'percent') {
                    $tax += ($price * $product_tax->tax) / 100;
                } elseif ($product_tax->tax_type == 'amount') {
                    $tax += $product_tax->tax;
                }
            }

            $price += $tax;
            $prices[$product_stock->id] = format_price(convert_price($price));
        }

        return $prices;
    }
}

// Subscription product addtocart check
if (! function_exists('check_subscription_product_cart')) {
    function check_subscription_product_cart($request, $carts)
    {
        $status = true;
        if (is_array($request->subscriptioin_date) && $request->subscriptioin_date != '') {
            if (count($carts) > 0) {
                $status = false;
            }
        } else {
            if (count($carts) > 0) {
                $issubscribe = 0;
                foreach ($carts as $key => $cartItem) {
                    if ($cartItem['subscription_day'] != '') {
                        $issubscribe++;
                    }
                }
                if ($issubscribe > 0) {
                    $status = false;
                }
            }
        }

        return $status;
    }
}
if (! function_exists('home_base_price')) {
    function home_base_price($product, $formatted = true)
    {
        if (is_array($product)) {
            return home_base_price_array($product, $formatted);
        }
        $price = $product->unit_price;
        $productprices = collect($product->productprices);
        if (count($productprices) > 0) {
            $productprices = $productprices->where('start_qty', '<=', 1)->where('end_qty', '>=', 1)->first();
            if ($productprices) {
                $price = $productprices->price;
            }
        }
        $tax = 0;

        if (! empty($product->taxes)) {
            foreach ($product->taxes as $product_tax) {
                if ($product_tax->tax_type == 'percent') {
                    $tax += ($price * $product_tax->tax) / 100;
                } elseif ($product_tax->tax_type == 'amount') {
                    $tax += $product_tax->tax;
                }
            }
        }

        $price += $tax;

        return $formatted ? format_price(convert_price($price)) : $price;
    }

    function home_base_price_array($product, $formatted = true)
    {
        // Convert to array if not already array accessible
        $product = Arr::accessible($product) ? $product : convertToArray($product);

        // Get base price with fallback
        $price = (float) Arr::get($product, 'unit_price', 0);

        // Handle product prices using Arr helpers
        $productPrices = Arr::get($product, 'productprices', []);
        if (! empty($productPrices)) {
            $applicablePrice = Arr::first($productPrices, function ($price) {
                return Arr::has($price, ['start_qty', 'end_qty', 'price']) &&
                    $price['start_qty'] <= 1 &&
                    $price['end_qty'] >= 1;
            });

            if ($applicablePrice) {
                $price = (float) Arr::get($applicablePrice, 'price', $price);
            }
        }

        // Calculate taxes using Arr helpers
        $tax = 0;
        foreach (Arr::get($product, 'taxes', []) as $productTax) {
            $taxAmount = (float) Arr::get($productTax, 'tax', 0);
            $taxType = Arr::get($productTax, 'tax_type');

            if ($taxType === 'percent') {
                $tax += ($price * $taxAmount) / 100;
            } elseif ($taxType === 'amount') {
                $tax += $taxAmount;
            }
        }

        $price += $tax;

        return $formatted
            ? format_price(convert_price($price))
            : $price;
    }
}

// Shows Base Price with discount
if (! function_exists('home_discounted_base_price_by_stock_id')) {
    function home_discounted_base_price_by_stock_id($id)
    {
        $product_stock = ProductStock::findOrFail($id);
        $product = $product_stock->product;
        $price = $product_stock->price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        $price += $tax;

        return format_price(convert_price($price));
    }
}

if (! function_exists('home_discounted_base_price_by_stock_id_non_converted')) {
    function home_discounted_base_price_by_stock_id_non_converted($id)
    {
        $product_stock = ProductStock::findOrFail($id);
        $product = $product_stock->product;
        $price = $product_stock->price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        $price += $tax;

        return $price;
    }
}

// Shows Base Price with discount
if (! function_exists('home_discounted_type')) {
    function home_discounted_type($product, $user_id = null)
    {
        $price = $product->unit_price;
        $group_price = $product->unit_price;
        if (count($product->productprices) > 0) {
            $productprices = $product->productprices->where('start_qty', '<=', 1)->where('end_qty', '>=', 1)->first();
            if ($productprices) {
                $price = $productprices->price;
            }
        }
        $discount_type = $product->discount_type;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        $price += $tax;
        if ($user_id != null) {
            $user_info = User::findOrFail($user_id);
            if ($user_info->customeringroup) {
                $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($user_info->customeringroup->group->discount_type == 'percent') {
                        $group_price -= ($group_price * $user_info->customeringroup->group->discount) / 100;
                    } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                        $group_price -= $user_info->customeringroup->group->discount;
                    }
                    $group_price += $tax;
                    if ($discount_applicable) {
                        if ($price < $group_price) {
                            $price = $price;
                            $discount_type = $product->discount_type;
                        } else {
                            $price = $group_price;
                            $discount_type = $user_info->customeringroup->group->discount_type;
                        }
                    } else {
                        $price = $group_price;
                        $discount_type = $product->discount_type;
                    }
                }
            }
        }

        return $discount_type;
    }
}

// Shows Base Price with discount
if (! function_exists('home_discounted_base_price')) {
    function home_discounted_base_price($product, $formatted = true, $user_id = null)
    {
        if (is_array($product)) {
            return home_discounted_base_price_array($product, $formatted = true, $user_id = null);
        }
        $price = $product->unit_price;
        $group_price = $product->unit_price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        if (! empty($product->taxes)) {
            foreach ($product->taxes as $product_tax) {
                if ($product_tax->tax_type == 'percent') {
                    $tax += ($price * $product_tax->tax) / 100;
                } elseif ($product_tax->tax_type == 'amount') {
                    $tax += $product_tax->tax;
                }
            }
        }
        $price += $tax;
        if ($user_id != null) {
            $user_info = User::find($user_id);
            if ($user_info && $user_info->customeringroup) {
                $discount_status = $user_info->customeringroup->group?->discount_status;
                $start_date = $user_info->customeringroup->group?->start_date;
                $end_date = $user_info->customeringroup->group?->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($user_info->customeringroup->group?->discount_type == 'percent') {
                        $group_price -= ($group_price * $user_info->customeringroup->group?->discount) / 100;
                    } elseif ($user_info->customeringroup->group?->discount_type == 'amount') {
                        $group_price -= $user_info->customeringroup->group?->discount;
                    }
                    $group_price += $tax;
                    if ($discount_applicable) {
                        if ($price < $group_price) {
                            $price = $price;
                        } else {
                            $price = $group_price;
                        }
                    } else {
                        $price = $group_price;
                    }
                }
            }
        } else {
            if (isset(Auth::user()->id)) {
                if (Auth::user()->customeringroup) {
                    $discount_status = Auth::user()->customeringroup->group?->discount_status;
                    $start_date = Auth::user()->customeringroup->group?->start_date;
                    $end_date = Auth::user()->customeringroup->group?->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                        if (Auth::user()->customeringroup->group?->discount_type == 'percent') {
                            $group_price -= ($group_price * Auth::user()->customeringroup->group?->discount) / 100;
                        } elseif (Auth::user()->customeringroup->group?->discount_type == 'amount') {
                            $group_price -= Auth::user()->customeringroup->group?->discount;
                        }
                        $group_price += $tax;
                        if ($discount_applicable) {
                            if ($price < $group_price) {
                                $price = $price;
                            } else {
                                $price = $group_price;
                            }
                        } else {
                            $price = $group_price;
                        }
                    }
                }
            }
        }

        return $formatted ? format_price(convert_price($price)) : $price;
    }

    function home_discounted_base_price_array($product, $formatted = true, $user_id = null)
    {
        $product = Arr::accessible($product) ? $product : convertToArray($product);

        $price = (float) Arr::get($product, 'unit_price', 0);
        $group_price = $price;
        $tax = 0;

        // Check discount applicability
        $discount_applicable = Arr::get($product, 'discount_start_date', null) === null || (
            Arr::has($product, ['discount_start_date', 'discount_end_date']) &&
            time() >= strtotime(Arr::get($product, 'discount_start_date')) &&
            time() <= strtotime(Arr::get($product, 'discount_end_date'))
        );

        // Apply product discount
        if ($discount_applicable && Arr::has($product, ['discount_type', 'discount'])) {
            $discount = (float) Arr::get($product, 'discount');
            if (Arr::get($product, 'discount_type') === 'percent') {
                $price -= ($price * $discount) / 100;
            } elseif (Arr::get($product, 'discount_type') === 'amount') {
                $price -= $discount;
            }
        }

        // Calculate taxes
        foreach (Arr::get($product, 'taxes', []) as $product_tax) {
            $taxAmount = (float) Arr::get($product_tax, 'tax', 0);
            if (Arr::get($product_tax, 'tax_type') === 'percent') {
                $tax += ($price * $taxAmount) / 100;
            } elseif (Arr::get($product_tax, 'tax_type') === 'amount') {
                $tax += $taxAmount;
            }
        }
        $price += $tax;

        // Handle user group discounts
        $user = $user_id ? User::find($user_id) : (Auth::check() ? Auth::user() : null);
        if ($user && Arr::get($user, 'customeringroup.group')) {
            $group = Arr::get($user, 'customeringroup.group');
            $discount_status = Arr::get($group, 'discount_status');
            $start_date = strtotime(Arr::get($group, 'start_date'));
            $end_date = strtotime(Arr::get($group, 'end_date'));
            $cur_date = time();

            if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                $group_discount = (float) Arr::get($group, 'discount');
                $group_discount_type = Arr::get($group, 'discount_type');

                if ($group_discount_type === 'percent') {
                    $group_price -= ($group_price * $group_discount) / 100;
                } elseif ($group_discount_type === 'amount') {
                    $group_price -= $group_discount;
                }
                $group_price += $tax;

                $price = (! $discount_applicable || $price >= $group_price) ? $group_price : $price;
            }
        }

        return $formatted ? format_price(convert_price($price)) : $price;
    }
}

if (! function_exists('renderStarRating')) {
    function renderStarRating($rating, $maxRating = 5)
    {
        $fullStar = "<i class = 'las la-star active fs-10'></i>";
        $halfStar = "<i class = 'las la-star half fs-10'></i>";
        $emptyStar = "<i class = 'las la-star fs-10'></i>";
        $rating = $rating <= $maxRating ? $rating : $maxRating;

        $fullStarCount = (int) $rating;
        $halfStarCount = ceil($rating) - $fullStarCount;
        $emptyStarCount = $maxRating - $fullStarCount - $halfStarCount;

        $html = str_repeat($fullStar, $fullStarCount);
        $html .= str_repeat($halfStar, $halfStarCount);
        $html .= str_repeat($emptyStar, $emptyStarCount);

        return $html;
    }
}

function translate($key, $lang = null)
{
    if ($lang == null) {
        $lang = App::getLocale();
    }

    $lang_key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($key)));

    $translations_default = Cache::rememberForever('translations-'.env('DEFAULT_LANGUAGE', 'en'), function () {
        return Translation::where('lang', env('DEFAULT_LANGUAGE', 'en'))->pluck('lang_value', 'lang_key')->toArray();
    });

    if (! isset($translations_default[$lang_key])) {
        $translation_def = new Translation;
        $translation_def->lang = env('DEFAULT_LANGUAGE', 'en');
        $translation_def->lang_key = $lang_key;
        $translation_def->lang_value = $key;
        $translation_def->save();
        Cache::forget('translations-'.env('DEFAULT_LANGUAGE', 'en'));
    }

    $translation_locale = Cache::rememberForever('translations-'.$lang, function () use ($lang) {
        return Translation::where('lang', $lang)->pluck('lang_value', 'lang_key')->toArray();
    });

    // Check for session lang
    if (isset($translation_locale[$lang_key])) {
        return $translation_locale[$lang_key];
    } elseif (isset($translations_default[$lang_key])) {
        return $translations_default[$lang_key];
    } else {
        return $key;
    }
}

function remove_invalid_charcaters($str)
{
    $str = str_ireplace(['\\'], '', $str);

    return str_ireplace(['"'], '\"', $str);
}

function getShippingCost($carts, $index)
{
    $admin_products = [];
    $seller_products = [];

    $cartItem = $carts[$index];
    $product = $cartItem->product;

    if ($product->digital == 1) {
        return 0;
    }

    foreach ($carts as $key => $cartItem) {
        $product = $cartItem->product;
        if ($product->added_by == 'admin') {
            array_push($admin_products, $cartItem['product_id']);
        } else {
            $product_ids = [];
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem['product_id']);
            $seller_products[$product->user_id] = $product_ids;
        }
    }

    $cartItem = $carts[$index];
    if (! empty($cartItem['user_id'])) {
        $userinfo = User::find($cartItem['user_id']);
        if ($userinfo && $userinfo->customeringroup) {
            if ($userinfo->customeringroup->group?->delivery_discount == 1) {
                return $userinfo->customeringroup->group->delivery_discount_amount;
            }
        }
    }

    if (get_setting('shipping_type') == 'flat_rate') {
        return get_setting('flat_rate_shipping_cost') / count($carts);
    } elseif (get_setting('shipping_type') == 'seller_wise_shipping') {
        if ($product->added_by == 'admin') {
            return get_setting('shipping_cost_admin') / count($admin_products);
        } else {
            return Shop::where('user_id', $product->user_id)->first()->shipping_cost / count($seller_products[$product->user_id]);
        }
    } elseif (get_setting('shipping_type') == 'area_wise_shipping') {

        if ($cartItem['shipping_method'] == null) {

            $shipping_info = Address::where('id', $cartItem['address_id'])->first();
            if ($shipping_info == null) {
                return 0;
            }
            // $city = City::where('id', $shipping_info->city_id)->first();
            $city = Area::where('id', $shipping_info->area_id)->first();
            if ($city != null) {
                if ($product->added_by == 'admin') {
                    return $city->cost / count($admin_products);
                } else {
                    return $city->cost / count($seller_products[$product->user_id]);
                }
            }

            return 0;

        } else {

            $addressInfo = Address::find($cartItem['address_id']);
            $matchZone = $addressInfo ? \App\Models\ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id])->first() : null;
            $shippingMethods = null;
            if (@$matchZone !== null) {
                $shippingMethods = json_decode($matchZone->rates);
                $rate = 0;
                foreach ($shippingMethods as $k => $v) {
                    if ($v->id == $cartItem['shipping_method']) {
                        $rate += $v->price;
                    }
                }

                return $rate;
            } else {

                $matchZone = \App\Models\ShippingZone::whereNull('area_ids')->first();
                if ($matchZone != null) {
                    $shippingMethods = json_decode($matchZone->rates);
                    $rate = 0;
                    foreach ($shippingMethods as $k => $v) {
                        if ($v->id == $cartItem['shipping_method']) {
                            $rate += $v->price;
                        }
                    }

                    return $rate;
                }
            }

            return 0;

        }

    } else {
        if ($product->is_quantity_multiplied && get_setting('shipping_type') == 'product_wise_shipping') {
            return $product->shipping_cost * $cartItem['quantity'];
        }

        return $product->shipping_cost;
    }
}

function timezones()
{
    // return Timezones::timezonesToArray();
    $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    return $timezones;
}

if (! function_exists('app_timezone')) {
    function app_timezone()
    {
        return config('app.timezone');
    }
}

if (! function_exists('api_asset')) {
    function api_asset($id)
    {
        $asset = Cache::remember('api_uploded_file_'.$id, 86400, function () use ($id) {
            return Upload::find($id);
        });
        if ($asset != null) {
            return $asset->file_name;
        }

        return '';
    }
}

// return file uploaded via uploader
if (! function_exists('uploaded_asset')) {
    function uploaded_asset($uid)
    {
        $id = (int) $uid;
        // Log::channel('custom')->info('Uploaded asset called with ID: ' . $id . ', casted to int: ' . $new_id);
        if ($id != $uid) {
            return $uid;
        }
        if (! empty($id)) {
            $asset = Cache::remember('uni_uploaded_file_'.$id, 86400, function () use ($id) {
                return Upload::find($id);
            });
        } else {
            $asset = $id;
        }

        if ($asset != null) {
            if ($asset->type == 'video') {
                if (get_setting('video_file_driver', 'local') == 's3') {
                    return Storage::disk('s3')->url(is_string($asset) ? $asset : $asset->file_name ?? '');
                }

                return app('url')->asset(is_string($asset) ? $asset : $asset->file_name ?? '');
            }

            return my_asset(is_string($asset) ? $asset : $asset->file_name ?? '');
        }

        return $id;
    }
}

if (! function_exists('my_asset')) {
    function my_asset($path, $secure = null)
    {
        if (config('filesystems.default') == 's3') {
            return Storage::disk('s3')->url($path);
        } else {
            return app('url')->asset($path, $secure);
        }
    }
}

if (! function_exists('static_asset')) {
    function static_asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
        if (config('filesystems.default') == 's3') {
            return Storage::disk('s3')->url($path);
        } else {
            return app('url')->asset($path, $secure);
        }
    }
}

// if (!function_exists('isHttps')) {
//     function isHttps()
//     {
//         return !empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS']);
//     }
// }

if (! function_exists('getBaseURL')) {
    function getBaseURL()
    {
        $root = '//'.$_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        return $root;
    }
}

if (! function_exists('getFileBaseURL')) {
    function getFileBaseURL()
    {
        if (config('filesystems.default') == 's3') {
            return Storage::disk('s3')->url('');
        } else {
            return config('app.url').'/';
        }
        // if (env('FILESYSTEM_DRIVER') == 's3') {
        //     return env('AWS_URL') . '/';
        // } else {
        //     return getBaseURL() . 'public/';
        // }
    }
}

if (! function_exists('isUnique')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function isUnique($email)
    {
        $user = User::where('email', $email)->first();

        if ($user == null) {
            return '1'; // $user = null means we did not get any match with the email provided by the user inside the database
        } else {
            return '0';
        }
    }
}

if (! function_exists('get_setting')) {
    function get_setting($key, $default = null, $lang = false)
    {
        $settings = Cache::remember('business_settings', 86400, function () {
            return BusinessSetting::all();
        });

        if ($lang == false) {
            $setting = $settings->where('type', $key)->first();
        } else {
            $setting = $settings->where('type', $key)->where('lang', $lang)->first();
            $setting = ! $setting ? $settings->where('type', $key)->first() : $setting;
        }

        return $setting->value ?? $default;
    }
}

function hex2rgba($color, $opacity = false)
{
    // return Colorcodeconverter::convertHexToRgba($color, $opacity);
    // Remove the '#' if it exists
    $hex = str_replace('#', '', $color);

    // Make sure the hexadecimal color code is valid
    if (preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        // Convert the hexadecimal color code to RGB values
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        // Ensure the alpha value is between 0 and 1
        $alpha = max(0, min(1, $opacity));

        // Create the RGBA string
        $rgba = "rgba($red, $green, $blue, $alpha)";

        return $rgba;
    }

    // Return a default value or handle the invalid input as needed
    return 'rgba(0, 0, 0, 1.0)';
}

if (! function_exists('isAdmin')) {
    function isAdmin()
    {
        if (Auth::check() && (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff')) {
            return true;
        }

        return false;
    }
}

if (! function_exists('isSeller')) {
    function isSeller()
    {
        if (Auth::check() && Auth::user()->user_type == 'seller') {
            return true;
        }

        return false;
    }
}

if (! function_exists('isCustomer')) {
    function isCustomer()
    {
        if (Auth::check() && Auth::user()->user_type == 'customer') {
            return true;
        }

        return false;
    }
}

if (! function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}

// duplicates m$ excel's ceiling function
if (! function_exists('ceiling')) {
    function ceiling($number, $significance = 1)
    {
        return (is_numeric($number) && is_numeric($significance)) ? (ceil($number / $significance) * $significance) : false;
    }
}

if (! function_exists('get_images')) {
    function get_images($given_ids, $with_trashed = false)
    {
        if (is_array($given_ids)) {
            $ids = $given_ids;
        } elseif ($given_ids == null) {
            $ids = [];
        } else {
            $ids = explode(',', $given_ids);
        }

        return $with_trashed
            ? Upload::withTrashed()->whereIn('id', $ids)->get()
            : Upload::whereIn('id', $ids)->get();
    }
}

// for api
if (! function_exists('get_images_path')) {
    function get_images_path($given_ids, $with_trashed = false)
    {
        $paths = [];
        $images = get_images($given_ids, $with_trashed);
        if (! $images->isEmpty()) {
            foreach ($images as $image) {
                $paths[] = ! is_null($image) ? $image->file_name : '';
            }
        }

        return $paths;
    }
}

// For Payment
if (! function_exists('make_payment')) {
    function make_payment(Order $order, array $payment_details, $amount = null): ?Payment
    {
        if (! $order) {
            Log::error('Order not found for payment processing.');

            return null;
        }
        // Newly added code for store payment details
        $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
        $pinv = 'PAY-'.date('Ymd').($inv_counting + 1);

        $method = $payment_details['method'] ?? Session::get('payment_method', null);
        $pdetails = [
            'payment_method' => $method,
            'bank_type' => $payment_details['bank_type'] ?? null,
            'bank_info' => $method,
            'payment_amount' => ! is_null($amount) ? $amount : get_order_due_amount($order),
        ];

        try {
            DB::beginTransaction();
            $payment = new Payment;
            $payment->invoice_no = $pinv;
            $payment->date = date('Y-m-d');
            $payment->payable_id = $order->user_id;
            $payment->payable_type = User::class;
            $payment->reference_id = $order->id;
            $payment->reference_type = Order::class;
            $payment->seller_id = null;
            $payment->amount = ! is_null($amount) ? $amount : get_order_due_amount($order);
            $payment->payment_details = json_encode($pdetails);
            $payment->payment_method = $method;
            $payment->txn_code = null;
            $payment->user_id = auth()->user()?->id ?? null;
            $payment->remarks = 'Payment for Order #'.$order->code;
            $payment->save();
            // End of newly added code

            $order->payment_status = 'paid';
            $order->due_amount = 0;
            $order->payment_details = $payment;
            $order->save();
            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment Error: '.$e->getMessage(), $e->getTrace());

            return null;
        }
    }
}

// for api
if (! function_exists('checkout_done')) {
    function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::find($combined_order_id);
        $paymentData = json_decode($payment, true);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);

            // Newly added code for store payment details
            $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
            $pinv = 'PAY-'.date('Ymd').($inv_counting + 1);

            $method = $paymentData['method'] ?? Session::get('payment_method', null);
            $pdetails = [
                'payment_method' => $method,
                'bank_type' => $paymentData['bank_type'] ?? null,
                'bank_info' => $method,
                'payment_amount' => get_order_grand_total($order),
            ];

            try {
                DB::beginTransaction();
                $payment = new Payment;
                $payment->invoice_no = $pinv;
                $payment->date = date('Y-m-d');
                $payment->payable_id = $order->user_id;
                $payment->payable_type = User::class;
                $payment->reference_id = $order->id;
                $payment->reference_type = Order::class;
                $payment->seller_id = null;
                $payment->amount = get_order_grand_total($order);
                $payment->payment_details = json_encode($pdetails);
                $payment->payment_method = $method;
                $payment->txn_code = null;
                $payment->user_id = $paymentData['user_id'] ?? $order->user_id ?? null;
                $payment->remarks = 'Payment for Order #'.$order->code;
                $payment->save();
                // End of newly added code

                $order->payment_status = 'paid';
                $order->due_amount = 0;
                $order->payment_details = $payment;
                $order->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Payment Error: '.$e->getMessage(), $e->getTrace());
            }

            try {
                NotificationUtility::sendOrderPlacedNotification($order);
                calculateCommissionAffilationClubPoint($order);
            } catch (\Exception $e) {
                Log::error('Order Placement Notification Error: '.$e->getMessage(), $e->getTrace());
            }
        }
    }
}

// for api
if (! function_exists('wallet_payment_done')) {
    function wallet_payment_done($user_id, $amount, $payment_method, $payment_details, $combined_order_id = null)
    {
        $user = User::find($user_id);
        // if ($user->balance >= $amount) {
        //     $user->balance -= $amount;
        //     $user->save();
        // }
        $user->balance = $user->balance + $amount;
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $amount;
        $wallet->payment_method = $payment_method;
        $wallet->payment_details = $payment_details;
        $wallet->save();
    }
}

if (! function_exists('purchase_payment_done')) {
    function purchase_payment_done($user_id, $package_id)
    {
        $user = User::findOrFail($user_id);
        $user->customer_package_id = $package_id;
        $customer_package = CustomerPackage::findOrFail($package_id);
        $user->remaining_uploads += $customer_package->product_upload;
        $user->save();

        return 'success';
    }
}

// Commission Calculation
if (! function_exists('calculateCommissionAffilationClubPoint')) {
    function calculateCommissionAffilationClubPoint($order)
    {
        (new CommissionController)->calculateCommission($order);

        if (addon_is_activated('affiliate_system')) {
            (new AffiliateController)->processAffiliatePoints($order);
        }

        if (addon_is_activated('club_point')) {
            if ($order->user != null) {
                (new ClubPointController)->processClubPoints($order);
            }
        }

        $order->commission_calculated = 1;
        $order->save();
    }
}

// Addon Activation Check
if (! function_exists('addon_is_activated')) {
    function addon_is_activated($identifier, $default = null)
    {
        $addons = Cache::remember('addons', 86400, function () {
            return Addon::all();
        });

        $activation = $addons->where('unique_identifier', $identifier)->where('activated', 1)->first();

        return $activation == null ? false : true;
    }
}

if (! function_exists('check_discount_product_from_cart')) {
    function check_discount_product_from_cart($product_id)
    {
        $find_products = Product::where('discount', '>', 0)
            ->where('discount_start_date', '<', time())
            ->where('discount_end_date', '>', time())
            ->where('id', $product_id)
            ->exists();
        if ($find_products) {
            return true;
        } else {
            $find_product_from_flashdeals = FlashDealProduct::where('discount', '>', 0)
                ->where('product_id', $product_id)
                ->get();
            if ($find_product_from_flashdeals) {
                // check flashdeal product is available for discount or not
                foreach ($find_product_from_flashdeals as $key => $value) {
                    $check_flash_deal_date_status = FlashDeal::where('id', $value->flash_deal_id)
                        ->where('status', 1)
                        ->where('start_date', '<', time())
                        ->where('end_date', '>', time())
                        ->exists();
                    if ($check_flash_deal_date_status) {
                        return true;
                    }
                }
            }

            return false;
        }
    }
}

if (! function_exists('group_identity')) {
    function group_identity($id, $type = 'name')
    {

        $query = Customeringroup::where('user_id', $id)->where('status', '1')->first();

        if (! $query) {
            return '';
        }

        if ($type == 'icon') {
            return @$query->group->group_icon ?? '';
        } elseif ($type == 'image') {
            return '<img width="20" class="mb-1" style="vertical-align:middle" src="'.uploaded_asset(@$query->group->group_image).'" onerror="this.onerror=null;this.src=\''.static_asset('assets/img/placeholder.jpg').'\';">';
        } else {
            return @$query->group->group_name;
        }

    }
}

if (! function_exists('getHoldStatuses')) {
    function getHoldStatuses()
    {
        return [
            'call_received' => 'Call Received',
            'no_response' => 'No Response',
            'call_me_later' => 'Call Me Later',
            'order_hold' => 'Hold Order',
            'out_of_stock' => 'Out of Stock',
            'bkash_advance_payment' => 'Bkash Advance Payment',
            'shipment_failed' => 'Shipment Failed',
            'others' => 'Others',
        ];
    }
}

if (! function_exists('statusWiseOrderStatuses')) {
    function statusWiseOrderStatuses($status = 'pending')
    {
        // return match ($status) {
        //     'pending' => ['confirmed', 'cancelled'],
        //     'confirmed' => ['pending', 'picked_up', 'on_the_way', 'delivered', 'cancelled'],
        //     'picked_up' => ['confirmed', 'on_the_way', 'delivered', 'cancelled'],
        //     'on_the_way' => ['confirmed', 'picked_up', 'delivered', 'cancelled'],
        //     'delivered' => ['confirmed', 'on_the_way', 'picked_up', 'cancelled'],
        //     'cancelled' => ['confirmed', 'on_the_way', 'picked_up', 'cancelled'],
        // };
        $statuses = [];
        switch ($status) {
            case 'preorder':
                $statuses = ['pending', 'cancelled'];
                break;
            case 'pending':
                $statuses = ['processing', 'cancelled'];
                break;
            case 'processing':
                $statuses = ['pending', 'hold', 'confirmed', 'cancelled'];
                break;
            case 'hold':
                $statuses = ['pending', 'processing', 'confirmed', 'cancelled'];
                // $statuses = ['pending', 'processing', 'picked_up', 'confirmed', 'cancelled'];
                break;
            case 'confirmed':
                $statuses = ['pending', 'processing', 'packaging', 'hold', 'cancelled'];
                break;
            case 'packaging':
                $statuses = ['pending', 'processing', 'confirmed', 'hold', 'cancelled'];
                break;
            case 'picked_up':
                $statuses = ['pending', 'processing', 'on_the_way', 'cancelled'];
                break;
            case 'on_the_way':
                $statuses = ['pending', 'processing', 'delivered', 'returned', 'cancelled'];
                break;
            case 'delivered':
                $statuses = ['pending', 'processing', 'cancelled'];
                break;
            case 'cancelled':
                // $statuses = ['pending', 'processing'];
                $statuses = ['cancelled'];
                break;
            case 'merchant':
                $statuses = ['pending', 'picked_up', 'on_the_way'];
                break;
            case 'returned':
                // $statuses = ['pending', 'processing', 'cancelled'];
                $statuses = ['returned'];
                break;
            default:
                $statuses = ['pending', 'processing', 'hold', 'confirmed', 'packaging', 'on_the_way', 'picked_up', 'delivered', 'cancelled'];
        }

        return $statuses;
    }
}

if (! function_exists('getCustomerGroup')) {
    function getCustomerGroup($orderCount = 0, $orderAmount = 0)
    {

        $groups = Customergroup::all();
        $status = [];
        foreach ($groups as $index => $group) {
            if ($orderAmount >= $group->min_order_amount && $orderCount >= $group->min_order_qty) {
                $status[$group->id] = true;
            } else {
                $status[$group->id] = false;
            }
        }

        // return $status;
        return array_search('true', array_reverse($status, true));
    }
}

if (! function_exists('get_product_count_based_status')) {
    function get_product_count_based_status()
    {
        $allproducts = Product::with('stocks')->where('published', 1)->get();

        return [
            'published' => DB::table('products')->where('published', 1)->count(),
            'unpublished' => DB::table('products')->where('published', 0)->count(),
            'outofstock' => get_products_with_outofstock($allproducts)->count(),
            'lowstock' => get_products_with_lowstock($allproducts)->count(),
            'all' => $allproducts->count(),
        ];
    }
}

if (! function_exists('get_products_with_outofstock')) {
    function get_products_with_outofstock($items)
    {
        foreach ($items as $index => $product) {
            // $product->current_stock = ProductStock::where('product_id', $product->id)->sum('qty');
            $product->current_stock = $product->stocks->sum('qty');
        }
        $filterredItems = $items->where('current_stock', '<=', 0);

        return $filterredItems;
    }
}

if (! function_exists('get_products_with_lowstock')) {
    function get_products_with_lowstock($items)
    {
        foreach ($items as $index => $product) {
            $product->current_stock = $product->stocks->sum('qty');
        }
        $filterredItems = $items->filter(function ($item) {
            return $item->current_stock <= $item->low_stock_quantity;
        });

        $filterredItems = $filterredItems->filter(function ($item) {
            return $item->current_stock > 0;
        });

        return $filterredItems;
    }
}

if (! function_exists('get_product_current_stock')) {
    function get_product_current_stock($items)
    {
        foreach ($items as $product) {
            $product->current_stock = ProductStock::where('product_id', $product->id)->sum('qty');
        }

        $items = $items->where('current_stock', '<=', 0);

        return $items->count();
    }
}

if (! function_exists('collectionTopaginate')) {
    function collectionTopaginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

// Show APP Price
if (! function_exists('home_app_price')) {
    function home_app_price($product, $formatted = true, $user_id = null)
    {

        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        $group_lowest_price = $product->unit_price;
        $group_highest_price = $product->unit_price;

        if (count($product->productprices) > 0) {
            $productprices = $product->productprices->where('start_qty', '<=', 1)->where('end_qty', '>=', 1)->first();
            if ($productprices) {
                $lowest_price = $productprices->price;
                $highest_price = $productprices->price;
            }
        }
        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if ($lowest_price > $stock->price) {
                    $lowest_price = $stock->price;
                    $group_lowest_price = $stock->price;
                }
                if ($highest_price < $stock->price) {
                    $highest_price = $stock->price;
                    $group_highest_price = $stock->price;
                }
            }
        }

        $discount_applicable = false;

        if ($product->app_discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->app_discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->app_discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->app_discount_type == 'percent') {
                $lowest_price -= ($lowest_price * $product->app_discount) / 100;
                $highest_price -= ($highest_price * $product->app_discount) / 100;
            } elseif ($product->app_discount_type == 'amount') {
                $lowest_price -= $product->app_discount;
                $highest_price -= $product->app_discount;
            }
        }

        if ($user_id != null) {
            $user_info = User::findOrFail($user_id);
            if ($user_info->customeringroup) {
                $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($user_info->customeringroup->group->discount_type == 'percent') {
                        $group_highest_price -= ($group_highest_price * $user_info->customeringroup->group->discount) / 100;
                        $group_lowest_price -= ($group_lowest_price * $user_info->customeringroup->group->discount) / 100;
                    } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                        $group_highest_price -= $user_info->customeringroup->group->discount;
                        $group_lowest_price -= $user_info->customeringroup->group->discount;
                    }
                    if ($discount_applicable) {
                        if ($highest_price < $group_highest_price) {
                            $highest_price = $highest_price;
                            $lowest_price = $lowest_price;
                        } else {
                            $highest_price = $group_highest_price;
                            $lowest_price = $group_lowest_price;
                        }
                    } else {
                        $highest_price = $group_highest_price;
                        $lowest_price = $group_lowest_price;
                    }
                }
            }
        } else {
            if (isset(Auth::user()->id)) {
                if (Auth::user()->customeringroup) {
                    $discount_status = Auth::user()->customeringroup->group->discount_status;
                    $start_date = Auth::user()->customeringroup->group->start_date;
                    $end_date = Auth::user()->customeringroup->group->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                        if (Auth::user()->customeringroup->group->discount_type == 'percent') {
                            $group_highest_price -= ($group_highest_price * Auth::user()->customeringroup->group->discount) / 100;
                            $group_lowest_price -= ($group_lowest_price * Auth::user()->customeringroup->group->discount) / 100;
                        } elseif (Auth::user()->customeringroup->group->discount_type == 'amount') {
                            $group_highest_price -= Auth::user()->customeringroup->group->discount;
                            $group_lowest_price -= Auth::user()->customeringroup->group->discount;
                        }
                        if ($discount_applicable) {
                            if ($highest_price < $group_highest_price) {
                                $highest_price = $highest_price;
                                $lowest_price = $lowest_price;
                            } else {
                                $highest_price = $group_highest_price;
                                $lowest_price = $group_lowest_price;
                            }
                        } else {
                            $highest_price = $group_highest_price;
                            $lowest_price = $group_lowest_price;
                        }
                    }
                }
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $lowest_price += ($lowest_price * $product_tax->tax) / 100;
                $highest_price += ($highest_price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $lowest_price += $product_tax->tax;
                $highest_price += $product_tax->tax;
            }
        }

        if ($formatted) {
            if ($lowest_price == $highest_price) {
                return format_price(convert_price($lowest_price));
            } else {
                return format_price(convert_price($lowest_price)).' - '.format_price(convert_price($highest_price));
            }
        } else {
            return $lowest_price.' - '.$highest_price;
        }
    }
}

// Shows Base Price with discount
if (! function_exists('home_app_base_price')) {
    function home_app_base_price($product, $formatted = true, $user_id = null)
    {
        $price = $product->unit_price;
        $group_price = $product->unit_price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->app_discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->app_discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->app_discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->app_discount_type == 'percent') {
                $price -= ($price * $product->app_discount) / 100;
            } elseif ($product->app_discount_type == 'amount') {
                $price -= $product->app_discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;
        if ($user_id != null) {
            $user_info = User::findOrFail($user_id);
            if ($user_info->customeringroup) {
                $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($user_info->customeringroup->group->discount_type == 'percent') {
                        $group_price -= ($group_price * $user_info->customeringroup->group->discount) / 100;
                    } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                        $group_price -= $user_info->customeringroup->group->discount;
                    }
                    $group_price += $tax;
                    if ($discount_applicable) {
                        if ($price < $group_price) {
                            $price = $price;
                        } else {
                            $price = $group_price;
                        }
                    } else {
                        $price = $group_price;
                    }
                }
            }
        } else {
            if (isset(Auth::user()->id)) {
                if (Auth::user()->customeringroup) {
                    $discount_status = Auth::user()->customeringroup->group->discount_status;
                    $start_date = Auth::user()->customeringroup->group->start_date;
                    $end_date = Auth::user()->customeringroup->group->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                        if (Auth::user()->customeringroup->group->discount_type == 'percent') {
                            $group_price -= ($group_price * Auth::user()->customeringroup->group->discount) / 100;
                        } elseif (Auth::user()->customeringroup->group->discount_type == 'amount') {
                            $group_price -= Auth::user()->customeringroup->group->discount;
                        }
                        $group_price += $tax;
                        if ($discount_applicable) {
                            if ($price < $group_price) {
                                $price = $price;
                            } else {
                                $price = $group_price;
                            }
                        } else {
                            $price = $group_price;
                        }
                    }
                }
            }
        }

        return $formatted ? format_price(convert_price($price)) : $price;
    }
}

if (! function_exists('app_price')) {
    function app_price($product, $variant_price = null, $formatted = true)
    {

        $unitprice = $variant_price ?? $product->unit_price;

        $appdiscountprice = $unitprice;
        $app_price_check = check_app_discount_product($product);
        if ($app_price_check) {
            if ($product->app_discount_type == 'percent') {
                $appdiscountprice -= ($appdiscountprice * $product->app_discount) / 100;
            } elseif ($product->app_discount_type == 'amount') {
                $appdiscountprice -= $product->app_discount;
            }
        }

        return $formatted ? format_price(convert_price($appdiscountprice)) : $appdiscountprice;
    }
}
// ALL Prices
// All discounted prices for a product
if (! function_exists('get_all_discount_prices')) {
    function get_all_discount_prices($product, $stack = 'web', $quantity = 0)
    {
        $unitprice = $product->unit_price;

        // Product Discount Price
        $pdiscountprice = $unitprice;
        $discount_applicable = false;
        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date && strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $pdiscountprice -= ($pdiscountprice * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $pdiscountprice -= $product->discount;
            }
        }

        // Break Down Price
        $breakdownprice = $unitprice;
        $breakdownprices = [];
        if (count($product->productprices) > 0) {
            $productprice = $product->productprices->where('start_qty', '<=', $quantity)->where('end_qty', '>=', $quantity)->first();
            if ($productprice) {
                $breakdownprice = $productprice->price;
                if ($discount_applicable) {
                    if ($product->discount_type == 'percent') {
                        $breakdownprice -= ($productprice->price * $product->discount) / 100;
                    } elseif ($product->discount_type == 'amount') {
                        $breakdownprice -= $product->discount;
                    }
                }
            }
            foreach ($product->productprices as $key => $value) {
                $breakdownpriceff = $value->price;
                if ($discount_applicable) {
                    if ($product->discount_type == 'percent') {
                        $breakdownpriceff -= ($breakdownpriceff * $product->discount) / 100;
                    } elseif ($product->discount_type == 'amount') {
                        $breakdownpriceff -= $product->discount;
                    }
                }
                $breakdownprices[$key] = $breakdownpriceff;
            }
        }

        // Flash Deal
        $flashdealprice = $unitprice;
        $flash_deal_check = check_flash_deal_product($product);
        if ($flash_deal_check) {
            if ($product->discount_type == 'percent') {
                $flashdealprice -= ($flashdealprice * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $flashdealprice -= $product->discount;
            }
        }

        // Category Discount
        $categorydiscountprice = $unitprice;
        $category_discount_check = check_category_discount_product($product);
        if ($category_discount_check) {
            if ($product->category->discount_type == 'percent') {
                $categorydiscountprice -= ($categorydiscountprice * $product->category->discount) / 100;
            } elseif ($product->category->discount_type == 'amount') {
                $categorydiscountprice -= $product->category->discount;
            }
        }

        // Brand Discount
        $branddiscountprice = $unitprice;
        $brand_discount_check = check_brand_discount_product($product);
        if ($brand_discount_check) {
            if ($product->brand->discount_type == 'percent') {
                $branddiscountprice -= ($branddiscountprice * $product->brand->discount) / 100;
            } elseif ($product->brand->discount_type == 'amount') {
                $branddiscountprice -= $product->brand->discount;
            }
        }

        $prices = [
            'Product Discount Price' => $pdiscountprice,
            'Break Down Price' => $breakdownprice,
            'Break Down Prices' => $breakdownprices,
            'Flash Deal Price' => $flashdealprice,
            'Category Discount Price' => $categorydiscountprice,
            'Brand Discount Price' => $branddiscountprice,
        ];
        if ($stack != 'web') {
            $prices['App Price'] = app_price($product, false);
        }

        return $prices;
    }
}

// Get only the minimum price from all discounted prices
if (! function_exists('getMinimumPrice')) {
    function getMinimumPrice($product, $for = 'web', $quantity = 1, $user_id = null)
    {
        $price = $product->unit_price;
        $group_price = $product->unit_price;
        $tax = 0;

        $discount_applicable = false;
        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date && strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }
        $price += $tax;

        $prices = get_all_discount_prices($product, $for, $quantity);
        $price = min($prices);
        if ($user_id != null) {
            $user_info = User::findOrFail($user_id);
            if ($user_info->customeringroup) {
                $discount_status = $user_info->customeringroup->group->discount_status;
                $start_date = $user_info->customeringroup->group->start_date;
                $end_date = $user_info->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($user_info->customeringroup->group->discount_type == 'percent') {
                        $group_price -= ($group_price * $user_info->customeringroup->group->discount) / 100;
                    } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
                        $group_price -= $user_info->customeringroup->group->discount;
                    }
                    $group_price += $tax;
                    if ($discount_applicable) {
                        if ($price < $group_price) {
                            $price = $price;
                        } else {
                            $price = $group_price;
                        }
                    } else {
                        $price = $group_price;
                    }
                }
            }
        } else {
            if (isset(Auth::user()->id)) {
                if (Auth::user()->customeringroup) {
                    $discount_status = Auth::user()->customeringroup->group->discount_status;
                    $start_date = Auth::user()->customeringroup->group->start_date;
                    $end_date = Auth::user()->customeringroup->group->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                        if (Auth::user()->customeringroup->group->discount_type == 'percent') {
                            $group_price -= ($group_price * Auth::user()->customeringroup->group->discount) / 100;
                        } elseif (Auth::user()->customeringroup->group->discount_type == 'amount') {
                            $group_price -= Auth::user()->customeringroup->group->discount;
                        }
                        $group_price += $tax;
                        if ($discount_applicable) {
                            if ($price < $group_price) {
                                $price = $price;
                            } else {
                                $price = $group_price;
                            }
                        } else {
                            $price = $group_price;
                        }
                    }
                }
            }
        }

        return $price;
    }
}

if (! function_exists('check_category_discount_product')) {
    function check_category_discount_product($product)
    {
        try {
            if ($product->category != null && strtotime(date('Y-m-d H:i:s')) >= $product->category->start_date && strtotime(date('Y-m-d H:i:s')) <= $product->category->end_date && $product->category->status == 1) {
                return 1;
            } else {
                return 0;
            }
        } catch (Exception $e) {

        }

        return 0;
    }
}

if (! function_exists('check_brand_discount_product')) {
    function check_brand_discount_product($product)
    {
        try {
            if ($product->brand != null && strtotime(date('Y-m-d H:i:s')) >= $product->brand->start_date && strtotime(date('Y-m-d H:i:s')) <= $product->brand->end_date && $product->brand->status == 1) {
                return 1;
            } else {
                return 0;
            }
        } catch (Exception $e) {

        }

        return 0;
    }
}

// Check App Price
if (! function_exists('check_app_discount_product')) {
    function check_app_discount_product($product)
    {
        // dd(date('d-m-Y', $product->app_discount_end_date));
        try {
            if ($product->app_discount != null && strtotime(date('Y-m-d H:i:s')) >= $product->app_discount_start_date && strtotime(date('Y-m-d H:i:s')) <= $product->app_discount_end_date) {
                return 1;
            } else {
                return 0;
            }
        } catch (Exception $e) {

        }

        return 0;
    }
}

// get discount percentage
if (! function_exists('get_discount_percentage')) {
    function get_discount_percentage($stroked_price, $main_price)
    {
        // Remove currency symbol and comma from the prices
        $strokedPrice = str_replace(['৳', ','], '', $stroked_price);
        $discountedPrice = str_replace(['৳', ','], '', $main_price);

        // Convert the string prices to float values
        $strokedPrice = floatval($strokedPrice);
        $discountedPrice = floatval($discountedPrice);

        $discountPercentage = (($strokedPrice - $discountedPrice) / $strokedPrice) * 100;

        // Round the discount percentage to two decimal places
        $discountPercentage = round($discountPercentage, 2);

        return $discountPercentage;
    }
}

// Get only the minimum price by variant from all discounted prices
if (! function_exists('getMinimumPriceByVariant')) {
    // function getMinimumPriceByVariantOld($product, $variant = null, $for = 'web', $quantity = 1, $user_info = null)
    // {
    //     if(empty($product)){
    //         $product = Product::find($variant->product_id);
    //     }
    //     $price = $variant->price ?? $variant->stock_price ?? $product->unit_price;
    //     $group_price = $variant->price ?? $variant->stock_price ?? $product->unit_price;
    //     $tax = 0;

    //     $discount_applicable = false;
    //     $discountStartDate = $product->discount_start_date ?? null;
    //     $discountEndDate = $product->discount_end_date ?? null;
    //     if (is_null($discountStartDate)) {
    //         $discount_applicable = true;
    //     } elseif (strtotime(date('d-m-Y H:i:s')) >= $discountStartDate && strtotime(date('d-m-Y H:i:s')) <= $discountEndDate) {
    //         $discount_applicable = true;
    //     }

    //     if(isset($product->taxes)){
    //         foreach ($product->taxes as $product_tax) {
    //             if ($product_tax->tax_type == 'percent') {
    //                 $tax += ($price * $product_tax->tax) / 100;
    //             } elseif ($product_tax->tax_type == 'amount') {
    //                 $tax += $product_tax->tax;
    //             }
    //         }
    //     }
    //     $price += $tax;

    //     $prices = get_all_discount_prices_by_variant($product, $group_price, $for, $quantity);
    //     $price = min($prices);
    //     if($user_info!=null){
    //         if($user_info->customeringroup){
    //             $discount_status = $user_info->customeringroup->group->discount_status;
    //             $start_date = $user_info->customeringroup->group->start_date;
    //             $end_date = $user_info->customeringroup->group->end_date;
    //             $cur_date = strtotime(date('Y-m-d H:i:s'));
    //             if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date){
    //                 if ($user_info->customeringroup->group->discount_type == 'percent') {
    //                     $group_price -= ($group_price * $user_info->customeringroup->group->discount) / 100;
    //                 } elseif ($user_info->customeringroup->group->discount_type == 'amount') {
    //                     $group_price -= $user_info->customeringroup->group->discount;
    //                 }
    //                 $group_price += $tax;
    //                 if($discount_applicable) {
    //                     if($price < $group_price){
    //                         $price = $price;
    //                     }else{
    //                         $price = $group_price;
    //                     }
    //                 }else{
    //                     $price = $group_price;
    //                 }
    //             }
    //         }
    //     }else{
    //         if(auth()->check()){
    //             if(auth()->user()->customeringroup){
    //                 $discount_status = auth()->user()->customeringroup->group->discount_status;
    //                 $start_date = auth()->user()->customeringroup->group->start_date;
    //                 $end_date = auth()->user()->customeringroup->group->end_date;
    //                 $cur_date = strtotime(date('Y-m-d H:i:s'));
    //                 if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date){
    //                     if (auth()->user()->customeringroup->group->discount_type == 'percent') {
    //                         $group_price -= ($group_price * auth()->user()->customeringroup->group->discount) / 100;
    //                     } elseif (auth()->user()->customeringroup->group->discount_type == 'amount') {
    //                         $group_price -= auth()->user()->customeringroup->group->discount;
    //                     }
    //                     $group_price += $tax;
    //                     if ($discount_applicable) {
    //                         if($price < $group_price){
    //                             $price = $price;
    //                         }else{
    //                             $price = $group_price;
    //                         }
    //                     }else{
    //                         $price = $group_price;
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $price;
    // }

    /* Optimized function with less code repetition */
    function getMinimumPriceByVariant($product, $variant = null, $for = 'web', $quantity = 1, $user_info = null)
    {
        if (empty($product) && !empty($variant)) {
            $product = Product::with('category', 'brand', 'stocks')->find($variant->product_id);
        } elseif (empty($product) && empty($variant)) {
            return 0; // or throw an exception
        }

        $price = $variant->price ?? $variant->stock_price ?? $product->unit_price;
        $group_price = $variant->price ?? $variant->stock_price ?? $product->unit_price;
        $tax = 0;

        $discount_applicable = false;
        $discountStartDate = $product->discount_start_date ?? null;
        $discountEndDate = $product->discount_end_date ?? null;
        if (is_null($discountStartDate) || (strtotime(date('d-m-Y H:i:s')) >= $discountStartDate && strtotime(date('d-m-Y H:i:s')) <= $discountEndDate)) {
            $discount_applicable = true;
        }

        if (isset($product->taxes)) {
            foreach ($product->taxes as $product_tax) {
                if ($product_tax->tax_type == 'percent') {
                    $tax += ($price * $product_tax->tax) / 100;
                } elseif ($product_tax->tax_type == 'amount') {
                    $tax += $product_tax->tax;
                }
            }
        }
        $price += $tax;

        $prices = get_all_discount_prices_by_variant($product, $group_price, $for, $quantity);
        $filteredPrices = collect($prices)->filter(fn($v) => is_numeric($v))
            ->unique()->values()->toArray();
        $price = min($filteredPrices);

        if (is_null($user_info)) {
            $user_info = auth($for === 'app' ? 'api' : 'web')->check() ? auth($for === 'app' ? 'api' : 'web')->user() : null;
        }

        if (! is_null($user_info) && $user_info->customeringroup) {
            $group = $user_info->customeringroup?->group ?? null;
            if (! is_null($group)) {
                $discount_status = $group->discount_status;
                $start_date = $group->start_date;
                $end_date = $group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date) {
                    if ($group->discount_type == 'percent') {
                        $group_price -= ($group_price * $group->discount) / 100;
                    } elseif ($group->discount_type == 'amount') {
                        $group_price -= $group->discount;
                    }
                    $group_price += $tax;
                    $price = $discount_applicable
                        ? min($price, $group_price)
                        : $group_price;
                }
            }
        }

        return min($price, ...$filteredPrices);
    }
}

// All discounted prices for a product
if (! function_exists('get_all_discount_prices_by_variant')) {
    function get_all_discount_prices_by_variant($product, $stock_price, $stack = 'web', $quantity = 1)
    {
        // dd($product);
        $unitprice = $stock_price ?? $product->unit_price;

        // Product Discount Price
        $pdiscountprice = $unitprice;
        $discount_applicable = false;
        $discountStartDate = $product->discount_start_date ?? null;
        $discountEndDate = $product->discount_end_date ?? null;
        if (is_null($discountStartDate) || (strtotime(date('d-m-Y H:i:s')) >= $discountStartDate && strtotime(date('d-m-Y H:i:s')) <= $discountEndDate)) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            $discountType = $product->discount_type ?? null;
            if ($discountType == 'percent') {
                $pdiscountprice -= ($pdiscountprice * $product->discount) / 100;
            } elseif ($discountType == 'amount') {
                $pdiscountprice -= $product->discount;
            }
        }

        // Break Down Price
        $breakdownprice = $unitprice;
        $breakdownprices = [];
        if (! empty($product->productprices)) {
            $productprice = $product->productprices->where('start_qty', '<=', $quantity)->where('end_qty', '>=', $quantity)->first();
            if ($productprice) {
                $breakdownprice = $productprice->price;
                if ($discount_applicable) {
                    if ($product->discount_type == 'percent') {
                        $breakdownprice -= ($productprice->price * $product->discount) / 100;
                    } elseif ($product->discount_type == 'amount') {
                        $breakdownprice -= $product->discount;
                    }
                }
            }
            foreach ($product->productprices as $key => $value) {
                $breakdownpriceff = $value->price;
                if ($discount_applicable) {
                    if ($product->discount_type == 'percent') {
                        $breakdownpriceff -= ($breakdownpriceff * $product->discount) / 100;
                    } elseif ($product->discount_type == 'amount') {
                        $breakdownpriceff -= $product->discount;
                    }
                }
                $breakdownprices[$key] = $breakdownpriceff;
            }
        }

        // Flash Deal
        $flashdealprice = $unitprice;
        $flash_deal_check = check_flash_deal_product($product);
        if ($flash_deal_check) {
            if ($product->discount_type == 'percent') {
                $flashdealprice -= ($flashdealprice * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $flashdealprice -= $product->discount;
            }
        }

        // Category Discount
        $categorydiscountprice = $unitprice;
        $category_discount_check = check_category_discount_product($product);
        if ($category_discount_check) {
            if ($product->category->discount_type == 'percent') {
                $categorydiscountprice -= ($categorydiscountprice * $product->category->discount) / 100;
            } elseif ($product->category->discount_type == 'amount') {
                $categorydiscountprice -= $product->category->discount;
            }
        }

        // Brand Discount
        $branddiscountprice = $unitprice;
        $brand_discount_check = check_brand_discount_product($product);
        if ($brand_discount_check) {
            if ($product->brand->discount_type == 'percent') {
                $branddiscountprice -= ($branddiscountprice * $product->brand->discount) / 100;
            } elseif ($product->brand->discount_type == 'amount') {
                $branddiscountprice -= $product->brand->discount;
            }
        }

        $prices = [
            'Product Discount Price' => $pdiscountprice,
            'Break Down Price' => $breakdownprice,
            'Break Down Prices' => $breakdownprices,
            'Flash Deal Price' => $flashdealprice,
            'Category Discount Price' => $categorydiscountprice,
            'Brand Discount Price' => $branddiscountprice,
        ];
        if ($stack != 'web') {
            $prices['App Price'] = app_price($product, $stock_price, false);
        }

        return $prices;
    }
}

// Check stock if product is pre_order active
if (! function_exists('check_in_stock')) {
    function check_in_stock($product)
    {
        $in_stock = ($product->stocks?->first()?->qty ?? 0) > 0 ? true : false;
        try {
            $in_stock = check_preorder_product($product) ? true : $in_stock;
        } catch (Exception $e) {

        }

        return $in_stock;
    }
}

if (! function_exists('phone_number_format')) {
    function phone_number_format($number)
    {

        $format_number = substr($number, 0, -10).' '.
                         substr($number, -10, 4).' '.
                         substr($number, -6);

        return $format_number;
    }
}

if (! function_exists('flash_deal_count')) {
    function flash_deal_count()
    {
        $today = strtotime(date('y-m-d h:i:s'));
        $data = FlashDeal::where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>', $today)
            ->count();

        return $data;
    }
}

/**
 * Template body text replace
 */
if (! function_exists('templateReplace')) {
    function templateReplace($template, $arr)
    {
        $templateResult = $template;
        foreach ($arr as $key => $value) {
            $templateResult = str_replace('{{'.$key.'}}', $value, $templateResult);
        }

        return $templateResult;
    }
}

/*
* Convert Reawrd Point to Amount
*/
if (! function_exists('convert_point_to_amount')) {
    function convert_point_to_amount($redeeminfo, $applicablepoint)
    {
        $amount = ($redeeminfo->earn_amount * $applicablepoint) / $redeeminfo->spent_point;

        return floor($amount);
    }
}

/*
* Convert Amount to Reward Point
*/
if (! function_exists('convert_amount_to_point')) {
    function convert_amount_to_point($earnactioninfo, $applicableamount)
    {
        $point = ($earnactioninfo->earn_point * $applicableamount) / $earnactioninfo->spent_amount;

        return floor($point);
    }
}
/**
 * ads show function
 */
if (! function_exists('ads_show')) {
    function ads_show($position)
    {
        $now = date('Y-m-d H:i:s');
        $ads = Advertizement::with(['product', 'category', 'brand'])->where('ads_type', 'web')->where('position', $position)
            ->where('status', 1)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        if (count($ads) > 0) {
            return $ads;
        }

        return false;
    }
}
// if more text then ... or view more

if (! function_exists('custom_text_replace')) {
    function custom_text_replace($data)
    {
        $str = $data['message'];
        $maxLength = $data['max_length'] ?? 100;
        $start = $data['start'] ?? 0;
        $btn = $data['btn'] ?? ' ...';

        if (strlen($str) > $maxLength) {
            $excerpt = substr($str, $start, $maxLength - 3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt = substr($excerpt, 0, $lastSpace);
            $excerpt .= $btn;
        } else {
            $excerpt = $str;
        }

        return $excerpt;
    }
}

// Unread notification count

if (! function_exists('unread_notification')) {
    function unread_notification($user_id = null)
    {
        // return $user_id;
        if ($user_id == null) {
            $user_id = Auth::guard('web')->id();
        } else {
            $user_id = $user_id;
        }
        $notifications_unread = 0;
        // $where_not_in = 'id NOT IN (SELECT notification_id FROM user_notification_reads)';
        // $where_cond = $where_not_in;
        $notifications_unread = UserNotification::leftJoin('user_notification_reads', function ($join) use ($user_id) {
            $join->on('user_notifications.id', '=', 'user_notification_reads.notification_id')
                ->where('user_notification_reads.user_id', '=', $user_id);
        })
            ->whereNull('user_notification_reads.notification_id')
            ->count();

        return $notifications_unread;
    }
}

// Laracon21 combinations alternative
if (! function_exists('makeCombinations')) {
    function makeCombinations($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }

        return $result;
    }
}

// Check If Coupon Discount Applicable Before Place Order. $source = API, WEB
if (! function_exists('check_coupon_discount')) {
    function check_coupon_discount($user_id, $cart_items = null, $source = null)
    {
        try {
            // code...
            if (empty($cart_items)) {
                $cart_items = Cart::withoutGlobalScopes()->with('product')->where('user_id', $user_id)->get();
            }

            if ($cart_items->isEmpty()) {
                return [
                    'combined_order_id' => 0,
                    'result' => false,
                    'message' => ('Cart is empty'),
                ];
            }

            $coupon = null;
            if (isset($cart_items->first()->coupon_code)) {
                $coupon = Coupon::where('status', 1)->where('code', $cart_items->first()->coupon_code)->first();
            }

            if (empty($coupon)) {
                $data = [
                    'result' => true,
                    'message' => ('Invalid coupon code!'),
                ];

                return $data;
            }

            // check discount product
            foreach ($cart_items as $key => $item) {
                if (check_discount_product_from_cart($item->product_id) == true && $coupon->force_apply == 0) {
                    return [
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => ('COUPON Not Available For Discounted Products.'),
                    ];
                }
            }

            // Group Discount Section
            if (! empty($user_id)) {
                $user_info = User::with('customeringroup.group')->find($user_id);
                if ($user_info && $user_info->customeringroup) {
                    $discount_status = $user_info->customeringroup->group?->discount_status;
                    $start_date = $user_info->customeringroup->group?->start_date;
                    $end_date = $user_info->customeringroup->group?->end_date;
                    $cur_date = strtotime(date('Y-m-d H:i:s'));
                    if ($discount_status == 1 && $cur_date >= $start_date && $cur_date <= $end_date && $coupon->force_apply == 0) {
                        return [
                            'combined_order_id' => 0,
                            'result' => false,
                            'message' => translate('You already have a group discount!'),
                        ];
                    }
                }
            }

            $in_range = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;

            if (! $in_range) {
                return [
                    'combined_order_id' => 0,
                    'result' => false,
                    'message' => ('Coupon expired!'),
                ];
            }

            $is_used = CouponUsage::where('user_id', $user_id)->where('coupon_id', $coupon->id)->first() != null;

            if ($coupon->usage_limit == 'single') {
                if ($is_used) {
                    return [
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => ('You already used this coupon!'),
                    ];
                }
            }

            $coupon_details = json_decode($coupon->details);

            if ($coupon->type === 'cart_base') {
                $sum = $cart_items->sum(function ($cartItem) {
                    return $cartItem['price'] * $cartItem['quantity'];
                });

                if ($sum >= $coupon_details->min_buy) {
                    if ($coupon->discount_type == 'percent') {
                        $coupon_discount = ($sum * $coupon->discount) / 100;
                        if ($coupon_discount > $coupon_details->max_discount) {
                            $coupon_discount = $coupon_details->max_discount;
                        }
                    } elseif ($coupon->discount_type == 'amount') {
                        $coupon_discount = $coupon->discount;
                    }

                    Cart::where('user_id', $user_id)->update([
                        'discount' => $coupon_discount / count($cart_items),
                        'coupon_code' => $coupon->code,
                        'coupon_applied' => 1,
                    ]);

                    return [
                        'result' => true,
                        'message' => ('Coupon Applied'),
                    ];
                } else {
                    return [
                        'combined_order_id' => 0,
                        'result' => false,
                        'message' => ('Minimum order amount needed to apply this coupon!'),
                    ];
                }
            } elseif ($coupon->type === 'product_base') {
                $coupon_discount = 0;
                foreach ($cart_items as $key => $cartItem) {
                    foreach ($coupon_details as $key => $coupon_detail) {
                        if ($coupon_detail->product_id == $cartItem['product_id']) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount += $cartItem['price'] * $coupon->discount / 100;
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount += $coupon->discount;
                            }
                        }
                    }
                }

                Cart::where('user_id', $user_id)->update([
                    'discount' => $coupon_discount / count($cart_items),
                    'coupon_code' => $coupon->code,
                    'coupon_applied' => 1,
                ]);

                return [
                    'result' => true,
                    'message' => ('Coupon Applied'),
                ];

            }
        } catch (\Throwable $th) {
            // throw $th;
            return [
                'combined_order_id' => 0,
                'result' => false,
                'message' => 'Something went wrong! Please try again',
            ];
        }
    }
}

if (! function_exists('is_coupon_valid')) {
    function is_coupon_valid(Coupon $coupon, $userId = null, $source = 'web')
    {
        if ($coupon->only_for_app && $source === 'web') {
            return ['status' => false, 'message' => 'This coupon is for app users only!', 'code' => 403];
        }
        if (filled($coupon->group_ids)) {
            if (Str::startsWith($userId, 'tmp') || is_null($userId)) {
                return ['status' => false, 'message' => $coupon->description ?: 'This coupon is for a specific customer group only!', 'code' => 403];
            }
            $groupMember = Customeringroup::where('user_id', $userId)
                ->whereIn('customer_groups_id', $coupon->group_ids)
                ->first();
            if (!$groupMember) {
                return ['status' => false, 'message' => $coupon->description ?: 'This coupon is for a specific customer group only!', 'code' => 403];
            }
            return ['status' => true, 'message' => '', 'code' => 200];
        }
        // Assigned to system users like staffs or affiliates
        $couponAssignedToEmployee = $coupon->assigned_to;

        if (Str::startsWith($userId, 'tmp') || is_null($userId)) {
            return ['status' => true, 'message' => '', 'code' => 200];
        }

        // Assigned to specific customer
        $couponAssignedToCustomer = \App\Models\CouponCustomerAssignment::where('customer_id', $userId)->where('coupon_id', $coupon->id)->first();

        if ($couponAssignedToEmployee && (! $couponAssignedToCustomer || $couponAssignedToCustomer->customer_id != $userId)) {
            return ['status' => false, 'message' => ('You can not use this coupon code!'), 'code' => 403];
        }

        if ($couponAssignedToCustomer && Carbon::parse($couponAssignedToCustomer->expire_date)->isPast()) {
            return ['status' => false, 'message' => ('You can not use this coupon code!'), 'code' => 403];
        }

        // Check For Used or Not
        if ($couponAssignedToCustomer && ! Carbon::parse($couponAssignedToCustomer->expire_date)->isPast()) {
            $is_used = $couponAssignedToCustomer->is_used;
        } else {
            $is_used = CouponUsage::where('user_id', $userId)->where('coupon_id', $coupon->id)->exists();
        }

        if (strtolower($coupon->usage_limit) === 'single' && $is_used) {
            return ['status' => false, 'message' => ('You have already used this coupon code'), 'code' => 403];
        }

        return ['status' => true, 'message' => '', 'code' => 200];
    }
}

// Shipping discount available or not
if (! function_exists('check_shipping_discount')) {
    function check_shipping_discount()
    {

        $now = now()->timestamp;
        $discount = DB::table('shipping_discounts')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('status', 1)
            ->first();

        if ($discount) {
            return true;
        }

        return false;
    }
}

if (! function_exists('check_shipping_discount_product')) {
    function check_shipping_discount_product($ids = [], $zoneid = null)
    {

        $now = now()->timestamp;

        // Check if any shipping discount available with zone id 0
        $zone0Discount = DB::table('shipping_discounts')->where('zone_id', 0)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->first();

        $allDiscount = DB::table('shipping_discounts')->where('zone_id', isset($zone0Discount) ? 0 : $zoneid)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->where('type', 'all')->first();
        if ($allDiscount) {
            return ['amount' => $allDiscount->s_charge, 'status' => true, 'min_amount' => $allDiscount->threshold_amount];
        }

        if (! empty($ids)) {
            $ids = array_map('strval', $ids);

            $result = [];
            $continue = true;
            foreach ($ids as $id) {
                $productDiscount = DB::table('shipping_discounts')->where('zone_id', isset($zone0Discount) ? 0 : $zoneid)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->where('type', 'product')->whereJsonContains('details', $id)->first();
                if ($productDiscount) {
                    $result[$id] = ['amount' => $productDiscount->s_charge, 'status' => true, 'min_amount' => $productDiscount->threshold_amount];
                    $continue = false;
                }
            }

            if ($continue) {
                foreach ($ids as $id) {
                    $product = Product::find($id);
                    if ($product) {
                        $brandDiscount = DB::table('shipping_discounts')->where('zone_id', isset($zone0Discount) ? 0 : $zoneid)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->where('type', 'brand')->whereJsonContains('details', strval($product->brand_id))->first();

                        if ($brandDiscount) {
                            $result[$id] = ['amount' => $brandDiscount->s_charge ?? 0, 'status' => true, 'min_amount' => $brandDiscount->threshold_amount];
                            $continue = false;
                        }
                    }
                }
            }

            if ($continue) {
                // $result = array();
                foreach ($ids as $id) {
                    $product = Product::find($id);
                    $categoryDiscount = DB::table('shipping_discounts')->where('zone_id', isset($zone0Discount) ? 0 : $zoneid)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->where('type', 'category')->whereJsonContains('details', strval($product->category_id))->first();

                    if ($categoryDiscount) {
                        $result[$id] = ['amount' => $categoryDiscount->s_charge ?? 0, 'status' => true, 'min_amount' => $categoryDiscount->threshold_amount];
                    }
                }
            }

            if (count($ids) > count($result)) {
                // flash(('Revoking shipping shipping discount as you can not add mixed product for shipping discounts'))->info();
                return ['amount' => 0, 'status' => false, 'min_amount' => PHP_INT_MAX];
            } elseif (count($ids) == count($result)) {
                return ! empty($result) ? reset($result) : [];
            } else {
                return ['amount' => 0, 'status' => false, 'min_amount' => PHP_INT_MAX];
            }
        }

        return ['amount' => 0, 'status' => false, 'min_amount' => PHP_INT_MAX];
    }
}

if (! function_exists('check_shipping_discount_carts')) {
    function check_shipping_discount_carts(\Illuminate\Support\Collection $carts, ?int $zone_id)
    {
        $now = now()->timestamp;

        $carts = $carts->loadMissing(['product', 'product.brand:id', 'product.category:id']);
        $productIds = $carts->pluck('product_id')->toArray();
        $brandIds = $carts->pluck('product.brand.id')->filter()->unique()->toArray();
        $categoryIds = $carts->pluck('product.category.id')->filter()->unique()->toArray();

        // Check if any shipping discount available with zone id 0
        $zone0Discount = DB::table('shipping_discounts')->where('zone_id', 0)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->first();
        if ($zone0Discount) {
            $details = json_decode($zone0Discount->details, true) ?? [];
            $checkFor = match ($zone0Discount->type) {
                'product' => $productIds,
                'brand' => $brandIds,
                'category' => $categoryIds,
                default => []
            };
            if (! empty($checkFor) && any_in_array($checkFor, $details)) {
                return ['amount' => $zone0Discount->s_charge, 'status' => true, 'min_amount' => $zone0Discount->threshold_amount];
            }
        }

        $allDiscount = DB::table('shipping_discounts')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('status', 1)
            ->where('type', 'all')
            ->when(! is_null($zone_id), function ($query) use ($zone_id) {
                return $query->where('zone_id', $zone_id);
            })
            ->first();
        if ($allDiscount) {
            return ['amount' => $allDiscount->s_charge, 'status' => true, 'min_amount' => $allDiscount->threshold_amount];
        }

        return ['amount' => 0, 'status' => false, 'min_amount' => PHP_INT_MAX];
    }
}

if (! function_exists('storeJsonData')) {
    function storeJsonData()
    {
        try {
            // code...
            $languageFilePath = storage_path('app/public/languages/language.json');
            if (! file_exists($languageFilePath)) {
                $items = DB::table('languages')->get();
                $jsonData = $items->toJson();
                // file_put_contents($languageFilePath, $jsonData);
                Storage::disk('public')->put('languages/language.json', $jsonData);
            }

            $currencyFilePath = storage_path('app/public/currencies/currency.json');
            if (! file_exists($currencyFilePath)) {
                $rows = DB::table('currencies')->get();
                $jsonRowData = $rows->toJson();
                // file_put_contents($currencyFilePath, $jsonRowData);
                Storage::disk('public')->put('currencies/currency.json', $jsonRowData);
            }

            $categoryFilePath = storage_path('app/public/categories/category.json');
            if (! file_exists($categoryFilePath)) {
                $rows = Category::all();
                $jsonRowData = $rows->toJson();
                // file_put_contents($categoryFilePath, $jsonRowData);
                Storage::disk('public')->put('categories/category.json', $jsonRowData);
            }

            $shippingMethodFilePath = storage_path('app/public/shipping/methods.json');
            if (! file_exists($shippingMethodFilePath)) {
                $rows = DB::table('shipping_methods')->get();
                $jsonRowData = $rows->toJson();
                // file_put_contents($shippingMethodFilePath, $jsonRowData);
                Storage::disk('public')->put('shipping/methods.json', $jsonRowData);
            }

            $countriesFilePath = storage_path('app/public/countries/countries.json');
            if (! file_exists($countriesFilePath)) {
                $rows = DB::table('countries')->get();
                $jsonRowData = $rows->toJson();
                // file_put_contents($countriesFilePath, $jsonRowData);
                Storage::disk('public')->put('countries/countries.json', $jsonRowData);
            }
        } catch (\Throwable $th) {
            // throw $th;
            Log::alert($th->getMessage());
        }
    }
}

if (! function_exists('getLinkType')) {
    function getLinkType($url)
    {
        $uri_Data = getRouteAndParams($url);
        $uri_segments = explode('/', $url);
        if (strpos($url, '.html') != false) {
            $type = 'product';
            if ($uri_Data && $uri_Data['route_name'] == 'product') {
                $wth = $uri_Data['params']['slug'];
            } else {
                $wth = substr($uri_segments[3], 0, -5);
            }
        } else {
            $type = $uri_segments[3] ?? '';
        }
        if ($type == 'product') {
            $id = Product::where('slug', $wth)->first()->id ?? '';
        } elseif ($type == 'category') {
            $id = Category::where('slug', $uri_segments[4])->first()->id ?? '';
        } elseif ($type == 'brand') {
            $id = Brand::where('slug', $uri_segments[4])->first()->id ?? '';
        } elseif ($type == 'flash-deal') {
            $id = FlashDeal::where('slug', $uri_segments[4])->first()->id ?? '';
        } else {
            $id = $uri_segments[4] ?? '';
        }

        return [
            'id' => $id,
            'type' => $type,
            'link' => $url,
        ];
    }
}

if (! function_exists('getRouteAndParams')) {
    function getRouteAndParams($url)
    {
        // Parse the URL and get the path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];

        // Create a new request instance with the given URL path
        $request = Request::create($path);

        // Iterate through all routes and match the one for the URL path
        $matchedRoute = null;
        $params = [];

        foreach (Route::getRoutes() as $route) {
            if ($route->matches($request)) {
                $matchedRoute = $route;
                $params = $route->bind($request)->parameters(); // Get route parameters
                break;
            }
        }

        if ($matchedRoute) {
            return [
                'route_name' => $matchedRoute->getName(),  // Get the route name
                'uri' => $matchedRoute->uri(),             // Get the route URI
                'params' => $params,                        // Get route parameters
            ];
        }

        return null;
    }
}

if (! function_exists('get_yt_thumb')) {
    /**
     * Get YouTube video thumbnail URL based on the video URL and type.
     *
     * @param  string  $url  The YouTube video URL.
     * @param  int  $quality  The quality of the thumbnail (1: default, 2: mqdefault, 3: hqdefault, 4: sddefault, 5: maxresdefault).
     *
     * @default quality 2 (mqdefault).
     *
     * @return string The URL of the thumbnail image.
     */
    function get_yt_thumb(string $url, int $quality = 2): string
    {
        // Allowed thumbnail types
        $qualities = [
            'default', // 120x90
            'mqdefault', // 320x180
            'hqdefault', // 480x360
            'sddefault', // 640x480
            'maxresdefault', // 1280x720
        ];

        // Validate thumbnail type
        if (! array_key_exists($quality - 1, $qualities)) {
            $quality = 2; // Default to 'mqdefault' if invalid type
        }

        // Extract video ID from URL
        $videoId = get_yt_video_id($url);

        if ($videoId) {
            return "https://img.youtube.com/vi/{$videoId}/".$qualities[$quality - 1].'.jpg';
        }

        return static_asset('images/default-thumbnail.jpg'); // Fallback to a default thumbnail
    }
}

if (! function_exists('get_yt_video_id')) {
    /**
     * Get YouTube video ID from the video URL.
     *
     * @param  string  $url  The YouTube video URL.
     * @return string|null The video ID or null if not found.
     */
    function get_yt_video_id(string $url): ?string
    {
        // Extract video ID from URL
        $videoId = null;

        // Standard URL (https://www.youtube.com/watch?v=VIDEO_ID)
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // Short URL (https://youtu.be/VIDEO_ID)
        elseif (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // Embedded URL (https://www.youtube.com/embed/VIDEO_ID)
        elseif (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // YouTube Shorts URL (https://www.youtube.com/shorts/VIDEO_ID)
        elseif (preg_match('/youtube\.com\/shorts\/([^\&\?\/]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId;
    }
}

if (! function_exists('get_yt_embed')) {
    /**
     * Get YouTube video embed URL based on the video URL.
     *
     * @param  string  $url  The YouTube video URL.
     * @return string The embed URL of the video.
     */
    function get_yt_embed(string $url): string
    {
        // Extract video ID from URL
        $videoId = get_yt_video_id($url);

        if ($videoId) {
            return "https://www.youtube.com/embed/{$videoId}";
        }

        return ''; // Return empty string if no valid video ID found
    }
}

if (! function_exists('push_products_to_rokomari')) {
    function push_products_to_rokomari()
    {
        if (get_setting('enable_rokomari_service') != 1) {
            return;
        }

        Artisan::call('product:push rokomari');

    }
}

if (! function_exists('update_products_stock')) {
    function update_products_stock(array $productIds): void
    {
        if (get_setting('enable_rokomari_service') == 1) {
            Log::channel('merchant')->info('Updating product stock for Rokomari', ['productIds' => $productIds]);
            UpdateProductStockForRokomari::dispatch($productIds);
        }
        if (get_setting('enable_kireibd_service') == 1) {
            Log::channel('merchant')->info('Updating product stock for Kireibd', ['productIds' => $productIds]);
            KireibdJob::dispatch($productIds, 'stock');
        }

    }
}

if (! function_exists('record_order_cancellation')) {
    function record_order_cancellation(array $data): bool
    {
        try {
            \App\Models\OrderCancellation::updateOrCreate([
                'order_id' => $data['order_id'],
            ], $data);

            return true;
        } catch (\Exception $e) {
            Log::error(
                "Failed to store order cancellation info for order_id {$data['order_id']}: ".$e->getMessage(),
                ['data' => $data, 'trace' => $e->getTraceAsString()]
            );

            return false;
        }
    }
}

if (! function_exists('readableNumber')) {
    function readableNumber($n)
    {
        // check laravel version
        if (version_compare(app()->version(), '10.0.0', '>=')) {
            return \Illuminate\Support\Number::abbreviate($n);
        }

        return customReadableNumber($n);
    }
}

if (! function_exists('customReadableNumber')) {
    function customReadableNumber($n)
    {
        $n = (int) $n;
        if ($n >= 0 && $n < 1000) {
            // 1 - 999
            $n_format = floor($n);
            $suffix = '';
        } elseif ($n >= 1000 && $n < 1000000) {
            // 1k-999k
            $n_format = floor($n / 1000);
            $suffix = 'K+';
        } elseif ($n >= 1000000 && $n < 1000000000) {
            // 1m-999m
            $n_format = floor($n / 1000000);
            $suffix = 'M+';
        } elseif ($n >= 1000000000 && $n < 1000000000000) {
            // 1b-999b
            $n_format = floor($n / 1000000000);
            $suffix = 'B+';
        } elseif ($n >= 1000000000000) {
            // 1t+
            $n_format = floor($n / 1000000000000);
            $suffix = 'T+';
        }

        return ! empty($n_format.$suffix) ? $n_format.$suffix : 0;
    }
}

if (! function_exists('dynamic_api_middlewares')) {
    function dynamic_api_middlewares()
    {
        if (get_setting('guest_order_activation') == 1 && request()->header('source', 'app') === 'web') {
            return ['is_guest_user'];
        } else {
            return ['auth:api', 'is_guest_user'];
        }
    }
}

if (! function_exists('dynamic_web_middlewares')) {
    function dynamic_web_middlewares()
    {
        if (get_setting('guest_order_activation') == 1) {
            return ['is_guest_user:web'];
        } else {
            return ['user', 'verified', 'unbanned', 'is_guest_user:web'];
        }
    }
}

if (! function_exists('temp_user_id')) {
    function temp_user_id($user_id)
    {
        $tmp = \App\Models\TempUser::where('user_id', $user_id)->whereNotNull('temp_user_id')->first();

        return $tmp ? $tmp->temp_user_id : null;
    }
}

if (! function_exists('remove_previous_cart')) {
    function remove_previous_cart($user_id)
    {
        try {
            $carts = Cart::whereNotNull('user_id')->where('user_id', $user_id)->delete();
            Log::channel('custom')->info($carts.' Item removed from cart for user: '.$user_id);
        } catch (\Exception $e) {
            Log::channel('custom')->error('Error removing cart for user_id: '.$user_id.'. Error: '.$e->getMessage());
        }
    }
}

if (! function_exists('replace_temp_user_id')) {
    function replace_temp_user_id(?string $temp_user_id, ?int $user_id): void
    {
        // $temp_user_id = temp_user_id($user_id);
        if (! $temp_user_id || ! $user_id) {
            return;
        }
        Address::whereNotNull('temp_user_id')->where('temp_user_id', $temp_user_id)->update(['user_id' => $user_id, 'temp_user_id' => null]);
        $defaultAddress = Address::latest()->where('user_id', $user_id)->where('set_default', 1)->get();
        if ($defaultAddress->count() > 1) {
            foreach ($defaultAddress as $key => $address) {
                if ($key > 0) {
                    $address->set_default = 0;
                    $address->save();
                }
            }
        }
        Cart::withoutGlobalScopes()->whereNotNull('temp_user_id')->where('temp_user_id', $temp_user_id)->update(['user_id' => $user_id, 'temp_user_id' => null]);
        \App\Models\Wishlist::whereNotNull('temp_user_id')->where('temp_user_id', $temp_user_id)->update(['user_id' => $user_id, 'temp_user_id' => null]);
        CouponUsage::whereNotNull('temp_user_id')->where('temp_user_id', $temp_user_id)->update(['user_id' => $user_id, 'temp_user_id' => null]);
        \App\Models\TempUser::whereNotNull('temp_user_id')->where('temp_user_id', $temp_user_id)->delete();

        Session::forget('temp_user_id');
    }
}

/**
 * @param  string  $fullUrl
 * @param  string|null  $type  (category, brand, flash-deal etc)
 * @return string new frontend url
 */
if (! function_exists('to_frontend')) {
    function to_frontend(string $fullUrl, ?string $type = null): string
    {
        $frontendUrl = config('app.frontend') ?? null;
        if (! $frontendUrl) {
            return $fullUrl;
        }
        $parsedFullUrl = parse_url($fullUrl);

        $path = isset($parsedFullUrl['path']) ? Str::afterLast($parsedFullUrl['path'], '/') : '';

        if ($path) {
            $frontendUrl .= match ($type) {
                'category' => '/category',
                'brand' => '/brand',
                'flash-deals' => '/flash-deals',
                'page' => '/page',
                'blog' => '/blog',
                'sitemaps' => '/sitemaps',
                'job' => '/jobs',
                'product' => '/product',
                default => '/product'
            };
            $frontendUrl .= '/'.$path;
        }

        if (isset($parsedFullUrl['query'])) {
            $frontendUrl .= '?'.$parsedFullUrl['query'];
        }

        return $frontendUrl;
    }
}

if (! function_exists('getMaxProductsCount')) {
    function getMaxProductsCount()
    {
        return Cache::remember('max_products_count', 86400, function () {
            return Product::published()->count();
        });
    }
}

if (! function_exists('isBanglaLanguage')) {
    function isBanglaLanguage($text)
    {
        if (preg_match('/[\x{0980}-\x{09FF}]/u', $text)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('isBase64Image')) {
    function isBase64Image($string): bool
    {
        if (! is_string($string) || empty($string)) {
            return false;
        }

        // Check if it contains base64 header (optional)
        if (preg_match('/^data:image\/(\w+);base64,/', $string, $match)) {
            $string = substr($string, strpos($string, ',') + 1);
        }

        // Base64 validation
        if (! base64_decode($string, true)) {
            return false;
        }

        // Check if decoded string is an actual image
        $imageData = base64_decode($string);

        // getimagesizefromstring returns false if not an image
        if (! @getimagesizefromstring($imageData)) {
            return false;
        }

        return true;
    }
}

if (! function_exists('record_product_visit')) {
    function record_product_visit(array $payloads, ?int $product_id = null): void
    {
        try {
            if (! $product_id) {
                return;
            }
            $source = request()->header('source', 'app') === 'web' ? 'website' : 'app';
            $payloads['utm_source'] ??= $source;
            $payloads['ref_id'] ??= null; // Represent affiliate_users id e.g. AFF135
            $userId = $payloads['user_id'] ?? null;
            if (Str::startsWith($userId, 'tmp')) {
                $userId = null;
            }

            \App\Models\ProductVisit::create([
                'product_id' => $product_id,
                'user_id' => $userId,
                'utm_source' => $payloads['utm_source'],
                'ref_id' => $payloads['ref_id'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Failed to record product visit for product_id {$product_id}: ".$e->getMessage(),
                ['payloads' => $payloads, 'trace' => $e->getTraceAsString()]
            );
        }
    }
}

if (! function_exists('trackOrder')) {
    function trackOrder(int $order_id, array $payloads): void
    {
        if (get_setting('enable_utm_order_tracking', 0) == 1) {
            $payloads['utm_source'] ??= null;
            $payloads['utm_medium'] ??= null;
            $payloads['utm_content'] ??= null;
            $payloads['utm_campaign'] ??= null;
            $payloads['ref_id'] ??= null; // Represent affiliate_users id e.g. AFF135
            $payloads['order_id'] = $order_id;
            if (! is_null($payloads['utm_source']) && ! in_array(strtolower($payloads['utm_source']), ['app', 'website'])) {
                \App\Models\OrderTrack::create($payloads);
            }
        }
    }
}

//
if (! function_exists('existsOrCreateUser')) {
    function existsOrCreateUser(array $payloads): ?User
    {
        if (empty($payloads)) {
            return null;
        }

        if (! empty($payloads['user_id']) && is_numeric($payloads['user_id'])) {
            $user = User::find($payloads['user_id']);
            if ($user) {
                return $user;
            }
        }

        $phone = trim(str_replace(['+88', '-', ' '], '', $payloads['phone'] ?? ''));
        if (str_starts_with($phone, '88') && strlen($phone) > 11) {
            $phone = substr($phone, 2);
        }

        $hasValidPhone = strlen($phone) == 11;

        if ($hasValidPhone) {
            $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        } else {
            $user = null;
        }

        if (! $user) {
            $user = new User;
            $user->name = $payloads['name'] ?? 'Walk In Customer';
            $user->email = $payloads['email'] ?? null;
            $user->address = $payloads['address'] ?? null;
            $user->phone = $hasValidPhone ? $phone : null;
            $password = Str::random(rand(8, 10));
            $user->password = bcrypt($password);
            $user->email_verified_at = now()->toDateTimeString();
            $user->recent_login = null;
            $user->save();

            $customer = new \App\Models\Customer;
            $customer->user_id = $user->id;
            $customer->save();

            $group = Customergroup::orderBy('ordering', 'asc')->first();

            if ($group && $group->exists) {
                $first_group = new Customeringroup;
                $first_group->user_id = $user->id;
                $first_group->customer_groups_id = $group->id;
                $first_group->status = 1;
                $first_group->save();
            }
            if ($hasValidPhone && replaceExistingOrdersByUser($user->id, $phone)) {
                fixUserGroup($user->id);
            }
            Log::channel('custom')->info("Created new user with ID {$user->id}", ['phone' => $phone]);
        }

        return $user;
    }
}

if (! function_exists('replaceExistingOrdersByUser')) {
    function replaceExistingOrdersByUser($userId, $phone): bool
    {
        try {
            $orders = Order::whereNull('user_id')
                ->whereJsonContains('shipping_address->phone', $phone)
                ->update([
                    'user_id' => $userId,
                    'guest_id' => null,
                ]);
            Log::channel('custom')->info("Replaced existing orders for user_id {$userId}", ['phone' => $phone, 'orders_updated' => $orders]);

            return $orders;
        } catch (\Exception $e) {
            // dd($e->getMessage());
            Log::error(
                "Failed to replace existing orders for user_id {$userId}: ".$e->getMessage(),
                ['phone' => $phone, 'trace' => $e->getTraceAsString()]
            );

            return false;
        }
    }
}

if (! function_exists('fixUserGroup')) {
    function fixUserGroup($userId): bool
    {
        try {
            $user = User::find($userId);
            if (! $user) {
                return false;
            }
            $defaultGroup = Customergroup::where('min_order_qty', 0)->first();
            $defaultGroupId = $defaultGroup->id ?? 1;
            $totalDeliveredOrders = Order::where([
                'user_id' => $userId,
                'delivery_status' => 'delivered',
            ])->get();
            $totalDeliveredAmount = round($totalDeliveredOrders->sum('grand_total') ?? 0);

            $deliveredCount = $totalDeliveredOrders->count();

            $eligibleGroup = getCustomerGroup($deliveredCount, $totalDeliveredAmount) ?? $defaultGroupId;

            Customeringroup::updateOrCreate(
                ['user_id' => $userId],
                [
                    'customer_groups_id' => $eligibleGroup,
                    'status' => 1,
                ]
            );

            $user->delivered_order = $deliveredCount;
            $user->save();

            Log::channel('custom')->info("Fixed user group for user_id {$userId}", ['eligible_group' => $eligibleGroup, 'delivered_count' => $deliveredCount, 'total_delivered_amount' => $totalDeliveredAmount]);

            return true;
        } catch (\Exception $e) {
            Log::error(
                "Failed to fix user group for user_id {$userId}: ".$e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );

            return false;
        }
    }
}

if (! function_exists('getDiscountShippingCharge')) {
    function getDiscountShippingCharge(\Illuminate\Support\Collection $carts, ?int $zone_id): int
    {
        $cartTotal = $carts->sum(function ($item) {
            return ($item->price + $item->tax) * $item->quantity - $item->discount;
        });

        $carts = $carts->loadMissing(['product', 'product.brand:id', 'product.category:id']);
        $productIds = $carts->pluck('product_id')->toArray();
        $brandIds = $carts->pluck('product.brand.id')->filter()->unique()->toArray();
        $categoryIds = $carts->pluck('product.category.id')->filter()->unique()->toArray();

        $now = now()->timestamp;
        $shippingDiscounts = \App\Models\ShippingDiscount::query()
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('status', 1)
            ->when($zone_id, fn ($q) => $q->whereIn('zone_id', [0, $zone_id]))
            ->where(fn ($q) => $q->whereNull('threshold_amount')
                ->orWhere('threshold_amount', '<=', $cartTotal)
            )
            ->get();

        // $did = ''; // Just for debug purpose
        $shippingCharge = PHP_INT_MAX;
        foreach ($shippingDiscounts as $discount) {
            if ($discount->type === 'all') {
                $shippingCharge = min($shippingCharge, (int) $discount->s_charge);
                // $did = $discount->id;
            } else {
                $details = json_decode($discount->details, true) ?? [];
                $checkFor = match ($discount->type) {
                    'product' => $productIds,
                    'brand' => $brandIds,
                    'category' => $categoryIds,
                    default => []
                };
                if (! empty($checkFor) && any_in_array($checkFor, $details)) {
                    $shippingCharge = min($shippingCharge, (int) $discount->s_charge);
                    // $did = $discount->id;
                }
            }

            // Early break if shipping charge is zero
            if ($shippingCharge === 0) {
                break;
            }
        }

        // dd($productIds, $brandIds, $categoryIds, $cartTotal, $shippingCharge);
        return $shippingCharge;
    }
}

if (! function_exists('remove_from_flashdeal')) {
    function remove_from_flashdeal(int $flashDealId, int $productId)
    {
        try {
            FlashDealProduct::query()
                ->where('flash_deal_id', $flashDealId)
                ->where('product_id', $productId)
                ->delete();
            reset_products_discount($productId);

            return true;
        } catch (\Exception $e) {
            Log::error(
                "Failed to remove product_id {$productId} from flash deal {$flashDealId}: ".$e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );

            return false;
        }
    }
}

if (! function_exists('reset_products_discount')) {
    function reset_products_discount(int|array $productId)
    {
        try {
            if (empty($productId)) {
                return;
            }
            $now = Carbon::now();
            Product::query()
                ->whereIn('id', (array) $productId)
                ->update([
                    'discount_start_date' => $now->copy()->subDays(4)->timestamp,
                    'discount_end_date' => $now->copy()->subDays(2)->timestamp,
                ]);
        } catch (\Exception $e) {
            Log::error(
                'Failed to reset discounts for product(s): '.$e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );
        }
    }
}

if (! function_exists('calculate_discount')) {
    function calculate_discount(float $price, float $discount, string $discountType): float
    {
        try {
            if ($discountType === 'percent') {
                return round(($price * $discount) / 100, 2);
            } elseif ($discountType === 'amount') {
                return round($discount, 2);
            }

            return $price;
        } catch (\Exception $e) {
            Log::error(
                'Failed to calculate discount: '.$e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );

            return $price;
        }
    }
}

if (! function_exists('getProductIds')) {
    function getProductIds(Request $request): array
    {
        if (get_setting('enable_meilisearch') != 1) {
            return [];
        }

        $keyword = trim($request->keyword ?? $request->name ?? $request->search ?? '');
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $rating = $request->rating ?? 0;
        $productIds = Cache::remember('search_product_ids_'.md5($keyword.$min_price.$max_price.$rating), 3600, function () use ($min_price, $max_price, $keyword, $rating) {
            return Product::search($keyword ?: '', function ($meilisearch, string $query, array $options) use ($min_price, $max_price, $rating) {
                $filters = [];
                if (filled($min_price) && filled($max_price)) {
                    $filters[] = 'unit_price >= '.$min_price.' AND unit_price <= '.$max_price;
                } elseif (filled($min_price)) {
                    $filters[] = 'unit_price >= '.$min_price;
                } elseif (filled($max_price)) {
                    $filters[] = 'unit_price <= '.$max_price;
                }
                if ($rating > 0 && $rating <= 5) {
                    $filters[] = 'rating >= '.$rating;
                }
                $options['filter'] = implode(' AND ', $filters);
                $maxCount = getMaxProductsCount();
                $options['limit'] = $maxCount;
                $options['hitsPerPage'] = $maxCount;

                return $meilisearch->search($query, $options);
            })->keys()->toArray();
        });

        return $productIds;
    }
}

if (! function_exists('hasGiftItem')) {
    function hasGiftItem(Order $order)
    {
        $order->loadMissing('orderDetails');

        return $order->orderDetails->where('product_type', '!=', 'regular')->count() > 0;
    }
}

if (! function_exists('validateCarts')) {
    function validateCarts(Collection $carts): Collection
    {
        if ($carts->isEmpty()) {
            return $carts;
        }

        $giftCarts = $carts->where('cart_type', '!=', 'regular')->values();
        $regularCarts = $carts->where('cart_type', 'regular')->values();

        if ($giftCarts->isEmpty()) {
            return $carts;
        }

        $giftOfferItemIds = $giftCarts->pluck('gift_offer_item_id')->unique();

        $giftOfferItems = \App\Models\GiftOfferItem::with('giftOffer.conditions')->whereIn('id', $giftOfferItemIds)->get();

        $cartTotal = $regularCarts->sum(fn ($cart) => $cart->price * $cart->quantity);

        $regularProductQty = $regularCarts
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        $isValid = true;

        foreach ($giftOfferItems as $giftOfferItem) {
            $giftOffer = $giftOfferItem->giftOffer;

            if (! $giftOffer) {
                $isValid = false;
                break;
            }

            /**
             * Cart amount condition
             */
            if ($giftOffer->offer_type === 'cart') {
                if ($giftOffer->min_cart_amount > 0 && $cartTotal < $giftOffer->min_cart_amount) {
                    $isValid = false;
                    break;
                }
            } else {
                $conditionMet = false;
                foreach ($giftOffer->conditions as $condition) {
                    if ($condition->condition_type !== 'product') {
                        continue;
                    }

                    $productQty = $regularProductQty[$condition->product_id] ?? 0;

                    if ($productQty >= $condition->min_qty) {
                        $conditionMet = true;
                        break;
                    }
                }

                if (! $conditionMet) {
                    $isValid = false;
                    break;
                }
            }
        }

        /**
         * Remove invalid gift carts
         */
        if (! $isValid) {
            $deleteIds = $giftCarts->pluck('id');
            Cart::withoutGlobalScopes()->whereIn('id', $deleteIds)->delete();
            $carts = $carts->whereNotIn('id', $deleteIds)->values();
        }

        return $carts;
    }
}

if (! function_exists('normalizePhoneNumber')) {
    function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $normalized = preg_replace('/\D/', '', $phone);

        // Remove leading +88 or 88 or + if present
        $normalized = preg_replace('/^(\+?88)?/', '', $normalized);

        return $normalized;
    }
}

if (! function_exists('getTodayAttendanceData')) {
    function getTodayAttendanceData(?User $user): array
    {
        if ($user) {
            $user->loadMissing('staff');
        }

        if (! $user || ! $user->staff) {
            return [
                'attendance' => null,
                'completed' => false,
                'checked_in' => false,
                'doing_overtime' => false,
            ];
        }

        $attendance = \App\Models\Attendance::where('staff_id', $user->staff->id)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->latest('date')
            ->first();

        if (! $attendance) {
            $attendance = \App\Models\Attendance::with([
                'overtimes' => fn ($q) => $q->latest(),
            ])
                ->firstOrCreate([
                    'staff_id' => $user->staff->id,
                    'date' => today(),
                ], ['shift' => $user->staff->shift?->value ?? \App\Enums\ShiftEnum::DAY->value]);
        } else {
            $attendance->load(['overtimes' => fn ($q) => $q->latest()]);
        }

        if (! $attendance) {
            return [
                'attendance' => null,
                'completed' => false,
                'checked_in' => false,
                'doing_overtime' => false,
            ];
        }

        $completed = $attendance->check_in && $attendance->check_out;

        $checkedIn = $attendance->check_in && ! $attendance->check_out;

        $doingOvertime = $attendance->overtimes->isNotEmpty()
            && is_null($attendance->overtimes->first()->end_time);

        return [
            'attendance' => $attendance,
            'completed' => $completed,
            'checked_in' => $checkedIn,
            'doing_overtime' => $doingOvertime,
        ];
    }
}

if (! function_exists('spellWorkTime')) {
    function spellWorkTime(string $time): string
    {
        $parts = explode(':', $time);
        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];
        $spell = '';

        if ($hours <= 0 && $minutes <= 0) {
            $spell = '0 minute';
        } else {
            if ($hours > 0) {
                $spell .= $hours.' hour'.($hours > 1 ? 's' : '');
            }
            if ($minutes > 0) {
                if ($spell) {
                    $spell .= ' ';
                }
                $spell .= $minutes.' minute'.($minutes > 1 ? 's' : '');
            }
        }

        return $spell;
    }
}

if (! function_exists('parseDate')) {
    function parseDate(Carbon|string $date, ?string $format = null): Carbon|string|null
    {
        try {
            $parsedDate = Carbon::parse($date);
            if ($format) {
                return $parsedDate->format($format);
            }

            return $parsedDate;
        } catch (\Exception $e) {
            Log::error(
                "Failed to parse date string '{$date}' with format '{$format}': ".$e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );

            return null;
        }
    }
}

if (! function_exists('normalizeMeiliSearchText')) {
    function normalizeMeiliSearchText(string $text) {
        // Normalize apostrophes
        $text = str_replace(["’", "`", "´"], "", $text);

        // Lowercase
        $text = strtolower($text);

        // Convert separators to space
        $text = preg_replace('/[-–—_\/(),.]+/', ' ', $text);

        // Keep letters + digits only
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        // Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}

if (!function_exists('update_products_stock')) {
    function update_products_stock(array $productIds) : void {
        if(get_setting('enable_rokomari_service') == 1) {
            Log::channel('merchant')->info('Updating product stock for Rokomari', ['productIds' => $productIds]);
            UpdateProductStockForRokomari::dispatch($productIds);
        }
        if(get_setting('enable_kireibd_service') == 1) {
            Log::channel('merchant')->info('Updating product stock for Kireibd', ['productIds' => $productIds]);
            KireibdJob::dispatch($productIds, 'stock');
        }
        return;
    }
}

if(!function_exists('adjust_products_stock')){
    function adjust_products_stock($productinfoarray = []): int {
        $item = (object)$productinfoarray;
        if($item){
            $product = Product::find($item->product_id);
            $productStock = ProductStock::where('id', $item->variant)->where('product_id', $item->product_id)->first();
            $product_variation = $productStock?->variant;

            $previousStock = $productStock?->qty;

            $totalPurchase = DB::table('purchase_order_item')
                            ->where('product_id', $product->id)
                            ->where('variant', $productStock->id)
                            ->sum('qty');

            empty($product_variation) ? $totalSales = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.product_id', $product->id)
            ->where(function ($query) {
                $query->where('orders.delivery_status', '<>', 'preorder')
                    ->orWhere('order_details.delivery_status', '<>', 'preorder');
            })
            ->where(function ($query) {
                $query->where('orders.delivery_status', '<>', 'cancelled')
                    ->orWhere('order_details.delivery_status', '<>', 'cancelled');
            })
            ->where(function ($query) {
                $query->where('orders.delivery_status', '<>', 'returned')
                    ->orWhere('order_details.delivery_status', '<>', 'returned');
            })
            ->sum('order_details.quantity')
            : $totalSales = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.product_id', $product->id)
            ->where('order_details.variation', $product_variation)
            ->where(function ($query) {
                $query->where('orders.delivery_status', '<>', 'preorder')
                    ->orWhere('order_details.delivery_status', '<>', 'preorder');
            })
            ->where(function ($query) {
                $query->where('orders.delivery_status', '<>', 'cancelled')
                    ->orWhere('order_details.delivery_status', '<>', 'cancelled');
            })
            ->where(function ($query) {
                $query->where('orders.delivery_status', '<>', 'returned')
                    ->orWhere('order_details.delivery_status', '<>', 'returned');
            })
            ->sum('order_details.quantity');

            $totalMinusAdjust = DB::table('stock_adjust_items')
                                ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
                                ->where('stock_adjust_items.product_id', $product->id)
                                ->where('stock_adjust_items.variant', $productStock->id)
                                ->where(function ($query) {
                                    $query->where('stock_adjust.sa_type', 'damage')
                                        ->orWhere('stock_adjust.sa_type', 'others')
                                        ->orWhere(function($q) {
                                            $q->where('stock_adjust.sa_type', 'transfer')
                                            ->where('stock_adjust_items.adjust_type', 'subtract');
                                        });
                                })
                                ->sum('stock_adjust_items.qty');

            $totalPlusAdjust = DB::table('stock_adjust_items')
                                ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
                                ->where('stock_adjust_items.product_id', $product->id)
                                ->where('stock_adjust_items.variant', $productStock->id)
                                ->where(function($query) {
                                    $query->where('stock_adjust.sa_type', 'returned')
                                        ->orWhere(function($q) {
                                            $q->where('stock_adjust.sa_type', 'transfer')
                                            ->where('stock_adjust_items.adjust_type', 'add');
                                        });
                                })
                                ->sum('stock_adjust_items.qty');

            $productStock->qty = $totalPurchase + $totalPlusAdjust - $totalSales - $totalMinusAdjust;
            $productStock->save();
            Log::info('Product stock qty: ' . $productStock->qty);

            if($previousStock > 0 && $productStock->qty <= 0) {
                update_products_stock([$product->id]);
            }elseif($previousStock <= 0 && $productStock->qty > 0) {
                update_products_stock([$product->id]);
            }

            return $productStock->qty;
        }
        return 0;
    }
}

if(!function_exists('adjust_products_stock_new')){
    // This function only using for checking
    function adjust_products_stock_new($productinfoarray = []){
        $item = (object)$productinfoarray;
        if($item){
            $product = Product::find($item->product_id);
            $productStock = ProductStock::where('id', $item->variant)
                ->where('product_id', $item->product_id)
                ->first();
            $product_variation = $productStock->variant;

            $previousStock = $productStock->qty;

            $totalPurchase = DB::table('purchase_order_item')
                ->where('product_id', $product->id)
                ->where('variant', $productStock->id)
                ->sum('qty');

            $totalSales = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->where('order_details.product_id', $product->id)
                ->when(!empty($product_variation), function ($query) use ($product_variation) {
                    $query->where('order_details.variation', $product_variation);
                })
                ->where(function ($query) {
                    $query->where('orders.delivery_status', '<>', 'preorder')
                        ->orWhere('order_details.delivery_status', '<>', 'preorder');
                })
                ->where(function ($query) {
                    $query->where('orders.delivery_status', '<>', 'cancelled')
                        ->orWhere('order_details.delivery_status', '<>', 'cancelled');
                })
                ->where(function ($query) {
                    $query->where('orders.delivery_status', '<>', 'returned')
                        ->orWhere('order_details.delivery_status', '<>', 'returned');
                })
                ->sum('order_details.quantity');

            $totalMinusAdjust = DB::table('stock_adjust_items')
                ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
                ->where('stock_adjust_items.product_id', $product->id)
                ->where('stock_adjust_items.variant', $productStock->id)
                ->where(function ($query) {
                    $query->where('stock_adjust.sa_type', 'damage')
                        ->orWhere('stock_adjust.sa_type', 'others')
                        ->orWhere(function($q) {
                            $q->where('stock_adjust.sa_type', 'transfer')
                            ->where('stock_adjust_items.adjust_type', 'subtract');
                        });
                })
                ->sum('stock_adjust_items.qty');

            $totalPlusAdjust = DB::table('stock_adjust_items')
                ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
                ->where('stock_adjust_items.product_id', $product->id)
                ->where('stock_adjust_items.variant', $productStock->id)
                ->where(function($query) {
                    $query->where('stock_adjust.sa_type', 'returned')
                        ->orWhere(function($q) {
                            $q->where('stock_adjust.sa_type', 'transfer')
                            ->where('stock_adjust_items.adjust_type', 'add');
                        });
                })
                ->sum('stock_adjust_items.qty');

            // return;
            $productStock->qty = $totalPurchase + $totalPlusAdjust - $totalSales - $totalMinusAdjust;

            dd("totalPurchase", $totalPurchase, "totalPlusAdjust", $totalPlusAdjust, "totalSales", $totalSales, "totalMinusAdjust", $totalMinusAdjust, "total_qty", $productStock->qty);
        }
    }
}

if (! function_exists('getUserIdForStaff')) {
    function getUserIdForStaff(int $staffId): ?int
    {
        $staff = Cache::remember("staff_user_id_{$staffId}", now()->addDay(), function () use ($staffId) {
            return \App\Models\Staff::find($staffId);
        });

        return $staff ? $staff->user_id : null;
    }
}
