<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BrandTranslation;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $brands = Brand::with('logo')->orderBy('name', 'asc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $brands = $brands->where('name', 'like', '%'.$sort_search.'%');
        }
        $brands = $brands->paginate(15);
        return view('backend.product.brands.index', compact('brands', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $brand = new Brand;
        $brand->name = $request->name;
        $brand->meta_title = $request->meta_title;
        $brand->meta_description = $request->meta_description;
        $brand->slug = Brand::generateUniqueSlug($request->slug ?: $request->name);

        if($request->discount>0):
            $date_var = explode(" to ", $request->date_range);
            $brand->start_date = strtotime($date_var[0]);
            $brand->end_date   = strtotime( $date_var[1]);
            $brand->discount   = $request->discount;
            $brand->discount_type   = $request->discount_type;
        endif;

        $brand->logo = $request->logo;
        $brand->page_banner = $request->page_banner;
        $brand->status = $request->status ?? 1; // Default to active if not set
        $brand->save();
        if($request->discount>0):
            save_product_discount($request, 'brand_id', $brand->id);
        endif;

        $brand_translation = BrandTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'brand_id' => $brand->id]);
        $brand_translation->name = $request->name;
        $brand_translation->save();

        Cache::flush();
        flash(('Brand has been inserted successfully'))->success();
        return redirect()->route('brands.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang   = $request->lang;
        $brand  = Brand::findOrFail($id);
        return view('backend.product.brands.edit', compact('brand','lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $brand = Brand::findOrFail($id);
        $old_slug = $brand->slug;
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $brand->name = $request->name;
        }
        if($request->discount>0):
        $date_var = explode(" to ", $request->date_range);
        $brand->start_date = strtotime($date_var[0]);
        $brand->end_date   = strtotime( $date_var[1]);
        $brand->discount   = $request->discount;
        $brand->discount_type   = $request->discount_type;
        endif;
        $brand->meta_title = $request->meta_title;
        $brand->meta_description = $request->meta_description;
        if ($request->slug != $brand->slug) {
            $brand->slug = Brand::generateUniqueSlug($request->slug ?: $request->name, $brand->id);
        }
        $brand->logo = $request->logo;
        $brand->page_banner = $request->page_banner;
        $brand->status = $request->status ?? 1; // Default to active if not set
        $brand->save();

        if($old_slug != $brand->slug && filled($request->rewrite_url)){
            rewrite_url('brand/'.$old_slug, 'brand/'.$brand->slug);
        }
        if($request->has('update_products_discount') && $request->discount>0):
            if($brand->status == 1):
                save_product_discount($request, 'brand_id', $brand->id);
            endif;
        endif;

        $brand_translation = BrandTranslation::firstOrNew(['lang' => $request->lang, 'brand_id' => $brand->id]);
        $brand_translation->name = $request->name;
        $brand_translation->save();

        Cache::flush();
        flash(('Brand has been updated successfully'))->success();
        return back();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        Product::where('brand_id', $brand->id)->delete();
        foreach ($brand->brand_translations as $key => $brand_translation) {
            $brand_translation->delete();
        }
        remove_rewrite_url('brand/'.$brand->slug);
        Brand::destroy($id);

        Cache::flush();
        flash(('Brand has been deleted successfully'))->success();
        return redirect()->route('brands.index');

    }
    public function update_status(Request $request)
    {
        $brand_discount = Brand::findOrFail($request->id);
        if ($request->has('status')) {
            $brand_discount->status = $request->status;
            $brand_discount->save();
            update_product_discount($brand_discount, 'brand_id');
            return 1;
            flash('Brand discount status updated successfully')->success();
        }
        elseif ($request->has('top')) {
            $brand_discount->top = $request->top;
            $brand_discount->save();
            return 1;
        }
        return 0;
    }

    public function fetchAll(Request $request)
    {
        $brands = Cache::remember('all_brands', now()->addHours(3), function () {
            return Brand::pluck('name', 'id')->toArray();
        });
        return response()->json($brands);
    }
}
