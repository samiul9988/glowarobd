<?php

namespace App\Http\Controllers;

use App\Events\ProductOrStockUpdated;
use App\Exports\ProductsExport;
use App\Exports\StockOutProductsExport;
use App\Jobs\CacheProducts;
use App\Jobs\KireibdJob;
use App\Jobs\PurgeCloudflareCache;
use App\Models\AttributeValue;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Color;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\ProductCustomField;
use App\Models\ProductPrice;
use App\Models\ProductsCustomFieldsData;
use App\Models\ProductStock;
use App\Models\ProductTax;
use App\Models\ProductTranslation;
use App\Models\User;
use App\Utility\CategoryUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_products(Request $request)
    {
        $type = 'In House';
        $col_name = null;
        $query = null;
        $sort_search = null;

        $currentStatus = @$request->status;
        if($currentStatus==null){
            $currentStatus = 'published';
        }

        $products = Product::with('user', 'thumbnail_image', 'stocks', 'productprices')->where('added_by', 'admin')->where('auction_product',0);

        if ($request->type != null){
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        if ($request->search != null){
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);

        $productStatusCount = get_product_count_based_status();

        return view('backend.product.products.index', compact('products','type', 'col_name', 'query', 'sort_search', 'currentStatus', 'productStatusCount'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seller_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::with('user', 'thumbnail_image', 'stocks', 'productprices')->where('added_by', 'seller')->where('auction_product',0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null){
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }
        if ($request->type != null){
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Seller';

        return view('backend.product.products.index', compact('products','type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    public function all_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $brand_id = null;
        $category_id = null;

        $currentStatus = @$request->status;
        if ($currentStatus==null) {
            $currentStatus = 'published';
        }

        $products = Product::with('user:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'thumbnail_image', 'stocks', 'productprices')
            ->orderBy('created_at', 'desc')->where('auction_product',0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->has('brand_id') && $request->brand_id != null) {
            $products = $products->where('brand_id', $request->brand_id);
            $brand_id = $request->brand_id;
        }
        if ($request->has('category_id') && $request->category_id != null) {
            $products = $products->where('category_id', $request->category_id);
            $category_id = $request->category_id;
        }
        if ($request->search != null){
            $products = $products->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }

        if ($currentStatus != null){
            if($currentStatus == 'published'){
                $products = $products->where('published', 1);
            }elseif($currentStatus == 'unpublished'){
                $products = $products->where('published', 0);
            }elseif($currentStatus == 'outofstock'){
                $products = $products->whereHas('stocks', function ($q) {
                    $q->where('qty', '<=', 0);
                })->where('published', 1);
            }elseif($currentStatus == 'lowstock'){
                $products = $products->whereHas('stocks', function ($q) {
                    $q->where('qty', '>', 0)->whereRaw('qty <= products.low_stock_quantity');
                })->where('published', 1);
            }
        }

        if ($request->type != null){
            [$col_name, $query] = explode(",", $request->type);
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->paginate(15);

        $type = 'All';

        $productStatusCount = Cache::remember('all_product_status_count', now()->addMinutes(30), function () {
            return get_product_count_based_status();
        });
        return view('backend.product.products.index', compact('products','type', 'col_name', 'query', 'seller_id', 'sort_search', 'currentStatus', 'productStatusCount', 'brand_id', 'category_id'));
    }

    public function allStockOutProductsOld(Request $request)
    {
        $products = Product::with([
                'user:id,name',
                'stocks',
                'lastPurchaseOrderItem',
                'last30DaysSales',
            ])
            ->orderBy('created_at', 'desc')
            ->where('auction_product',0)
            ->where('published', 1)
            ->whereHas('stocks', function ($q) {
                $q->where('qty', '<=', 0);
            });

        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
        }
        if ($request->has('brand_id') && $request->brand_id != null) {
            $products = $products->where('brand_id', $request->brand_id);
        }
        if ($request->has('category_id') && $request->category_id != null) {
            $products = $products->where('category_id', $request->category_id);
        }
        if ($request->search != null){
            $products = $products->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $products->get();

        return view('backend.product.products.stock-out-products', compact('products'));
    }

    public function allStockOutProducts(Request $request)
    {
        $userId = $request->user_id;
        $brandId = $request->brand_id;
        $categoryId = $request->category_id;
        $search = trim($request->search);
        $cacheKey = 'all_stock_out_products_'.md5($userId.'_'.$brandId.'_'.$categoryId.'_'.$search);
        $products = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($request) {
                return Product::with([
                    'user:id,name', 'stocks', 'lastPurchaseOrderItem',
                ])
                ->withSum(['orderDetails as last_30_days_sell' => function ($q) {
                    $q->where('created_at', '>=', now()->subDays(30))->whereIn('delivery_status', ['delivered', 'picked_up']);
                }], 'quantity')
                ->orderByDesc('num_of_sale')
                ->orderByDesc('last_30_days_sell')
                ->where('auction_product', 0)
                ->where('published', 1)
                ->whereHas('stocks', function ($q) {
                    $q->where('qty', '<=', 0);
                })
                ->when($request->user_id, function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                })
                ->when($request->brand_id, function ($q) use ($request) {
                    $q->where('brand_id', $request->brand_id);
                })
                ->when($request->category_id, function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where('name', 'like', '%' . trim($request->search) . '%');
                })
                ->get();
        });

        if ($request->boolean('export')) {
            return Excel::download(new StockOutProductsExport($products), 'stock_out_products_' . time() . '.xlsx');
        }

        return view('backend.product.products.stock-out-products', compact('products'));
    }

    public function allStockOutProductsExport(Request $request)
    {
        return $this->allStockOutProducts($request->merge([
            'export' => true
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Cache::remember('categories_for_product_create_edit', now()->addDay(), function () {
            return Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        });

        // dd($categories->first());

        $customFields = Cache::remember('custom_fields_for_product_create', now()->addDay(), function () {
            return ProductCustomField::active()->get();
        });

        $brands = Cache::remember('filter_brands', now()->addDay(), function () {
            return \App\Models\Brand::pluck('name', 'id')->toArray();
        });

        return view('backend.product.products.create', compact('categories', 'customFields', 'brands'));
    }

    public function add_more_choice_option(Request $request) {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = new Product;
        $product->name = $request->name;
        $product->added_by = $request->added_by;
        if(Auth::user()->user_type == 'seller'){
            $product->user_id = Auth::user()->id;
            if(get_setting('product_approve_by_admin') == 1) {
                $product->approved = 0;
            }
        }
        else{
            $product->user_id = User::where('user_type', 'admin')->first()->id;
        }
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->barcode = $request->barcode;

        if(filled($request->usage_duration)){
            $product->usage_duration = $request->usage_duration ?? null;
        }

        if (addon_is_activated('refund_request')) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            }
            else {
                $product->refundable = 0;
            }
        }
        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;
        $product->faq_img = $request->faq_img;
        $product->unit = $request->unit;
        $product->min_qty = $request->min_qty;
        $product->low_stock_quantity = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;
        $product->external_link_btn = $request->external_link_btn;

        $product->created_at = now();
        $product->created_by = Auth::id() ?? null;

        $tags = array();
        if($request->tags[0] != null){
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags = implode(',', $tags);

        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->video_provider = $request->video_provider;
        $product->video_link = $request->video_link;
        $product->video_aspect_ratio = $request->video_aspect_ratio;
        $product->unit_price = $request->unit_price;
        $product->discount = $request->discount;
        if($request->min_order_amount){
            $product->min_order_amount  = $request->min_order_amount;
        }
        else{
            $product->min_order_amount  = 0;
        }
        $product->discount_type = $request->discount_type;

        if ($request->date_range != null) {
            $date_var               = explode(" to ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime( $date_var[1]);
        }

        $product->shipping_type = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (addon_is_activated('club_point')) {
            if($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if($request->shipping_type == 'free'){
                $product->shipping_cost = 0;
            }
            elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            }
            elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }
        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }

        $product->meta_title = $request->meta_title;
        $product->meta_description = $request->meta_description;

        if($request->has('meta_img')){
            $product->meta_img = $request->meta_img;
        } else {
            $product->meta_img = $product->thumbnail_img;
        }

        if($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        if($request->hasFile('pdf')){
            $product->pdf = $request->pdf->store('uploads/products/pdf');
        }

        $product->slug = Product::generateUniqueSlug($request->slug ?: $request->name);

        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $product->colors = json_encode($request->colors);
        }
        else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_'.$no;

                $item['attribute_id'] = $no;

                $data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($request[$str] as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        }
        else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);

        $product->published = 1;
        if($request->button == 'unpublish' || $request->button == 'draft') {
            $product->published = 0;
        }

        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }
        if ($request->has('featured')) {
            $product->featured = 1;
        }
        // if ($request->has('allow_out_Of_stock_purchases')) {
        //     $product->allow_stock_out_purchases = 1;
        // }
        if ($request->has('subscription')) {
            $product->subscription = 1;
        }
        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        }
        $product->cash_on_delivery = 0;
        if ($request->cash_on_delivery) {
            $product->cash_on_delivery = 1;
        }

        // Pre-oder values
        $product->pre_order = 0;
        if ($request->pre_order) {
            $product->pre_order = 1;
        }
        if ($request->preorder_date_range != null) {
            $date_variable = explode(" to ", $request->preorder_date_range);
            $product->preorder_start_date = strtotime($date_variable[0]);
            $product->preorder_end_date   = strtotime($date_variable[1]);
        }
        $product->preorder_max_qty = $request->maxpreorderqty;
        //$variations = array();
        $product->note = $request->note;

        // App Price Values
        $product->app_discount = $request->app_discount;
        $product->app_discount_type = $request->app_discount_type;
        if ($request->app_discount_date_range != null) {
            $date_varapp = explode(" to ", $request->app_discount_date_range);
            $product->app_discount_start_date = strtotime($date_varapp[0]);
            $product->app_discount_end_date   = strtotime( $date_varapp[1]);
        }

        $product->save();

        //VAT & Tax
        if($request->tax_id) {
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }
        //Flash Deal
        if($request->flash_deal_id) {
            $flash_deal_product = new FlashDealProduct;
            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->save();
        }

        //combinations start
        $options = array();
        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                foreach ($request[$name] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }
                array_push($options, $data);
            }
        }

        //Generates the combinations of customer choice options
        $combinations = makeCombinations($options);
        if(count($combinations[0]) > 0){
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination){
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ){
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else{
                        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                            $color_name = Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        }
                        else{
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if($product_stock == null){
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }

                $product_stock->variant = $str;
                $product_stock->price = $request['price_'.str_replace('.', '_', $str)];
                $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                $product_stock->qty = $request['qty_'.str_replace('.', '_', $str)];
                $product_stock->image = $request['img_'.str_replace('.', '_', $str)];
                $product_stock->save();
            }
        }
        else{
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }
        //combinations end

	    $product->save();
        //Price Break Down
        if (is_array($request->start_qty) && count($request->start_qty)>0) {
            for($i=0; $i<count($request->start_qty); $i++){
                if($request->start_qty[$i]!='' && $request->end_qty[$i]!='' && $request->price[$i]!=''){
                    $product_price = new ProductPrice();
                    $product_price->product_id = $product->id;
                    $product_price->start_qty = $request->start_qty[$i];
                    $product_price->end_qty = $request->end_qty[$i];
                    $product_price->price = $request->price[$i];
                    $product_price->save();
                }
            }
        }
        try{
            $dynamicFields = collect($request->all())->filter(function ($value, $key) {
                return Str::contains($key, '_dynamic');
            })->mapWithKeys(function ($value, $key) {
                $newKey = str_replace('_', ' ', str_replace('_dynamic', '', $key));
                return [$newKey => $value];
            })->toArray();
            $metaFields = collect($request->all())->filter(function ($value, $key) {
                return Str::contains($key, '_meta');
            })->mapWithKeys(function ($value, $key) {
                $newKey = str_replace('_', ' ', str_replace('_meta', '', $key));
                return [$newKey => $value];
            })->toArray();
            $customFields = ProductCustomField::whereIn('name', array_keys($dynamicFields))->pluck('id', 'name')->toArray();
            // dd($dynamicFields, $metaFields, $customFields);
            foreach ($dynamicFields as $key => $value) {
                if($value == null) continue;
                ProductsCustomFieldsData::create([
                    'product_id' => $product->id,
                    'product_custom_field_id' => $customFields[$key],
                    'meta_object_id' => $metaFields[$key],
                    'value' => json_encode($value)
                ]);
            }
        }catch(\Exception $e){
            //
        }

        flash(('Product has been inserted successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

        CacheProducts::dispatch()->onQueue('high');

        // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        // if (file_exists($cachedProductsFilePath)) {
        //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
        //     $jsonData = $items->toJson();
        //     file_put_contents($cachedProductsFilePath, $jsonData);
        // }

        if(Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff'){
            return redirect()->route('products.all');
        }
        else{
            if(addon_is_activated('seller_subscription')){
                $seller = Auth::user()->seller;
                $seller->remaining_uploads -= 1;
                $seller->save();
            }
            return redirect()->route('seller.products');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_product_edit(Request $request, $id)
    {
        $product = Product::with('customFieldsData.productCustomField','customFieldsData.metaObject.items')->findOrFail($id);

        $customFields = Cache::remember('custom_fields_for_product_edit_'.$id, now()->addHour(1), function () use ($product) {
            return ProductCustomField::with([
                'fieldsData' => function($query) use ($product) {
                    $query->where('product_id', $product->id);
                },
                'fieldsData.metaObject' => function($query) {
                    $query->active();
                },
                'fieldsData.metaObject.items' => function($query) {
                    $query->active();
                }
            ])->active()->get()->toArray();
        });
        // $customFieldsData = $product->customFieldsData->toArray();
        // dd($customFields);

        //dd(count($product->productprices));
        if($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Cache::remember('categories_for_product_create_edit', now()->addDay(), function () {
            return Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        });
        $brands = Cache::remember('filter_brands', now()->addDay(), function () {
            return \App\Models\Brand::pluck('name', 'id')->toArray();
        });
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang', 'customFields', 'brands'));
    }

     public function admin_product_stock(Request $request, $id)
     {
        $product = Product::findOrFail($id);

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.products.stock', compact('product', 'categories', 'tags','lang'));
     }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seller_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::all();
        return view('backend.product.products.edit', compact('product', 'categories', 'tags','lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $product                    = Product::findOrFail($id);
        $product->category_id       = $request->category_id;
        $product->brand_id          = $request->brand_id;
        $product->barcode           = $request->barcode;
        $product->cash_on_delivery = 0;
        $product->pre_order = 0;
        $product->subscription = 0;
        $product->featured = 0;
        $product->todays_deal = 0;
        $product->is_quantity_multiplied = 0;

        $product->updated_at = now();
        $product->updated_by = Auth::id() ?? null;

        $product->note = $request->note;
        $old_slug = $product->slug;

        if (addon_is_activated('refund_request')) {
            if ($request->refundable != null) {
                $product->refundable = 1;
            }
            else {
                $product->refundable = 0;
            }
        }

        if($request->lang == env("DEFAULT_LANGUAGE")){
            $product->name          = $request->name;
            $product->unit          = $request->unit;
            $product->description   = $request->description;
            $product->short_description   = $request->short_description;
        }

        if($request->slug != $old_slug){
            $product->slug = Product::generateUniqueSlug($request->slug ?: $request->name, $product->id);
        }

        $product->photos = $request->photos;
        $product->thumbnail_img = $request->thumbnail_img;
        $product->faq_img = $request->faq_img ?? $product->faq_img;
        $product->min_qty = $request->min_qty;
        $product->max_qty = $request->max_qty;
        $product->low_stock_quantity = $request->low_stock_quantity;
        $product->stock_visibility_state = $request->stock_visibility_state;
        $product->external_link = $request->external_link;
        $product->external_link_btn = $request->external_link_btn;

        $tags = array();
        if($request->tags[0] != null){
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags           = implode(',', $tags);

        $product->video_provider = $request->video_provider;
        $product->video_link     = $request->video_link;
        $product->video_aspect_ratio = $request->video_aspect_ratio;
        $product->unit_price     = $request->unit_price;
        $product->discount       = $request->discount;
        $product->min_order_amount  = $request->min_order_amount;
        $product->discount_type     = $request->discount_type;

        if ($request->date_range != null) {
            $date_var               = explode(" to ", $request->date_range);
            $product->discount_start_date = strtotime($date_var[0]);
            $product->discount_end_date   = strtotime( $date_var[1]);
        }

        // App Price Values
        $product->app_discount = $request->app_discount;
        $product->app_discount_type = $request->app_discount_type;
        if ($request->app_discount_date_range != null) {
            $date_varapp = explode(" to ", $request->app_discount_date_range);
            $product->app_discount_start_date = strtotime($date_varapp[0]);
            $product->app_discount_end_date   = strtotime( $date_varapp[1]);
        }

        // Pre-oder values
        if ($request->has('pre_order')) {
            $product->pre_order = 1;
        }
        if ($request->preorder_date_range != null) {
            $date_variable = explode(" to ", $request->preorder_date_range);
            $product->preorder_start_date = strtotime($date_variable[0]);
            $product->preorder_end_date   = strtotime($date_variable[1]);
        }
        $product->preorder_max_qty = $request->maxpreorderqty;

        $product->shipping_type  = $request->shipping_type;
        $product->est_shipping_days  = $request->est_shipping_days;

        if (addon_is_activated('club_point')) {
            if($request->earn_point) {
                $product->earn_point = $request->earn_point;
            }
        }

        if ($request->has('shipping_type')) {
            if($request->shipping_type == 'free'){
                $product->shipping_cost = 0;
            }
            elseif ($request->shipping_type == 'flat_rate') {
                $product->shipping_cost = $request->flat_shipping_cost;
            }
            elseif ($request->shipping_type == 'product_wise') {
                $product->shipping_cost = json_encode($request->shipping_cost);
            }
        }

        if ($request->has('is_quantity_multiplied')) {
            $product->is_quantity_multiplied = 1;
        }
        if ($request->has('cash_on_delivery')) {
            $product->cash_on_delivery = 1;
        }

        if ($request->has('featured')) {
            $product->featured = 1;
        } else {
            $product->featured = 0;
        }

        // if ($request->has('allow_out_Of_stock_purchases')) {
        //     $product->allow_stock_out_purchases = 1;
        // } else {
        //     $product->allow_stock_out_purchases = 0;
        // }

        if ($request->has('subscription')) {
            $product->subscription = 1;
        } else {
            $product->subscription = 0;
        }

        if ($request->has('todays_deal')) {
            $product->todays_deal = 1;
        } else {
            $product->todays_deal = 0;
        }

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;

        if($product->meta_title == null) {
            $product->meta_title = $product->name;
        }

        if($product->meta_description == null) {
            $product->meta_description = strip_tags($product->description);
        }

        if($product->meta_img == null) {
            $product->meta_img = $product->thumbnail_img;
        }

        $product->pdf = $request->pdf;

        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $product->colors = json_encode($request->colors);
        }
        else {
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_'.$no;

                $item['attribute_id'] = $no;

                $data = array();
                foreach ($request[$str] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        // foreach ($product->stocks as $key => $stock) {
        //     $stock->delete();
        // }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        }
        else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);


        //combinations start
        $options = array();
        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = makeCombinations($options);
        if(count($combinations[0]) > 0){
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination){
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ){
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else{
                        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                            $color_name = Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        }
                        else{
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }

                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if($product_stock == null){
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }
                if(isset($request['price_'.str_replace('.', '_', $str)])) {

                    $product_stock->variant = $str;
                    $product_stock->price = $request['price_'.str_replace('.', '_', $str)];
                    $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                    $product_stock->qty = $request['qty_'.str_replace('.', '_', $str)];
                    $product_stock->image = $request['img_'.str_replace('.', '_', $str)];

                    $product_stock->save();
                }
            }
        }else{
            $product_stock = $product->stocks->first();
            if($product_stock){
                $product_stock->price = $request->unit_price;
                $product_stock->save();
            }
        }

        if (is_array($request->start_qty) && count($request->start_qty)>0) {
            ProductPrice::where('product_id', $product->id)->delete();
            for($i=0; $i<count($request->start_qty); $i++){
                if($request->start_qty[$i]!='' && $request->end_qty[$i]!='' && $request->price[$i]!=''){
                    $product_price = new ProductPrice();
                    $product_price->product_id = $product->id;
                    $product_price->start_qty = $request->start_qty[$i];
                    $product_price->end_qty = $request->end_qty[$i];
                    $product_price->price = $request->price[$i];
                    $product_price->save();
                }
            }
        }

        $product->save();

        KireibdJob::dispatch([$product->id], 'update');

        // dd($old_slug, $product->slug, $request->rewrite_url);
        if($old_slug != $product->slug && filled($request->rewrite_url)){
            rewrite_url($old_slug, $product->slug);
        }
        //Flash Deal
        if($request->flash_deal_id) {
            if($product->flash_deal_product){
                $flash_deal_product = FlashDealProduct::findOrFail($product->flash_deal_product->id);
                if(!$flash_deal_product) {
                    $flash_deal_product = new FlashDealProduct;
                }
            } else {
                $flash_deal_product = new FlashDealProduct;
            }

            $flash_deal_product->flash_deal_id = $request->flash_deal_id;
            $flash_deal_product->product_id = $product->id;
            $flash_deal_product->discount = $request->flash_discount;
            $flash_deal_product->discount_type = $request->flash_discount_type;
            $flash_deal_product->save();
        }

        //VAT & Tax
        if($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            foreach ($request->tax_id as $key => $val) {
                $product_tax = new ProductTax;
                $product_tax->tax_id = $val;
                $product_tax->product_id = $product->id;
                $product_tax->tax = $request->tax[$key];
                $product_tax->tax_type = $request->tax_type[$key];
                $product_tax->save();
            }
        }

        try{
            $dynamicFields = collect($request->all())->filter(function ($value, $key) {
                return Str::contains($key, '_dynamic');
            })->mapWithKeys(function ($value, $key) {
                $newKey = str_replace('_', ' ', str_replace('_dynamic', '', $key));
                return [$newKey => $value];
            })->toArray();
            $metaFields = collect($request->all())->filter(function ($value, $key) {
                return Str::contains($key, '_meta');
            })->mapWithKeys(function ($value, $key) {
                $newKey = str_replace('_', ' ', str_replace('_meta', '', $key));
                return [$newKey => $value];
            })->toArray();
            $customFields = ProductCustomField::whereIn('name', array_keys($dynamicFields))->pluck('id', 'name')->toArray();

            ProductsCustomFieldsData::where('product_id', $product->id)->delete();
            foreach ($dynamicFields as $key => $value) {
                if($value == null) continue;
                ProductsCustomFieldsData::create([
                    'product_id' => $product->id,
                    'product_custom_field_id' => $customFields[$key],
                    'meta_object_id' => $metaFields[$key],
                    'value' => json_encode($value)
                ]);
            }
        }catch(\Exception $e){
            // dd($e->getMessage());
        }

        // Product Translations
        $product_translation                = ProductTranslation::firstOrNew(['lang' => $request->lang, 'product_id' => $product->id]);
        $product_translation->name          = $request->name;
        $product_translation->unit          = $request->unit;
        $product_translation->description   = $request->description;
        $product_translation->short_description   = $request->short_description;
        $product_translation->save();

        flash(('Product has been updated successfully'))->success();

        // This will purge the cache of the product from Cloudflare
        PurgeCloudflareCache::dispatch($old_slug)->onQueue('high');
        CacheProducts::dispatch()->onQueue('high');

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();



        // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        // if (file_exists($cachedProductsFilePath)) {
        //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals', 'customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
        //     $jsonData = $items->toJson();
        //     file_put_contents($cachedProductsFilePath, $jsonData);
        // }

        return back();
    }

    public function updatestock(Request $request, $id)
    {
        //dd($request);
        $product                    = Product::findOrFail($id);

        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $product->colors = json_encode($request->colors);
        }else{
            $colors = array();
            $product->colors = json_encode($colors);
        }

        $choice_options = array();

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_'.$no;

                $item['attribute_id'] = $no;

                $data = array();
                foreach ($request[$str] as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
        }

        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        if (!empty($request->choice_no)) {
            $product->attributes = json_encode($request->choice_no);
        }else {
            $product->attributes = json_encode(array());
        }

        $product->choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);


        //combinations start
        $options = array();
        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $colors_active = 1;
            array_push($options, $request->colors);
        }

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                foreach ($request[$name] as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = makeCombinations($options);
        if(count($combinations[0]) > 0){
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination){
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ){
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else{
                        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                            $color_name = Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        }
                        else{
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }

                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if($product_stock == null){
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }
                if(isset($request['price_'.str_replace('.', '_', $str)])) {

                    $product_stock->variant = $str;
                    $product_stock->price = $request['price_'.str_replace('.', '_', $str)];
                    $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                    $product_stock->qty = $request['qty_'.str_replace('.', '_', $str)];
                    $product_stock->image = $request['img_'.str_replace('.', '_', $str)];

                    $product_stock->save();
                }
            }
        }
        else{
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product->id;
            $product_stock->variant     = '';
            $product_stock->price       = $request->unit_price;
            $product_stock->sku         = $request->sku;
            $product_stock->qty         = $request->current_stock;
            $product_stock->save();
        }

        $product->save();

        flash(('Product has been updated successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

        CacheProducts::dispatch()->onQueue('high');

        // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        // if (file_exists($cachedProductsFilePath)) {
        //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
        //     $jsonData = $items->toJson();
        //     file_put_contents($cachedProductsFilePath, $jsonData);
        // }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        $product = Product::with('customFieldsData')->findOrFail($id);
        $product->customFieldsData()->delete();
        $product->product_translations()->delete();
        $product->stocks()->delete();
        $product->reviews()->delete();
        $product->productprices()->delete();
        $product->wishlists()->delete();
        $product->taxes()->delete();
        $product->flash_deal_product()->delete();
        $product->thumbnail_image()->delete();
        $product->merchantProducts()->delete();
        $product->highlighted_items()->delete();
        $product->visits()->delete();

        remove_rewrite_url($product->slug);

        if($product->delete()){
            Cart::where('product_id', $id)->delete();
            DB::commit();
            flash(('Product has been deleted successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

            CacheProducts::dispatch()->onQueue('high');
            return back();
        }
        else{
            DB::rollBack();
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    public function bulk_product_delete(Request $request) {
        if($request->id) {
            foreach ($request->id as $product_id) {
                $this->destroy($product_id);
            }
        }

        return 1;
    }

    /**
     * Duplicates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, $id)
    {
        $product = Product::with('customFieldsData')->find($id);

        if(Auth::user()->id == $product->user_id || Auth::user()->user_type == 'staff'){
            $product_new = $product->replicate();
            $product_new->slug = Product::generateUniqueSlug($product->slug.'-copy');
            $product_new->save();

            // Custom Fields Copy
            foreach ($product->customFieldsData as $key => $customField) {
                $product_custom_field = new ProductsCustomFieldsData;
                $product_custom_field->product_id = $product_new->id;
                $product_custom_field->product_custom_field_id = $customField->product_custom_field_id;
                $product_custom_field->meta_object_id = $customField->meta_object_id;
                $product_custom_field->value = $customField->value;
                $product_custom_field->save();
            }

            foreach ($product->stocks as $key => $stock) {
                $product_stock              = new ProductStock;
                $product_stock->product_id  = $product_new->id;
                $product_stock->variant     = $stock->variant;
                $product_stock->price       = $stock->price;
                $product_stock->sku         = $stock->sku;
                $product_stock->qty         = $stock->qty;
                $product_stock->save();

            }

            flash(('Product has been duplicated successfully'))->success();

            CacheProducts::dispatch()->onQueue('high');

            // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
            // if (file_exists($cachedProductsFilePath)) {
            //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
            //     $jsonData = $items->toJson();
            //     file_put_contents($cachedProductsFilePath, $jsonData);
            // }
            if(Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff'){
              if($request->type == 'In House')
                return redirect()->route('products.admin');
              elseif($request->type == 'Seller')
                return redirect()->route('products.seller');
              elseif($request->type == 'All')
                return redirect()->route('products.all');
            }
            else{
                if (addon_is_activated('seller_subscription')) {
                    $seller = Auth::user()->seller;
                    $seller->remaining_uploads -= 1;
                    $seller->save();
                }
                return redirect()->route('seller.products');
            }
        }
        else{
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    public function get_products_by_brand(Request $request)
    {
        $products = Product::where('brand_id', $request->brand_id)->get();
        return view('partials.product_select', compact('products'));
    }

    public function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        $product->save();
        Cache::forget('todays_deal_products');

        CacheProducts::dispatch()->onQueue('high');

        // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        // if (file_exists($cachedProductsFilePath)) {
        //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
        //     $jsonData = $items->toJson();
        //     file_put_contents($cachedProductsFilePath, $jsonData);
        // }
        return 1;
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;

        if($product->added_by == 'seller' && addon_is_activated('seller_subscription')){
            $seller = $product->user->seller;
            if($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0){
                return 0;
            }
        }

        $product->save();

        CacheProducts::dispatch()->onQueue('high');

        // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        // if (file_exists($cachedProductsFilePath)) {
        //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
        //     $jsonData = $items->toJson();
        //     file_put_contents($cachedProductsFilePath, $jsonData);
        // }
        return 1;
    }

    public function updateProductApproval(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->approved = $request->approved;

        if($product->added_by == 'seller' && addon_is_activated('seller_subscription')){
            $seller = $product->user->seller;
            if($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0){
                return 0;
            }
        }

        $product->save();

        CacheProducts::dispatch()->onQueue('high');

        // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
        // if (file_exists($cachedProductsFilePath)) {
        //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
        //     $jsonData = $items->toJson();
        //     file_put_contents($cachedProductsFilePath, $jsonData);
        // }
        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if($product->save()){
        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

            CacheProducts::dispatch()->onQueue('high');


            // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
            // if (file_exists($cachedProductsFilePath)) {
            //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
            //     $jsonData = $items->toJson();
            //     file_put_contents($cachedProductsFilePath, $jsonData);
            // }
            return 1;
        }
        return 0;
    }
    public function updateSubscription(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->subscription = $request->status;
        if($product->save()){
            dispatch(function () {
                Artisan::call('optimize:clear');
            })->afterResponse();

            CacheProducts::dispatch()->onQueue('high');

            // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
            // if (file_exists($cachedProductsFilePath)) {
            //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
            //     $jsonData = $items->toJson();
            //     file_put_contents($cachedProductsFilePath, $jsonData);
            // }
            return 1;
        }
        return 0;
    }

    public function updateSellerSubscription(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->subscription = $request->status;
        if($product->save()){
            dispatch(function () {
                Artisan::call('optimize:clear');
            })->afterResponse();
            return 1;
        }
        return 0;
    }

    public function updateSellerFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->seller_featured = $request->status;
        if($product->save()){
            CacheProducts::dispatch()->onQueue('high');

            // $cachedProductsFilePath = storage_path('app/public/products/get_cached_products.json');
            // if (file_exists($cachedProductsFilePath)) {
            //     $items = Product::with('stocks', 'productprices', 'flash_deal_product.flash_deals','customFieldsData.productCustomField','customFieldsData.metaObject.items')->where('published', 1)->where('approved', '1')->where('auction_product', 0)->get();
            //     $jsonData = $items->toJson();
            //     file_put_contents($cachedProductsFilePath, $jsonData);
            // }
            return 1;
        }
        return 0;
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $colors_active = 1;
            array_push($options, $request->colors);
        }
        else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();

                foreach (($request[$name] ?? []) as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = makeCombinations($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $colors_active = 1;
            array_push($options, $request->colors);
        }
        else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = makeCombinations($options);
        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product'));
    }

    public function export(Request $request) {
        $fileName = 'products_' . now()->format('Ymd_His') . '.xlsx';

        $filters = $request->all();
        return Excel::download(new ProductsExport($filters), $fileName);
    }

    public function exportOld(Request $request) {
        $currentStatus = $request->status ?? 'published';

        $products = Product::with(['purchaseOrderItems', 'lastPurchaseOrderItem', 'category:id,name'])
            ->where('auction_product',0)
            ->orderBy('created_at', 'desc')
            ->select('id', 'name', 'category_id', 'unit_price', 'discount_start_date', 'discount_end_date', 'discount_type', 'discount');

        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
        }
        if ($request->has('brand_id') && $request->brand_id != null) {
            $products = $products->where('brand_id', $request->brand_id);
        }
        if ($request->has('category_id') && $request->category_id != null) {
            $products = $products->where('category_id', $request->category_id);
        }
        if ($request->search != null){
            $products = $products->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->type != null){
            $var = explode(",", $request->type);
            if (count($var) > 2){
                $products = $products->orderBy($var[0], $var[1]);
            }
        }

        if (!is_null($currentStatus)){
            if($currentStatus == 'published'){
                $products = $products->where('published', 1)->get();
            }elseif($currentStatus == 'unpublished'){
                $products = $products->where('published', 0)->get();
            }elseif($currentStatus == 'outofstock'){
                $products = $products->whereHas('stocks', function ($q) {
                    $q->where('qty', '<=', 0);
                })->get();
            }elseif($currentStatus == 'lowstock'){
                $products = $products->whereHas('stocks', function ($q) {
                    $q->where('qty', '>', 0)->whereRaw('qty <= products.low_stock_quantity');
                })->get();
            }else{
                $products = $products->get();
            }
        }

        $export = new ProductsExport($products);

        return Excel::download($export, 'products.xlsx');
    }

    public function categoryWiseProducts(Request $request){
        $n_cid = [];
        $category_ids = [];
        $n_cid = array_merge($n_cid, CategoryUtility::children_ids($request->category_id));

        if (!empty($n_cid)) {
            $category_ids = array_merge($category_ids, $n_cid);
        }
        $products = Product::whereIn('category_id', $category_ids)->get();
        return array(
            'view' => view('frontend.partials.category_wise_products', compact('products'))->render(),
        );
    }


    public function import(Request $request)
    {
        $file = storage_path('app/public/existing_products.xlsx');
        if(!file_exists($file)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.'
            ], 404);
        }
        try {
            $import = new \App\Imports\ProductsImport();
            Excel::import($import, $file);

            $existingProducts = $import->productNames;

            $alreadyUploadedProducts = array_filter(Storage::disk('public')->exists('rokomari_uploaded_products.txt')
            ? explode(',', Storage::disk('public')->get('rokomari_uploaded_products.txt'))
            : []);
            // dd($alreadyUploadedProducts);

            $products = Product::with('stocks')->published()
                ->when($alreadyUploadedProducts, function ($query) use ($alreadyUploadedProducts) {
                    return $query->whereNotIn('id', $alreadyUploadedProducts);
                })
                ->get();
            dd($products->count(), count($alreadyUploadedProducts));
            return response()->json([
                'success' => true,
                'message' => 'File processed successfully.',
                'count' => count($existingProducts),
                'products' => $existingProducts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchAll(Request $request)
    {
        // $products = Product::published()
        //     ->when(filled($request->search) || filled($request->selected), function ($query) use ($request) {
        //         return $query->where(function ($q) use ($request) {
        //             if (filled($request->search)) {
        //                 $q->where('name', 'LIKE', '%' . $request->search . '%');
        //             }
        //             if (filled($request->selected)) {
        //                 $q->orWhere('id', $request->selected);
        //             }
        //         });
        //     })
        //     ->orderBy('name')
        //     ->limit(100)
        //     ->pluck('name', 'id')
        //     ->toArray();
        $products = Cache::remember('all_products', now()->addHours(3), function () {
            return Product::published()
                ->pluck('name', 'id')
                ->toArray();
        });
        return response()->json($products);
    }
}
