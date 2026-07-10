<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LastThreeMonthsOrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $seenPhones = [];
    public function query()
    {
        $startDate = now()->subMonth(3)->startOfMonth();
        $endDate = now()->subDay(1)->endOfDay();

        return Order::query()
            ->select(['id', 'grand_total', 'shipping_address', 'created_at', 'delivery_status'])
            ->where('delivery_status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->orderBy('id');  // Ensure predictable order for chunks
    }

    public function headings(): array
    {
        return [
            'email',
            'phone',
            'add_to_messaging_customer_base_for_whatsapp',
            'madid',
            'fn',
            'ln',
            'zip',
            'ct',
            'st',
            'country',
            'dob',
            'doby',
            'gen',
            'age',
            'uid',
            'value'
        ];
    }

    public function map($order): array
    {
        $shippingAddress = json_decode($order->shipping_address, true) ?? [];
        $name = $shippingAddress['name'] ?? '';
        $email = $shippingAddress['email'] ?? '';
        $phone = str_replace('+88', '', $shippingAddress['phone'] ?? '');
        $address = $shippingAddress['address'] ?? '';
        $district = $shippingAddress['city'] ?? '';
        $country = 'BD';

        // Skip if Bangla content found (handled before mapping)
        if (isBanglaLanguage($name) || isBanglaLanguage($address) || isset($this->seenPhones[$phone]) || in_array($phone, ['01760833340','01714117604']) || empty($phone)) {
            return []; // Skip this row altogether
        }

        if (!empty($phone)) {
            $this->seenPhones[$phone] = true;
            $phone = '+88' . $phone;
        }
        return [
            $email, // email
            // "'" . $phone, // phone including leading apostrophe to preserve formatting in Excel
            $phone, // phone
            '', // add_to_messaging_customer_base_for_whatsapp
            '', // madid
            $name, // fn
            '', // ln
            '', // zip
            $address, // ct
            $district, // st
            $country, // country
            '', // dob
            '', // doby
            '', // gen
            '', // age
            '', // uid
            number_format($order->grand_total, 2) // value
        ];
    }
}
