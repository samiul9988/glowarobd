<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ExpireProductsExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $items;

    public function __construct($items)
    {
        $subset = $items->map(function ($item) {
            return [
                'name' => $item->product->name,
                'left_qty' => $item->left_qty ?? 0,
                'expire_date' => $item->expire_date ? Carbon::parse($item->expire_date)->format('d-m-Y') : 'N/A',
            ];
        });

        $this->items = $subset;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return ['Product Name', 'Left Qty (Pcs)', 'Expire Date'];
    }
}
