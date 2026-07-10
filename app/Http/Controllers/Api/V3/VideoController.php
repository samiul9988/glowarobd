<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    public function viewCounter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:videos,id',
            'completed' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->first()
            ], 422);
        }

        if (!$request->boolean('completed')) {
            $response = Video::where('id', $request->id)->increment('views', 1, ['last_viewed_at' => now()]);
        } else {
            $response = Video::where('id', $request->id)->increment('completed', 1);
        }
        return response()->json([
            'success' => $response ? true : false
        ], $response ? 200 : 500);
    }
}
