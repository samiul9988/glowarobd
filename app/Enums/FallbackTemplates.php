<?php

namespace App\Enums;

use App\Models\User;

enum FallbackTemplates
{
    public static function getTemplates(): array
    {
        return [
            'call_not_received' => 'Hi {customer}, we tried calling you for a quick feedback about your experience with {company}. We value your opinion! Please share your thoughts or call us back at your convenience. Thank you!',
            'reschedule_reminder' => 'Reminder: Dear {customer}, Today you have a scheduled meeting with us. Thank you!',
        ];
    }

    public static function getLabels(): array
    {
        return [
            'call_not_received' => 'Call Not Received',
            'reschedule_reminder' => 'Reschedule Reminder',
        ];
    }

    public static function getLabel(string $key): string
    {
        return self::getLabels()[$key] ?? 'Unknown';
    }

    public static function replacePlaceholder(string $key, User $customer): string
    {
        $template = self::getTemplates()[$key] ?? '';
        $replacements = [
            '{customer}' => $customer->name,
            '{company}' => config('app.name'),
        ];

        return strtr($template, $replacements);
    }
}
