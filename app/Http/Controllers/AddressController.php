<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\Order;
use App\Models\State;
use App\Models\Address;
use App\Models\Country;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'address_type' => 'nullable',
                'name' => 'required|min:3',
                'address' => 'required|min:10',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'area_id' => 'required|exists:areas,id',
                'phone' => 'required|min:11|max:11',
            ]);

            $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
            $hasPreviousAddress = Address::where($request->user_field, $userId)->exists();

            $address = new Address;
            if ($request->has('customer_id')) {
                $address->user_id = $request->customer_id;
            } else {
                $address->{$request->user_field} = $userId;
            }
            $address->name = $request->name;
            $address->address = $request->address;
            $address->address_type = $request->address_type ?? 'Home';
            $address->country_id = $request->country_id;
            $address->state_id = $request->state_id;
            $address->city_id = $request->city_id;
            $address->area_id = $request->area_id;
            $address->longitude = $request->longitude;
            $address->latitude = $request->latitude;
            $address->postal_code = $request->postal_code;
            $address->phone = $request->phone;
            $address->set_default = $hasPreviousAddress ? 0 : 1;
            $address->save();

            $cart = \App\Models\Cart::where($request->user_field, $userId)->first();
            if (!$hasPreviousAddress && $cart) {
                $matchZone = ShippingZone::whereRaw('FIND_IN_SET(?,area_ids)', [$address->area_id ?? null])->first() ?? ShippingZone::where('rest_of_the_world', 1)->first();
                if ($matchZone) {
                    $discountShippingCharge = getDiscountShippingCharge(\App\Models\Cart::where($request->user_field, $userId)->get(), $matchZone->id);
                    $rates = json_decode($matchZone->rates, true);
                    if (!empty($rates)) {
                        $cart->shipping_cost = min($rates[0]['price'] ?? 0, $discountShippingCharge);
                        $cart->shipping_method = $rates[0]['id'] ?? null;
                    }
                    $cart->address_id = $address->id;
                    $cart->shipping_type = 'home_delivery';
                    $cart->save();
                }
            }

            flash('Address added successfully')->success();
        } catch (\Exception $e) {
            // dd($e->getMessage());
            flash('Something went wrong')->error();
        }
        return back();
    }

    public function edit(Request $request, $id)
    {
        $address = Address::find($id);
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ]);
        }
        $data['address_data'] = $address;
        $data['states'] = State::where('status', 1)->where('country_id', $address->country_id)->pluck('name', 'id');
        $data['cities'] = City::where('status', 1)->where('state_id', $address->state_id)->pluck('name', 'id');
        $data['areas'] = Area::where('status', 1)->where('city_id', $address->city_id)->pluck('name', 'id');

        if ($request->has('isFromSpa') && get_setting('spa_checkout') == 1) {
            $returnHTML = view('frontend.spa_checkout.address_edit_modal', $data)->render();
        } else {
            $returnHTML = view('frontend.partials.address_edit_modal', $data)->render();
        }
        return response()->json([
            'success' => true,
            'data' => $data,
            'html' => $returnHTML
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:3',
            'address_type' => 'nullable',
            'address' => 'required|min:10',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'phone' => 'required|min:11|max:11',
        ]);

        $address = Address::findOrFail($id);

        $address->name = $request->name;
        $address->address = $request->address;
        $address->address_type = $request->address_type ?? 'Home';
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->area_id = $request->area_id;
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        $address->postal_code = $request->postal_code;
        $address->phone = $request->phone;

        $address->save();

        if (filled($request->order_id)) {
            $order = Order::find($request->order_id);
            $shippingAddress = json_decode($order->shipping_address, true);

            $data['name'] = $shippingAddress['name'];
            $data['email'] = $shippingAddress['email'];
            $data['address'] = $address->address;
            $data['country'] = $address->country?->name ?? '';
            $data['state'] = $address->state?->name ?? '';
            $data['city'] = $address->city?->name ?? '';
            $data['area'] = $address->area?->name ?? '';
            $data['postal_code'] = $address->postal_code;
            $data['phone'] = $address->phone;

            $order->shipping_address = json_encode($data);
            $order->save();
        }

        flash(('Address info updated successfully'))->success();
        return back();
    }

    public function destroy(Request $request, $id)
    {
        $address = Address::find($id);
        $type = 'error';
        $message = 'Something went wrong';

        if (!$address) {
            $message = 'Address not found';
        }
        if ($address->set_default) {
            $message = 'Default address can not be deleted';
        } else {
            $address->delete();
            $type = 'success';
            $message = 'Address deleted successfully';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $type === 'success', 'message' => $message]);
        } else {
            $type === 'success' ? flash($message)->success() : flash($message)->error();
            return back();
        }
    }

    public function getStates(Request $request)
    {
        $all_states = Cache::remember('all_states', now()->addDay(), function () {
            return State::query()
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get();
        });
        $states = $request->country_id ? $all_states->where('country_id', $request->country_id) : $all_states;
        $html = '<option value="">' . ("Select State") . '</option>';

        foreach ($states as $state) {
            $selected = strtolower($request->selected ?? '') === strtolower($state->name);
            $html .= '<option value="' . $state->id . '"' . ($selected ? ' selected' : '') . '>' . $state->name . '</option>';
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function getCities(Request $request)
    {
        $all_cities = Cache::remember('all_cities', now()->addDay(), function () {
            return City::query()
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
        });
        $cities = $request->state_id ? $all_cities->where('state_id', $request->state_id) : $all_cities;
        $html = '<option value="">' . ("Select City") . '</option>';

        foreach ($cities as $row) {
            $selected = strtolower($request->selected ?? '') === strtolower($row->name);
            $html .= '<option value="' . $row->id . '"' . ($selected ? ' selected' : '') . '>' . $row->name . '</option>';
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function getAreas(Request $request)
    {
        $all_areas = Cache::remember('all_areas', now()->addDay(), function () {
            return Area::query()
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
        });
        $areas = filled($request->city_id) ? $all_areas->where('city_id', $request->city_id) : $all_areas;
        $html = '<option value="">' . ("Select Area") . '</option>';

        foreach ($areas as $row) {
            $selected = strtolower($request->selected ?? '') === strtolower($row->name);
            $html .= '<option value="' . $row->id . '"' . ($selected ? ' selected' : '') . '>' . $row->name . '</option>';
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function set_default(Request $request, $id)
    {
        $address = Address::findOrFail($id);
        $userId = Auth::check() ? Auth::id() : $request->temp_user_id;
        Address::where($request->user_field, $userId)->update(['set_default' => 0]);
        $address->set_default = 1;
        $address->save();

        return back();
    }
}
