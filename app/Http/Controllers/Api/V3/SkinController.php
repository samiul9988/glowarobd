<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\MetaObject;
use Illuminate\Http\Request;
use App\Models\MetaObjectItem;
use App\Http\Controllers\Controller;

class SkinController extends Controller
{
    public function getSkinConcerns(Request $request)
    {
        $metaObject = MetaObject::where('name', 'like', 'skin concern%')->first();

        if (!$metaObject) {
            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => [],
            ]);
        }

        $skinConcerns = MetaObjectItem::active()->where('meta_object_id', $metaObject->id);

        if ($request->has('limit')) {
            $skinConcerns = $skinConcerns->limit($request->limit);
        }
        $skinConcerns = $skinConcerns->get();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $skinConcerns->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    // 'subtitle' => $item->subtitle ?? '',
                    // 'description' => $item->description ?? '',
                    'image' => api_asset($item->image)
                ];
            }),
        ]);
    }
}
