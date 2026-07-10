<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SmsLogController extends Controller
{
    public function index(Request $request)
    {
        $smsLogs = \App\Models\SmsLog::with('user:id,name,phone')
            ->when($request->date && count(explode(' to ', $request->date)) == 2, function ($query) use ($request) {
                $dates = explode(' to ', $request->date);
                if (count($dates) == 2) {
                    $start = Carbon::parse($dates[0])->startOfDay();
                    $end = Carbon::parse($dates[1])->endOfDay(); // 23:59:59
                    $query->whereBetween('created_at', [$start, $end]);
                }
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->phone, function ($query) use ($request) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            })
            ->latest('created_at')
            ->paginate(50);
        return view('backend.reports.sms_log_report', compact('smsLogs'));
    }
}
