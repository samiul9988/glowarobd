<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $merchants = User::active()->merchant()->paginate(10);
        return view('backend.merchants.index', compact('merchants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.merchants.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(User::where('email', $request->email)->first() == null){
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->mobile;
            $user->user_type = "merchant";
            $user->password = Hash::make($request->password);
            if($user->save()){
                $user->generateAppId();
                flash(('Merchant has been inserted successfully'))->success();
                return redirect()->route('merchants.index');
            }
        }

        flash(('Email already used'))->error();
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
        $merchant = User::active()->merchant()->findOrFail($id);

        $logs = $merchant->apiLogs()->latest()->paginate(10);

        return response()->json($logs);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $merchant = User::active()->merchant()->findOrFail($id);
        return view('backend.merchants.edit', compact('merchant'));
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
        $user = User::active()->merchant()->findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile;
        if(filled($request->password)){
            $user->password = Hash::make($request->password);
        }
        if($user->save()){
            $user->generateAppId();
            flash(('Merchant has been updated successfully'))->success();
            return redirect()->route('merchants.index');
        }

        flash(('Something went wrong'))->error();
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
        if(User::destroy($id)){
            flash(('Merchant has been deleted successfully'))->success();
            return redirect()->route('merchants.index');
        }

        flash(('Something went wrong'))->error();
        return back();
    }
}
