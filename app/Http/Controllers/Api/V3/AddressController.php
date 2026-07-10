<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\Area;
use App\Models\Cart;
use App\Models\City;
use App\Models\State;
use App\Models\Address;
use App\Models\Country;
use Illuminate\Support\Str;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\V3\AreasCollection;
use App\Http\Resources\V3\CitiesCollection;
use App\Http\Resources\V3\StatesCollection;
use App\Http\Resources\V3\AddressCollection;
use App\Http\Resources\V3\CountriesCollection;

class AddressController extends Controller
{
    public function addresses($id)
    {
        $field = 'user_id';
        if (Str::startsWith($id, 'tmp')) {
            $field = 'temp_user_id';
        }
        $addresses = Address::whereNotNull($field)->where($field, '!=', '0')->where($field, $id)->get();
        return new AddressCollection($addresses);
    }

    public function createShippingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|min:10',
            'name' => 'nullable|min:3|max:50',
            'area_id' => 'required|exists:areas,id',
            // 'phone' => 'required|min:11'
            'phone' => 'required|min:11|regex:/^(\+88)?01[3-9]\d{8}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => $validator->errors(),
                'result' => false
            ]);
        }

        $address = new Address;
        $address->user_id = $request->is_guest_user ? null : $request->user_id;
        $address->temp_user_id = $request->is_guest_user ? $request->user_id : null;
        $address->name = $request->name ?: auth('api')->user()->name ?: 'Guest User';
        $address->address = $request->address;
        $address->country_id = $request->country_id ?? Country::active()->first()?->id ?? null;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->area_id = $request->area_id;
        $address->postal_code = $request->postal_code;
        $address->phone = trim(str_replace(['-', '+88'], '', $request->phone));
        $address->address_type = $request->address_type ?? 'Home';
        $address->save();

        return response()->json([
            'result' => true,
            'message' => 'Shipping information has been added successfully'
        ]);
    }

    public function updateShippingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|min:10',
            'name' => 'nullable|min:3|max:50',
            'area_id' => 'required|exists:areas,id',
            // 'phone' => 'required|min:11'
            'phone' => 'required|min:11|regex:/^(\+88)?01[3-9]\d{8}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => $validator->errors(),
                'result' => false
            ]);
        }

        $address = Address::find($request->id);
        $address->name = $request->name ?: $address->name ?: auth('api')->user()->name ?: 'Guest User';
        $address->address = $request->address;
        $address->country_id = $request->country_id ?? Country::active()->first()?->id ?? null;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->area_id = $request->area_id;
        $address->postal_code = $request->postal_code;
        $address->phone = trim(str_replace(['-', '+88'], '', $request->phone));
        $address->address_type = $request->address_type ?? 'Home';
        $address->save();

        return response()->json([
            'result' => true,
            'message' => ('Shipping information has been updated successfully')
        ]);
    }

    public function updateShippingAddressLocation(Request $request)
    {
        $address = Address::find($request->id);
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => ('Shipping location in map updated successfully')
        ]);
    }


    public function deleteShippingAddress($id)
    {
        Address::destroy($id);
        return response()->json([
            'result' => true,
            'message' => ('Shipping information has been deleted')
        ]);
    }

    public function makeShippingAddressDefault(Request $request)
    {
        $addresses = Address::whereNotNull($request->user_field)->where($request->user_field, $request->user_id)->get();
        if($addresses->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'No address found for the user'
            ]);
        }

        Address::whereNotNull($request->user_field)->where($request->user_field, $request->user_id)->update(['set_default' => 0]); //make all user addressed non default first
        // $addresses->where('id', $request->id)->update(['set_default' => 1]);

        $address = Address::find($request->id);
        $address->set_default = 1;
        $address->save();
        return response()->json([
            'result' => true,
            'message' => 'Default shipping information has been updated'
        ]);
    }

    public function updateAddressInCart(Request $request)
    {
        try {
            $carts = Cart::withoutGlobalScopes()
                ->whereNotNull($request->user_field)
                ->where($request->user_field, $request->user_id)
                ->get();

            if($carts->isEmpty()) {
                return response()->json([
                    'result' => false,
                    'message' => 'No cart found for the user'
                ]);
            }

            Cart::withoutGlobalScopes()
                ->whereNotNull($request->user_field)
                ->where($request->user_field, $request->user_id)
                ->update(['address_id' => $request->address_id]);

            $dShippingAmount = PHP_INT_MAX;
            if(check_shipping_discount()){
                $addressInfo = Address::find($carts->first()->address_id);
                $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$addressInfo->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                $sDiscount = check_shipping_discount_carts($carts, $matchZone->id ?? null);
                if(!empty($sDiscount) && $sDiscount['status']){
                    $cartAmount = 0;
                    foreach($carts as $cart){
                        $cartAmount += $cart->price * $cart->quantity;
                    }
                    if($cartAmount >= $sDiscount['min_amount']){
                        $dShippingAmount = $sDiscount['amount'];
                    }
                }
            }

            $shippingCalByOwner = [];
            $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
            foreach ($carts as $key => $cartItem) {
                $cartItem['address_id'] = $request->address_id;
                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    if(!in_array($cartItem['owner_id'],$shippingCalByOwner)){
                        $shippingCalByOwner[]=$cartItem['owner_id'];
                        $prevShip = getShippingCost($carts, $key);
                        $dShipping = min($prevShip, $dShippingAmount);
                        $cartItem['shipping_cost'] = abs($dShipping);
                    }
                }

                if(isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {
                    foreach(json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                        if($shipping_info['city'] == $shipping_region) {
                            $cartItem['shipping_cost'] = min($dShippingAmount, (double)($val));
                            break;
                        } else {
                            $cartItem['shipping_cost'] = 0;
                        }
                    }
                } else {
                    if (!$cartItem['shipping_cost'] || $cartItem['shipping_cost'] == null || $cartItem['shipping_cost'] == 'null') {
                        $cartItem['shipping_cost'] = 0;
                    }
                }

                $cartItem->save();
            }

            // Cart::where('user_id', $request->user_id)->update(['address_id' => $request->address_id, 'shipping_cost' => $request->shipping_cost]);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Could not save the address',
                // 'error' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString()
            ]);
        }
        return response()->json([
            'result' => true,
            'message' => 'Address is saved'
        ]);


    }

    public function getCities()
    {
        return new CitiesCollection(City::where('status', 1)->get());
    }

    public function getStates()
    {
        return new StatesCollection(State::where('status', 1)->get());
    }

    public function getCountries(Request $request)
    {
        $country_query = Country::where('status', 1);
        if ($request->name != "" || $request->name != null) {
             $country_query->where('name', 'like', '%' . $request->name . '%');
        }
        $countries = $country_query->get();

        return new CountriesCollection($countries);
    }

    public function getCitiesByState($state_id,Request $request)
    {
        $city_query = City::where('status', 1)->where('state_id',$state_id);
        if ($request->name != "" || $request->name != null) {
             $city_query->where('name', 'like', '%' . $request->name . '%');
        }
        $cities = $city_query->get();
        return new CitiesCollection($cities);
    }

    public function getAreasByCity($city_id,Request $request)
    {
        $city_query = Area::where('status', 1)->where('city_id',$city_id);
        if ($request->name != "" || $request->name != null) {
             $city_query->where('name', 'like', '%' . $request->name . '%');
        }
        $cities = $city_query->get();
        return new AreasCollection($cities);
    }

    public function getStatesByCountry($country_id,Request $request)
    {
        $state_query = State::where('status', 1)->where('country_id',$country_id);
        if ($request->name != "" || $request->name != null) {
            $state_query->where('name', 'like', '%' . $request->name . '%');
       }
        $states = $state_query->get();
        return new StatesCollection($states);
    }
}
