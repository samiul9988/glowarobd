<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVisitController extends Controller
{
    public function index(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        if (filled($request->date)) {
            $dateRange = explode(' to ', $request->date);
            if (count($dateRange) == 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate   = Carbon::parse($dateRange[1])->endOfDay();
            }
        }
        $source    = $request->input('source');
        $productId = $request->input('product_id');

        // Get base statistics
        $query = ProductVisit::query()
            ->select('product_id', DB::raw('COUNT(*) as total_visits, MAX(created_at) as last_visited_at'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('product_id')
            ->with('product:id,name,slug,last_viewed_at,views_count');

        if ($source) {
            $query->where('utm_source', $source);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $visits = $query->orderBy('total_visits', 'desc')->paginate(20);

        // Get source breakdown for each product
        foreach ($visits as $visit) {
            $breakdown = ProductVisit::where('product_id', $visit->product_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('utm_source', DB::raw('COUNT(*) as count'))
                ->groupBy('utm_source')
                ->get()
                ->mapWithKeys(function ($item) {
                    $source = $item->utm_source ?: 'direct';
                    return [$source => $item->count];
                });

            $visit->breakdown = $breakdown;
        }

        // return response()->json([
        //     'visits' => $visits->items(),
        // ]);

        // Get available sources for filter
        $sources = ProductVisit::whereBetween('created_at', [$startDate, $endDate])
            ->distinct()
            ->pluck('utm_source')
            ->filter()
            ->values();

        return view('backend.reports.product_visits_report', compact('sources', 'visits'));
    }

    public function indexx(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        if (filled($request->date)) {
            $dateRange = explode(' to ', $request->date);
            if (count($dateRange) == 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate   = Carbon::parse($dateRange[1])->endOfDay();
            }
        }

        $products = Product::with([
            'visits' => function ($query) use ($startDate, $endDate) {
                $query->select('product_id', 'utm_source', DB::raw('COUNT(utm_source) as total_count'))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('product_id', 'utm_source');
            },
        ])
            ->without('taxes', 'stocks')
            ->where('views_count', '>', 0)
            ->when($request->source, function ($query) use ($request, $startDate, $endDate) {
                $query->whereHas('visits', function ($q) use ($request, $startDate, $endDate) {
                    $q->where('utm_source', $request->source)
                        ->whereBetween('created_at', [$startDate, $endDate]);
                });
            })
            ->when($request->product, function ($query) use ($request) {
                $query->where('id', $request->product);
            })
            ->whereNotNull('last_viewed_at')
            ->whereHas('visits', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderByDesc('views_count')
            ->select('id', 'name', 'slug', 'views_count', 'last_viewed_at')
            ->paginate(25);

        return response()->json([
            'reports' => $products->getCollection(),
        ]);

        $sources = ProductVisit::select('utm_source')->distinct()->pluck('utm_source')
            ->filter()->values();
        return view('backend.reports.product_visits_report', compact('sources', 'products'));
    }
}
