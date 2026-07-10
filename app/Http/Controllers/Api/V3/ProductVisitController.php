<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductVisitController extends Controller
{
    public function store(Request $request)
    {
        if ($request->product_id) {
            record_product_visit($request->all(), $request->product_id);
            return response()->json(['success' => true, 'message' => 'Product visit recorded successfully.'], 200);
        }
        return response()->json(['success' => false, 'message' => 'Product ID is required.'], 400);
    }
}
