<?php

namespace App\Enums;

enum TemplateTypes: string
{
    case PRODUCT_STICKER = 'product_sticker';
    case SHIPPING_LABEL = 'shipping_label';
    case APPOINTMENT_LETTER = 'appointment-letter';
    case JOINING_LETTER = 'joining-letter';
    case PROMOTION_LETTER = 'promotion-letter';
    case INCREMENT_LETTER = 'increment-letter';
    case NOC = 'noc';

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT_STICKER => 'Product Sticker',
            self::SHIPPING_LABEL => 'Shipping Label',
            self::APPOINTMENT_LETTER => 'Appointment Letter',
            self::JOINING_LETTER => 'Joining Letter',
            self::PROMOTION_LETTER => 'Promotion Letter',
            self::INCREMENT_LETTER => 'Increment Letter',
            self::NOC => 'No Objection Certificate (NOC)',
            default => 'Unknown',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}
