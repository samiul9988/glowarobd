<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\OrderTrack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OrderTrackService
{
    public function getUniqueUtmSources()
    {
        return OrderTrack::select('utm_source')
            ->distinct()
            ->pluck('utm_source')
            ->filter()
            ->values();
    }
    /**
     * Main list with pagination + order details
     */
    public function listReports($startDate, $endDate, $perPage = 20)
    {
        return OrderTrack::with('order:id,delivery_status,grand_total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate($perPage);
    }

    public function revenueBySource($startDate, $endDate)
    {
        $cacheKey = 'utm_report:' . md5($startDate->toDateString() . '|' . $endDate->toDateString());
        $utmAgg = DB::table('order_tracks')
            ->select(
                'utm_source',
                'order_id'
            )
            ->whereNotNull('utm_source')
            ->groupBy('utm_source', 'order_id');

        $report = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($startDate, $endDate, $utmAgg) {
            return DB::query()
                ->fromSub($utmAgg, 'ot')
                ->join('orders as o', 'o.id', '=', 'ot.order_id')
                ->whereBetween('o.created_at', [$startDate, $endDate])
                ->select(
                    'ot.utm_source',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(o.grand_total) as revenue')
                )
                ->groupBy('ot.utm_source')
                ->orderByDesc('revenue')
                ->get();
        });

        $days = $startDate->diffInDays($endDate);

        if ($days > 365) {
            $group = '%Y';
            $labelFormat = 'Y';
        } elseif ($days > 60) {
            $group = '%Y-%m';
            $labelFormat = 'M Y';
        } else {
            $group = '%Y-%m-%d';
            $labelFormat = 'd M';
        }

        $timeSeries = DB::table('orders as o')
            ->join('order_tracks as ot', 'ot.order_id', '=', 'o.id')
            ->select(
                DB::raw("DATE_FORMAT(o.created_at, '$group') as label"),
                DB::raw('SUM(o.grand_total) as revenue'),
                DB::raw('COUNT(DISTINCT o.id) as orders')
            )
            ->whereBetween('o.created_at', [$startDate, $endDate])
            ->whereNotNull('ot.utm_source')
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $labels = $timeSeries->pluck('label')->map(fn ($d) =>
            Carbon::createFromFormat(
                str_contains($group, '%d') ? 'Y-m-d' : (str_contains($group, '%m') ? 'Y-m' : 'Y'),
                $d
            )->format($labelFormat)
        );

        $revenueData = $timeSeries->pluck('revenue');
        $orderData   = $timeSeries->pluck('orders');

        return [
            'labels' => $labels,

            'datasets' => [
                [
                    'label' => 'Revenue',
                    'borderColor' => '#1a73e8',
                    'data' => $revenueData->map(fn($val) => round($val, 2)),
                ],
                [
                    'label' => 'Orders',
                    'borderColor' => '#34a853',
                    'data' => $orderData,
                ]
            ],

            'legend' => [
                'revenue' => $revenueData->sum(),
                'orders'  => $orderData->sum(),
            ],

            'top_sources' => $report->map(fn($item) => [
                'utm_source'   => strtoupper(\App\Enums\UtmSources::value($item->utm_source)),
                'order_count'  => $item->order_count,
                'revenue'      => round($item->revenue, 2),
            ])
        ];
    }

    /**
     * Revenue grouped by utm_campaign
     */
    public function revenueByCampaign($startDate, $endDate)
    {
        return DB::table('order_tracks')
            ->join('orders', 'order_tracks.order_id', '=', 'orders.id')
            ->select(
                'order_tracks.utm_campaign',
                DB::raw('COUNT(order_tracks.id) as total_orders'),
                DB::raw('SUM(orders.grand_total) as total_amount')
            )
            ->whereBetween('order_tracks.created_at', [$startDate, $endDate])
            ->whereNotNull('order_tracks.utm_campaign')
            ->groupBy('order_tracks.utm_campaign')
            ->orderBy('total_amount', 'DESC')
            ->get();
    }

    /**
     * CPC vs Organic traffic comparison
     */
    public function cpcVsOrganic($startDate, $endDate)
    {
        // CPC TRAFFIC
        $cpc = DB::table('order_tracks')
            ->join('orders', 'order_tracks.order_id', '=', 'orders.id')
            ->whereBetween('order_tracks.created_at', [$startDate, $endDate])
            ->where(function($q){
                $q->where('utm_medium', 'cpc')
                  ->orWhereIn('utm_source', ['google_ads', 'fb_ads']);
            })
            ->select(
                DB::raw('COUNT(order_tracks.id) as total_orders'),
                DB::raw('SUM(orders.grand_total) as total_amount')
            )
            ->first();

        // ORGANIC TRAFFIC
        $organic = DB::table('order_tracks')
            ->join('orders', 'order_tracks.order_id', '=', 'orders.id')
            ->whereBetween('order_tracks.created_at', [$startDate, $endDate])
            ->where(function($q){
                $q->where('utm_source', 'website')
                  ->orWhere('utm_medium', 'organic');
            })
            ->select(
                DB::raw('COUNT(order_tracks.id) as total_orders'),
                DB::raw('SUM(orders.grand_total) as total_amount')
            )
            ->first();

        return [
            'cpc' => $cpc,
            'organic' => $organic,
        ];
    }

    /**
     * Influencer / Affiliate performance report
     */
    public function influencerPerformance($startDate, $endDate)
    {
        return DB::table('order_tracks')
            ->join('orders', 'order_tracks.order_id', '=', 'orders.id')
            ->select(
                'order_tracks.ref_id',
                DB::raw('COUNT(order_tracks.id) as total_orders'),
                DB::raw('SUM(orders.grand_total) as total_revenue')
            )
            ->whereBetween('order_tracks.created_at', [$startDate, $endDate])
            ->where(function($q){
                $q->where('utm_source', 'affiliate')
                  ->orWhereNotNull('ref_id');
            })
            ->groupBy('order_tracks.ref_id')
            ->get();
    }
}
