<?php

namespace App\Http\Controllers\Accounts;

use App\Events\InsertAccHead;
use App\Events\InsertAccTransaction;
use App\Events\UpdateAccHead;
use App\Http\Controllers\Controller;
use App\Models\ACCBank;
use App\Models\AccHead;
use App\Models\AccTransaction;
use Illuminate\Http\Request;

class ACCBankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        !empty($request->search) ? $search = $request->search : $search = null;

        $banks = ACCBank::query();
        if(!empty($search)){
            $banks = $banks->where('bank_name', 'LIKE', $search)->orWhere('acc_name', 'LIKE', $search)->orWhere('acc_no', 'LIKE', $search);
        }
        $banks = $banks->paginate(15);
        return view('backend.accounts.banks.index', compact('banks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.accounts.banks.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required',
            'acc_no' => 'required',
            'acc_name' => 'required',
            'type' => 'required'
        ]);

        $bank = new ACCBank();
        $bank->bank_name = $request->bank_name;
        $bank->acc_no = $request->acc_no;
        $bank->acc_name = $request->acc_name;
        $bank->type = $request->type;
        $bank->address = $request->address;
        $bank->contact_no = $request->contact_no;
        $bank->user_id = $request->user()->id ?? 0;
        if ($bank->save()) {
            $headData = [
                'parent_head' => 'assets',
                'sub_head' => 'Cash & Bank',
                'head' => $request->bank_name.' '.$request->acc_no,
                'reference_id' => $bank->id ?? 0,
                'reference_type' => ACCBank::class,
                'user_id' => $bank->user_id ?? 0
            ];
            InsertAccHead::dispatch($headData);

            if($request->has('opening_balance') && !empty($request->opening_balance)){
                $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                $vno = date('Ymd') . '-' . ($vno_counting + 1);

                $accTransData = [
                    'date' => date('Y-m-d'),
                    'user_id' => $request->user()->id ?? 0,
                    'vno' => $vno,
                    'head' => $request->bank_name.' '.$request->acc_no,
                    'head_type' => ACCBank::class,
                    'head_id' => $bank->id ?? 0,
                    'debit' => $request->opening_balance,
                    'credit' => 0,
                    'description' => 'New Bank Account Opening Balance'
                ];
                InsertAccTransaction::dispatch($accTransData);
            }
            flash(('Bank created successfully'))->success();
            return redirect()->route('banks.index');
        }else{
            flash(('Something went wrong!'))->error();
            return redirect()->route('banks.index');
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
    public function edit($id)
    {
        $bank = ACCBank::find($id);
        return view('backend.accounts.banks.edit', compact('bank'));
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
        $request->validate([
            'bank_name' => 'required',
            'acc_no' => 'required',
            'acc_name' => 'required',
            'type' => 'required'
        ]);

        $bank = ACCBank::find($id);
        $oldHead = AccHead::where('head', $bank->bank_name.' '.$bank->acc_no)->first();

        $bank->bank_name = $request->bank_name;
        $bank->acc_no = $request->acc_no;
        $bank->acc_name = $request->acc_name;
        $bank->type = $request->type;
        $bank->address = $request->address;
        $bank->contact_no = $request->contact_no;
        $bank->user_id = $request->user()->id ?? 0;
        if ($bank->save()) {
            if(!empty($oldHead)){
                $headData = [
                    'parent_head' => 'assets',
                    'sub_head' => 'Cash & Bank',
                    'head' => $request->bank_name.' '.$request->acc_no,
                    'reference_id' => $bank->id ?? 0,
                    'reference_type' => ACCBank::class,
                    'user_id' => $bank->user_id ?? 0
                ];
                UpdateAccHead::dispatch($oldHead->id, $headData);
            }
            flash(('Bank updated successfully'))->success();
            return redirect()->route('banks.index');
        }else{
            flash(('Something went wrong!'))->error();
            return redirect()->route('banks.index');
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
        ACCBank::find($id)->delete();

        flash(('Bank deleted successfully'))->success();
        return redirect()->route('banks.index');
    }
}
