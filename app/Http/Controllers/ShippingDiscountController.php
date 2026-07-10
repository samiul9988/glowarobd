<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ShippingDiscount;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $discounts = ShippingDiscount::with('zone')->paginate();
        return view('backend.marketing.shipping.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $zones = DB::table('shipping_zones')->select('id','title')->get();
        return view('backend.marketing.shipping.create', compact('zones'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'zone_id' => 'required',
            'type_ids' => 'required_if:type,product,type,brand,type,category',
            'threshold_amount' => 'required',
            's_charge' => 'required',
            'date_range' => 'required',
            'status' => 'required'
        ]);


        $checkCountWithZone = ShippingDiscount::where('status',1)->where('zone_id',$request->zone_id)->count();
        if($checkCountWithZone > 0){
            flash(('Discount already exists for this zone'))->error();
            return redirect()->route('ship_discounts.index');
        }

        $date_var = explode(" - ", $request->date_range);

        $sDiscount = new ShippingDiscount;
        $sDiscount->user_id = auth()->user()->id;
        $sDiscount->zone_id = $request->zone_id;
        $sDiscount->type = $request->type;
        $sDiscount->details = !empty($request->type_ids) ? json_encode($request->type_ids) : null;
        $sDiscount->s_charge = $request->s_charge;
        $sDiscount->threshold_amount = $request->threshold_amount;
        $sDiscount->start_date = strtotime($date_var[0]);
        $sDiscount->end_date = strtotime($date_var[1]);
        $sDiscount->status = $request->status;
        $sDiscount->save();

        flash(('Ship Discount has been created successfully'))->success();
        return redirect()->route('ship_discounts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $discount = ShippingDiscount::find($id);
        $zones = DB::table('shipping_zones')->select('id','title')->get();
        return view('backend.marketing.shipping.edit', compact('discount', 'zones'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $request->validate([
            'type' => 'required',
            'zone_id' => 'required|unique:shipping_discounts,zone_id,'.$id,
            'type_ids' => 'required_if:type,product,type,brand,type,category',
            'threshold_amount' => 'required',
            's_charge' => 'required',
            'date_range' => 'required',
            'status' => 'required'
        ]);

        // $checkCountWithZone = ShippingDiscount::where('status',1)->where('zone_id',$request->zone_id)->count();
        // if($checkCountWithZone > 0){
        //     flash(('Discount already exists for this zone'))->error();
        //     return redirect()->route('ship_discounts.index');
        // }

        $date_var = explode(" - ", $request->date_range);

        $sDiscount = ShippingDiscount::find($id);
        $sDiscount->zone_id = $request->zone_id;
        $sDiscount->type = $request->type;
        $sDiscount->details = !empty($request->type_ids) ? json_encode($request->type_ids) : null;
        $sDiscount->s_charge = $request->s_charge;
        $sDiscount->threshold_amount = $request->threshold_amount;
        $sDiscount->start_date = strtotime($date_var[0]);
        $sDiscount->end_date = strtotime($date_var[1]);
        $sDiscount->status = $request->status;
        $sDiscount->save();

        flash(('Ship Discount has been updated successfully'))->success();
        return redirect()->route('ship_discounts.index');
    }

    public function change_status(Request $request) {

        $validator = Validator::make(request()->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => $validator->errors(),
                'result' => false
            ]);
        }

        // if($request->status == 1){
        //     ShippingDiscount::where('status',1)->update(['status' => 0]);
        // }
        $sDiscount = ShippingDiscount::find($request->id);
        $sDiscount->status = $request->status;

        $sDiscount->save();
        return 1;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ShippingDiscount::find($id)->delete();
        return redirect()->route('ship_discounts.index');
    }

    public function get_discount_dropdown(Request $request){
        if($request->type == "product") {
            if(Auth::user()->user_type == 'seller') {
                $products = filter_products(\App\Models\Product::where('user_id', Auth::user()->id))->get();
            } else {
                $admin_id = \App\Models\User::where('user_type', 'admin')->first()->id;
                $products = filter_products(\App\Models\Product::where('user_id', $admin_id))->get();
            }

            return view('partials.shipping.product_dropdown', compact('products'));
        }elseif($request->type == "brand"){
            $brands = Brand::all();
            return view('partials.shipping.brand_dropdown', compact('brands'));
        }elseif($request->type == "category"){
            $categories = Category::all();
            return view('partials.shipping.category_dropdown', compact('categories'));
        }else{
            flash(('Something Missing'))->error();
            return redirect()->route('ship_discounts.index');
        }
    }
}
