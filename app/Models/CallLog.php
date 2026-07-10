<?php

namespace App\Models;

use App\Enums\CallStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    protected $table = 'call_logs';

    protected $fillable = [
        'reference_id',
        'reference_type',
        'called_by',
        'status',
        'rescheduled_at',
        'duration',
        'note',
    ];

    protected $casts = [
        'rescheduled_at' => 'datetime',
    ];

    public function reference()
    {
        return $this->morphTo();
    }

    public function orderOnly()
    {
        return $this->reference()->where('reference_type', Order::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(OrderFeedback::class, 'call_log_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    public function caller()
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    public function scopeByReference($query, $referenceType, $referenceId = null)
    {
        return $query->where('reference_type', $referenceType)
                     ->when($referenceId, fn($q) => $q->where('reference_id', $referenceId));
    }

    public function scopeForOrders($query, $orderId = null)
    {
        return $query->where('reference_type', Order::class)
                    ->when($orderId, fn($q) => $q->where('reference_id', $orderId));
    }

    public function scopeForCustomers($query, $customerId = null)
    {
        return $query->where('reference_type', User::class)
                    ->when($customerId, fn($q) => $q->where('reference_id', $customerId));
    }

}
