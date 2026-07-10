<?php

namespace App\Http\Controllers;

use App\Exports\ExpireProductsExport;
use App\Exports\NotSellingProductsExport;
use App\Exports\SalesReportExport;
use App\Exports\TopSellingProductsExport;
use App\Jobs\UpdateClosingStockJob;
use App\Models\Brand;
use App\Models\CommissionHistory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderTrack;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductsClosingStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Search;
use App\Models\Seller;
use App\Models\ShippingLog;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;

class ReportController extends Controller
{
    public function stock_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id')) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(15);
        return view('backend.reports.stock_report', compact('products', 'sort_by'));
    }

    public function in_house_sale_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('num_of_sale', 'desc')->where('added_by', 'admin');
        if ($request->has('category_id')) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(15);
        return view('backend.reports.in_house_sale_report', compact('products', 'sort_by'));
    }

    public function seller_sale_report(Request $request)
    {
        $sort_by = null;
        $sellers = Seller::orderBy('created_at', 'desc');
        if ($request->has('verification_status')) {
            $sort_by = $request->verification_status;
            $sellers = $sellers->where('verification_status', $sort_by);
        }
        $sellers = $sellers->paginate(10);
        return view('backend.reports.seller_sale_report', compact('sellers', 'sort_by'));
    }

    public function wish_report(Request $request)
    {
        $sort_by = null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id')) {
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(10);
        return view('backend.reports.wish_report', compact('products', 'sort_by'));
    }

    public function user_search_report(Request $request)
    {
        $searches = Search::orderBy('count', 'desc')->paginate(10);
        return view('backend.reports.user_search_report', compact('searches'));
    }

    public function commission_history(Request $request)
    {
        $seller_id = null;
        $date_range = null;

        if (Auth::user()->user_type == 'seller') {
            $seller_id = Auth::user()->id;
        }if ($request->seller_id) {
            $seller_id = $request->seller_id;
        }

        $commission_history = CommissionHistory::orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $commission_history = $commission_history->where('created_at', '>=', $date_range1[0]);
            $commission_history = $commission_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($seller_id) {

            $commission_history = $commission_history->where('seller_id', '=', $seller_id);
        }

        $commission_history = $commission_history->paginate(10);
        if (Auth::user()->user_type == 'seller') {
            return view('frontend.user.seller.reports.commission_history_report', compact('commission_history', 'seller_id', 'date_range'));
        }
        return view('backend.reports.commission_history_report', compact('commission_history', 'seller_id', 'date_range'));
    }

    public function wallet_transaction_history(Request $request)
    {
        $user_id = null;
        $date_range = null;

        if ($request->user_id) {
            $user_id = $request->user_id;
        }

        $users_with_wallet = User::whereIn('id', function ($query) {
            $query->select('user_id')->from(with(new Wallet)->getTable());
        })->get();

        $wallet_history = Wallet::orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $wallet_history = $wallet_history->where('created_at', '>=', $date_range1[0]);
            $wallet_history = $wallet_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($user_id) {
            $wallet_history = $wallet_history->where('user_id', '=', $user_id);
        }

        $wallets = $wallet_history->paginate(10);

        return view('backend.reports.wallet_history_report', compact('wallets', 'users_with_wallet', 'user_id', 'date_range'));
    }

    public function sales_report(Request $request)
    {
        $orders = [];
        $sum = null;
        $count = null;
        $grand = 0;
        $delivery_charge = 0;
        $grand_delivery = 0;
        if ($request->has('submit') && $request->submit == 'yes' && !empty(array_filter($request->only(['date', 'delivery_status', 'payment_method', 'payment_status', 'order_source', 'utm_source', 'seller', 'search', 'product', 'brand'])))) {
            $qtyQuery = Order::with('orderDetails.product', 'orderTrack')
                ->when($request->product, function ($query) use ($request) {
                    return $query->whereHas('orderDetails', function ($q) use ($request) {
                        $q->where('product_id', $request->product);
                    });
                })
                ->when($request->brand, function ($query) use ($request) {
                    return $query->whereHas('orderDetails.product', function ($q) use ($request) {
                        $q->where('brand_id', $request->brand);
                    });
                })
                ->when(filled($request->utm_source) && $request->utm_source !== 'all', function ($query) use ($request) {
                    return $query->whereHas('orderTrack', function ($q) use ($request) {
                        $q->where('utm_source', $request->utm_source);
                    });
                })
                ->when($request->utm_source === 'all', function ($query) {
                    return $query->whereHas('orderTrack');
                });

            $orders = $qtyQuery
                ->where('orders.delivery_status', '!=', 'cancelled')
                ->where('orders.delivery_status', '!=', 'returned')
                ->orderBy('orders.id', 'desc')
                ->groupBy('orders.id');

            if ($request->date != null && count(explode(" to ", $request->date)) == 2) {
                $orders = $orders
                    ->whereBetween('orders.created_at', [date('Y-m-d 00:00:00', strtotime(explode(" to ", $request->date)[0])), date('Y-m-d 23:59:59', strtotime(explode(" to ", $request->date)[1]))]);
            }

            if ($request->search != null) {
                $orders = $orders->where(function ($query) use ($request) {
                    $query->orWhere('orders.code', 'like', '%' . $request->search . '%')
                        ->orWhere('orders.shipping_address', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->delivery_status != null) {
                $orders = $orders->where('orders.delivery_status', '=', $request->delivery_status);
            }

            if ($request->payment_method != null) {
                $orders = $orders->where('orders.payment_type', '=', $request->payment_method);
            }

            if ($request->payment_status != null) {
                $orders = $orders->where('orders.payment_status', $request->payment_status);
            }
            if ($request->order_source != null) {
                if(strtolower($request->order_source) == 'merchant') {
                    $orders = $orders->where(function ($q) {
                        $q->where('orders.order_source', 'merchant')
                          ->orWhere('orders.order_type', 'merchant');
                    });
                } else {
                    $orders = $orders->where('orders.order_source', $request->order_source);
                }
            }

            if ($request->seller != null) {
                $orders = $orders->where('orders.seller_id', $request->seller);
            }

            // Calculate the grand total excluding shipping costs
            $allOrders = (clone $orders)->get();
            $grand = $allOrders->sum(function ($item) {
                return $item->grand_total - $item->orderDetails->sum('shipping_cost');
            });

            // Calculate the full sum of grand_total
            $grand_delivery = $allOrders->sum('grand_total');

            // Calculate Delivery Charge
            $delivery_charge = $grand_delivery - $grand;

            // Now paginate after computing totals
            $orders = $orders->paginate(20);
            $count = $orders->total();
        }

        $utmSources = OrderTrack::distinct()->pluck('utm_source')->filter()->values();
        return view('backend.reports.sales_report', compact('orders', 'sum', 'count', 'grand', 'delivery_charge', 'grand_delivery', 'utmSources'));
    }

    public function export_sales_report(Request $request)
    {
        try{
            $date = $request->date;
            $seller_id = $request->seller;
            $product_id = $request->product;
            $search = $request->search;
            $delivery_status = $request->delivery_status;
            $payment_method = $request->payment_method;
            $payment_status = $request->payment_status;
            $order_source = $request->order_source;
            $orders = [];
            $sum = null;
            $count = null;
            $grand = 0;
            $delivery_charge = 0;
            $grand_delivery = 0;
            if ($request->has('submit') && $request->submit == 'yes' && ($date != '' or $delivery_status != '' or $payment_method != '' or $payment_status != '' or $order_source != '' or $seller_id != '' or $search != '')) {
                $qtyQuery = Order::with('orderDetails');
                if (!empty($product_id)) {
                    $qtyQuery = Order::with('orderDetails')
                        ->whereHas('orderDetails', function ($query) use ($product_id) {
                            $query->where('product_id', $product_id);
                        });
                }

                $orders = $qtyQuery
                    ->where('orders.delivery_status', '!=', 'cancelled')
                    ->where('orders.delivery_status', '!=', 'returned')
                    ->orderBy('orders.id', 'desc')
                    ->groupBy('orders.id');

                if ($date != null) {
                    $orders = $orders
                        ->whereBetween('orders.created_at', [date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0])), date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1]))]);
                }

                if ($search != null) {
                    $orders = $orders->where(function ($query) use ($search) {
                        $query->orWhere('orders.code', 'like', '%' . $search . '%')
                            ->orWhere('orders.shipping_address', 'like', '%' . $search . '%');
                    });
                }

                if ($delivery_status != null) {
                    $orders = $orders->where('orders.delivery_status', '=', $delivery_status);
                }

                if ($payment_method != null) {
                    $orders = $orders->where('orders.payment_type', '=', $payment_method);
                }

                if ($payment_status != null) {
                    $orders = $orders->where('orders.payment_status', '=', $payment_status);
                }
                if ($order_source != null) {
                    $orders = $orders->where('orders.order_source', '=', $order_source);
                }

                if ($seller_id != null) {
                    $orders = $orders->where('orders.seller_id', '=', $seller_id);
                }

                // Calculate the grand total excluding shipping costs
                $allOrders = (clone $orders)->get();
                $grand = $allOrders->sum(function ($item) {
                    return $item->grand_total - $item->orderDetails->sum('shipping_cost');
                });

                // Calculate the full sum of grand_total
                $grand_delivery = $allOrders->sum('grand_total');

                // Calculate Delivery Charge
                $delivery_charge = $grand_delivery - $grand;

                // Now paginate after computing totals
                $orders = $orders->get();
            }

            // dd($orders->first());
            // ? Direct return for small data
            $filename = 'sales-report-'.time().'.xlsx';
            $summary = [
                'sales_amount' => $grand,
                'delivery_charge' => $delivery_charge,
                'total' => $grand_delivery
            ];
            return Excel::download(new SalesReportExport($orders, $summary), $filename);

        }catch(\Exception $e){
            Log::error('Sales Report Export Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Server Error!');
        }
    }

    public function purchase_order_report(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $seller_id = null;

        $purchaseorder = PurchaseOrder::orderBy('purchase_date', 'desc');

        $purchase_order_report = PurchaseOrderItem::select(['purchase_order_item.product_id', 'purchase_order_item.price', 'purchase_order_item.qty', 'purchase_order_item.total_price', 'purchase_order.user_id', 'purchase_order.po_number', 'purchase_order.supplier_id', 'purchase_order_item.variant', 'purchase_order.purchase_date'])
            ->leftJoin('purchase_order', 'purchase_order_item.purchase_order_id', '=', 'purchase_order.id')
            ->orderBy('purchase_order.id', 'desc');

        if ($request->has('user_id') && $request->user_id != null) {
            $seller_id = $request->user_id;
            $purchase_order_report = PurchaseOrderItem::select(['purchase_order_item.product_id', 'purchase_order_item.price', 'purchase_order_item.qty', 'purchase_order_item.total_price', 'purchase_order.user_id', 'purchase_order.po_number', 'purchase_order.supplier_id'])
                ->leftJoin('purchase_order', 'purchase_order_item.purchase_order_id', '=', 'purchase_order.id')
                ->orderBy('purchase_order.id', 'desc')->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $sort_search = $request->search;
            $purchase_order_report = $purchase_order_report->where(function ($query) use ($sort_search) {
                $query->orWhere('po_number', 'like', '%' . $sort_search . '%');
            });
        }

        if ($date != null) {
            $purchase_order_report = $purchase_order_report->where('purchase_order.purchase_date', '>=', strtotime(explode(" to ", $date)[0]))->where('purchase_order.purchase_date', '<=', strtotime(explode(" to ", $date)[1]));
        }

        $purchase_order_reports = $purchase_order_report->paginate(15);

        return view('backend.reports.purchase_order.index', compact('sort_search', 'date', 'seller_id', 'purchase_order_reports'));
    }

    public function testt($date){
        return $date;
    }

    public function products_stock_new(Request $request)
    {
        $brands = Brand::orderBy('name', 'asc')->get();
        $date = $request->date;
        $brand_id = $request->brand;
        $products = [];

        if ($request->has('submit') && $request->submit == 'yes' && ($date != '')) {
            $from = date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0]));
            $fromx = strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[0])));

            $to = date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1]));
            $tox = strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[1])));

            $products = DB::table('product_stocks')
                ->join('products', 'products.id', '=', 'product_stocks.product_id')
                ->select(
                    "products.id", "products.name", "product_stocks.variant", "products.published", "products.brand_id",

                    DB::raw("(SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date < '$fromx') AS opening_purchase"),

                    // DB::raw("(SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at < '$from') AS opening_sell"),
                    DB::raw("(SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at < '$from') AS opening_sell"),

                    DB::raw("(SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox') AS current_purchase"),

                    // DB::raw("(SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to') AS current_sell"),
                    DB::raw("(SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$from' AND '$to') AS current_sell"),

                    DB::raw("(SELECT AVG(price) FROM purchase_order_item WHERE product_id = products.id) AS avg_price"),

                    DB::raw("(SELECT price FROM purchase_order_item WHERE product_id = products.id ORDER BY id DESC LIMIT 1) AS last_price"),

                    DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx') AS opening_minus_adjustment"),

                    DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox') AS current_minus_adjustment"),

                    DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx') AS opening_plus_adjustment"),

                    DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox') AS current_plus_adjustment")
                )
                ->where('products.published', 1)
                ->orderBy('products.name', 'ASC');

            if (isset($brand_id)) {
                $products = $products->where('products.brand_id', $brand_id);
            }

            if ($request->has('search')) {
                $sort_search = $request->search;
                $products = $products->where(function ($query) use ($sort_search) {
                    $query->where('products.name', 'like', '%' . $sort_search . '%');
                });
            }

            $products = $products->get();
        }

        return view('backend.reports.products_stock_new', compact('date', 'brand_id', 'products', 'brands'));
    }

    public function products_stock_latest(Request $request)
    {
        $brands = Brand::orderBy('name', 'asc')->get();
        $date = $request->date;
        $brand_id = $request->brand;
        $products = [];

        if ($request->has('submit') && $request->submit == 'yes' && ($date != '')) {
            $from = date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0]));
            $fromx = strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[0])));

            $to = date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1]));
            $tox = strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[1])));

            $lastClosingStockDate = ProductsClosingStock::where('date' , '<', $from)->orderBy('date', 'desc')->first()->date ?? null;

            // dd($lastClosingStockDate);
            $products = DB::table('product_stocks')
                ->join('products', 'products.id', '=', 'product_stocks.product_id')
                ->select("products.id", "products.name", "product_stocks.variant", "products.published", "products.brand_id");

            if($lastClosingStockDate){
                $products = $products->addSelect(
                    DB::raw("(SELECT products_closing_stocks.closing_stock FROM products_closing_stocks WHERE products_closing_stocks.product_id = product_stocks.product_id AND products_closing_stocks.date < '$from' ORDER BY products_closing_stocks.date DESC LIMIT 1) AS last_closing_stock")
                );

                $lastStockDate = Carbon::parse($lastClosingStockDate)->format('Y-m-d');
                $endTo = Carbon::parse($from)->subDay(1)->format('Y-m-d 23:59:59');
                $endTox = strtotime($endTo);
                if($lastStockDate != $endTo){
                    $startFrom = Carbon::parse($lastStockDate)->addDay(1)->format('Y-m-d 00:00:00');
                    $startFromx = strtotime($startFrom);
                    $products->addSelect(
                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE purchase_order_item.product_id = product_stocks.product_id AND purchase_order_item.variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$startFromx' AND '$endTox'), 0) AS last_current_purchase"),

                        // DB::raw("COALESCE((SELECT SUM(order_details.quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE order_details.product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at BETWEEN '$startFrom' AND '$endTo'), 0) AS last_current_sell"),
                        DB::raw("COALESCE((SELECT SUM(order_details.quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE order_details.product_id = product_stocks.product_id AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$startFrom' AND '$endTo'), 0) AS last_current_sell"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE stock_adjust_items.product_id = product_stocks.product_id AND stock_adjust_items.variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$startFromx' AND '$endTox'), 0) AS last_current_minus_adjustment"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE stock_adjust_items.product_id = product_stocks.product_id AND stock_adjust_items.variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$startFromx' AND '$endTox'), 0) AS last_current_plus_adjustment")
                    );
                }
            }else{
                $products->addSelect(
                    DB::raw("(SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE purchase_order_item.product_id = product_stocks.product_id AND purchase_order_item.variant = product_stocks.id AND purchase_order.purchase_date < '$fromx') AS opening_purchase"),

                    // DB::raw("(SELECT SUM(order_details.quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE order_details.product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at < '$from') AS opening_sell"),
                    DB::raw("(SELECT SUM(order_details.quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE order_details.product_id = product_stocks.product_id AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at < '$from') AS opening_sell"),

                    DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE stock_adjust_items.product_id = product_stocks.product_id AND stock_adjust_items.variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx') AS opening_minus_adjustment"),

                    DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE stock_adjust_items.product_id = product_stocks.product_id AND stock_adjust_items.variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx') AS opening_plus_adjustment")
                );
            }

            $products->addSelect(
                DB::raw("(SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE purchase_order_item.product_id = product_stocks.product_id AND purchase_order_item.variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox') AS current_purchase"),

                // DB::raw("(SELECT SUM(order_details.quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE order_details.product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at BETWEEN '$from' AND '$to') AS current_sell"),
                DB::raw("(SELECT SUM(order_details.quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE order_details.product_id = product_stocks.product_id AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$from' AND '$to') AS current_sell"),

                DB::raw("(SELECT AVG(price) FROM purchase_order_item WHERE product_id = products.id) AS avg_price"),

                DB::raw("(SELECT price FROM purchase_order_item WHERE product_id = products.id ORDER BY id DESC LIMIT 1) AS last_price"),

                DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE stock_adjust_items.product_id = product_stocks.product_id AND stock_adjust_items.variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox') AS current_minus_adjustment"),

                DB::raw("(SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE stock_adjust_items.product_id = product_stocks.product_id AND stock_adjust_items.variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox') AS current_plus_adjustment")
            )
            ->where('products.published', 1)
            ->orderBy('products.name', 'ASC');

            if (isset($brand_id)) {
                $products = $products->where('products.brand_id', $brand_id);
            }

            if ($request->has('search')) {
                $sort_search = $request->search;
                $products = $products->where(function ($query) use ($sort_search) {
                    $query->where('products.name', 'like', '%' . $sort_search . '%');
                });
            }
            //dd($products->toSql());
            $products = $products->paginate(10);
        }

        return view('backend.reports.products_stock_latest', compact('date', 'brand_id', 'products', 'brands'));
    }

    // Brand Wise Stock Report
    public function brandWiseStockReport(Request $request)
    {
        $brands = Brand::query()->orderBy('name', 'asc')->get();
        $brand_id = $request->brand;
        $cacheKey = 'brand_stock_report_' . ($brand_id ?? 'all');

        $brandStocks = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($brand_id) {
            $query = DB::table('product_stocks')
                ->join('products', 'products.id', '=', 'product_stocks.product_id')
                ->join('brands', 'brands.id', '=', 'products.brand_id')
                ->select(
                    'brands.id as brand_id',
                    'brands.name as brand_name',
                    DB::raw('COUNT(DISTINCT products.id) as number_of_products'),
                    DB::raw('SUM(product_stocks.qty) as total_available_stock'),
                    DB::raw('SUM(product_stocks.qty * COALESCE((
                        SELECT price FROM purchase_order_item
                        WHERE product_id = products.id
                        ORDER BY id DESC LIMIT 1
                    ), 0)) as total_stock_value')
                )
                ->where('products.published', 1)
                ->groupBy('brands.id', 'brands.name');

            if ($brand_id) {
                $query->where('brands.id', $brand_id);
            }

            return $query->orderByDesc('total_stock_value')->get();
        });

        $grandTotalStockValue = $brandStocks->sum('total_stock_value');

        return view('backend.reports.brand_wise_stock', compact(
            'brandStocks', 'brands', 'brand_id', 'grandTotalStockValue'
        ));
    }

    public function topSellingProducts(Request $request)
    {
        // dd($request->all());
        $totalQty = 0;
        $totalAmount = 0;
        $purchaseTotal = 0;

        $date = $request->date;
        $id = $request->product_id;
        $brand_id = $request->brand_id;
        $search = $request->search;
        $status = $request->status;
        if ($request->has('submit') && $request->submit == 'yes') {
                $sales_report = OrderDetail::with(['product.stocks', 'product.brand', 'product.lastPurchaseOrderItem'])
                        ->join('orders', 'orders.id', '=', 'order_details.order_id')
                        ->join('products', 'products.id', '=', 'order_details.product_id')
                        ->join('brands', 'brands.id', '=', 'products.brand_id')
                        ->join('categories', 'categories.id', '=', 'products.category_id')
                        ->when(filled($date), function ($query) use ($date) {
                            $dateRange = explode(" to ", $date);
                            if (count($dateRange) == 2) {
                                $from = Carbon::parse($dateRange[0])->startOfDay()->toDateTimeString();
                                $to = Carbon::parse($dateRange[1])->endOfDay()->toDateTimeString();
                                $query->whereBetween('orders.created_at', [$from, $to]);
                            }
                            // $from = date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0]));
                            // $to = date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1]));
                            // $query->whereBetween('orders.created_at', [$from, $to]);
                        })
                        ->selectRaw('SUM(order_details.quantity) as total_quantity,
                                    SUM(order_details.price) as total_amount,
                                    MAX(order_details.created_at) as max_time,
                                    products.name as product_name,
                                    order_details.product_id,
                                    order_details.variation,
                                    brands.name as brand,
                                    categories.name as category,
                                    products.variant_product')
                        ->whereNotIn('orders.delivery_status', ['cancelled', 'returned'])
                        ->orderBy('total_quantity', 'desc');

            if ($id != null) {
                $product_id = $request->product_id;
                $sales_report = $sales_report->where(function ($query) use ($product_id) {
                    $query->orWhere('products.id', $product_id);
                });
            }

            if(!is_null($brand_id)) {
                $sales_report = $sales_report->where('products.brand_id', $brand_id);
            }

            if(filled($request->category_id)) {
                $sales_report = $sales_report->where('products.category_id', $request->category_id);
            }

            if (isset($status)) {
                $sales_report = $sales_report->where(function ($query) use ($status) {
                    $query->orWhere('orders.delivery_status', $status);
                });
            }

            if ($request->has('search') && ($id == null)) {
                $sort_search = $request->search;
                $sales_report = $sales_report->where(function ($query) use ($sort_search) {
                    $query->orWhere('products.name', 'like', '%' . $sort_search . '%');
                });
            }

            $allsales = $sales_report->groupBy('product_id')->groupBy('variation')->get();

            $totalQty = $allsales->sum('total_quantity');
            $totalAmount = $allsales->sum('total_amount');
            $purchaseTotal = $allsales->sum(function ($sale) {
                $lastPurchasePrice = $sale->product?->lastPurchaseOrderItem?->price ?? 0;
                return $sale->total_quantity * $lastPurchasePrice;
            });

            $sales = $allsales;
        } else {
            $sales = [];
        }

        if ($request->has('export') && $request->export == 'yes') {
            if (empty($sales)) {
                flash('No data available for export.')->error();
                return redirect()->back();
            }
            $filename = 'top-selling-products-'.time().'.xlsx';
            return Excel::download(new TopSellingProductsExport($sales), $filename);
        }

        return view('backend.reports.top-selling-products', compact('sales', 'totalQty', 'totalAmount', 'purchaseTotal'));
    }

    public function notSellingProducts(Request $request)
    {
        $today = now()->format('Y-m-d');
        $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : $today;
        $date = $date > $today ? $today : $date;

        $brand_id = $request->brand_id;
        $search = $request->search;

        $sold_product_ids = Cache::remember("sold_product_ids_{$date}_{$today}", now()->addMinutes(60), function () use ($date, $today) {
            return OrderDetail::whereHas('order', function ($query) use ($date, $today) {
                    $query->whereNotIn('delivery_status', ['cancelled', 'returned']);
                    if ($date) {
                        $query->whereBetween('created_at', [$date, $today]);
                    }
                })
                ->select('product_id')
                ->distinct()
                ->pluck('product_id');
        });

        $unsoldProductsQuery = Product::published()->notNew()
            ->with([
                'lastPurchaseOrderItem:id,product_id,price,updated_at',
                'brand:id,name'
            ])
            ->select('products.id', 'products.name', 'products.slug', 'products.brand_id', 'products.created_at')
            ->addSelect(['latest_stock_qty' => function ($query) {
                $query->select('qty')
                    ->from('product_stocks')
                    ->whereColumn('product_id', 'products.id')
                    ->orderBy('updated_at', 'desc')
                    ->limit(1);
            }])
            // Only include products that have stock available
            ->having('latest_stock_qty', '>', 0)
            // Exclude products with recent purchase orders in the last month
            ->whereHas('lastPurchaseOrderItem', function ($query) use ($today) {
                $query->whereNotBetween('updated_at', [
                    Carbon::parse(now()->subMonth())->startOfDay(),
                    Carbon::parse($today)->startOfDay()
                ]);
            })
            ->whereNotIn('id', $sold_product_ids)
            ->when($brand_id, fn ($q) => $q->where('brand_id', $brand_id))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('latest_stock_qty', $request->sort_by ?? 'desc');

        $allUnsoldProducts = (clone $unsoldProductsQuery)->get();

        $summary = [
            'total_stocks' => $allUnsoldProducts->sum('latest_stock_qty'),
            'total_stock_amount' => $allUnsoldProducts->sum(function ($product) {
                $lastPurchasePrice = $product->lastPurchaseOrderItem?->price ?? 0;
                return $product->latest_stock_qty * $lastPurchasePrice;
            }),
        ];

        if ($request->boolean('export')) {
            $filename = 'not-selling-products-'.time().'.xlsx';
            return Excel::download(new NotSellingProductsExport($allUnsoldProducts, $summary), $filename);
        }

        if ($request->per_page === 'all') {
            $unsoldProducts = $allUnsoldProducts;
            $unsoldProductsCount = $unsoldProducts->count();
        } else {
            $perPage = is_numeric($request->per_page) && $request->per_page > 0 ? min($request->per_page, 100) : 25;
            $unsoldProducts = $unsoldProductsQuery->paginate($perPage);
            $unsoldProductsCount = $unsoldProducts->total();
        }
        return view('backend.reports.not-selling-products', compact('unsoldProducts', 'unsoldProductsCount', 'summary'));
    }

    public function notSellingProductsExport(Request $request)
    {
        return $this->notSellingProducts($request->merge(['export' => true]));
    }


    public function shippingScannedReport(Request $request)
    {

        $date = $request->date;
        $entry_status = $request->entry_status;
        $shipping_method = $request->shipping_method;
        $methods = ShippingMethod::select('id', 'name', 'status')->where('status', 1)->get();
        $logs = [];

        if ($request->has('submit') && $request->submit == 'yes' && ($date != '')) {

            $logs = ShippingLog::with('order.orderDetails', 'shipping_method')->orderBy('created_at', 'desc');

            if ($request->has('date') && $request->date != '') {
                $logs = $logs->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0])), date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1]))]);
            }

            if ($request->has('entry_status') && $request->entry_status != '') {
                $logs = $logs->where('createdEntry', intval($request->entry_status));
            }

            if ($request->has('shipping_method') && $request->shipping_method != '') {
                $logs = $logs->where('shipping_method_id', $request->shipping_method);
            }

            $logs = $logs->get();
        }

        return view('backend.reports.shipping-scanned-report', compact('date', 'entry_status', 'shipping_method', 'methods', 'logs'));
    }

    /**
     * ? Old Stock Report by Product
     */
    public function stock_by_product(Request $request)
    {
        $allproducts = Product::where('published', 1)->orderBy('num_of_sale', 'DESC')->get();
        $date = $request->date;
        $product_id = $request->product_id;
        $variant_id = $request->variant_id ?? "NULL";

        $report = [];
        if ($request->has('submit') && $request->submit == 'yes' && ($date != '') && isset($product_id)) {
            list($startDateStr, $endDateStr) = explode(' to ', $date);
            $startDate = Carbon::createFromFormat('d-m-Y', $startDateStr);
            $endDate = Carbon::createFromFormat('d-m-Y', $endDateStr);

            $period = CarbonPeriod::create($startDate, $endDate);
            $dateArray = [];
            foreach ($period as $date) {
                $dateArray[] = $date->format('d-m-Y');
            }
            foreach ($dateArray as $dateY) {
                $from = date('Y-m-d 00:00:00', strtotime($dateY));
                $fromx = strtotime($from);

                $to = date('Y-m-d 23:59:59', strtotime($dateY));
                $tox = strtotime($to);

                $data = DB::table('product_stocks')
                    ->join('products', 'products.id', '=', 'product_stocks.product_id')
                    ->select(
                        DB::raw("'$dateY' AS 'date'"),

                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date < '$fromx'), 0) AS opening_purchase"),

                        // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at < '$from'), 0) AS opening_sell"),
                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at < '$from'), 0) AS opening_sell"),

                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                        DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                        DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),

                        // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),
                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx'), 0) AS opening_minus_adjustment"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS minus_adjustments"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx'), 0) AS opening_plus_adjustment"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS plus_adjustments")
                    )
                    ->where('product_id', $product_id)
                    ->get()->toArray();

                $report[] = $data[0];
                // dump($data[0]);

            }

        }
        $date = $request->date;

        return view('backend.reports.stock_report_product', compact('date', 'product_id', 'report', 'allproducts'));
    }

    /**
     * ? New Stock Report by Product
     */
    public function stock_by_product_new(Request $request)
    {
        $allproducts = Product::where('published', 1)->orderBy('num_of_sale', 'DESC')->get();
        $date = $request->date;
        $product_id = $request->product_id;
        $variant_id = $request->variant_id ?? "NULL";

        $report = [];
        if ($request->has('submit') && $request->submit == 'yes' && ($date != '') && isset($product_id)) {
            list($startDateStr, $endDateStr) = explode(' to ', $date);
            $startDate = Carbon::createFromFormat('d-m-Y', $startDateStr);
            $endDate = Carbon::createFromFormat('d-m-Y', $endDateStr);

            $period = CarbonPeriod::create($startDate, $endDate);
            $dateArray = [];
            foreach ($period as $date) {
                $dateArray[] = $date->format('d-m-Y');
            }

            foreach ($dateArray as $index => $dateY) {
                $from = date('Y-m-d 00:00:00', strtotime($dateY));
                $to = date('Y-m-d 23:59:59', strtotime($dateY));

                $fromx = strtotime($from);
                $tox = strtotime($to);

                // dd($from, $to, $fromx, $tox, $dateY);
                $opening = 0;
                $reportData = [];
                if($index === 0){
                    $last_closing_stock = ProductsClosingStock::where('product_id', $product_id)->where('date', '<', $from)->orderBy('date', 'desc')->first();

                    if($last_closing_stock){
                        $lastStockDate = Carbon::parse($last_closing_stock->date)->format('Y-m-d');
                        $lastStockDateQuantity = $last_closing_stock->closing_stock;
                        $endTo = Carbon::parse($from)->subDay(1)->format('Y-m-d 23:59:59');
                        $endTox = strtotime($endTo);
                        if($lastStockDate != $endTo){
                            $startFrom = Carbon::parse($lastStockDate)->addDay(1)->format('Y-m-d 00:00:00');
                            $startFromx = strtotime($startFrom);

                            // dd($startFrom, $endTo);
                            $data = DB::table('product_stocks')
                                ->join('products', 'products.id', '=', 'product_stocks.product_id')
                                ->select(
                                    DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$startFromx' AND '$endTox'), 0) AS purchases"),

                                    // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at BETWEEN '$startFrom' AND '$endTo'), 0) AS sales"),
                                    DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND orders.delivery_status <> 'preorder' AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$startFrom' AND '$endTo'), 0) AS sales"),

                                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$startFromx' AND '$endTox'), 0) AS minus_adjustments"),

                                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$startFromx' AND '$endTox'), 0) AS plus_adjustments")
                                )->where('product_id', $product_id)
                                ->first();
                            // dd($data);

                            $adjustments = $data->plus_adjustments - $data->minus_adjustments;
                            $closing = $lastStockDateQuantity + $data->purchases - $data->sales + $adjustments;
                            $data->closing = $closing;
                            $opening = $closing;
                            // dd($data, $opening);
                            // dd($lastStockDate, $startFrom, $endTo, $data);
                        }else{
                            $opening = $lastStockDateQuantity;
                        }
                    }else{
                        $data = DB::table('product_stocks')
                            ->join('products', 'products.id', '=', 'product_stocks.product_id')
                            ->select(
                                DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date < '$fromx'), 0) AS opening_purchase"),

                                // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at < '$from'), 0) AS opening_sell"),
                                DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at < '$from'), 0) AS opening_sell"),

                                DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx'), 0) AS opening_minus_adjustment"),

                                DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx'), 0) AS opening_plus_adjustment")
                            )->where('product_id', $product_id)
                            ->first();

                        $adjustment = $data->opening_plus_adjustment - $data->opening_minus_adjustment;
                        $opening = $data->opening_purchase - $data->opening_sell + $adjustment;
                        // dd($data);
                    }

                    $data = DB::table('product_stocks')
                    ->join('products', 'products.id', '=', 'product_stocks.product_id')
                    ->select(
                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                        // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),
                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS minus_adjustments"),

                        DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS plus_adjustments"),

                        DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                        DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),
                    )->where('product_id', $product_id)
                    ->first();
                    $adjustment = $data->plus_adjustments - $data->minus_adjustments;
                    $closing = $opening + $data->purchases - $data->sales + $adjustment;

                    $reportData = [
                        'date' => $dateY,
                        'opening' => $opening,
                        'purchases' => $data->purchases,
                        'sales' => $data->sales,
                        'minus_adjustments' => $data->minus_adjustments,
                        'plus_adjustments' => $data->plus_adjustments,
                        'closing' => $closing,
                        'po_id' => $data->po_id,
                        'po_number' => $data->po_number
                    ];
                }else{
                    $data = DB::table('product_stocks')
                        ->join('products', 'products.id', '=', 'product_stocks.product_id')
                        ->select(
                            DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                            // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),
                            DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (product_stocks.variant = '' OR (product_stocks.variant != '' AND order_details.variation = product_stocks.variant)) AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

                            DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS minus_adjustments"),

                            DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS plus_adjustments"),

                            DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                            DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),
                        )->where('product_id', $product_id)
                        ->first();

                    $opening = $report[$index - 1]['closing'];
                    $adjustment = $data->plus_adjustments - $data->minus_adjustments;
                    $closing = $opening + $data->purchases - $data->sales + $adjustment;
                    $reportData = [
                        'date' => $dateY,
                        'opening' => $opening,
                        'purchases' => $data->purchases,
                        'sales' => $data->sales,
                        'minus_adjustments' => $data->minus_adjustments,
                        'plus_adjustments' => $data->plus_adjustments,
                        'closing' => $closing,
                        'po_id' => $data->po_id,
                        'po_number' => $data->po_number
                    ];
                }
                // dd($last_closing_stock);
                $report[] = $reportData;
            }
        }
        // dd($report);
        $date = $request->date;
        return view('backend.reports.stock_report_product_new', compact('date', 'product_id', 'report', 'allproducts'));
    }

    public function expireProductsReport(Request $request)
    {
        if(get_setting('enable_product_expire_date') != 1) {
            abort(404);
        }
        $product = $request->product;
        $date = $request->date;
        $search = $request->search;

        $beforeDays = (int) get_setting('expire_products_alert_duration', 7);
        if(filled($date)) {
            $expireDate = Carbon::parse($date)->format('Y-m-d');
        }else{
            $expireDate = now()->addDays($beforeDays)->format('Y-m-d');
        }


        $items = PurchaseOrderItem::with('product')
            ->whereNotNull('expire_date')
            ->whereDate('expire_date', '<=', $expireDate)
            ->where('left_qty', '>', 0)
            ->when($product, function ($query) use ($product) {
                return $query->where('product_id', $product);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('expire_date', 'asc')
            ->paginate(50);

        return view('backend.reports.expire_products', compact('items', 'date', 'search', 'product'));
    }

    public function expireProductsExport(Request $request)
    {
        $product = $request->product;
        $date = $request->date;
        $search = $request->search;

        $beforeDays = (int) get_setting('expire_products_alert_duration', 7);
        if(filled($date)) {
            $expireDate = Carbon::parse($date)->format('Y-m-d');
        }else{
            $expireDate = now()->addDays($beforeDays)->format('Y-m-d');
        }

        $items = PurchaseOrderItem::with('product')
            ->whereNotNull('expire_date')
            ->whereDate('expire_date', '<=', $expireDate)
            ->where('left_qty', '>', 0)
            ->when($product, function ($query) use ($product) {
                return $query->where('product_id', $product);
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('expire_date', 'asc')
            ->get();

        $filename = 'expire-products-'.time().'.xlsx';
        return Excel::download(new ExpireProductsExport($items), $filename);
    }

    public function test()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $from = date('Y-m-d 00:00:00', strtotime($yesterday));
        $fromx = strtotime($from);

        $to = date('Y-m-d 23:59:59', strtotime($yesterday));
        $tox = strtotime($to);

        $products = Product::where('published', 1)->get();

        foreach ($products as $product) {
            $data = DB::table('product_stocks')
                ->join('products', 'products.id', '=', 'product_stocks.product_id')
                ->select(
                    DB::raw("'$yesterday' AS 'date'"),

                    DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date < '$fromx'), 0) AS opening_purchase"),

                    // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.delivery_status <> 'returned' AND order_details.created_at < '$from'), 0) AS opening_sell"),
                    DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at < '$from'), 0) AS opening_sell"),

                    DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                    DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                    DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),

                    // DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),
                    DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND (orders.delivery_status <> 'preorder' OR order_details.delivery_status <> 'preorder') AND (orders.delivery_status <> 'cancelled' OR order_details.delivery_status <> 'cancelled') AND (orders.delivery_status <> 'returned' OR order_details.delivery_status <> 'returned') AND orders.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx'), 0) AS opening_minus_adjustment"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS minus_adjustments"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx'), 0) AS opening_plus_adjustment"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS plus_adjustments")
                )
                ->where('product_id', 46)
                ->get()->toArray();

            if ($data) {
                $data = $data[0];
                // dd($data);
                $openStock = ($data->opening_purchase + $data->opening_plus_adjustment) - ($data->opening_sell + $data->opening_minus_adjustment);
                $adjustments = ($data->plus_adjustments - $data->minus_adjustments);
                $closingStock = $openStock + $data->purchases - $data->sales + $adjustments;

                ProductsClosingStock::updateOrCreate([
                    'product_id' => $product->id,
                    'date' => Carbon::parse($yesterday)->format('Y-m-d 23:59:59'),
                ], [
                    'closing_stock' => $closingStock,
                ]);
            }
        }
    }
}
