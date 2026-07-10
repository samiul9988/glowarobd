<?php

namespace App\Enums;

enum JobApplicationStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case HIRED = 'hired';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::HIRED => 'Hired',
            self::REJECTED => 'Rejected',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}
