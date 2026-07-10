<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CouponUsageController extends Controller
{
    public function index(Request $request)
    {
        $couponUsages = CouponUsage::with([
                'coupon:id,code',
                'user:id,name',
                'referrer:id,name',
                'order:id,code'
            ])
            ->whereHas('coupon')
            ->when($request->date, function ($query) use ($request) {
                $dateRange = explode(' to ', $request->date);
                if (count($dateRange) === 2) {
                    $startDate = Carbon::parse($dateRange[0])->startOfDay();
                    $endDate = Carbon::parse($dateRange[1])->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->when($request->coupon, function ($query) use ($request) {
                $query->where('coupon_id', $request->integer('coupon'));
            })
            ->when($request->referrer, function ($query) use ($request) {
                $query->whereNotNull('ref_id')->where('ref_id', $request->integer('referrer'));
            })
            ->latest('created_at')
            ->paginate(20);

        $coupons = Cache::remember('all_coupons', now()->addHour(), function () {
            return Coupon::pluck('code', 'id')->toArray();
        });

        // return response()->json([
        //     'coupon_usages' => $couponUsages,
        //     'coupons' => $coupons,
        // ]);
        return view('backend.reports.coupon_usage_report', compact('couponUsages', 'coupons'));
    }
}
