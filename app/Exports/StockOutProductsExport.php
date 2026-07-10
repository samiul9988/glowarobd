<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class StockOutProductsExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $products;
    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            return [
                $product->name,
                $product->num_of_sale ?? 0,
                ($product->last_30_days_sell ?? 0) . " Qty",
                single_price(optional($product->lastPurchaseOrderItem)->price ?? 0),
                optional($product->lastPurchaseOrderItem)->qty ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return ['Product Name', 'Number Of Sale', 'Last 30 Days Sales', 'Last Purchase Price', 'Last Purchase Qty'];
    }
}
