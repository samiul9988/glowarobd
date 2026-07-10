<?php

namespace App\Http\Controllers\Accounts;

use Carbon\Carbon;
use App\Models\AccHead;
use Illuminate\Http\Request;
use App\Models\AccVoucherEntry;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function ledger(Request $request)
    {
        $opening_balance = 0;
        $from = Carbon::now()->startOfMonth()->toDateString();
        $to = Carbon::now()->toDateString();
        if (filled($request->date) && count(explode(" to ", $request->date)) === 2) {
            $dateRange = explode(" to ", $request->date);
            $from = Carbon::parse($dateRange[0])->toDateString();
            $to = Carbon::parse($dateRange[1])->toDateString();
        }

        if($request->has('submit') && $request->submit == 'yes'){
            $query = DB::table('acc_transactions')->where('head', $request->head);
            $transactions = (clone $query)
                ->whereBetween('date', [$from, $to])
                ->get();
            // $beforedebts = (clone $query)
            //     ->whereDate('date', '<', $from)
            //     ->sum('debit');
            // $beforecreds = (clone $query)
            //     ->whereDate('date', '<', $from)
            //     ->sum('credit');
            // $opening_balance = $beforedebts - $beforecreds;
            $opening_balance = (clone $query)
                ->whereDate('date', '<', $from)
                ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
                ->value('balance');
        }else{
            $transactions = collect();
        }

        $heads = AccHead::select('id', 'head', 'sub_head')->whereNotNull('sub_head')->groupBy('id')->get();

        return view('backend.accounts.reports.ledger_report', compact('opening_balance', 'heads', 'transactions'));
    }

    public function trial_balance(Request $request)
    {
        $date = $request->date;
        if (!empty($date)) {
            $startDate = date('Y-m-d', strtotime(explode(" to ", $date)[0]));
            $endDate = date('Y-m-d', strtotime(explode(" to ", $date)[1]));
            try {
                $transactions = DB::table('acc_transactions')
                ->select('head')
                ->selectRaw('(SUM(CASE WHEN date < ? THEN debit ELSE 0 END) - SUM(CASE WHEN date < ? THEN credit ELSE 0 END)) as opening_balance', [$startDate, $startDate])
                ->selectRaw('SUM(CASE WHEN date BETWEEN ? AND ? THEN debit ELSE 0 END) as debit_balance', [$startDate, $endDate])
                ->selectRaw('SUM(CASE WHEN date BETWEEN ? AND ? THEN credit ELSE 0 END) as credit_balance', [$startDate, $endDate])
                ->groupBy('head')
                ->get();
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }else{
            $startDate = now()->startOfMonth();
            $endDate = date('Y-m-d');

            $transactions = DB::table('acc_transactions')
            ->select('head')
            ->selectRaw('(SUM(CASE WHEN date < ? THEN debit ELSE 0 END) - SUM(CASE WHEN date < ? THEN credit ELSE 0 END)) as opening_balance', [$startDate, $startDate])
            ->selectRaw('SUM(CASE WHEN date BETWEEN ? AND ? THEN debit ELSE 0 END) as debit_balance', [$startDate, $endDate])
            ->selectRaw('SUM(CASE WHEN date BETWEEN ? AND ? THEN credit ELSE 0 END) as credit_balance', [$startDate, $endDate])
            ->groupBy('head')
            ->get();
        }

        return view('backend.accounts.reports.trial_balance', compact('date', 'transactions', 'startDate', 'endDate'));
    }

    public function sub_head_ledger(Request $request)
    {
        $date = $request->date;
        $subhead = $request->subhead;

        $subheads = AccHead::select('id', 'head')->whereNull('sub_head')->groupBy('id')->get();
        if($request->has('submit') && $request->submit == 'yes'){
            $heads = DB::table('acc_heads')->select('head')->where('sub_head', 'like', '%'.$subhead.'%')->groupBy('id')->get();

            if ($date != null) {
                $start = date('Y-m-d', strtotime(explode(" to ", $date)[0]));
                $end = date('Y-m-d', strtotime(explode(" to ", $date)[1]));
            }else{
                $start = now()->startOfMonth();
                $end = date('Y-m-d');
            }

            $transactions = [];
            if(count($heads) > 0){
                foreach($heads as $head){
                    $htransactions = DB::table('acc_transactions')->where('head', 'like', '%'.$head->head.'%')->whereBetween('date', [$start, $end])->get();
                    $htotaldebit = $htransactions->sum('debit');
                    $htotalcredit = $htransactions->sum('credit');

                    $btransactions = DB::table('acc_transactions')->where('head', 'like', '%'.$head->head.'%')->whereDate('date', '<', $start)->get();
                    $btotaldebit = $btransactions->sum('debit');
                    $btotalcredit = $btransactions->sum('credit');

                    $opening_balance = $btotaldebit - $btotalcredit;

                    $hdata = [];
                    $hdata['head'] = $head->head;
                    $hdata['opening'] = $opening_balance;
                    $hdata['debit'] = $htotaldebit;
                    $hdata['credit'] = $htotalcredit;
                    $hdata['closing'] = $opening_balance + $htotaldebit - $htotalcredit;

                    $transactions[] = (object) $hdata;
                }
            }else{
                $htransactions = DB::table('acc_transactions')->where('head', 'like', '%'.$subhead.'%')->whereBetween('date', [$start, $end])->get();
                $htotaldebit = $htransactions->sum('debit');
                $htotalcredit = $htransactions->sum('credit');

                $btransactions = DB::table('acc_transactions')->where('head', 'like', '%'.$subhead.'%')->whereDate('date', '<', $start)->get();
                $btotaldebit = $btransactions->sum('debit');
                $btotalcredit = $btransactions->sum('credit');

                $opening_balance = $btotaldebit - $btotalcredit;

                $hdata = [];
                $hdata['head'] = $subhead;
                $hdata['opening'] = $opening_balance;
                $hdata['debit'] = $htotaldebit;
                $hdata['credit'] = $htotalcredit;
                $hdata['closing'] = $opening_balance + $htotaldebit - $htotalcredit;

                $transactions[] = (object) $hdata;
            }
        }else{
            $transactions = [];
        }

        $transactions = collect($transactions);

        return view('backend.accounts.reports.sub_head_ledger', compact('subhead', 'date', 'subheads', 'transactions'));
    }

    public function daily_report(Request $request)
    {
        $date = $request->date;
        $opening_balance = 0;

        if($request->has('submit') && $request->submit == 'yes'){
            $transactions = [];
            if ($date != null) {
                $start = date('Y-m-d', strtotime(explode(" to ", $date)[0]));
                $end = date('Y-m-d', strtotime(explode(" to ", $date)[1]));

                $headsBanks = DB::table("acc_heads")->where("sub_head", "like", '%Cash & Bank%')->pluck('head');
                foreach($headsBanks as $head){
                    $beforedebts = DB::table("acc_transactions")->where('head', 'like', '%' . $head . '%')->whereDate('date', '<', $start)->sum('debit');
                    $beforecreds = DB::table("acc_transactions")->where('head', 'like', '%' . $head . '%')->whereDate('date', '<', $start)->sum('credit');
                    $opening_balance = $beforedebts - $beforecreds;

                    $htransactions = DB::table('acc_transactions')->where('head', 'like', '%' . $head . '%')->whereBetween('date', [$start, $end])->get();
                    $htotaldebit = $htransactions->sum('debit');
                    $htotalcredit = $htransactions->sum('credit');

                    $hdata = [];
                    $hdata['head'] = $head;
                    $hdata['transactions'] = $htransactions;
                    $hdata['opening'] = $opening_balance;
                    $hdata['debit'] = $htotaldebit;
                    $hdata['credit'] = $htotalcredit;
                    $hdata['closing'] = $opening_balance + $htotaldebit - $htotalcredit;

                    $transactions[] = (object) $hdata;
                }
            }else{
                $start = date('Y-m-d');
                $end = date('Y-m-d');

                $headsBanks = DB::table("acc_heads")->where("sub_head", "like", '%Cash & Bank%')->pluck('head');
                foreach($headsBanks as $head){
                    $beforedebts = DB::table("acc_transactions")->where('head', 'like', '%' . $head . '%')->whereDate('date', '<', $start)->sum('debit');
                    $beforecreds = DB::table("acc_transactions")->where('head', 'like', '%' . $head . '%')->whereDate('date', '<', $start)->sum('credit');
                    $opening_balance = $beforedebts - $beforecreds;

                    $htransactions = DB::table('acc_transactions')->where('head', 'like', '%' . $head . '%')->whereBetween('date', [$start, $end])->get();
                    $htotaldebit = $htransactions->sum('debit');
                    $htotalcredit = $htransactions->sum('credit');

                    $hdata = [];
                    $hdata['head'] = $head;
                    $hdata['transactions'] = $htransactions;
                    $hdata['opening'] = $opening_balance;
                    $hdata['debit'] = $htotaldebit;
                    $hdata['credit'] = $htotalcredit;
                    $hdata['closing'] = $opening_balance + $htotaldebit - $htotalcredit;

                    $transactions[] = (object) $hdata;
                }
            }

        }else{
            $transactions = [];
        }

        $transactions = collect($transactions);

        // dd($transactions);

        return view('backend.accounts.reports.daily_report', compact('date', 'transactions'));
    }

    public function expense_report(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();
        $head = $request->head ?? null;

        if (filled($request->date)) {
            $dateRange = explode(' to ', $request->date);
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate   = Carbon::parse($dateRange[1])->endOfDay();
            }
        }
        $cacheKey = 'expense_report_' . $startDate->toDateString() . '_' . $endDate->toDateString() . '_' . $head;
        // Cache::forget($cacheKey); // For testing purposes, remove this line in production
        $expenses = Cache::remember($cacheKey, now()->addHours(3), function () use ($startDate, $endDate, $head) {
            return AccVoucherEntry::query()
                ->where('particular_type', \App\Models\AccHead::class)
                ->whereNotNull('particular_id')
                ->where('particular_id', '!=', 2) // Exclude 'Purchase' head
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereHasMorph(
                    'particular',
                    [\App\Models\AccHead::class], // only apply conditions to AccHead
                    function ($q) use ($head) {
                        $q->where('parent_head', 'expense');
                        if ($head) {
                            $q->where('head', $head);
                        }
                    }
                )
                ->with(['particular', 'user:id,name'])
                ->get();
        });

        // return response()->json([
        //     'expenses' => $expenses,
        // ]);
        $headCacheKey = 'expense_heads';
        $heads = Cache::remember($headCacheKey, now()->addHours(12), function () {
            return AccHead::select('id', 'head', 'sub_head')
                ->where('parent_head', 'expense')
                ->whereNotNull('sub_head')
                ->groupBy('id')
                ->get();
        });

        return view('backend.accounts.reports.expense_report', compact('expenses', 'heads'));
    }

    public function expense_report_chart(Request $request)
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        if (filled($request->date)) {
            $dateRange = explode(' to ', $request->date);
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse($dateRange[0])->startOfDay();
                $endDate   = Carbon::parse($dateRange[1])->endOfDay();
            }
        }
        $cacheKey = 'expense_report_chart_' . $startDate->toDateString() . '_' . $endDate->toDateString();
        Cache::forget($cacheKey); // For testing purposes, remove this line in production
        $headGroups = Cache::remember($cacheKey, now()->addHours(3), function () use ($startDate, $endDate) {
            return AccVoucherEntry::query()
                ->selectRaw('
                    acc_heads.head,
                    SUM(acc_voucher_entries.debit) as total_debit,
                    SUM(acc_voucher_entries.credit) as total_credit,
                    COUNT(acc_voucher_entries.id) as total_entries
                ')
                ->join('acc_heads', function ($join) {
                    $join->on('acc_heads.id', '=', 'acc_voucher_entries.particular_id')
                        ->where('acc_voucher_entries.particular_type', \App\Models\AccHead::class);
                })
                ->where('acc_voucher_entries.particular_id', '!=', 2)
                ->whereBetween('acc_voucher_entries.created_at', [$startDate, $endDate])
                ->where('acc_heads.parent_head', 'expense')
                ->groupBy('acc_heads.head')
                ->orderBy('acc_heads.head')
                ->get();
        });
        $totalExpense = $headGroups->sum('total_debit');
        return response()->json([
            'success' => true,
            'groups' => $headGroups->map(function ($group) use ($totalExpense) {
                return [
                    'head' => $group->head,
                    'total_debit' => single_price($group->total_debit),
                    'total_credit' => single_price($group->total_credit),
                    'total_entries' => $group->total_entries,
                    'percentage' => $totalExpense > 0 ? round(($group->total_debit / $totalExpense) * 100, 2) : 0,
                ];
            }),
            'total_expense' => single_price($totalExpense),
        ]);
    }
}
