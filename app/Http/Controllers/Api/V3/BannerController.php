<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\BannerCollection;

class BannerController extends Controller
{

    public function index()
    {
        // return new BannerCollection(json_decode(get_setting('home_banner1_images'), true));
        $banner1_images = json_decode(get_setting('home_banner1_images_mobile'), true) ?? [];
        $banner1_links = json_decode(get_setting('home_banner1_links_mobile'), true) ?? [];
        if(empty(array_filter($banner1_images))) {
            $banner1_images = json_decode(get_setting('home_banner1_images'), true) ?? [];
            $banner1_links = json_decode(get_setting('home_banner1_links'), true) ?? [];
        }
        $banner1 = array_combine($banner1_images, $banner1_links);
        $banner1 = new BannerCollection($banner1);

        $banner2_images = json_decode(get_setting('home_banner2_images_mobile'), true) ?? [];
        $banner2_links = json_decode(get_setting('home_banner2_links_mobile'), true) ?? [];
        if(empty(array_filter($banner2_images))) {
            $banner2_images = json_decode(get_setting('home_banner2_images'), true) ?? [];
            $banner2_links = json_decode(get_setting('home_banner2_links'), true) ?? [];
        }
        $banner2 = array_combine($banner2_images, $banner2_links);
        $banner2 = new BannerCollection($banner2);

        $banner3_images = json_decode(get_setting('home_banner3_images_mobile'), true) ?? [];
        $banner3_links = json_decode(get_setting('home_banner3_links_mobile'), true) ?? [];
        if(empty(array_filter($banner3_images))) {
            $banner3_images = json_decode(get_setting('home_banner3_images'), true) ?? [];
            $banner3_links = json_decode(get_setting('home_banner3_links'), true) ?? [];
        }
        $banner3 = array_combine($banner3_images, $banner3_links);
        $banner3 = new BannerCollection($banner3);

        return response()->json([
            'banner1' => $banner1,
            'banner2' => $banner2,
            'banner3' => $banner3
        ]);

        // return new BannerCollection(json_decode(get_setting('home_banner1_images'), true));

    }
}
