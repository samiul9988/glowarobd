<?php

namespace App\Http\Controllers\Auth;

use App\Models\Cart;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        // dd($provider, $request->all());
        try {
            if($provider == 'twitter'){
                $user = Socialite::driver('twitter')->user();
            }
            else{
                $user = Socialite::driver($provider)->stateless()->user();
            }
        } catch (\Exception $e) {
            flash("Something Went wrong. Please try again.")->error();
            return redirect()->route('user.login');
        }

        // check if they're an existing user
        $existingUser = User::where('provider_id', $user->id)->first();

        if($existingUser){
            // log them in
            auth()->login($existingUser, true);
        } else {
            // create a new user
            $newUser                  = new User;
            $newUser->name            = $user->name;
            $newUser->email           = $user->email;
            $newUser->email_verified_at = date('Y-m-d H:m:s');
            $newUser->provider_id     = $user->id;
            $newUser->save();

            $customer = new Customer;
            $customer->user_id = $newUser->id;
            $customer->save();

            auth()->login($newUser, true);
        }
        if(session('link') != null){
            return redirect(session('link'));
        }
        else{
            return redirect()->route('dashboard');
        }
    }

    public function verify_otp_form(Request $request)
    {
        try {
            $userData = decrypt($request->hash ?? '');
            [$userId, $contact] = explode('|', $userData);
            $user = User::find($userId);
            if (!$user) {
                flash('Invalid data provided.')->error();
                return redirect()->route('user.login');
            }
        } catch (\Exception $e) {
            flash('Invalid data provided.')->error();
            return redirect()->route('user.login');
        }

        return view('frontend.otp_login_form', ['user_id' => $userId, 'contact' => $contact]);
    }

    public function verify_and_login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'code' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            flash('Invalid user.')->error();
            return redirect()->route('user.login');
        }

        if (!is_null($user->verification_code) && $request->code === $user->verification_code) {
            auth()->login($user, true);
            $user->verification_code = null;
            $user->email_verified_at = now();
            $user->save();
            return $this->authenticated();
        } else {
            flash('Invalid OTP. Please try again.')->error();
            return redirect()->back()->withInput();
        }
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $loginType = filled($request->phone) ? 'phone' : 'email';
        if ($loginType === 'phone') {
            $phone = str_replace(['+88', '-', ' '], '', $request->phone);
            $user = User::whereIn('phone', [$phone, "+88".$phone])->first();
        } else {
            $user = User::where('email', $request->email)->first();
        }

        if ($user && Hash::check($request->password, $user->password)) {
            auth()->login($user, $request->filled('remember'));
            return $this->authenticated();
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        return $request->validate([
            'email'    => 'required_without:phone|email',
            'phone'    => 'required_without:email',
            'password' => 'required|string',
        ]);
    }

    public function authenticated()
    {
        if(auth()->user()->banned){
            request()->merge([
                'banned' => true,
            ]);
            return $this->logout(request());
        }

        if (!in_array(auth()->user()->user_type, ['admin', 'staff'])) {
            replace_temp_user_id(session('temp_user_id'), auth()->id());
        }

        $recent_login = User::find(auth()->user()->id);
        $recent_login->recent_login = date('Y-m-d H:i:s');
        $recent_login->update();

        if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') {
            return redirect()->route('admin.dashboard');
        } else {
            if(!empty(session('link'))){
                return redirect(session('link'));
            } else {
                if (!empty(request()->redirect)){
                    try {
                        $redirectTo = decrypt(request()->redirect);
                        if (is_url($redirectTo)) {
                            return redirect($redirectTo);
                        }
                    } catch (\Exception $e) {
                        // If decryption fails, ignore and redirect to default
                    }
                }
                //purchase_history
                if(auth()->user()->user_type == 'customer'){
                    return redirect('/purchase_history');
                }else{
                    return redirect()->route('dashboard');
                }
            }
        }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        flash(('Invalid login credentials'))->error();
        return back();
    }

    public function logout(Request $request)
    {
        if(auth()->user() != null && (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')){
            $redirect_route = 'login';
        }
        else{
            $redirect_route = 'home';
        }

        //User's Cart Delete
        if(auth()->user()){
            Cart::where('user_id', auth()->user()->id)->delete();
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        if($request->banned){
            flash('Your account has been banned. Please contact the administrator.')->error();
        }
        return $this->loggedOut($request) ?: redirect()->route($redirect_route);
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
