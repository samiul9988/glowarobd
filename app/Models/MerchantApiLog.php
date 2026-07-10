<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantApiLog extends Model
{
    use HasFactory, Prunable;

    protected $table = 'merchant_api_logs';

    protected $fillable = [
        'user_id',
        'method',
        'url',
        'payload',
        'response',
        'response_code',
        'response_time',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'json',
        'response' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subMonth())->limit(1000);
    }
}