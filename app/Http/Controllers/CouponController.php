<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customergroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;


class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::query()
            ->withCount('usage')
            ->latest()
            ->get();
        return view('backend.marketing.coupons.index', compact('coupons'));
    }

    public function sellerIndex()
    {
        $coupons = Coupon::where('user_id', Auth::id())
            ->orderBy('id','desc')
            ->get();
        return view('frontend.user.seller.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.marketing.coupons.create');
    }

    public function sellerCreate()
    {
        return view('frontend.user.seller.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            if(count(Coupon::where('code', $request->coupon_code)->get()) > 0){
                flash(('Coupon already exist for this coupon code'))->error();
                return back();
            }

            $request->validate([
                'coupon_for' => 'nullable|string',
                'group_ids' => 'nullable|required_if:coupon_for,customer_group|array',
                'group_ids.*' => 'exists:customer_groups,id',
            ]);

            $coupon = new Coupon;
            $coupon->user_id = User::where('user_type', 'admin')->first()->id;
            $coupon = $this->setCouponData($request, $coupon);
            $coupon->save();

            flash(('Coupon has been saved successfully'))->success();
            return redirect()->route('coupon.index');
        } catch (ValidationException $e) {
            flash($e->getMessage())->error();
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            flash(('An error occurred while saving the coupon'))->error();
            return back()->withInput();
        }
    }

    public function sellerStore(Request $request)
    {
        if(count(Coupon::where('code', $request->coupon_code)->get()) > 0){
            flash(('Coupon already exist for this coupon code'))->error();
            return back();
        }

        $coupon = new Coupon;
        $coupon->user_id = Auth::user()->id;
        $coupon = $this->setCouponData($request, $coupon);
        $coupon->save();

        flash(('Coupon has been saved successfully'))->success();
        return redirect()->route('seller.coupon.index');
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
    public function edit($id)
    {
        $coupon = Coupon::findOrFail(decrypt($id));
        return view('backend.marketing.coupons.edit', compact('coupon'));
    }

    public function sellerEdit($id)
    {
        $coupon = Coupon::findOrFail(decrypt($id));
        return view('frontend.user.seller.coupons.edit', compact('coupon'));
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
        try{
            if(count(Coupon::where('id', '!=' , $id)->where('code', $request->coupon_code)->get()) > 0){
                flash('Coupon already exist for this coupon code')->error();
                return back();
            }

            $request->validate([
                'coupon_for' => 'nullable|string',
                'group_ids' => 'nullable|required_if:coupon_for,customer_group|array',
                'group_ids.*' => 'exists:customer_groups,id',
            ]);

            $coupon = Coupon::findOrFail($id);
            $this->setCouponData($request, $coupon);
            $coupon->save();

            flash('Coupon has been updated successfully')->success();
            return redirect()->route('coupon.index');
        } catch (ValidationException $e) {
            flash($e->getMessage())->error();
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            flash('An error occurred while saving the coupon')->error();
            return back()->withInput();
        }
    }

    public function sellerUpdate(Request $request, $id)
    {
        if(count(Coupon::where('id', '!=' , $id)->where('code', $request->coupon_code)->get()) > 0){
            flash(('Coupon already exist for this coupon code'))->error();
            return back();
        }

        $coupon = Coupon::findOrFail($id);
        $this->setCouponData($request, $coupon);
        $coupon->save();

        flash(('Coupon has been updated successfully'))->success();
        return redirect()->route('seller.coupon.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Coupon::destroy($id);
        flash(('Coupon has been deleted successfully'))->success();
        return redirect()->route('coupon.index');
    }

    public function sellerDestroy($id)
    {
        Coupon::destroy($id);
        flash(('Coupon has been deleted successfully'))->success();
        return redirect()->route('seller.coupon.index');
    }

    public function setCouponData(Request $request, Coupon $coupon){
        $coupon->type = $request->coupon_type;
        $coupon->usage_limit = $request->usage_limit;
        $coupon->code = strtoupper($request->coupon_code);
        $coupon->discount = $request->discount;
        $coupon->discount_type = $request->discount_type;
        $coupon->description = trim($request->description ?? '') ?: null;
        $coupon->force_apply = filled($request->force_apply) ? true : false;
        $coupon->only_for_app = filled($request->only_for_app) ? true : false;
        $coupon->featured = filled($request->featured) ? true : false;
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $dateRange = explode(" - ", $request->date_range);
        if (count($dateRange) == 2) {
            $startDate = Carbon::parse($dateRange[0])->startOfDay();
            $endDate = Carbon::parse($dateRange[1])->endOfDay();
        }
        $coupon->start_date = $startDate->timestamp;
        $coupon->end_date = $endDate->timestamp;

        $details = [];
        if ($request->coupon_type == "product_base") {
            foreach($request->product_ids as $product_id) {
                $details[]['product_id'] = $product_id;
            }
        } elseif (in_array($request->coupon_type, ["cart_base", "shipping_charge"])) {
            $details['min_buy'] = $request->min_buy;
            $details['max_discount'] = $request->max_discount;
        }
        $coupon->details = json_encode($details);

        // New fields
        if(filled($request->coupon_for) && in_array($request->coupon_for, ['crm', 'affiliate']) && filled($request->assign_to)) {
            $coupon->assigned_to = $request->assign_to;
            $coupon->assigned_by = Auth::id();
            $coupon->group_ids = null;
            $coupon->is_affiliate = $request->coupon_for === 'affiliate';
            Cache::forget('coupons_for_' . $request->assign_to);
        } elseif($request->coupon_for == 'customer_group') {
            $ids = (array) $request->group_ids;
            $coupon->group_ids = array_map('intval', $ids);
            $coupon->assigned_to = null;
            $coupon->assigned_by = null;
            $coupon->is_affiliate = 0;
        } else {
            $coupon->assigned_to = null;
            $coupon->assigned_by = null;
            $coupon->group_ids = null;
            $coupon->is_affiliate = 0;
        }
        return $coupon;
    }

    public function touch(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);

        if($request->has('status')) {
            $coupon->status = $request->status ? 1 : 0;
            $message = "Status updated successfully.";
        } elseif($request->has('featured')) {
            $coupon->featured = $request->featured ? 1 : 0;
            $message = "Featured status updated successfully.";
        } else {
            return response()->json([
                'success' => false,
                'message' => "No valid field to update.",
            ], 400);
        }

        $coupon->save();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getAssignee(Request $request)
    {
        $type = $request->type;
        $options = '';
        if(in_array($type, ['affiliates', 'crm'])) {
            $options .= '<option value="">Select Assignee</option>';
            $optionArray = User::active()
                ->where('user_type', $type === 'crm' ? 'staff' : 'affiliate')
                ->pluck('name', 'id')
                ->toArray();
        } elseif($type == "customer_group") {
            $options .= '<option value="" disabled>Select Groups</option>';
            $optionArray = Customergroup::pluck('group_name', 'id')->toArray();
        } else {
            $options .= '<option value="">Select Assignee</option>';
            $optionArray = [];
        }

        $selected = explode(',', $request->selected);
        foreach($optionArray as $id => $name) {
            $isSelected = in_array($id, $selected) ? 'selected' : '';
            $options .= '<option value="'.$id.'" '.$isSelected.'>'.$name.'</option>';
        }

        return response()->json(['success' => true, 'options' => $options]);
    }

    public function get_coupon_form(Request $request)
    {
        if($request->coupon_type == "product_base") {
            if(Auth::user()->user_type == 'seller') {
                $products = filter_products(\App\Models\Product::with('product_translations')->where('user_id', Auth::user()->id))->get();
            } else {
                $admin_id = \App\Models\User::where('user_type', 'admin')->first()->id;
                $products = filter_products(\App\Models\Product::with('product_translations')->where('user_id', $admin_id))->get();
            }

            return view('partials.coupons.product_base_coupon', compact('products'));
        }
        elseif($request->coupon_type == "cart_base"){
            return view('partials.coupons.cart_base_coupon');
        }
    }

    public function get_coupon_form_edit(Request $request)
    {
        if($request->coupon_type == "product_base") {
            $coupon = Coupon::findOrFail($request->id);

            if(Auth::user()->user_type == 'seller') {
                $products = filter_products(\App\Models\Product::where('user_id', Auth::user()->id))->get();
            } else {
                $admin_id = \App\Models\User::where('user_type', 'admin')->first()->id;
                $products = filter_products(\App\Models\Product::where('user_id', $admin_id))->get();
            }

            return view('partials.coupons.product_base_coupon_edit',compact('coupon', 'products'));
        }
        elseif($request->coupon_type == "cart_base"){
            $coupon = Coupon::findOrFail($request->id);
            return view('partials.coupons.cart_base_coupon_edit',compact('coupon'));
        }
    }

}
