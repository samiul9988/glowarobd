<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\PurchaseHistoryDetailCollection;
use App\Models\OrderDetail;

class PurchaseHistoryDetailController extends Controller
{
    public function index($id)
    {
        return new PurchaseHistoryDetailCollection(OrderDetail::where('order_id', $id)->get());
    }
}
