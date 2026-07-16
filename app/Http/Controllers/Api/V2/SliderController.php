<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Support\Facades\Cache;
use App\Http\Resources\V2\SliderCollection;

class SliderController extends Controller
{
    public function index()
    {
        return Cache::remember('app.home_slider_images_v2', 300, function () {
            $webImages =  json_decode(get_setting('home_slider_images'), true);
            $mobileImages =  json_decode(get_setting('home_slider_images_mobile'), true);
            $links =  json_decode(get_setting('home_slider_links'), true);
            $sliders = collect($mobileImages)->map(function ($mobileImage, $index) use ($webImages, $links) {
                return [
                    'photo'      => api_asset($mobileImage) ?? "",
                    'photo_web'  => api_asset($webImages[$index]) ?? "",
                    'url'        => $links[$index] ?? "",
                ];
            })->filter(function ($item) {
                return $item['photo'] || $item['photo_web'];
            })->values();

            return response()->json([
                'data' => $sliders,
                'version' => '2.0.0',
                'success' => true,
                'status' => 200
            ]);
        });
    }
}
