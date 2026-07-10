<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\FlashDeal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\FlashDealProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlashDealController extends Controller
{
    public function index(Request $request)
    {
        $sort_search =null;
        $flash_deals = FlashDeal::withCount('flash_deal_products')->orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $flash_deals = $flash_deals->where('title', 'like', '%'.$sort_search.'%');
        }
        $flash_deals = $flash_deals->paginate(15);
        return view('backend.marketing.flash_deals.index', compact('flash_deals', 'sort_search'));
    }

    public function create()
    {
        return view('backend.marketing.flash_deals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'date_range'  => 'required|string',
            'products'    => 'array',
            'products.*'  => 'exists:products,id',
        ]);

        try {
            DB::transaction(function () use ($request) {

                [$startDate, $endDate] = $this->parseDateRange($request->date_range);

                $flashDeal = FlashDeal::create([
                    'title'            => $request->title,
                    'text_color'       => $request->text_color,
                    'background_color' => $request->background_color,
                    'slug'             => FlashDeal::generateUniqueSlug($request->title),
                    'banner'           => $request->banner,
                    'desktop_banner'   => $request->desktopBanner,
                    'start_date'       => $startDate,
                    'end_date'         => $endDate,
                ]);

                if (empty($request->products)) {
                    return;
                }

                // Remove products from other flash deals
                FlashDealProduct::whereIn('product_id', $request->products)->delete();

                // Fetch products in one query
                $products = Product::whereIn('id', $request->products)
                    ->get()
                    ->keyBy('id');

                $dealProducts = [];

                foreach ($request->products as $index => $productId) {

                    $product = $products->get($productId);
                    if (!$product) {
                        continue;
                    }

                    $currentStock = (int) ($request->current_stocks[$index] ?? 0);
                    $reqQuantity = (int) ($request->quantities[$index] ?? 0);
                    if ($currentStock <= 0 || $reqQuantity <= 0) {
                        continue;
                    }

                    $quantity = $reqQuantity > $currentStock ? $currentStock : $reqQuantity;

                    $discount     = (float) ($request->discounts[$index] ?? 0);
                    $discountType = $request->discount_types[$index] ?? 'amount';

                    $dealProducts[] = [
                        'flash_deal_id' => $flashDeal->id,
                        'product_id'    => $productId,
                        'quantity'      => $quantity,
                        'discount'      => $discount,
                        'discount_type' => $discountType,
                        'sell_quantity' => 0
                    ];

                    // Update product discount
                    $product->discount = $discount;
                    $product->discount_type = $discountType;
                    $product->discount_start_date = $startDate;
                    $product->discount_end_date = $endDate;
                    $product->save();
                }

                if (!empty($dealProducts)) {
                    FlashDealProduct::insert($dealProducts);
                }
            });

            flash('Flash Deal has been inserted successfully')->success();
            return redirect()->route('flash_deals.index');

        } catch (\Throwable $e) {
            Log::error('Flash deal create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            flash('An error occurred while creating the flash deal')->error();
            return back()->withInput();
        }
    }

    public function show($id)
    {
        //
    }

    public function edit(Request $request, $id)
    {
        $flashDeal = FlashDeal::with([
            'flash_deal_products.product',
            'flash_deal_products.product.stocks',
            'flash_deal_products.product.lastPurchaseOrderItem'
        ])->findOrFail($id);
        return view('backend.marketing.flash_deals.edit', compact('flashDeal'));
    }

    public function update($id, Request $request)
    {
        $flashDeal = FlashDeal::with('flash_deal_products')->findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'date_range'  => 'required|string',
            'products'    => 'array',
            'products.*'  => 'exists:products,id',
        ]);

        try {
            DB::transaction(function () use ($request, $flashDeal) {

                [$startDate, $endDate] = $this->parseDateRange($request->date_range);

                $flashDeal->flash_deal_products()->delete();
                $flashDeal->flash_deal_translations()->delete();

                if (($flashDeal->slug == null) || ($flashDeal->title != $request->title)) {
                    $slug = Str::slug($request->title) . '-' . Str::random(5);
                } else {
                    $slug = $flashDeal->slug;
                }

                $flashDeal->update([
                    'title'            => $request->title,
                    'text_color'       => $request->text_color,
                    'background_color' => $request->background_color,
                    'slug'             => $slug,
                    'banner'           => $request->banner,
                    'desktop_banner'   => $request->desktopBanner,
                    'start_date'       => $startDate,
                    'end_date'         => $endDate,
                    'status'           => empty($request->products) ? 0 : $flashDeal->status,
                ]);

                if (empty($request->products)) {
                    return;
                }

                // Remove products from other flash deals
                FlashDealProduct::whereIn('product_id', $request->products)->delete();

                // Fetch products in one query
                $products = Product::whereIn('id', $request->products)
                    ->get()
                    ->keyBy('id');

                $dealProducts = [];

                foreach ($request->products as $index => $productId) {

                    $product = $products->get($productId);
                    if (!$product) {
                        continue;
                    }

                    $currentStock = (int) ($request->current_stocks[$index] ?? 0);
                    $reqQuantity = (int) ($request->quantities[$index] ?? 0);
                    if ($currentStock <= 0 || $reqQuantity <= 0) {
                        continue;
                    }

                    $quantity = $reqQuantity > $currentStock ? $currentStock : $reqQuantity;

                    $discount     = (float) ($request->discounts[$index] ?? 0);
                    $discountType = $request->discount_types[$index] ?? 'amount';

                    $dealProducts[] = [
                        'flash_deal_id' => $flashDeal->id,
                        'product_id'    => $productId,
                        'quantity'      => $quantity,
                        'discount'      => $discount,
                        'discount_type' => $discountType
                    ];

                    // Update product discount
                    $product->discount = $discount;
                    $product->discount_type = $discountType;
                    $product->discount_start_date = $startDate;
                    $product->discount_end_date = $endDate;
                    $product->save();
                }

                if (!empty($dealProducts)) {
                    FlashDealProduct::insert($dealProducts);
                }
            });

            flash('Flash Deal has been updated successfully')->success();
            return redirect()->route('flash_deals.index');

        } catch (\Throwable $e) {
            Log::error('Flash deal update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            flash('An error occurred while updating the flash deal')->error();
            return back()->withInput();
        }
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {

            $flashDeal = FlashDeal::findOrFail($id);

            $productIds = $flashDeal->flash_deal_products()
                ->pluck('product_id')
                ->toArray();

            if ($productIds) {
                reset_products_discount($productIds);
            }

            // Bulk delete relations (single query each)
            $flashDeal->flash_deal_products()->delete();
            $flashDeal->flash_deal_translations()->delete();

            // Delete flash deal itself
            $flashDeal->delete();
        });

        flash('Flash Deal has been deleted successfully')->success();
        return redirect()->route('flash_deals.index');
    }


    public function update_status(Request $request)
    {
        $flash_deal = FlashDeal::withCount('flash_deal_products')->findOrFail($request->id);
        if ($request->status == 1 && $flash_deal->flash_deal_products_count == 0) {
            flash(('Cannot activate flash deal without products'))->error();
            return 0;
        }
        $flash_deal->status = $request->status;
        if($flash_deal->save()){
            update_flash_deal_discount($flash_deal);
            flash(('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function update_featured(Request $request)
    {
        // foreach (FlashDeal::all() as $key => $flash_deal) {
        //     $flash_deal->featured = 0;
        //     $flash_deal->save();
        // }
        $featureType = $request->type ?? 'web'; // 'web' or 'app'
        $flash_deal = FlashDeal::findOrFail($request->id);
        if ($featureType === 'web') {
            $flash_deal->featured = $request->featured;
        } elseif ($featureType === 'app') {
            $flash_deal->app_featured = $request->featured;
        }
        if($flash_deal->save()){
            flash(('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function product_discount(Request $request){
        $product_ids = $request->product_ids;
        return view('backend.marketing.flash_deals.flash_deal_discount', compact('product_ids'));
    }

    public function product_discount_edit(Request $request){
        $product_ids = $request->product_ids;
        $flash_deal_id = $request->flash_deal_id;
        return view('backend.marketing.flash_deals.flash_deal_discount_edit', compact('product_ids', 'flash_deal_id'));
    }

    public function is_exist_in_any_deals($id, Request $request){
        // $flash_deal_product = FlashDealProduct::where('product_id', $id)->first();
        $excludeDealId = $request->get('exclude_deal_id');

        $flash_deal_product = FlashDealProduct::where('product_id', $id)
            ->when($excludeDealId, function($query) use ($excludeDealId) {
                $query->where('flash_deal_id', '!=', $excludeDealId);
            })
            ->with('flash_deals')
            ->first();

        if($flash_deal_product){
            return response()->json(['success' => true, 'exist' => true, 'title' => $flash_deal_product->flash_deals->title]);
        }
        return response()->json(['success' => true, 'exist' => false, 'title' => '']);
    }

    private function parseDateRange(string $range): array
    {
        $dates = explode(' to ', $range);

        if (count($dates) !== 2) {
            throw new \InvalidArgumentException('Invalid date range');
        }

        return [
            strtotime(trim($dates[0])),
            strtotime(trim($dates[1])),
        ];
    }

}
