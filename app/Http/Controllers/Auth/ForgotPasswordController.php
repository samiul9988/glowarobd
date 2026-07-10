<?php

namespace App\Http\Controllers\Auth;

use Mail;
use App\Models\User;
use App\Utility\SmsUtility;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Mail\SecondEmailVerifyMailManager;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // dd($request->all());
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                if($user->banned){
                    if($request->ajax()){
                        return response()->json([
                            'success' => false,
                            'message' => ('Your account has been banned')
                        ]);
                    }
                    flash(('Your account has been banned'))->error();
                    return back();
                }
                $user->verification_code = rand(100000,999999);
                $user->save();

                $array['view'] = 'emails.verification';
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['subject'] = ('Password Reset');
                $array['content'] = $user->verification_code;

                Mail::to($user->email)->queue(new SecondEmailVerifyMailManager($array));

                Session::put('user_email_for_password_reset', $user->email);

                if($request->ajax()){
                    return response()->json([
                        'success' => true,
                        'message' => ('OTP sent successfully')
                    ]);
                }
                return view('auth.passwords.reset');
            }
            else {
                flash(('No account exists with this email'))->error();
                return back();
            }
        }
        else{
            $number = str_replace(' ', '', $request->email);
            $number = str_replace('-', '', $number);
            $phone = '+'.$request->country_code.$number;
            // dd($phone);
            $user = User::where('phone', $phone)->first();
            if ($user) {
                if($user->banned){
                    if($request->ajax()){
                        return response()->json([
                            'success' => false,
                            'message' => ('Your account has been banned')
                        ]);
                    }
                    flash(('Your account has been banned'))->error();
                    return back();
                }
                $user->verification_code = rand(100000,999999);
                $user->save();
                SmsUtility::password_reset($user);

                Session::put('user_phone_for_password_reset', $number);
                Session::put('user_country_code_for_password_reset', $request->country_code);

                if($request->ajax()){
                    return response()->json([
                        'success' => true,
                        'message' => ('Password reset code sent to your phone')
                    ]);
                }
                return view('otp_systems.frontend.auth.passwords.reset_with_phone');
            }
            else {
                flash(('No account exists with this phone number'))->error();
                return back();
            }
        }
    }
}
