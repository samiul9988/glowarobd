<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CategoryContent;
use App\Utility\CategoryUtility;
use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $categories = Category::with('parentCategory:id,name')->orderBy('order_level', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        if (filled($request->ctype)) {
            if ($request->ctype === 'parent'){
                $categories = $categories->where('parent_id', 0);
            } elseif ($request->ctype === 'child') {
                $categories = $categories->where('parent_id', '!=', 0);
            } elseif ($request->ctype === 'featured') {
                $categories = $categories->where('featured', 1);
            }
        }
        $categories = $categories->paginate(15);
        return view('backend.product.categories.index', compact('categories', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->get();

        // dd($categories);

        return view('backend.product.categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $category = new Category;
        $category->name = $request->name;
        $category->order_level = 0;
        if($request->order_level != null) {
            $category->order_level = $request->order_level;
        }
        $category->child_bg_color = $request->child_bg_color ?? null;
        $category->digital = $request->digital;
        $category->banner = json_encode($request->banner);
        $category->page_banner = json_encode($request->pageBanner);
        $category->icon = json_encode($request->icon);
        $category->featured_icon = json_encode($request->featured_icon);
        $category->bg_image = json_encode($request->bg_image);
        $category->app_slider = json_encode($request->app_slider);
        $category->app_banner1 = json_encode($request->app_banner1);
        $category->app_banner2 = json_encode($request->app_banner2);
        $category->app_featured_image = $request->app_featured_image;
        $category->app_home_page_image = $request->app_home_page_image;
        $category->meta_title = $request->meta_title;
        $category->meta_description = $request->meta_description;
        $category->status = $request->status ?? 1;
        if($request->variation_attributes!='')
            $category->variation_attributes = implode(',',$request->variation_attributes);

        if ($request->has('variation_color')) {
            $category->variation_color = 1;
        }

        if ($request->parent_id != "0") {
            $category->parent_id = $request->parent_id;

            $parent = Category::find($request->parent_id);
            $category->level = $parent->level + 1 ;
        }

        $category->slug = Category::generateUniqueSlug($request->slug ?: $request->name);

        if ($request->commision_rate != null) {
            $category->commision_rate = $request->commision_rate;
        }

        $category->save();

        $category->attributes()->sync($request->filtering_attributes);

        $category_translation = CategoryTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'category_id' => $category->id]);
        $category_translation->name = $request->name;
        $category_translation->save();

        $categoryFilePath = storage_path('app/public/categories/category.json');
        if (!file_exists($categoryFilePath) || file_exists($categoryFilePath)) {
            $rows = Category::all();
            $jsonRowData = $rows->toJson();
            if(!file_exists(storage_path('app/public/categories'))){
                mkdir(storage_path('app/public/categories'), 0775, true);
            }
            file_put_contents($categoryFilePath, $jsonRowData);
        }

        Cache::flush();
        Cache::forget('app.featured_categories_10');
        Cache::forget('app.featured_categories_');
        Cache::forget('app.home_categories');
        Cache::forget('app.top_categories');
        Cache::forget('all_categories');
        Cache::forget('firstLevelCat');
        for ($i = 0; $i <= 10; $i++) {
            Cache::forget("app.categories-$i");
        }

        flash(('Category has been inserted successfully'))->success();
        return redirect()->route('categories.index');
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
        $lang = $request->lang;
        $category = Category::findOrFail($id);
        // $banner = json_decode($category->banner, true);
        // dd($banner);
        //dd($category->attributes->pluck('id'));
        //dd(array_map('intval', explode(',',$category->variation_attributes)));
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->whereNotIn('id', CategoryUtility::children_ids($category->id, true))->where('id', '!=' , $category->id)
            ->orderBy('name','asc')
            ->get();

        return view('backend.product.categories.edit', compact('category', 'categories', 'lang'));
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
        $category = Category::findOrFail($id);
        $old_slug = $category->slug;
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $category->name = $request->name;
        }
        if($request->order_level != null) {
            $category->order_level = $request->order_level;
        }
        $category->variation_color = 0;
        $category->child_bg_color = $request->child_bg_color ?? null;
        $category->digital = $request->digital;
        $category->banner = json_encode($request->banner);
        $category->page_banner = json_encode($request->pageBanner);
        $category->icon = json_encode($request->icon);
        $category->featured_icon = json_encode($request->featured_icon);
        $category->bg_image = json_encode($request->bg_image);
        $category->app_slider = json_encode($request->app_slider);
        $category->app_banner1 = json_encode($request->app_banner1);
        $category->app_banner2 = json_encode($request->app_banner2);
        $category->app_featured_image = $request->app_featured_image;
        $category->app_home_page_image = $request->app_home_page_image;
        $category->meta_title = $request->meta_title;
        $category->meta_description = $request->meta_description;
        $category->status = $request->status ?? $category->status;
        if($request->variation_attributes!='')
            $category->variation_attributes = implode(',',$request->variation_attributes);

        if ($request->has('variation_color')) {
            $category->variation_color = 1;
        }
        $previous_level = $category->level;

        if ($request->parent_id != "0") {
            $category->parent_id = $request->parent_id;

            $parent = Category::find($request->parent_id);
            $category->level = $parent->level + 1 ;
        }
        else{
            $category->parent_id = 0;
            $category->level = 0;
        }

        if($category->level > $previous_level){
            CategoryUtility::move_level_down($category->id);
        }
        elseif ($category->level < $previous_level) {
            CategoryUtility::move_level_up($category->id);
        }

        if ($request->slug != $category->slug) {
            $category->slug = Category::generateUniqueSlug($request->slug ?: $request->name, $category->id);
        }


        if ($request->commision_rate != null) {
            $category->commision_rate = $request->commision_rate;
        }
        if($request->discount>0):
            $date_var = explode(" to ", $request->date_range);
            $category->start_date = strtotime($date_var[0]);
            $category->end_date   = strtotime( $date_var[1]);
            $category->discount   = $request->discount;
            $category->discount_type   = $request->discount_type;
            //$category->status   = 1;
        endif;

        $category->save();
        if($old_slug != $category->slug && filled($request->rewrite_url)){
            rewrite_url('category/'.$old_slug, 'category/'.$category->slug);
        }
        if($request->has('update_products_discount') && $request->discount>0):
            if($category->status == 1):
                save_product_discount($request, 'category_id', $category->id);
            endif;
        endif;
        $category->attributes()->sync($request->filtering_attributes);

        $category_translation = CategoryTranslation::firstOrNew(['lang' => $request->lang, 'category_id' => $category->id]);
        $category_translation->name = $request->name;
        $category_translation->save();

        Cache::flush();
        Cache::forget('app.featured_categories_10');
        Cache::forget('app.featured_categories_');
        Cache::forget('app.home_categories');
        Cache::forget('app.top_categories');
        Cache::forget('all_categories');
        Cache::forget('firstLevelCat');
        for ($i = 0; $i <= 10; $i++) {
            Cache::forget("app.categories-$i");
        }

        $categoryFilePath = storage_path('app/public/categories/category.json');
        if (!file_exists($categoryFilePath) || file_exists($categoryFilePath)) {
            $rows = Category::all();
            $jsonRowData = $rows->toJson();
            if(!file_exists(storage_path('app/public/categories'))){
                mkdir(storage_path('app/public/categories'), 0775, true);
            }
            file_put_contents($categoryFilePath, $jsonRowData);
        }

        flash(('Category has been updated successfully'))->success();
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
        $category = Category::findOrFail($id);
        $category->attributes()->detach();

        remove_rewrite_url('category/'.$category->slug);
        // Category Translations Delete
        foreach ($category->category_translations as $key => $category_translation) {
            $category_translation->delete();
        }

        foreach (Product::where('category_id', $category->id)->get() as $product) {
            $product->category_id = null;
            $product->save();
        }

        CategoryUtility::delete_category($id);
        Cache::forget('featured_categories');
        Cache::forget('app.featured_categories_10');
        Cache::forget('app.featured_categories_');
        Cache::forget('app.home_categories');
        Cache::forget('app.top_categories');
        Cache::forget('all_categories');
        Cache::forget('firstLevelCat');
        for ($i = 0; $i <= 10; $i++) {
            Cache::forget("app.categories-$i");
        }

        $categoryFilePath = storage_path('app/public/categories/category.json');
        if (!file_exists($categoryFilePath) || file_exists($categoryFilePath)) {
            $rows = Category::all();
            $jsonRowData = $rows->toJson();
            if(!file_exists(storage_path('app/public/categories'))){
                mkdir(storage_path('app/public/categories'), 0775, true);
            }
            file_put_contents($categoryFilePath, $jsonRowData);
        }

        Cache::flush();

        flash(('Category has been deleted successfully'))->success();
        return redirect()->route('categories.index');
    }

    public function updateFeatured(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->featured = $request->status;
        $category->save();
        Cache::forget('featured_categories');
        Cache::forget('app.featured_categories_10');
        Cache::forget('app.featured_categories_');
        Cache::forget('app.home_categories');
        Cache::forget('app.top_categories');
        Cache::forget('firstLevelCat');
        return 1;
    }

    public function update_status(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->status = $request->status;
        if($category->save()){
         update_product_discount($category, 'category_id');
            flash(('Category discount status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function editCategoryContent(Request $request, $id){
        $category = Category::with('subcategories.childrenCategories', 'content')->find($id);
        if(!$category){
            flash(('Category not found!'));
            return redirect()->route('categories.index');
        }else{
            return view('backend.product.categories.modify_content', compact('category'));
        }
    }

    public function updateCategoryContent(Request $request, $id){
        // dd($request->all());
        foreach ($request->types as $key => $type) {
            $lang = null;
            if(gettype($type) == 'array'){
                $lang = array_key_first($type);
                $type = $type[$lang];
                $catcontent = CategoryContent::where('category_id', $id)->where('type', $type)->where('lang',$lang)->first();
            }else{
                $catcontent = CategoryContent::where('category_id', $id)->where('type', $type)->first();
            }

            if($catcontent!=null){
                if(gettype($request[$type]) == 'array'){
                    $catcontent->value = json_encode($request[$type]);
                }else {
                    $catcontent->value = $request[$type];
                }
                $catcontent->lang = $lang;
                $catcontent->save();
            }else{
                $catcontent = new CategoryContent;
                $catcontent->category_id = $id;
                $catcontent->type = $type;
                if(gettype($request[$type]) == 'array'){
                    $catcontent->value = json_encode($request[$type]);
                }else {
                    $catcontent->value = $request[$type];
                }
                $catcontent->lang = $lang;
                $catcontent->save();
            }
        }
        Cache::flush();
        Cache::forget('app.featured_categories_10');
        Cache::forget('app.featured_categories_');
        Cache::forget('app.home_categories');
        Cache::forget('app.top_categories');
        Cache::forget('all_categories');
        Cache::forget('firstLevelCat');
        for ($i = 0; $i <= 10; $i++) {
            Cache::forget("app.categories-$i");
        }

        flash(("Settings updated successfully"))->success();
        return back();
    }

    public function fetchAll(Request $request)
    {
        $categories = Cache::remember('all_categories', now()->addHours(3), function () {
            return Category::pluck('name', 'id')->toArray();
        });
        return response()->json($categories);
    }
}
