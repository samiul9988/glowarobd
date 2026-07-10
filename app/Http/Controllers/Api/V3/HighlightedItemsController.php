<?php

namespace App\Http\Controllers\Api\V3;

use Illuminate\Http\Request;
use App\Http\Resources\V3\HighlightedItemCollection;

class HighlightedItemsController extends Controller
{
    public function index(Request $request)
    {
        $highlightedItems = \App\Models\HighlightedItem::with('linkable')
            ->where('status', 1)
            ->orderBy('position','asc')
            ->paginate($request->limit ?? 5);

        return new HighlightedItemCollection($highlightedItems);
    }
}
