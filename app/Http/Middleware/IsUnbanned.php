<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class IsUnbanned
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->banned) {

            $redirect_to = "";
            if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff'){
                $redirect_to = "login";
            }else{
                $redirect_to = "user.login";
            }

            auth()->logout();
            $request->session()->invalidate();

            flash(('Your account has been banned!'))->error();


            return redirect()->route($redirect_to);
        }

        return $next($request);
    }
}
