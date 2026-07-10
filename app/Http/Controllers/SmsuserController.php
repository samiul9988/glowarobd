<?php
namespace App\Http\Controllers;

use App\Models\Smsuser;
use App\Models\SmsuserImport;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SmsuserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $users       = Smsuser::where('status', 1)->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $users->where(function ($q) use ($sort_search) {
                $q->where('mobile_number', 'like', '%' . $sort_search . '%');
            });
        }
        $users = $users->paginate(15);
        return view('backend.sms_user.index', compact('users', 'sort_search'));
    }

    public function bulk_upload()
    {
        return view('backend.sms_user.bulk_upload');
    }

    public function bulk_sms_user_upload(Request $request)
    {
        if ($request->hasFile('sms_user_bulk_file')) {
            Excel::import(new SmsuserImport, request()->file('sms_user_bulk_file'));
            Cache::forget('sms_unregistered_users');
        }

        flash(('SMS User imported successfully'))->success();
        return back();
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
        $mobilenumberarray = explode(PHP_EOL, $request->mobile_number);
        //dd($request);
        $saveata = 0;
        if (is_array($mobilenumberarray)):
            for ($i = 0; $i < count($mobilenumberarray); $i++):
                $oldnumber = Smsuser::where('mobile_number', trim($mobilenumberarray[$i]))->first();
                if (! $oldnumber):
                    $smsuser                = new Smsuser();
                    $smsuser->mobile_number = trim($mobilenumberarray[$i]);
                    $smsuser->status        = 1;
                    if ($smsuser->save()) {
                        $saveata++;
                    }

                endif;
            endfor;
        endif;
        if ($saveata > 0) {
            Cache::forget('sms_unregistered_users');
            flash(('SMS User has been inserted successfully'))->success();
        } else {
            flash(('Something went wrong!'))->error();
        }

        return redirect()->route('sms_user.index');

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
        $lang    = $request->lang;
        $smsuser = Smsuser::findOrFail($id);
        return view('backend.sms_user.edit', compact('smsuser', 'lang'));
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
        //dd($request);
        $brand                = Smsuser::findOrFail($id);
        $brand->mobile_number = $request->mobile_number;
        $brand->save();
        Cache::forget('sms_unregistered_users');
        flash(('SMS User has been updated successfully'))->success();
        return redirect()->route('sms_user.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Smsuser::destroy($id);
        Cache::forget('sms_unregistered_users');
        flash(('SMS User has been deleted successfully'))->success();
        return redirect()->route('sms_user.index');

    }
}
