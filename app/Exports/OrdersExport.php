<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class OrdersExport implements FromCollection, WithHeadings, WithStrictNullComparison, ShouldQueue, WithChunkReading
{
    protected $orders;
    protected $type;

    public function __construct($orders, $type = 'Customer')
    {
        $this->orders = $orders;
        $this->type = $type;
    }

    public function collection()
    {
        return collect($this->orders)->map(function ($order) {
            return $this->map($order);
        });
    }

    public function map($order): array
    {
        $shippingAddress = json_decode($order->shipping_address ?? '');
        return [
            'Date & Time' => $order->created_at->format('d/m h:i:s A'),
            $this->type.' Name' => ucwords(data_get($shippingAddress, 'name', '')),
            'Number' => ucwords(data_get($shippingAddress, 'phone', '')),
            $this->type.' Address' => ucwords(data_get($shippingAddress, 'address', '')),
            'Product' => implode(', ', $order->orderDetails->pluck('product.name')->toArray()),
            'Qty' => $order->orderDetails->sum('quantity'),
            'Total Amount' => $order->grand_total,
            'Payment Method' => ucfirst(str_replace('_', ' ', $order->payment_type)),
            'Status' => ucfirst($order->delivery_status),
            'Shipping Method' => implode(', ', array_unique($order->orderDetails->pluck('shippingMethod.name')->toArray())),
            'Note' => '',
            'Source' => $order->order_source,
        ];
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            $this->type.' Name',
            'Number',
            $this->type.' Address',
            'Product',
            'Qty',
            'Total Amount',
            'Payment Method',
            'Status',
            'Shipping Method',
            'Note',
            'Source'
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

