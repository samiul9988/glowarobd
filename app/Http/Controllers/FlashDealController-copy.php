<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\FlashDeal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\FlashDealProduct;
use Illuminate\Support\Facades\Log;
use App\Models\FlashDealTranslation;

class FlashDealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $flash_deals = FlashDeal::withCount('flash_deal_products')->orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $flash_deals = $flash_deals->where('title', 'like', '%'.$sort_search.'%');
        }
        $flash_deals = $flash_deals->paginate(15);
        return view('backend.marketing.flash_deals.index', compact('flash_deals', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.marketing.flash_deals.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        dd($request->all());
        $flash_deal = new FlashDeal;
        $flash_deal->title = $request->title;
        $flash_deal->text_color = $request->text_color;

        $date_var               = explode(" to ", $request->date_range);
        $flash_deal->start_date = strtotime($date_var[0]);
        $flash_deal->end_date   = strtotime( $date_var[1]);

        $flash_deal->background_color = $request->background_color;
        $flash_deal->slug = strtolower(str_replace(' ', '-', $request->title).'-'.Str::random(5));
        $flash_deal->banner = $request->banner;
        $flash_deal->desktop_banner = $request->desktopBanner;
        if($flash_deal->save()){
            try {
                $removedData = $this->removeFromExistingDeals($request->products ?? []);
            } catch (\Exception $e) {
                Log::error('Error removing products from existing deals: ' . $e->getMessage());
            }
            foreach ($request->products as $key => $product) {
                $flash_deal_product = new FlashDealProduct;
                $flash_deal_product->flash_deal_id = $flash_deal->id;
                $flash_deal_product->product_id = $product;
                $flash_deal_product->quantity = ($request['quantity_'.$product] ?? 0) + ($removedData[$product] ?? 0);
                $flash_deal_product->save();

                $root_product = Product::findOrFail($product);
                $root_product->discount = $request['discount_'.$product];
                $root_product->discount_type = $request['discount_type_'.$product];
                $root_product->discount_start_date = strtotime($date_var[0]);
                $root_product->discount_end_date   = strtotime( $date_var[1]);
                $root_product->save();
            }

            // $flash_deal_translation = FlashDealTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'flash_deal_id' => $flash_deal->id]);
            // $flash_deal_translation->title = $request->title;
            // $flash_deal_translation->save();

            flash(('Flash Deal has been inserted successfully'))->success();
            return redirect()->route('flash_deals.index');
        }
        else{
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    private function removeFromExistingDeals(array $products = []){
        if (empty($products)) {
            return [];
        }
        $data = [];
        FlashDealProduct::whereIn('product_id', $products)->each(function($item) use (&$data){
            $data[$item->product_id] = $item->quantity;
            $item->delete();
        });
        return $data;
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
        $lang       = $request->lang;
        $flash_deal = FlashDeal::findOrFail($id);
        return view('backend.marketing.flash_deals.edit', compact('flash_deal','lang'));
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
        //dd($request);
        $flash_deal = FlashDeal::findOrFail($id);

        $flash_deal->text_color = $request->text_color;

        $date_var               = explode(" to ", $request->date_range);
        $flash_deal->start_date = strtotime($date_var[0]);
        $flash_deal->end_date   = strtotime( $date_var[1]);

        $flash_deal->background_color = $request->background_color;

        if($request->lang == env("DEFAULT_LANGUAGE")){
          $flash_deal->title = $request->title;
          if (($flash_deal->slug == null) || ($flash_deal->title != $request->title)) {
              $flash_deal->slug = strtolower(str_replace(' ', '-', $request->title) . '-' . Str::random(5));
          }
        }

        $flash_deal->banner = $request->banner;
        $flash_deal->desktop_banner = $request->desktopBanner;
        foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
            $flash_deal_product->delete();
        }
        if($flash_deal->save()){
            try {
                $removedData = $this->removeFromExistingDeals($request->products ?? []);
            } catch (\Exception $e) {
                Log::error('Error removing products from existing deals: ' . $e->getMessage());
            }
            foreach ($request->products as $key => $product) {
                $flash_deal_product = new FlashDealProduct;
                $flash_deal_product->flash_deal_id = $flash_deal->id;
                $flash_deal_product->product_id = $product;
                $flash_deal_product->quantity = ($request['quantity_'.$product] ?? 0) + ($removedData[$product] ?? 0);
                $flash_deal_product->save();
                if($flash_deal->status==1):
                    $root_product = Product::findOrFail($product);
                    $root_product->discount = $request['discount_'.$product];
                    $root_product->discount_type = $request['discount_type_'.$product];
                    $root_product->discount_start_date = strtotime($date_var[0]);
                    $root_product->discount_end_date   = strtotime( $date_var[1]);
                    $root_product->min_order_amount = 0;
                    $root_product->max_qty = 0;
                    $root_product->save();
                endif;
            }

            // $sub_category_translation = FlashDealTranslation::firstOrNew(['lang' => $request->lang, 'flash_deal_id' => $flash_deal->id]);
            // $sub_category_translation->title = $request->title;
            // $sub_category_translation->save();

            flash(('Flash Deal has been updated successfully'))->success();
            return back();
        }
        else{
            flash(('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $flash_deal = FlashDeal::findOrFail($id);
        foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
            $flash_deal_product->delete();
        }

        foreach ($flash_deal->flash_deal_translations as $key => $flash_deal_translation) {
            $flash_deal_translation->delete();
        }

        FlashDeal::destroy($id);
        flash(('FlashDeal has been deleted successfully'))->success();
        return redirect()->route('flash_deals.index');
    }

    public function update_status(Request $request)
    {
        $flash_deal = FlashDeal::findOrFail($request->id);
        $flash_deal->status = $request->status;
        if($flash_deal->save()){
            update_flash_deal_discount($flash_deal);
            flash(('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function update_featured(Request $request)
    {
        // foreach (FlashDeal::all() as $key => $flash_deal) {
        //     $flash_deal->featured = 0;
        //     $flash_deal->save();
        // }
        $featureType = $request->type ?? 'web'; // 'web' or 'app'
        $flash_deal = FlashDeal::findOrFail($request->id);
        if ($featureType === 'web') {
            $flash_deal->featured = $request->featured;
        } elseif ($featureType === 'app') {
            $flash_deal->app_featured = $request->featured;
        }
        if($flash_deal->save()){
            flash(('Flash deal status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function product_discount(Request $request){
        $product_ids = $request->product_ids;
        return view('backend.marketing.flash_deals.flash_deal_discount', compact('product_ids'));
    }

    public function product_discount_edit(Request $request){
        $product_ids = $request->product_ids;
        $flash_deal_id = $request->flash_deal_id;
        return view('backend.marketing.flash_deals.flash_deal_discount_edit', compact('product_ids', 'flash_deal_id'));
    }

    public function is_exist_in_any_deals($id){
        $flash_deal_product = FlashDealProduct::where('product_id', $id)->first();

        if($flash_deal_product){
            return response()->json(['success' => true, 'exist' => true, 'title' => $flash_deal_product->flash_deals->title]);
        }
        return response()->json(['success' => true, 'exist' => false, 'title' => '']);
    }
}
