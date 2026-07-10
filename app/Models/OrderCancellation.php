<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancellation extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'user_type',
        'reason_type',
        'reason',
    ];

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
