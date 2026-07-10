<?php

namespace App\Enums;

enum ApplicationTypes: string
{
    case CASUAL = 'casual';
    case LEAVE = 'leave';
    // case LOAN = 'loan';
    case COMPLAINT = 'complaint';
    // case CERTIFICATE = 'certificate';

    public function label(): string
    {
        return match ($this) {
            self::CASUAL => 'Casual',
            self::LEAVE => 'Leave',
            // self::LOAN => 'Loan',
            self::COMPLAINT => 'Complaint',
            // self::CERTIFICATE => 'Certificate',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->filter(function ($case) {
            if ($case->value === 'leave' && get_setting('enable_application_management', 0) != 1) {
                return false;
            } else {
                return true;
            }
        })->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}
