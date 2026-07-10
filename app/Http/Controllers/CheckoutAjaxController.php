<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CheckoutAjaxController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Get user ID and field for cart queries
     */
    protected function getUserContext(Request $request): array
    {
        if (Auth::check()) {
            return [
                'user_id' => Auth::id(),
                'user_field' => 'user_id',
            ];
        }

        return [
            'user_id' => $request->temp_user_id,
            'user_field' => 'temp_user_id',
        ];
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $request->validate([
            'cart_id' => 'required|integer',
            'is_plus' => 'required|boolean',
        ]);

        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $result = $this->checkoutService->updateQuantity(
            $request->cart_id,
            1,
            $request->is_plus
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        // Get area ID from selected address or cart
        $areaId = $this->getAreaIdFromRequest($request);

        // Get updated checkout data
        $checkoutData = $this->checkoutService->getCheckoutData(
            $areaId,
            $request->shipping_method_id
        );

        return response()->json(array_merge($result, [
            'checkout_data' => $checkoutData,
        ]));
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $request->validate([
            'cart_id' => 'required|integer',
        ]);

        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $result = $this->checkoutService->removeFromCart($request->cart_id);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        // Get area ID from selected address or cart
        $areaId = $this->getAreaIdFromRequest($request);

        // Get updated checkout data
        $checkoutData = $this->checkoutService->getCheckoutData(
            $areaId,
            $request->shipping_method_id
        );

        return response()->json(array_merge($result, [
            'checkout_data' => $checkoutData,
        ]));
    }

    /**
     * Get shipping methods for an address or area
     */
    public function getShippingMethods(Request $request): JsonResponse
    {
        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $areaId = $this->getAreaIdFromRequest($request);

        $carts = $this->checkoutService->getCarts();
        $shippingMethods = $this->checkoutService->getShippingMethods($areaId, $carts);
        $shippingMessage = $this->checkoutService->getShippingMessage($carts);

        return response()->json([
            'success' => true,
            'shipping_methods' => $shippingMethods,
            'shipping_message' => $shippingMessage,
        ]);
    }

    /**
     * Get cart summary
     */
    public function getCartSummary(Request $request): JsonResponse
    {
        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $areaId = $this->getAreaIdFromRequest($request);

        $summary = $this->checkoutService->getCartSummary(
            null,
            $areaId,
            $request->shipping_method_id
        );

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Get full checkout data (cart items, shipping methods, summary)
     */
    public function getCheckoutData(Request $request): JsonResponse
    {
        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $areaId = $this->getAreaIdFromRequest($request);

        $checkoutData = $this->checkoutService->getCheckoutData(
            $areaId,
            $request->shipping_method_id
        );

        return response()->json($checkoutData);
    }

    /**
     * Update address and get refreshed checkout data
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|integer',
        ]);

        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $result = $this->checkoutService->updateCartAddress($request->address_id);

        return response()->json($result);
    }

    /**
     * Handle area change (for new address form)
     */
    public function onAreaChange(Request $request): JsonResponse
    {
        $request->validate([
            'area_id' => 'required|integer',
        ]);

        $context = $this->getUserContext($request);
        $this->checkoutService->setUserContext($context['user_id'], $context['user_field']);

        $carts = $this->checkoutService->getCarts();
        $shippingMethods = $this->checkoutService->getShippingMethods($request->area_id, $carts);
        $shippingMessage = $this->checkoutService->getShippingMessage($carts);
        $summary = $this->checkoutService->getCartSummary(null, $request->area_id);

        return response()->json([
            'success' => true,
            'shipping_methods' => $shippingMethods,
            'shipping_message' => $shippingMessage,
            'summary' => $summary,
        ]);
    }

    /**
     * Handle shipping method change
     */
    public function onShippingMethodChange(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_method_id' => 'required|integer',
        ]);

        $address = \App\Models\Address::find($request->address_id);
        if ($address) {
            $areaId = $address->area_id;
        } else {
            $areaId = null;
        }

        $shippingZone = \App\Models\ShippingZone::where('rest_of_the_world', 1)->first();
        if($areaId) {
            $shippingZone = \App\Models\ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$areaId])->first() ?? $shippingZone;
        }
        $rates = json_decode($shippingZone->rates, true);
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        $cart = Cart::where($request->user_field, $userId)->first();
        if ($cart) {
            $cart->shipping_method = $request->shipping_method_id;
            $cart->shipping_cost = collect($rates)->firstWhere('id', $request->shipping_method_id)['price'] ?? $cart->shipping_cost;
            $cart->save();
            $message = "Shipping method updated successfully.";
        }
        return response()->json([
            'success' => true,
            'message' => $message ?? '',
        ]);
    }

    /**
     * Get area ID from request (address_id or area_id)
     */
    protected function getAreaIdFromRequest(Request $request): ?int
    {
        if ($request->filled('area_id')) {
            return (int)$request->area_id;
        }

        if ($request->filled('address_id')) {
            $address = \App\Models\Address::find($request->address_id);
            return $address ? $address->area_id : null;
        }

        // Try to get from selected address in cart
        $context = $this->getUserContext($request);
        $carts = $this->checkoutService->getCarts($context['user_id'], $context['user_field']);

        if ($carts->isNotEmpty()) {
            $firstCart = $carts->first();
            if ($firstCart->address_id) {
                $address = \App\Models\Address::find($firstCart->address_id);
                return $address ? $address->area_id : null;
            }
        }

        return null;
    }
}
