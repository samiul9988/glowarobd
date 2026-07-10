<?php

namespace App\Http\Controllers\Api\Merchant\V1;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\JWToken;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{
    public function generate(Request $request)
    {
        $user = User::where('email', $request->email)->where('banned', 0)->first();
        if (!$user || !password_verify($request->password, $user->password)) {
            return ResponseHelper::error('Invalid credentials', 401);
        }
        if(blank($user->app_id) && blank($user->app_key)) {
            $user->generateAppId();
        }
        return ResponseHelper::successtoken('Token generated successfully.', 200, [
            'app_id' => $user->app_id,
            'app_key' => $user->app_key
        ]);
    }

    public function regenerate(Request $request)
    {
        $token = JWToken::regenerate($request->header('Authorization'));

        if (!$token) {
            return ResponseHelper::error('Invalid token', 401);
        }

        if($token == 'invalid'){
            return ResponseHelper::error('Invalid or banned user', 401);
        }

        return ResponseHelper::success('Token regenerated successfully.', 200)
        ->cookie('token', $token, Carbon::now()->addDays(7)->diffInMinutes());
    }
}
