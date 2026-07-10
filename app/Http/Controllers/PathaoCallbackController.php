<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PathaoCallbackService;

class PathaoCallbackController extends Controller
{
    public function __invoke(Request $request, PathaoCallbackService $service)
    {
        $payload = $request->all();

        // dd($payload);

        $service->handle($payload);

        return response()->json([
            'status' => 'ok'
        ], 202)->header('X-Pathao-Merchant-Webhook-Integration-Secret', env('PATHAO_WEBHOOK_SECRET'));
    }
}
