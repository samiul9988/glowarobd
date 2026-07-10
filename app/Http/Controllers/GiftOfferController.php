<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\GiftOffer;
use Illuminate\Http\Request;
use App\Services\GiftOfferService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\V3\GiftOfferCollection;
use App\Http\Resources\V3\GiftOfferItemCollection;

class GiftOfferController extends Controller
{
    protected GiftOfferService $giftOfferService;

    public function __construct(GiftOfferService $giftOfferService)
    {
        $this->giftOfferService = $giftOfferService;
    }

    /**
     * Get all active and valid gift offers
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;

        $offers = GiftOffer::with(['items.product', 'conditions.product', 'conditions.brand', 'conditions.category'])
            ->active()
            ->valid()
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc');

        if ($request->featured) {
            $offers->featured();
        }

        return new GiftOfferCollection($offers->paginate($limit));
    }

    /**
     * Get gift offers for a specific product
     * Returns offers where this product matches the condition (product-wise, brand-wise, or category-wise)
     */
    public function getOffersForProduct(Request $request, $productId)
    {
        $field = is_numeric($productId) ? 'id' : 'slug';
        $product = Product::with('category', 'brand')
            ->where($field, $productId)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Product not found',
                'data' => []
            ], 404);
        }

        // Use service method instead of duplicating logic
        $offers = $this->giftOfferService->getOffersForProduct($product);

        return new GiftOfferCollection($offers);
    }

    /**
     * Get all available gift offers for cart summary
     * Returns product-wise, brand-wise, category-wise offers based on cart items
     * and cart amount-wise offers based on cart total
     */
    public function getOffersForCart(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');
        // Use service method to get separated offers
        $result = $this->giftOfferService->getSeparatedOffersForCart($userId);

        if ($result['cart_items']->isEmpty()) {
            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => [
                    'product_offers' => [],
                    'cart_amount_offers' => [],
                    'all_offers' => [],
                ],
                'cart_total' => 0,
                'formatted_cart_total' => format_price(0),
            ]);
        }

        // Format response with matched product info for each offer
        $productOffersFormatted = (new GiftOfferCollection($result['product_offers']))->toArray($request)['data'];
        $cartAmountOffersFormatted = (new GiftOfferCollection($result['cart_amount_offers']))->toArray($request)['data'];
        $allOffersFormatted = (new GiftOfferCollection($result['all_offers']))->toArray($request)['data'];

        if (!count($allOffersFormatted)) {
            $userField = Auth::check() ? 'user_id' : 'temp_user_id';
            Cart::withoutGlobalScopes()->withoutRegular()->where($userField, $userId)->delete();
        }
        // Add cart matching info to product offers using service method
        foreach ($productOffersFormatted as &$offer) {
            $offer['matched_cart_items'] = $this->giftOfferService->getMatchedCartItems($offer, $result['cart_items']);
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => [
                'product_offers' => $productOffersFormatted,
                'cart_amount_offers' => $cartAmountOffersFormatted,
                'all_offers' => $allOffersFormatted,
            ],
            'cart_total' => (float) $result['cart_total'],
            'formatted_cart_total' => format_price($result['cart_total']),
        ]);
    }

    /**
     * Get single gift offer details
     */
    public function show(Request $request, $id)
    {
        $field = is_numeric($id) ? 'id' : 'slug';

        $offer = GiftOffer::with(['items.product', 'conditions.product', 'conditions.brand', 'conditions.category'])
            ->active()
            ->valid()
            ->where($field, $id)
            ->get();

        if ($offer->isEmpty()) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Gift offer not found or expired',
                'data' => []
            ], 404);
        }

        return new GiftOfferCollection($offer);
    }

    /**
     * Get free products (items) for a specific gift offer
     */
    public function items(Request $request, $id)
    {
        $field = is_numeric($id) ? 'id' : 'slug';

        $offer = GiftOffer::with(['items.product'])
            ->active()
            ->valid()
            ->where($field, $id)
            ->first();

        if (!$offer) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Gift offer not found or expired',
                'data' => []
            ], 404);
        }

        return new GiftOfferItemCollection($offer->items);
    }

    /**
     * Get current gift selections for a user
     */
    public function getSelections(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');
        $selections = $this->giftOfferService->getFormattedSelections($userId);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $selections,
        ]);
    }

    /**
     * Select a gift item
     */
    public function selectGift(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');

        $request->validate([
            'gift_offer_id' => 'required|integer|exists:gift_offers,id',
            'gift_offer_item_id' => 'required|integer|exists:gift_offer_items,id',
            'quantity' => 'nullable|integer|min:1',
            'variation' => 'nullable|string',
        ]);

        $result = $this->giftOfferService->selectGift(
            $userId,
            $request->gift_offer_id,
            $request->gift_offer_item_id,
            $request->quantity ?? 1,
            $request->variation
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => $result['message'],
            ], 400);
        }

        // Return updated selections
        $selections = $this->giftOfferService->getFormattedSelections($userId);

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => $result['message'],
            'data' => $selections,
        ]);
    }

    /**
     * Remove a gift selection
     */
    public function removeSelection(Request $request, $selectionId)
    {
        $userId = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');

        $result = $this->giftOfferService->removeSelection($userId, (int) $selectionId);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => $result['message'],
            ], 400);
        }

        // Return updated selections
        $selections = $this->giftOfferService->getFormattedSelections($userId);

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => $result['message'],
            'data' => $selections,
        ]);
    }

    /**
     * Clear all gift selections for a user
     */
    public function clearSelections(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');

        $this->giftOfferService->clearSelections($userId);
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'All gift selections cleared successfully',
            'data' => [
                'has_selections' => false,
                'selected_offer_id' => null,
                'selections' => [],
                'total_offer_price' => 0,
                'formatted_total_offer_price' => format_price(0),
            ],
        ]);
    }

    /**
     * Get cart summary with gift offers and selections
     */
    public function getCartWithGifts(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : $request->session()->get('temp_user_id');

        $cartItems = $this->giftOfferService->getCartItems($userId);

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => [
                    'offers' => [],
                    'selections' => $this->giftOfferService->getFormattedSelections($userId),
                    'cart_total' => 0,
                    'formatted_cart_total' => format_price(0),
                    'gift_total' => 0,
                    'formatted_gift_total' => format_price(0),
                    'grand_total' => 0,
                    'formatted_grand_total' => format_price(0),
                ],
            ]);
        }

        // Use service methods
        $eligibleOffers = $this->giftOfferService->getEligibleOffersForCart($userId);
        $cartTotal = $this->giftOfferService->calculateCartTotal($cartItems, $userId);
        $selections = $this->giftOfferService->getFormattedSelections($userId);
        $giftTotal = $this->giftOfferService->calculateSelectionsTotal($userId);
        $grandTotal = $cartTotal + $giftTotal;

        // Format offers
        $offersFormatted = (new GiftOfferCollection($eligibleOffers))->toArray($request)['data'] ?? [];

        // Mark selected offer
        foreach ($offersFormatted as &$offer) {
            $offer['is_selected'] = $selections['selected_offer_id'] == $offer['id'];
            $offer['is_disabled'] = $selections['has_selections'] && !$offer['is_selected'];
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => [
                'offers' => $offersFormatted,
                'selections' => $selections,
                'cart_total' => (float) $cartTotal,
                'formatted_cart_total' => format_price($cartTotal),
                'gift_total' => (float) $giftTotal,
                'formatted_gift_total' => format_price($giftTotal),
                'grand_total' => (float) $grandTotal,
                'formatted_grand_total' => format_price($grandTotal),
            ],
        ]);
    }
}
