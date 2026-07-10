<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'note',
        'called_by',
        'duration',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'called_by' => 'integer',
        'duration' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'called_by');
    }
    public function calledBy()
    {
        return $this->belongsTo(User::class, 'called_by');
    }
}
