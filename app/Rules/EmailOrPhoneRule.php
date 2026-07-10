<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class EmailOrPhoneRule implements Rule
{
    protected $message;

    public function __construct()
    {
        $this->message = 'Please enter a valid email address or phone number.';
    }

    public function passes($attribute, $value)
    {
        // ✅ Check if it's a valid email
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $exists = User::where('email', $value)->exists();
            if ($exists) {
                $this->message = 'The email has already been taken.';
                return false;
            }
            return true;
        }

        // Accepts: +8801XXXXXXXXX or 01XXXXXXXXX (exact 11 digits if starting with 0)
        if (preg_match('/^(?:\+8801\d{9}|01\d{9})$/', $value)) {
            $exists = User::where('phone', $value)->exists();
            if ($exists) {
                $this->message = 'The phone number has already been taken.';
                return false;
            }
            return true;
        }

        // ❌ Neither valid email nor phone
        $this->message = 'Please enter a valid email address or phone number.';
        return false;
    }

    public function message()
    {
        return $this->message;
    }
}
