<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewriteUrl extends Model
{
    use HasFactory;

    protected $table = 'rewrite_urls';

    protected $fillable = [
        'url',
        'redirect_to',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('d M, Y');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    protected static function booted()
    {
        // static::saving(function ($model) {
        //     $model->url = '/' . trim($model->url, '/');
        //     $model->redirect_to = '/' . trim($model->redirect_to, '/');
        // });

        // static::updating(function ($model) {
        //     $model->url = '/' . trim($model->url, '/');
        //     $model->redirect_to = '/' . trim($model->redirect_to, '/');
        // });

        static::saved(function () {
            Cache::forget('rewrite_urls');
        });

        static::deleted(function () {
            Cache::forget('rewrite_urls');
        });
    }
}
