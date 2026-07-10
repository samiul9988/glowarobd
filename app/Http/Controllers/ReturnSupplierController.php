<?php
namespace App\Http\Controllers;

use App\Events\ProductStockAdjusted;
use App\Events\ProductStockAffected;
use App\Models\AccHead;
use App\Models\AccTransaction;
use App\Models\AccVoucherEntry;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\ReturnSupplier;
use App\Models\StockAdjust;
use App\Models\StockAdjustItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReturnSupplierController extends Controller
{
    public function index(Request $request)
    {
        $returnSuppliers = ReturnSupplier::with(['supplier:id,name', 'user:id,name'])
            ->withCount('items')
            ->when($request->search, function ($query, $search) {
                $query->where('rs_number', 'like', "%{$search}%");
            })
            ->when($request->supplier_id, function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($request->date, function ($query, $date) {
                $dateRange = explode(' to ', $date);
                if (count($dateRange) === 2) {
                    $startDate = Carbon::parse($dateRange[0])->startOfDay();
                    $endDate = Carbon::parse($dateRange[1])->endOfDay();
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            })
            ->latest()
            ->paginate(15);
        $suppliers = Cache::remember('suppliers_for_stock_adjust', now()->addHours(3), function () {
            return Supplier::query()->latest()->select('id', 'name', 'contact_number as phone')->get();
        });
        return view('backend.product.return_supplier.index', compact('returnSuppliers', 'suppliers'));
    }

    public function show(int $id)
    {
        $returnSupplier = ReturnSupplier::with(['supplier:id,name,contact_number,address', 'user:id,name', 'stockAdjust', 'items.product:id,name', 'items.product_stock:id,sku,variant'])->findOrFail($id);

        // dd($returnSupplier->toArray());
        return view('backend.product.return_supplier.show', compact('returnSupplier'));
    }

    public function create()
    {
        $products = Cache::remember('all_published_products', now()->addHours(3), function () {
            return Product::with('lastPurchaseOrderItem:product_id,price')->latest()->published()->get();
        });
        $suppliers = Cache::remember('suppliers_for_stock_adjust', now()->addHours(3), function () {
            return Supplier::query()->latest()->select('id', 'name', 'contact_number as phone')->get();
        });
        return view('backend.product.return_supplier.create', compact('products', 'suppliers'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'note' => 'required|string',
                'date' => 'required|date',
                'supplier' => 'required|exists:supplier,id',
                'products' => 'required|array',
                'products.*' => 'required|exists:products,id',
                'variants' => 'required|array',
                'quantities' => 'required|array',
                'quantities.*' => 'required|integer|min:1',
                'purchase_prices' => 'required|array',
                'purchase_prices.*' => 'required|numeric|min:0'
            ], [
                'products.required' => 'Please select at least one product.',
                'quantities.*.min' => 'The quantity must be at least 1.',
                'purchase_prices.*.min' => 'The purchase price must be at least 0.',
            ]);

            // Group payloads by supplier before entering the transaction
            $supplierId = $validatedData['supplier'];
            $date = $validatedData['date'];
            $note = $validatedData['note'];
            $parsedDate = Carbon::parse($date);

            $payloads = [];
            foreach ($validatedData['products'] as $index => $productId) {
                $payloads[] = [
                    'product_id' => $productId,
                    'variant_id' => $validatedData['variants'][$index],
                    'quantity' => (int) $validatedData['quantities'][$index],
                    'purchase_price' => (float) $validatedData['purchase_prices'][$index],
                ];
            }

            // Validate all variant IDs exist before touching the DB
            $variantIds = collect($validatedData['variants'])->filter()->unique()->values();
            $productStocks = ProductStock::whereIn('id', $variantIds)->get()->keyBy('id');

            $missingVariants = $variantIds->diff($productStocks->keys());
            if ($missingVariants->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'variants' => 'One or more selected product variants are invalid.',
                ]);
            }

            // Collect events to dispatch after a successful commit
            $pendingEvents = [];

            DB::transaction(function () use ($payloads, $productStocks, $parsedDate, $note, $supplierId, &$pendingEvents) {
                $userId = auth()->id();
                $timestamp = now()->format('YmdHis');

                $suffix = $timestamp . random_int(10, 99);

                $stockAdjust = StockAdjust::create([
                    'user_id' => $userId,
                    'sa_number' => config('app.stock_adjust_no_prefix') . $suffix,
                    'sa_type' => 'others',
                    'sa_date' => $parsedDate->timestamp,
                    'note' => trim($note) . PHP_EOL . '** Returned to Supplier **',
                ]);

                $totalAmount = collect($payloads)->sum(
                    fn($item) => $item['quantity'] * $item['purchase_price']
                );

                $returnSupplier = ReturnSupplier::create([
                    'stock_adjust_id' => $stockAdjust->id,
                    'user_id' => $userId,
                    'supplier_id' => $supplierId,
                    'rs_number' => 'RS' . $suffix,
                    'total_amount' => $totalAmount,
                    'date' => $parsedDate->toDateString(),
                    'note' => trim($note),
                ]);

                $stockDeductions = []; // variant_id => quantity to deduct

                foreach ($payloads as $payload) {
                    $productStock = $productStocks->get($payload['variant_id']);

                    $stockAdjustItem = StockAdjustItem::create([
                        'stock_adjust_id' => $stockAdjust->id,
                        'product_id' => $payload['product_id'],
                        'variant' => $payload['variant_id'],
                        'qty' => $payload['quantity'],
                        'purchase_price' => $payload['purchase_price'],
                    ]);

                    $stockDeductions[$payload['variant_id']] = ($stockDeductions[$payload['variant_id']] ?? 0) + $payload['quantity'];

                    $pendingEvents[] = [
                        'stockAdjusted' => $stockAdjustItem,
                        'stockAffected' => [
                            'product_id' => (int) $payload['product_id'],
                            'variant' => $productStock->variant ?: null,
                            'sku' => $productStock->sku ?: null,
                            'qty' => $payload['quantity'],
                            'isAddition' => 0,
                            'isSubtraction' => 1,
                            'purpose' => 'stock_adjust',
                            'purpose_id' => $stockAdjust->id,
                            'note' => "Returned to Supplier. StockAdjust ID: {$stockAdjust->id}, ReturnSupplier ID: {$returnSupplier->id}",
                        ],
                    ];
                }

                // Batch-decrement stock with a single query per supplier group
                foreach ($stockDeductions as $variantId => $deduction) {
                    ProductStock::where('id', $variantId)->decrement('qty', $deduction);
                }
                $this->adjustSuppliersPayable($parsedDate->toDateString(), $supplierId, $totalAmount, $returnSupplier->rs_number);
            });
        } catch (ValidationException $e) {
            flash('Validation errors. Please check your input and try again.')->error();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error processing return to supplier: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            flash('An error occurred while processing your request. Please try again.')->error();
            return redirect()->back()->withInput();
        }

        // Dispatch events only after the transaction has committed successfully
        foreach ($pendingEvents as $eventPair) {
            event(new ProductStockAdjusted($eventPair['stockAdjusted']));
            event(new ProductStockAffected($eventPair['stockAffected']));
        }

        Cache::flush(); // Clear cache to reflect updated stock and supplier data
        flash('Products returned to supplier successfully.')->success();
        return redirect()->route('stock-adjust.return_supplier.create');
    }

    private function adjustSuppliersPayable(string $date, int $supplierId, float $amount, string $return_supplier_code)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $today = now()->format('Y-m-d');
        $total_pay = $amount;

        $paymentsData = [];
        $inv_counting = Payment::whereDate('date', $today)->distinct()->count('invoice_no');
        while ($total_pay > 0) {
            $invoice = PurchaseOrder::where('supplier_id', $supplierId)->whereColumn('total_payment', '<', 'grand_total')->first();
            if (!$invoice) {
                $paymentsData[] = [
                    'invoice_no' => "PAY-" . date('Ymd') . ($inv_counting++),
                    'date' => $date,
                    'payable_id' => $supplierId,
                    'payable_type' => Supplier::class,
                    'reference_id' => $supplierId,
                    'reference_type' => Supplier::class,
                    'seller_id' => auth()->id() ?? null,
                    'amount' => $payAmount,
                    'payment_details' => json_encode([
                        "payment_method" => "Return Adjustment",
                        "bank_type" => "Return Adjustment",
                        "bank_info" => "Payment against Return to Supplier. ReturnSupplier Code: " . $return_supplier_code,
                        "payment_amount" => $payAmount
                    ]),
                    'payment_method' => null,
                    'txn_code' => null,
                    'user_id' => auth()->id() ?? null,
                    'remarks' => "General Payment",
                ];
                break; // No more due invoices, exit the loop
            }

            $dueAmount = $invoice->grand_total - $invoice->total_payment;
            $payAmount = min($dueAmount, $total_pay);
            $paymentsData[] = [
                'invoice_no' => "PAY-" . date('Ymd') . ($inv_counting++),
                'date' => $date,
                'payable_id' => $supplierId,
                'payable_type' => Supplier::class,
                'reference_id' => $invoice->id,
                'reference_type' => PurchaseOrder::class,
                'seller_id' => auth()->id() ?? null,
                'amount' => $payAmount,
                'payment_details' => json_encode([
                    "payment_method" => "Return Adjustment",
                    "bank_type" => "Return Adjustment",
                    "bank_info" => "Payment against Return to Supplier. ReturnSupplier Code: " . $return_supplier_code,
                    "payment_amount" => $payAmount
                ]),
                'payment_method' => null,
                'txn_code' => null,
                'user_id' => auth()->id() ?? null,
                'remarks' => "Payment for Purchase Invoice " . $invoice->po_number . " against Return to Supplier. ReturnSupplier Code: " . $return_supplier_code,
            ];
            $invoice->total_payment += $payAmount;
            $invoice->total_due = $invoice->grand_total - $invoice->total_payment;
            $invoice->save();

            $total_pay -= $payAmount; // Reduce the remaining amount to pay
        }

        if (!empty($paymentsData)) {
            Payment::insert($paymentsData);
        } else {
            throw new \Exception("No due invoices found for the supplier. Unable to apply payment for ReturnSupplier Code: " . $return_supplier_code);
        }

        // Generate voucher no
        $voucherCount = AccVoucherEntry::whereDate('date', $today)
            ->distinct('vno')->count('vno');
        $voucherNo = 'VNO-' . now()->format('Ymd') . ($voucherCount + 1);

        // Generate transaction vno
        $transactionCount = AccTransaction::whereDate('date', $today)
            ->distinct('vno')->count('vno');

        $purchaseHeadName = 'Purchase';
        $supplierHeadName = $supplier->name . ' ' . $supplier->contact_number;

        $purchaseHead = AccHead::where('head', 'like', "%{$purchaseHeadName}%")->first();
        $supplierHead = AccHead::where('head', 'like', "%{$supplierHeadName}%")->first();

        $note = "Payment for Return to Supplier. ReturnSupplier Code: {$return_supplier_code}";

        $creditDescription = "Credited to {$supplierHeadName} Adjust Suppliers Payable for Return to Supplier. ReturnSupplier Code: {$return_supplier_code}";

        $debitDescription = "Debited from {$purchaseHeadName} Adjust Suppliers Payable for Return to Supplier. ReturnSupplier Code: {$return_supplier_code}";

        $transactions = [
            [
                'date' => $today,
                'user_id' => auth()->id(),
                'vno' => now()->format('Ymd') . '-' . ($transactionCount + 1),
                'head' => $purchaseHeadName,
                'head_type' => AccHead::class,
                'head_id' => $purchaseHead?->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => $creditDescription,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => $today,
                'user_id' => auth()->id(),
                'vno' => now()->format('Ymd') . '-' . ($transactionCount + 2),
                'head' => $supplierHeadName,
                'head_type' => Supplier::class,
                'head_id' => $supplier->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => $debitDescription,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        AccTransaction::insert($transactions);

        $voucherEntries = [
            [
                'date'             => $today,
                'vno'              => $voucherNo,
                'voucher_type'     => 'payment',
                'entry_type'       => 'credit',
                'debit'            => 0,
                'credit'           => $amount,
                'particular_id'    => $purchaseHead?->id,
                'particular_type'  => $purchaseHead?->reference_type ?? AccHead::class,
                'note'             => $note,
                'user_id'          => auth()->id(),
                'created_at'       => now(),
                'updated_at'       => now()
            ],
            [
                'date'             => $today,
                'vno'              => $voucherNo,
                'voucher_type'     => 'payment',
                'entry_type'       => 'debit',
                'debit'            => $amount,
                'credit'           => 0,
                'particular_id'    => $supplierHead?->id,
                'particular_type'  => $supplierHead?->reference_type ?? AccHead::class,
                'note'             => $note,
                'user_id'          => auth()->id(),
                'created_at'       => now(),
                'updated_at'       => now()
            ],
        ];

        AccVoucherEntry::insert($voucherEntries);
    }
}
