<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use Illuminate\Support\Str;

class ShippingZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $shippingZones = ShippingZone::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $shippingZones = $shippingZones->where('title', 'like', '%'.$sort_search.'%');
        }
        $shippingZones = $shippingZones->paginate(15);
        return view('backend.setup_configurations.shipping_zone.index', compact('shippingZones', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.setup_configurations.shipping_zone.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $shipping_zone = new ShippingZone;
        $shipping_zone->title = $request->title;

        if($request->rest_of_the_world!==NULL){
            $shipping_zone->rest_of_the_world = $request->rest_of_the_world;

            if(ShippingZone::where('rest_of_the_world',1)->exists()){
                flash(('Rest of the world already exists'))->error();
                return back();
            }

        }else{
            if($request->area_ids!==NULL && is_array($request->area_ids) )
                $shipping_zone->area_ids = implode(',',$request->area_ids);
        }


        if($shipping_zone->save()){

            flash(('Shipping zone has been inserted successfully'))->success();
            return redirect()->route('shipping_zone.index');
        }
        else{
            flash(('Something went wrong'))->error();
            return back();
        }
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
        $shipping_zone = ShippingZone::findOrFail($id);
        return view('backend.setup_configurations.shipping_zone.edit', compact('shipping_zone'));
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
        $shipping_zone = ShippingZone::findOrFail($id);

        $shipping_zone->title = $request->title;
        //dd($request->rest_of_the_world);
        if($request->rest_of_the_world=='1'){
            $shipping_zone->rest_of_the_world = $request->rest_of_the_world;

            if(ShippingZone::where('rest_of_the_world',1)->exists()){
                flash(('Rest of the world already exists'))->error();
                return back();
            }
        }

        else{
            if($request->area_ids!==NULL && is_array($request->area_ids) )
                $shipping_zone->area_ids = implode(',',$request->area_ids);
        }

        if($shipping_zone->save()){
            flash(('Shipping zone has been updated successfully'))->success();
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
        ShippingZone::destroy($id);
        flash(('Shipping zone has been deleted successfully'))->success();
        return redirect()->route('shipping_zone.index');
    }

    public function rates($id)
    {
        $shippingMethods = ShippingMethod::where('status',1)->get();
        return view('backend.setup_configurations.shipping_zone.rates', compact('shippingMethods','id'))->render();
    }

    public function updateRates(Request $request)
    {
        $data = [];

        if($request->rate!==NULL && is_array($request->rate)){
            $zoneId = $request->zone;
            $rates = $request->rate;
            for($i=0; $i < count($rates['id']); $i++):
                $data[]= [
                    'id'=> $rates['id'][$i],
                    'price'=> @$rates['price'][$rates['id'][$i]]
                ];
            endfor;
        }

        if(count($data)>0){
            $shipping_zone = ShippingZone::findOrFail($zoneId);
            $shipping_zone->rates = json_encode($data);
            $shipping_zone->save();
            flash(('Shipping rates has been updated successfully'))->success();
            return 1;
        }

        return 0;
    }

}
