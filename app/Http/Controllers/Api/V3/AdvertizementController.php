<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\AdvertizementCollection;
use App\Models\Advertizement;
use Illuminate\Http\Request;

class AdvertizementController extends Controller
{
    public function index()
    {
        $now = date('Y-m-d H:i:s');
        $ads = Advertizement::where('ads_type', 'app')->where('status', 1)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        if (count($ads) > 0) {
            return new AdvertizementCollection($ads);
        } else {
            return response()->json([
                'data' => null,
                'success' => false,
                'status' => 404,
                'message' => 'Data not found'
            ]);
        }
    }
}
