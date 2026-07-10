<?php
namespace App\Http\Controllers;

use App\Events\ProductPurchased;
use App\Events\ProductStockAffected;
use App\Helpers\BarcodeHelper;
use App\Models\ACCBank;
use App\Models\AccHead;
use App\Models\AccTransaction;
use App\Models\AccVoucherEntry;
use App\Models\Barcode;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDF;
use Route;

class PurchaseorderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $supplier_id = null;
        $purchaseorder = PurchaseOrder::with('sellername','supplier','purchaseOrderDetails')
        ->withCount([
            'items' => function ($query) {
                $query->whereNotNull('expire_date');
            }
        ])
        ->orderBy('purchase_date', 'desc');

        // dd($purchaseorder->paginate(15));

        if ($request->has('user_id') && $request->user_id != null) {
            $supplier_id = $request->user_id;
            $purchaseorder = $purchaseorder->where('supplier_id', $request->user_id);
        }

        if ($request->has('search')) {
            $sort_search = $request->search;
            //$orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            $purchaseorder = $purchaseorder->where(function ($query) use ($sort_search) {
                $query->orWhere('po_number', 'like', '%' . $sort_search . '%');
            });
        }

        if (!empty($date)) {
            $fromx = strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[0])));
            $tox = strtotime(date('Y-m-d', strtotime(explode(" to ", $date)[1])));

            $orders = $purchaseorder->whereBetween('purchase_date', [$fromx, $tox]);
        }

        $purchaseorders = $purchaseorder->paginate(15);

        return view('backend.product.purchase_order.index', compact('purchaseorders','sort_search', 'date','supplier_id'));
    }

    public function all_purchase_order(Request $request)
    {
        $date = $request->date;
        $sort_search = null;

        $purchaseorder = PurchaseOrder::orderBy('purchase_date', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            //$orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            $purchaseorders = $purchaseorder->where(function ($query) use ($sort_search) {
                $query->orWhere('po_number', 'like', '%' . $sort_search . '%');
            });
        }

        if ($date != null) {
            $orders = $purchaseorder->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $purchaseorders = $purchaseorder->paginate(15);

        return view('frontend.user.seller.products', compact('products', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Cache::remember('suppliers_for_purchase_order', now()->addDay(), function () {
            if(Auth::user()->user_type === 'admin' || in_array('25', json_decode(Auth::user()->staff->role->permissions))) {
                return Supplier::with('template:id,content')->orderBy('created_at', 'desc')->get();
            } else {
                return Supplier::with('template:id,content')->where('user_id',Auth::user()->id)->orderBy('created_at', 'desc')->get();
            }
        });

        $products = Cache::remember('all_published_products', now()->addHours(3), function () {
            return Product::latest()->published()->get();
        });

        return view('backend.product.purchase_order.create', compact('suppliers','products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $supplier_id = $request->supplier_id;
        $template_id = $request->template_id ?? '';
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $price = $request->stock_price;
        $quantity = $request->stock_quantity;
        $expire_date = array_filter($request->stock_expire_date ?? []);

        DB::beginTransaction();

        $errors = [];
        try {
            $purchaseordermodel = new PurchaseOrder;
            $purchaseordermodel->po_number = config('app.purchase_no_prefix').date('YmdHis') . rand(10, 99);
            $purchaseordermodel->user_id =  Auth::user()->id;
            $purchaseordermodel->supplier_id = $supplier_id;
            $purchaseordermodel->total_payment = $request->total_pay ?? 0;
            $purchaseordermodel->total_due = $request->total_due ?? 0;
            $purchaseordermodel->purchase_date = strtotime(date('Y-m-d',strtotime($request->purchase_date ?? now())));
            $purchaseordermodel->attachement = $request->attachement;
            if($purchaseordermodel->save()){
                if(is_array($product_id) && is_array($varient_id) && is_array($price) && is_array($quantity) && is_array($expire_date)){
                    $grand_total = 0;
                    for($i=0; $i<count($product_id);  $i++){
                        try {
                            // PO order item create
                            $purchaseorderitemmodel = new PurchaseOrderItem;
                            $purchaseorderitemmodel->purchase_order_id = $purchaseordermodel->id;
                            $purchaseorderitemmodel->product_id = $product_id[$i];
                            $purchaseorderitemmodel->variant = $varient_id[$i];
                            $purchaseorderitemmodel->price = $price[$i];
                            $purchaseorderitemmodel->qty = $quantity[$i];
                            $purchaseorderitemmodel->left_qty = $quantity[$i];
                            $purchaseorderitemmodel->expire_date = $expire_date[$i] ?? null;


                            $item_price =  $purchaseorderitemmodel->price;
                            $item_qty = $purchaseorderitemmodel->qty;
                            $item_total_price = $item_price * $item_qty;
                            $purchaseorderitemmodel->total_price = $item_total_price;
                            $grand_total = $grand_total + $item_total_price;
                            $purchaseorderitemmodel->save();

                            if(get_setting('enable_product_expire_date') == 1 && isset($expire_date[$i])){
                                $barcode = BarcodeHelper::generate(
                                    $purchaseorderitemmodel->product_id,
                                    $purchaseorderitemmodel->id,
                                    $purchaseorderitemmodel->variant,
                                    $purchaseorderitemmodel->expire_date
                                );
                                $uniqueCode = BarcodeHelper::unique();
                                $purchaseorderitemmodel->barcode = $uniqueCode;
                                Log::info('Generated Barcode', [
                                    'purchase_order_item_id' => $purchaseorderitemmodel->id,
                                    'barcode' => $barcode,
                                    'unique_code' => $uniqueCode
                                ]);
                                if($purchaseorderitemmodel->save()){
                                    $newBarcode = new Barcode;
                                    $newBarcode->code = $uniqueCode;
                                    $newBarcode->value = $barcode;
                                    $newBarcode->save();
                                    // dd($newBarcode);
                                }else{
                                    DB::rollBack();
                                    flash(('Failed to generate barcode'))->error();
                                    return redirect()->back();
                                }
                            }

                            // product stock update
                            $productstock = ProductStock::findOrFail($varient_id[$i]);
                            $productstock->qty = $productstock->qty+$quantity[$i];
                            $productstock->save();

                            // Store Stock Transaction
                            $transaction = [
                                'product_id'    => (int)$product_id[$i],
                                'variant'       => empty($productstock->variant) ? null : $productstock->variant,
                                'sku'           => $productstock->sku ?? null,
                                'qty'           => $quantity[$i],
                                'isAddition'    => 1,
                                'isSubtraction' => 0,
                                'purpose'       => 'purchase',
                                'purpose_id'    => $purchaseordermodel->id,
                                'note'          => 'New Purchase, Ref ID = '.$purchaseorderitemmodel->id ?? 'Unknown'.''
                            ];
                            // Trigger The Event
                            event(new ProductPurchased($purchaseorderitemmodel));
                            event(new ProductStockAffected($transaction));
                        } catch (\Throwable $th) {
                            // throw $th;
                            $errors[] = $th->getMessage();
                            continue;
                        }
                    }
                    $purchaseordermodel->grand_total = $grand_total;
                    $purchaseordermodel->save();
                }
            }

            if (!empty($errors)) {
                Log::error('Errors occurred while processing purchase order items', ['errors' => $errors]);
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some items could not be processed. Please check the logs for details.',
                        'errors' => $errors
                    ]);
                }
                flash('Some items could not be processed. Please check the logs for details.')->error();
                return redirect()->back()->withInput();
            }

            // Save Payments
            $pmethods = $request->input('method', '');
            $pbanktypes = $request->input('bank_type', '');
            $pbanks = $request->input('bank', '');
            $pamounts = $request->input('amount', '');
            if(is_array($pmethods) && is_array($pbanktypes) && is_array($pbanks) && is_array($pamounts)){

                $vnent_counting = AccVoucherEntry::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                $vinv = "VNO-" . date('Ymd') . ($vnent_counting + 1);

                $supplier = DB::table('supplier')->where('id', $supplier_id)->first();

                $headArray = [];
                for($i=0; $i<count($pmethods);  $i++){
                    // save the payment
                    // code
                    $inv_counting = Payment::whereDate('date', date('Y-m-d'))->distinct()->count('invoice_no');
                    $pinv = "PAY-" . date('Ymd') . ($inv_counting + 1);

                    $bank_info = ACCBank::find($pbanks[$i]);
                    $pdetails = [
                        'payment_method' => $pmethods[$i],
                        'bank_type' => $pbanktypes[$i],
                        'bank_info' => $bank_info ?? null,
                        'payment_amount' => $pamounts[$i]
                    ];

                    $payment = new Payment;
                    $payment->invoice_no = $pinv;
                    $payment->date = date('Y-m-d');
                    $payment->payable_id = $supplier_id;
                    $payment->payable_type = Supplier::class;
                    $payment->reference_id = $purchaseordermodel->id;
                    $payment->reference_type = PurchaseOrder::class;
                    $payment->seller_id = auth()->user()?->id ?? null;
                    $payment->amount = $pamounts[$i];
                    $payment->payment_details = json_encode($pdetails);
                    $payment->payment_method = $pbanktypes[$i];
                    $payment->txn_code = null;
                    $payment->user_id = auth()->user()?->id ?? null;
                    $payment->remarks = "Purchase from Invoice " . $purchaseordermodel->po_number;

                    // code
                    if ($payment->save()) {
                        if($pmethods[$i] == 'cash'){
                            $head = "Cash In Hand";
                        }else if($pmethods[$i] == 'bank'){
                            if(!empty($bank_info)){
                                $head = $bank_info->bank_name.' '.$bank_info->acc_no;
                            }else{
                                $head = $pmethods[$i].' Payment';
                            }
                        }else{
                            $head = $pmethods[$i].' Payment';
                        }

                        $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                        $vno = date('Ymd') . '-' . ($vno_counting + 1);

                        $description = 'Credited to '.$supplier->name." ".$supplier->contact_number.' Voucher No. '.$vinv;
                        $credit = $pamounts[$i];
                        $debit = 0;

                        AccTransaction::create([
                            'date' => $request->purchase_date,
                            'user_id' => auth()->user()->id,
                            'vno' => $vno,
                            'head' => $head,
                            'head_type' => !empty($bank_info) ? ACCBank::class : AccHead::class,
                            'head_id' => !empty($bank_info) ? $bank_info->id : AccHead::where('head', 'Cash In Hand')->first()->id ?? null,
                            'debit' => $debit,
                            'credit' => $credit,
                            'description' => $description
                        ]);

                        $headArray[] = $head;

                        $headinfo = AccHead::where("head", $head)->first();

                        $entry = new AccVoucherEntry;
                        $entry->date = date('Y-m-d');
                        $entry->vno = $vinv;
                        $entry->voucher_type = 'payment';
                        $entry->entry_type = 'credit';
                        $entry->credit = $pamounts[$i];
                        $entry->particular_id = $headinfo->id ?? null;
                        $entry->particular_type = $headinfo->reference_type ?? AccHead::class;
                        $entry->naration = null;
                        $entry->note = "Purchase Payment";
                        $entry->attachement = $request->attachement;
                        $entry->user_id = auth()->user()->id;
                        $entry->save();
                    }
                }

                $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                $vno = date('Ymd') . '-' . ($vno_counting + 1);

                $head = "Purchase";
                $description = "Debited from ".implode(',', $headArray)." Payment for Purchase Invoice " . $purchaseordermodel->po_number;
                $credit = 0;
                $debit = $purchaseordermodel->grand_total;

                // save related transactions
                AccTransaction::create([
                    'date' => $request->purchase_date,
                    'user_id' => auth()->user()->id,
                    'vno' => $vno,
                    'head' => $head,
                    'head_type' => PurchaseOrder::class,
                    'head_id' => $purchaseordermodel->id,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $description
                ]);

                $headinfo = AccHead::where("head", $head)->first();

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

                $due = $purchaseordermodel->grand_total - $request->total_pay;
                if ($due > 0) {

                    $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                    $vno = date('Ymd') . '-' . ($vno_counting + 1);

                    $head = $supplier->name." ".$supplier->contact_number;
                    $description = "Credited to Purchase for Purchase Due From Invoice  " . $purchaseordermodel->po_number;
                    $credit = $due;
                    $debit = 0;

                    AccTransaction::create([
                        'date' => $request->purchase_date,
                        'user_id' => auth()->user()->id,
                        'vno' => $vno,
                        'head' => $head,
                        'head_type' => Supplier::class,
                        'head_id' => $supplier->id,
                        'debit' => $debit,
                        'credit' => $credit,
                        'description' => $description
                    ]);

                    $headinfo = AccHead::where("head", $head)->first();

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
                }
            }

            DB::commit();

            dispatch(function () {
                Artisan::call('optimize:clear');
            })->afterResponse();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => ('Purchase order & product stock has been updated successfully'),
                    'redirect_base' => route('purchaseorder.index'),
                    'redirect_print' => (get_setting('enable_product_expire_date') == 1 && count($expire_date) > 0) ? route('purchaseorder.print_barcode', encrypt($purchaseordermodel->id)) : null
                ]);
            }

            flash(('Purchase order & product stock has been updated successfully'))->success();
            return redirect()->route('purchaseorder.index', compact('errors'));
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error creating purchase order', ['error' => $th->getMessage(), 'stack' => $th->getTraceAsString()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => ('Something Went Wrong Please Try Again'),
                ]);
            }
            flash(('Something Went Wrong Please Try Again'))->error();
            return redirect()->back()->withErrors($th->getMessage())->withInput();
        }
    }

    public function print_barcode($id)
    {
        if(get_setting('enable_product_expire_date') != 1) {
            abort(400);
        }
        // $id = encrypt($id);
        $purchaseOrder = PurchaseOrder::with('items.product', 'supplier.template:id,content')->withCount([
            'items' => function ($query) {
                $query->whereNotNull('expire_date');
            }
        ])->findOrFail(decrypt($id));

        if($purchaseOrder->items_count == 0) {
            flash(('No items with expire date found for this purchase order'))->error();
            return redirect()->route('purchaseorder.index');
        }
        if($purchaseOrder->supplier->template) {
            $template = $purchaseOrder->supplier->template->content;
        } else {
            $template = view('backend.templates.defaults.product_sticker')->render();
        }

        $renderTemplates = [];
        foreach ($purchaseOrder->items as $item) {
            for ($i = 0; $i < $item->qty; $i++) {
                $productPrice = $item->product->unit_price;
                $additionalCharge = get_setting('additional_charge', 0);
                $productPriceWithCharge = $productPrice + ($productPrice * $additionalCharge / 100); // Apply additional charge
                $renderTemplates[] = str_replace(
                    ['[[supplier_name]]', '[[supplier_address]]', '[[product_name]]', '[[product_price]]', '[[po_number]]', '[[barcode]]', '[[exp_date]]'],
                    [
                        $purchaseOrder->supplier->name,
                        $purchaseOrder->supplier->address,
                        $item->product->name,
                        number_format($productPriceWithCharge, 2),
                        $purchaseOrder->po_number,
                        strlen(trim($item->barcode)) > 0 ? BarcodeHelper::render((string) $item->barcode ?? '', true) : '',
                        strlen(trim($item->barcode)) > 0 ? date('Y-m-d', strtotime($item->expire_date)) : ''
                    ],
                    $template
                );
            }
        }

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [38, 25],
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
        ]);

        // ✅ Chunk the renderTemplates (30 per chunk is safe)
        $chunks = array_chunk($renderTemplates, 30);

        foreach ($chunks as $chunk) {
            $html = view('backend.invoices.product_sticker', [
                'renderTemplates' => $chunk
            ])->render();

            $pdf->WriteHTML($html);
        }

        $pdfContent = $pdf->Output('', 'S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="product-sticker.pdf"');
        // return $pdf->Output('product-sticker.pdf', 'I');
    }

    public function getproductvarient(Request $request)
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
            if($productstock->count()>0){
                $data['status'] = true;
                $data['varient_status'] = 0;
                $data['varientdata'] = '<option value="'.$productstock->first()->id.'">N/A</option>';
            }else{
                $newPStock = new ProductStock;
                $newPStock->product_id = $product->id;
                $newPStock->variant = '';
                $newPStock->price = $product->unit_price;
                $newPStock->save();

                $data['status'] = true;
                $data['varient_status'] = 0;
                $data['varientdata'] = '<option value="'.$newPStock->id.'">N/A</option>';
            }
        }
        return response()->json($data);
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
            $id = decrypt($id);
        } catch (\RuntimeException $e) {
            $id = $id;
        }
        $purchase_order = PurchaseOrder::findOrFail($id);
        return view('backend.product.purchase_order.show', compact('purchase_order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase_order = PurchaseOrder::findOrFail($id);
        (Auth::user()->user_type === 'admin') ? $suppliers = Supplier::orderBy('created_at', 'desc')->get() : $suppliers = Supplier::where('user_id',Auth::user()->id)->orderBy('created_at', 'desc')->get();
        (Auth::user()->user_type === 'admin') ? $products = Product::where('published', 1)->orderBy('created_at', 'desc')->get() : $products = Product::where('user_id', Auth::user()->id)->where('published', 1)->orderBy('created_at', 'desc')->get();

        return view('backend.product.purchase_order.edit', compact('suppliers','products', 'purchase_order'));
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
        $supplier_id = $request->supplier_id;
        $item_id = $request->item_id;
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $price = $request->stock_price;
        $quantity = $request->stock_quantity;
        $purchaseordermodel = PurchaseOrder::findOrFail($id);
        $purchaseordermodel->supplier_id = $supplier_id;
        $purchaseordermodel->purchase_date = strtotime(date('Y-m-d',strtotime($request->purchase_date)));
        DB::beginTransaction();
        if($purchaseordermodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($price) && is_array($quantity)){
                $grand_total = 0;
                for($i=0; $i<count($product_id); $i++){
                    $productstock = ProductStock::findOrFail($varient_id[$i]);
                    // PO order item create
                    if($item_id[$i]!=0){
                        $purchaseorderitemmodel = PurchaseOrderItem::findOrFail($item_id[$i]);
                        $item_qty = $purchaseorderitemmodel->qty;
                        if($item_qty <= $productstock->qty){
                            $productstock_qty = $productstock->qty-$item_qty;
                            $productstock->qty = $productstock_qty+$quantity[$i];
                        }

                        // Store Stock Transaction
                        if($item_qty > $quantity[$i]){
                            $transaction = [
                                'product_id'    => (int)$product_id[$i],
                                'variant'       => empty($productstock->variant) ? null : $productstock->variant,
                                'sku'           => $productstock->sku ?? null,
                                'qty'           => $item_qty - $quantity[$i],
                                'isAddition'    => 0,
                                'isSubtraction' => 1,
                                'purpose'       => 'purchase',
                                'purpose_id'    => $purchaseordermodel->id,
                                'note'          => 'Update Purchase, Ref. ID = '.$purchaseorderitemmodel->id ?? 'Unknown'.''
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }else{
                            $transaction = [
                                'product_id'    => (int)$product_id[$i],
                                'variant'       => empty($productstock->variant) ? null : $productstock->variant,
                                'sku'           => $productstock->sku ?? null,
                                'qty'           => $quantity[$i] - $item_qty,
                                'isAddition'    => 1,
                                'isSubtraction' => 0,
                                'purpose'       => 'purchase',
                                'purpose_id'    => $purchaseordermodel->id,
                                'note'          => 'Update Purchase, Ref. ID = '.$purchaseorderitemmodel->id ?? 'Unknown'.''
                            ];
                            // Trigger The Event
                            event(new ProductStockAffected($transaction));
                        }
                    }else{
                        $purchaseorderitemmodel = new PurchaseOrderItem;
                        $productstock->qty = $productstock->qty+$quantity[$i];
                    }
                    $purchaseorderitemmodel->purchase_order_id = $purchaseordermodel->id;
                    $purchaseorderitemmodel->product_id = $product_id[$i];
                    $purchaseorderitemmodel->variant = $varient_id[$i];
                    $purchaseorderitemmodel->price = $price[$i];
                    $purchaseorderitemmodel->qty = $quantity[$i];
                    $item_price =  $purchaseorderitemmodel->price;
                    $item_qty = $purchaseorderitemmodel->qty;
                    $item_total_price = $item_price * $item_qty;
                    $purchaseorderitemmodel->total_price = $item_total_price;
                    $grand_total = $grand_total + $item_total_price;
                    $purchaseorderitemmodel->save();

                    event(new ProductPurchased($purchaseorderitemmodel));
                    // product stock update
                    if(!$productstock->save()){
                        DB::rollBack();
                    }
                }
                $purchaseordermodel->grand_total = $grand_total;
                if(!$purchaseordermodel->save()){
                    DB::rollBack();
                }
            }
        }
        DB::commit();

        flash(('Purchase order & product stock has been updated successfully'))->success();

        dispatch(function () {
            Artisan::call('optimize:clear');
        })->afterResponse();
        return redirect()->route('purchaseorder.index');
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
        $item_details = PurchaseOrderItem::findOrFail($item_id);
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

                $transaction = [
                    'product_id'    => (int)$item_details->product_id,
                    'variant'       => empty($product_stock_model->variant) ? null : $product_stock_model->variant,
                    'sku'           => $product_stock_model->sku ?? null,
                    'qty'           => $item_details->qty,
                    'isAddition'    => 1,
                    'isSubtraction' => 0,
                    'purpose'       => 'purchase_delete_item',
                    'purpose_id'    => $item_details->id,
                    'note'          => 'Delete Purchase Item, Ref. ID = '.$item_details->id ?? 'Unknown'.''
                ];
                // Trigger The Event
                event(new ProductPurchased($item_details));
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

        $purchaseorder = PurchaseOrder::orderBy('purchase_date', 'desc');
        if ($request->has('search')) {
            $sort_search = $request->search;
            //$orders = $orders->where('code', 'like', '%' . $sort_search . '%');
            $purchaseorders = $purchaseorder->where(function ($query) use ($sort_search) {
                $query->orWhere('po_number', 'like', '%' . $sort_search . '%');
            });
        }

        if ($date != null) {
            $orders = $purchaseorder->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        $purchaseorders = $purchaseorder->where('user_id', Auth::user()->id)->paginate(15);

        return view('frontend.user.seller.purchase_order.index', compact('purchaseorders','sort_search', 'date'));
    }

    public function create_seller()
    {
        $suppliers = Supplier::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        $products = Product::where('user_id', Auth::user()->id)->where('published', 1)->orderBy('created_at', 'desc')->get();
        return view('frontend.user.seller.purchase_order.create', compact('suppliers', 'products'));
    }
    public function edit_seller($id)
    {
        $purchase_order = PurchaseOrder::findOrFail($id);
        $suppliers = Supplier::where('user_id',Auth::user()->id)->orderBy('created_at', 'desc')->get();
        $products = Product::where('user_id', Auth::user()->id)->where('published', 1)->orderBy('created_at', 'desc')->get();

        return view('frontend.user.seller.purchase_order.edit', compact('suppliers','products', 'purchase_order'));
    }

    public function seller_update(Request $request, $id)
    {
        $supplier_id = $request->supplier_id;
        $item_id = $request->item_id;
        $product_id = $request->stock_product_id;
        $varient_id = $request->stock_varient_id;
        $price = $request->stock_price;
        $quantity = $request->stock_quantity;
        $purchaseordermodel = PurchaseOrder::findOrFail($id);
        $purchaseordermodel->supplier_id = $supplier_id;
        $purchaseordermodel->purchase_date = strtotime(date('Y-m-d',strtotime($request->purchase_date)));
        DB::beginTransaction();
        if($purchaseordermodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($price) && is_array($quantity)){
                $grand_total = 0;
                for($i=0; $i<count($product_id); $i++){
                    $productstock = ProductStock::findOrFail($varient_id[$i]);
                    // PO order item create
                    if($item_id[$i]!=0){
                        $purchaseorderitemmodel = PurchaseOrderItem::findOrFail($item_id[$i]);
                        $item_qty = $purchaseorderitemmodel->qty;
                        if($item_qty <= $productstock->qty){
                            $productstock_qty = $productstock->qty-$item_qty;
                            $productstock->qty = $productstock_qty+$quantity[$i];
                        }
                    }else{
                        $purchaseorderitemmodel = new PurchaseOrderItem;
                        $productstock->qty = $productstock->qty+$quantity[$i];
                    }
                    $purchaseorderitemmodel->purchase_order_id = $purchaseordermodel->id;
                    $purchaseorderitemmodel->product_id = $product_id[$i];
                    $purchaseorderitemmodel->variant = $varient_id[$i];
                    $purchaseorderitemmodel->price = $price[$i];
                    $purchaseorderitemmodel->qty = $quantity[$i];
                    $item_price =  $purchaseorderitemmodel->price;
                    $item_qty = $purchaseorderitemmodel->qty;
                    $item_total_price = $item_price * $item_qty;
                    $purchaseorderitemmodel->total_price = $item_total_price;
                    $grand_total = $grand_total + $item_total_price;
                    $purchaseorderitemmodel->save();
                    // product stock update
                    if(!$productstock->save()){
                        DB::rollBack();
                    }
                }
                $purchaseordermodel->grand_total = $grand_total;
                if(!$purchaseordermodel->save()){
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
        $item_details = PurchaseOrderItem::findOrFail($item_id);
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
        $purchaseordermodel = new PurchaseOrder;
        $purchaseordermodel->po_number = config('app.purchase_no_prefix').date('YmdHis') . rand(10, 99);;
        $purchaseordermodel->supplier_id = $supplier_id;
        $purchaseordermodel->user_id =  Auth::user()->id;
        $purchaseordermodel->purchase_date = strtotime(date('Y-m-d',strtotime($request->purchase_date ?? now())));
        if($purchaseordermodel->save()){
            if(is_array($product_id) && is_array($varient_id) && is_array($price) && is_array($quantity)){
                for($i=0; $i<count($product_id);  $i++){
                    // PO order item create
                    $purchaseorderitemmodel = new PurchaseOrderItem;
                    $purchaseorderitemmodel->purchase_order_id = $purchaseordermodel->id;
                    $purchaseorderitemmodel->product_id = $product_id[$i];
                    $purchaseorderitemmodel->variant = $varient_id[$i];
                    $purchaseorderitemmodel->price = $price[$i];
                    $purchaseorderitemmodel->qty = $quantity[$i];
                    $purchaseorderitemmodel->save();

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
        $purchase_order = PurchaseOrder::findOrFail(decrypt($id));
        return view('frontend.user.seller.purchase_order.show', compact('purchase_order'));
    }

    public function purchases_by_supplier(Request $request)
    {
        if (!empty($request->input('user_id'))) {
            $purchaseorders = PurchaseOrder::with('sellername','supplier','purchaseOrderDetails')
                ->when(filled($request->input('product_id')), function ($query) use ($request) {
                    $query->whereHas('purchaseOrderDetails', function ($q) use ($request) {
                        $q->where('product_id', $request->integer('product_id'));
                    });
                })
                ->when(filled($request->input('date')), function ($query) use ($request) {
                    $dateRange = explode(' to ', $request->input('date'));
                    if (count($dateRange) === 2) {
                        $fromDate = Carbon::parse($dateRange[0])->startOfDay()->timestamp;
                        $toDate = Carbon::parse($dateRange[1])->endOfDay()->timestamp;
                        $query->whereBetween('purchase_date', [$fromDate, $toDate]);
                    }
                })
                ->when(filled($request->input('search')), function ($query) use ($request) {
                    $sort_search = $request->input('search');
                    $query->where(function ($q) use ($sort_search) {
                        $q->orWhere('po_number', 'like', '%' . $sort_search . '%');
                    });
                })
                ->where('supplier_id', $request->user_id)
                ->orderBy('purchase_date', 'desc')
                ->get();
        }else{
            $purchaseorders = [];
        }

        return view('backend.product.purchase_order.purchase_report', compact('purchaseorders'));
    }

    public function get_due_invoices_by_supplier(Request $request){

        $invoices = PurchaseOrder::where('supplier_id', $request->id)->whereColumn('total_payment', '<', 'grand_total')->get();

        return response()->json([
            'data' => $invoices,
            'success' => true
        ]);

    }
}
