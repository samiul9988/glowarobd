<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Search;
use App\Models\Seller;
use App\Models\Wallet;
use App\Models\Product;
use Carbon\CarbonPeriod;
use App\Models\OrderDetail;
use App\Models\ShippingLog;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\ShippingMethod;
use App\Models\CommissionHistory;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateClosingStockJob;
use App\Models\ProductsClosingStock;
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
            // $orders = Order::where('delivery_status','!=', 'cancelled')->orderBy('id', 'desc');

            // $qtyQuery = "SELECT SUM(quantity) FROM order_details WHERE order_details.order_id = orders.id AND order_details.product_id > 0";
            $qtyQuery = Order::with('orderDetails');
            if (!empty($product_id)) {
                // $qtyQuery = "SELECT SUM(quantity) FROM order_details WHERE order_details.order_id = orders.id AND order_details.product_id = ".$product_id."";
                $qtyQuery = Order::with('orderDetails')
                    ->whereHas('orderDetails', function ($query) use ($product_id) {
                        $query->where('product_id', $product_id);
                    });
            }

            $orders = $qtyQuery
                ->where('orders.delivery_status', '!=', 'cancelled')
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

            // if($product_id != null){
            //     $orders = $orders->where('order_details.product_id', '=', $product_id);
            // }

            // $orders = $orders->get();
            // dd($orders[0]);
            // $count = count($orders);

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

            // dd($grand_delivery, $grand, $delivery_charge);
        }

        return view('backend.reports.sales_report', compact('date', 'search', 'orders', 'delivery_status', 'payment_method', 'payment_status', 'order_source', 'seller_id', 'sum', 'count', 'product_id', 'grand', 'delivery_charge', 'grand_delivery'));
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

                    DB::raw("(SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at < '$from') AS opening_sell"),

                    DB::raw("(SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox') AS current_purchase"),

                    DB::raw("(SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to') AS current_sell"),

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

    public function topSellingProducts(Request $request)
    {

        $products = Product::where('published', 1)->get();
        $totalQty = 0;
        $totalAmount = 0;

        $date = $request->date;
        $id = $request->product_id;
        $search = $request->search;
        $status = $request->status;
        if ($request->has('submit') && $request->submit == 'yes') {
            $sales_report = OrderDetail::join('products', 'products.id', '=', 'order_details.product_id')
                ->join('purchase_order_item as poi', function ($join) {
                    $join->on('poi.product_id', '=', 'order_details.product_id')
                        ->leftJoin('purchase_order_item as poi2', function ($join) {
                            $join->on('poi.product_id', '=', 'poi2.product_id')
                                ->whereRaw('poi.created_at < poi2.created_at');
                        })
                        ->whereNull('poi2.product_id');
                })
                ->selectRaw('SUM(order_details.quantity) as total_quantity, SUM(order_details.price) as total_amount, MAX(order_details.created_at) as max_time, products.name as product_name, order_details.product_id, order_details.variation, poi.price as purchase_price')
                ->orderBy('total_quantity', 'desc');

            if ($id != null) {
                $product_id = $request->product_id;
                $sales_report = $sales_report->where(function ($query) use ($product_id) {
                    $query->orWhere('products.id', $product_id);
                });
            }

            if (isset($status)) {
                $sales_report = $sales_report->where(function ($query) use ($status) {
                    $query->orWhere('order_details.delivery_status', $status);
                });
            }

            if ($request->has('search') && ($id == null)) {
                $sort_search = $request->search;
                $sales_report = $sales_report->where(function ($query) use ($sort_search) {
                    $query->orWhere('products.name', 'like', '%' . $sort_search . '%');
                });
            }

            if ($date != null) {
                $from = date('Y-m-d 00:00:00', strtotime(explode(" to ", $date)[0]));
                $to = date('Y-m-d 23:59:59', strtotime(explode(" to ", $date)[1]));
                $sales_report = $sales_report->where('order_details.created_at', '>=', $from)->where('order_details.created_at', '<=', $to);
            }

            $allsales = $sales_report->groupBy('product_id')->groupBy('variation')->get();

            $totalQty = $allsales->sum('total_quantity');
            $totalAmount = $allsales->sum('total_amount');

            // $sales = $sales_report->groupBy('product_id')->groupBy('variation')->paginate(15);
            $sales = $allsales;
        } else {
            $sales = [];
        }

        return view('backend.reports.top-selling-products', compact('date', 'sales', 'products', 'id', 'totalQty', 'totalAmount', 'search', 'status'));
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

                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at < '$from'), 0) AS opening_sell"),

                        DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                        DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                        DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),

                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

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

                                    DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$startFrom' AND '$endTo'), 0) AS sales"),

                                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$startFromx' AND '$endTox'), 0) AS minus_adjustments"),

                                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$startFromx' AND '$endTox'), 0) AS plus_adjustments")
                                )->where('product_id', $product_id)
                                ->first();
                                // ->toSql();
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

                                DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at < '$from'), 0) AS opening_sell"),

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

                        DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

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

                            DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

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
        // dd($date);
        return view('backend.reports.stock_report_product_new', compact('date', 'product_id', 'report', 'allproducts'));
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

                    DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at < '$from'), 0) AS opening_sell"),

                    DB::raw("COALESCE((SELECT SUM(purchase_order_item.qty) FROM purchase_order_item INNER JOIN purchase_order ON purchase_order_item.purchase_order_id = purchase_order.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox'), 0) AS purchases"),

                    DB::raw("(SELECT purchase_order.id FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_id"),

                    DB::raw("(SELECT purchase_order.po_number FROM purchase_order INNER JOIN purchase_order_item ON purchase_order.id = purchase_order_item.purchase_order_id WHERE purchase_order_item.product_id = product_stocks.product_id AND variant = product_stocks.id AND purchase_order.purchase_date BETWEEN '$fromx' AND '$tox' LIMIT 1) AS po_number"),

                    DB::raw("COALESCE((SELECT SUM(quantity) FROM order_details INNER JOIN orders ON order_details.order_id = orders.id WHERE product_id = product_stocks.product_id AND order_details.delivery_status <> 'preorder' AND order_details.delivery_status <> 'cancelled' AND order_details.created_at BETWEEN '$from' AND '$to'), 0) AS sales"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date < '$fromx'), 0) AS opening_minus_adjustment"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND (stock_adjust.sa_type = 'damage' OR stock_adjust.sa_type = 'others') AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS minus_adjustments"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date < '$fromx'), 0) AS opening_plus_adjustment"),

                    DB::raw("COALESCE((SELECT SUM(stock_adjust_items.qty) FROM stock_adjust_items INNER JOIN stock_adjust ON stock_adjust_items.stock_adjust_id = stock_adjust.id WHERE product_id = product_stocks.product_id AND variant = product_stocks.id AND stock_adjust.sa_type = 'returned' AND stock_adjust.sa_date BETWEEN '$fromx' AND '$tox'), 0) AS plus_adjustments")
                )
                ->where('product_id', 46)
                ->get()->toArray();

            if ($data) {
                $data = $data[0];
                dd($data);
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
