<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class NotSellingProductsExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $products;
    protected $summary;

    public function __construct($products, array $summary = [])
    {
        $this->products = $products;
        $this->summary = $summary;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            $qty = $product->latest_stock_qty ?? 0;
            $unit_price = $product->lastPurchaseOrderItem?->price ?? 0;
            return [
                $product->name,
                $product->created_at->format('d-m-Y'),
                $product->brand?->name ?? 'N/A',
                $product->lastPurchaseOrderItem?->updated_at->format('d-m-Y') ?? 'N/A',
                single_price($unit_price),
                $qty,
                single_price($qty * $unit_price)
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product Name', 'Product Created At', 'Brand Name', 'Last Purchase Date', 'Last Purchase Price',
            'Current Stock' . (isset($this->summary['total_stocks']) ? ' (Total: '.$this->summary['total_stocks'].')' : ''),
            'Stock Amount' . (isset($this->summary['total_stock_amount']) ? ' (Total: '.single_price($this->summary['total_stock_amount']).')' : '')
        ];
    }
}
