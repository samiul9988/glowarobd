<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureGuestOrderAccess
{
    public function handle(Request $request, Closure $next, $guard = 'api')
    {
        $guestOrderActive = get_setting('guest_order_activation');

        if ($guestOrderActive == 1 && $guard === 'api' && Str::startsWith($request->user_id, 'tmp')) {
            // ✅ Guest checkout with existing temp ID
            $request->merge([
                'is_guest_user' => 1,
                'user_field' => 'temp_user_id',
                'user_id' => $request->user_id,
            ]);
        } elseif ($guestOrderActive == 1 && $guard === 'api' && empty($request->user_id)) {
            // ✅ Guest checkout without ID — generate a new temp ID
            $guestUserId = 'tmp_' . bin2hex(random_bytes(10));
            $request->merge([
                'is_guest_user' => 1,
                'user_field' => 'temp_user_id',
                'user_id' => $guestUserId,
            ]);
        } elseif ($guestOrderActive == 1 && $guard === 'web' && !Auth::check()) {
            // ✅ Guest checkout allowed
            if($request->session()->get('temp_user_id')) {
                $guestUserId = $request->session()->get('temp_user_id');
            } else {
                $guestUserId = bin2hex(random_bytes(10));
                $request->session()->put('temp_user_id', $guestUserId);
            }
            $request->merge([
                'is_guest_user' => 1,
                'user_field' => 'temp_user_id',
                'temp_user_id' => $guestUserId,
            ]);
        } else {
            // ✅ Merge authenticated user_id into request if missing
            $request->merge([
                'is_guest_user' => 0,
                'user_field' => 'user_id',
                'user_id' => $guard === 'web' ? Auth::id() : (auth('api')->id() ?? $request->user_id ?? null),
            ]);
        }

        return $next($request);
    }
}
