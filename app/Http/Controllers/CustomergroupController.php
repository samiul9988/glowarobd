<?php

namespace App\Http\Controllers;

use App\Models\Customergroup;
use App\Models\Customeringroup;
use Hash;
use Illuminate\Http\Request;

class CustomergroupController extends Controller
{
    public function index()
    {
        $groups =  Customergroup::with('image')->orderBy('ordering', 'asc')->paginate(15);
        return view ('backend.customer_group.index', compact('groups'));
    }

    public function create()
    {
        return view ('backend.customer_group.create');
    }

    public function store(Request $request)
    {
        $customer =  new Customergroup();
        $customer->group_name = $request->group_name;
        $customer->min_order_amount = $request->min_order_amount;
        $customer->min_order_qty = $request->min_order_qty;
        $customer->group_icon = $request->group_icon;
        $customer->group_image = $request->group_image;
        $customer->message = $request->message;
        $customer->ordering = $request->ordering;
        $customer->delivery_discount_amount = $request->delivery_discount_amount;
        if ($request->has('delivery_discount')) {
            $customer->delivery_discount = 1;
        }
        if($request->discount>0){
            $date_var = explode(" to ", $request->date_range);
            $customer->start_date = strtotime($date_var[0]);
            $customer->end_date   = strtotime( $date_var[1]);
            $customer->discount   = $request->discount;
            $customer->discount_type   = $request->discount_type;
        }
        $customer->save();
        flash(('Group has been inserted successfully'))->success();
        return redirect()->route('customer.group');
    }

    public function edit($id){
        $group = Customergroup::find(decrypt($id));
        return view('backend.customer_group.edit', compact('group'));

    }

    public function update(Request $request, $id){
        $customer =  Customergroup::find(decrypt($id));
        $customer->delivery_discount = 0;
        $customer->group_name = $request->group_name;
        $customer->min_order_amount = $request->min_order_amount;
        $customer->min_order_qty = $request->min_order_qty;
        $customer->group_icon = $request->group_icon;
        $customer->group_image = $request->group_image;
        $customer->message = $request->message;
        $customer->ordering = $request->ordering;
        $customer->delivery_discount_amount = $request->delivery_discount_amount;
        if ($request->has('delivery_discount')) {
            $customer->delivery_discount = 1;
        }
        if($request->discount>0){
            $date_var = explode(" to ", $request->date_range);
            $customer->start_date = strtotime($date_var[0]);
            $customer->end_date   = strtotime( $date_var[1]);
            $customer->discount   = $request->discount;
            $customer->discount_type   = $request->discount_type;
        }
        $customer->update();
        flash(('Group has been updated successfully'))->success();
        return redirect()->route('customer.group');
    }
    public function delete($id){
        $customer = Customergroup::find(decrypt($id));
        $customer->delete();
        flash(('Group has been deleted successfully'))->success();
        return redirect()->route('customer.group');
    }


    public function customer_group(Request $request){
        $savedata = 0;
        if($request->id) {
            foreach ($request->id as $user_id){
                $exist_or_not = Customeringroup::where('user_id', $user_id)->count();
                if($exist_or_not > 0){
                    Customeringroup::where('user_id', $user_id)->update(['status' => 0]);
                }
                    $groups = new Customeringroup();
                    $groups->user_id = $user_id;
                    $groups->customer_groups_id = $request->customer_group;
                    $groups->status = 1;
                    if($groups->save()){
                        $savedata++;
                    }
            }
            if($savedata>0){
                flash(('Customer has been added into a group successfully'))->success();
                return 1;
            }
        }
    }
    public function update_status(Request $request)
    {

        $brand_discount = Customergroup::findOrFail($request->id);
        $brand_discount->discount_status = $request->status;
        if($brand_discount->save()){
            flash(('Discount status updated successfully'))->success();
            return 1;
        }
        return 0;
    }

    public function update_delivery_discount_status(Request $request)
    {
        $customer_group = Customergroup::findOrFail($request->id);
        $customer_group->delivery_discount = $request->status;
        if($customer_group->save()){
            flash(('Delivery discount status updated successfully'))->success();
            return 1;
        }
        return 0;
    }
}
