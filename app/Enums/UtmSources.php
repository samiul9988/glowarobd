<?php

namespace App\Enums;

enum UtmSources
{
    public static function all(): array
    {
        return [
            'an' => 'AdNetwork',
            'app' => 'App',
            'fb' => 'Facebook',
            'ig' => 'Instagram',
            'li' => 'LinkedIn',
            'th' => 'Thread',
            'tw' => 'Twitter',
            'website' => 'Website',
        ];
    }

    public static function value(string $key)
    {
        return self::all()[strtolower($key)] ?? $key;
    }
}
