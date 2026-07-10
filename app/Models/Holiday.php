<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'color',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function isPast(): bool
    {
        return $this->date->startOfDay()->lt(Carbon::today());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
