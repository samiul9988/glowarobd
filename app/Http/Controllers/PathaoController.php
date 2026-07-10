<?php

namespace App\Http\Controllers;

use App\Events\ProductStockAffected;
use App\Models\Area;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PathaoArea;
use App\Models\PathaoMatchedArea;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Validator;

class PathaoController extends Controller
{
    public function settings(){
        $already_matched_areas = PathaoMatchedArea::select('system_area_id', 'pathao_area_id')->get()->toArray();
        return view('backend.pathao.settings', compact('already_matched_areas'));
    }

    public function generateOrMatchAreas(){
        $nextPageUrl = $matched_areas['next_page_url'] ?? null;
        $already_matched_areas = PathaoMatchedArea::select('system_area_id', 'pathao_area_id')->get()->toArray();
        $system_areas = Area::select('id', 'name', 'status')->where('status', 1)->get()->toArray();

        // Retrieve matched areas with their associated system areas
        $matchedAreas = PathaoMatchedArea::with('pathaoArea', 'systemArea')
        ->select('pathao_area_id', 'system_area_id')
        ->get();

        // Group matched areas by pathao_area_id
        $groupedAreas = $matchedAreas->groupBy('pathao_area_id');

        $result = [];

        // Iterate through the grouped areas
        foreach ($groupedAreas as $pathaoAreaId => $areas) {
            // Get the pathao_area record (assuming 'pathaoArea' is the relationship name)
            $pathaoArea = $areas->first()->pathaoArea;

            $systemAreas = $areas->pluck('systemArea'); // Retrieve all associated system areas

            $result[] = [
                'pathao_area_id' => $pathaoAreaId,
                'pathao_area' => [
                    'id' => $pathaoArea->id,
                    'area_name' => $pathaoArea->full_area_name,
                ],
                'items' => $systemAreas->toArray(),
            ];
        }

        $matched_areas = $result;

        return array(
            'status' => true,
            'view' => view('backend.pathao.matched_areas', compact('matched_areas', 'nextPageUrl'))->render(),
            'areas' => $matched_areas,
            'matched_areas' => $already_matched_areas,
            'system_areas' => $system_areas,
            'nextPageUrl' => $nextPageUrl
        );
    }

    public function searchSystemArea(Request $request) {
        $system_areas = isset($request->q)
            ? Area::select('id', 'city_id', 'name')->where('name', 'LIKE', "%$request->q%")
            : new Area();

        return $system_areas->paginate(10)->toArray();
    }

    public function searchPathaoArea(Request $request) {
        if (filled($request->q)) {
            $pathao_areas = PathaoArea::where(function($query) use ($request) {
                $query->where('full_area_name', 'LIKE', "%$request->q%")
                    ->orWhere('area_name', 'LIKE', "%$request->q%");
            })->select('id', 'full_area_name as area_name');
        } else {
            $pathao_areas = new PathaoArea();
        }

        return $pathao_areas->paginate(10)->toArray();
    }

    public function saveMatchedAreas(Request $request){
        // Retrieve the form data from the request
        $system_areas = $request->input('system_areas');
        $pathao_areas = $request->input('pathao_areas');
        // dd($request->all());
        try {
            //code...
            DB::beginTransaction();
            // save the corresponding values from arrays
            DB::table('pathao_matched_areas')->truncate();


            $unable2Saved = [];
            foreach ($system_areas as $index => $value) {
                foreach($value as $key => $val){
                    if(!isset($pathao_areas[$index][0]) || !intval($pathao_areas[$index][0])){
                        $unable2Saved[] = $val;
                        continue;
                    }else{
                        $area = PathaoMatchedArea::where('system_area_id', intval($val))->first();
                        if($area){
                            $area->pathao_area_id = intval($pathao_areas[$index][0]);
                            $area->save();
                        }else{
                            $newarea = new PathaoMatchedArea();
                            $newarea->pathao_area_id = intval($pathao_areas[$index][0]);
                            $newarea->system_area_id = intval($val);
                            $newarea->save();
                        }
                    }
                }
            }

            if(count($unable2Saved) > 0){
                $unable2Saved = implode(',', $unable2Saved);
                flash(('Some areas could not be matched because unmatched/empty areas'))->success();
                return back();
            }
            flash(('Matched areas have been saved'))->success();
            return back();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            flash(('Some areas could not be matched because unmatched/empty areas'))->error();
            return back();
        }
    }

    public function updateOrderStatus(Request $request){
        // Retrieve the form data from the request
        // Log::info($request);
        $payloadData = $request->all();
        if(isset($payloadData)){
            Log::channel('pathao_callback')->info('Pathao Callback With: ', [$payloadData]);
            if(isset($payloadData['order_status_slug'])){
                $orderData = Order::where('code', $payloadData['merchant_order_id'])->first();

                if($orderData){
                    if(isset($orderData->user_id)){
                        $user = User::find($orderData->user_id);
                        if($payloadData['order_status_slug'] == 'Delivered'){
                            $orderData->delivery_status = "delivered";
                            // Update the order's payment status and due amount with payment data
                            $payment = make_payment($orderData, [
                                'method' => 'pathao',
                                'bank_type' => 'cash',
                            ]);
                            // Even if the payment data is not inserted successfully, update the order's payment status and due amount
                            if(!$payment){
                                $orderData->payment_status = "paid";
                                $orderData->due_amount = 0;
                            }
                            OrderDetail::where('order_id', $orderData->order_id)->update([
                                'delivery_status' => "delivered",
                                'payment_status' => "paid"
                            ]);
                            $user->delivered_order = $user->delivered_order + 1;
                        }elseif($payloadData['order_status_slug'] == 'Assigned_for_Delivery'){
                            $orderData->delivery_status = "on_the_way";
                            OrderDetail::where('order_id', $orderData->order_id)->update([
                                'delivery_status' => "on_the_way"
                            ]);
                        }elseif($payloadData['order_status_slug'] == 'Return'){
                            $orderData->delivery_status = "returned";
                            $user->delivered_order = $user->delivered_order - 1;
                            OrderDetail::where('order_id', $orderData->order_id)->update([
                                'delivery_status' => "returned"
                            ]);
                            foreach($orderData->orderDetails as $orderDetail){
                                $variant = $orderDetail->variation;
                                if ($orderDetail->variation == null) {
                                    $variant = '';
                                }

                                $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $variant)->first();

                                if ($product_stock != null) {
                                    $product_stock->qty += $orderDetail->quantity;
                                    $product_stock->save();

                                    $isAddition = true;
                                    // Store Stock Transaction
                                    $transaction = [
                                        'product_id'    => (int)$orderDetail->product_id,
                                        'variant'       => empty($product_stock->variant) ? null : $product_stock->variant,
                                        'sku'           => $product_stock->sku ?? null,
                                        'qty'           => abs($orderDetail->quantity),
                                        'isAddition'    => ($isAddition) ? 1 : 0,
                                        'isSubtraction' => ($isAddition) ? 0 : 1,
                                        'purpose'       => 'order_returned',
                                        'purpose_id'    => $orderData->id ?? 0,
                                        'note'          => 'Order Returned From Pathao, Ref. ID = '.$orderData->code ?? 'Unknown'
                                    ];
                                    // Trigger The Event
                                    event(new ProductStockAffected($transaction));
                                }
                            }
                        }
                        $orderData->save();
                        $user->save();
                    }
                }else{
                    Log::channel('pathao_callback')->info('Order not found with code: ', [$payloadData['merchant_order_id']]);
                }
            }else{
                Log::channel('pathao_callback')->info('payloadData[order_status_slug] not found');
            }
        }else{
            Log::channel('pathao_callback')->info('Pathao Callback With: ', ['No Payload']);
        }
    }

    public function saveSingleMatchedArea(Request $request){

        $validator = Validator::make($request->all(), [
            'system_areas' => 'required',
            'pathao_areas' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Something Missing',
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $system_areas[] = explode(',', $request->input('system_areas'));
        $pathao_areas[] = explode(',', $request->input('pathao_areas'));

        try {
            // save the corresponding values from arrays
            $unable2Saved = [];
            $newareas = [];
            foreach ($system_areas as $index => $value) {
                foreach($value as $key => $val){
                    // insert or update
                    if(!isset($pathao_areas[$index][0]) || !intval($pathao_areas[$index][0])){
                        $unable2Saved[] = $val;
                        continue;
                    }else{
                        $area = PathaoMatchedArea::where('system_area_id', intval($val))->first();
                        if($area){
                            $area->pathao_area_id = intval($pathao_areas[$index][0]);
                            $area->save();
                        }else{
                            $newarea = new PathaoMatchedArea();
                            $newarea->pathao_area_id = intval($pathao_areas[$index][0]);
                            $newarea->system_area_id = intval($val);
                            if($newarea->save()){
                                $newareas[] = $newarea->id;
                            }
                        }
                    }
                }
            }

            if(count($unable2Saved) > 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Areas could not be matched because unmatched/empty areas'
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Matched areas have been saved'
            ]);

        } catch (\Throwable $th) {
            //throw $th;
            // DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function deleteSingleMatchedArea(Request $request){
        $validator = Validator::make($request->all(), [
            'system_areas' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Something Missing',
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $system_areas = explode(',', $request->input('system_areas'));

        try {
            // delete the corresponding values from arrays
            $unableToDelete = [];

            foreach ($system_areas as $val) {
                $area = PathaoMatchedArea::where('system_area_id', intval($val))->first();

                if ($area) {
                    $area->delete();
                } else {
                    $unableToDelete[] = $val;
                }
            }

            if (count($unableToDelete) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some areas could not be deleted because they were not found',
                    'unmatched_areas' => $unableToDelete,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Matched areas have been deleted successfully'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
