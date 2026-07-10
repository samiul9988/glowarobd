<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\CallLog;
use Illuminate\Http\Request;
use App\Models\OrderFeedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CallLogController extends Controller
{
    public function store(Request $request){
        // dd($request->all());
        try{
            $request->validate([
                'reference' => 'required',
                'reference_id' => 'required|exists:'. ($request->reference === 'customer' ? 'users' : 'orders') .',id',
                'status' => 'required|string|max:255',
                'note' => 'nullable|string|max:255',
                'duration' => 'nullable|numeric|min:0',
            ]);
        }catch(\Illuminate\Validation\ValidationException $e){
            if($request->ajax() || $request->wantsJson()){
                return response()->json([
                    'success' => true,
                    'message' => $e->getMessage()
                ], 421);
            }
            flash(($e->getMessage()))->error();
            return redirect()->back()->withInput();
        }

        $model = ($request->reference === 'customer' ? User::find($request->reference_id) : Order::find($request->reference_id));

        $callLog = $model->addCallLog([
            'status' => $request->status,
            'rescheduled_at' => $request->rescheduled_at ?? null,
            'note' => $request->note,
            'duration' => $request->duration ?? 0,
            'called_by' => auth()->user()->id,
        ]);

        if('feedback' === $request->type) {
            OrderFeedback::where('call_log_id', Auth::id() . '_' . $request->user_id)
                ->update(['call_log_id' => $callLog->id]);
        }

        if (filled($request->rescheduled_at) && \Carbon\Carbon::parse($request->rescheduled_at)->isToday()) {
            Cache::forget('rescheduledCount_'.now()->format('d M'));
        }

        if($request->ajax() || $request->wantsJson()){
            return response()->json([
                'success' => true,
                'message' => ('Call log created successfully.')
            ], 201);
        }
        flash(('Call log created successfully.'))->success();
        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        $callLog = CallLog::findOrFail($id);
        if (!is_null($callLog->rescheduled_at) && \Carbon\Carbon::parse($callLog->rescheduled_at)->isToday()) {
            Cache::forget('rescheduledCount_'.now()->format('d M'));
        }
        $callLog->delete();


        if($request->ajax() || $request->wantsJson()){
            return response()->json([
                'success' => true,
                'message' => ('Call log deleted successfully.')
            ]);
        }

        flash(('Call log deleted successfully.'))->success();
        return redirect()->back();
    }
}
