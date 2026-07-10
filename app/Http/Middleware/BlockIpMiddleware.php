<?php

namespace App\Http\Middleware;

use App\Models\BlockIp;
use App\Models\SmsLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class BlockIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
         if(@$user->user_type != 'admin'){
            $today = Carbon::today();
            $smsLog = SmsLog::where('ip', request()->ip())->whereDate('created_at', $today)->count();
            if($smsLog > 2){
                $block = BlockIp::where('ip', $request->ip())->first();
                if(!isset($block)){
                    $blockIp = new BlockIp();
                    $blockIp->user_id = @$user->id ?? null;
                    $blockIp->ip = request()->ip();
                    $blockIp->reason = 'Too many send code by sms';
                    $blockIp->save();
                }
            }
        }

        // $block = BlockIp::where('ip', $request->ip())->count();
        // if($block > 0){
        //     if($request->wantsJson()){
        //         return response()->json([
        //             'status' => false,
        //             'code'  => 203,
        //             'message' => 'You are Blocked !!!'
        //         ]);
        //     }else{
        //         return redirect()->route('ip.blocked');
        //     }
        // };
        
        return $next($request); 
    }
}
