<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MerchantApiLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MerchantApiLogger
{
    public function handle(Request $request, Closure $next)
    {
        // Start time for response time calculation
        $startTime = microtime(true);

        // Proceed with the request
        $response = $next($request);

        // Calculate response time
        $responseTime = microtime(true) - $startTime;

        // dd($request->header('id'));
        $id = $request->header('id');
        $email = $request->header('email');
        // Get the authenticated merchant (if any)
        $merchant = User::where('id', $id)->first();

        // dd($merchant);

        // Log the request and response
        $log = MerchantApiLog::create([
            'user_id' => $merchant ? $merchant->id : null,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'payload' => $request->all(),
            'response' => $response->getContent(),
            'response_code' => $response->getStatusCode(),
            'response_time' => number_format($responseTime * 1000, 2), // Convert to milliseconds
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // dd($log);
        return $response;
    }
}