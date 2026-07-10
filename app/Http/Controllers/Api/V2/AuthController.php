<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\OTPVerificationController;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Customergroup;
use App\Models\Customeringroup;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        // if(User::where('ip', $request->ip())->where('user_agent', $request->userAgent())->exists()){
        //     return response()->json([
        //         'result' => false,
        //         'message' => ('You are already registered from this device. Please login to continue.'),
        //         'user_id' => 0
        //     ], 200);
        // }
        if (User::where('email', $request->email_or_phone)->orWhere('phone', $request->email_or_phone)->first() != null) {
            return response()->json([
                'result' => false,
                'message' => ('User already exists.'),
                'user_id' => 0,
            ], 201);
        }

        if ($request->register_by == 'email') {
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'email_or_phone' => ['required', 'string', 'email', 'max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => ('There is problem with the email address & name can not be less than 10 words'),
                    'user_id' => 0,
                ], 201);
            }
            $user = new User([
                'name' => $request->name,
                'email' => $request->email_or_phone,
                'password' => bcrypt($request->password),
                'verification_code' => rand(100000, 999999),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'email_or_phone' => 'required|min:10',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'result' => false,
                    'message' => ('There is problem with the phone number & name can not be less than 10 words'),
                    'user_id' => 0,
                ], 201);
            }
            $user = new User([
                'name' => $request->name,
                'phone' => $request->email_or_phone,
                'password' => bcrypt($request->password),
                'verification_code' => rand(100000, 999999),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        if ($request->register_by == 'email') {
            if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                $user->email_verified_at = date('Y-m-d H:m:s');
            } else {
                try {
                    $user->notify(new AppEmailVerificationNotification);
                } catch (\Exception $e) {

                }
            }
        } else {
            $otpController = new OTPVerificationController;
            $otpController->send_code($user);
        }

        $user->save();

        $group = Customergroup::orderBy('ordering', 'asc')->first();

        if ($group->count() > 0) {
            $first_group = new Customeringroup;
            $first_group->user_id = $user->id;
            $first_group->customer_groups_id = $group->id;
            $first_group->status = 1;
            $first_group->save();
        }

        $recent_login = User::find($user->id);
        $recent_login->recent_login = date('Y-m-d H:i:s');
        $recent_login->update();

        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();

        return response()->json([
            'result' => true,
            'message' => ('Registration Successful. Please verify and log in to your account.'),
            'user_id' => $user->id,
        ], 201);
    }

    public function resendCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->verification_code = rand(100000, 999999);

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification);
        } else {
            $otpController = new OTPVerificationController;
            $otpController->send_code($user);
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => ('Verification code is sent again'),
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();

            return response()->json([
                'result' => true,
                'message' => ('Your account is now verified.Please login'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => ('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    // public function login(Request $request)
    // {
    //     $delivery_boy_condition = $request->has('user_type') && $request->user_type == 'delivery_boy';

    //     if ($delivery_boy_condition) {
    //         $user = User::whereIn('user_type', ['delivery_boy'])->where('email', $request->email)->orWhere('phone', $request->email)->first();
    //     } else {
    //         $user = User::whereIn('user_type', ['customer', 'seller'])->where('email', $request->email)->orWhere('phone', $request->email)->first();
    //     }

    //     if (!$delivery_boy_condition) {
    //         if (\App\Utility\PayhereUtility::create_wallet_reference($request->identity_matrix) == false) {
    //             return response()->json(['result' => false, 'message' => 'Identity matrix error', 'user' => null], 401);
    //         }
    //     }

    //     if ($user != null) {
    //         if (Hash::check($request->password, $user->password)) {

    //             if ($user->email_verified_at == null) {
    //                 return response()->json([
    //                     'result' => false,
    //                     'message' => ('Please verify your account'),
    //                     'user' => null,
    //                     'user_id'=>$user->id
    //                 ], 401);
    //             }
    //             $tokenResult = $user->createToken('Personal Access Token');
    //             return $this->loginSuccess($tokenResult, $user);
    //         } else {
    //             return response()->json(['result' => false, 'message' => ('Unauthorized'), 'user' => null], 401);
    //         }
    //     } else {
    //         return response()->json(['result' => false, 'message' => ('User not found'), 'user' => null], 401);
    //     }
    // }

    public function login(Request $request)
    {
        $contactType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $contact = $contactType === 'email' ? $request->email : normalizePhoneNumber($request->email);
        $user = User::whereIn('user_type', ['delivery_boy', 'customer', 'seller'])
            ->where(function ($query) use ($contact, $contactType) {
                if ($contactType === 'email') {
                    $query->where('email', $contact);
                } else {
                    $query->whereIn('phone', [$contact, '+88'.$contact]);
                }
            })
            ->first();

        $delivery_boy_condition = $request->has('user_type') && $request->user_type == 'delivery_boy';
        if (! $delivery_boy_condition) {
            if (\App\Utility\PayhereUtility::create_wallet_reference($request->identity_matrix) == false) {
                return response()->json(['result' => false, 'message' => 'Identity matrix error', 'user' => null], 401);
            }
        }

        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                if ($user->email_verified_at == null) {
                    return response()->json([
                        'result' => false,
                        'message' => ('Please verify your account'),
                        'user' => null,
                        'user_id' => $user->id,
                    ], 401);
                }
                if (filled($request->temp_user_id)) {
                    replace_temp_user_id($request->temp_user_id, $user->id);
                }
                $tokenResult = $user->createToken('Personal Access Token');

                return $this->loginSuccess($tokenResult, $user);
            } else {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid credentials!',
                    'user' => null,
                ], 401);
            }
        } else {
            return response()->json([
                'result' => false,
                'message' => 'User not found',
                'user' => null,
            ], 401);
        }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'result' => true,
            'message' => ('Successfully logged out'),
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (isset($request->provider_id)) {
            try {
                // get the provider's user. (In the provider server)
                $providerUser = Socialite::driver($request->provider_name)->userFromToken($request->provider_id);

                if ($providerUser) {
                    if (User::where('email', $request->email)->first() != null) {
                        $user = User::where('email', $request->email)->first();
                    } else {
                        $user = new User([
                            'name' => $request->name,
                            'email' => $request->email,
                            'provider_id' => $request->provider_id,
                            'email_verified_at' => Carbon::now(),
                        ]);
                        $user->save();
                        $customer = new Customer;
                        $customer->user_id = $user->id;
                        $customer->save();

                        @$eligibleGroup = getCustomerGroup(0, 0);
                        $group = new Customeringroup;
                        $group->user_id = $user->id;
                        $group->customer_groups_id = $eligibleGroup ?? 1;
                        $group->status = 1;
                        $group->save();
                    }
                } else {
                    return response()->json([
                        'result' => false,
                        'message' => ('Provider Token Not Valid'),
                        'user' => null,
                    ], 401);
                }

                $tokenResult = $user->createToken('Personal Access Token');

                return $this->loginSuccess($tokenResult, $user);
            } catch (\Throwable $th) {
                return response()->json([
                    'result' => false,
                    'message' => ('Provider name or token not valid'),
                    'user' => null,
                ], 401);
            }
        } else {
            return response()->json([
                'result' => false,
                'message' => ('Provider name or token not valid'),
                'user' => null,
            ], 401);
        }
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        $refreshToken = Str::random(80);
        $user->refresh_token = hash('sha256', $refreshToken);
        $user->refresh_token_expires_at = Carbon::now()->addMonths(3);
        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();

        return response()->json([
            'result' => true,
            'message' => ('Successfully logged in'),
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => $user->refresh_token_expires_at->toDateTimeString(),
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
            ],
        ]);
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => 'Refresh token is required.',
            ], 422);
        }

        $hashedToken = hash('sha256', $request->refresh_token);
        $user = User::where('refresh_token', $hashedToken)->first();

        if (! $user) {
            return response()->json([
                'result' => false,
                'message' => 'Invalid refresh token.',
            ], 401);
        }

        if ($user->refresh_token_expires_at === null || Carbon::parse($user->refresh_token_expires_at)->isPast()) {
            return response()->json([
                'result' => false,
                'message' => 'Refresh token has expired. Please log in again.',
            ], 401);
        }

        // Revoke existing access tokens
        $user->tokens()->update(['revoked' => true]);

        // Issue a new access token
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();

        // Rotate the refresh token
        $newRefreshToken = Str::random(80);
        $user->refresh_token = hash('sha256', $newRefreshToken);
        $user->refresh_token_expires_at = Carbon::now()->addDays(30);
        $user->save();

        return response()->json([
            'result' => true,
            'message' => 'Token refreshed successfully.',
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'refresh_token' => $newRefreshToken,
            'refresh_token_expires_at' => $user->refresh_token_expires_at->toDateTimeString(),
        ]);
    }
}
