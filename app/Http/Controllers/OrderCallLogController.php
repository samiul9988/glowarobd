<?php

namespace App\Http\Controllers;

use App\Models\OrderCallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderCallLogController extends Controller
{
    public function store(Request $request){
        // dd($request->all());
        try{
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'status' => 'required|string|max:255',
                'note' => 'nullable|string|max:255',
                'duration' => 'nullable|numeric|min:0',
            ]);
        }catch(\Illuminate\Validation\ValidationException $e){
            flash(($e->getMessage()))->error();
            return redirect()->back()->withInput();
        }

        $callLog = new OrderCallLog();
        $callLog->order_id = $request->order_id;
        $callLog->status = $request->status;
        $callLog->note = $request->note;
        $callLog->called_by = auth()->user()->id;
        $callLog->duration = $request->duration;
        $callLog->save();

        Log::channel('custom')->info('Order call log created from route '.request()->url(), $callLog->toArray());
        flash(('Call log created successfully.'))->success();
        return redirect()->back();
    }

    public function addCallLog(Request $request)
    {
        try{
            $callLog = new OrderCallLog();
            $callLog->order_id = $request->order_id;
            $callLog->called_by = auth()->user()->id;
            $callLog->status = $request->status;
            $callLog->note = $request->note;
            $callLog->duration = $request->duration ?? 0;
            $callLog->save();

            Log::channel('custom')->info('Order call log created from route '.request()->url(), $callLog->toArray());
            return response()->json([
                'success' => true,
                'message' => ('Call log created successfully.'),
            ], 201);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => ('Failed to create call log. Please try again.'),
            ], 500);
        }
    }

    function destroy($id)
    {
        $callLog = OrderCallLog::findOrFail($id);
        $callLog->delete();

        flash(('Call log deleted successfully.'))->success();
        return redirect()->back();
    }
}
