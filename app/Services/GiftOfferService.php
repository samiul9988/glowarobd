<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\GiftOffer;
use App\Models\GiftOfferItem;
use App\Models\OrderGiftOffer;
use App\Models\CartGiftSelection;
use App\Models\OrderGiftOfferItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GiftOfferService
{
    /**
     * Get user field based on user ID format
     */
    public function getUserField($userId): string
    {
        if (Str::startsWith($userId, 'tmp')) {
            return 'temp_user_id';
        }

        return ctype_digit((string) $userId) ? 'user_id' : 'temp_user_id';
    }

    /**
     * Get category hierarchy (category and all parent categories)
     */
    public function getCategoryHierarchy($categoryId): array
    {
        if (!$categoryId) {
            return [];
        }

        $ids = [$categoryId];
        $category = Category::find($categoryId);

        while ($category && $category->parent_id) {
            $ids[] = $category->parent_id;
            $category = Category::find($category->parent_id);
        }

        return array_unique($ids);
    }

    /**
     * Get eligible offers for a specific product
     */
    public function getOffersForProduct(Product $product)
    {
        $categoryIds = $this->getCategoryHierarchy($product->category_id);

        return GiftOffer::with(['items.product', 'conditions.product'])
            ->active()
            ->valid()
            ->where(function ($query) use ($product, $categoryIds) {
                // Product-wise offers
                $query->whereHas('conditions', function ($q) use ($product) {
                    $q->where('condition_type', 'product')
                      ->where('item_id', $product->id);
                });
                // Brand-wise offers
                // ->orWhereHas('conditions', function ($q) use ($product) {
                //     $q->where('condition_type', 'brand')
                //       ->where('item_id', $product->brand_id);
                // })
                // // Category-wise offers (including parent categories)
                // ->orWhereHas('conditions', function ($q) use ($categoryIds) {
                //     $q->where('condition_type', 'category')
                //       ->whereIn('item_id', $categoryIds);
                // });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get matched cart items for an offer
     */
    public function getMatchedCartItems(array $offer, $cartItems): array
    {
        $matched = [];

        if (!isset($offer['conditions'])) {
            return $matched;
        }

        foreach ($offer['conditions'] as $condition) {
            foreach ($cartItems as $cartItem) {
                if (!$cartItem->product) {
                    continue;
                }

                $isMatch = false;

                if ($condition['condition_type'] === 'product' && isset($condition['product_id'])) {
                    $isMatch = $cartItem->product_id == $condition['product_id'];
                }
                // elseif ($condition['condition_type'] === 'brand' && isset($condition['brand_id'])) {
                //     $isMatch = $cartItem->product->brand_id == $condition['brand_id'];
                // } elseif ($condition['condition_type'] === 'category' && isset($condition['category_id'])) {
                //     $categoryIds = $this->getCategoryHierarchy($cartItem->product->category_id);
                //     $isMatch = in_array($condition['category_id'], $categoryIds);
                // }

                if ($isMatch) {
                    $matched[] = [
                        'cart_id' => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'quantity' => $cartItem->quantity,
                    ];
                }
            }
        }

        return collect($matched)->unique('cart_id')->values()->toArray();
    }

    /**
     * Get cart items for a user
     */
    public function getCartItems($userId)
    {
        $userField = $this->getUserField($userId);
        return Cart::where($userField, $userId)
            ->with('product.category', 'product.brand')
            ->get();
    }

    /**
     * Calculate cart total
     */
    public function calculateCartTotal($cartItems, $userId = null): float
    {
        $total = 0;
        $userInfo = null;

        if ($userId && !Str::startsWith($userId, 'tmp')) {
            $userInfo = User::with('customeringroup.group')->find($userId);
        }

        foreach ($cartItems as $cartItem) {
            $product = Product::find($cartItem->product_id);
            $productStock = $product->stocks->where('variant', $cartItem->variation)->first();
            $price = getMinimumPriceByVariant($product, $productStock, 'app', $cartItem->quantity, $userInfo);
            $total += ($price + $cartItem->tax) * $cartItem->quantity;
        }

        return $total;
    }

    /**
     * Get all IDs from cart items (products, brands, categories)
     */
    public function getCartIdentifiers($cartItems): array
    {
        $productIds = $cartItems->pluck('product_id')->unique()->toArray();
        $brandIds = $cartItems->pluck('product.brand_id')->filter()->unique()->toArray();
        $categoryIds = [];

        foreach ($cartItems as $cartItem) {
            if ($cartItem->product && $cartItem->product->category_id) {
                $hierarchy = $this->getCategoryHierarchy($cartItem->product->category_id);
                $categoryIds = array_merge($categoryIds, $hierarchy);
            }
        }

        return [
            'product_ids' => $productIds,
            'brand_ids' => $brandIds,
            'category_ids' => array_unique($categoryIds),
        ];
    }

    /**
     * Get eligible offers for cart
     */
    public function getEligibleOffersForCart($userId)
    {
        $cartItems = $this->getCartItems($userId);

        if ($cartItems->isEmpty()) {
            return collect();
        }

        $cartTotal = $this->calculateCartTotal($cartItems, $userId);
        $identifiers = $this->getCartIdentifiers($cartItems);

        // Get product/brand/category based offers
        $productOffers = GiftOffer::with(['items.product', 'conditions'])
            ->active()
            ->valid()
            ->whereIn('offer_type', ['product'])
            ->where(function ($query) use ($identifiers) {
                $query->whereHas('conditions', function ($q) use ($identifiers) {
                    $q->where('condition_type', 'product')
                      ->whereIn('item_id', $identifiers['product_ids']);
                });
                // ->orWhereHas('conditions', function ($q) use ($identifiers) {
                //     $q->where('condition_type', 'brand')
                //       ->whereIn('item_id', $identifiers['brand_ids']);
                // })
                // ->orWhereHas('conditions', function ($q) use ($identifiers) {
                //     $q->where('condition_type', 'category')
                //       ->whereIn('item_id', $identifiers['category_ids']);
                // });
            })
            ->get();

        // Get cart amount based offers
        $cartAmountOffers = GiftOffer::with(['items.product', 'conditions'])
            ->active()
            ->valid()
            ->where('offer_type', 'cart')
            ->where('min_cart_amount', '<=', $cartTotal)
            ->get();

        return $productOffers->merge($cartAmountOffers)->unique('id');
    }

    /**
     * Get separated offers for cart (product-based and cart-amount-based)
     * Returns array with 'product_offers', 'cart_amount_offers', 'all_offers', 'cart_total'
     */
    public function getSeparatedOffersForCart($userId): array
    {
        $cartItems = $this->getCartItems($userId);

        if ($cartItems->isEmpty()) {
            return [
                'product_offers' => collect(),
                'cart_amount_offers' => collect(),
                'all_offers' => collect(),
                'cart_total' => 0,
                'cart_items' => collect(),
            ];
        }

        $cartTotal = $this->calculateCartTotal($cartItems, $userId);
        $identifiers = $this->getCartIdentifiers($cartItems);

        // Get product based offers
        $productOffers = GiftOffer::with(['items.product', 'conditions.product'])
            ->active()
            ->valid()
            ->whereIn('offer_type', ['product'])
            ->where(function ($query) use ($identifiers) {
                $query->whereHas('conditions', function ($q) use ($identifiers) {
                    $q->where('condition_type', 'product')
                      ->whereIn('item_id', $identifiers['product_ids']);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get cart amount based offers
        $cartAmountOffers = GiftOffer::with(['items.product', 'conditions'])
            ->active()
            ->valid()
            ->where('offer_type', 'cart')
            ->where('min_cart_amount', '<=', $cartTotal)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'product_offers' => $productOffers,
            'cart_amount_offers' => $cartAmountOffers,
            'all_offers' => $productOffers->merge($cartAmountOffers)->unique('id'),
            'cart_total' => $cartTotal,
            'cart_items' => $cartItems,
        ];
    }

    /**
     * Check if a specific offer is eligible for a user's cart
     */
    public function isOfferEligible($offerId, $userId): bool
    {
        $eligibleOffers = $this->getEligibleOffersForCart($userId);
        return $eligibleOffers->contains('id', $offerId);
    }

    /**
     * Get current gift selections for a user
     */
    public function getSelections($userId)
    {
        $userField = $this->getUserField($userId);
        return CartGiftSelection::where($userField, $userId)
            ->with(['giftOffer', 'giftOfferItem.product', 'product'])
            ->get();
    }

    /**
     * Check if user has any active selection
     */
    public function hasActiveSelection($userId): bool
    {
        $userField = $this->getUserField($userId);
        return CartGiftSelection::where($userField, $userId)->exists();
    }

    /**
     * Get selected offer ID for a user
     */
    public function getSelectedOfferId($userId): ?int
    {
        $userField = $this->getUserField($userId);
        $selection = CartGiftSelection::where($userField, $userId)->first();
        return $selection ? $selection->gift_offer_id : null;
    }

    /**
     * Select a gift item
     */
    public function selectGift($userId, int $giftOfferId, int $giftOfferItemId, int $quantity = 1, ?string $variation = null): array
    {
        $userField = $this->getUserField($userId);

        // Validate offer is eligible
        if (!$this->isOfferEligible($giftOfferId, $userId)) {
            return ['success' => false, 'message' => 'This offer is not eligible for your cart'];
        }

        // Get the gift offer
        $offer = GiftOffer::find($giftOfferId);
        if (!$offer || !$offer->isValid()) {
            return ['success' => false, 'message' => 'Gift offer not found or expired'];
        }

        // Get the gift item
        $item = GiftOfferItem::where('id', $giftOfferItemId)
            ->where('gift_offer_id', $giftOfferId)
            ->where('status', 1)
            ->first();

        if (!$item) {
            return ['success' => false, 'message' => 'Gift item not found'];
        }

        // Check stock availability
        $remainingQty = $item->available_qty - $item->used_qty;
        if ($remainingQty < $quantity) {
            return ['success' => false, 'message' => 'Insufficient gift stock available'];
        }

        // Check if user already has selection from a DIFFERENT offer
        $existingSelection = CartGiftSelection::where($userField, $userId)->first();
        if ($existingSelection && $existingSelection->gift_offer_id != $giftOfferId) {
            return ['success' => false, 'message' => 'You already have a selection from another offer. Remove it first.'];
        }

        // Check max quantity per order (0 or null means unlimited)
        if ($offer->max_qty_per_order && $offer->max_qty_per_order > 0) {
            $currentSelectionQty = CartGiftSelection::where($userField, $userId)
                ->where('gift_offer_id', $giftOfferId)
                ->sum('quantity');

            if (($currentSelectionQty + $quantity) > $offer->max_qty_per_order) {
                // return ['success' => false, 'message' => 'Maximum gift quantity limit reached for this offer'];
            }
        }

        // Check if same item already selected
        $existingItem = CartGiftSelection::where($userField, $userId)
            ->where('gift_offer_id', $giftOfferId)
            ->where('gift_offer_item_id', $giftOfferItemId)
            ->first();

        if ($existingItem) {
            // Update quantity
            $newQty = $existingItem->quantity + $quantity;
            if ($newQty > $remainingQty) {
                return ['success' => false, 'message' => 'Insufficient gift stock available'];
            }
            $existingItem->update(['quantity' => $newQty]);
        } else {
            // Create new selection
            CartGiftSelection::create([
                $userField => $userId,
                'gift_offer_id' => $giftOfferId,
                'gift_offer_item_id' => $giftOfferItemId,
                'product_id' => $item->product_id,
                'quantity' => $quantity,
                'variation' => $variation,
            ]);
        }

        return ['success' => true, 'message' => 'Gift item selected successfully'];
    }

    /**
     * Remove a gift selection
     */
    public function removeSelection($userId, int $selectionId): array
    {
        $userField = $this->getUserField($userId);

        $selection = CartGiftSelection::where('id', $selectionId)
            ->where($userField, $userId)
            ->first();

        if (!$selection) {
            return ['success' => false, 'message' => 'Selection not found'];
        }

        $selection->delete();

        return ['success' => true, 'message' => 'Gift selection removed successfully'];
    }

    /**
     * Clear all selections for a user
     */
    public function clearSelections($userId): void
    {
        $userField = $this->getUserField($userId);
        CartGiftSelection::where($userField, $userId)->delete();
    }

    /**
     * Calculate total offer price for selections
     */
    public function calculateSelectionsTotal($userId): float
    {
        $selections = $this->getSelections($userId);
        $total = 0;

        foreach ($selections as $selection) {
            if ($selection->giftOfferItem) {
                $total += (float) $selection->giftOfferItem->offer_price * $selection->quantity;
            }
        }

        return $total;
    }

    /**
     * Process gift selections for order placement
     */
    public function processOrderGifts(Order $order, $userId): void
    {
        $selections = $this->getSelections($userId);

        if ($selections->isEmpty()) {
            return;
        }

        $cartItems = $this->getCartItems($userId);
        $cartTotal = $this->calculateCartTotal($cartItems, $userId);

        // Group selections by offer
        $groupedSelections = $selections->groupBy('gift_offer_id');

        foreach ($groupedSelections as $offerId => $offerSelections) {
            $offer = GiftOffer::find($offerId);
            if (!$offer) continue;

            // Create order gift offer record
            $orderGiftOffer = OrderGiftOffer::create([
                'order_id' => $order->id,
                'user_id' => Str::startsWith($userId, 'tmp') ? null : $userId,
                'temp_user_id' => Str::startsWith($userId, 'tmp') ? $userId : null,
                'gift_offer_id' => $offerId,
                'qualifying_amount' => $cartTotal,
                'qualifying_products' => $cartItems->pluck('product_id')->toArray(),
            ]);

            // Create order gift offer items
            foreach ($offerSelections as $selection) {
                $item = $selection->giftOfferItem;
                $product = $selection->product;

                if (!$item || !$product) continue;

                OrderGiftOfferItem::create([
                    'order_gift_offer_id' => $orderGiftOffer->id,
                    'order_id' => $order->id,
                    'gift_offer_id' => $offerId,
                    'gift_offer_item_id' => $selection->gift_offer_item_id,
                    'product_id' => $selection->product_id,
                    'quantity' => $selection->quantity,
                    'product_name' => $product->name,
                    'product_price' => $item->offer_price,
                    'variation' => $selection->variation,
                ]);

                // Update used quantity
                $item->increment('used_qty', $selection->quantity);
            }

            // Update offer usage count
            $offer->increment('total_used_count');
        }

        // Clear cart selections
        $this->clearSelections($userId);
    }

    /**
     * Format selection for API response
     */
    public function formatSelection($selection): array
    {
        $item = $selection->giftOfferItem;
        $product = $selection->product ?? $item?->product;
        $offer = $selection->giftOffer;

        $originalPrice = $product ? (float) $product->unit_price : 0;
        $offerPrice = $item ? (float) $item->offer_price : 0;
        $discountPercent = $originalPrice > 0 ? round((($originalPrice - $offerPrice) / $originalPrice) * 100) : 0;

        return [
            'id' => $selection->id,
            'gift_offer_id' => $selection->gift_offer_id,
            'gift_offer_item_id' => $selection->gift_offer_item_id,
            'offer_title' => $offer?->title,
            'offer_slug' => $offer?->slug,
            'product_id' => $selection->product_id,
            'product_name' => $product?->name,
            'product_slug' => $product?->slug,
            'product_thumbnail' => $product ? api_asset($product->thumbnail_img) : null,
            'quantity' => (int) $selection->quantity,
            'variation' => $selection->variation,
            'original_price' => $originalPrice,
            'formatted_original_price' => format_price($originalPrice),
            'offer_price' => $offerPrice,
            'formatted_offer_price' => format_price($offerPrice),
            'discount_percent' => $discountPercent,
            'is_free' => $offerPrice == 0,
            'discount_label' => $offerPrice == 0 ? 'FREE' : ($discountPercent > 0 ? $discountPercent . '% OFF' : ''),
            'line_total' => $offerPrice * $selection->quantity,
            'formatted_line_total' => format_price($offerPrice * $selection->quantity),
        ];
    }

    /**
     * Get formatted selections for API
     */
    public function getFormattedSelections($userId): array
    {
        $selections = $this->getSelections($userId);

        if ($selections->isEmpty()) {
            return [
                'has_selections' => false,
                'selected_offer_id' => null,
                'selections' => [],
                'total_offer_price' => 0,
                'formatted_total_offer_price' => format_price(0),
            ];
        }

        $formatted = $selections->map(fn($s) => $this->formatSelection($s))->values()->toArray();
        $total = $this->calculateSelectionsTotal($userId);

        return [
            'has_selections' => true,
            'selected_offer_id' => $selections->first()->gift_offer_id,
            'selections' => $formatted,
            'total_offer_price' => $total,
            'formatted_total_offer_price' => format_price($total),
        ];
    }
}
