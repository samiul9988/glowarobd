<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithChunkReading,
    WithMapping
};
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductsExport implements FromQuery, WithHeadings, WithChunkReading, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $currentStatus = $this->request['status'] ?? 'published';
        $products = Product::with(['stocks', 'lastPurchaseOrderItem', 'category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->where('auction_product', 0);

        if (isset($this->request['user_id'])) {
            $products->where('user_id', $this->request['user_id']);
        }

        if (isset($this->request['brand_id'])) {
            $products->where('brand_id', $this->request['brand_id']);
        }

        if (isset($this->request['category_id'])) {
            $products->where('category_id', $this->request['category_id']);
        }

        if (isset($this->request['search'])) {
            $products->where('name', 'like', "%{$this->request['search']}%");
        }

        if ($currentStatus == 'published') {
            $products->where('published', 1);
        } elseif ($currentStatus == 'unpublished') {
            $products->where('published', 0);
        } elseif ($currentStatus == 'outofstock') {
            $products->whereHas('stocks', function ($q) {
                $q->where('qty', '<=', 0);
            })->where('published', 1);
        } elseif ($currentStatus == 'lowstock') {
            $products->whereHas('stocks', function ($q) {
                $q->where('qty', '>', 0)->whereRaw('qty <= products.low_stock_quantity');
            });
        }

        $orderByCol = 'created_at';
        $orderByType = 'desc';
        if (isset($this->request['type'])) {
            $var = explode(",", $this->request['type']);
            if (count($var) > 2) {
                $orderByCol = $var[0];
                $orderByType = $var[1];
            }
        }

        $products->orderBy($orderByCol, $orderByType);

        return $products;
        // return $products->select('id', 'name', 'category_id', 'unit_price', 'discount_start_date', 'discount_end_date', 'discount_type', 'discount');
    }

    public function map($product): array
    {
        $product_stock = $product->stocks->first();
        $appPrice = getMinimumPriceByVariant($product, $product_stock, 'app', 1, null);
        $webPrice = getMinimumPriceByVariant($product, $product_stock, 'web', 1, null);
        return [
            $product->name,
            optional($product->category)->name ?? 'N/A',
            $product->unit_price,
            optional($product->lastPurchaseOrderItem)->price ?? 'N/A',
            // $product->app_price,
            // $product->web_price,
            $appPrice,
            $webPrice,
            $product->stocks->sum('qty'),
        ];
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Category',
            'Unit Price',
            'Purchase Price',
            'App Price',
            'Web Price',
            'Current Stock'
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
