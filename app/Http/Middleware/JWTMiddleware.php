<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\JWToken;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\SignatureInvalidException;

class JWTMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // dd($request->header('App-ID'), $request->header('Secret-Key'));
        $app_id = $request->header('App-ID');
        $app_key = $request->header('Secret-Key');
        if (!$app_id || !$app_key) {
            return response()->json(['error' => 'App ID or Secret Key not provided'], 401);
        }
        try {
            $decoded = JWToken::verify($app_id, $app_key);
            if (!$decoded) {
                return response()->json(['error' => 'Invalid App-ID'], 401);
            }else if($decoded == 'invalid'){
                return response()->json(['error' => 'User not found'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid App-ID or Secret-Key'], 401);
        }
        Auth::loginUsingId($decoded->id);
        $request->headers->set('id', $decoded->id);
        $request->headers->set('email', $decoded->email);
        if(in_array($decoded->email, ['merchant@gmail.com', 'merchant@tekka.com.bd', 'merchant@glowarobd.com'])){
            $request->headers->set('merchant_type', 'test');
        }
        if(!$request->hasHeader('Content-Type')){
            $request->headers->set('Content-Type', 'application/json');
        }
        return $next($request);
    }
}
