<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFeedback extends Model
{
    use HasFactory;

    protected $table = 'crm_orders_feedbacks';

    protected $fillable = ['order_id', 'call_log_id', 'feedback', 'note', 'rating', 'created_by'];

    protected $casts = [
        'feedback' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function callLog()
    {
        return $this->belongsTo(CallLog::class, 'call_log_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }
}
