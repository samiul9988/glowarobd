<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\OTPVerificationController;
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
    public function lookupContact($contact)
    {
        $contactType = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if ($contactType === 'phone') {
            if (! preg_match('/^(?:\+8801\d{9}|01\d{9})$/', $contact)) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid phone number or email address',
                ], 422);
            }
            $contact = normalizePhoneNumber($contact);
            $user = User::whereIn('phone', [$contact, '+88'.$contact])->first();
        } else {
            $user = User::where('email', $contact)->first();
        }

        if ($user) {
            return response()->json([
                'result' => true,
                'message' => 'User exists',
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => 'Account not found. Please use a different account or sign up for a new account.',
            ], 404);
        }
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'email_or_phone' => [
                'required',
                'string',
                // new EmailOrPhoneRule()
            ],
        ], [
            'name.required' => 'The name field is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'email_or_phone.required' => 'The email or phone field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->first(),
                'user_id' => 0,
                'errors' => $validator->errors(),
            ], 422);
        }

        $register_by = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if ($register_by === 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $phone = normalizePhoneNumber($request->email_or_phone);
            $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        }

        if ($user) {
            $user->name = $user->name.'#NEW#'.$request->name;
            $user->temp_password = bcrypt($request->password);
            $user->save();

            return $this->verifyUser($user, $register_by);
        }

        $user = new User([
            'name' => $request->name,
            'email' => $register_by === 'email' ? $request->email_or_phone : null,
            'phone' => $register_by === 'phone' ? normalizePhoneNumber($request->email_or_phone) : null,
            'password' => bcrypt($request->password),
            'verification_code' => rand(100000, 999999),
        ]);
        $user->save();

        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();

        if ($register_by == 'email') {
            if (get_setting('email_verification') != 1 || ! app()->environment('production')) {
                $user->email_verified_at = date('Y-m-d H:m:s');
            } else {
                try {
                    $user->notify(new AppEmailVerificationNotification);
                } catch (\Exception $e) {
                    return response()->json([
                        'result' => false,
                        'message' => ('Email not sent. Please contact customer support.'),
                        'user_id' => 0,
                    ], 201);
                }
            }
        } elseif ($register_by == 'phone' && addon_is_activated('otp_system') && app()->environment('production')) {
            $otpController = new OTPVerificationController;
            $otpController->send_code($user);
        }

        $group = Customergroup::orderBy('ordering', 'asc')->first();

        if ($group->count() > 0) {
            $first_group = new Customeringroup;
            $first_group->user_id = $user->id;
            $first_group->customer_groups_id = $group->id;
            $first_group->status = 1;
            $first_group->save();
        }

        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();

        if (filled($request->temp_user_id)) {
            replace_temp_user_id($request->temp_user_id, $user->id);
        }

        return response()->json([
            'result' => true,
            'message' => 'Registration Successful. Please verify and log in to your account.',
            'user_id' => $user->id,
        ], 201);
    }

    public function verifyUser(User $user, $contactType)
    {
        $otp = rand(100000, 999999);
        $user->verification_code = $otp;
        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();
        if (addon_is_activated('otp_system') && app()->environment('production') && $contactType == 'phone') {
            $otpController = new OTPVerificationController;
            $otpController->send_code($user);
        } elseif ($contactType == 'email') {
            $user->notify(new AppEmailVerificationNotification);
        }
        $message = $contactType === 'email' ? 'A verification link has been sent to your email address. Please check inbox or spam folder.' : 'A verification code has been sent to your phone number. Please check your messages.';

        return response()->json([
            'result' => true,
            'message' => $message,
            'user_id' => $user->id,
        ], 201);
    }

    public function resendCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->verification_code = rand(100000, 999999);

        if ($request->verify_by == 'email') {
            if (app()->environment('production')) {
                $user->notify(new AppEmailVerificationNotification);
            }
        } else {
            if (app()->environment('production')) {
                $otpController = new OTPVerificationController;
                $otpController->send_code($user);
            }
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => 'Verification code is sent again',
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        if (! isset($request->user_id) || ! isset($request->verification_code)) {
            return response()->json([
                'result' => false,
                'message' => 'User ID and verification code are required.',
            ], 400);
        }
        $user = User::where('id', $request->user_id)->first();

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            if (str_contains($user->name, '#NEW#')) {
                $nameParts = explode('#NEW#', $user->name);
                $user->name = end($nameParts) ?? $nameParts[0];
                if (! is_null($user->temp_password)) {
                    $user->password = $user->temp_password;
                    $user->temp_password = null;
                }
                if (filled($request->temp_user_id)) {
                    replace_temp_user_id($request->temp_user_id, $user->id);
                }
            }
            $user->save();

            if ($request->has('auth_type') && $request->auth_type === 'login') {
                $tokenResult = $user->createToken('Personal Access Token');

                return $this->loginSuccess($tokenResult, $user);
            }

            return response()->json([
                'result' => true,
                'message' => 'Your account is now verified. Please login',
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => 'Invalid verification code. Please try again.',
            ], 200);
        }
    }

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

    public function loginWithOtp(Request $request)
    {
        $type = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if ($type == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $phone = normalizePhoneNumber($request->email_or_phone);
            if (strlen($phone) < 11) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid phone number or email!',
                    'user' => null,
                ], 400);
            }
            $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        }

        if (! $user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found',
                'user' => null,
            ], 401);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        try {
            if ($type === 'email') {
                $user->notify(new AppEmailVerificationNotification);
            } else {
                if (addon_is_activated('otp_system')) {
                    $otpController = new OTPVerificationController;
                    $otpController->send_code($user);
                } else {
                    return response()->json([
                        'result' => false,
                        'message' => 'Login with OTP not available. Please contact support.',
                        'user' => null,
                    ], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Failed to send OTP. Please login with password or contact support.',
                'user' => null,
            ], 500);
        }

        return response()->json([
            'result' => true,
            'message' => 'OTP sent successfully to your '.$type,
            'user' => $user->id,
            'hash' => encrypt($user->id.'|'.$request->email_or_phone),
        ], 200);
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

                if (! $providerUser) {
                    return response()->json([
                        'result' => false,
                        'message' => 'Provider Token Not Valid',
                        'user' => null,
                    ], 401);
                }
                $email = $request->email ?? $providerUser->getEmail();
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $name = $providerUser->getName();

                    $user = new User([
                        'name' => $name,
                        'email' => $email,
                        'provider_id' => $providerUser->getId(),
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

                $tokenResult = $user->createToken('Personal Access Token');

                return $this->loginSuccess($tokenResult, $user);
            } catch (\Throwable $th) {
                return response()->json([
                    'result' => false,
                    'message' => 'Provider name or token not valid',
                    'user' => null,
                ], 401);
            }
        } else {
            return response()->json([
                'result' => false,
                'message' => 'Provider name or token not valid',
                'user' => null,
            ], 401);
        }
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $refreshToken = Str::random(80);
        $user->refresh_token = hash('sha256', $refreshToken);
        $user->refresh_token_expires_at = Carbon::now()->addMonths(3);
        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();

        $user->loadMissing('customeringroup.group');

        return response()->json([
            'result' => true,
            'message' => 'Successfully logged in',
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
                'customer_group' => [
                    'id' => $user->customeringroup->group->id ?? null,
                    'name' => $user->customeringroup->group->group_name ?? null,
                ],
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
                'success' => false,
                'message' => 'Refresh token is required.',
            ], 422);
        }

        $hashedToken = hash('sha256', $request->refresh_token);
        $user = User::where('refresh_token', $hashedToken)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid refresh token.',
            ], 401);
        }

        if ($user->refresh_token_expires_at === null || Carbon::parse($user->refresh_token_expires_at)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token has expired. Please log in again.',
            ], 401);
        }

        // Revoke existing access tokens
        $user->tokens()->update(['revoked' => true]);

        // Issue a new access token
        $tokenResult = $user->createToken('Personal Access Token');

        // Rotate the refresh token
        $newRefreshToken = Str::random(80);
        $user->refresh_token = hash('sha256', $newRefreshToken);
        $user->refresh_token_expires_at = Carbon::now()->addDays(30);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully.',
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'refresh_token' => $newRefreshToken,
            'refresh_token_expires_at' => $user->refresh_token_expires_at->toDateTimeString(),
        ]);
    }
}
