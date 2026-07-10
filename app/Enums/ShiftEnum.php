<?php

namespace App\Enums;

use Carbon\Carbon;

enum ShiftEnum: string
{
    case DAY = 'day';
    case EVENING = 'evening';
    case NIGHT = 'night';

    public function checkIn(Carbon $date): Carbon
    {
        return match ($this) {
            self::DAY => $date->copy()->setTime(10, 0),
            self::EVENING => $date->copy()->setTime(15, 0),
            self::NIGHT => $date->copy()->setTime(18, 0),
        };
    }

    public function checkOut(Carbon $date): Carbon
    {
        return match ($this) {
            self::DAY => $date->copy()->setTime(18, 0),
            self::EVENING => $date->copy()->setTime(20, 0),
            self::NIGHT => $date->copy()->addDay()->setTime(1, 0),
        };
    }

    public function reportingTime(): string
    {
        return match ($this) {
            self::DAY => '10:00',
            self::EVENING => '15:00',
            self::NIGHT => '18:00',
        };
    }

    public function reportingTimeFormatted(): string
    {
        return match ($this) {
            self::DAY => '10:00 AM',
            self::EVENING => '3:00 PM',
            self::NIGHT => '6:00 PM',
        };
    }

    public function schedule(): string
    {
        return match ($this) {
            self::DAY => '10:00 AM - 06:00 PM',
            self::EVENING => '03:00 PM - 08:00 PM',
            self::NIGHT => '06:00 PM - 01:00 AM',
        };
    }

    public function range(Carbon $now): array
    {
        return [
            $this->checkIn($now),
            $this->checkOut($now),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::DAY => 'Day Shift',
            self::EVENING => 'Evening Shift',
            self::NIGHT => 'Night Shift',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DAY => 'sun',
            self::EVENING => 'cloud-sun',
            self::NIGHT => 'moon',
            default => 'cloak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DAY => 'success',
            self::EVENING => 'secondary',
            self::NIGHT => 'dark',
            default => 'primary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label()
            ])
            ->toArray();
    }
}
