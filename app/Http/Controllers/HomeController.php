<?php

namespace App\Http\Controllers;

use Cookie;
use Carbon\Carbon;
use App\Models\Shop;
use App\Models\User;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\PickupPoint;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AffiliateConfig;
use App\Models\CustomerPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Mail\SecondEmailVerifyMailManager;

class HomeController extends Controller
{
    /**
     * Show the application frontend home.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $new_arrival_products = Cache::rememberForever('new_arrival_products', function () {
            return filter_products(Product::with('thumbnail_image', 'stocks', 'productprices')->availableInStock()->where('published', 1)->latest())->take(6)->get();
        });
        $featured_categories = Cache::rememberForever('featured_categories', function () {
            return Category::where('featured', 1)->orderBy('order_level')->get();
        });

        $todays_deal_products = Cache::rememberForever('todays_deal_products', function () {
            return filter_products(Product::with('thumbnail_image', 'stocks', 'productprices')->availableInStock()->where('published', 1)->where('todays_deal', '1'))->get();
        });

        $flash_deal = FlashDeal::with('flash_deal_products.product.thumbnail_image', 'flash_deal_products.product.stocks', 'flash_deal_products.product.productprices', 'flash_deal_products.product.flash_deal_product.flash_deals')->where('status', 1)->where('featured', 1)->first();

        // Define the file path where you want to store the JSON file
        $languageFilePath = storage_path('app/public/languages/language.json');
        if (!file_exists($languageFilePath)) {
            $items = DB::table('languages')->get();
            $jsonData = $items->toJson();
            Storage::disk('public')->put('languages/language.json', $jsonData);
            // file_put_contents($languageFilePath, $jsonData);
        }

        $currencyFilePath = storage_path('app/public/currencies/currency.json');
        if (!file_exists($currencyFilePath)) {
            $rows = DB::table('currencies')->get();
            $jsonRowData = $rows->toJson();
            Storage::disk('public')->put('currencies/currency.json', $jsonRowData);
            // file_put_contents($currencyFilePath, $jsonRowData);
        }

        $categoryFilePath = storage_path('app/public/categories/category.json');
        if (!file_exists($categoryFilePath)) {
            $rows = Category::all();
            $jsonRowData = $rows->toJson();
            Storage::disk('public')->put('categories/category.json', $jsonRowData);
            // file_put_contents($categoryFilePath, $jsonRowData);
        }

        $shippingMethodFilePath = storage_path('app/public/shipping/methods.json');
        if (!file_exists($shippingMethodFilePath)) {
            $rows = DB::table('shipping_methods')->get();
            $jsonRowData = $rows->toJson();
            Storage::disk('public')->put('shipping/methods.json', $jsonRowData);
            // file_put_contents($shippingMethodFilePath, $jsonRowData);
        }

        $countriesFilePath = storage_path('app/public/countries/countries.json');
        if (!file_exists($countriesFilePath)) {
            $rows = DB::table('countries')->get();
            $jsonRowData = $rows->toJson();
            Storage::disk('public')->put('countries/countries.json', $jsonRowData);
            // file_put_contents($countriesFilePath, $jsonRowData);
        }

        $productFilePath = storage_path('app/public/products/get_cached_products.json');
        if (!file_exists($productFilePath)) {
            $rows = Product::with('reviews', 'category', 'brand', 'stocks', 'flash_deal_product.flash_deals', 'productprices', 'customFieldsData.productCustomField', 'customFieldsData.metaObject:id', 'customFieldsData.metaObject.items', 'thumbnail_image')->get();
            $jsonRowData = $rows->toJson();
            Storage::disk('public')->put('products/get_cached_products.json', $jsonRowData);
            // file_put_contents($categoryFilePath, $jsonRowData);
        }

        $playlistsData = (new \App\Http\Controllers\Api\V3\VideoPlaylistController)
            ->featuredPlaylists(request())
            ->response()
            ->getData(true);

        $playlists = $playlistsData['data'] ?? [];

        return view('frontend.index', compact('featured_categories', 'todays_deal_products', 'new_arrival_products', 'flash_deal', 'playlists'));
    }

    public function login()
    {
        if(Auth::check()){
            return redirect()->route('home');
        }
        return view('frontend.user_login');
    }

    public function registration(Request $request)
    {
        if(Auth::check()){
            return redirect()->route('home');
        }

        if($request->has('referral_code') && addon_is_activated('affiliate_system')) {
            try {
                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }

                // Cookie::queue('referral_code', $request->referral_code, $cookie_minute);
                // $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                // $affiliateController = new AffiliateController;
                // $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            } catch (\Exception $e) {

            }
        }
        return view('frontend.user_registration');
    }

    public function cart_login(Request $request)
    {
        $user = null;
        if($request->get('phone') != null){
            $user = User::whereIn('user_type', ['customer', 'seller'])->where('phone', "+{$request['country_code']}{$request['phone']}")->first();
        }
        elseif($request->get('email') != null){
            $user = User::whereIn('user_type', ['customer', 'seller'])->where('email', $request->email)->first();
        }

        if($user != null){
            if(Hash::check($request->password, $user->password)){
                if($request->has('remember')){
                    auth()->login($user, true);
                }
                else{
                    auth()->login($user, false);
                }
            }
            else {
                flash(('Invalid email or password!'))->warning();
            }
        }
        else{
            flash(('Invalid email or password!'))->warning();
        }
        return back();
    }

    public function updateHeader(Request $request)
    {
        return response()->json([
            'auth' => view('frontend.partials.auth_status')->render(),
            'wishlist_view' => view('frontend.partials.wishlist')->render(),
            'compare_view' => view('frontend.partials.compare')->render(),
            'cart_view' => view('frontend.partials.cart')->render(),
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the customer/seller dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // $route = Route::current();
        // $name = $route->getName();
        // echo($name);
        if(Auth::user()->user_type == 'seller'){
            return view('frontend.user.seller.dashboard');
        }
        elseif(Auth::user()->user_type == 'customer'){
            return view('frontend.user.customer.dashboard');
        }
        elseif(Auth::user()->user_type == 'delivery_boy'){
            return view('delivery_boys.frontend.dashboard');
        }
        else {
            abort(404);
        }
    }

    public function profile(Request $request)
    {
        if(Auth::user()->user_type == 'delivery_boy'){
            return view('delivery_boys.frontend.profile');
        }
        else{
            $gender = $request->gender;
            return view('frontend.user.profile', compact('gender'));
        }
    }

    public function complain_suggestions()
    {
        return view('frontend.user.complain_suggestions');
    }

    public function userProfileUpdate(Request $request)
    {
        // dd($request->all());
        if(env('DEMO_MODE') == 'On'){
            flash(('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        if($request->filled('email') && $user->email != $request->email){
            $user->email = $request->email;
            if(get_setting('email_verification') == 1){
                $user->email_verified_at = null;
            }
        }
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        if(filled($request->date_of_birth)){
            $user->date_of_birth = $request->date_of_birth;
        }

        $user->avatar_original = $request->photo;

        $seller = $user->seller;

        if($seller){
            $seller->cash_on_delivery_status = $request->cash_on_delivery_status;
            $seller->bank_payment_status = $request->bank_payment_status;
            $seller->bank_name = $request->bank_name;
            $seller->bank_acc_name = $request->bank_acc_name;
            $seller->bank_acc_no = $request->bank_acc_no;
            $seller->bank_routing_no = $request->bank_routing_no;

            $seller->save();
        }

        if($user->save()) {
            Cache::forget('birthDayCount_'.now()->format('d M'));
            if(filled($request->skin_type)) {
                $user->metaData()->updateOrCreate(
                    ['key' => 'skin_type'],
                    ['value' => $request->skin_type]
                );
            }
            if(filled($request->skin_concern)) {
                $user->metaData()->updateOrCreate(
                    ['key' => 'skin_concern'],
                    ['value' => json_encode($request->skin_concern)]
                );
            }
        }

        flash(('Your Profile has been updated successfully!'))->success();
        return back();
    }

    public function userPasswordUpdate(Request $request)
    {
        if(env('DEMO_MODE') == 'On'){
            flash(('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        if($request->new_password == null || $request->confirm_password == null){
            flash(('Password fields are required'))->error();
            return back()->withInput();
        }
        if($request->new_password != $request->confirm_password){
            flash(('Password does not match'))->error();
            return back()->withInput();
        }

        $user = Auth::user();
        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        flash(('Password has been updated successfully!'))->success();
        return back();
    }

    public function flash_deal_details($slug)
    {
        $flash_deal = FlashDeal::with('flash_deal_products.product.thumbnail_image', 'flash_deal_products.product.stocks', 'flash_deal_products.product.productprices')->where('slug', $slug)->first();
        if($flash_deal != null)
            return view('frontend.flash_deal_details', compact('flash_deal'));
        else {
            abort(404);
        }
    }

    public function load_featured_section(){
        return view('frontend.partials.featured_products_section_api');
    }

    public function load_best_selling_section(){
        return view('frontend.partials.best_selling_section');
    }

    public function load_auction_products_section(){
        if(!addon_is_activated('auction')){
            return;
        }
        return view('auction.frontend.auction_products_section');
    }

    public function load_home_categories_section(Request $request)
    {
        $agent = app('agent');

        $collection_designs = Cache::remember('collection_designs', Carbon::now()->addDay(), function () {
            return \App\Models\CollectionDesign::all();
        });

        $home_categories = json_decode(get_setting('home_categories', ''), true);

        if(is_null( $home_categories )) {
            return '';
        }

        $cacheKey = 'home_categories_section' . ($agent->isMobile() ? '_mobile' : '');
        return Cache::remember($cacheKey, Carbon::now()->addHours(5), function() use ($collection_designs) {
            return view('frontend.partials.home_categories_section', compact('collection_designs'))->render();
        });
    }

    public function load_best_sellers_section(){
        return view('frontend.partials.best_sellers_section');
    }

    public function trackOrder(Request $request)
    {
        if($request->has('order_code')){
            $order = Order::where('code', $request->order_code)->first();
            if($order != null){
                return view('frontend.track_order', compact('order'));
            }
        }
        return view('frontend.track_order');
    }

    public function product(Request $request, $slug)
    {
        $route = $request->route();
        // dd($route->gatherMiddleware());
        $detailedProduct  = Product::with([
            'stocks',
            'latestStock',
            'reviews' => function ($query) {
                $query->whereNotNull('comment');
            },
            'reviews.user:id,name',
            'category',
            'brand',
            'stocks',
            'user',
            'user.shop',
            'flash_deal_product.flash_deals',
            'productprices',
            'customFieldsData.productCustomField',
            'customFieldsData.metaObject:id',
            'customFieldsData.metaObject.items',
            'videos'
        ])
        ->where('auction_product', 0)
        ->where('slug', $slug)
        ->where('approved', 1)
        ->first();

        // dd($detailedProduct->stocks->first(), $detailedProduct->latestStock);
        // dd($detailedProduct->id);

        // dd($detailedProduct);
        if($detailedProduct != null && $detailedProduct->published){
            if($request->has('product_referral_code') && addon_is_activated('affiliate_system')) {

                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }
                // Cookie::queue('product_referral_code', $request->product_referral_code, $cookie_minute);
                // Cookie::queue('referred_product_id', $detailedProduct->id, $cookie_minute);

                // $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                // $affiliateController = new AffiliateController;
                // $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            }
            // Push product ID to session
            session()->push('products.recently_viewed', $detailedProduct->id);
            // $topSellingProducts = Cache::get('app.best_selling_products') ?? Http::get(route('api.products.best-seller'), [
            //     'limit' => 20
            // ])->body();

            // if($topSellingProducts){
            //     $topSellingProducts = $topSellingProducts;
            // }

            $productInfoToCopy = get_copy_content($detailedProduct);
            // dd($productInfoToCopy);

            // dd($detailedProduct);
            $customFieldsData = $detailedProduct->customFieldsData?->mapWithKeys(function ($field) {
                return [
                    $field->productCustomField->slug => [
                        'type' => $field->productCustomField->type,
                        'value' => $field->metaObject
                        ? $field->metaObject->items->whereIn('id', json_decode($field->value, true))->values()->toArray()
                        : json_decode($field->value, true)
                    ]
                ];
            })->toArray();

            $relatedVideos = $detailedProduct->videos?->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'slug' => $video->slug,
                    'description' => $video->description,
                    'thumbnail' => $video->thumbnail ? uploaded_asset($video->thumbnail) : null,
                    'video_url' => $video->attachment ? uploaded_asset($video->attachment) : $video->video_url,
                    'views_count' => (int) $video->views,
                    'type' => $video->type,
                ];
            })->toArray();

            // dd($customFieldsData);
            if ($detailedProduct->digital == 1) {
                return view('frontend.digital_product_details', compact('detailedProduct', 'productInfoToCopy', 'customFieldsData'));
            } else {
                return view('frontend.product_details', compact('detailedProduct', 'productInfoToCopy', 'customFieldsData', 'relatedVideos'));
            }
        }
        abort(404);
    }

    public function isCommentable($id)
    {
        $product = Product::select('id')->find($id);
        if(!$product) {
            return '';
        }
        $commentable = false;
        $whoCanPostReview = get_setting('who_can_post_reviews');
        if($whoCanPostReview == 'everyone'){
            $commentable = true;
        }elseif($whoCanPostReview == 'all_registered_buyers'){
            $customer = auth()->check() ? auth()->user()->load('orders.products') : null;
            if(isset($customer)){
                $orders = $customer->orders;
            }else{
                $orders = null;
            }
            if(isset($orders)){
                $hasPurchased = $orders->filter(function ($order) use ($product) {
                    return $order->products->contains('id', $product['id']) && $order->delivery_status === 'delivered';
                })->isNotEmpty();

                if ($hasPurchased) {
                    $commentable = true;
                }
            }
        }elseif($whoCanPostReview == 'all_registered_customers'){
            if(auth()->check()){
                $commentable = true;
            }
        }
        $canUploadImage = (get_setting('reviews_image_upload') == 'on') ? true : false;
        if(get_setting('reviews_image_upload_only_user') == 'on'){
            if(auth()->check()){
                $canUploadImage = true;
            }else{
                $canUploadImage = false;
            }
        }

        if($commentable){
            return view('frontend.partials.product_review_form', compact('product', 'canUploadImage'))->render();
        }

        return '';
    }

    public function relatedProduct($id)
    {
        $product = Product::select('id', 'category_id')->find($id);
        if(!$product) {
            return '';
        }
        $relatedProducts = filter_products(Product::with('thumbnail_image', 'stocks','productprices')->where('category_id', $product['category_id'])->where('id', '!=', $product['id']))->orderByRaw("RAND()")->limit(6)->get();
        if($relatedProducts->isNotEmpty()){
            return view('frontend.partials.related_products', [
                'relatedProducts' => $relatedProducts,
            ])->render();
        }
        return '';
    }

    public function shop($slug)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if($shop!=null){
            $seller = Seller::where('user_id', $shop->user_id)->first();
            if ($seller->verification_status != 0){
                return view('frontend.seller_shop', compact('shop'));
            }
            else{
                return view('frontend.seller_shop_without_verification', compact('shop', 'seller'));
            }
        }
        abort(404);
    }

    public function filter_shop($slug, $type)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if($shop!=null && $type != null){
            return view('frontend.seller_shop', compact('shop', 'type'));
        }
        abort(404);
    }

    public function all_categories(Request $request)
    {
        //$categories = Category::where('level', 0)->orderBy('name', 'asc')->get();
        $categories = Category::where('level', 0)->orderBy('order_level', 'asc')->get();
        return view('frontend.all_category', compact('categories'));
    }
    public function all_brands(Request $request)
    {
        $categories = Category::all();
        return view('frontend.all_brand', compact('categories'));
    }

    public function show_product_upload_form(Request $request)
    {
        $seller = Auth::user()->seller;
        if(addon_is_activated('seller_subscription')){
            if($seller->seller_package && $seller->seller_package->product_upload_limit > $seller->user->products()->count()){
                $categories = Category::where('parent_id', 0)
                    ->where('digital', 0)
                    ->with('childrenCategories')
                    ->get();
                return view('frontend.user.seller.product_upload', compact('categories'));
            }
            else {
                flash(('Upload limit has been reached. Please upgrade your package.'))->warning();
                return back();
            }
        }
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('frontend.user.seller.product_upload', compact('categories'));
    }

    public function show_product_edit_form(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('frontend.user.seller.product_edit', compact('product', 'categories', 'tags', 'lang'));
    }

    public function seller_product_list(Request $request)
    {
        $search = null;
        $products = Product::where('user_id', Auth::user()->id)->where('digital', 0)->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%'.$search.'%');
        }
        $products = $products->paginate(10);
        return view('frontend.user.seller.products', compact('products', 'search'));
    }

    public function home_settings(Request $request)
    {
        return view('home_settings.index');
    }

    public function top_10_settings(Request $request)
    {
        foreach (Category::all() as $key => $category) {
            if(is_array($request->top_categories) && in_array($category->id, $request->top_categories)){
                $category->top = 1;
                $category->save();
            }
            else{
                $category->top = 0;
                $category->save();
            }
        }

        foreach (Brand::all() as $key => $brand) {
            if(is_array($request->top_brands) && in_array($brand->id, $request->top_brands)){
                $brand->top = 1;
                $brand->save();
            }
            else{
                $brand->top = 0;
                $brand->save();
            }
        }

        flash(('Top 10 categories and brands have been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    public function variant_price(Request $request)
    {
        $product = Product::with('category', 'brand', 'stocks', 'productprices', 'flash_deal_product.flash_deals')->find($request->id);
        $flash_deal_check = check_flash_deal_product(collect($product));
        $str = '';
        $quantity = 0;
        $tax = 0;
        $max_limit = 0;

        if($request->has('color')){
            $str = $request['color'];
        }

        if(json_decode($product->choice_options) != null){
            foreach (json_decode($product->choice_options) as $key => $choice) {
                if($str != null){
                    $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
                else{
                    $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
            }
        }

        $product_stock = collect($product->stocks)->where('variant', $str)->first();
        $price = $product_stock->price;
        $group_price = $product_stock->price;

        /* if($product->wholesale_product){
            $wholesalePrice = collect($product_stock->wholesalePrices)->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
            if($wholesalePrice){
                $price = $wholesalePrice->price;
                $group_price = $wholesalePrice->price;
            }
        } */

        if(count(collect($product->productprices))>0){
            $productprices = collect($product->productprices)->where('start_qty', '<=', $request->quantity)->where('end_qty', '>=', $request->quantity)->first();
            if($productprices){
                $price = $productprices->price;
            }
        }

        if($flash_deal_check){
            $quantity = $product->flash_deal_product->quantity;
            if($product->max_qty>0){
                if($product->max_qty <= $product->flash_deal_product->quantity){
                    $max_limit = $product->max_qty;
                }else{
                    $max_limit = $product->flash_deal_product->quantity;
                }
            }else{
                $max_limit = $product->flash_deal_product->quantity;
            }
        }else{
            $quantity = $product_stock->qty;
            if($product->max_qty>0){
                if($product->max_qty <= $product_stock->qty){
                    $max_limit = $product->max_qty;
                }else{
                    $max_limit = $product_stock->qty;
                }
            }else{
                $max_limit = $product_stock->qty;
            }
        }

        //return $max_limit;
        if($quantity >= 1 && $product->min_qty <= $quantity){
            $in_stock = 1;
        }else{
            if($product->allow_stock_out_purchases == 1){
                $in_stock = 1;
                $max_limit = $product->max_qty > 0 ? $product->max_qty : 10;
            }else{
                $in_stock = 0;
            }
        }

        //Product Stock Visibility
        if($product->stock_visibility_state == 'text') {
            if($quantity >= 1 && $product->min_qty < $quantity){
                $quantity = ('In Stock');
            }else{
                $quantity = ('Out Of Stock');
            }
        }

        //discount calculation
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        $minprice = getMinimumPriceByVariant($product, $product_stock, 'web', $request->quantity, $this->currentlyAuthenticatedUser);
        /* if(isset(Auth::user()->id)){
            if(Auth::user()->customeringroup){
                $discount_status = Auth::user()->customeringroup->group->discount_status;
                $start_date = Auth::user()->customeringroup->group->start_date;
                $end_date = Auth::user()->customeringroup->group->end_date;
                $cur_date = strtotime(date('Y-m-d H:i:s'));
                if($discount_status==1 && $cur_date >= $start_date && $cur_date <= $end_date){
                    if (Auth::user()->customeringroup->group->discount_type == 'percent') {
                        $group_price -= ($group_price * Auth::user()->customeringroup->group->discount) / 100;
                    } elseif (Auth::user()->customeringroup->group->discount_type == 'amount') {
                        $group_price -= Auth::user()->customeringroup->group->discount;
                    }
                    if ($discount_applicable) {
                        if($price < $group_price){
                            $price = $price;
                            $minprice = $minprice;
                        }else{
                            $price = $group_price;
                            $minprice = $group_price;
                        }
                    }else{
                        $price = $group_price;
                        $minprice = $group_price;
                    }
                }
            }
        } */

        // taxes
        foreach ($product->taxes as $product_tax) {
            if($product_tax->tax_type == 'percent'){
                $tax += ($price * $product_tax->tax) / 100;
            }
            elseif($product_tax->tax_type == 'amount'){
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;
        $minprice += $tax;

        //Pre-order
        $preorder_check = check_preorder_product($product);
        $preorder_max = $product->preorder_max_qty - preorder_product_count($product);
        return array(
            'min_price' => single_price($minprice*$request->quantity),
            'price' => single_price($price*$request->quantity),
            'quantity' => $quantity,
            'digital' => $product->digital,
            'variation' => $str,
            'max_limit' => $max_limit,
            'unit_price' => single_price($price),
            'min_unit_price' => $minprice,
            'in_stock' => $in_stock,
            'is_preorder' => $preorder_check,
            'preorder_max' => $preorder_max
        );
    }

    public function sellerpolicy(){
        return view("frontend.policies.sellerpolicy");
    }

    public function returnpolicy(){
        return view("frontend.policies.returnpolicy");
    }

    public function supportpolicy(){
        return view("frontend.policies.supportpolicy");
    }

    public function terms(Request $request){
        $isApp = $request->has('isApp') ? true : false;
        return view("frontend.policies.terms", compact('isApp'));
    }

    public function privacypolicy(Request $request){
        $isApp = $request->has('isApp') ? true : false;
        return view("frontend.policies.privacypolicy", compact('isApp'));
    }
    public function aboutus(Request $request){
        $isApp = $request->has('isApp') ? true : false;
        return view("frontend.about-us", compact('isApp'));
    }

    public function get_pick_up_points(Request $request)
    {
        $pick_up_points = Cache::remember('pick_up_points', now()->addDay(), function () {
            return PickupPoint::all();
        });
        return view('frontend.partials.pick_up_points', compact('pick_up_points'));
    }

    public function get_category_items(Request $request){
        $category = Cache::remember('category_uni' . $request->id, 86400, function () use ($request) {
            return Category::findOrFail($request->id);
        });
        return view('frontend.partials.category_elements', compact('category'));
    }

    public function premium_package_index()
    {
        $customer_packages = CustomerPackage::all();
        return view('frontend.user.customer_packages_lists', compact('customer_packages'));
    }

    public function seller_digital_product_list(Request $request)
    {
        $products = Product::where('user_id', Auth::user()->id)->where('digital', 1)->orderBy('created_at', 'desc')->paginate(10);
        return view('frontend.user.seller.digitalproducts.products', compact('products'));
    }
    public function show_digital_product_upload_form(Request $request)
    {
        $seller = Auth::user()->seller;
        if(addon_is_activated('seller_subscription')){
            if($seller->seller_package && $seller->seller_package->product_upload_limit > $seller->user->products()->count()){
                $categories = Category::where('digital', 1)->get();
                return view('frontend.user.seller.digitalproducts.product_upload', compact('categories'));
            }
            else {
                flash(('Upload limit has been reached. Please upgrade your package.'))->warning();
                return back();
            }
        }
        $categories = Category::where('digital', 1)->get();
        return view('frontend.user.seller.digitalproducts.product_upload', compact('categories'));
    }

    public function show_digital_product_edit_form(Request $request, $id)
    {
        $categories = Category::where('digital', 1)->get();
        $lang = $request->lang;
        $product = Product::find($id);
        return view('frontend.user.seller.digitalproducts.product_edit', compact('categories', 'product', 'lang'));
    }

    // Ajax call
    public function new_verify(Request $request)
    {
        $email = $request->email;
        if(isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = 'Email already exists!';
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }


    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        if(isUnique($email)) {
            $this->send_email_change_verification_mail($request, $email);
            flash(('A verification mail has been sent to the mail you provided us with.'))->success();
            return back();
        }

        flash(('Email already exists!'))->warning();
        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $response['status'] = 0;
        $response['message'] = 'Unknown';

        $verification_code = Str::random(32);

        $array['subject'] = 'Email Verification';
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Verify your account';
        $array['link'] = route('email_change.callback').'?new_email_verificiation_code='.$verification_code.'&email='.$email;
        $array['sender'] = Auth::user()->name;
        $array['details'] = "Email Second";

        $user = Auth::user();
        $user->new_email_verificiation_code = $verification_code;
        $user->save();

        try {
            Mail::to($email)->queue(new SecondEmailVerifyMailManager($array));

            $response['status'] = 1;
            $response['message'] = ("Your verification mail has been Sent to your email.");

        } catch (\Exception $e) {
            // return $e->getMessage();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request){
        if($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if($user != null) {

                $user->email = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();

                auth()->login($user, true);

                flash(('Email Changed successfully'))->success();
                return redirect()->route('dashboard');
            }
        }

        flash(('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');

    }

    // public function reset_password_with_code(Request $request){
    //     if (($user = User::where('email', $request->email)->where('verification_code', $request->code)->first()) != null) {
    //         if($request->password == $request->password_confirmation){
    //             $user->password = Hash::make($request->password);
    //             $user->email_verified_at = date('Y-m-d h:m:s');
    //             $user->save();
    //             event(new PasswordReset($user));
    //             auth()->login($user, true);

    //             flash(('Password updated successfully'))->success();

    //             if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
    //             {
    //                 return redirect()->route('admin.dashboard');
    //             }
    //             return redirect()->route('home');
    //         }
    //         else {
    //             flash("Password and confirm password didn't match")->warning();
    //             return redirect()->route('password.request');
    //         }
    //     }
    //     else {
    //         flash("Verification code mismatch")->error();
    //         return redirect()->route('password.request');
    //     }
    // }

    public function reset_password_with_code(Request $request){
        $user = User::where('email', $request->email)->where('verification_code', $request->code)->first();
        if ($user) {
            $user->email_verified_at = date('Y-m-d h:m:s');
            $user->save();
            auth()->login($user, true);
            flash(('Account Verified. Don\'t forget to update your password'))->success();

            session()->forget('user_email_for_password_reset');
            if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
            {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('profile');
        }
        else {
            flash(("Verification code mismatch"))->error();
            return redirect()->route('password.request');
        }
    }


    public function all_flash_deals() {
        $today = strtotime(date('Y-m-d H:i:s'));
        $data['all_flash_deals'] = FlashDeal::where('status', 1)
                ->where('start_date', "<=", $today)
                ->where('end_date', ">", $today)
                ->orderBy('created_at', 'desc')
                ->get();

        return view("frontend.flash_deal.all_flash_deal_list", $data);
    }

    public function all_seller(Request $request) {
        $shops = Shop::whereIn('user_id', verified_sellers_id())
                ->paginate(15);

        return view('frontend.shop_listing', compact('shops'));
    }

    public function all_coupons(Request $request) {
        $coupons = Coupon::valid()->paginate(15);
        return view('frontend.coupons', compact('coupons'));
    }

    public function inhouse_products(Request $request) {
        $products = filter_products(Product::where('added_by', 'admin'))->with('taxes')->paginate(12)->appends(request()->query());
        return view('frontend.inhouse_products', compact('products'));
    }

    public function featuredLoadMoreProducts(){
        $response = Http::get(url('/') . '/api/v2/products?page=2&orderby=rand&limit=30');
        $products = $response->json()['data'];
        // $products = Product::where('published', 1)->orderByRaw('RAND()')->limit(30)->get();
        return view('frontend.partials.featured_load_more_products', compact('products'));
    }

    public function wellKnownFile($fileName) {
        if(!str_contains($fileName, '.json')) {
            $fileName = $fileName . '.json';
        }
        $path = storage_path('app/public/well-known/' . $fileName);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }

    public function csrfToken()
    {
        return csrf_token();
    }
}
