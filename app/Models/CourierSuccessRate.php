<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierSuccessRate extends Model
{
    protected $table = 'courier_success_rate';

    protected $fillable = [
        'phone',
        'summary',
        'success_rate',
    ];

    protected $casts = [
        'summary' => 'array',
    ];
}
