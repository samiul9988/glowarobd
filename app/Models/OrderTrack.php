<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTrack extends Model
{
    protected $fillable = [
        'order_id',
        'ref_id',
        'utm_source',
        'utm_medium',
        'utm_content',
        'utm_campaign',
        'utm_term',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function referral()
    {
        return $this->belongsTo(User::class, 'ref_id');
    }
}
