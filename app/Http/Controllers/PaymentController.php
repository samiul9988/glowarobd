<?php

namespace App\Http\Controllers;

use App\Models\ACCBank;
use App\Models\AccHead;
use App\Models\AccTransaction;
use App\Models\AccVoucherEntry;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Auth;
use DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = Payment::where('seller_id', Auth::user()->seller->id)->paginate(9);
        return view('frontend.user.seller.payment_history', compact('payments'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment_histories(Request $request)
    {
        $payments = Payment::orderBy('created_at', 'desc')->paginate(15);
        return view('backend.sellers.payment_histories.index', compact('payments'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payments = Payment::where('seller_id', decrypt($id))->orderBy('created_at', 'desc')->get();
        if($payments->count() > 0){
            return view('backend.sellers.payment', compact('payments'));
        }
        flash(('No payment history available for this seller'))->warning();
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paybill(){
        $permissions = json_decode(Auth::user()->staff?->role?->permissions ?? '[]', true) ?? [];

        (Auth::user()->user_type === 'admin' || in_array('26', $permissions)) ? $suppliers = Supplier::orderBy('created_at', 'desc')->get() : $suppliers = Supplier::where('user_id',Auth::user()->id)->orderBy('created_at', 'desc')->get();

        return view('backend.accounts.payments.pay_bill', compact('suppliers'));
    }

    public function saveInvoicePayment(Request $request){

        // Save Payments
        try {
            //code...
            if(!empty($request->refId) && !empty($request->total_pay)){
                $payments = $request->payments;
                if(!empty($payments) && is_array($payments)){
                    DB::beginTransaction();

                    $vnent_counting = AccVoucherEntry::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                    $vinv = "VNO-" . date('Ymd') . ($vnent_counting + 1);

                    $purchaseO = PurchaseOrder::where('po_number', $request->refId)->first();
                    if($purchaseO){
                        $purchaseO->total_payment += floatval($request->total_pay);
                        $purchaseO->total_due = $purchaseO->grand_total - ($purchaseO->total_payment);
                        if($purchaseO->save()){
                            for($i=0; $i<count($payments);  $i++){
                                // save the payment
                                $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                                $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                                $bank_info = ACCBank::find($payments[$i]['bank']);
                                $pdetails = [
                                    'payment_method' => $payments[$i]['method'],
                                    'bank_type' => $payments[$i]['bank_type'],
                                    'bank_info' => ($payments[$i]['method'] != 'cash') ? $bank_info : null,
                                    'payment_amount' => $payments[$i]['amount']
                                ];

                                $payment = new Payment;
                                $payment->invoice_no = $pinv;
                                $payment->date = date('Y-m-d');
                                $payment->payable_id = $request->payableId;
                                $payment->payable_type = Supplier::class;
                                $payment->reference_id = $purchaseO->id;
                                $payment->reference_type = PurchaseOrder::class;
                                $payment->seller_id = auth()->user()?->id ?? null;
                                $payment->amount = $payments[$i]['amount'];
                                $payment->payment_details = json_encode($pdetails);
                                $payment->payment_method = $payments[$i]['method'];
                                $payment->txn_code = null;
                                $payment->user_id = auth()->user()?->id ?? null;
                                $payment->remarks = "Payment for Purchase Invoice " . $request->refId;

                                // code
                                if ($payment->save()) {
                                    if($payments[$i]['method'] == 'cash'){
                                        $head1 = "Cash In Hand";
                                    }else if($payments[$i]['method'] == 'bank'){
                                        if(!empty($bank_info)){
                                            $head1 = $bank_info->bank_name.' '.$bank_info->acc_no;
                                        }else{
                                            $head1 = $payments[$i]['method'].' Payment';
                                        }
                                    }else{
                                        $head1 = $payments[$i]['method'].' Payment';
                                    }

                                    $supplier = DB::table('supplier')->where('id', $request->payableId)->first();

                                    $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                                    $vno = date('Ymd') . '-' . ($vno_counting + 1);

                                    $description = "Credited to ".$supplier->name." ".$supplier->contact_number." Payment for Purchase Invoice " . $request->refId;
                                    $credit = $payments[$i]['amount'];
                                    $debit = 0;

                                    AccTransaction::create([
                                        'date' => date('Y-m-d'),
                                        'user_id' => auth()->user()->id,
                                        'vno' => $vno,
                                        'head' => $head1,
                                        'head_type' => !empty($bank_info) ? ACCBank::class : AccHead::class,
                                        'head_id' => !empty($bank_info) ? $bank_info->id : AccHead::where('head', 'like', '%Cash In Hand%')->first()->id ?? null,
                                        'debit' => $debit,
                                        'credit' => $credit,
                                        'description' => $description
                                    ]);

                                    $headinfo = AccHead::where("head", $head1)->first();

                                    $entry = new AccVoucherEntry;
                                    $entry->date = date('Y-m-d');
                                    $entry->vno = $vinv;
                                    $entry->voucher_type = 'payment';
                                    $entry->entry_type = 'credit';
                                    $entry->credit = $credit;
                                    $entry->particular_id = $headinfo->id ?? null;
                                    $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                                    $entry->naration = null;
                                    $entry->note = "Purchase Payment";
                                    $entry->attachement = $request->attachement;
                                    $entry->user_id = auth()->user()->id;
                                    $entry->save();

                                    $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                                    $vno = date('Ymd') . '-' . ($vno_counting + 1);

                                    $head = $supplier->name." ".$supplier->contact_number;
                                    $description = "Debited from ".$head1." Payment for Purchase Invoice " . $request->refId;
                                    $credit = 0;
                                    $debit = $payments[$i]['amount'];

                                    // save related transactions
                                    AccTransaction::create([
                                        'date' => date('Y-m-d'),
                                        'user_id' => auth()->user()->id,
                                        'vno' => $vno,
                                        'head' => $head,
                                        'head_type' => Supplier::class,
                                        'head_id' => $supplier->id,
                                        'debit' => $debit,
                                        'credit' => $credit,
                                        'description' => $description
                                    ]);

                                    $headinfo = AccHead::where("head", 'like', '%'.$head.'%')->first();

                                    $entry = new AccVoucherEntry;
                                    $entry->date = date('Y-m-d');
                                    $entry->vno = $vinv;
                                    $entry->voucher_type = 'payment';
                                    $entry->entry_type = 'debit';
                                    $entry->debit = $debit;
                                    $entry->particular_id = $headinfo->id ?? null;
                                    $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                                    $entry->naration = null;
                                    $entry->note = "Purchase Payment";
                                    $entry->attachement = $request->attachement;
                                    $entry->user_id = auth()->user()->id;
                                    $entry->save();
                                }
                            }
                        }
                    }

                    DB::commit();
                }
                return response()->json([
                    'data' => [],
                    'success' => true,
                    'message' => ('Payments has been saved')
                ]);
            }else{
                return response()->json([
                    'data' => [],
                    'success' => false,
                    'message' => ('Invoice to pay data not found. Please try again')
                ], 404);
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response()->json([
                'data' => [],
                'success' => false,
                'message' => ('Something went wrong. Please try again')
            ], 500);
        }

    }

    public function saveBulkInvoicePayment(Request $request){
        try {
            // dd($request->all());
            //code...
            if(empty($request->payableId)){
                return response()->json([
                    'data' => [],
                    'success' => false,
                    'message' => ('Please select supplier then try again')
                ], 404);
            }
            if(!empty($request->payments) && is_array($request->payments) && !empty($request->total_pay)){
                $payments = $request->payments;
                $total_pay = $request->total_pay;
                DB::beginTransaction();

                $vnent_counting = AccVoucherEntry::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                $vinv = "VNO-" . date('Ymd') . ($vnent_counting + 1);

                if(!empty($request->bills) && is_array($request->bills)){
                    $bills = $request->bills;

                    $bank_info = ACCBank::find($payments[0]['bank']);
                    $pdetails = [
                        'payment_method' => $payments[0]['method'],
                        'bank_type' => $payments[0]['bank_type'],
                        'bank_info' => ($payments[0]['method'] != 'cash') ? $bank_info : null,
                        'payment_amount' => $payments[0]['amount']
                    ];
                    $po_array = [];
                    foreach($bills as $bill){
                        if($bill['total_payment'] > 0){
                            $po_array[] = $bill['po_number'];
                            // save the payment
                            $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                            $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                            $payment = new Payment;
                            $payment->invoice_no = $pinv;
                            $payment->date = $request->payment_date ?? date('Y-m-d');
                            $payment->payable_id = $request->payableId;
                            $payment->payable_type = Supplier::class;
                            $payment->reference_id = $bill['id'];
                            $payment->reference_type = PurchaseOrder::class;
                            $payment->seller_id = auth()->user()?->id ?? null;
                            $payment->amount = $bill['total_payment'];
                            $payment->payment_details = json_encode($pdetails);
                            $payment->payment_method = $payments[0]['method'];
                            $payment->txn_code = null;
                            $payment->user_id = auth()->user()?->id ?? null;
                            $payment->remarks = "Payment for Purchase Invoice " . $bill['po_number'];

                            if ($payment->save()) {
                                $purchaseO = PurchaseOrder::find($bill['id']);
                                if($purchaseO){
                                    $purchaseO->total_payment += floatval($bill['total_payment']);
                                    $purchaseO->total_due = $purchaseO->grand_total - ($purchaseO->total_payment);
                                    $purchaseO->save();
                                }
                            }
                        }
                    }
                    $supplier = DB::table('supplier')->where('id', $request->payableId)->first();

                    $description = "Credited to ".$supplier->name." ".$supplier->contact_number." Payment for Purchase Invoice(s): " . implode(',', $po_array);
                    if($payments[0]['method'] == 'cash'){
                        $head1 = "Cash In Hand";
                    }else if($payments[0]['method'] == 'bank'){
                        if(!empty($bank_info)){
                            $head1 = $bank_info->bank_name.' '.$bank_info->acc_no;
                        }else{
                            $head1 = $payments[0]['method'].' Payment';
                        }
                    }else{
                        $head1 = $payments[0]['method'].' Payment';
                    }

                    $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                    $vno = date('Ymd') . '-' . ($vno_counting + 1);

                    $credit = $total_pay;
                    $debit = 0;

                    AccTransaction::create([
                        'date' => date('Y-m-d'),
                        'user_id' => auth()->user()->id,
                        'vno' => $vno,
                        'head' => $head1,
                        'head_type' => !empty($bank_info) ? ACCBank::class : AccHead::class,
                        'head_id' => !empty($bank_info) ? $bank_info->id : AccHead::where('head', 'like', '%Cash In Hand%')->first()->id ?? null,
                        'debit' => $debit,
                        'credit' => $credit,
                        'description' => $description
                    ]);

                    $headinfo = AccHead::where("head", $head1)->first();

                    $entry = new AccVoucherEntry;
                    $entry->date = date('Y-m-d');
                    $entry->vno = $vinv;
                    $entry->voucher_type = 'payment';
                    $entry->entry_type = 'credit';
                    $entry->credit = $credit;
                    $entry->particular_id = $headinfo->id ?? null;
                    $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                    $entry->naration = null;
                    $entry->note = "Purchase Payment";
                    $entry->attachement = $request->attachement;
                    $entry->user_id = auth()->user()->id;
                    $entry->save();

                    $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                    $vno = date('Ymd') . '-' . ($vno_counting + 1);

                    $head = $supplier->name." ".$supplier->contact_number;
                    $credit = 0;
                    $debit = $total_pay;
                    $description = "Debited from ".$head1." Payment for Purchase Invoice(s): " . implode(',', $po_array);

                    // save related transactions
                    AccTransaction::create([
                        'date' => date('Y-m-d'),
                        'user_id' => auth()->user()->id,
                        'vno' => $vno,
                        'head' => $head,
                        'head_type' => Supplier::class,
                        'head_id' => $supplier->id,
                        'debit' => $debit,
                        'credit' => $credit,
                        'description' => $description
                    ]);

                    $headinfo = AccHead::where("head", 'like', '%'.$head.'%')->first();

                    $entry = new AccVoucherEntry;
                    $entry->date = date('Y-m-d');
                    $entry->vno = $vinv;
                    $entry->voucher_type = 'payment';
                    $entry->entry_type = 'debit';
                    $entry->debit = $debit;
                    $entry->particular_id = $headinfo->id ?? null;
                    $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                    $entry->naration = null;
                    $entry->note = "Purchase Payment";
                    $entry->attachement = $request->attachement;
                    $entry->user_id = auth()->user()->id;
                    $entry->save();

                }else{
                    // save the payment
                    $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                    $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                    $bank_info = ACCBank::find($payments[0]['bank']);
                    $pdetails = [
                        'payment_method' => $payments[0]['method'],
                        'bank_type' => $payments[0]['bank_type'],
                        'bank_info' => ($payments[0]['method'] != 'cash') ? $bank_info : null,
                        'payment_amount' => $payments[0]['amount']
                    ];

                    $payment = new Payment;
                    $payment->invoice_no = $pinv;
                    $payment->date = date('Y-m-d');
                    $payment->payable_id = $request->payableId;
                    $payment->payable_type = Supplier::class;
                    $payment->reference_id = $request->payableId;
                    $payment->reference_type = Supplier::class;
                    $payment->seller_id = auth()->user()?->id ?? null;
                    $payment->amount = $payments[0]['amount'];
                    $payment->payment_details = json_encode($pdetails);
                    $payment->payment_method = $payments[0]['method'];
                    $payment->txn_code = null;
                    $payment->user_id = auth()->user()?->id ?? null;
                    $payment->remarks = "General Payment";

                    if ($payment->save()) {
                        if($payments[0]['method'] == 'cash'){
                            $head1 = "Cash In Hand";
                        }else if($payments[0]['method'] == 'bank'){
                            if(!empty($bank_info)){
                                $head1 = $bank_info->bank_name.' '.$bank_info->acc_no;
                            }else{
                                $head1 = $payments[0]['method'].' Payment';
                            }
                        }else{
                            $head1 = $payments[0]['method'].' Payment';
                        }

                        $supplier = DB::table('supplier')->where('id', $request->payableId)->first();

                        $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                        $vno = date('Ymd') . '-' . ($vno_counting + 1);

                        $description = "Credited to ".$supplier->name." ".$supplier->contact_number." by paybill option";
                        $credit = $payments[0]['amount'];
                        $debit = 0;

                        AccTransaction::create([
                            'date' => date('Y-m-d'),
                            'user_id' => auth()->user()->id,
                            'vno' => $vno,
                            'head' => $head1,
                            'head_type' => !empty($bank_info) ? ACCBank::class : AccHead::class,
                            'head_id' => !empty($bank_info) ? $bank_info->id : AccHead::where('head', 'like', '%Cash In Hand%')->first()->id ?? null,
                            'debit' => $debit,
                            'credit' => $credit,
                            'description' => $description
                        ]);

                        $headinfo = AccHead::where("head", $head1)->first();

                        $entry = new AccVoucherEntry;
                        $entry->date = date('Y-m-d');
                        $entry->vno = $vinv;
                        $entry->voucher_type = 'payment';
                        $entry->entry_type = 'credit';
                        $entry->credit = $credit;
                        $entry->particular_id = $headinfo->id ?? null;
                        $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                        $entry->naration = null;
                        $entry->note = "Purchase Payment";
                        $entry->attachement = $request->attachement;
                        $entry->user_id = auth()->user()->id;
                        $entry->save();

                        $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                        $vno = date('Ymd') . '-' . ($vno_counting + 1);

                        $head = $supplier->name." ".$supplier->contact_number;
                        $description = "Debited from ".$head1." by paybill option";
                        $credit = 0;
                        $debit = $payments[0]['amount'];

                        // save related transactions
                        AccTransaction::create([
                            'date' => date('Y-m-d'),
                            'user_id' => auth()->user()->id,
                            'vno' => $vno,
                            'head' => $head,
                            'head_type' => Supplier::class,
                            'head_id' => $supplier->id,
                            'debit' => $debit,
                            'credit' => $credit,
                            'description' => $description
                        ]);

                        $headinfo = AccHead::where("head", 'like', '%'.$head.'%')->first();

                        $entry = new AccVoucherEntry;
                        $entry->date = date('Y-m-d');
                        $entry->vno = $vinv;
                        $entry->voucher_type = 'payment';
                        $entry->entry_type = 'debit';
                        $entry->debit = $debit;
                        $entry->particular_id = $headinfo->id ?? null;
                        $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                        $entry->naration = null;
                        $entry->note = "Purchase Payment";
                        $entry->attachement = $request->attachement;
                        $entry->user_id = auth()->user()->id;
                        $entry->save();
                    }
                }
                DB::commit();
            }else{
                return response()->json([
                    'data' => [],
                    'success' => false,
                    'message' => ('Please select payment method & amount then try again')
                ], 404);
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response()->json([
                'data' => [],
                'success' => false,
                'message' => ('Something went wrong. Please try again')
            ], 500);
        }
    }

}
