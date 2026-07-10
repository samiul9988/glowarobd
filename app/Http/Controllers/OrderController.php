<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Events\ProductStockAffected;
use App\Exports\OrdersExport;
use App\Helpers\BarcodeHelper;
use App\Jobs\CourierSuccessRateJob;
use App\Mail\InvoiceEmailManager;
use App\Models\Address;
use App\Models\Area;
use App\Models\Barcode;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\Coupon;
use App\Models\CouponCustomerAssignment;
use App\Models\CouponUsage;
use App\Models\Currency;
use App\Models\Customeringroup;
use App\Models\FlashDealProduct;
use App\Models\GiftOfferItem;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderFeedback;
use App\Models\OrderLog;
use App\Models\PathaoArea;
use App\Models\PathaoMatchedArea as MatchedArea;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrderItem;
use App\Models\Review;
use App\Models\RewardEarnAction;
use App\Models\RewardPointLog;
use App\Models\RewardRedeemAction;
use App\Models\ShippingLog;
use App\Models\ShippingMethod;
use App\Models\SmsTemplate;
use App\Models\SoldOrderItem;
use App\Models\User;
use App\Utility\NotificationUtility;
use App\Utility\Pathao\OrderAPIUtility;
use App\Utility\SmsUtility;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use PDF;

class OrderController extends Controller
{
    public function checkProductStock($id)
    {
        $productStock = ProductStock::where('product_id', $id)
                ->first();
        if($productStock){
            $productinfoarray = [];
            $productinfoarray = [
                'product_id' => $id,
                'variant' => $productStock->id
            ];
            adjust_products_stock_new($productinfoarray);
        }
        exit;

        $yesterday = Carbon::parse('2026-03-31')->format('Y-m-d');
        $from = date('Y-m-d 00:00:00', strtotime($yesterday));
        $fromx = strtotime($from);

        $to = date('Y-m-d 23:59:59', strtotime($yesterday));
        $tox = strtotime($to);


        // $products = Product::published()->get();
        $products = ProductStock::where('id',$id)->get();

        foreach ($products as $product) {

            $product_variation = $product->variant;
            $opening_purchase = DB::table('purchase_order_item')
            ->join('purchase_order', 'purchase_order.id', '=', 'purchase_order_item.purchase_order_id')
            ->where('product_id', $product->product_id)
            ->where('variant', $product->id)
            ->where('purchase_order.purchase_date', '<', $fromx)
            ->sum('qty');
            $purchases = DB::table('purchase_order_item')
            ->join('purchase_order', 'purchase_order.id', '=', 'purchase_order_item.purchase_order_id')
            ->where('product_id', $product->product_id)
            ->where('variant', $product->id)
            ->whereBetween('purchase_order.purchase_date', [$fromx, $tox])
            ->sum('qty');
// dd($purchases);
            $opening_minus_adjustment = DB::table('stock_adjust_items')
                ->join('stock_adjust', 'stock_adjust.id', '=', 'stock_adjust_items.stock_adjust_id')
                ->where('stock_adjust_items.product_id', $product->product_id)
                ->where('stock_adjust_items.variant', $product->id)
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
                ->where('stock_adjust_items.product_id', $product->product_id)
                ->where('stock_adjust_items.variant', $product->id)
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
                ->where('stock_adjust_items.product_id', $product->product_id)
                ->where('stock_adjust_items.variant', $product->id)
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
                ->where('stock_adjust_items.product_id', $product->product_id)
                ->where('stock_adjust_items.variant', $product->id)
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
            ->where('order_details.product_id', $product->product_id)
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
            ->where('order_details.product_id', $product->product_id)
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
            ->where('order_details.product_id', $product->product_id)
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
            ->where('order_details.product_id', $product->product_id)
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
                $openStock   = ($opening_purchase + $opening_plus_adjustment)
                             - ($opening_sell + $opening_minus_adjustment);

                $adjustments = $plus_adjustments - $minus_adjustments;

                $closingStock = $openStock + $purchases - $sales + $adjustments;

                dd($closingStock);
        }

    }
    public function index(Request $request)
    {
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $orders = DB::table('orders')
            ->orderBy('id', 'desc')
            // ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('seller_id', Auth::user()->id)
            ->select('orders.id')
            ->distinct();

        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }

        $orders = $orders->paginate(15);

        foreach ($orders as $key => $value) {
            $order = \App\Models\Order::find($value->id);
            $order->viewed = 1;
            $order->save();
        }

        return view('frontend.user.seller.orders', compact('orders', 'payment_status', 'delivery_status', 'sort_search'));
    }

    // All Orders
    public function all_orders(Request $request, $status = 'pending')
    {
        $request->merge(['delivery_status' => $status]);

        // Extract and validate parameters
        $filters = $this->extractFilters($request);

        // Build base query with optimized eager loading
        $orders = Order::with(['user.customeringroup.group', 'callLogs', 'orderDetails', 'lockedBy', 'cancellation.cancelledBy:id,name', 'pendingReturnRequest', 'orderTrack:id,order_id,utm_source'])->orderBy('id', 'desc');

        // Apply filters
        $orders = $this->applyFilters($orders, $filters);

        // Paginate results
        $orders = $orders->paginate(25);

        // Get order sources efficiently
        $orderSources = $this->getOrderSources($filters['currentStatus']);

        // Get delivery status counts (consider caching this)
        $deliveryStatusCount = get_order_count_based_delivery_status();
        // $deliveryStatusCount = [];

        return view('backend.sales.all_orders.index', [
            'orders' => $orders,
            'sort_search' => $filters['search'],
            'delivery_status' => $filters['deliveryStatus'],
            'date' => $filters['date'],
            'currentStatus' => $filters['currentStatus'],
            'deliveryStatusCount' => $deliveryStatusCount,
            'order_sources' => $orderSources,
            'source' => $filters['source'],
            'call_status' => $filters['callStatus'],
        ]);
    }

    private function extractFilters(Request $request): array
    {
        return [
            'currentStatus' => $request->status ?? 'pending',
            'date' => $request->date,
            'search' => $request->filled('search') ? $request->search : null,
            'deliveryStatus' => strtolower($request->delivery_status ?? 'pending'),
            'source' => strtolower($request->source ?? ''),
            'callStatus' => $request->filled('call_status') ? $request->call_status : null,
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        // Source filter
        if ($filters['source']) {
            if ($filters['source'] == 'merchant') {
                $query->where(function ($q) {
                    $q->where('order_source', 'merchant')
                        ->orWhere('order_type', 'merchant');
                });
            } else {
                $query->where('order_source', $filters['source']);
            }
        }

        // Search filter
        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('code', 'like', '%'.$filters['search'].'%')
                    ->orWhere('shipping_address', 'like', '%'.$filters['search'].'%');
            });
        }

        // Status-based filtering
        if ($filters['currentStatus'] === 'merchant') {
            $query->where('order_type', 'merchant')
                ->where('delivery_status', 'pending');
        } else {
            if ($filters['currentStatus'] === 'pending') {
                $query->where('order_type', '!=', 'merchant');
            }

            // Apply delivery status filter
            $deliveryStatusToFilter = $filters['deliveryStatus'] ?? $filters['currentStatus'];
            if ($deliveryStatusToFilter) {
                $query->where('delivery_status', $deliveryStatusToFilter);
            }
        }

        // Date range filter
        if ($filters['date']) {
            $dates = explode(' to ', $filters['date']);
            if (count($dates) === 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($dates[1]));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // Call status filter
        if ($filters['callStatus']) {
            $query->whereHas('callLogs', function ($q) use ($filters) {
                $q->where('status', $filters['callStatus']);
            });
        }
        

        return $query;
    }

    private function getOrderSources(string $currentStatus): array
    {
        // Use caching for frequently accessed data
        $cacheKey = $currentStatus === 'merchant' ? 'merchant_order_sources' : 'all_order_sources';

        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($currentStatus) {
            $query = Order::query();

            if ($currentStatus === 'merchant') {
                $query->where('order_type', 'merchant');
            }

            return $query->pluck('order_source')->unique()->values()->toArray();
        });
    }

    // Bulk product downloads
    public function bulk_product_download_old()
    {
        $status = $_GET['status'];
        $ids = array_filter(explode(',', $_GET['ids']));
        // if(!filled($status)){
        //     abort(404);
        // }
        if (Session::has('currency_code')) {
            $currency_code = Session::get('currency_code');
        } else {
            $currency_code = Currency::findOrFail(get_setting('system_default_currency'))->code;
        }
        $language_code = Session::get('locale', config('app.locale'));

        if (Language::where('code', $language_code)->first()->rtl == 1) {
            $direction = 'rtl';
            $text_align = 'right';
            $not_text_align = 'left';
        } else {
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
        }

        if ($currency_code == 'BDT' || $language_code == 'bd') {
            // bengali font
            $font_family = "'Hind Siliguri','sans-serif'";
        } elseif ($currency_code == 'KHR' || $language_code == 'kh') {
            // khmer font
            $font_family = "'Hanuman','sans-serif'";
        } elseif ($currency_code == 'AMD') {
            // Armenia font
            $font_family = "'arnamu','sans-serif'";
        } elseif ($currency_code == 'ILS') {
            // Israeli font
            $font_family = "'Varela Round','sans-serif'";
        } elseif ($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD' || $language_code == 'ir' || $language_code == 'om' || $currency_code == 'ROM') {
            // middle east/arabic font
            $font_family = "'XBRiyaz','sans-serif'";
        } else {
            // general for all
            $font_family = "'Roboto','sans-serif'";
        }
        $pdfview = view('frontend.orders.bulk_product_head', [
            'status' => $status,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align,
        ]);
        if ($ids) {
            $orders = Order::with(['orderDetails.product', 'user'])->whereIn('id', $ids)->get();
        } else {
            $orders = Order::with(['orderDetails.product', 'user'])->where('delivery_status', $status)->get();
        }
        $products = [];
        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                $userId = $order->user_id ?? $order->guest_id;
                $userName = $order->user?->name ?? 'Guest';
                foreach ($order->orderDetails as $detail) {
                    $productId = $detail->product->id;
                    $productName = $detail->product->name;
                    $quantity = $detail->quantity;
                    if (! isset($products[$productId])) {
                        $products[$productId] = [
                            'thumbnail' => $detail->product->thumbnail_img,
                            'name' => $productName,
                            'total_quantity' => 0,
                            'users' => [], // Initialize an array to track user-specific quantities
                        ];
                    }
                    // Update total quantity
                    $products[$productId]['total_quantity'] += $quantity;
                    // Track user-specific quantities
                    if (! isset($products[$productId]['users'][$userId])) {
                        $products[$productId]['users'][$userId] = [
                            'name' => $userName,
                            'quantity' => 0,
                        ];
                    }
                    $products[$productId]['users'][$userId]['quantity'] += $quantity;
                }
            }
        }

        $pdfview .= view('frontend.orders.bulk_product_list', [
            'status' => $status,
            'products' => $products,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align,
        ]);
        $pdfview .= '</body>
        </html>';

        return PDF::loadHTML($pdfview)->stream('all-'.$status.'-order-products.pdf');
    }

    public function bulk_product_download(Request $request) {
        try {
            // -----------------------------
            // Request handling (safe)
            // -----------------------------
            $status = $request->input('status');

            $ids = $request->filled('ids')
                ? array_filter(explode(',', $request->input('ids')))
                : [];

            if (empty($ids) && !$status) {
                abort(404);
            }

            // -----------------------------
            // Currency (safe)
            // -----------------------------
            $currency_code = session('currency_code');

            if (!$currency_code) {
                $currency = \App\Models\Currency::find(get_setting('system_default_currency'));
                $currency_code = $currency?->code ?? 'BDT';
            }

            // -----------------------------
            // Language (safe)
            // -----------------------------
            $language_code = session('locale', config('app.locale'));

            $lang = \App\Models\Language::where('code', $language_code)->first();
            $rtl = $lang?->rtl ?? 0;

            $direction = $rtl ? 'rtl' : 'ltr';
            $text_align = $rtl ? 'right' : 'left';
            $not_text_align = $rtl ? 'left' : 'right';

            // -----------------------------
            // Font selection
            // -----------------------------
            $font_family = match (true) {
                $currency_code === 'BDT' || $language_code === 'bd' => "'Hind Siliguri','sans-serif'",
                $currency_code === 'KHR' || $language_code === 'kh' => "'Hanuman','sans-serif'",
                $currency_code === 'AMD' => "'arnamu','sans-serif'",
                $currency_code === 'ILS' => "'Varela Round','sans-serif'",
                in_array($currency_code, ['AED', 'EGP', 'IQD', 'ROM']) || in_array($language_code, ['sa', 'ir', 'om'])
                    => "'XBRiyaz','sans-serif'",
                default => "'Roboto','sans-serif'",
            };

            // -----------------------------
            // Query orders (safe)
            // -----------------------------
            $orders = \App\Models\Order::with([
                'orderDetails.product',
                'user'
            ])
            ->when($status, function ($query) use ($status) {
                $query->where('delivery_status', $status);
            })
            ->when(count($ids) > 0, function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            })
            ->limit(25)
            ->get();

            // -----------------------------
            // Process data safely
            // -----------------------------
            $products = [];

            foreach ($orders as $order) {
                $userId = $order->user_id ?? $order->guest_id ?? 'guest';
                $userName = $order->user?->name ?? 'Guest';

                foreach ($order->orderDetails as $detail) {

                    // 🔴 critical null guard
                    if (!$detail->product) {
                        continue;
                    }

                    $product = $detail->product;

                    $productId = $product->id;

                    if (!isset($products[$productId])) {
                        $products[$productId] = [
                            'thumbnail' => $product->thumbnail_img,
                            'name' => $product->name,
                            'total_quantity' => 0,
                            'users' => [],
                        ];
                    }

                    $quantity = (int) $detail->quantity;

                    $products[$productId]['total_quantity'] += $quantity;

                    if (!isset($products[$productId]['users'][$userId])) {
                        $products[$productId]['users'][$userId] = [
                            'name' => $userName,
                            'quantity' => 0,
                        ];
                    }

                    $products[$productId]['users'][$userId]['quantity'] += $quantity;
                }
            }

            // -----------------------------
            // Render views (safe)
            // -----------------------------
            $header = view('frontend.orders.bulk_product_head', [
                'status' => $status,
                'font_family' => $font_family,
                'direction' => $direction,
                'text_align' => $text_align,
                'not_text_align' => $not_text_align,
            ])->render();

            $body = view('frontend.orders.bulk_product_list', [
                'status' => $status,
                'products' => $products,
                'font_family' => $font_family,
                'direction' => $direction,
                'text_align' => $text_align,
                'not_text_align' => $not_text_align,
            ])->render();

            $html = $header . $body . '</body></html>';

            // -----------------------------
            // Generate PDF (safe)
            // -----------------------------
            // Clean buffer (important for FrankenPHP)
            while (ob_get_level()) {
                ob_end_clean();
            }

            ini_set('zlib.output_compression', 'Off');

            // Storage::disk('local')->put('debug.html', $html);

            return \PDF::loadHTML($html)->stream('orders-products-' . now()->timestamp . '.pdf');

        } catch (\Throwable $e) {
            \Log::error('Bulk Product PDF Failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all(),
            ]);

            abort(500);
        }
    }

    // Export Orders
    public function export(Request $request)
    {
        try {
            // dd($request->all());
            $currentStatus = @$request->status;
            if ($currentStatus == null) {
                $currentStatus = 'pending';
            }

            $date = $request->date;
            $sort_search = null;
            $source = $request->source ?? null;
            $filename = '';

            $orders = Order::with('orderDetails.product', 'orderDetails.shippingMethod', 'user.customeringroup.group')->orderBy('id', 'desc');
            if (filled($source)) {
                $orders = $orders->where('order_source', $source);
                $filename .= $source.'-';
            }

            if ($request->has('search') && filled($request->search)) {
                $sort_search = $request->search;
                // $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
                $orders = $orders->where(function ($query) use ($sort_search) {
                    $query->orWhere('code', 'like', '%'.$sort_search.'%')
                        ->orWhere('shipping_address', 'like', '%'.$sort_search.'%');
                });
            }

            if ($currentStatus === 'merchant') {
                $type = 'Merchant';
                $orders = $orders->where('order_type', 'merchant');
            } else {
                $type = 'Customer';
                $orders = $orders->where('order_type', '!=', 'merchant');
                if ($request->delivery_status != null) {
                    $orders = $orders->where('delivery_status', $request->delivery_status);
                    $delivery_status = $request->delivery_status;
                }
                if ($currentStatus != null) {
                    $orders = $orders->where('delivery_status', $currentStatus);
                }
            }

            if ($date != null) {
                $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(' to ', $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(' to ', $date)[1])));
            }

            $orders = $orders->get();

            // dd($orders);
            // ? Direct return for small data
            $filename .= 'orders-'.time().'.xlsx';

            return Excel::download(new OrdersExport($orders, $type), $filename);

            // ? Process in the queue For Large Data
            // ExportOrdersJob::dispatch($orders);
            // return redirect()->back()->with('success', 'Your export is being processed!');
        } catch (\Exception $e) {
            // dd($e->getMessage());

            return redirect()->back()->with('error', 'Server Error!');
        }
    }

    public function extendLock(Order $order)
    {
        if ($order->extendLock()) {
            return response()->json([
                'success' => true,
                'message' => ('Order lock duration extended successfully.'),
                'unlock_in' => $order->unlockIn(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => ('Failed to extend order lock duration.'),
        ]);
    }

    public function unlock(Order $order)
    {
        if ($order->unlock()) {
            return response()->json([
                'success' => true,
                'message' => ('Order unlocked successfully.'),
                'redirect_url' => route('all_orders.status', $order->delivery_status),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => ('Failed to unlock order.'),
        ]);
    }

    public function getNotes(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $notes = $order->notes ?? [];

        return view('backend.sales.all_orders.order_notes', compact('notes'));
    }

    public function addNote(Request $request)
    {
        $order = Order::findOrFail($request->id);
        $notes = $order->notes ?? [];
        $notes[] = [
            'message' => strip_tags($request->note),
            'created_by' => auth()->user()->id,
        ];
        $order->notes = $notes;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => ('Note added successfully.'),
        ]);
    }

    public function deleteNote(Request $request)
    {
        $order = Order::findOrFail($request->id);
        $notes = $order->notes ?? [];
        unset($notes[$request->index]);
        $order->notes = array_values($notes);
        $order->save();

        return response()->json([
            'success' => true,
            'message' => ('Note deleted successfully.'),
        ]);
    }

    public function all_orders_show($id)
    {
        $order = Order::with('orderDetails.product', 'payments', 'returnRequest')->findOrFail(decrypt($id));

        $returnRequest = $order->returnRequest;

        $pendingReturnRequest = ($returnRequest && $returnRequest->status !== 'approved') ? $returnRequest : null;

        $isPartialDelivered = ($returnRequest && $returnRequest->status === 'approved' && $order->delivery_status === 'delivered');

        unlock_all_orders_except($order);
        if (! $pendingReturnRequest) {
            if ($order->delivery_status != 'packaging' && $order->isLocked() && $order->lockedBy && $order->lockedBy->id != auth()->user()->id) {
                abort(403, 'This order is locked by '.$order->lockedBy->name.' and unlock in '.number_format($order->unlockIn() / 60, 2).' '.Str::plural('minute', number_format($order->unlockIn() / 60, 2)));
            }
            if (in_array(strtolower($order->delivery_status), ['processing'])) {
                $order->lock(auth()->user());
            }
        }

        $order_id = decrypt($id);

        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', @$order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        // dd($pendingReturnRequest, $isPartialDelivered);
        logOrder($order, 'viewed');

        return view('backend.sales.all_orders.show', compact('order', 'delivery_boys', 'pendingReturnRequest', 'isPartialDelivered'));
    }

    public function getOrdersCallLogs($id)
    {
        $order = Order::with('callLogs.user')->findOrFail($id);

        $callLogs = $order->callLogs->map(function ($log) use ($order) {
            return [
                'id' => $log->id,
                'hasCreator' => $log->user ? true : false,
                'creator' => $log->user?->name ?? 'N/A',
                'created_at' => $log->created_at->format('d-m-Y h:i A'),
                'duration' => $log->duration,
                'note' => ucfirst(trim($log->note) ?? 'N/A'),
                'status' => $log->status ?? 'N/A',
                'rescheduled_at' => $log->rescheduled_at ? $log->rescheduled_at->format('d-m-Y h:i A') : null,
                'deleteable' => $order->delivery_status == 'processing' && $log->user != null && $log->user->id == auth()->user()->id,
            ];
        });

        return response()->json([
            'success' => true,
            'view' => view('backend.components.orders-call-logs', compact('callLogs'))->render(),
        ]);
    }

    public function getOrderLogs($id)
    {
        $logs = OrderLog::latest()
            ->with([
                'managedBy:id,name',
                'order',
            ])
            ->where('order_id', $id)
            ->where('action', '!=', 'viewed')
            ->get();

        $logs = $logs->map(function ($log) {
            return [
                'message' => str_replace($log->order->code.' ', '', $log->message),
                'created_at' => $log->created_at->format('d-m-Y h:i A'),
                'hasManager' => ($log->managedBy || $log->managed_by == 0) ? true : false,
                'manager' => $log->managed_by == 0 ? 'Pathao' : $log->managedBy->name ?? 'N/A',
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'view' => view('backend.components.order-log-list', compact('logs'))->render(),
        ]);
    }

    public function getCustomerSuccessRate($id)
    {
        $order = Order::findOrFail($id);

        $shippingInfo = json_decode($order->shipping_address, true) ?? [];
        $phone = data_get($shippingInfo, 'phone', '');
        $successRatio = get_customer_success_rate($order->user_id, $phone);

        return response()->json([
            'success' => true,
            'view' => view('backend.components.customer-success-rate', compact('successRatio'))->render(),
        ]);
    }

    public function getRecentOrders($id)
    {
        $order = Order::findOrFail($id);
        $shippingInfo = json_decode($order->shipping_address, true) ?? [];
        $phone = data_get($shippingInfo, 'phone', '');
        $user_id = $order->user_id ?? null;

        $recentOrders = collect();
        if (! is_null($user_id) || filled($phone)) {
            $recentOrders = Order::with('orderDetails.product', 'user', 'payments')
                ->when($user_id, function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })
                ->when($phone, function ($query) use ($phone) {
                    $query->whereJsonContains('shipping_address', ['phone' => $phone]);
                })
                ->where('id', '!=', $order->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        return response()->json([
            'success' => true,
            'view' => view('backend.components.recent-orders-list', compact('recentOrders', 'order'))->render(),
        ]);
    }

    public function removePaidAmount($id)
    {
        $order = Order::findOrFail($id);
        $order->payments()->delete();

        return response()->json([
            'success' => true,
            'message' => ('Paid amount removed successfully.'),
        ]);
    }

    public function all_orders_package($id)
    {
        $order = Order::with('orderDetails.soldItems', 'callLogs.user', 'payments')->findOrFail($id);
        // dd($order);
        if ($order->delivery_status != 'packaging') {
            return redirect()->route('all_orders.status', 'packaging');
        }

        unlock_all_orders_except($order);
        if ($order->isLocked() && $order->lockedBy && $order->lockedBy->id != auth()->user()->id) {
            abort(403, 'This order is locked by '.$order->lockedBy->name.' and unlock in '.number_format($order->unlockIn() / 60, 2).' '.Str::plural('minute', number_format($order->unlockIn() / 60, 2)));
        }
        if (in_array(strtolower($order->delivery_status), ['packaging'])) {
            $order->lock(auth()->user());
        }

        foreach ($order->orderDetails as $detail) {
            foreach ($detail->soldItems ?? [] as $soldItem) {
                $soldItem->delete();
            }
        }

        return view('backend.sales.all_orders.packaging', compact('order'));
    }

    public function checkExpireDate(Request $request)
    {
        $barcode = Barcode::where('code', $request->code)->first();

        if (! $barcode) {
            return $this->jsonResponse(false, 'not_found', ('Barcode not found.'));
        }

        $decodedBarcode = BarcodeHelper::decode($barcode->value);

        if (! is_array($decodedBarcode)) {
            return $this->jsonResponse(false, 'invalid', ('Invalid barcode.'));
        }

        $productId = $decodedBarcode['product_id'] ?? null;
        $variant = $decodedBarcode['variant'] ?? null;
        $expireDate = Carbon::parse($decodedBarcode['expire_date'] ?? null);

        if (! $expireDate || ! $expireDate->isValid()) {
            return $this->jsonResponse(false, 'invalid', ('Invalid expiry date in barcode.'));
        }

        $order = Order::with(['orderDetails:id,order_id,product_id,barcode,quantity', 'orderDetails.product:id'])
            ->find($request->order_id);

        if (! $order) {
            return $this->jsonResponse(false, 'order_not_found', ('Order not found.'));
        }

        $productIds = $order->orderDetails->pluck('product_id')->toArray();

        if (! in_array($productId, $productIds)) {
            return $this->jsonResponse(false, 'not_in_order', ('This product is not part of the order.'));
        }

        $orderItem = $order->orderDetails->where('product_id', $productId)->first();

        // Get total qty already packed for this order line
        $alreadyPacked = SoldOrderItem::where('order_detail_id', $orderItem->id)
            ->where('status', 0)
            ->pluck('qty', 'barcode')
            ->toArray();

        $qtyAlreadyPacked = collect($alreadyPacked)->sum();

        // dd($alreadyPacked, $qtyAlreadyPacked, $orderItem->quantity);

        if ($qtyAlreadyPacked >= $orderItem->quantity) {
            return $this->jsonResponse(false, 'already_fulfilled', ('This order item is already fully packaged.'), [
                'product_id' => $productId,
            ]);
        }

        // ==================================== //
        // Checking for older batches that expire sooner
        $olderBatch = PurchaseOrderItem::where('product_id', $productId)
            ->where('variant', $variant)
            ->where('left_qty', '>', 0)
            ->where('expire_date', '<', $expireDate->format('Y-m-d'))
            ->orderBy('expire_date')
            ->first();

        // If an older batch exists, check its left quantity
        if ($olderBatch) {
            $olderBatchLeftQty = $olderBatch->left_qty;
            if (isset($alreadyPacked[$olderBatch->barcode])) {
                $olderBatchLeftQty -= $alreadyPacked[$olderBatch->barcode];
            }
            if ($olderBatchLeftQty > 0) {
                return $this->jsonResponse(false, 'expiring_soon',
                    "There are older batches expiring sooner (e.g. {$olderBatch->expire_date} with {$olderBatch->left_qty} left). Consider using those first.",
                    [
                        'product_id' => $productId,
                        'order_item_id' => $orderItem->id,
                    ]
                );
            }
        }

        // If not any older batch found, check current batch
        $currentBatch = PurchaseOrderItem::where('product_id', $productId)
            ->where('variant', $variant)
            ->where('barcode', $barcode->code)
            ->where('left_qty', '>', 0)
            ->orderBy('expire_date')
            ->first();

        if ($currentBatch) {
            $currentBatchLeftQty = $currentBatch->left_qty;
            if (isset($alreadyPacked[$currentBatch->barcode])) {
                $currentBatchLeftQty -= $alreadyPacked[$currentBatch->barcode];
            }
            if ($currentBatchLeftQty > 0) {
                // Now record as sold for this scan
                DB::transaction(function () use ($orderItem, $barcode) {
                    $soldItem = SoldOrderItem::firstOrCreate(
                        [
                            'order_detail_id' => $orderItem->id,
                            'barcode' => $barcode->code,
                            'status' => 0,
                        ],
                        [
                            'qty' => 0,
                        ]
                    );
                    $soldItem->increment('qty');
                });

                return $this->jsonResponse(true, 'valid', ('This product is valid until ').$expireDate->format('Y-m-d').'.', [
                    'product_id' => $productId,
                ]);
            }
        }

        return $this->jsonResponse(false, 'not_enough_stock', ('This product is not available.'));
        // ==================================== //
    }

    protected function jsonResponse($success, $status, $message, $extra = [])
    {
        return response()->json(array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message,
        ], $extra));
    }

    public function forcelyMarkAsPackaged(Request $request)
    {
        // dd($request->all());
        $barcode = Barcode::where('code', $request->barcode)->first();

        if (! $barcode) {
            return $this->jsonResponse(false, 'not_found', ('Barcode not found.'));
        }

        $decodedBarcode = BarcodeHelper::decode($barcode->value);

        if (! is_array($decodedBarcode)) {
            return $this->jsonResponse(false, 'invalid', ('Invalid barcode.'));
        }

        $productId = $decodedBarcode['product_id'] ?? null;
        $variant = $decodedBarcode['variant'] ?? null;
        $expireDate = Carbon::parse($decodedBarcode['expire_date'] ?? null);

        if (! $expireDate || ! $expireDate->isValid()) {
            return $this->jsonResponse(false, 'invalid', ('Invalid expiry date in barcode.'));
        }

        $alreadyPacked = SoldOrderItem::where('order_detail_id', $request->order_item_id)
            ->where('status', 0)
            ->pluck('qty', 'barcode')
            ->toArray();

        $currentBatch = PurchaseOrderItem::where('product_id', $productId)
            ->where('variant', $variant)
            ->where('barcode', $barcode->code)
            ->where('left_qty', '>', 0)
            ->orderBy('expire_date')
            ->first();

        if ($currentBatch) {
            $currentBatchLeftQty = $currentBatch->left_qty;
            if (isset($alreadyPacked[$currentBatch->barcode])) {
                $currentBatchLeftQty -= $alreadyPacked[$currentBatch->barcode];
            }
            if ($currentBatchLeftQty > 0) {
                // Now record as sold for this scan
                DB::transaction(function () use ($request, $barcode) {
                    $soldItem = SoldOrderItem::firstOrCreate(
                        [
                            'order_detail_id' => $request->order_item_id,
                            'barcode' => $barcode->code,
                            'status' => 0,
                        ],
                        [
                            'qty' => 0,
                        ]
                    );
                    $soldItem->increment('qty');
                });

                return $this->jsonResponse(true, 'valid', ('This product is valid until ').$expireDate->format('Y-m-d').'.', [
                    'product_id' => $productId,
                ]);
            }
        }

        return $this->jsonResponse(false, 'not_enough_stock', ('This product is not available.'));
    }

    // Inhouse Orders
    public function admin_orders(Request $request)
    {
        $currentStatus = @$request->status;
        if ($currentStatus == null) {
            $currentStatus = 'pending';
        }

        $date = $request->date;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $orders = Order::orderBy('id', 'desc')
            ->where('seller_id', $admin_user_id);

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($currentStatus != null) {
            $orders = $orders->where('delivery_status', $currentStatus);
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(' to ', $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(' to ', $date)[1])));
        }

        $orders = $orders->paginate(15);

        $deliveryStatusCount = get_order_count_based_delivery_status('Yes');

        return view('backend.sales.inhouse_orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search', 'admin_user_id', 'date', 'deliveryStatusCount', 'currentStatus'));
    }

    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $delivery_boys = User::where('city', $order_shipping_address->city)
            ->where('user_type', 'delivery_boy')
            ->get();

        $order->viewed = 1;
        $order->save();

        return view('backend.sales.inhouse_orders.show', compact('order', 'delivery_boys'));
    }

    // Seller Orders
    public function seller_orders(Request $request)
    {
        $date = $request->date;
        $seller_id = $request->seller_id;
        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;
        $orders = Order::orderBy('code', 'desc')
            ->where('orders.seller_id', '!=', $admin_user_id);

        if ($request->payment_type != null) {
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }
        if ($date != null) {
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(' to ', $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(' to ', $date)[1])));
        }
        if ($seller_id) {
            $orders = $orders->where('seller_id', $seller_id);
        }

        $orders = $orders->paginate(15);

        return view('backend.sales.seller_orders.index', compact('orders', 'payment_status', 'delivery_status', 'sort_search', 'admin_user_id', 'seller_id', 'date'));
    }

    public function seller_orders_show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order->viewed = 1;
        $order->save();

        return view('backend.sales.seller_orders.show', compact('order'));
    }

    // Pickup point orders
    public function pickup_point_order_index(Request $request)
    {
        $date = $request->date;
        $sort_search = null;

        if (Auth::user()->user_type == 'staff' && Auth::user()->staff->pick_up_point != null) {
            $orders = DB::table('orders')
                ->orderBy('code', 'desc')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->where('order_details.pickup_point_id', Auth::user()->staff->pick_up_point->id)
                ->select('orders.id')
                ->distinct();

            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
            }
            if ($date != null) {
                $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime(explode(' to ', $date)[0])))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime(explode(' to ', $date)[1])));
            }

            $orders = $orders->paginate(15);

            return view('backend.sales.pickup_point_orders.index', compact('orders', 'sort_search', 'date'));
        } else {
            $orders = DB::table('orders')
                ->orderBy('code', 'desc')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->where('order_details.shipping_type', 'pickup_point')
                ->select('orders.id')
                ->distinct();

            if ($request->has('search')) {
                $sort_search = $request->search;
                $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
            }
            if ($date != null) {
                $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime(explode(' to ', $date)[0])))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime(explode(' to ', $date)[1])));
            }

            $orders = $orders->paginate(15);

            return view('backend.sales.pickup_point_orders.index', compact('orders', 'sort_search', 'date'));
        }
    }

    public function pickup_point_order_sales_show($id)
    {
        if (Auth::user()->user_type == 'staff') {
            $order = Order::findOrFail(decrypt($id));
            $order_shipping_address = json_decode($order->shipping_address);
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();

            return view('backend.sales.pickup_point_orders.show', compact('order', 'delivery_boys'));
        } else {
            $order = Order::findOrFail(decrypt($id));
            $order_shipping_address = json_decode($order->shipping_address);
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();

            return view('backend.sales.pickup_point_orders.show', compact('order', 'delivery_boys'));
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $guestOrder = false;
            if ($request->session()->has('temp_user_id')) {
                $guestOrder = true;
                replace_temp_user_id($request->session()->get('temp_user_id'), Auth::id());
            }
            $allCarts = Cart::withoutGlobalScopes()->where('user_id', Auth::id())->get();

            $carts = $allCarts->where('cart_type', 'regular');

            if ($carts->isEmpty()) {
                flash('Your cart is empty')->warning();

                return redirect()->route('home');
            }
            $cartCouponDiscountData = check_coupon_discount(auth()->user()->id, $carts, 'web');
            // dd($cartCouponDiscountData);
            if (! empty($cartCouponDiscountData) && ! $cartCouponDiscountData['result']) {
                flash(($cartCouponDiscountData['message']))->warning();

                return redirect()->route('checkout.shipping_info')->send();
            }

            if ($carts[0]['subscription_day'] != '') {
                // Subscription Order section
                $now = new \DateTime('now');
                $start = new \DateTime($now->format('m/d/Y'));
                $nexmonth = date('m/d/Y', strtotime('+ 30 days'));
                $end = new \DateTime($now->format($nexmonth));
                $interval = new \DateInterval('P1D');
                $period = new \DatePeriod($start, $interval, $end);
                $day = explode(',', $carts[0]['subscription_day']);
                foreach ($period as $date) {
                    for ($i = 0; $i < count($day); $i++) {
                        if ($date->format('w') == intval($day[$i])) {
                            $address = Address::where('id', $carts[0]['address_id'])->first();
                            if (empty($address)) {
                                $address = Address::where('id', $request->address_id)->first();
                            }
                            $shippingAddress = [];
                            if ($address != null) {
                                $shippingAddress['name'] = $address->name ?? Auth::user()->name;
                                $shippingAddress['email'] = $address->email ?? Auth::user()->email;
                                $shippingAddress['address'] = $address->address;
                                $shippingAddress['country'] = $address->country->name;
                                $shippingAddress['state'] = $address->state->name;
                                $shippingAddress['city'] = $address->city->name;
                                $shippingAddress['area'] = $address->area->name;
                                $shippingAddress['postal_code'] = $address->postal_code;
                                $shippingAddress['phone'] = $address->phone;
                                if ($address->latitude || $address->longitude) {
                                    $shippingAddress['lat_lang'] = $address->latitude.','.$address->longitude;
                                }
                            }
                            $subscriptionday = '';

                            $combined_order = new CombinedOrder;
                            $combined_order->user_id = Auth::user()->id;
                            $combined_order->shipping_address = json_encode($shippingAddress);
                            $combined_order->save();

                            $seller_products = [];
                            foreach ($allCarts as $cartItem) {
                                $product_ids = [];
                                $product = Product::find($cartItem['product_id']);
                                if (isset($seller_products[$product->user_id])) {
                                    $product_ids = $seller_products[$product->user_id];
                                }
                                array_push($product_ids, $cartItem);
                                $seller_products[$product->user_id] = $product_ids;
                            }

                            foreach ($seller_products as $seller_product) {
                                $order = new Order;
                                $order->combined_order_id = $combined_order->id;
                                $order->user_id = Auth::id();
                                $order->shipping_address = $combined_order->shipping_address;
                                $order->address_id = isset($address) ? $address->id : intval($carts[0]['address_id']);

                                $order->payment_type = $request->payment_option;
                                $order->delivery_viewed = '0';
                                $order->payment_status_viewed = '0';
                                $order->code = config('app.order_no_prefix').date('YmdHis').rand(10, 99);
                                $order->date = strtotime('now');
                                $order->delivery_date = strtotime($date->format('Y-m-d'));
                                $order->guest_order = $guestOrder;
                                $notes = [];
                                if (isset($request->note)) {
                                    $notes[] = [
                                        'message' => strip_tags($request->note),
                                        'created_by' => auth()->user()->id,
                                    ];
                                    $order->notes = $notes;
                                }
                                // Pre-order
                                $order->order_type = 'regular';
                                if (has_preorder_product_to_cart($carts)) {
                                    $order->order_type = 'preorder';
                                    $order->delivery_status = 'preorder';
                                }
                                $order->save();

                                $subtotal = 0;
                                $giftSubtotal = 0;
                                $tax = 0;
                                $shipping = 0;
                                $coupon_discount = 0;

                                // Order Details Storing
                                foreach ($seller_product as $cartItem) {
                                    $product = Product::with('category', 'brand', 'stocks', 'productprices', 'flash_deal_product.flash_deals')->find($cartItem['product_id']);
                                    $giftOfferItem = GiftOfferItem::with('giftOffer')->find($cartItem['gift_offer_item_id']);

                                    $product_variation = $cartItem['variation'];
                                    $product_stock = $product->stocks->where('variant', $product_variation)->first();

                                    $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                                    if ($lastPurchaseItem) {
                                        $lastPurchasePrice = $lastPurchaseItem->price;
                                    } else {
                                        $lastPurchasePrice = 0;
                                    }

                                    $isRegularCart = data_get($cartItem, 'cart_type') === 'regular';
                                    $isDigital = $product->digital == 1;
                                    $requestedQty = $cartItem['quantity'];
                                    $availableQty = $product_stock->qty;
                                    $isOutOfStock = ! $isDigital && $requestedQty > $availableQty;

                                    $flash_deal_check = check_flash_deal_product($product);
                                    if ($isRegularCart) {
                                        $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $requestedQty, $this->currentlyAuthenticatedUser);
                                        $subtotal += $cartItem['price'] * $requestedQty;
                                        $coupon_discount += $cartItem['discount'];
                                    } else {
                                        $giftSubtotal += $cartItem['price'] * $requestedQty;
                                    }
                                    $tax += $cartItem['tax'] * $requestedQty;

                                    if (! check_preorder_product($product)) {
                                        if ($isRegularCart && $isOutOfStock && $product->allow_stock_out_purchases == 0) {
                                            flash(('The requested quantity is not available for ').$product->name)->warning();
                                            $order->delete();

                                            return redirect()->route('cart')->send();
                                        } elseif (! $isDigital) {
                                            if (! $isRegularCart && $giftOfferItem) {
                                                $isGiftOutOfStock = $requestedQty > $availableQty && $requestedQty > $giftOfferItem->available_qty;
                                                if ($isGiftOutOfStock) {
                                                    $giftOfferItem->available_qty = $availableQty;
                                                    $giftOfferItem->save();
                                                    flash('The requested quantity is not available for gift offer item '.$product->name)->warning();
                                                    $order->delete();

                                                    return redirect()->route('cart')->send();
                                                }
                                                $giftOfferItem->available_qty = max(0, $giftOfferItem->available_qty - $requestedQty);
                                                $giftOfferItem->used_qty += $requestedQty;
                                                $giftOfferItem->save();
                                            }

                                            $product_stock->qty -= $requestedQty;
                                            $product_stock->save();

                                            // Store Stock Transaction
                                            $isAddition = false;
                                            $transaction = [
                                                'product_id' => (int) $product->id,
                                                'variant' => empty($product_stock->variant) ? null : $product_stock->variant,
                                                'sku' => $product_stock->sku ?? null,
                                                'qty' => abs($requestedQty),
                                                'isAddition' => ($isAddition) ? 1 : 0,
                                                'isSubtraction' => ($isAddition) ? 0 : 1,
                                                'purpose' => 'sales',
                                                'purpose_id' => $order->id ?? 0,
                                                'note' => 'New Sales, Ref. Code = '.$order->code ?? 'Unknown'.'',
                                            ];
                                            // Trigger The Event
                                            event(new ProductStockAffected($transaction));
                                            if ($flash_deal_check && $isRegularCart) {
                                                $flash_deal_product = FlashDealProduct::find($product->flash_deal_product->id);
                                                $flash_deal_product->quantity -= $requestedQty;
                                                $flash_deal_product->save();
                                            }
                                        }
                                    } else {
                                        if ($isRegularCart && ! $isDigital && $requestedQty > ($product->preorder_max_qty - preorder_product_count($product))) {
                                            flash('The requested pre-order quantity is not available for '.$product->name)->warning();
                                            $order->delete();

                                            return redirect()->route('cart')->send();
                                        } elseif ($isRegularCart && ! $isDigital) {
                                            $flashDealProduct = $product->flash_deal_product ?? null;
                                            if ($flash_deal_check && $flashDealProduct) {
                                                $flashDealProductQuantity = $flashDealProduct->quantity ?? 0;
                                                if ($flashDealProductQuantity > 0) {
                                                    $flashDealProductQuantity -= $requestedQty;
                                                    $flashDealProduct->quantity = max(0, $flashDealProductQuantity);
                                                    $flashDealProduct->save();
                                                } else {
                                                    remove_from_flashdeal($flashDealProduct->flash_deal_id, $product->id);
                                                }
                                            }
                                        }
                                    }

                                    $order_detail = new OrderDetail;
                                    $order_detail->order_id = $order->id;
                                    $order_detail->seller_id = $product->user_id;
                                    $order_detail->product_id = $product->id;
                                    $order_detail->gift_offer_id = $giftOfferItem ? $giftOfferItem->gift_offer_id : null;
                                    $order_detail->gift_offer_item_id = $giftOfferItem ? $giftOfferItem->id : null;
                                    $order_detail->product_type = $giftOfferItem ? 'gift' : 'regular';
                                    $order_detail->variation = empty($product_variation) ? null : $product_variation;
                                    $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                                    $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                                    $order_detail->shipping_type = $cartItem['shipping_type'];
                                    $order_detail->shipping_method = $cartItem['shipping_method'];
                                    $order_detail->coupon_code = $cartItem['coupon_code'];
                                    $order_detail->coupon_applied = $cartItem['coupon_applied'];
                                    $order_detail->product_referral_code = $cartItem['product_referral_code'];
                                    $order_detail->shipping_cost = $cartItem['shipping_cost'] ?? 0;
                                    $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                                    // Pre-order
                                    $order_detail->delivery_status = 'pending';
                                    if ($isRegularCart && has_preorder_product_to_cart($carts)) {
                                        $order_detail->delivery_status = 'preorder';
                                    }

                                    $shipping += $order_detail->shipping_cost;

                                    if ($cartItem['shipping_type'] == 'pickup_point') {
                                        $order_detail->pickup_point_id = $cartItem['pickup_point'];
                                    }
                                    // End of storing shipping cost

                                    $order_detail->quantity = $cartItem['quantity'];
                                    $order_detail->save();
                                    $subscriptionday = $cartItem['subscription_day'];
                                    $product->num_of_sale += $cartItem['quantity'];
                                    $product->save();

                                    $order->seller_id = $product->user_id;

                                    if ($product->added_by == 'seller' && $product->user->seller != null) {
                                        $seller = $product->user->seller;
                                        $seller->num_of_sale += $cartItem['quantity'];
                                        $seller->save();
                                    }

                                    if (addon_is_activated('affiliate_system')) {
                                        if ($order_detail->product_referral_code) {
                                            $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                                            $affiliateController = new AffiliateController;
                                            $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                                        }
                                    }
                                }

                                $order->gift_offer_total = $giftSubtotal;
                                $order->grand_total = $subtotal + $tax + $shipping;

                                if ($seller_product[0]->coupon_code != null) {
                                    // if (Session::has('club_point')) {
                                    //     $order->club_point = Session::get('club_point');
                                    // }
                                    $coupon = Coupon::where('status', 1)->where('code', $seller_product[0]->coupon_code)->first();

                                    if ($coupon) {
                                        $order->coupon_discount = $coupon_discount;
                                        $order->grand_total -= $coupon_discount;
                                        $order->save();

                                        $coupon_usage = new CouponUsage;
                                        $coupon_usage->user_id = Auth::id();
                                        $coupon_usage->coupon_id = $coupon->id;
                                        $coupon_usage->order_id = $order->id;
                                        $coupon_usage->save();

                                        $couponAssignedToCustomer = CouponCustomerAssignment::where('customer_id', Auth::id())->where('coupon_id', $coupon->id)->first();
                                        if ($couponAssignedToCustomer) {
                                            $couponAssignedToCustomer->is_used = 1;
                                            $couponAssignedToCustomer->save();

                                            // Update Coupon Usage Table
                                            $coupon_usage->ref_id = $couponAssignedToCustomer->assigned_by;
                                            $coupon_usage->save();
                                        }
                                    }
                                }

                                if ($request->session()->has('reward_point_discount')) {
                                    $currentDateTime = new DateTime;
                                    $timestamp = auth()->user()->reward_point_expires_at;

                                    if ((Auth::user()->point_balance >= $request->session()->get('reward_point_discount')) && ($currentDateTime <= new DateTime($timestamp))) {
                                        $order->grand_total -= $request->session()->get('reward_point_discount');
                                        $order->reward_point_applied = 1;
                                        $order->reward_point_discount = $request->session()->get('reward_point_discount');
                                        $order->applied_reward_point = $request->session()->get('applied_reward_point');

                                        $redeemaction = RewardRedeemAction::where('activity_type', 'checkout')->first();
                                        if ($redeemaction) {
                                            $user = User::find(Auth::user()->id);
                                            if ($user) {
                                                $point = $request->session()->get('applied_reward_point');
                                                $user->point_balance = $user->point_balance - $point;
                                                if ($user->save()) {
                                                    $rewardlog = new RewardPointLog;
                                                    $rewardlog->user_id = $user->id;
                                                    $rewardlog->activity_id = $redeemaction->id;
                                                    $rewardlog->activity_type = 'Redeemed';
                                                    $rewardlog->activity = 'OrderPlaced';
                                                    $rewardlog->earned = 0;
                                                    $rewardlog->spent = $point;
                                                    $rewardlog->activity_str = 'Spent '.$point.' Reward Points for the order '.$order->code.'';
                                                    $rewardlog->purpose_id = $order->id;
                                                    $rewardlog->purpose_str = $order->code;
                                                    $rewardlog->save();
                                                }
                                            }
                                        }
                                    }
                                }

                                $combined_order->grand_total += $order->grand_total + $order->gift_offer_total;
                                $order->order_source = 'WEBSITE';
                                $order->save();
                                // dd($subscriptionday);
                            }
                            $combined_order->save();
                            $request->session()->put('combined_order_id', $combined_order->id);
                        }
                    }

                    // $request->session()->put('combined_order_id', $combined_order->id);
                }
            } else {
                // Subscription Order section else
                $address = Address::where('id', $carts[0]['address_id'])->first();
                if (empty($address)) {
                    $address = Address::where('id', $request->address_id)->first();
                }
                $shippingAddress = [];
                if ($address != null) {
                    $shippingAddress['name'] = Auth::user()->name;
                    $shippingAddress['email'] = Auth::user()->email;
                    $shippingAddress['address'] = $address->address;
                    $shippingAddress['country'] = $address->country?->name ?? null;
                    $shippingAddress['state'] = $address->state?->name ?? '';
                    $shippingAddress['city'] = $address->city?->name ?? '';
                    $shippingAddress['area'] = $address->area?->name ?? '';
                    $shippingAddress['postal_code'] = $address->postal_code ?? '';
                    $shippingAddress['phone'] = $address->phone;
                    if ($address->latitude || $address->longitude) {
                        $shippingAddress['lat_lang'] = $address->latitude.','.$address->longitude;
                    }
                }
                $subscriptionday = '';

                $combined_order = new CombinedOrder;
                $combined_order->user_id = Auth::user()->id;
                $combined_order->shipping_address = json_encode($shippingAddress);
                $combined_order->save();

                $seller_products = [];
                foreach ($allCarts as $cartItem) {
                    $product_ids = [];
                    $product = Product::find($cartItem['product_id']);
                    if (isset($seller_products[$product->user_id])) {
                        $product_ids = $seller_products[$product->user_id];
                    }
                    array_push($product_ids, $cartItem);
                    $seller_products[$product->user_id] = $product_ids;
                }

                foreach ($seller_products as $seller_product) {
                    $order = new Order;
                    $order->combined_order_id = $combined_order->id;
                    $order->user_id = Auth::user()->id;
                    $order->shipping_address = $combined_order->shipping_address;
                    $order->address_id = isset($address) ? $address->id : intval($carts[0]['address_id']);

                    $order->payment_type = $request->payment_option;
                    $order->delivery_viewed = '0';
                    $order->payment_status_viewed = '0';
                    $order->code = config('app.order_no_prefix').date('ymdHis').rand(10, 99);
                    $order->date = strtotime('now');
                    $order->guest_order = $guestOrder;
                    $notes = [];
                    if (isset($request->note)) {
                        $notes[] = [
                            'message' => strip_tags($request->note),
                            'created_by' => auth()->user()->id,
                        ];
                        $order->notes = $notes;
                    }
                    // Pre-order
                    $order->order_type = 'regular';
                    if (has_preorder_product_to_cart($carts)) {
                        $order->order_type = 'preorder';
                        $order->delivery_status = 'preorder';
                    }
                    $order->save();

                    $giftSubtotal = 0;
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $coupon_discount = 0;

                    // Order Details Storing
                    foreach ($seller_product as $cartItem) {
                        $product = Product::with('category', 'brand', 'stocks', 'productprices', 'flash_deal_product.flash_deals')->find($cartItem['product_id']);
                        $giftOfferItem = GiftOfferItem::find($cartItem['gift_offer_item_id']);
                        $product_variation = $cartItem['variation'];
                        $product_stock = $product->stocks->where('variant', $product_variation)->first();
                        // if($product_stock->qty <= 0) {
                        //     update_products_stock([$product->id]);
                        // }
                        $lastPurchaseItem = $product->getLastPurchaseOrderItemByVariant($product_variation);
                        if ($lastPurchaseItem) {
                            $lastPurchasePrice = $lastPurchaseItem->price;
                        } else {
                            $lastPurchasePrice = 0;
                        }

                        $isRegularCart = data_get($cartItem, 'cart_type') === 'regular';
                        $isDigital = $product->digital == 1;
                        $requestedQty = $cartItem['quantity'];
                        $availableQty = $product_stock->qty;
                        $isOutOfStock = ! $isDigital && $requestedQty > $availableQty;

                        $flash_deal_check = check_flash_deal_product($product);
                        if ($isRegularCart) {
                            $cartItem['price'] = getMinimumPriceByVariant($product, $product_stock, 'web', $requestedQty, $this->currentlyAuthenticatedUser);
                            $subtotal += $cartItem['price'] * $requestedQty;
                            $coupon_discount += $cartItem['discount'];
                        } else {
                            $giftSubtotal += $cartItem['price'] * $requestedQty;
                        }
                        $tax += $cartItem['tax'] * $requestedQty;

                        if (! check_preorder_product($product)) {

                            if ($isRegularCart && $isOutOfStock && $product->allow_stock_out_purchases == 0) {
                                flash(('The requested quantity is not available for ').$product->name)->warning();
                                $order->delete();

                                return redirect()->route('cart')->send();
                            } elseif (! $isDigital) {
                                if (! $isRegularCart && $giftOfferItem) {
                                    $isGiftOutOfStock = $requestedQty > $availableQty && $requestedQty > $giftOfferItem->available_qty;
                                    if ($isGiftOutOfStock) {
                                        $giftOfferItem->available_qty = $availableQty;
                                        $giftOfferItem->save();
                                        flash('The requested quantity is not available for gift offer item '.$product->name)->warning();
                                        $order->delete();

                                        return redirect()->route('cart')->send();
                                    }
                                    $giftOfferItem->available_qty = max(0, $giftOfferItem->available_qty - $requestedQty);
                                    $giftOfferItem->used_qty += $requestedQty;
                                    $giftOfferItem->save();
                                }

                                $product_stock->qty -= $requestedQty;
                                $product_stock->save();

                                $isAddition = false;
                                // Store Stock Transaction
                                $transaction = [
                                    'product_id' => (int) $product->id,
                                    'variant' => empty($product_stock->variant) ? null : $product_stock->variant,
                                    'sku' => $product_stock->sku ?? null,
                                    'qty' => abs($requestedQty),
                                    'isAddition' => ($isAddition) ? 1 : 0,
                                    'isSubtraction' => ($isAddition) ? 0 : 1,
                                    'purpose' => 'sales',
                                    'purpose_id' => $order->id ?? 0,
                                    'note' => 'New Sales, Ref. Code = '.$order->code ?? 'Unknown'.'',
                                ];
                                // Trigger The Event
                                event(new ProductStockAffected($transaction));

                                if ($flash_deal_check && $isRegularCart) {
                                    $flash_deal_product = FlashDealProduct::find($product->flash_deal_product->id);
                                    $flash_deal_product->quantity -= $requestedQty;
                                    $flash_deal_product->save();
                                }
                            }
                        } else {
                            if ($isRegularCart && ! $isDigital && $requestedQty > ($product->preorder_max_qty - preorder_product_count($product))) {
                                flash(('The requested pre-order quantity is not available for ').$product->name)->warning();
                                $order->delete();

                                return redirect()->route('cart')->send();
                            } elseif ($isRegularCart && ! $isDigital) {
                                $flashDealProduct = $product->flash_deal_product ?? null;
                                if ($flash_deal_check && $flashDealProduct) {
                                    $flashDealProductQuantity = $flashDealProduct->quantity ?? 0;
                                    if ($flashDealProductQuantity > 0) {
                                        $flashDealProductQuantity -= $requestedQty;
                                        $flashDealProduct->quantity = max(0, $flashDealProductQuantity);
                                        $flashDealProduct->save();
                                    } else {
                                        remove_from_flashdeal($flashDealProduct->flash_deal_id, $product->id);
                                    }
                                }
                            }
                        }

                        $order_detail = new OrderDetail;
                        $order_detail->order_id = $order->id;
                        $order_detail->seller_id = $product->user_id;
                        $order_detail->product_id = $product->id;
                        $order_detail->gift_offer_id = $giftOfferItem ? $giftOfferItem->gift_offer_id : null;
                        $order_detail->gift_offer_item_id = $giftOfferItem ? $giftOfferItem->id : null;
                        $order_detail->product_type = $giftOfferItem ? 'gift' : 'regular';
                        $order_detail->variation = empty($product_variation) ? null : $product_variation;
                        $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                        $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                        $order_detail->shipping_type = $cartItem['shipping_type'];
                        $order_detail->shipping_method = $cartItem['shipping_method'];
                        $order_detail->coupon_code = $cartItem['coupon_code'];
                        $order_detail->coupon_applied = $cartItem['coupon_applied'];
                        $order_detail->product_referral_code = $cartItem['product_referral_code'];
                        $order_detail->shipping_cost = $cartItem['shipping_cost'] ?? 0;
                        $order_detail->last_purchase_price = $lastPurchasePrice > 0 ? $lastPurchasePrice : $cartItem['price'];

                        // Pre-order
                        $order_detail->delivery_status = 'pending';
                        if (has_preorder_product_to_cart($carts)) {
                            $order_detail->delivery_status = 'preorder';
                        }

                        $shipping += $order_detail->shipping_cost;

                        if ($cartItem['shipping_type'] == 'pickup_point') {
                            $order_detail->pickup_point_id = $cartItem['pickup_point'];
                        }
                        // End of storing shipping cost

                        $order_detail->quantity = $cartItem['quantity'];
                        $order_detail->save();
                        $subscriptionday = $cartItem['subscription_day'];
                        $product->num_of_sale += $cartItem['quantity'];
                        $product->save();

                        $order->seller_id = $product->user_id;

                        if ($product->added_by == 'seller' && $product->user->seller != null) {
                            $seller = $product->user->seller;
                            $seller->num_of_sale += $cartItem['quantity'];
                            $seller->save();
                        }

                        if (addon_is_activated('affiliate_system')) {
                            if ($order_detail->product_referral_code) {
                                $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                                $affiliateController = new AffiliateController;
                                $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                            }
                        }
                    }

                    $order->gift_offer_total = $giftSubtotal;
                    $order->grand_total = $subtotal + $tax + $shipping;

                    if ($seller_product[0]->coupon_code != null) {
                        // if (Session::has('club_point')) {
                        //     $order->club_point = Session::get('club_point');
                        // }
                        $order->coupon_discount = $coupon_discount;
                        $order->grand_total -= $coupon_discount;

                        $coupon = Coupon::where('status', 1)->where('code', $seller_product[0]->coupon_code)->first();
                        if ($coupon) {
                            $coupon_usage = new CouponUsage;
                            $coupon_usage->user_id = Auth::user()->id;
                            $coupon_usage->coupon_id = $coupon->id;
                            $coupon_usage->order_id = $order->id;
                            $coupon_usage->save();

                            $couponAssignedToCustomer = CouponCustomerAssignment::where('customer_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first();
                            if ($couponAssignedToCustomer) {
                                $couponAssignedToCustomer->is_used = 1;
                                $couponAssignedToCustomer->save();

                                // Update Coupon Usage Table
                                $coupon_usage->ref_id = $couponAssignedToCustomer->assigned_by;
                                $coupon_usage->save();
                            }
                        }
                    }

                    if ($request->session()->has('reward_point_discount')) {
                        $order->grand_total -= $request->session()->get('reward_point_discount');
                        $order->reward_point_applied = 1;
                        $order->reward_point_discount = $request->session()->get('reward_point_discount');
                        $order->applied_reward_point = $request->session()->get('applied_reward_point');

                        $redeemaction = RewardRedeemAction::where('activity_type', 'checkout')->first();
                        if ($redeemaction) {
                            $user = User::find(Auth::user()->id);
                            if ($user) {
                                $point = $request->session()->get('applied_reward_point');
                                $user->point_balance = $user->point_balance - $point;
                                if ($user->save()) {
                                    $rewardlog = new RewardPointLog;
                                    $rewardlog->user_id = $user->id;
                                    $rewardlog->activity_id = $redeemaction->id;
                                    $rewardlog->activity_type = 'Redeemed';
                                    $rewardlog->activity = 'OrderPlaced';
                                    $rewardlog->earned = 0;
                                    $rewardlog->spent = $point;
                                    $rewardlog->activity_str = 'Spent '.$point.' Reward Points for the order '.$order->code.'';
                                    $rewardlog->purpose_id = $order->id;
                                    $rewardlog->purpose_str = $order->code;
                                    $rewardlog->save();
                                }
                            }
                        }
                    }

                    $combined_order->grand_total += ($order->grand_total + $order->gift_offer_total);
                    $order->order_source = 'WEBSITE';
                    $order->save();

                    // dd('Event will call');
                    // Adjust Ordered Product Stocks
                    logOrder($order, 'created');
                    event(new OrderPlaced($order));
                    $this->sendEmail($order);
                    if (json_decode($order->shipping_address)?->phone ?? false) {
                        CourierSuccessRateJob::dispatch(json_decode($order->shipping_address)?->phone ?? '');
                    }
                }

                $combined_order->save();

                $request->session()->put('combined_order_id', $combined_order->id);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // dd($e->getMessage(), $e->getLine(), $e->getFile());
            Log::error('Order placement failed - '.$e->getMessage().' in file '.$e->getFile().' at line '.$e->getLine());
            flash('An error occurred while placing your order. Please try again.')->error();

            return redirect()->route('checkout.shipping_info');
        }
    }

    public function sendEmail($order)
    {
        $email = null;
        if ($order->user_id && $order?->user?->email) {
            $email = $order->user->email;
        } else {
            // dd($email);
            $shipping = json_decode($order->shipping_address, true);
            $email = $shipping['email'] ?? null;
        }

        // dd($order);
        if ($email) {
            $array['view'] = 'emails.invoice';
            $array['subject'] = 'Your order has been placed - '.$order->code;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['order'] = $order;
            try {
                Mail::to($email)->queue(new InvoiceEmailManager($array));
            } catch (\Exception $e) {
                Log::error('Mail sending failed after order placed for Order ID: '.$order->id.' - '.$e->getMessage());
            }
        }
    }

    public function getOrderSummary(Request $request, $id): ?string
    {
        $order = Order::with('payments', 'orderDetails')->findOrFail($id);
        $products = $order->orderDetails->map(function ($orderDetail) {
            $price = $orderDetail->price / $orderDetail->quantity;

            return [
                'name' => $orderDetail->product->name,
                'quantity' => $orderDetail->quantity,
                'price' => $price,
                'shipping_cost' => $orderDetail->shipping_cost,
                'tax' => $orderDetail->tax,
            ];
        });

        $subtotal = $order->orderDetails->sum('price');
        $shipping_cost = $order->orderDetails->sum('shipping_cost');
        $discount = $order->coupon_discount ?? 0;
        $tax = $order->orderDetails->sum('tax');
        $paid_amount = $order->payments->sum('amount') ?? 0;

        $grand_total = $subtotal + $tax + $shipping_cost - $discount - $paid_amount;

        // return response()->json([
        //     'products' => $products,
        //     'subtotal' => $subtotal,
        //     'shipping_cost' => $shipping_cost,
        //     'discount' => $discount,
        //     'tax' => $tax,
        //     'paid_amount' => $paid_amount,
        //     'grand_total' => $grand_total,
        // ]);
        $summary = generate_order_summary($products, $subtotal, $tax, $shipping_cost, $discount, $paid_amount, $grand_total);

        return $summary;
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order != null) {
            foreach ($order->orderDetails as $key => $orderDetail) {
                try {
                    $variant = $orderDetail->variation;
                    if (empty($orderDetail->variation)) {
                        $variant = '';
                    }
                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $variant)->first();
                    if ($product_stock) {
                        if (($order->delivery_status !== 'cancelled') || ($order->delivery_status !== 'preorder') || ($order->delivery_status !== 'returned')) {
                            $product_stock->qty += $orderDetail->quantity;
                            $product_stock->save();

                            $isAddition = true;
                            // Store Stock Transaction
                            $transaction = [
                                'product_id' => (int) $orderDetail->product_id,
                                'variant' => empty($product_stock->variant) ? null : $product_stock->variant,
                                'sku' => $product_stock->sku ?? null,
                                'qty' => abs($orderDetail->quantity),
                                'isAddition' => ($isAddition) ? 1 : 0,
                                'isSubtraction' => ($isAddition) ? 0 : 1,
                                'purpose' => 'order_deleted',
                                'purpose_id' => $order->id,
                                'note' => 'Order Deleted, Ref. Code = '.$order->code ?? 'Unknown'.'',
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }
                    }

                } catch (\Exception $e) {

                }

                $orderDetail->delete();
            }

            if ($order->delivery_status == 'delivered') {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->delivered_order = $user->delivered_order - 1;
                    $user->save();
                }
            }
            $order->delete();
            logOrder($order, 'deleted');
            flash(('Order has been deleted successfully'))->success();
        } else {
            flash('Order not found')->error();
        }

        return back();
    }

    public function bulk_order_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->save();

        return view('frontend.user.seller.order_details_seller', compact('order'));
    }

    public function update_delivery_status(Request $request)
    {
        DB::beginTransaction();
        $order = Order::with('callLogs', 'pendingReturnRequest')->findOrFail($request->order_id);
        if ($order->isLocked() && $order->lockedBy && $order->lockedBy->id != auth()->user()->id) {
            return 403;
        } elseif ($order->delivery_status == 'cancelled') {
            flash(('Order status can not be changed once cancelled'))->error();

            return back();
        } elseif ($request->status == 'cancelled' && trim($request->reason) == '') {
            flash(('Please provide a cancellation reason'))->error();

            return back();
        } elseif ($order->pendingReturnRequest) {
            flash(('Order status can not be changed as there is a pending return request for this order'))->error();

            return back();
        }

        if ($order->delivery_status == 'hold' && $order->callLogs->where('status', 'shipment_failed')->count()) {
            $order->callLogs()->where('status', 'shipment_failed')->delete();
        }
        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled') {
            if ($order->payment_type == 'wallet' && $order->user_id !== null) {
                $user = User::where('id', $order->user_id)->first();
                $user->balance += get_order_grand_total($order);
                $user->save();
            }
            $reasonLabel = \App\Enums\Reasons::value(trim($request->reason));
            record_order_cancellation([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'user_type' => Auth::user()->user_type,
                'reason_type' => is_null($reasonLabel) ? 'other' : trim($request->reason),
                'reason' => is_null($reasonLabel) ? trim($request->reason) : $reasonLabel,
            ]);
        }

        $orderDetails = Auth::user()->user_type == 'seller'
        ? $order->orderDetails->where('seller_id', Auth::user()->id)
        : $order->orderDetails;

        $transactions = [];
        foreach ($orderDetails as $orderDetail) {
            $orderDetail->delivery_status = $request->status;
            $orderDetail->save();

            $variant = $orderDetail->variation ?? '';
            $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                ->where('variant', $variant)
                ->first();

            if ($product_stock != null) {
                switch ($request->status) {
                    case 'cancelled':
                        $product_stock->qty += $orderDetail->quantity;
                        $isAddition = true;
                        $purpose = 'order_cancelled';
                        $note = 'Order Cancelled, Ref. ID = '.($order->code ?? 'Unknown');
                        break;
                    case 'pending':
                        $product_stock->qty -= $orderDetail->quantity;
                        $isAddition = false;
                        $purpose = 'order_status_change';
                        $note = 'Order Cancelled to Pending, Ref. Code = '.($order->code ?? 'Unknown');
                        break;
                    case 'returned':
                        $product_stock->qty += $orderDetail->quantity;
                        $isAddition = true;
                        $purpose = 'order_returned';
                        $note = 'Order Returned, Ref. ID = '.($order->code ?? 'Unknown');
                        break;
                    default:
                        continue 2;
                }
                $product_stock->save();
                $transactions[] = [
                    'product_id' => (int) $orderDetail->product_id,
                    'variant' => empty($product_stock->variant) ? null : $product_stock->variant,
                    'sku' => $product_stock->sku ?? null,
                    'qty' => abs($orderDetail->quantity),
                    'isAddition' => ($isAddition) ? 1 : 0,
                    'isSubtraction' => ($isAddition) ? 0 : 1,
                    'purpose' => $purpose,
                    'purpose_id' => $order->id ?? 0,
                    'note' => $note,
                ];
            }

            if (addon_is_activated('affiliate_system')) {
                if (($request->status == 'delivered' || $request->status == 'cancelled') &&
                    $orderDetail->product_referral_code) {
                    $no_of_delivered = $request->status == 'delivered' ? $orderDetail->quantity : 0;
                    $no_of_canceled = $request->status == 'cancelled' ? $orderDetail->quantity : 0;
                    $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();
                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                }
            }
        }

        if ($request->status == 'confirmed') {
            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
                try {
                    SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
                } catch (\Exception $e) {

                }
            }
        }

        // Check if the request status is 'picked_up'
        if ($request->status == 'picked_up') {
            // Log::info("Products left quantity decreased for order ID: {$order->id} when status is 'picked_up' from OrderController line ". __LINE__);
            $this->updateProductsLeftQty([$order->id]); // Decrease the left quantity
        } elseif (in_array($request->status, ['cancelled', 'returned'])) {
            // Log::info("Products left quantity increased for order ID: {$order->id} when status is {$request->status} from OrderController line ". __LINE__);
            $this->updateProductsLeftQty([$order->id]); // Increase the left quantity
        }

        DB::commit();
        // sends Notifications to user
        if ($order->user_id !== null) {
            NotificationUtility::sendNotification($order, $request->status);
            if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
                $status = str_replace('_', '', $order->delivery_status);
                // $request->device_token = $order->user->device_token;
                // $request->title = "Order updated !";
                // $request->text = " Your order {$order->code} has been {$status}";
                // $request->type = "order";
                // $request->id = $order->id;
                // $request->user_id = $order->user->id;

                $request->merge([
                    'device_token' => $order->user->device_token,
                    'title' => 'Order updated !',
                    'text' => " Your order {$order->code} has been {$status}",
                    'type' => 'order',
                    'id' => $order->id,
                    'user_id' => $order->user->id,
                ]);

                NotificationUtility::sendFirebaseNotification($request);
            }
        }

        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }

        foreach ($transactions as $transaction) {
            event(new ProductStockAffected($transaction));
        }

        if (filled($request->hold_status)) {
            $order->addCallLog([
                'called_by' => auth()->user()->id,
                'status' => $request->hold_status,
                'note' => $request->hold_note,
            ]);
        }

        logOrder($order, 'delivery_status');
        $order->unlock();

        return 1;
    }

    public function bulk_order_status(Request $request)
    {
        // dd($request->all());
        if ($request->status == 'cancelled' && trim($request->reason) == '') {
            flash(('Please provide a cancellation reason'))->error();

            return back();
        }
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $order = Order::with('pendingReturnRequest')->findOrFail($order_id);
                if ($order->delivery_status == 'cancelled' || $order->delivery_status == 'returned' || $order->pendingReturnRequest) {
                    continue;
                }
                $oldStatus = $order->delivery_status;
                if ($request->status == 'delivered') {
                    $find_user_id = Order::find($order_id);
                    $user_id = $find_user_id->user_id;
                    if ($user_id != null) {
                        $find_delivered_order = User::find($user_id);
                        $delivered_order = (int) @$find_delivered_order->delivered_order;
                        $delivered_count = User::find($user_id);

                        if ($request->status == 'delivered') {
                            $delivered_count->delivered_order = $delivered_order + 1;
                        } elseif ($oldStatus == 'delivered') {
                            $delivered_count->delivered_order = $delivered_order - 1;
                        }
                        $delivered_count->update();

                        if ($request->status == 'delivered') {
                            $totalDeliveredAmount = Order::where(['user_id' => $user_id, 'delivery_status' => 'delivered'])->sum('grand_total');
                            // dd(getCustomerGroup($delivered_count->delivered_order, $totalDeliveredAmount));
                            $eligibleGroup = getCustomerGroup($delivered_count->delivered_order, $totalDeliveredAmount);
                            $currentGroup = Customeringroup::updateOrCreate(
                                ['user_id' => $user_id],
                                ['customer_groups_id' => $eligibleGroup, 'status' => 1]
                            );
                        }
                    }
                }
                $this->change_status($order, $request);
            }
        }

        return 1;
    }

    public function change_status($order, $request)
    {
        if ($request->status == 'cancelled' && trim($request->reason) == '') {
            flash(('Please provide a cancellation reason'))->error();

            return back();
        } elseif ($order->delivery_status == 'cancelled') {
            flash(('Order status can not be changed once cancelled'))->error();

            return back();
        } elseif ($order->pendingReturnRequest) {
            flash(('Order status can not be changed as there is a pending return request for this order'))->error();

            return back();
        }

        if ($request->status == 'cancelled') {
            if ($order->payment_type == 'wallet' && $order->user_id !== null) {
                $user = User::where('id', $order->user_id)->first();
                $user->balance += get_order_grand_total($order);
                $user->save();
            }
            $reasonLabel = \App\Enums\Reasons::value(trim($request->reason));
            record_order_cancellation([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'user_type' => Auth::user()->user_type,
                'reason_type' => is_null($reasonLabel) ? 'other' : trim($request->reason),
                'reason' => is_null($reasonLabel) ? trim($request->reason) : $reasonLabel,
            ]);
        }

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $order->delivery_status = $request->status;
                $orderDetail->delivery_status = $request->status;

                // Pre-order to pending stock decrease
                if ($order->order_type == 'preorder') {
                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->first();
                    if ($product_stock != null && $product_stock->qty < $orderDetail->quantity) {
                        $order->delivery_status = 'preorder';
                        $order->order_type = 'preorder';
                        $orderDetail->delivery_status = 'preorder';
                        flash(('You are trying to change status of pre-order that have one or more out of stock product(s). Please, update product stock before change status.'))->warning();
                    } else {
                        $order->order_type = 'regular';
                        $orderDetail->delivery_status = $request->status;
                    }
                }

                $transaction = null;
                if ($request->status == 'cancelled') {
                    $transaction = $this->handleStockChange($orderDetail, $order, true, 'order_cancelled', 'Order Cancelled, Ref. ID = '.$order->code ?? 'Unknown');
                }

                if ($request->status == 'pending') {
                    $transaction = $this->handleStockChange($orderDetail, $order, false, 'status_change', 'Order Status Change, Ref. Code = '.$order->code ?? 'Unknown');
                }

                if ($request->status == 'returned') {
                    $transaction = $this->handleStockChange($orderDetail, $order, true, 'order_returned', 'Order Returned, Ref. ID = '.$order->code ?? 'Unknown');
                }

                $order->save();
                $orderDetail->save();

                if ($transaction) {
                    event(new ProductStockAffected($transaction));
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $order->delivery_status = $request->status;
                $orderDetail->delivery_status = $request->status;

                // Pre-order to pending stock decrease
                if ($order->order_type == 'preorder') {
                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->first();
                    if ($product_stock != null && $product_stock->qty < $orderDetail->quantity) {
                        $order->delivery_status = 'preorder';
                        $order->order_type = 'preorder';
                        $orderDetail->delivery_status = 'preorder';
                        flash(('You are trying to change status of pre-order that have one or more out of stock product(s). Please, update product stock before change status.'))->warning();
                    } else {
                        $order->order_type = 'regular';
                        $orderDetail->delivery_status = $request->status;
                    }
                }

                $transaction = null;
                if ($request->status == 'cancelled') {
                    $transaction = $this->handleStockChange($orderDetail, $order, true, 'order_cancelled', 'Order Cancelled, Ref. ID = '.$order->code ?? 'Unknown');
                }

                if ($request->status == 'pending') {
                    $transaction = $this->handleStockChange($orderDetail, $order, false, 'status_change', 'Order Status Change, Ref. Code = '.$order->code ?? 'Unknown');
                }

                if ($request->status == 'returned') {
                    $transaction = $this->handleStockChange($orderDetail, $order, true, 'order_returned', 'Order Returned, Ref. ID = '.$order->code ?? 'Unknown');
                }

                $order->save();
                $orderDetail->save();

                if ($transaction) {
                    event(new ProductStockAffected($transaction));
                }
            }
        }

        if ($request->status == 'confirmed') {
            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
                try {
                    SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
                } catch (\Exception $e) {

                }
            }
        }

        // Check if the request status is 'picked_up'
        if ($request->status == 'picked_up') {
            // Log::info("Products left quantity decreased for order ID: {$order->id} when status is 'picked_up' from OrderController line ". __LINE__);
            $this->updateProductsLeftQty([$order->id]); // Decrease the left quantity
        } elseif (in_array($request->status, ['cancelled', 'returned'])) {
            // Log::info("Products left quantity increased for order ID: {$order->id} when status is {$request->status} from OrderController line ". __LINE__);
            $this->updateProductsLeftQty([$order->id]); // Increase the left quantity
        }

        // sends Notifications to user
        if ($order->user_id !== null) {
            NotificationUtility::sendNotification($order, $request->status);
            if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
                $request->device_token = $order->user->device_token;
                $request->title = 'Order updated !';
                $status = str_replace('_', '', $order->delivery_status);
                $request->text = " Your order {$order->code} has been {$status}";

                $request->type = 'order';
                $request->id = $order->id;
                $request->user_id = $order->user->id;

                NotificationUtility::sendFirebaseNotification($request);
            }
        }

        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }

        // Reward Point Calculate & Populate
        if (get_setting('reward_point_system') == 1) {
            if ($request->status == 'delivered' && $order->user_id !== null) {
                $user = User::where('id', $order->user_id)->first();

                $earnaction = RewardEarnAction::where('activity_type', 'orderDelivered')->first();
                if ($earnaction) {

                    $user_point_balance = $user->point_balance;
                    $point = convert_amount_to_point($earnaction, $order->grand_total);
                    $user->point_balance = $user_point_balance + $point;
                    $user->reward_point_expires_at = date('Y-m-d 23:59:59', strtotime('+'.($earnaction->validity - 1).' days'));
                    if ($user->save()) {
                        $rewardlog = new RewardPointLog;
                        $rewardlog->user_id = $order->user_id;
                        $rewardlog->activity_id = $earnaction->id;
                        $rewardlog->activity_type = 'Earned';
                        $rewardlog->activity = $earnaction->activity_type;
                        $rewardlog->earned = $point;
                        $rewardlog->spent = 0;
                        $rewardlog->activity_str = 'Earned '.$point.' Reward Points for the order '.$order->code.'';
                        $rewardlog->purpose_id = $order->id;
                        $rewardlog->purpose_str = $order->code;
                        $rewardlog->save();
                    }
                }
            }
        }
    }

    public function update_payment_status(Request $request)
    {
        $order = Order::with('payments')->findOrFail($request->order_id);

        $order->payment_status_viewed = '0';
        $order->save();

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = $request->status;
            }
        }
        $order->payment_status = $status;
        $order->save();

        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        // sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = 'Order updated !';
            $status = str_replace('_', '', $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = 'order';
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }

        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'payment_status_change')->first()->status == 1) {
            try {
                SmsUtility::payment_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {

            }
        }

        if ($request->status == 'unpaid') {
            $order->allPayments()->update(['status' => 0]);
        } else {
            $order->allPayments()->update(['status' => 1]);
        }

        logOrder($order, 'payment_status');

        return 1;
    }

    public function assign_delivery_boy(Request $request)
    {
        if (addon_is_activated('delivery_boy')) {

            $order = Order::findOrFail($request->order_id);
            if ($order->isLocked() && $order->lockedBy && $order->lockedBy->id != auth()->user()->id) {
                return 403;
            }
            $order->assign_delivery_boy = $request->delivery_boy;
            $order->delivery_history_date = date('Y-m-d H:i:s');
            $order->save();

            $delivery_history = \App\Models\DeliveryHistory::where('order_id', $order->id)
                ->where('delivery_status', $order->delivery_status)
                ->first();

            if (empty($delivery_history)) {
                $delivery_history = new \App\Models\DeliveryHistory;

                $delivery_history->order_id = $order->id;
                $delivery_history->delivery_status = $order->delivery_status;
                $delivery_history->payment_type = $order->payment_type;
            }
            $delivery_history->delivery_boy_id = $request->delivery_boy;

            $delivery_history->save();

            if (($order->delivery_boy?->email ?? null) && get_setting('delivery_boy_mail_notification') == '1') {
                $array['view'] = 'emails.invoice';
                $array['subject'] = ('You are assigned to delivery an order. Order code').' - '.$order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;

                try {
                    Mail::to($order->delivery_boy->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {

                }
            }

            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'assign_delivery_boy')->first()->status == 1) {
                try {
                    SmsUtility::assign_delivery_boy($order->delivery_boy->phone, $order->code);
                } catch (\Exception $e) {

                }
            }
        }

        return 1;
    }

    public function upcoming_delivery(Request $request)
    {
        $currentStatus = @$request->status;
        if ($currentStatus == null) {
            $currentStatus = 'pending';
        }

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;

        $orders = Order::orderBy('delivery_date', 'desc');
        $orders = $orders->where('delivery_date', '>', strtotime(date('Y-m-d')));
        if ($request->has('search')) {
            $sort_search = $request->search;
            // $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            $orders = $orders->where(function ($query) use ($sort_search) {
                $query->orWhere('code', 'like', '%'.$sort_search.'%')
                    ->orWhere('shipping_address', 'like', '%'.$sort_search.'%');
            });
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }

        if ($currentStatus != null) {
            $orders = $orders->where('delivery_status', $currentStatus);
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(' to ', $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(' to ', $date)[1])));
        }

        $orders = $orders->paginate(15);

        $deliveryStatusCount = get_order_count_based_delivery_status();

        return view('backend.sales.upcoming_delivery.index', compact('orders', 'sort_search', 'delivery_status', 'date', 'currentStatus', 'deliveryStatusCount'));
    }

    public function processToShip(Request $request)
    {

        $shipping_methods = ShippingMethod::where('status', 1)->get();

        return view('backend.sales.all_orders.process_to_ship', compact('shipping_methods'));
    }

    public function processToShipSave(Request $request)
    {
        // dd($request->all());
        $shippingMethod = ShippingMethod::find(intval($request->shipping_method));
        $orderIds = $request->orderIDs ? explode(',', $request->orderIDs) : [];

        $missedOrders = [];
        $completedOrderIds = [];
        try {
            if (isset($orderIds) && count($orderIds) > 0) {
                foreach ($orderIds as $order) {
                    try {
                        $pathaoOrder = new OrderAPIUtility;

                        $orderData = Order::with('payments')->find($order);
                        $shippingInfo = json_decode($orderData->shipping_address);
                        $systemAreaID = ($orderData->order_source == 'POS') ? $orderData->address_id : Address::find($orderData->address_id)?->area_id ?? null;

                        if (empty($systemAreaID)) {
                            $systemAreaID = Area::where('name', $shippingInfo->area)->first()?->id ?? null;
                        }

                        $createdEntry = 0;
                        if(!isset($systemAreaID)){
                            $data = [];
                            $data['orderId'] = $order;
                            $data['reason'] = 'No selected address found for Order Number: '.$orderData->code;
                            Log::channel('pathao_callback')->info('Error From Order Controller Process To Ship In Pathao - No matched pathao area found for Order Number: '.$orderData->code.' & area id: '.$systemAreaID);
                            array_push($missedOrders, $data);
                        } else {

                            if (@get_setting('automated_pathao_shipping') == 1) {
                                if (isset($shippingMethod) && $shippingMethod->id === 1) {
                                    $pathaoMatchedArea = MatchedArea::where('system_area_id', $systemAreaID)->first();

                                    if (! isset($pathaoMatchedArea)) {
                                        $data = [];
                                        $data['orderId'] = $order;
                                        $data['reason'] = 'No matched pathao area found for Order Number: '.$orderData->code.' & area id: '.$systemAreaID;
                                        Log::channel('pathao_callback')->info('Error From Order Controller Process To Ship In Pathao - No matched pathao area found for Order Number: '.$orderData->code.' & area id: '.$systemAreaID);
                                        array_push($missedOrders, $data);
                                    } else {
                                        $pathaoAreaInfo = PathaoArea::find($pathaoMatchedArea->pathao_area_id);

                                        if ($pathaoAreaInfo) {
                                            $data = [
                                                'store_id' => env('PATHAO_STORE_ID'),
                                                'merchant_order_id' => $orderData->code ?? $orderData->id,
                                                'sender_name' => env('APP_NAME'),
                                                'sender_phone' => env('PATHAO_STORE_PHONE'),
                                                'recipient_name' => $shippingInfo->name,
                                                'recipient_phone' => substr($shippingInfo->phone, -11),
                                                'recipient_address' => $shippingInfo->address,
                                                'recipient_city' => $pathaoAreaInfo->city_id,
                                                'recipient_zone' => $pathaoAreaInfo->zone_id,
                                                'recipient_area' => $pathaoAreaInfo->area_id,
                                                'delivery_type' => 48,
                                                'item_type' => 2,
                                                'special_instruction' => 'Please Call The Customer Before Deliver',
                                                'item_quantity' => count($orderData->orderDetails) ?? 1,
                                                'item_weight' => 0.5,
                                                'amount_to_collect' => intval(get_order_due_amount($orderData)),
                                                'item_description' => 'Beauty Products',
                                            ];
                                            // Log::channel('pathao_callback')->info('From Order Controller Pathao Order Data: '.json_encode($data));
                                            $res = $pathaoOrder->create($data);
                                            $res = json_decode(json_encode($res), true);
                                            if (isset($res['type']) && $res['type'] == 'error' && count(data_get($res,'errors',$res['error'] ?? [])) > 0) {
                                                $newArray = array_values(data_get($res,'errors',$res['error'] ?? []));
                                                $result = [];
                                                foreach ($newArray as $index => $error) {
                                                    array_push($result, $error[0]);
                                                }
                                                $data = [];
                                                $data['orderId'] = $order;
                                                $data['reason'] = implode(' & ', $result).'for Order Number: '.$orderData->code.' & customer: '.$shippingInfo->name;
                                                array_push($missedOrders, $data);

                                                // Create Shipping Log
                                                $log = new ShippingLog;
                                                $log->created_by = auth()->id();
                                                $log->order_id = intval($order);
                                                $log->shipping_method_id = intval($request->shipping_method);
                                                $log->createdEntry = $createdEntry;
                                                $log->error_response = json_encode($data);
                                                $log->success_response = null;
                                                $log->save();
                                            } else {
                                                $createdEntry = 1;
                                                $completedOrderIds[] = intval($order);
                                                // Create Shipping Log
                                                $log = new ShippingLog;
                                                $log->created_by = auth()->id();
                                                $log->order_id = intval($order);
                                                $log->shipping_method_id = intval($request->shipping_method);
                                                $log->createdEntry = $createdEntry;
                                                $log->error_response = null;
                                                $log->success_response = json_encode($res);
                                                $log->save();
                                            }
                                        } else {
                                            $data = [];
                                            $data['orderId'] = $order;
                                            $data['reason'] = 'No matched pathao area found for Order Number: '.$orderData->code.' & area id: '.$systemAreaID;
                                            Log::channel('pathao_callback')->info('Error From Order Controller Process To Ship In Pathao - No matched pathao area found for Order Number: '.$orderData->code.' & area id: '.$systemAreaID);
                                            array_push($missedOrders, $data);
                                        }
                                    }
                                } else {
                                    $log = new ShippingLog;
                                    $log->created_by = auth()->id();
                                    $log->order_id = intval($order);
                                    $log->shipping_method_id = intval($request->shipping_method);
                                    $log->createdEntry = $createdEntry;
                                    $log->error_response = null;
                                    $log->success_response = null;
                                    $log->save();
                                }
                            }
                        }

                        $this->change_status($orderData, $request);

                    } catch (\Throwable $th) {
                        Log::channel('pathao_callback')->info('Error From Order Controller Process To Ship In Pathao: '.$th->getMessage());
                        // Log::info($th->getMessage());
                        // throw $th;
                        continue;
                    }
                }

                if (count($missedOrders) > 0) {
                    $Ids = collect($missedOrders)->pluck('orderId')->toArray();
                    $this->hold_orders($Ids);
                    $this->assignPackaging($Ids);
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => 0,
                            'hold' => 1,
                            'message' => ('Order process failed! And moved to hold.'),
                            'orderIds' => $Ids,
                        ], 422);
                    }

                    return redirect()->route('orders.shipping.process')->with('failedOrders', $missedOrders);
                } else {
                    if (count($completedOrderIds) == 0) {
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => 0,
                                'message' => 'Failed to access shipping API. Please try again or contact support.',
                            ], 500);
                        }
                        flash('Failed to access shipping API. Please try again or contact support.')->error();

                        return redirect()->route('orders.shipping.process');
                    }
                    $this->updateProductsLeftQty($completedOrderIds);
                    $this->logOrders($completedOrderIds, 'delivery_status');
                    if ($request->ajax()) {
                        $this->assignPackaging($completedOrderIds);

                        return response()->json([
                            'success' => 1,
                            'message' => ('Order processed successfully.'),
                        ], 200);
                    }
                    flash(('All orders processed successfully.'))->success();

                    return redirect()->route('orders.shipping.process');
                }

            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => 0,
                        'hold' => 0,
                        'message' => ('No order provided!'),
                    ], 422);
                }
                flash(('No order provided!'))->error();

                return redirect()->route('orders.shipping.process');
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => 0,
                    'hold' => 0,
                    'message' => 'Something went wrong!',
                    'error' => $e->getMessage(),
                ], 500);
            }
            flash('Something went wrong!')->error();

            return redirect()->back();
        }
    }

    public function logOrders(array $orderIds, string $action)
    {
        $orders = Order::whereIn('id', $orderIds)->get();
        foreach ($orders as $order) {
            logOrder($order, $action);
        }
    }

    public function updateProductsLeftQty(array $orderIds)
    {
        // $orderIds = request()->orderIds;
        // dd($orderIds);
        try {
            $orders = Order::with(['orderDetails' => function ($query) {
                $query->select('id', 'order_id');
            }, 'orderDetails.soldItems'])
                ->whereIn('id', $orderIds)
                ->select('id', 'delivery_status as status')
                ->get();
            // dd($orders->toArray());
            DB::transaction(function () use ($orders) {
                // Collect all barcodes and quantities to update
                $updates = [];
                foreach ($orders as $order) {
                    foreach ($order->orderDetails as $item) {
                        if ($item->soldItems->isEmpty()) {
                            continue; // Skip if no sold items
                        }
                        foreach ($item->soldItems as $soldItem) {
                            // Prepare updates for purchase order items
                            $barcode = $soldItem->barcode;
                            // $updates[$barcode] = ($updates[$barcode] ?? 0) + $soldItem->qty;
                            $updates[$barcode] = [
                                'qty' => ($updates[$barcode] ?? 0) + $soldItem->qty,
                                'status' => $soldItem->status,
                                'order_status' => $order->status,
                            ];

                            if (! in_array($order->status, ['picked_up', 'hold'])) {
                                Log::info('Deleting sold items from for status '.$order->status.'.'.__LINE__);
                                $soldItem->delete();
                            }
                        }
                    }
                }

                // dd($orders->toArray(), $updates);
                // Process all purchase order item updates in bulk
                foreach ($updates as $barcode => $data) {
                    $purchaseOrderItem = PurchaseOrderItem::whereNotNull('barcode')
                        ->whereNotNull('expire_date')
                        ->where('barcode', $barcode)
                        ->first();
                    if (! $purchaseOrderItem) {
                        continue; // Skip if no purchase order item found
                    }
                    if ($data['order_status'] === 'picked_up' && ! $data['status']) {
                        $qty = $purchaseOrderItem->left_qty - $data['qty'];
                    } else {
                        if ($data['status']) {
                            $qty = $purchaseOrderItem->left_qty + $data['qty'];
                        } else {
                            $qty = $purchaseOrderItem->left_qty;
                        }
                    }
                    // $qty = $purchaseOrderItem->left_qty - $quantity;
                    $purchaseOrderItem->left_qty = max($qty, 0);
                    $purchaseOrderItem->save();
                    // dd($purchaseOrderItem);
                    SoldOrderItem::where('barcode', $barcode)->update([
                        'status' => $data['order_status'] === 'picked_up' ? 1 : 0,
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::info('Error updating products left quantity: '.$e->getMessage());
        }
    }

    public function assignPackaging($orderIds)
    {
        $orders = Order::whereIn('id', $orderIds)->get();
        try {
            DB::beginTransaction();
            foreach ($orders as $order) {
                $order->packaged_by = auth()->id();
                $order->save();

                $order->unlock();

                logOrder($order, 'packaged');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function hold_orders(array $orderIds)
    {
        $orders = Order::whereIn('id', $orderIds)->get();
        try {
            DB::beginTransaction();
            foreach ($orders as $order) {
                $order->delivery_status = 'hold';
                $order->packaged_by = auth()->id();
                $order->save();

                $order->unlock();

                logOrder($order, 'delivery_status');

                $order->addCallLog([
                    'called_by' => auth()->user()->id,
                    'status' => 'shipment_failed',
                    'note' => 'Order shipment failed',
                ]);

                // foreach ($order->orderDetails as $item) {
                //     foreach ($item->soldItems as $soldItem) {
                //         // Delete sold item
                //         $soldItem->delete();
                //     }
                // }
            }
            DB::commit();
            $this->updateProductsLeftQty($orderIds);
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function handleStockChange($orderDetail, $order, $isAddition, $purpose, $note)
    {
        $variant = $orderDetail->variation ?? '';
        $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
            ->where('variant', $variant)
            ->first();

        if ($product_stock != null) {
            $product_stock->qty += $isAddition ? $orderDetail->quantity : -$orderDetail->quantity;
            $product_stock->save();

            return [
                'product_id' => (int) $orderDetail->product_id,
                'variant' => empty($product_stock->variant) ? null : $product_stock->variant,
                'sku' => $product_stock->sku ?? null,
                'qty' => abs($orderDetail->quantity),
                'isAddition' => $isAddition ? 1 : 0,
                'isSubtraction' => $isAddition ? 0 : 1,
                'purpose' => $purpose,
                'purpose_id' => $order->id ?? 0,
                'note' => $note,
            ];
        }

        return null;
    }

    public function storeFeedback(Request $request)
    {
        // dd($request->except(['_token']));
        try {
            DB::beginTransaction();
            $order = Order::find($request->feedback_order_id);
            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }
            $user = User::find($request->user_id);

            // dd($user);
            $rating = intval($request->rating ?? 0);
            $note = $request->feedback_note;

            $except = ['_token'];

            $totalProductsRating = 0;
            $feedback_product_rating = [];
            foreach ($request->feedback_products ?? [] as $pid) {
                $pRating = intval($request->input("feedback_product_rating_$pid", 0));
                $totalProductsRating += $pRating;
                $feedback_product_rating[] = $pRating;
                $except[] = "feedback_product_rating_$pid";
            }
            $countProducts = count($request->feedback_products ?? []);
            $request->merge([
                'products_rating' => $countProducts > 0
                    ? (int) round($totalProductsRating / $countProducts)
                    : 0,
                'feedback_product_rating' => $feedback_product_rating,
            ]);

            $feedback = OrderFeedBack::updateOrCreate([
                'order_id' => $order->id,
            ], [
                'call_log_id' => Auth::id().'_'.$user->id,
                'rating' => $rating,
                'note' => $note,
                'created_by' => Auth::id(),
                'feedback' => $request->except($except),
            ]);

            $totalOrderIds = Order::where('user_id', $user->id)
                ->pluck('id');

            $totalFeedbackOrder = OrderFeedback::query()
                ->whereIn('order_id', $totalOrderIds)
                ->pluck('rating');

            $averageRating = $totalFeedbackOrder->avg();
            $user->createMeta(['satisfaction' => (round($averageRating) / 5) * 100]);

            $products = $request->feedback_products ?? [];

            // dd($products, $productRatings);
            foreach ($products as $productId) {
                $productRating = intval($request->input("feedback_product_rating_$productId", 0));
                if ($productRating <= 0) {
                    continue;
                }
                $review = Review::where('product_id', $productId)->where('user_id', $user->id)->first();
                if ($review) {
                    $review->rating = $productRating;
                } else {
                    $review = new Review;
                    $review->product_id = $productId;
                    $review->user_id = $user->id;
                    $review->rating = $productRating;
                }
                $review->review_type = 'feedback';
                $review->save();
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Feedback submitted successfully',
                'data' => $feedback,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Feedback submission failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCourierSuccessRate(Request $request)
    {
        $successRatio = get_setting('enable_courier_success_rate') == 1 ? get_courier_success_rate($request->phone) : null;

        return response()->json([
            'success' => true,
            'view' => is_null($successRatio) ? null : view('backend.components.customer-success-rate', compact('successRatio'))->render(),
        ], 200);
    }

    public function getOrderInfo(Request $request)
    {
        $orderCode = $request->code;
        $order = Order::with('orderDetails.product', 'pendingReturnRequest')
            ->where('code', $orderCode)
            // ->whereIn('delivery_status', ['picked_up', 'on_the_way', 'delivered'])
            // ->whereNotIn('delivery_status', ['returned', 'cancelled'])
            ->first();

        if ($order) {
            if (! in_array($order->delivery_status, ['picked_up', 'on_the_way', 'delivered'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is not eligible for return.',
                ], 409);
            }
            if ($order->pendingReturnRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has a pending return request.',
                ], 409);
            }

            $products = $order->orderDetails->map(function ($detail) {
                return [
                    'item_id' => $detail->id,
                    'id' => $detail->product_id,
                    'name' => $detail->product->name,
                    'quantity' => $detail->quantity,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'products' => $products,
                    'customer' => json_decode($order->shipping_address, true) ?? [],
                ],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }
    }

    public function getStatusCount()
    {
        $statusCount = get_order_count_based_delivery_status();

        return response()->json([
            'success' => true,
            'counts' => $statusCount,
        ]);
    }

    public function lossProfitReport(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if (filled($request->date)) {
            $dateRange = explode(' to ', $request->date);
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate = Carbon::parse($dateRange[1])->endOfDay();
            }
        }

        $statusList = ['picked_up', 'on_the_way', 'delivered'];

        // 1) Per-order paginated listing (grouped join) — unchanged semantics
        $orders = DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->whereNotNull('orders.delivery_fee')
            ->whereIn('orders.delivery_status', $statusList)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy(
                'orders.id',
                'orders.code',
                'orders.delivery_fee',
                'orders.coupon_discount',
                'orders.reward_point_discount',
                'orders.grand_total'
            )
            ->select(
                'orders.id',
                'orders.code',
                'orders.delivery_fee',
                'orders.coupon_discount',
                'orders.reward_point_discount',
                'orders.grand_total',
                DB::raw('SUM(order_details.last_purchase_price * order_details.quantity) as total_purchase'),
                DB::raw('SUM(order_details.price) as total_selling'),
                DB::raw('COUNT(order_details.id) as total_items')
            )
            ->orderByDesc('orders.id')->paginate(50);

        // 2) Order-level aggregates (no join) — avoids duplication
        $orderSums = DB::table('orders')
            ->whereNotNull('delivery_fee')
            ->whereIn('delivery_status', $statusList)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('COALESCE(SUM(grand_total),0) as grand_selling'),
                DB::raw('COALESCE(SUM(coupon_discount),0) as total_coupon'),
                DB::raw('COALESCE(SUM(reward_point_discount),0) as total_reward'),
                DB::raw('COALESCE(SUM(delivery_fee),0) as grand_delivery')
            )
            ->first();

        // 3) Total purchase from order_details (joined to orders to apply same order filters)
        $totalPurchase = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->whereNotNull('orders.delivery_fee')
            ->whereIn('orders.delivery_status', $statusList)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(DB::raw('COALESCE(SUM(order_details.last_purchase_price * order_details.quantity),0) as total_purchase'))
            ->value('total_purchase');

        // 4) Calculate All Expenses within the date range
        $expensesCacheKey = 'total_expenses_'.$startDate->toDateString().'_'.$endDate->toDateString();
        // Cache::forget($expensesCacheKey); // For testing purposes; remove in production
        $totalExpenses = Cache::remember($expensesCacheKey, now()->addHours(3), function () use ($startDate, $endDate) {
            return \App\Models\AccVoucherEntry::query()
                ->where('particular_type', \App\Models\AccHead::class)
                ->whereNotNull('particular_id')
                ->where('particular_id', '!=', 2) // Exclude 'Purchase' Head
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereHasMorph(
                    'particular',
                    [\App\Models\AccHead::class], // only apply conditions to AccHead
                    function ($q) {
                        $q->where('parent_head', 'expense');
                    }
                )
                ->with(['particular:id,parent_head'])
                ->sum('debit');
        });

        $summary = [
            'grand_selling' => (float) ($orderSums->grand_selling ?? 0),
            'grand_discounts' => (float) (($orderSums->total_coupon ?? 0) + ($orderSums->total_reward ?? 0)),
            'grand_delivery' => (float) ($orderSums->grand_delivery ?? 0),
            'total_purchase' => (float) $totalPurchase,
            'grand_profit' => (float) ((($orderSums->grand_selling ?? 0) - $totalPurchase) - ($orderSums->grand_delivery ?? 0)),
            'total_expenses' => (float) ($totalExpenses ?? 0),
        ];

        return view('backend.reports.orders.loss-profit-report', compact('orders', 'summary'));
    }

    public function orderLookup($code)
    {
        $ignoreStatus = ['cancelled', 'returned', 'picked_up', 'on_the_way', 'delivered'];

        $order = Order::query()
            ->where('code', trim($code))
            ->whereNotIn('delivery_status', $ignoreStatus)
            ->select('id', 'code', 'delivery_status', 'shipping_address', 'payment_status', 'grand_total', 'payment_type')
            ->first();

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found!',
                'order' => null,
            ]);
        }

        $shippingAddress = json_decode($order->shipping_address, true);

        return response()->json([
            'status' => true,
            'message' => 'Order found!',
            'order' => [
                'id' => $order->id,
                'url' => route('all_orders.show', encrypt($order->id)),
                'code' => $order->code,
                'payment_status' => ucfirst($order->payment_status),
                'grand_total' => single_price($order->grand_total),
                'payment_type' => ucfirst(str_replace('_', ' ', $order->payment_type)),
                'shipping_address' => [
                    'name' => $shippingAddress['name'] ?? 'N/A',
                    'phone' => $shippingAddress['phone'] ?? 'N/A',
                ],
            ],
        ]);
    }
}
