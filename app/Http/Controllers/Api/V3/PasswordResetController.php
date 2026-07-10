<?php

namespace App\Http\Controllers\Api\V3;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;
use App\Notifications\PasswordResetRequest;
use App\Http\Controllers\OTPVerificationController;
use App\Notifications\AppEmailVerificationNotification;

class PasswordResetController extends Controller
{
    public function forgetRequest(Request $request)
    {
        $is_email = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL);

        $user = User::where(function ($query) use ($request) {
                $query->where('email', $request->email_or_phone)
                    ->orWhere('phone', $request->email_or_phone)
                    ->orWhere('phone', '+88'.$request->email_or_phone);
            })->first();

        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();
        if ($request->send_code_by == 'email' || $is_email) {
            if(app()->environment('production')){
                $user->notify(new AppEmailVerificationNotification());
            }
        } else {
            if(app()->environment('production')){
                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            }
        }

        return response()->json([
            'result' => true,
            'message' => 'A verification code is sent to your ' . ($is_email ? 'email' : 'phone')
        ], 200);
    }

    public function confirmReset(Request $request)
    {
        $user = User::whereNotNull('verification_code')->where('verification_code', $request->verification_code)->first();

        if ($user != null) {
            $user->verification_code = null;
            if(filled($request->password)){
                $user->password = Hash::make($request->password);
                $user->email_verified_at = now();
                $user->save();
            } else {
                $user->email_verified_at = now();
                $user->save();
                if(filled($request->temp_user_id)){
                    replace_temp_user_id($request->temp_user_id, $user->id);
                }
                $tokenResult = $user->createToken('Personal Access Token');
                return $this->loginSuccess($tokenResult, $user);
            }
            return response()->json([
                'result' => true,
                'message' => ('Your password is reset successfully. Please login to your account with new password.'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => ('No user is found'),
            ], 200);
        }
    }

    public function resendCode(Request $request)
    {
        $is_email = filter_var($request->email_or_code, FILTER_VALIDATE_EMAIL);

        $user = User::where(function ($query) use ($request) {
                $query->where('email', $request->email_or_code)
                    ->orWhere('phone', $request->email_or_code)
                    ->orWhere('phone', '+88'.$request->email_or_code);
            })->first();

        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        if ($request->verify_by == 'email' || $is_email) {
            if(app()->environment('production')){
                $user->notify(new AppEmailVerificationNotification());
            }
        } else {
            if(app()->environment('production')){
                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            }
        }

        return response()->json([
            'result' => true,
            'message' => 'Verification code is sent again',
        ], 200);
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();

        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();

        $user->loadMissing('customeringroup.group');
        return response()->json([
            'result' => true,
            'message' => ('Successfully logged in'),
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => api_asset($user->avatar_original),
                'phone' => $user->phone,
                'gender' => $user->gender,
                'date_of_birth' => isset($user->date_of_birth) ? Carbon::parse($user->date_of_birth)->format('Y-m-d') : null,
                'customer_group' => [
                    'id' => $user->customeringroup->group->id ?? null,
                    'name' => $user->customeringroup->group->name ?? null,
                ]
            ]
        ]);
    }
}
