<?php

namespace App\Http\Controllers;

use App\Events\InsertAccHead;
use App\Events\InsertAccTransaction;
use App\Events\UpdateAccHead;
use App\Models\AccHead;
use App\Models\AccTransaction;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\ReturnSupplier;
use App\Models\Supplier;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request){
        $search = $request->search ?? '';
        $suppliers = Cache::remember('suppliers_all_'.$search, now()->addHour(1), function() use ($search) {
            return Supplier::with('userinfo','transactions')
                ->withSum('purchaseorders as total_purchase', 'grand_total')
                ->withSum('purchaseorders as total_paid', 'total_payment')
                ->when(filled($search), function ($query) use ($search) {
                    $query->where(function($query) use ($search){
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('contact_number', 'like', '%'.$search.'%');
                    });
                })->get();
        });

        return view('backend.supplier.index', compact('suppliers', 'search'));
    }

    public function create(Request $request){
        if($request->isMethod('get')){
            return view('backend.supplier.create');
        }
        else{
            $supplier = new Supplier();
            $data = $request->only($supplier->getFillable());
            $supplier->fill($data)->save();
            $headData = [
                'parent_head' => 'liability',
                'sub_head' => 'Suppliers Payable',
                'head' => $data['name'].' '.$data['contact_number'] ?? '',
                'reference_id' => $supplier->id ?? 0,
                'reference_type' => Supplier::class,
                'user_id' => $data['user_id'] ?? 0
            ];
            InsertAccHead::dispatch($headData);

            if($request->has('opening_balance') && !empty($request->opening_balance)){
                $vno_counting = AccTransaction::whereDate('date', date('Y-m-d'))->distinct()->count('vno');
                $vno = date('Ymd') . '-' . ($vno_counting + 1);

                $accTransData = [
                    'date' => date('Y-m-d'),
                    'user_id' => $request->user()->id ?? 0,
                    'vno' => $vno,
                    'head' => $data['name'].' '.$data['contact_number'] ?? '',
                    'head_type' => Supplier::class,
                    'head_id' => $supplier->id ?? 0,
                    'debit' => 0,
                    'credit' => $request->opening_balance,
                    'note' => 'Opening Balance',
                    'description' => 'New Supplier Opening Balance'
                ];
                InsertAccTransaction::dispatch($accTransData);
            }

            return redirect()->route('supplier.index');
        }
    }

    public function edit($id){
        $data = Supplier::where('id', $id)->first();
        return view('backend.supplier.edit',  ['data'=>$data]);
    }
    public function update(Request $request, $id){
        DB::beginTransaction();
        $supplier = Supplier::findOrFail($id);
        $oldHead = AccHead::where('head', $supplier->name.' '.$supplier->contact_number)->first();

        $data = $request->only($supplier->getFillable());
        $supplier->fill($data)->save();

        if(!empty($oldHead)){
            $headData = [
                'parent_head' => 'liability',
                'sub_head' => 'Suppliers Payable',
                'head' => $data['name'].' '.$data['contact_number'] ?? '',
                'reference_id' => $supplier->id ?? 0,
                'reference_type' => Supplier::class,
                'user_id' => $data['user_id'] ?? 0
            ];
            UpdateAccHead::dispatch($oldHead->id, $headData);
            // dd($headData);
        }else{
            $headData = [
                'parent_head' => 'liability',
                'sub_head' => 'Suppliers Payable',
                'head' => $data['name'].' '.$data['contact_number'] ?? '',
                'reference_id' => $supplier->id ?? 0,
                'reference_type' => Supplier::class,
                'user_id' => $data['user_id'] ?? 0
            ];
            InsertAccHead::dispatch($headData);
        }

        DB::commit();
        return redirect()->route('supplier.index');
    }

    public function delete($id){
        Supplier::destroy($id);
        return redirect()->route('supplier.index');
    }





    public function index_seller(Request $request){
        $search='';
        $supplier = Supplier::where('user_id', Auth::user()->id);
        if($request->has('search')){
            $search = $request->get('search');
            $supplier = $supplier->where(function($query){
                $query->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('contact_number', 'like', '%'.request('search').'%');
            });
        }
        $supplier = $supplier->get();
        return view('frontend.user.seller.supplier.index', compact('supplier', 'search'));
    }

    public function create_seller(Request $request){
        if($request->isMethod('get')){
            return view('frontend.user.seller.supplier.create');
        }
        else{
            $supplier = new Supplier();
            $data = $request->only($supplier->getFillable());
            $supplier->fill($data)->save();
            return redirect()->route('seller.supplier');
        }
    }

    public function edit_seller($id){
        $data = Supplier::where('id', decrypt($id))->first();
        return view('frontend.user.seller.supplier.edit', compact('data'));
    }

    public function update_seller(Request $request, int $id){
        $supplier = Supplier::findOrFail(decrypt($id));
        $data = $request->only($supplier->getFillable());
        $supplier->fill($data)->save();
        return redirect()->route('seller.supplier');
    }
    public function delete_seller(int $id){
        Supplier::destroy(decrypt($id));
        return redirect()->route('seller.supplier');
    }

    public function details(Request $request, int $id){
        $supplier = Supplier::with('payments')
            ->withSum('returnedPurchases as returned_amount', 'total_amount')
            ->withSum('purchaseorders as total_purchase', 'grand_total')
            ->withSum('purchaseorders as total_paid', 'total_payment')
            ->find($id);

        // $purchaseOrders = PurchaseOrder::with('sellername','purchaseOrderDetails')
        //     ->where('supplier_id', $id)
        //     ->orderBy('purchase_date', 'desc')
        //     ->paginate(15);
        // $returnedPurchases = ReturnSupplier::query()
        //     ->where('supplier_id', $id)
        //     ->orderBy('date', 'desc')
        //     ->paginate(15);

        $advancePayments = $supplier->payments
            ->filter(fn($item) => Str::contains($item->remarks, 'General Payment', true))
            ->sum('amount');
        $total_due = $supplier->total_purchase - $supplier->total_paid - $advancePayments;

        if ($request->input('history', 'purchase') === 'returned') {
            $collection = ReturnSupplier::with('user:id,name')
                ->withCount('items as num_products')
                ->withSum('items as total_qty', 'qty')
                ->where('supplier_id', $id)
                ->orderBy('date', 'desc')
                ->paginate(15);
        } else {
            $collection = PurchaseOrder::with('sellername:id,name')
                ->withCount('purchaseOrderDetails as num_products')
                ->withSum('purchaseOrderDetails as total_qty', 'qty')
                ->where('supplier_id', $id)
                ->orderBy('purchase_date', 'desc')
                ->paginate(15);
        }

        $records = [];
        foreach ($collection as $item) {
            $records[] = [
                'id' => $item->id,
                'date' => Carbon::parse($item->purchase_date ?? $item->date)->timestamp,
                'number' => $item->po_number ?? $item->rs_number,
                'seller_name' => $item->sellername->name ?? $item->user->name ?? 'N/A',
                'num_products' => $item->num_products ?? 0,
                'total_qty' => (int) $item->total_qty ?? 0,
                'amount' => $item->grand_total ?? $item->total_amount ?? 0,
                'link' => $item->po_number ? route('purchaseorder.show', encrypt($item->id)) : route('stock-adjust.return_supplier.show', $item->id)
            ];

            if ($request->input('history', 'purchase') === 'purchase') {
                $records[count($records) - 1]['payment_status'] = isset($item->total_payment) ? ($item->total_payment <= 0 ? 'Unpaid' : ($item->total_payment < $item->grand_total ? 'Partial' : 'Paid')) : null;
            }
        }

        // dd($records);
        return view('backend.supplier.show', compact('supplier', 'records', 'collection', 'total_due', 'advancePayments'));
    }

}
