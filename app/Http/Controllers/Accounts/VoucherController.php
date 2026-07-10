<?php

namespace App\Http\Controllers\Accounts;

use App\Models\AccHead;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\AccTransaction;
use App\Models\AccVoucherEntry;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class VoucherController extends Controller
{
    public function create(){
        return view('backend.accounts.voucher.voucher_entry');
    }

    public function index(Request $request){

        $date = $request->date ?? null;
        $sort_search = null;

        $entries = DB::table("acc_voucher_entries")->select(
            'date',
            'vno',
            'voucher_type',
            DB::raw("SUM(debit) as debit"),
            DB::raw("SUM(credit) as credit")
        )->orderBy('date', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            $entries = $entries->where(function ($query) use ($sort_search) {
                $query->orWhere('vno', 'like', '%' . $sort_search . '%');
            });
        }
        if ($date != null) {
            $entries = $entries->where('date', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('date', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }
        $entries = $entries->groupBy('vno')->orderBy('date', 'desc')->paginate(20);

        return view('backend.accounts.voucher.index', compact('entries', 'date', 'sort_search'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($vno){
        $voucher = AccVoucherEntry::with('particular','user')->where("vno", $vno)->get();

        // dd($voucher);

        return view('backend.accounts.voucher.show', compact('voucher'));
    }

    public function save(Request $request){

        try {
            //code...
            if(!empty($request->transactions) && is_array($request->transactions) && !empty($request->voucher_type)){
                $transactions = $request->transactions;
                $voucher_type = $request->voucher_type;
                DB::beginTransaction();

                $vno_counting = AccVoucherEntry::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                $vinv = "VNO-" . date('Ymd') . ($vno_counting + 1);

                foreach($transactions as $index => $transaction){
                    $headinfo = AccHead::find($transaction['particular']);
                    $reverseHead = AccHead::find($index == 0 ? $transactions[1]['particular'] : $transactions[0]['particular']);
                    if($transaction['type'] == 'debit' && $transaction['debit'] > 0){
                        // save the voucher
                        $entry = new AccVoucherEntry;
                        $entry->date = $request->entry_date ?? date('Y-m-d');
                        $entry->vno = $vinv;
                        $entry->voucher_type = $voucher_type;
                        $entry->entry_type = 'debit';
                        $entry->debit = floatval($transaction['debit']);
                        $entry->particular_id = $headinfo->id;
                        $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                        $entry->naration = $transaction['naration'] ?? null;
                        $entry->note = $request->note ?? null;
                        $entry->attachement = $request->attachement;
                        $entry->user_id = auth()->user()->id;

                        if ($entry->save()) {
                            $tno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                            $tno = date('Ymd') . '-' . ($tno_counting + 1);

                            $credit = 0;
                            $debit = $entry->debit;
                            $head = $headinfo->head;
                            $description = 'Debited from '.$reverseHead->head.' Voucher No. '.$entry->vno;

                            AccTransaction::create([
                                'date' => date('Y-m-d'),
                                'user_id' => auth()->user()->id,
                                'vno' => $tno,
                                'head' => $head,
                                'head_type' => $headinfo->reference_type ?? AccHead::class,
                                'head_id' => $entry->particular_id,
                                'debit' => $debit,
                                'credit' => $credit,
                                'description' => $description
                            ]);
                        }
                    }else if($transaction['type'] == 'credit' && $transaction['credit'] > 0){
                        // save the voucher
                        $entry = new AccVoucherEntry;
                        $entry->date = $request->entry_date ?? date('Y-m-d');
                        $entry->vno = $vinv;
                        $entry->voucher_type = $voucher_type;
                        $entry->entry_type = 'credit';
                        $entry->credit = floatval($transaction['credit']);
                        $entry->particular_id = $headinfo->id;
                        $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                        $entry->naration = $transaction['naration'] ?? null;
                        $entry->note = $request->note ?? null;
                        $entry->attachement = $request->attachement;
                        $entry->user_id = auth()->user()->id;

                        if ($entry->save()) {
                            if($voucher_type == "payment"){
                                // save the payment
                                $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                                $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                                $pdetails = [
                                    'payment_method' => 'Voucher Payment',
                                    'bank_type' => 'Voucher Payment',
                                    'bank_info' => null,
                                    'payment_amount' => $entry->credit
                                ];

                                $payment = new Payment;
                                $payment->invoice_no = $pinv;
                                $payment->date = date('Y-m-d');
                                $payment->payable_id = $entry->particular_id;
                                $payment->payable_type = $headinfo->reference_type ?? AccHead::class;
                                $payment->reference_id = $entry->id;
                                $payment->reference_type = AccVoucherEntry::class;
                                $payment->seller_id = auth()->user()?->id ?? null;
                                $payment->amount = $entry->credit;
                                $payment->payment_details = json_encode($pdetails);
                                $payment->payment_method = 'Voucher Payment';
                                $payment->txn_code = null;
                                $payment->user_id = auth()->user()?->id ?? null;
                                $payment->remarks = "Voucher Payment";
                                $payment->save();
                            }

                            $tno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                            $tno = date('Ymd') . '-' . ($tno_counting + 1);

                            $credit = $entry->credit;
                            $debit = 0;
                            $head = AccHead::find($transaction['particular'])->head;
                            $description = 'Credited to '.$reverseHead->head.' Voucher No. '.$entry->vno;

                            AccTransaction::create([
                                'date' => date('Y-m-d'),
                                'user_id' => auth()->user()->id,
                                'vno' => $tno,
                                'head' => $head,
                                'head_type' => $headinfo->reference_type ?? AccHead::class,
                                'head_id' => $entry->particular_id,
                                'debit' => $debit,
                                'credit' => $credit,
                                'description' => $description
                            ]);
                        }
                    }
                }

                DB::commit();
            }else{
                return response()->json([
                    'data' => [],
                    'success' => false,
                    'message' => ('Please select particulars & amount then try again')
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
