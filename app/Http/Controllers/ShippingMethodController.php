<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use DB;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $queries = ShippingMethod::query()->with('logo');
        $shippingMethods = $queries->orderBy('status', 'desc')->paginate(15);

        return view('backend.setup_configurations.shipping_method.index', compact('shippingMethods'));
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
        $shippingMethod = new ShippingMethod;

        $shippingMethod->name = $request->name;
        $shippingMethod->logo = $request->logo;

        $shippingMethod->save();

        $shippingMethodFilePath = storage_path('app/public/shipping/methods.json');
        if (!file_exists($shippingMethodFilePath) || file_exists($shippingMethodFilePath)) {
            $rows = DB::table('shipping_methods')->get();
            $jsonRowData = $rows->toJson();
            file_put_contents($shippingMethodFilePath, $jsonRowData);
        }

        flash(('Shipping method has been inserted successfully'))->success();

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function edit(Request $request, $id)
     {
         $shippingMethod  = ShippingMethod::findOrFail($id);
         return view('backend.setup_configurations.shipping_method.edit', compact('shippingMethod'));
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
        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->name = $request->name;
        $shippingMethod->logo = $request->logo;

        $shippingMethod->save();

        $shippingMethodFilePath = storage_path('app/public/shipping/methods.json');
        if (!file_exists($shippingMethodFilePath) || file_exists($shippingMethodFilePath)) {
            $rows = DB::table('shipping_methods')->get();
            $jsonRowData = $rows->toJson();
            file_put_contents($shippingMethodFilePath, $jsonRowData);
        }

        flash(('Shipping method has been updated successfully'))->success();
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
        $area = ShippingMethod::findOrFail($id);

        ShippingMethod::destroy($id);

        $shippingMethodFilePath = storage_path('app/public/shipping/methods.json');
        if (!file_exists($shippingMethodFilePath) || file_exists($shippingMethodFilePath)) {
            $rows = DB::table('shipping_methods')->get();
            $jsonRowData = $rows->toJson();
            file_put_contents($shippingMethodFilePath, $jsonRowData);
        }

        flash(('Shipping method has been deleted successfully'))->success();
        return redirect()->route('shipping_method.index');
    }

    public function updateStatus(Request $request){
        $shippingMethod = ShippingMethod::findOrFail($request->id);
        $shippingMethod->status = $request->status;
        $shippingMethod->save();

        $shippingMethodFilePath = storage_path('app/public/shipping/methods.json');
        if (!file_exists($shippingMethodFilePath) || file_exists($shippingMethodFilePath)) {
            $rows = DB::table('shipping_methods')->get();
            $jsonRowData = $rows->toJson();
            file_put_contents($shippingMethodFilePath, $jsonRowData);
        }

        return 1;
    }
}
