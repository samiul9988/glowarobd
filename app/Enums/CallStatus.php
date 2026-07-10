<?php

namespace App\Enums;

enum CallStatus
{
    public static function getAllStatus(): array
    {
        return [
            'bkash_advance_payment' => 'Bkash Advance Payment',
            'call_me_later' => 'Call Me Later',
            'call_received' => 'Call Received',
            'no_response' => 'No Response',
            'order_hold' => 'Order Hold',
            'others' => 'Others',
            'out_of_stock' => 'Out of Stock',
            're_schedule' => 'Re-Schedule',
            'shipment_failed' => 'Shipment Failed'
        ];
    }

    public static function getStatus($type = 'order'): array
    {
        if($type == 'crm')
        {
            return [
                'call_received' => 'Call Received',
                'no_response' => 'No Response',
                're_schedule' => 'Re-Schedule',
            ];
        } else {
            return [
                'bkash_advance_payment' => 'Bkash Advance Payment',
                'call_me_later' => 'Call Me Later',
                'call_received' => 'Call Received',
                'no_response' => 'No Response',
                'order_hold' => 'Order Hold',
                'others' => 'Others',
                'out_of_stock' => 'Out of Stock',
                're_schedule' => 'Re-Schedule',
                'shipment_failed' => 'Shipment Failed'
            ];
        }
    }

    public static function getStatusName(string $status): string
    {
        return self::getAllStatus()[$status] ?? 'Unknown';
    }
}
