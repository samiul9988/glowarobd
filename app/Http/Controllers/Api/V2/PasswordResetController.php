<?php

namespace App\Http\Controllers\Api\V2;

use Hash;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequest;

use App\Http\Controllers\OTPVerificationController;
use App\Notifications\AppEmailVerificationNotification;

class PasswordResetController extends Controller
{
    public function forgetRequest(Request $request)
    {
        if ($request->send_code_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => ('User is not found')], 404);
        }

        if ($user) {
            $user->verification_code = rand(100000, 999999);
            $user->save();
            if ($request->send_code_by == 'phone') {

                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            } else {
                $user->notify(new AppEmailVerificationNotification());
            }
        }

        return response()->json([
            'result' => true,
            'message' => ('A code is sent')
        ], 200);
    }

    public function confirmReset(Request $request)
    {
        $user = User::where('verification_code', $request->verification_code)->first();

        if ($user != null) {
            $user->verification_code = null;
            $user->save();
            $tokenResult = $user->createToken('Personal Access Token');
            return $this->loginSuccess($tokenResult, $user);
            // $user->password = Hash::make($request->password);
            // $user->save();
            // return response()->json([
            //     'result' => true,
            //     'message' => ('Your password is reset.Please login'),
            // ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => ('No user is found'),
            ], 200);
        }
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();


        $recent_login = User::find($user->id);
        $recent_login->recent_login = date('Y-m-d H:i:s');
        $recent_login->update();

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
            ]
        ]);
    }

    public function resendCode(Request $request)
    {

        if ($request->verify_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => ('User is not found')], 404);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }



        return response()->json([
            'result' => true,
            'message' => ('A code is sent again'),
        ], 200);
    }
}
