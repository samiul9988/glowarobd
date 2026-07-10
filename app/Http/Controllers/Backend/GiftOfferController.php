<?php

namespace App\Http\Controllers\Backend;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\GiftOffer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GiftOfferItem;
use App\Models\GiftOfferCondition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class GiftOfferController extends Controller
{
    public function index(Request $request)
    {
        $sort_search = null;
        $offer_type = null;

        $giftOffers = GiftOffer::withCount(['items', 'conditions'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $sort_search = $request->search;
            $giftOffers = $giftOffers->where('title', 'like', '%' . $sort_search . '%');
        }

        if ($request->has('offer_type') && $request->offer_type != '') {
            $offer_type = $request->offer_type;
            $giftOffers = $giftOffers->where('offer_type', $offer_type);
        }

        $giftOffers = $giftOffers->paginate(15);

        return view('backend.marketing.gift_offers.index', compact('giftOffers', 'sort_search', 'offer_type'));
    }

    public function create()
    {
        return view('backend.marketing.gift_offers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'offer_type' => 'required|in:product,cart',
            'date_range' => 'required|string',
            'max_item_per_order' => 'required|integer|min:1',
            'max_qty_per_order' => 'required|integer|min:1',
            'gift_products' => 'required|array|min:1',
            'gift_products.*' => 'exists:products,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                [$startDate, $endDate] = $this->parseDateRange($request->date_range);

                $this->validateProductStocks($request->gift_products, $request->gift_quantities);

                // Create Gift Offer
                $giftOffer = GiftOffer::create([
                    'title' => $request->title,
                    'slug' => Str::slug($request->title) . '-' . Str::random(5),
                    'description' => $request->description,
                    'offer_type' => $request->offer_type,
                    'min_cart_amount' => $request->offer_type === 'cart' ? ($request->min_cart_amount ?? 0) : 0,
                    'max_qty_per_order' => $request->max_qty_per_order,
                    'max_item_per_order' => $request->max_item_per_order,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 0, // Start as inactive
                    'created_by' => Auth::id(),
                ]);

                // Create conditions based on offer type
                $this->createConditions($giftOffer, $request);

                // Create gift items (free products)
                $this->createGiftItems($giftOffer, $request);
            });

            flash('Gift Offer has been created successfully')->success();
            return redirect()->route('admin.gift_offers.index');

        } catch (\Throwable $e) {
            Log::error('Gift offer create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            flash($e->getMessage())->error();
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $giftOffer = GiftOffer::with(['conditions', 'items.product'])->findOrFail($id);

        return view('backend.marketing.gift_offers.edit', compact('giftOffer'));
    }

    public function update(Request $request, $id)
    {
        $giftOffer = GiftOffer::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'offer_type' => 'required|in:product,cart',
            'date_range' => 'required|string',
            'max_item_per_order' => 'required|integer|min:1',
            'max_qty_per_order' => 'required|integer|min:1',
            'gift_products' => 'required|array|min:1',
            'gift_products.*' => 'exists:products,id',
        ]);

        try {
            DB::transaction(function () use ($request, $giftOffer) {
                [$startDate, $endDate] = $this->parseDateRange($request->date_range);

                $this->validateProductStocks($request->gift_products, $request->gift_quantities);

                // Delete existing conditions and items
                $giftOffer->conditions()->delete();
                $giftOffer->items()->delete();

                // Update Gift Offer
                $giftOffer->update([
                    'title' => $request->title,
                    'slug' => Str::slug($request->title) . '-' . Str::random(5),
                    'description' => $request->description,
                    'offer_type' => $request->offer_type,
                    'min_cart_amount' => $request->offer_type === 'cart' ? ($request->min_cart_amount ?? 0) : 0,
                    'max_cart_amount' => $request->offer_type === 'cart' ? $request->max_cart_amount : null,
                    'max_item_per_order' => $request->max_item_per_order,
                    'max_qty_per_order' => $request->max_qty_per_order,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => empty($request->gift_products) ? 0 : $giftOffer->status,
                    'updated_by' => Auth::id(),
                ]);

                // Recreate conditions based on offer type
                $this->createConditions($giftOffer, $request);

                // Recreate gift items (free products)
                $this->createGiftItems($giftOffer, $request);
            });

            flash('Gift Offer has been updated successfully')->success();
            return redirect()->route('admin.gift_offers.index');

        } catch (\Throwable $e) {
            Log::error('Gift offer update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            flash($e->getMessage())->error();
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $giftOffer = GiftOffer::findOrFail($id);

                // Delete related records (cascade should handle this, but being explicit)
                $giftOffer->conditions()->delete();
                $giftOffer->items()->delete();

                $giftOffer->delete();
            });

            flash('Gift Offer has been deleted successfully')->success();
        } catch (\Throwable $e) {
            Log::error('Gift offer delete failed', [
                'error' => $e->getMessage(),
            ]);
            flash('An error occurred while deleting the gift offer')->error();
        }

        return redirect()->route('admin.gift_offers.index');
    }

    public function updateStatus(Request $request)
    {
        $giftOffer = GiftOffer::withCount('items')->findOrFail($request->id);

        if ($request->status == 1 && $giftOffer->items_count == 0) {
            flash('Cannot activate gift offer without gift products')->error();
            return 0;
        }

        $giftOffer->status = $request->status;

        if ($giftOffer->save()) {
            flash('Gift offer status updated successfully')->success();
            return 1;
        }

        return 0;
    }

    private function createConditions(GiftOffer $giftOffer, Request $request): void
    {
        if ($giftOffer->offer_type === 'cart') {
            // No conditions needed for cart_amount type
            return;
        }

        $conditions = [];

        dd($request->all());
        if ($giftOffer->offer_type === 'product') {
            if ($request->has('condition_products') && is_array($request->condition_products)) {
                foreach ($request->condition_products as $index => $productId) {
                    $conditions[] = [
                        'gift_offer_id' => $giftOffer->id,
                        'condition_type' => 'product',
                        'item_id' => $productId,
                        'min_qty' => $request->condition_min_qty[$index] ?? 1,
                    ];
                }
            }
        }

        if (!empty($conditions)) {
            GiftOfferCondition::insert($conditions);
        }
    }

    private function createGiftItems(GiftOffer $giftOffer, Request $request): void
    {
        if (!$request->has('gift_products') || !is_array($request->gift_products)) {
            return;
        }

        $giftItems = [];

        foreach ($request->gift_products as $index => $productId) {
            if (!isset($request->gift_quantities[$index]) || $request->gift_quantities[$index] <= 0) {
                continue; // Skip if quantity is not set or invalid
            }
            $giftItems[] = [
                'gift_offer_id' => $giftOffer->id,
                'product_id' => $productId,
                'available_qty' => $request->gift_quantities[$index] ?? 0,
                'offer_price' => $request->gift_offer_prices[$index] ?? 0,
                'used_qty' => 0,
            ];
        }

        if (!empty($giftItems)) {
            GiftOfferItem::insert($giftItems);
        }
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

    private function validateProductStocks(array $ids, array $quantities)
    {
        $products = Product::with('stocks')->whereIntegerInRaw('id', $ids)->get();

        foreach ($products as $product) {
            $currentStock = $product->stocks?->first()?->qty ?? 0;
            if ($currentStock <= 0) {
                throw new \Exception("Product '{$product->name}' is out of stock.");
            } elseif (isset($quantities[array_search($product->id, $ids)]) && $quantities[array_search($product->id, $ids)] > $currentStock) {
                throw new \Exception("Requested quantity for product '{$product->name}' exceeds available stock.");
            }
        }
    }
}
