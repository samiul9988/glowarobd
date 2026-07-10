<?php

namespace App\Enums;

enum Reasons
{
    public static function all(string $type = ''): array
    {
        return match ($type) {
            'cancelled' => self::cancelReason(),
            'returned' => self::returnReason(),
            default => array_unique(array_merge(self::cancelReason(), self::returnReason())),
        };
    }

    public static function value(string $key)
    {
        return self::all()[$key] ?? null;
    }

    public static function key(string $value): string
    {
        return array_search($value, self::all()) ?: 'other';
    }

    public static function cancelReason(): array
    {
        return [
            'change_of_mind' => 'Change of Mind',
            'ordered_by_mistake' => 'Ordered by Mistake',
            'out_of_stock' => 'Out of Stock',
            'better_price_found' => 'Better Price Found',
            'order_different_product' => 'Order different product',
            'no_response' => 'No Response',
            'double_ordered' => 'Double Ordered',
            'other' => 'Other',
        ];
    }

    public static function returnReason(): array
    {
        return [
            'not_received' => 'Not Received',
            'exchange' => 'Exchange',
            'wrong_product_sent' => 'Wrong Product Sent',
            'defective_or_damaged' => 'Defective or Damaged',
            'no_longer_needed' => 'No Longer Needed',
            'not_satisfied_with_quality' => 'Not Satisfied with Quality',
            'authenticity_issues' => 'Authenticity Issues',
            'other' => 'Other'
        ];
    }
}
