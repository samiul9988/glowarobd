<?php

namespace App\Http\Controllers;

use App\Jobs\MerchantProductPushJob;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\UpdateMerchantProductPriceJob;

class MerchantProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::published()
            ->with('lastPurchaseOrderItem:id,product_id,price', 'merchantProducts.merchant:id,name')
            ->where('auction_product',0)
            ->when($request->brand_id, function($query) use ($request){
                return $query->where('brand_id', $request->brand_id);
            })
            ->when($request->category_id, function($query) use ($request){
                return $query->where('category_id', $request->category_id);
            })
            ->when($request->search, function($query) use ($request){
                return $query->where('name', 'like', '%'.$request->search.'%');
            })
            // ->latest()
            ->paginate(15);

        // dd($products->toArray());
        return view('backend.product.merchant_products.index', compact('products'));
    }

    public function updatePrice(Request $request)
    {
        // dd($request->all());
        $productId = $request->input('product_id');
        $newPrice = $request->input('new_price');

        $product = Product::with('lastPurchaseOrderItem:id,product_id,price')->find($productId);
        if(!$product){
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        $minPrice = $product->lastPurchaseOrderItem?->price ?? 0;
        $maxPrice = $product->unit_price;
        if(!is_numeric($newPrice) || $newPrice < $minPrice || $newPrice > $maxPrice){
            return response()->json([
                'success' => false,
                'message' => 'Invalid price specified.'
            ], 400);
        }

        // Call the RokomariService to update the price
        $rokomariService = new \App\Services\RokomariService();
        $response = $rokomariService->updatePrice($productId, $newPrice);
        $status = $response->status();
        $body = is_array($response->body()) ? $response->body() : json_decode($response->body(), true);
        $success = $body['result'] ?? false;

        Log::channel('merchant')->info("Attempting to update price for product ID {$productId} to {$newPrice}. Response status: {$status}, body: ", $body);

        if ($response->successful() && $status == 200 && $success) {
            \App\Models\MerchantProduct::updateOrCreate(
                ['merchant_id' => $rokomariService::getMerchantId(), 'product_id' => $productId],
                ['last_price' => $newPrice]
            );
            Log::channel('merchant')->info("✅ SUCCESS: Price for product ID {$productId} updated successfully to {$newPrice}.");

            return response()->json([
                'success' => true,
                'message' => 'Product price updated successfully.'
            ]);
        } elseif($status == 200 && !$success) {
            return response()->json([
                'success' => false,
                'message' => $body['message'] ?? 'Request failed.'
            ], 200);
        } else {
            $statusMessage = match ($status) {
                400 => "⛔ ERROR: Bad Request (400)",
                404 => "⛔ ERROR: Not Found (404)",
                500 => "🔥 ERROR: Server Error (500)",
                default => "⁉️ UNKNOWN ERROR ({$status})",
            };

            Log::channel('merchant')->warning("{$statusMessage} while updating price for product ID {$productId}: {$body}");
            return response()->json([
                'success' => false,
                'message' => match ($status) {
                    400 => 'Bad Request. Please check the data and try again.',
                    404 => 'Product not found on Rokomari.',
                    500 => 'Server error at Rokomari. Please try again later.',
                    default => 'An unknown error occurred. Please try again.'
                }
            ], $status);
        }
    }

    public function bulkUpdatePrice(Request $request)
    {
        $amount = $request->input('amount');

        if (!is_numeric($amount) || $amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid amount specified.'
            ], 400);
        }

        // Dispatch a job to process the bulk update asynchronously
        UpdateMerchantProductPriceJob::dispatch($request->all(), \App\Services\RokomariService::getMerchantId())->onQueue('high');

        // Return the final response
        return response()->json([
            'success' => true,
            'message' => "Bulk price update initiated. Admin will be notified upon completion.",
        ]);
    }

    public function bulkPushProduct(Request $request)
    {
        // Dispatch a job to process the bulk push asynchronously
        MerchantProductPushJob::dispatch($request->all(), \App\Services\RokomariService::getMerchantId())->onQueue('high');

        // Return the final response
        return response()->json([
            'success' => true,
            'message' => "Bulk product push initiated. Admin will be notified upon completion.",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'attachment' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('attachment');
        $ext = $file->extension();
        $fileName = 'merchant_products_' . time() . '_' . date('Y_m_d') . '.' . $ext;

        // Store in storage/app/public/imports/
        $path = $file->storeAs('imports', $fileName, 'public');
        $fullPath = storage_path('app/public/' . $path);

        $rokomariService = new \App\Services\RokomariService();
        $rokomariService->importMerchantProducts($fullPath);

        if($request->ajax() || $request->wantsJson()){
            return response()->json([
                'success' => true,
                'message' => 'Import is in progress. You will be notified once it is complete.'
            ]);
        }else{
            flash('Import is in progress. You will be notified once it is complete.')->success();
            return back();
        }
    }

}
