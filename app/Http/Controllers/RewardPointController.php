<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\RewardEarnAction;
use App\Models\RewardRedeemAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RewardPointController extends Controller
{
    public function rewardPointSettings(){
        $rewardEarnActions = RewardEarnAction::where('status', 1)->get();
        $rewardRedeemActions = RewardRedeemAction::where('status', 1)->get();
        return view('backend.setup_configurations.reward_point_settings', compact('rewardEarnActions', 'rewardRedeemActions'));
    }

    /**
     * Update the settingss for reward point system.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRewardPointSettings(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $business_settings = BusinessSetting::where('type', $type)->first();
            if($business_settings!=null){
                if(gettype($request[$type]) == 'array'){
                    $business_settings->value = json_encode($request[$type]);
                }else {
                    $business_settings->value = $request[$type];
                }
                $business_settings->save();
            }else{
                $business_settings = new BusinessSetting;
                $business_settings->type = $type;
                if(gettype($request[$type]) == 'array'){
                    $business_settings->value = json_encode($request[$type]);
                }else {
                    $business_settings->value = $request[$type];
                }
                $business_settings->save();
            }
        }

        Cache::flush();

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function editEarnRewardAction(Request $request, $id){

        $earnAction = RewardEarnAction::find($id);

        if($earnAction){
            if($request->has('title')){
                $earnAction->activity_title = $request->title;
            }
            $earnAction->earn_point = $request->earn_point;
            $earnAction->spent_amount = $request->spent_amount;
            if($request->has('validity')){
                $earnAction->validity = $request->validity;
            }
            $earnAction->save();

            flash(("Activity action updated successfully"))->success();
            return back();
        }else{
            flash(("Activity action not found"))->error();
            return back();
        }
    }

    public function editRedeemRewardAction(Request $request, $id){
        $redeemAction = RewardRedeemAction::find($id);

        if($redeemAction){
            if($request->has('title')){
                $redeemAction->activity_title = $request->title;
            }
            $redeemAction->spent_point = $request->spent_point;
            $redeemAction->earn_amount = $request->earn_amount;
            $redeemAction->save();

            flash(("Redeem action updated successfully"))->success();
            return back();
        }else{
            flash(("Redeem action not found"))->error();
            return back();
        }
    }
}
