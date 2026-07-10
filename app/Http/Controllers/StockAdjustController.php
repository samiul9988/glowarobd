<?php

namespace App\Http\Controllers;

use App\Events\ProductStockAdjusted;
use App\Events\ProductStockAffected;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockAdjust;
use App\Models\StockAdjustItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StockAdjustController extends Controller
{
    //

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $seller_id = null;
        $StockAdjust = StockAdjust::with('sellername','stockAdjustDetails')->latest();

        if ($request->has('user_id') && $request->user_id != null) {
            $seller_id = $request->user_id;
            $StockAdjust = $StockAdjust->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $sort_search = $request->search;
            $StockAdjust = $StockAdjust->where(function ($query) use ($sort_search) {
                $query->orWhere('sa_number', 'like', '%' . $sort_search . '%');
            });
        }

        if ($date != null) {
            $StockAdjust = $StockAdjust->whereBetween('sa_date', [strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[0]))), strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[1])))]);
        }

        $stockadjustments = $StockAdjust->paginate(15);

        return view('backend.product.stock_adjust.index', compact('stockadjustments','sort_search', 'date','seller_id'));
    }
    public function all_stock_adjustments(Request $request)
    {
        $date = $request->date;
        $sort_search = null;

        $StockAdjust = StockAdjust::where('user_id', Auth::id())->orderBy('sa_date', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            //$orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            $purchaseorders = $StockAdjust->where(function ($query) use ($sort_search) {
                $query->orWhere('sa_number', 'like', '%' . $sort_search . '%');
            });
        }

        if ($date != null) {
            $orders = $StockAdjust->where('sa_date', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('sa_date', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $stockadjustments = $StockAdjust->paginate(15);

        return view('frontend.user.seller.stock_adjust.create', compact('stockadjustments','sort_search', 'date'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Cache::remember('all_published_products', now()->addHours(3), function () {
            return Product::latest()->published()->get();
        });
        return view('backend.product.stock_adjust.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $sa_type = $request->sa_type;
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $quantity = $request->stock_quantity;
        $stockadjustmodel = new StockAdjust;
        $stockadjustmodel->user_id = Auth::user()->id;
        $stockadjustmodel->sa_number = config('app.stock_adjust_no_prefix').date('YmdHis') . rand(10, 99);
        $stockadjustmodel->sa_type =  $sa_type;
        $stockadjustmodel->sa_date = strtotime(date('Y-m-d',strtotime($request->sa_date)));
        $stockadjustmodel->note = $request->note;
        $stockadjustmodel->attachments = $request->photos;
        if($stockadjustmodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($quantity)){
                for($i=0; $i<count($product_id);  $i++){
                    // PO order item create
                    $stockadjustitemmodel = new StockAdjustItem;
                    $stockadjustitemmodel->stock_adjust_id = $stockadjustmodel->id;
                    $stockadjustitemmodel->product_id = $product_id[$i];
                    $stockadjustitemmodel->variant = $varient_id[$i];
                    $stockadjustitemmodel->qty = $quantity[$i];
                    $stockadjustitemmodel->save();
                    // product stock update
                    $isAddition = true;
                    $productstock = ProductStock::findOrFail($varient_id[$i]);
                    if($sa_type == 'damage' || $sa_type == 'others'){
                        $isAddition = false;
                        $productstock->qty = $productstock->qty - $quantity[$i];
                    }elseif($sa_type == 'returned'){
                        $isAddition = true;
                        $productstock->qty = $productstock->qty + $quantity[$i];
                    }
                    $productstock->save();

                    // Store Stock Transaction
                    $transaction = [
                        'product_id'    => (int)$product_id[$i],
                        'variant'       => empty($productstock->variant) ? null : $productstock->variant,
                        'sku'           => $productstock->sku ?? null,
                        'qty'           => $quantity[$i],
                        'isAddition'    => ($isAddition) ? 1 : 0,
                        'isSubtraction' => ($isAddition) ? 0 : 1,
                        'purpose'       => $stockadjustmodel->sa_type ?? 'adjust',
                        'purpose_id'    => $stockadjustmodel->id,
                        'note'          => 'New Stock Adjust, Ref ID = '.$stockadjustitemmodel->id ?? 'Unknown'.''
                    ];
                    // Trigger The Event
                    event(new ProductStockAdjusted($stockadjustitemmodel));
                    event(new ProductStockAffected($transaction));
                }
                $stockadjustmodel->save();
            }
        }

        flash(('Stock Adjustment & product stock has been updated successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();
        return redirect()->route('stock-adjust.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        try {
            $decryptedId = decrypt($id);
        } catch (\Exception $e) {
            abort(404, 'Staff not found.');
        }
        $stock_adjust = StockAdjust::findOrFail($decryptedId);
        return view('backend.product.stock_adjust.show', compact('stock_adjust'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $stock_adjust = StockAdjust::findOrFail($id);
        $products = Cache::remember('all_published_products', now()->addHours(3), function () {
            return Product::latest()->published()->get();
        });

        return view('backend.product.stock_adjust.edit', compact('products', 'stock_adjust'));
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
        $item_id = $request->item_id;
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $quantity = $request->stock_quantity;
        $stockadjustmodel = StockAdjust::findOrFail($id);
        $stockadjustmodel->sa_date = strtotime(date('Y-m-d',strtotime($request->sa_date)));
        $stockadjustmodel->sa_type = $request->sa_type;
        $stockadjustmodel->note = $request->note;
        $stockadjustmodel->attachments = $request->sa_type == 'damage' ? $request->photos : null;
        DB::beginTransaction();
        if($stockadjustmodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($quantity)){
                for($i=0; $i<count($product_id); $i++){
                    $productstock = ProductStock::findOrFail($varient_id[$i]);
                    // SA order item create
                    if($item_id[$i]!=0){
                        $stockadjustitemmodel = StockAdjustItem::findOrFail($item_id[$i]);
                        $item_qty = $stockadjustitemmodel->qty;
                        if($item_qty <= $productstock->qty){
                            if($stockadjustmodel->sa_type == 'damage' || $stockadjustmodel->sa_type == 'others'){
                                $productstock_qty = $productstock->qty + $item_qty;
                                $productstock->qty = $productstock_qty - $quantity[$i];
                            }else{
                                $productstock_qty = $productstock->qty - $item_qty;
                                $productstock->qty = $productstock_qty + $quantity[$i];
                            }
                        }

                        $isAddition = ($item_qty > $quantity[$i]) ? false : true;
                        // Store Stock Transaction
                        $transaction = [
                            'product_id'    => (int)$product_id[$i],
                            'variant'       => empty($productstock->variant) ? null : $productstock->variant,
                            'sku'           => $productstock->sku ?? null,
                            'qty'           => abs($item_qty - $quantity[$i]),
                            'isAddition'    => ($isAddition) ? 1 : 0,
                            'isSubtraction' => ($isAddition) ? 0 : 1,
                            'purpose'       => $stockadjustmodel->sa_type ?? 'adjust',
                            'purpose_id'    => $stockadjustmodel->id,
                            'note'          => 'Updated Stock Adjust, Ref. ID = '.$stockadjustitemmodel->id ?? 'Unknown'.''
                        ];
                        // Trigger The Event
                        event(new ProductStockAdjusted($stockadjustitemmodel));
                        event(new ProductStockAffected($transaction));

                    }else{
                        $stockadjustitemmodel = new StockAdjustItem;
                        $isAddition = true;
                        if($stockadjustmodel->sa_type == 'damage' || $stockadjustmodel->sa_type == 'others'){
                            $isAddition = false;
                            $productstock->qty = $productstock->qty - $quantity[$i];
                        }else{
                            $isAddition = true;
                            $productstock->qty = $productstock->qty + $quantity[$i];
                        }
                    }
                    $stockadjustitemmodel->stock_adjust_id = $stockadjustmodel->id;
                    $stockadjustitemmodel->product_id = $product_id[$i];
                    $stockadjustitemmodel->variant = $varient_id[$i];
                    $stockadjustitemmodel->qty = $quantity[$i];
                    $item_qty = $stockadjustitemmodel->qty;
                    $stockadjustitemmodel->save();
                    // product stock update
                    if(!$productstock->save()){
                        DB::rollBack();
                    }

                    // Store Stock Transaction
                    $transaction = [
                        'product_id'    => (int)$product_id[$i],
                        'variant'       => empty($productstock->variant) ? null : $productstock->variant,
                        'sku'           => $productstock->sku ?? null,
                        'qty'           => abs($item_qty - $quantity[$i]),
                        'isAddition'    => ($isAddition) ? 1 : 0,
                        'isSubtraction' => ($isAddition) ? 0 : 1,
                        'purpose'       => $stockadjustmodel->sa_type ?? 'adjust',
                        'purpose_id'    => $stockadjustmodel->id,
                        'note'          => 'Updated Stock Adjust New Added, Ref. ID = '.$stockadjustitemmodel->id ?? 'Unknown'.''
                    ];
                    // Trigger The Event
                    event(new ProductStockAdjusted($stockadjustitemmodel));
                    event(new ProductStockAffected($transaction));
                }
                if(!$stockadjustmodel->save()){
                    DB::rollBack();
                }
            }
        }
        DB::commit();

        flash(('Stock adjust & product stock has been updated successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

        return redirect()->route('stock-adjust.index');
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

    public function bulk_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }
    public function delete_item(Request $request)
    {
        $item_id = $request->item_id;
        $item_details = StockAdjustItem::findOrFail($item_id);
        // $product_stock = ProductStock::findOrFail($item_details->variant);
        $data['status'] = false;
        $data['msg'] = 'Product stock quantity is less than item quantity';
        if($item_details->qty <= $item_details->product_stock->qty){
            $product_stock_model = $item_details->product_stock;
            $isAddition = true;
            if($item_details->stockadjust->sa_type == 'damage' || $item_details->stockadjust->sa_type == 'others'){
                $isAddition = false;
                $product_stock_model->qty = $product_stock_model->qty + $item_details->qty;
            }else{
                $isAddition = true;
                $product_stock_model->qty = $product_stock_model->qty - $item_details->qty;
            }
            DB::beginTransaction();
            if($product_stock_model->save()){
                if($item_details->delete()){
                    $data['status'] = true;
                    $data['msg'] = 'Item deleted & product stock updated successfully';
                }else{
                    $data['status'] = false;
                    $data['msg'] = 'Item not deleted!';
                    DB::rollBack();
                }

                $transaction = [
                    'product_id'    => (int)$item_details->product_id,
                    'variant'       => empty($product_stock_model->variant) ? null : $product_stock_model->variant,
                    'sku'           => $product_stock_model->sku ?? null,
                    'qty'           => $item_details->qty,
                    'isAddition'    => ($isAddition) ? 1 : 0,
                    'isSubtraction' => ($isAddition) ? 0 : 1,
                    'purpose'       => 'adjust_delete_item',
                    'purpose_id'    => $item_details->id,
                    'note'          => 'Delete Adjusted Item, Ref. ID = '.$item_details->id ?? 'Unknown'.''
                ];
                // Trigger The Event
                event(new ProductStockAdjusted($item_details));
                event(new ProductStockAffected($transaction));
            }
            DB::commit();
        }
        return response()->json($data);

    }

// ************************************* Frontend Purchase Order *************************************

    public function index_seller(Request $request)
    {
        $date = $request->date;
        $sort_search = null;

        $StockAdjust = StockAdjust::orderBy('sa_date', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            //$orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            $purchaseorders = $StockAdjust->where(function ($query) use ($sort_search) {
                $query->orWhere('sa_number', 'like', '%' . $sort_search . '%');
            });
        }

        if ($date != null) {
            $orders = $StockAdjust->where('sa_date', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('sa_date', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $purchaseorders = $StockAdjust->where('user_id', Auth::user()->id)->paginate(15);

        return view('frontend.user.seller.stock_adjust.index', compact('purchaseorders','sort_search', 'date'));
    }

    public function create_seller()
    {
        $products = Product::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return view('frontend.user.seller.stock_adjust.create', compact('products'));
    }
    public function edit_seller($id)
    {
        $purchase_order = StockAdjust::findOrFail($id);
        $products = Product::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();

        return view('frontend.user.seller.purchase_order.edit', compact('suppliers','products', 'purchase_order'));
    }

    public function seller_update(Request $request, $id)
    {
        $item_id = $request->item_id;
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $price = $request->stock_price;
        $quantity = $request->stock_quantity;
        $stockadjustmodel = StockAdjust::findOrFail($id);
        $stockadjustmodel->purchase_date = strtotime(date('Y-m-d',strtotime($request->purchase_date)));
        DB::beginTransaction();
        if($stockadjustmodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($price) && is_array($quantity)){
                $grand_total = 0;
                for($i=0; $i<count($product_id); $i++){
                    $productstock = ProductStock::findOrFail($varient_id[$i]);
                    // PO order item create
                    if($item_id[$i]!=0){
                        $stockadjustitemmodel = StockAdjustItem::findOrFail($item_id[$i]);
                        $item_qty = $stockadjustitemmodel->qty;
                        if($item_qty <= $productstock->qty){
                            $productstock_qty = $productstock->qty-$item_qty;
                            $productstock->qty = $productstock_qty+$quantity[$i];
                        }
                    }else{
                        $stockadjustitemmodel = new StockAdjustItem;
                        $productstock->qty = $productstock->qty+$quantity[$i];
                    }
                    $stockadjustitemmodel->purchase_order_id = $stockadjustmodel->id;
                    $stockadjustitemmodel->product_id = $product_id[$i];
                    $stockadjustitemmodel->variant = $varient_id[$i];
                    $stockadjustitemmodel->price = $price[$i];
                    $stockadjustitemmodel->qty = $quantity[$i];
                    $item_price =  $stockadjustitemmodel->price;
                    $item_qty = $stockadjustitemmodel->qty;
                    $item_total_price = $item_price * $item_qty;
                    $stockadjustitemmodel->total_price = $item_total_price;
                    $grand_total = $grand_total + $item_total_price;
                    $stockadjustitemmodel->save();
                    // product stock update
                    if(!$productstock->save()){
                        DB::rollBack();
                    }
                }
                $stockadjustmodel->grand_total = $grand_total;
                if(!$stockadjustmodel->save()){
                    DB::rollBack();
                }
            }
        }
        DB::commit();

        flash(('Purchase order & product stock has been updated successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

        return redirect()->route('seller.purchase-order.index');
    }

    public function seller_delete_item(Request $request)
    {
        $item_id = $request->item_id;
        $item_details = StockAdjustItem::findOrFail($item_id);
        // $product_stock = ProductStock::findOrFail($item_details->variant);
        $data['status'] = false;
        $data['msg'] = 'Product stock quantity is less than item quantity';
        if($item_details->qty <= $item_details->product_stock->qty){
            $product_stock_model = $item_details->product_stock;
            $product_stock_model->qty = $product_stock_model->qty - $item_details->qty;
            DB::beginTransaction();
            if($product_stock_model->save()){
                $purchase_model = $item_details->purchase_order;
                $purchase_model->grand_total = $purchase_model->grand_total - $item_details->total_price;
                if($purchase_model->save()){
                    if($item_details->delete()){
                        $data['status'] = true;
                        $data['msg'] = 'Item deleted & product stock updated successfully';
                    }else{
                        $data['status'] = false;
                        $data['msg'] = 'Item not deleted!';
                        DB::rollBack();
                    }
                }else{
                    $data['status'] = false;
                    $data['msg'] = 'Unable to save purchase order data!';
                    DB::rollBack();
                }
            }
            DB::commit();
        }
        return response()->json($data);

    }

    public function getproductvarient_seller(Request $request)
    {
        $data = [];
        $productid = $request->productid;
        $product = Product::findOrFail($productid);
        $productstock = ProductStock::where('product_id',$productid);
        if($product->variant_product==1){
            $data['status'] = true;
            $data['varient_status'] = 1;
            $varientdata = '<option value="">Select Varient</option>';
            foreach($productstock->get() as $stock):
                $varientdata .= '<option value="'.$stock->id.'">'.$stock->variant.'</option>';
            endforeach;
            $data['varientdata'] = $varientdata;
        }else{
            $data['status'] = true;
            $data['varient_status'] = 0;
            $data['varientdata'] = '<option value="'.$productstock->first()->id.'">N/A</option>';
        }
        return response()->json($data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_seller(Request $request)
    {
        $supplier_id = $request->supplier_id;
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $price = $request->stock_price;
        $quantity = $request->stock_quantity;
        $stockadjustmodel = new StockAdjust;
        $stockadjustmodel->po_number = config('app.purchase_no_prefix').date('YmdHis') . rand(10, 99);;
        $stockadjustmodel->supplier_id = $supplier_id;
        $stockadjustmodel->user_id =  Auth::user()->id;
        $stockadjustmodel->purchase_date = strtotime(date('Y-m-d',strtotime($request->purchase_date)));
        if($stockadjustmodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($price) && is_array($quantity)){
                for($i=0; $i<count($product_id);  $i++){
                    // PO order item create
                    $stockadjustitemmodel = new StockAdjustItem;
                    $stockadjustitemmodel->purchase_order_id = $stockadjustmodel->id;
                    $stockadjustitemmodel->product_id = $product_id[$i];
                    $stockadjustitemmodel->variant = $varient_id[$i];
                    $stockadjustitemmodel->price = $price[$i];
                    $stockadjustitemmodel->qty = $quantity[$i];
                    $stockadjustitemmodel->save();

                    // product stock update
                    $productstock = ProductStock::findOrFail($varient_id[$i]);
                    $productstock->qty = $productstock->qty+$quantity[$i];
                    $productstock->save();
                }
            }
        }

        flash(('Purchase order & product stock has been updated successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();

        return redirect()->route('seller.purchase-order.index');
    }



    public function show_seller($id)
    {
        $purchase_order = StockAdjust::findOrFail(decrypt($id));
        return view('frontend.user.seller.purchase_order.show', compact('purchase_order'));
    }
}
