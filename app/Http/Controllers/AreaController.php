<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\AreaTranslation;
use Illuminate\Support\Facades\Cache;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $city = $request->city;
        $areas = Area::with('city')
            ->when(filled($search), function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->when(filled($city), function ($query) use ($city) {
                $query->where('city_id', $city);
            })
            ->orderBy('status', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(15);

        $cities = Cache::remember('all_active_cities', now()->addHour(), function () {
            return City::where('status', 1)->get();
        });

        return view('backend.setup_configurations.area.index', compact('areas', 'cities'));
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
        $city = new Area;

        $city->name = $request->name;
        $city->cost = $request->cost;
        $city->city_id = $request->city_id;

        $city->save();

        Cache::forget('all_areas');
        flash(('Area has been inserted successfully'))->success();

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
         $area  = Area::findOrFail($id);
         $cities = City::where('status', 1)->get();
         return view('backend.setup_configurations.area.edit', compact('area', 'lang', 'cities'));
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
        $area = Area::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $area->name = $request->name;
        }

        $area->city_id = $request->city_id;
        $area->cost = $request->cost;

        $area->save();

        $area_translation = AreaTranslation::firstOrNew(['lang' => $request->lang, 'area_id' => $area->id]);
        $area_translation->name = $request->name;
        $area_translation->save();

        Cache::forget('all_areas');
        flash(('Area has been updated successfully'))->success();
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
        $area = Area::findOrFail($id);

        foreach ($area->area_translations as $key => $area_translation) {
            $area_translation->delete();
        }

        Area::destroy($id);

        Cache::forget('all_areas');
        flash(('Area has been deleted successfully'))->success();
        return redirect()->route('areas.index');
    }

    public function updateStatus(Request $request){
        $area = Area::findOrFail($request->id);
        $area->status = $request->status;
        $area->save();

        Cache::forget('all_areas');
        return 1;
    }
}
