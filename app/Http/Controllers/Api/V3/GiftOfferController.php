<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\GiftOfferCollection;
use App\Models\Cart;
use App\Models\GiftOffer;
use App\Models\GiftOfferItem;
use Illuminate\Http\Request;

class GiftOfferController extends Controller
{
    protected function getCarts(Request $request) {
        return Cart::withoutGlobalScopes()
            ->with('product.stocks', 'product.productprices', 'product.brand', 'product.category')
            ->whereNotNull($request->user_field)
            ->where($request->user_field, $request->user_id)
            ->get();
    }

    public function getGiftOffers(Request $request, $userId = null)
    {
        $carts = $this->getCarts($request);
        $regularCarts = $carts->where('cart_type', 'regular');

        if ($regularCarts->isEmpty()) {
            return new GiftOfferCollection(collect());
        }

        $regularCartTotal = $regularCarts->sum(fn($cart) => $cart->price * $cart->quantity);
        $productIds = $regularCarts->pluck('product_id')->unique()->toArray();
        $offers = GiftOffer::with([
            'items' => function ($query) {
                $query->where('available_qty', '>', 0);
            },
            'items.product',
            'conditions.product'
        ])
        ->valid()
        ->whereHas('items', fn ($query) => $query->where('available_qty', '>', 0))
        ->orderBy('min_cart_amount', 'desc')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($offer) use ($regularCartTotal, $productIds) {
            $isValid = false;
            if ($offer->offer_type === 'cart') {
                $isValid = $offer->min_cart_amount <= $regularCartTotal;
            }

            if ($offer->offer_type === 'product') {
                $conditionProductIds = $offer->conditions
                    ->where('condition_type', 'product')
                    ->pluck('item_id')
                    ->toArray();
                $isValid = count(array_intersect($conditionProductIds, $productIds)) > 0;
            }
            $offer->is_valid = $isValid;
            return $offer;
        });

        return new GiftOfferCollection($offers);
    }

    public function addGiftToCart(Request $request)
    {
        $giftOffer = GiftOffer::valid()->find($request->offer_id);
        if (!$giftOffer) {
            return response()->json(['success' => false, 'message' => 'Offer not found.'], 404);
        }

        $giftItem = GiftOfferItem::with('product.stocks')->where('gift_offer_id', $request->offer_id)->find($request->item_id);
        if (!$giftItem) {
            return response()->json(['success' => false, 'message' => 'Gift item not found.'], 404);
        }

        $carts = $this->getCarts($request); // Get all carts with product details

        $regularCarts = $carts->where('cart_type', 'regular');
        $regularCartTotal = $regularCarts->sum(function($cart) {
            return $cart->price * $cart->quantity;
        });

        $otherCarts = $carts->where('cart_type', '!=', 'regular');
        if ($otherCarts->count() && !$otherCarts->where('gift_offer_id', $giftOffer->id)->count()) {
            return response()->json(['success' => false, 'message' => 'You have already added a gift item from another offer to your cart.'], 400);
        }

        if ($otherCarts->count()) {
            if ($otherCarts->count() >= $giftOffer->max_item_per_order) {
                return response()->json(['success' => false, 'message' => 'You have already added the maximum allowed gift items to your cart.'], 400);
            } elseif ($otherCarts->sum('quantity') >= $giftOffer->max_qty_per_order) {
                return response()->json(['success' => false, 'message' => 'You have already added the maximum allowed gift items quantity to your cart.'], 400);
            } elseif ($otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') >= $giftItem->available_qty) {
                return response()->json(['success' => false, 'message' => 'You can only add up to the available quantity of this gift item.'], 400);
            }
        }

        $newQty = $otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') + 1;
        $product = $giftItem->product;
        $productStock = $product->stocks->first();
        $availableQty = $productStock->qty ?? 0;
        if ($availableQty <= 0) {
            $giftItem->available_qty = 0;
            $giftItem->save();
            return response()->json([
                'success' => false,
                'message' => "This gift item is out of stock",
            ], 400);
        } elseif ($newQty > $availableQty) {
            $giftItem->available_qty = max(min($giftItem->available_qty, $availableQty), 0);
            $giftItem->save();
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} items available in stock for this gift item",
            ], 400);
        }

        // dd($regularCarts, $otherCarts);
        $conditionMet = false;
        if ($giftOffer->offer_type === 'cart') {
            if ($regularCartTotal >= $giftOffer->min_cart_amount) {
                $conditionMet = true;
            }
        } else {
            $giftOffer->load('conditions');
            $cartProductIds = $regularCarts->pluck('product_id')->toArray();
            // dd($giftOffer->conditions, $cartProductIds, $regularCarts);
            foreach ($giftOffer->conditions as $condition) {
                if ($condition->condition_type == 'product' && in_array($condition->item_id, $cartProductIds) && $condition->min_qty <= $regularCarts->where('product_id', $condition->item_id)->sum('quantity')) {
                    $conditionMet = true;
                    break;
                }
            }
        }

        if (!$conditionMet) {
            return response()->json(['success' => false, 'message' => 'This offer item is not valid for your cart.'], 400);
        }

        Cart::withoutGlobalScopes()->updateOrCreate([
            $request->user_field => $request->user_id,
            'product_id' => $giftItem->product_id,
            'gift_offer_id' => $giftOffer->id,
            'gift_offer_item_id' => $giftItem->id,
            'cart_type' => 'gift',
        ], [
            'owner_id' => $giftItem->product->user_id,
            'variation' => '',
            'quantity' => $otherCarts->where('product_id', $giftItem->product_id)->sum('quantity') + 1,
            'price' => $giftItem->offer_price,
            'shipping_type' => '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gift item added to cart successfully.',
        ], 200);
    }

    protected function getAvailableQuantity($product, $productStock): int
    {
        $flashDealCheck = check_flash_deal_product($product);

        if ($flashDealCheck) {
            $quantity = $product->flash_deal_product->quantity;
            if ($product->max_qty > 0) {
                $quantity = min($product->max_qty, $quantity);
            }
            return $quantity;
        }

        // Pre-order check
        $preorderCheck = check_preorder_product($product);
        if ($preorderCheck) {
            return $product->preorder_max_qty - preorder_product_count($product);
        }

        $quantity = $productStock->qty ?? 0;
        if ($product->max_qty > 0) {
            $quantity = min($product->max_qty, $quantity);
        }

        return $quantity;
    }
}
