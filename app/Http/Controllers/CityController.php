<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\CityTranslation;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $state = $request->state;
        $cities = City::with('state')
            ->when(filled($search), function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->when(filled($state), function ($query) use ($state) {
                $query->where('state_id', $state);
            })
            ->orderBy('status', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        $states = Cache::remember('all_active_states', now()->addHour(), function () {
            return State::where('status', 1)->get();
        });

        return view('backend.setup_configurations.cities.index', compact('cities', 'states'));
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
        $city = new City;

        $city->name = $request->name;
        $city->cost = $request->cost;
        $city->state_id = $request->state_id;

        $city->save();

        Cache::forget('all_cities');
        flash(('City has been inserted successfully'))->success();

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
         $lang  = $request->lang;
         $city  = City::findOrFail($id);
         $states = State::where('status', 1)->get();
         return view('backend.setup_configurations.cities.edit', compact('city', 'lang', 'states'));
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
        $city = City::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $city->name = $request->name;
        }

        $city->state_id = $request->state_id;
        $city->cost = $request->cost;

        $city->save();

        $city_translation = CityTranslation::firstOrNew(['lang' => $request->lang, 'city_id' => $city->id]);
        $city_translation->name = $request->name;
        $city_translation->save();

        Cache::forget('all_cities');
        flash(('City has been updated successfully'))->success();
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
        $city = City::findOrFail($id);

        foreach ($city->city_translations as $key => $city_translation) {
            $city_translation->delete();
        }

        City::destroy($id);

        Cache::forget('all_cities');
        flash(('City has been deleted successfully'))->success();
        return redirect()->route('cities.index');
    }

    public function updateStatus(Request $request){
        $city = City::findOrFail($request->id);
        $city->status = $request->status;
        $city->save();

        Cache::forget('all_cities');
        return 1;
    }
}
