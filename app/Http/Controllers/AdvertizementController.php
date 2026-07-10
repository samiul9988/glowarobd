<?php

namespace App\Http\Controllers;

use App\Models\Advertizement;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class AdvertizementController extends Controller
{
    protected $web_position_type = [
        'below_slider'          => 'Below Slider',
        'above_category'        => 'Above Category',
        'above_home_category'   => 'Above Home Category',
        'above_shop_by_concern' => 'Above Shop By Concern',
        'above_favorite_one'    => 'Above Favorite One',
    ];
    protected $app_position_type = [
        'below_slider'            => 'Below Slider',
        'above_category'          => 'Above Category',
        'above_featured_category' => 'Above Featured Category',
        'below_home_category'     => 'Below Home Category',
        'above_favorite_one'      => 'Above Favorite One',
    ];

    public function index()
    {
        $advertizements = Advertizement::all();
        return view('backend.setup_configurations.advertizement.index', compact('advertizements'));
    }

    public function create()
    {
        $products = Product::where('published',1)->select('id','name')->get();
        $categories = Category::where('parent_id', 0)->where('digital', 0)->with('childrenCategories')->get();
        $brands = Brand::select(['id', 'name'])->get();
        $web_positions = $this->web_position_type;
        $app_positions = $this->app_position_type;

        return view('backend.setup_configurations.advertizement.create',compact(['web_positions', 'app_positions', 'products', 'categories', 'brands']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'position' => 'required',
            'ads_type' => 'required',
            'date_range' => 'required',
        ]);
        $advertizement = new Advertizement();
        $advertizement->position = strval($request->input('position'));
        $advertizement->image = $request->input('image');
        $advertizement->ads_type = strval($request->input('ads_type'));

        $link_type = $request->input('link_type');
        if($link_type != null){
            $advertizement->link_type = $link_type;
            if($link_type == 'product'){
                $advertizement->link = $request->input('product_id') ? intval($request->input('product_id')) : null;
            } else if($link_type == 'category'){
                $advertizement->link = $request->input('category_id') ? intval($request->input('category_id')) : null;
            } else if($link_type == 'brand'){
                $advertizement->link = $request->input('brand_id') ? intval($request->input('brand_id')) : null;
            } else if($link_type == 'tag'){
                $advertizement->link = $request->input('tag') ? strval($request->input('tag')) : null;
            } else if($link_type == 'custom'){
                $advertizement->link = $request->input('link') ? strval($request->input('link')) : null;
            }
        }

        $advertizement->code = $request->input('code') ? strval($request->input('code')) : null;
        $advertizement->status = $request->input('status') ?? 0;

        if ($request->date_range != null) {
            $date_var                   = explode(" to ", $request->date_range);
            $advertizement->start_date  = date("Y-m-d H:i:s", strtotime($date_var[0]));
            $advertizement->end_date    = date("Y-m-d H:i:s", strtotime($date_var[1]));
        }
        $advertizement->save();
        flash(('Advertizement has been inserted successfully'))->success();
        return redirect()->route('ads.index');
    }

    public function edit($id)
    {
        $advertizement = Advertizement::findOrFail($id);
        $web_positions = $this->web_position_type;
        $app_positions = $this->app_position_type;
        $products = Product::where('published', 1)->select('id', 'name')->get();
        $categories = Category::where('parent_id', 0)->where('digital', 0)->with('childrenCategories')->get();
        $brands = Brand::select(['id', 'name'])->get();

        return view('backend.setup_configurations.advertizement.edit', compact(['advertizement', 'web_positions', 'app_positions', 'products','categories', 'brands']));
    }

    public function update(Request $request)
    {
        $request->validate([
            'position' => 'required',
            'ads_type' => 'required',
            'date_range' => 'required',
        ]);

        $advertizement = Advertizement::where('id', $request->id)->first();
        $advertizement->position = strval($request->input('position'));
        $advertizement->image = $request->input('image');
        $advertizement->ads_type = strval($request->input('ads_type'));

        $link_type = $request->input('link_type');
        if ($link_type != null) {
            $advertizement->link_type = $link_type;
            if ($link_type == 'product') {
                $advertizement->link = $request->input('product_id') ? intval($request->input('product_id')) : null;
            } else if ($link_type == 'category') {
                $advertizement->link = $request->input('category_id') ? intval($request->input('category_id')) : null;
            } else if ($link_type == 'brand') {
                $advertizement->link = $request->input('brand_id') ? intval($request->input('brand_id')) : null;
            } else if ($link_type == 'custom') {
                $advertizement->link = $request->input('link') ? strval($request->input('link')) : null;
            }
        }

        $advertizement->code = strval($request->input('code'));

        if ($request->date_range != null) {
            $date_var                   = explode(" to ", $request->date_range);
            $advertizement->start_date  = date("Y-m-d H:i:s", strtotime($date_var[0]));
            $advertizement->end_date    = date("Y-m-d H:i:s", strtotime($date_var[1]));
        }
        $advertizement->save();
        flash(('Advertizement has been updated successfully'))->success();
        return redirect()->route('ads.index');
    }

    public function update_status(Request $request)
    {
        $advertizement = Advertizement::findOrFail($request->id);
        $advertizement->status = $request->status;
        if ($advertizement->save()) {
            return 1;
        }
        return 0;
    }

    public function destroy($id)
    {
        Advertizement::where('id', $id)->delete();
        flash(('Advertizement has been deleted successfully'))->success();
        return redirect()->route('ads.index');
    }
}
