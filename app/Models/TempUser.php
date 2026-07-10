<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Schema;

class TempUser extends Model
{
    use Prunable;

    protected $table = 'temp_users';
    protected $fillable = ['user_id', 'temp_user_id'];
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    public function usesTimestamps(): bool
    {
        return Schema::hasColumns($this->getTable(), ['created_at', 'updated_at']);
    }

    public function prunable()
    {
        if (!Schema::hasColumns($this->getTable(), ['created_at', 'updated_at'])) {
            return static::query()->whereRaw('1 = 0'); // disable pruning
        }

        return static::where('created_at', '<=', now()->subDays(30));
    }

    protected function pruning()
    {
        \App\Models\Address::whereNotNull('temp_user_id')->where('temp_user_id', $this->temp_user_id)->delete();
        \App\Models\Cart::whereNotNull('temp_user_id')->where('temp_user_id', $this->temp_user_id)->delete();
        \App\Models\Wishlist::whereNotNull('temp_user_id')->where('temp_user_id', $this->temp_user_id)->delete();
        \App\Models\CouponUsage::whereNotNull('temp_user_id')->where('temp_user_id', $this->temp_user_id)->delete();
    }
}
