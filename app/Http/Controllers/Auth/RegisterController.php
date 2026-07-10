<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Customergroup;
use App\Models\BusinessSetting;
use App\Models\Customeringroup;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Controllers\OTPVerificationController;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

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
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|min:3|max:80',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user->email = $data['email'];
            $user->save();
        }
        else {
            $user->phone = normalizePhoneNumber($data['phone']);
            $user->verification_code = rand(100000, 999999);
            $user->save();
            if (addon_is_activated('otp_system')){
                $otpController = new OTPVerificationController;
                $otpController->send_code($user);
            }
        }

        if (!in_array(auth()->user()->user_type, ['admin', 'staff'])) {
            replace_temp_user_id(session('temp_user_id'), $user->id);
        }

        if(Cookie::has('referral_code')){
            $referral_code = Cookie::get('referral_code');
            $referred_by_user = User::where('referral_code', $referral_code)->first();
            if($referred_by_user != null){
                $user->referred_by = $referred_by_user->id;
                $user->save();
            }
        }

        return $user;
    }

    public function register(Request $request)
    {
        // dd($request->all());
        // Prevent bot registration using honeypot approach
        if(filled($request->agent)){
            flash('Registration Failed!');
            return back();
        }
        // End of honeypot approach

        $this->validator($request->all())->validate();

        $registerBy = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if ($registerBy == 'email') {
            $user = User::where('email', $request->email)->first();
        }
        else {
            $phone = normalizePhoneNumber($request->phone);
            if (strlen($phone) < 11) {
                flash('Invalid phone number.');
                return back();
            }
            $user = User::whereIn('phone', [$phone, '+88'.$phone])->first();
        }

        if ($user) {
            $user->name = $request->name ?? $user->name;
            $user->password = bcrypt($request->password);
            $user->save();
            return $this->verifyUser($user, $registerBy);
        }

        $request->merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user = $this->create($request->all());

        $group = Customergroup::orderBy('ordering', 'asc')->first();

        if($group->count() > 0){
            $first_group = new Customeringroup();
            $first_group->user_id = $user->id;
            $first_group->customer_groups_id = $group->id;
            $first_group->status = 1;
            $first_group->save();
        }

        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();


        $this->guard()->login($user);


        if($user->email != null){
            if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();

                flash(('Registration successful.'))->success();
            }
            else {
                event(new Registered($user));
                flash(('Registration successful. Please verify your email.'))->success();
            }
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    public function verifyUser(User $user, $contactType)
    {
        $otp = rand(100000, 999999);
        $user->verification_code = $otp;
        $user->recent_login = date('Y-m-d H:i:s');
        $user->save();
        $this->guard()->login($user);
        if (!in_array($user->user_type, ['admin', 'staff'])) {
            replace_temp_user_id(session('temp_user_id'), $user->id);
        }
        if (addon_is_activated('otp_system') && app()->environment('production') && $contactType == 'phone') {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        } elseif ($contactType == 'email') {
            $user->sendEmailVerificationNotification();
        }
        // return redirect()->route('verification');
        return view('otp_systems.frontend.user_verification', ['contact_type' => $contactType]);
    }

    protected function registered(Request $request, $user)
    {
        if ($user->email == null) {
            return redirect()->route('verification');
        }elseif(session('link') != null){
            return redirect(session('link'));
        }elseif (request()->has('redirect')) {
            return redirect(decrypt(request()->redirect));
        }else {
            return redirect()->route('home');
        }
    }
}
