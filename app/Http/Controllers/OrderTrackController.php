<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\OrderTrackService;

class OrderTrackController extends Controller
{
    protected $service;

    public function __construct(OrderTrackService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('backend.reports.order_tracking_report');
    }

    public function reports(Request $request)
    {
        [$startDate, $endDate] = $this->parseDateRange($request->date);
        $reports = [
            'groupByUtmSource'      => $this->service->revenueBySource($startDate, $endDate),
            // 'groupByUtmCampaign'     => $this->service->revenueByCampaign($startDate, $endDate),
            // 'cpcVsOrganic'          => $this->service->cpcVsOrganic($startDate, $endDate),
            // 'influencerPerformance' => $this->service->influencerPerformance($startDate, $endDate),
        ];
        return response()->json($reports);
    }

    private function parseDateRange(?string $date): array
    {
        if (filled($date)) {
            $dateRange = explode(' to ', $date);
            if (count($dateRange) === 2) {
                return [
                    Carbon::parse($dateRange[0])->startOfDay(),
                    Carbon::parse($dateRange[1])->endOfDay(),
                ];
            }
        }

        return [
            Carbon::now()->subDays(7)->startOfDay(),
            Carbon::now()->endOfDay(),
        ];
    }
}
