<?php

namespace App\Http\Controllers\Api\V3;

use Exception;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\V3\BusinessSettingCollection;

class BusinessSettingController extends Controller
{
    public function index(Request $request)
    {
        if (filled($request->input('q'))) {
            $settings = Cache::remember('business_settings_' . md5($request->input('q')), now()->addHours(3), function () use ($request) {
                $queries = explode(',', $request->string('q'));
                if (count($queries) > 1) {
                    return BusinessSetting::whereIn('type', $queries)->get();
                }
                return BusinessSetting::where('type', $request->string('q'))->get();
            });
            return new BusinessSettingCollection($settings);
        }
        $settings = Cache::remember('all_business_settings', now()->addHours(3), function () {
            return BusinessSetting::all();
        });
        return new BusinessSettingCollection($settings);
    }

        /**
     * Home Category Store method
     */
    public function store_home_category(Request $request){
        try {
            $this->validate($request, [
                "home_categories" => "required|array",
                "collection_designs" => "required|array",
                "home_categories.*" => "integer",
                "collection_designs.*" => "integer",
            ]);

            $data = array_map(function ($category, $design) {
                return ["cid" => $category, "did" => $design];
            }, $request->input("home_categories"), $request->input("collection_designs"));

            BusinessSetting::where("type", "home_categories")->update([
                "value" => json_encode($data)
            ]);

            Cache::flush();
            flash(("Settings updated successfully"))->success();
            return back();
        } catch (Exception $e) {
            flash(("Request failed"))->error();
            return back();
        }

    }

    public function store_home_category_app(Request $request){
        try {
            $this->validate($request, [
                "home_categories" => "required|array",
                "collection_designs" => "required|array",
                "home_categories.*" => "integer",
                "collection_designs.*" => "integer",
            ]);

            $data = array_map(function ($category, $design) {
                return ["cid" => $category, "did" => $design];
            }, $request->input("home_categories"), $request->input("collection_designs"));

            BusinessSetting::updateOrCreate(
                ["type" => "home_categories_app"],
                ["value" => json_encode($data)]
            );

            Cache::flush();
            flash(("Settings updated successfully"))->success();
            return back();
        } catch (Exception $e) {
            flash(($e->getMessage()))->error();
            return back();
        }

    }

    function getDoctorsConsultation(Request $request) {
        $data = json_decode(get_setting('doctors_consultation'), true) ?? null;
        if($data) {
            $data['banner'] = isset($data['banner']) ? api_asset($data['banner']) : null;
        }
        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ]);
    }

    public function clearCache()
    {
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully'
        ]);
    }
}
