<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class ProductVisit extends Model
{
    use Prunable;

    protected $fillable = [
        'product_id',
        'user_id',
        'utm_source',
        'ref_id',
        'ip_address',
        'user_agent',
    ];

    public function prunable()
    {
        if (get_setting('retain_product_visit_logs_forever', 1)) {
            return static::query()->whereRaw('1 = 0'); // nothing to prune
        }
        return static::where('created_at', '<=', now()->subMonth(get_setting('retain_product_visit_logs_months', 12)));
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($productVisit) {
            $product = $productVisit->product;
            if ($product) {
                $product->timestamps = false;
                $product->increment('views_count');
                $product->last_viewed_at = now();
                $product->save();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
