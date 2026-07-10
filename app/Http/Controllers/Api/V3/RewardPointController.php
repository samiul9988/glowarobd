<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\RewardPointLogCollection;
use App\Models\RewardPointLog;
use App\Models\RewardRedeemAction;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardPointController extends Controller
{
    public function rewardPoint()
    {
        if (get_setting('reward_point_system') == 1) {
            $user = Auth::guard('api')->user();
            if($user){
                return response()->json([
                    'point' => $user->point_balance,
                    'success' => true,
                    'status'  => 200,
                    'message' => 'user point balance'
                ]);
            } else{
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'status' => 404,
                    'message' => 'User not found'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'status'  => 502,
                'message' => 'Reward point setting not enable'
            ]);
        }
    }

    public function rewardPointLog()
    {
        if (get_setting('reward_point_system') == 1) {
            $id = Auth::guard('api')->id();
            $log = RewardPointLog::where('user_id', $id)->get();
        
            if($log){
                return new RewardPointLogCollection($log);
            } else{
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'status' => 404,
                    'message' => 'log not found'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'status'  => 502,
                'message' => 'Reward point setting not enable'
            ]);
        }
    }

    public function rewardPointRedeem(Request $request)
    {
        if (get_setting('reward_point_system') == 1) {

            $redeemaction = RewardRedeemAction::where('activity_type', 'checkout')->first();
            $point = intval($request->point);
            $user = Auth::guard('api')->user();
            $currentDateTime = new DateTime();
            $timestamp = $user->reward_point_expires_at;

            if (($user->point_balance >= $point) && ($currentDateTime <= new DateTime($timestamp))) {
                $rewardamount = convert_point_to_amount($redeemaction, $point);
                // $user->point_balance = $user->point_balance - $point;
                // if($user->save()){
                    return response()->json([
                        'amount'  => $rewardamount,
                        'success' => true,
                        'status'  => 200,
                        'message' => 'Point has been redeemed!'
                    ]);
                // }
            } else {
                return response()->json([
                    'success' => false,
                    'status'  => 416,
                    'message' => 'Given invalid point'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'status'  => 502,
                'message' => 'Reward point setting not enable'
            ]);
        }
    }

    

}
