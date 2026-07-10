<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithStrictNullComparison, ShouldQueue, WithChunkReading, WithTitle, WithCustomStartCell, WithStyles
{
    protected $orders;
    protected $summary;

    public function __construct($orders, $summary = [])
    {
        $this->orders = $orders;
        $this->summary = $summary;
    }

    public function collection()
    {
        return collect($this->orders)->map(function ($order) {
            return $this->map($order);
        });
    }

    public function map($order): array
    {
        $shippingAddress = json_decode($order->shipping_address ?? '', true);
        $shippingCost = $order->orderDetails->sum('shipping_cost');
        return [
            'Date' => $order->created_at->format('d-m-Y'),
            'Order Code' => $order->code,
            'Customer Name' => data_get($shippingAddress, 'name'),
            'Customer Phone' => data_get($shippingAddress, 'phone'),
            'Order Amount' => round($order->grand_total - $shippingCost, 2),
            'Delivery Charge' => round($shippingCost, 2),
            'Delivery Status' => ucwords(str_replace('_', ' ', $order->delivery_status)),
            'Payment Method' => ucwords(str_replace('_', ' ', $order->payment_type)),
            'Payment Status' => strtolower($order->payment_status) == 'unpaid' ? 'Un-Paid' : ucfirst($order->payment_status),
            'Order Source' => strtoupper($order->order_source),
        ];
    }

    public function headings(): array
    {
        $summaryText = sprintf(
            'Total Count: %d' . PHP_EOL .
            'Total Sales Amount: %s' . PHP_EOL .
            'Total Delivery Charge: %s' . PHP_EOL .
            'Grand Total Amount: %s',
            $this->orders->count(),
            single_price($this->summary['sales_amount'] ?? 0),
            single_price($this->summary['delivery_charge'] ?? 0),
            single_price($this->summary['total'] ?? 0)
        );

        return [
            [$summaryText],
            [], // Empty row for spacing
            [
                'Date',
                'Order Code',
                'Customer Name',
                'Customer Phone',
                'Order Amount',
                'Delivery Charge',
                'Delivery Status',
                'Payment Method',
                'Payment Status',
                'Order Source'
            ]
        ];
    }

    public function startCell(): string
    {
        return 'A1'; // Data starts at row 1 to accommodate summary
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A1:J1' => [
                'mergeCells' => true,
                'alignment' => ['horizontal' => 'left'],
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD9D9D9']
                ]
            ],
            'A3:J3' => [
                'font' => ['bold' => true]
            ]
        ];
    }

    public function title(): string
    {
        return 'Sales Report';
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
