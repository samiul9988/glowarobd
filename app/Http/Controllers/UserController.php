<?php

namespace App\Http\Controllers;

use App\Utility\SmsUtility;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use App\Http\Controllers\OTPVerificationController;
use App\Models\{User, TempUser, Customergroup, Customeringroup, Address};

class UserController extends Controller
{
    public function createBeforeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required_if:phone,null|integer',
            'phone' => 'required_if:address_id,null|string|regex:/^(\+88)?01[3-9]\d{8}$/',
        ], [
            'address_id.required_if' => 'Address is required.',
            'phone.required_if' => 'Phone number is required.',
            'phone.regex' => 'Invalid phone number.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }
        if ($request->has('phone')) {
            $phoneNumber = $request->phone;
        } else {
            $address = Address::find($request->address_id);
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }
            $phoneNumber = $address->phone;
        }
        $phone = trim(str_replace(['-', ' ', '+88'], '', $phoneNumber));
        if (strlen($phone) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number'
            ], 400);
        }

        $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        if(!$user) {
            $user = new User;
            $user->name = $address->name ?? 'New User ('.$phone.')';
            $user->email = null;
            $user->address = $address->address ?? null;
            $user->phone = $phone;
            $password = Str::random(rand(8,10));
            $user->password = bcrypt($password);
            $user->temp_password = $password;
            $user->email_verified_at = null;
            $user->recent_login = null;
            $user->save();

            $customer = new \App\Models\Customer;
            $customer->user_id = $user->id;
            $customer->save();

            $group = Customergroup::orderBy('ordering', 'asc')->first();

            if($group){
                $first_group = new Customeringroup;
                $first_group->user_id = $user->id;
                $first_group->customer_groups_id = $group->id;
                $first_group->status = 1;
                $first_group->save();
            }
        }

        try{
            TempUser::updateOrCreate(
                ['user_id' => $user->id],
                ['temp_user_id' => $request->session()->get('temp_user_id')]
            );
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server Error'
            ], 500);
        }

        $this->send_verification_code($user);
        return response()->json([
            'success' => true,
            'phone' => $phone,
            'message' => 'A verification code has been sent to your phone.',
        ], 200);
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^(\+88)?01[3-9]\d{8}$/',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $phone = trim(str_replace(['-', ' ', '+88'], '', $request->phone));
        if (strlen($phone) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is invalid'
            ], 400);
        }

        $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Request'
            ], 400);
        }

        $this->send_verification_code($user);
        return response()->json([
            'success' => true,
            'message' => 'A verification code has been sent to your phone.',
        ], 200);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^(\+88)?01[3-9]\d{8}$/',
            'verification_code' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $phone = trim(str_replace('-','',str_replace('+88','',$request->phone)));
        if (strlen($phone) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is invalid'
            ], 400);
        }

        $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->verification_code != $request->verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 400);
        }

        if (app()->environment('production') && !is_null($user->temp_password)) {
            SmsUtility::user_created($user, $user->temp_password);
        }

        $user->email_verified_at = now();
        $user->recent_login = now();
        $user->verification_code = null;
        $user->temp_password = null;
        $user->save();

        remove_previous_cart($user->id);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Phone number verified successfully.',
        ], 200);
    }

    private function send_verification_code($user)
    {
        $code = rand(100000, 999999);
        $user->verification_code = $code;
        $user->save();
        if (addon_is_activated('otp_system') && app()->environment('production')) {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }
    }
}
