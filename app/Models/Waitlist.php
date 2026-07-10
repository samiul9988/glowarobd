<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $fillable = [
        'product_id',
        'contact',
        'contact_type',
        'notified',
    ];

    protected $casts = [
        'notified' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
