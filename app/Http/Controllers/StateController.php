<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $countries = Cache::remember('all_active_countries', now()->addHour(), function () {
            return Country::where('status', 1)->get();
        });
        $search = $request->search ?? '';
        $country = $request->country;
        $states = State::with('country')
            ->when(filled($search), function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->when(filled($country), function ($query) use ($country) {
                $query->where('country_id', $country);
            })
            ->whereHas('country', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('status', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(15);
        return view('backend.setup_configurations.states.index', compact('states', 'countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $state = new State;

        $state->name        = $request->name;
        $state->country_id  = $request->country_id;

        $state->save();

        Cache::forget('all_states');
        flash(('State has been inserted successfully'))->success();
        return back();
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
        $state  = State::findOrFail($id);
        $countries = Country::where('status', 1)->get();

        return view('backend.setup_configurations.states.edit', compact('countries', 'state'));
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
        $state = State::findOrFail($id);

        $state->name        = $request->name;
        $state->country_id  = $request->country_id;

        $state->save();

        Cache::forget('all_states');
        flash(('State has been updated successfully'))->success();
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
        State::destroy($id);

        Cache::forget('all_states');
        flash(('State has been deleted successfully'))->success();
        return redirect()->route('states.index');
    }

    public function updateStatus(Request $request)
    {
        $state = State::findOrFail($request->id);
        $state->status = $request->status;
        $state->save();

        if ($state->status) {
            foreach ($state->cities as $city) {
                $city->status = 1;
                $city->save();
            }
        }

        Cache::forget('all_states');
        Cache::forget('all_cities');
        return 1;
    }
}
