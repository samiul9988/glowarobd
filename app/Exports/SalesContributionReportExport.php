<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class SalesContributionReportExport implements FromCollection, WithHeadings, WithStrictNullComparison, ShouldQueue, WithChunkReading
{
    protected $usageCoupons;

    public function __construct($usageCoupons)
    {
        $this->usageCoupons = $usageCoupons;
    }

    public function collection()
    {
        return collect($this->usageCoupons)->map(function ($usageCoupon) {
            return $this->map($usageCoupon);
        });
    }

    public function map($usageCoupon): array
    {
        $shipping_info = json_decode($usageCoupon->order?->shipping_address, true);
        return [
            'Date & Time' => $usageCoupon->order?->created_at->format('d-m-Y h:i A'),
            'Order ID' => $usageCoupon->order?->code,
            'Customer Name' => ucwords(data_get($shipping_info, 'name', '')),
            'Customer Phone' => ucwords(data_get($shipping_info, 'phone', '')),
            'Coupon' => $usageCoupon->coupon?->code,
            'Coupon Discount' => $usageCoupon->order?->coupon_discount ?? 0,
            'Shipping Charge' => $usageCoupon->order?->total_shipping_cost ?? 0,
            'Order Amount' => $usageCoupon->order?->grand_total ?? 0,
            'Order Source' => strtoupper($usageCoupon->order?->order_source),
            'Order Status' => ucfirst($usageCoupon->order?->delivery_status),
        ];
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            'Order ID',
            'Customer Name',
            'Customer Phone',
            'Coupon',
            'Coupon Discount',
            'Shipping Charge',
            'Order Amount',
            'Order Source',
            'Order Status'
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
