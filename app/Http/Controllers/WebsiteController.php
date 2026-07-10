<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\FlashDeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebsiteController extends Controller
{
	public function header(Request $request)
	{
		return view('backend.website_settings.header');
	}
	public function dashboard()
	{
		$modules = \App\Models\ShortcutModule::get();
		$shortcuts = \App\Models\Shortcut::with('module:id,name')->get();
		return view('backend.website_settings.dashboard', compact('modules', 'shortcuts'));
	}
	public function footer(Request $request)
	{
		$lang = $request->lang;
		return view('backend.website_settings.footer', compact('lang'));
	}
	public function pages(Request $request)
	{
		return view('backend.website_settings.pages.index');
	}
	public function appearance(Request $request)
	{
        $products = Cache::remember('appearance_products', now()->addHours(3), function () {
            return Product::published()->where('approved',1)->orderBy('created_at', 'desc')->pluck('name', 'id');
        });
        $brands = Cache::remember('appearance_brands', now()->addHours(3), function () {
            return Brand::orderBy('created_at', 'desc')->pluck('name', 'id');
        });
        $categories = Cache::remember('appearance_categories', now()->addHours(3), function () {
            return Category::where('parent_id', 0)->where('digital', 0)->with('childrenCategories')->get();
        });
        $today = strtotime(date('Y-m-d H:i:s'));
        $flash_deals = FlashDeal::query()
            ->where('status', 1)->where('start_date', "<=", $today)
            ->where('end_date', ">", $today)->orderBy('created_at', 'desc')
            ->pluck('title', 'id');
		return view('backend.website_settings.appearance',compact('products', 'categories', 'flash_deals', 'brands'));
	}

	public function global_seo(Request $request)
	{
		return view('backend.website_settings.global_seo');
	}
}
