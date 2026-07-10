<?php

namespace App\Http\Controllers\Api\V2;
use App\Http\Resources\V2\CustomerCouponCollection;
use App\Models\Coupon;
use App\Models\Customeringroup as CustomerInGroup;
use App\Models\CouponCustomerAssignment;
use Illuminate\Http\Request;

class CouponCustomerAssignmentController extends Controller
{
    public function getAssignedCoupons(Request $request)
    {
        $userId = $request->user_id ?? null;
        $existingCouponIds = [];
        $availableCoupons = collect([]);
        $groupId = null;
        $isGuestUser = ! is_null($userId) && str_starts_with($userId, 'tmp');

        if (! $isGuestUser) {
            $assignedCoupons = CouponCustomerAssignment::with('coupon')
                ->where('customer_id', $userId)
                ->whereHas('coupon', function ($query) {
                    $query->valid();
                })
                ->get();

            $availableCoupons = $assignedCoupons->filter(function ($assignment) {
                if ('single' === strtolower($assignment->coupon->usage_limit)) {
                    return 0 === $assignment->is_used;
                }
                return true;
            });

            // Existing coupon IDs
            $existingCouponIds = $availableCoupons
                ->pluck('coupon.id')
                ->filter()
                ->unique()
                ->toArray();

            $groupId = CustomerInGroup::where('user_id', $userId)
                ->latest()
                ->value('customer_groups_id');
        }

        $groupCoupons = Coupon::valid()
            ->forGroup()
            ->when($isGuestUser, function ($query) {
                $query->featured();
            })
            ->when($groupId, function ($query) use ($groupId) {
                $query->whereJsonContains('group_ids', $groupId);
            })
            ->when(!empty($existingCouponIds), function ($query) use ($existingCouponIds) {
                $query->whereNotIn('id', $existingCouponIds);
            })
            ->get()
            ->each(function ($coupon) use (&$availableCoupons) {
                $availableCoupons->push((object) [
                    'coupon'      => $coupon,
                    'expire_date' => $coupon->end_date,
                ]);
            });

        $existingCouponIds = array_merge(
            $existingCouponIds,
            $groupCoupons->pluck('id')->toArray()
        );

        // Public coupons
        $publicCoupons = Coupon::valid()
            ->featured()
            ->forAll()
            ->when(!empty($existingCouponIds), function ($query) use ($existingCouponIds) {
                $query->whereNotIn('id', $existingCouponIds);
            })
            ->get()
            ->each(function ($coupon) use (&$availableCoupons) {
                $availableCoupons->push((object) [
                    'coupon'      => $coupon,
                    'expire_date' => $coupon->end_date,
                ]);
            });

        return new CustomerCouponCollection($availableCoupons->values());
    }
}
