<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class TopSellingProductsExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $items;
    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items->map(function ($item) {
            $currentStock = 0;
            if ($item->variant_product) {
                $currentStock = $item->product?->stocks?->sum('qty') ?? 0;
            } else {
                $currentStock = optional($item->product->stocks->first())->qty ?? 0;
            }

            // Show low stock warning if needed
            if ($currentStock <= $item->product->low_stock_quantity) {
                $currentStock .= ' (Low)';
            }
            return [
                'product_name' => $item->product_name,
                // 'product_link' => $item->product ? to_frontend(route('product', $item->product?->slug)) : 'N/A',
                'brand' => $item->brand ?? 'N/A',
                'total_sell_qty' => $item->total_quantity ?? 0,
                'total_sell_amount' => (round($item->total_amount ?? 0, 2)),
                'purchase_price' => round($item->product?->lastPurchaseOrderItem?->price ?? 0, 2),
                'last_sell' => Carbon::parse(@$item->max_time)->format('Y-m-d'),
                'current_stock' => $currentStock,
            ];
        });
    }

    public function headings(): array
    {
        // return ['Product Name', 'Product Link', 'Brand', 'Total Sell Qty', 'Total Sell Amount', 'Purchase Price', 'Last Sell', 'Current Stock'];
        return ['Product Name', 'Brand', 'Total Sell Qty', 'Total Sell Amount', 'Purchase Price', 'Last Sell', 'Current Stock'];
    }
}
