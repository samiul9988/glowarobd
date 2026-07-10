<?php

namespace App\Http\Controllers;

use Nexmo;
use App\Models\User;
use App\Utility\SmsUtility;
use Illuminate\Http\Request;
use App\Models\OtpConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\PasswordReset;

class OTPVerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verification(Request $request){
        if (Auth::check() && Auth::user()->email_verified_at == null) {
            return view('otp_systems.frontend.user_verification');
        }
        else {
            flash('You have already verified your number')->warning();
            return redirect()->route('home');
        }
    }


    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function verify_phone(Request $request){
        $user = Auth::user();
        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d h:m:s');
            $user->save();

            flash('Your phone number has been verified successfully')->success();
            return redirect()->route('home');
        }
        else{
            flash('Invalid Code')->error();
            return back();
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function resend_verificcation_code(Request $request){
        $user = Auth::user();
        $user->verification_code = rand(100000,999999);
        $user->save();
        SmsUtility::phone_number_verification($user);

        return back();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // public function reset_password_with_code(Request $request){
    //     $phone = '+'.$request->country_code.$request->phone;
    //     if (($user = User::where('phone', $phone)->where('verification_code', $request->code)->first()) != null) {
    //         if($request->password == $request->password_confirmation){
    //             $user->password = Hash::make($request->password);
    //             $user->email_verified_at = date('Y-m-d h:m:s');
    //             $user->save();
    //             event(new PasswordReset($user));
    //             auth()->login($user, true);

    //             if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
    //             {
    //                 return redirect()->route('admin.dashboard');
    //             }
    //             return redirect()->route('home');
    //         }
    //         else {
    //             flash("Password and confirm password didn't match")->warning();
    //             return back();
    //         }
    //     }
    //     else {
    //         flash("Verification code mismatch")->error();
    //         return back();
    //     }
    // }

    public function reset_password_with_code(Request $request){
        // dd($request->all());
        $phone = '+'.$request->country_code.$request->phone;
        $user = User::where('phone', $phone)->where('verification_code', $request->code)->first();
        if ($user) {
            auth()->login($user, true);
            flash(('Account Verified. Don\'t forget to update your password'))->success();

            session()->forget('user_country_code_for_password_reset');
            session()->forget('user_phone_for_password_reset');
            if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
            {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('profile');
        }
        else {
            flash("Verification code mismatch")->error();
            return redirect()->route('password.request');
        }
    }

    /**
     * @param  User $user
     * @return void
     */

    public function send_code($user){
        SmsUtility::phone_number_verification($user);
    }

    /**
     * @param  Order $order
     * @return void
     */
    public function send_order_code($order){
        $phone = json_decode($order->shipping_address)->phone;
        if($phone != null){
            SmsUtility::order_placement($phone, $order);
        }
    }

    /**
     * @param  Order $order
     * @return void
     */
    public function send_delivery_status($order){
        $phone = json_decode($order->shipping_address)->phone;
        if($phone != null){
            SmsUtility::delivery_status_change($phone, $order);
        }
    }

    /**
     * @param  Order $order
     * @return void
     */
    public function send_payment_status($order){
        $phone = json_decode($order->shipping_address)->phone;
        if($phone != null){
            SmsUtility::payment_status_change($phone, $order);
        }
    }
}
